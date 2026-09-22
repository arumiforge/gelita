<?php

/**
 * Helper umum GELITA. Dimuat global lewat Config\Autoload::$helpers.
 */

use CodeIgniter\HTTP\ResponseInterface;

if (! function_exists('uuid4')) {
    /** UUID versi 4 dari random_bytes(16), format 8-4-4-4-12 */
    function uuid4(): string
    {
        $b    = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0F) | 0x40); // versi 4
        $b[8] = chr((ord($b[8]) & 0x3F) | 0x80); // varian RFC 4122

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
    }
}

if (! function_exists('random_code')) {
    /** Hex acak; dipakai session_code (pengenal internal, bukan token login) */
    function random_code(int $bytes = 16): string
    {
        return bin2hex(random_bytes(max(1, $bytes)));
    }
}

if (! function_exists('participant_code')) {
    /** 1 → GLT-000001 */
    function participant_code(int $sequence): string
    {
        return sprintf('GLT-%06d', $sequence);
    }
}

if (! function_exists('hash_ip')) {
    /** SHA-256 IP + garam aplikasi. IP mentah tidak pernah disimpan. */
    function hash_ip(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        return hash('sha256', $ip . config('Gelita')->ipSalt);
    }
}

if (! function_exists('clamp')) {
    function clamp(float $v, float $min, float $max): float
    {
        return max($min, min($max, $v));
    }
}

if (! function_exists('ms_to_human')) {
    /** 125000 → "2:05"; 3725000 → "1:02:05" */
    function ms_to_human(int $ms): string
    {
        $total   = intdiv(max(0, $ms), 1000);
        $hours   = intdiv($total, 3600);
        $minutes = intdiv($total % 3600, 60);
        $seconds = $total % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%d:%02d', $minutes, $seconds);
    }
}

if (! function_exists('asset_url_versioned')) {
    /** base_url('assets/…') + ?v=assetVersion untuk cache-busting */
    function asset_url_versioned(string $path): string
    {
        return base_url('assets/' . ltrim($path, '/')) . '?v=' . rawurlencode(config('Gelita')->assetVersion);
    }
}

if (! function_exists('seeded_shuffle')) {
    /**
     * Acak deterministik untuk item_selection_mode = fixed
     * (benih participant_code|node_id → butir identik pada pretest & posttest).
     * Memakai Randomizer ber-engine sendiri, sehingga state mt_rand global
     * tidak tersentuh dan tidak perlu di-reseed.
     */
    function seeded_shuffle(array $items, string $seed): array
    {
        $randomizer = new Random\Randomizer(new Random\Engine\Mt19937(crc32($seed)));

        return $randomizer->shuffleArray(array_values($items));
    }
}

if (! function_exists('is_api_path')) {
    /** Path relatif request (tanpa garis miring depan) termasuk area /api? */
    function is_api_path(string $path): bool
    {
        $path = ltrim($path, '/');

        return $path === 'api' || str_starts_with($path, 'api/');
    }
}

if (! function_exists('api_error')) {
    /** Respons gagal JSON seragam untuk dipakai filter (di luar BaseController) */
    function api_error(string $code, string $message, int $status = 400, array $extra = []): ResponseInterface
    {
        $requestId = service('gelitaRequestId');

        return service('response')
            ->setStatusCode($status)
            ->setHeader('X-Request-Id', (string) $requestId)
            ->setJSON(array_merge([
                'success'    => false,
                'code'       => $code,
                'message'    => $message,
                'request_id' => $requestId,
            ], $extra));
    }
}

if (! function_exists('db_sync_timezone')) {
    /**
     * SET time_zone koneksi database sesuai Config\App::$appTimezone (WIB = +07:00),
     * agar DEFAULT CURRENT_TIMESTAMP(6) sama zonanya dengan waktu yang ditulis PHP.
     */
    function db_sync_timezone(): void
    {
        try {
            $offset = (new DateTimeImmutable('now', new DateTimeZone(config('App')->appTimezone)))->format('P');
            db_connect()->simpleQuery("SET time_zone = '{$offset}'");
        } catch (Throwable $e) {
            log_message('error', 'db_sync_timezone gagal: ' . $e->getMessage());
        }
    }
}
