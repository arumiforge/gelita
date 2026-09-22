<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter global: menentukan bahasa untuk seluruh request.
 *
 * Urutan:
 * 1. ?lang=id|en            → simpan ke session, redirect tanpa query
 * 2. session('locale')
 * 3. game_sessions.locale   → bila ada sesi permainan aktif (disalin ke session)
 * 4. 'id'
 *
 * Panel admin (/admin, /api/admin) selalu 'id' — hanya area game yang dwibahasa.
 */
class LocaleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        $path = $request->getPath();

        if ($this->isAdminPath($path)) {
            $this->apply($request, 'id');

            return null;
        }

        $supported = config('Gelita')->locales;
        $query     = $request->getGet();

        // 1. ?lang=
        if ($request->getMethod() === 'GET' && ! is_api_path($path) && array_key_exists('lang', $query)) {
            if (is_string($query['lang']) && in_array($query['lang'], $supported, true)) {
                session()->set('locale', $query['lang']);
            }

            unset($query['lang']);
            $target = site_url($path) . ($query === [] ? '' : '?' . http_build_query($query));

            return redirect()->to($target);
        }

        // 2. session
        $locale = session('locale');

        // 3. sesi permainan aktif
        if (! in_array($locale, $supported, true)) {
            $locale = $this->gameSessionLocale();

            if ($locale !== null) {
                session()->set('locale', $locale);
            }
        }

        // 4. default
        if (! in_array($locale, $supported, true)) {
            $locale = config('App')->defaultLocale;
        }

        $this->apply($request, $locale);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function apply(IncomingRequest $request, string $locale): void
    {
        $request->setLocale($locale);
        service('language')->setLocale($locale);
    }

    private function isAdminPath(string $path): bool
    {
        return $path === 'admin' || str_starts_with($path, 'admin/')
            || $path === 'api/admin' || str_starts_with($path, 'api/admin/');
    }

    private function gameSessionLocale(): ?string
    {
        $participantId = (int) session('participant_id');
        $gameSessionId = (int) session('game_session_id');

        if ($participantId <= 0 || $gameSessionId <= 0) {
            return null;
        }

        $row = db_connect()->table('game_sessions')
            ->select('locale')
            ->where('id', $gameSessionId)
            ->where('participant_id', $participantId)
            ->where('status !=', 'abandoned')
            ->get()
            ->getRow();

        return $row?->locale;
    }
}
