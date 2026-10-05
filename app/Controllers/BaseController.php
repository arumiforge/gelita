<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use App\Libraries\DeviceProfile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'text', 'gelita', 'content', 'ui'];

    protected string $locale = 'id';

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);

        $this->locale = service('request')->getLocale() ?: 'id';
    }

    /** Respons JSON sukses dengan bentuk seragam */
    protected function ok(array $data = [], int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success'    => true,
            'data'       => $data,
            'request_id' => service('gelitaRequestId'),
        ]);
    }

    /** Respons JSON gagal dengan bentuk seragam */
    protected function fail(string $code, string $message, int $status = 400, array $extra = []): ResponseInterface
    {
        $requestId = service('gelitaRequestId');

        log_message('warning', "API fail [{$code}] {$message} request_id={$requestId}");

        return $this->response->setStatusCode($status)->setJSON(array_merge([
            'success'    => false,
            'code'       => $code,
            'message'    => $message,
            'request_id' => $requestId,
        ], $extra));
    }

    /**
     * Profil perangkat untuk `device_logs` dan kolom ringkas `game_sessions`
     * (App\Libraries\DeviceProfile): User-Agent + Client Hints header +
     * `device_json` formulir (boleh kosong bila JavaScript mati).
     *
     * @return array<string, mixed>
     */
    protected function deviceInfo(): array
    {
        if (! $this->request instanceof IncomingRequest) {
            return [];
        }

        $client = DeviceProfile::decodeClient((string) $this->request->getPost('device_json'));

        return DeviceProfile::capture($this->request, $client);
    }

    /** Ambil payload JSON sebagai array; JSON rusak → array kosong (validasi akan menolak). */
    protected function jsonBody(): array
    {
        try {
            $body = $this->request->getJSON(true);
        } catch (HTTPException) {
            return [];
        }

        return is_array($body) ? $body : [];
    }
}
