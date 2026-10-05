<?php

namespace App\Filters;

use App\Entities\GameSession;
use App\Models\GameSessionModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Event `screen_viewed` (Dok 1 §12.4): setiap halaman game yang dirender
 * (200 text/html) untuk siswa bersesi dicatat dengan kode layarnya,
 * kode layar sebelumnya (`from`, session PHP `last_screen`), dan parameter
 * rute. Pengalihan tidak dicatat. Lama tinggal tidak dihitung sistem —
 * peneliti menghitungnya dari selisih `occurred_at` event navigasi.
 */
class ScreenViewFilter implements FilterInterface
{
    /** pola path → [kode layar, nama parameter segmen] */
    private const ROUTES = [
        '#^gerbang(/peta)?$#'             => ['gate', []],
        '#^intro$#'                       => ['intro', []],
        '#^peta$#'                        => ['map', []],
        '#^wilayah/([^/]+)$#'             => ['region', ['level']],
        '#^dialog/([^/]+)$#'              => ['dialogue', ['code']],
        '#^misi/([^/]+)/(\d+)$#'          => ['brief', ['level', 'seq']],
        '#^tantangan/([^/]+)/(\d+)$#'     => ['challenge', ['level', 'seq']],
        '#^selesai/(\d+)$#'               => ['finished', ['attempt']],
        '#^hasil/([^/]+)/(\d+)$#'         => ['result', ['level', 'seq']],
        '#^tuntas/([^/]+)$#'              => ['region_done', ['level']],
        '#^penutup$#'                     => ['ending', []],
        '#^pustaka$#'                     => ['library_index', []],
        '#^pustaka/([^/]+)$#'             => ['library', ['level']],
        '#^profil$#'                      => ['profile', []],
        '#^refleksi$#'                    => ['reflection', []],
        '#^ganti-sandi$#'                 => ['change_password', []],
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($request->getMethod() !== 'GET' || $response->getStatusCode() !== 200) {
            return null;
        }

        if (! str_contains(strtolower($response->getHeaderLine('Content-Type') ?: 'text/html'), 'text/html')) {
            return null;
        }

        $match = self::resolve(trim($request->getPath(), '/'));

        if ($match === null) {
            return null;
        }

        $session = $this->session();

        if ($session === null) {
            return null;
        }

        [$screen, $params] = $match;

        if ($screen === 'library' && str_contains((string) $response->getBody(), 'data-screen="library-locked"')) {
            $screen = 'library_locked';
        }

        $refs = $this->refs($params);
        $from = session('last_screen');

        try {
            service('eventService')->record($session, 'screen_viewed', [
                'screen' => $screen,
                'path'   => '/' . trim($request->getPath(), '/'),
                'from'   => is_string($from) ? $from : null,
                'params' => $params === [] ? (object) [] : $params,
            ], $refs);
        } catch (\Throwable $e) {
            // Pencatatan navigasi tidak boleh menggagalkan halaman
            log_message('error', 'screen_viewed gagal dicatat: {message}', ['message' => $e->getMessage()]);

            return null;
        }

        session()->set('last_screen', $screen);

        return null;
    }

    /**
     * Kode layar + parameter rute untuk satu path (tanpa garis miring awal).
     *
     * @return array{0: string, 1: array<string, int|string>}|null
     */
    public static function resolve(string $path): ?array
    {
        foreach (self::ROUTES as $pattern => [$screen, $names]) {
            if (preg_match($pattern, $path, $m) !== 1) {
                continue;
            }

            $params = [];

            foreach ($names as $index => $name) {
                $value         = $m[$index + 1] ?? '';
                $params[$name] = ctype_digit($value) ? (int) $value : $value;
            }

            return [$screen, $params];
        }

        return null;
    }

    private function session(): ?GameSession
    {
        $context = service('gameContext');

        if ($context->session !== null) {
            return $context->session;
        }

        $participantId = (int) session('participant_id');
        $sessionId     = (int) session('game_session_id');

        if ($participantId <= 0 || $sessionId <= 0) {
            return null;
        }

        $session = model(GameSessionModel::class)->find($sessionId);

        return $session !== null && $session->participant_id === $participantId ? $session : null;
    }

    /**
     * level_id / challenge_node_id dari parameter rute bila relevan.
     *
     * @param array<string, int|string> $params
     *
     * @return array<string, int>
     */
    private function refs(array $params): array
    {
        if (! isset($params['level']) || ! is_string($params['level'])) {
            return [];
        }

        $content = service('contentRepository');

        foreach ($content->levels() as $level) {
            if ((string) $level->code !== $params['level']) {
                continue;
            }

            $refs = ['level_id' => (int) $level->id];

            if (isset($params['seq'])) {
                foreach ($content->nodesForLevel($level->id) as $node) {
                    if ($node->sequence === (int) $params['seq']) {
                        $refs['challenge_node_id'] = (int) $node->id;
                    }
                }
            }

            return $refs;
        }

        return [];
    }
}
