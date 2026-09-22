<?php

namespace App\Libraries;

use App\Filters\LocaleFilter;
use CodeIgniter\Debug\BaseExceptionHandler;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * - /api/*   → JSON seragam {success:false, code, message, request_id}.
 *              Production: tanpa pesan exception, kelas, atau stack trace.
 * - HTML     → error_404.php / error_500.php bergaya GELITA;
 *              development tetap menampilkan halaman detail CI4 (error_exception.php).
 * - CLI & request non-HTML lain → handler bawaan CodeIgniter.
 */
class GelitaExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    private const API_CODES = [
        400 => 'INVALID_PAYLOAD',
        401 => 'INVALID_SESSION',
        403 => 'FORBIDDEN',
        404 => 'NOT_FOUND',
        405 => 'NOT_FOUND',
        409 => 'CONFLICT',
        413 => 'TOO_MANY_EVENTS',
        422 => 'INVALID_PAYLOAD',
        429 => 'RATE_LIMITED',
    ];

    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode,
    ): void {
        if ($statusCode >= 500 || $statusCode < 400) {
            // request_id yang sama tampil di respons (JSON / halaman 500) dan di log
            log_message('error', 'request_id={id} {class}: {msg} di {where}', [
                'id'    => (string) service('gelitaRequestId'),
                'class' => $exception::class,
                'msg'   => $exception->getMessage(),
                'where' => clean_path($exception->getFile()) . ':' . $exception->getLine(),
            ]);
        }

        if (! $request instanceof IncomingRequest) {
            (new ExceptionHandler($this->config))->handle($exception, $request, $response, $statusCode, $exitCode);

            return;
        }

        // 404 routing terjadi sebelum filter berjalan: terapkan bahasa pengguna di sini
        try {
            (new LocaleFilter())->before($request);
        } catch (Throwable) {
            // bila session/database bermasalah, halaman error tetap tampil dalam bahasa default
        }

        if (is_api_path($request->getPath())) {
            $this->respondApi($exception, $response, $statusCode);
            $this->finish($exitCode);

            return;
        }

        if (! str_contains($request->getHeaderLine('accept'), 'text/html')) {
            (new ExceptionHandler($this->config))->handle($exception, $request, $response, $statusCode, $exitCode);

            return;
        }

        try {
            $response->setStatusCode($statusCode);
        } catch (HTTPException) {
            $statusCode = 500;
            $response->setStatusCode($statusCode);
        }

        if (! headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $request->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ), true, $statusCode);
        }

        $this->render($exception, $statusCode, $this->findHtmlView($exception, $statusCode));
        $this->finish($exitCode);
    }

    private function findHtmlView(Throwable $exception, int $statusCode): ?string
    {
        $dir = $this->viewPath . 'html' . DIRECTORY_SEPARATOR;

        if ($exception instanceof PageNotFoundException || $statusCode === 404) {
            $view = 'error_404.php';
        } elseif ($this->showsDetails()) {
            $view = 'error_exception.php';
        } elseif (is_file($dir . 'error_' . $statusCode . '.php')) {
            $view = 'error_' . $statusCode . '.php';
        } elseif ($statusCode >= 500) {
            $view = 'error_500.php';
        } else {
            $view = 'production.php';
        }

        return is_file($dir . $view) ? $dir . $view : null;
    }

    private function respondApi(Throwable $exception, ResponseInterface $response, int $statusCode): void
    {
        if ($statusCode < 400 || $statusCode > 599) {
            $statusCode = 500;
        }

        $requestId = service('gelitaRequestId');
        $code      = $statusCode >= 500 ? 'SERVER_ERROR' : (self::API_CODES[$statusCode] ?? 'INVALID_PAYLOAD');

        $message = match ($code) {
            'SERVER_ERROR'    => lang('Game.errServer'),
            'NOT_FOUND'       => lang('Game.errNotFound'),
            'FORBIDDEN'       => lang('Game.errForbidden'),
            'INVALID_SESSION' => lang('Game.sessionExpired'),
            'RATE_LIMITED'    => lang('Game.errRateLimited'),
            default           => lang('Game.errInvalidPayload'),
        };

        $body = [
            'success'    => false,
            'code'       => $code,
            'message'    => $message,
            'request_id' => $requestId,
        ];

        if ($this->showsDetails()) {
            $body['debug'] = [
                'type'    => $exception::class,
                'message' => $exception->getMessage(),
                'file'    => clean_path($exception->getFile()),
                'line'    => $exception->getLine(),
            ];
        }

        $response->setStatusCode($statusCode)
            ->setHeader('X-Request-Id', (string) $requestId)
            ->setJSON($body)
            ->send();
    }

    private function showsDetails(): bool
    {
        return ENVIRONMENT !== 'production'
            && in_array(strtolower((string) ini_get('display_errors')), ['1', 'true', 'on', 'yes'], true);
    }

    private function finish(int $exitCode): void
    {
        if (ENVIRONMENT !== 'testing') {
            exit($exitCode); // @codeCoverageIgnore
        }
    }
}
