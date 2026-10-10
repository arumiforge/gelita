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

if (! function_exists('session_tag')) {
    /**
     * Penanda buram sesi permainan untuk antrean event offline di browser.
     *
     * HMAC atas game_session_id — tidak dapat dibalik menjadi id sesi, tetapi
     * stabil selama sesi itu. JavaScript hanya mengirim ulang event yang
     * tandanya sama dengan sesi yang sedang berjalan, sehingga event siswa A
     * tidak pernah masuk ke sesi siswa B di komputer kelas yang sama.
     */
    function session_tag(?int $gameSessionId): ?string
    {
        if ($gameSessionId === null || $gameSessionId <= 0) {
            return null;
        }

        $key = (string) config('Encryption')->key;
        $key = $key !== '' ? $key : (string) config('Gelita')->ipSalt;

        return substr(hash_hmac('sha256', 'gelita-events|' . $gameSessionId, $key !== '' ? $key : 'gelita'), 0, 16);
    }
}

if (! function_exists('js_config')) {
    /**
     * Isi <script type="application/json" id="app-config"> untuk JavaScript:
     * locale, token CSRF, alamat API, penanda sesi, dan teks UI dari
     * lang('Js.*'). Tidak ada identitas siswa di sini.
     *
     * Asset package Stage 4 (docs/rencana-paket-aset.md §4.4): `text` holds the
     * texts of every game locale (`text.id`, `text.en`), so a language switch
     * changes JS-built toasts and modals without another request
     * (core/config.js setLocale()). `locale` is still the active one.
     *
     * @return array<string, mixed>
     */
    function js_config(string $locale): array
    {
        $keys = array_keys(require APPPATH . 'Language/id/Js.php');
        $text = [];

        foreach (config('Gelita')->locales as $code) {
            foreach ($keys as $key) {
                $text[$code][$key] = lang('Js.' . $key, [], $code);
            }
        }

        $gameSessionId = session('participant_id') ? (int) session('game_session_id') : null;

        return [
            'locale'     => $locale,
            'csrfName'   => csrf_token(),
            'csrfHash'   => csrf_hash(),
            'apiBase'    => base_url('api'),
            'baseUrl'    => base_url(),
            'sessionTag' => session_tag($gameSessionId),
            'sequenceFloor' => event_sequence_floor($gameSessionId),
            'maxTries'   => config('Gelita')->maxTries,
            // panggung game 16:9 (dp); dipakai Dok 3 dan event viewport_changed
            'stage'      => ['w' => 1280, 'h' => 720],
            'text'       => $text,
        ];
    }
}

if (! function_exists('event_sequence_floor')) {
    /**
     * Lowest client `sequence_no` for the page being rendered (core/events.js,
     * rencana-paket-aset §7.3): EventService::nextServerSequence(), which
     * ScreenViewFilter also takes for this page's `screen_viewed` after
     * rendering, so client events continue above it. Without a database it is
     * 0 and the client counter in sessionStorage still continues.
     */
    function event_sequence_floor(?int $gameSessionId): int
    {
        if (! $gameSessionId) {
            return 0;
        }

        try {
            return service('eventService')->nextServerSequence($gameSessionId);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
            return 0;
        }
    }
}

if (! function_exists('next_assets_attr')) {
    /**
     * ` data-next-assets="[…]"` for a screen element: images of the screen the
     * student most likely opens next (asset package Stage 4, §4.5). The game
     * client (game/next-assets.js) loads and decodes them while the browser is
     * idle, so the next screen paints without a decode flash. One screen
     * ahead only, at most $limit images (iPad memory); empty when none.
     *
     * @param list<string|null> $urls
     */
    function next_assets_attr(array $urls, int $limit = 6): string
    {
        $urls = array_values(array_unique(array_filter(
            $urls,
            static fn ($url): bool => is_string($url) && $url !== '',
        )));
        $urls = array_slice($urls, 0, max(0, $limit));

        if ($urls === []) {
            return '';
        }

        return ' data-next-assets="' . esc(json_encode($urls, JSON_UNESCAPED_SLASHES), 'attr') . '"';
    }
}

if (! function_exists('component')) {
    /**
     * Render komponen view dengan data yang dioper — hanya itu.
     *
     * `$this->include()` milik CodeIgniter menerima *options* (cache) sebagai
     * argumen kedua, bukan data — array yang dioper ke sana diabaikan diam-diam.
     * Sebaliknya `view()` biasa menggabungkan data halaman ke komponen, sehingga
     * variabel halaman bernama sama (mis. `$actions`, `$id`, `$hint`) bocor ke
     * komponen yang menganggapnya opsional. Helper ini merender dengan renderer
     * terpisah: komponen hanya melihat $data, ditambah konteks halaman yang
     * memang dimaksudkan untuk dibagi ($pageContext) bila tidak dioper ulang.
     *
     * @param array<string, mixed> $data
     */
    function component(string $name, array $data = []): string
    {
        // Konteks filter panel: dipakai admin-filter-bar di halaman yang sama
        static $pageContext = ['filters', 'filterOptions'];

        $view    = str_contains($name, '/') ? $name : 'components/' . $name;
        $context = array_intersect_key(service('renderer')->getData(), array_flip($pageContext));

        return \Config\Services::renderer(null, null, false)
            ->setData($data + $context, 'raw')
            ->render($view, null, false);
    }
}

if (! function_exists('seeded_shuffle')) {
    /**
     * Acak deterministik: urutan bank kata dan keping puzzle tetap untuk satu
     * pengerjaan (benih id pengerjaan), sehingga halaman yang dimuat ulang
     * tidak berubah. Memakai Randomizer ber-engine sendiri, sehingga state mt_rand global
     * tidak tersentuh dan tidak perlu di-reseed.
     */
    function seeded_shuffle(array $items, string $seed): array
    {
        $randomizer = new Random\Randomizer(new Random\Engine\Mt19937(crc32($seed)));

        return $randomizer->shuffleArray(array_values($items));
    }
}

if (! function_exists('seeded_derangement')) {
    /**
     * Urutan awal keping Susun Gambar (Dok 2 §4): acakan deterministik
     * 0..n−1 tanpa satu keping pun di tempatnya sendiri, tetap untuk satu
     * pengerjaan. Benih pertama sama dengan seeded_shuffle($seed); bila masih
     * ada keping di tempatnya, benih berikutnya dicoba (paling banyak 50
     * kali), lalu jatuh ke geser satu langkah yang selalu memenuhi syarat.
     *
     * @return list<int>
     */
    function seeded_derangement(int $count, string $seed): array
    {
        if ($count < 2) {
            return $count === 1 ? [0] : [];
        }

        $identity = range(0, $count - 1);

        for ($k = 0; $k < 50; $k++) {
            $order = seeded_shuffle($identity, $k === 0 ? $seed : $seed . '|' . $k);

            if (array_filter($order, static fn (int $piece, int $slot): bool => $piece === $slot, ARRAY_FILTER_USE_BOTH) === []) {
                return $order;
            }
        }

        return array_merge(array_slice($identity, 1), [0]);
    }
}

if (! function_exists('apply_research_filters')) {
    /**
     * Menerapkan filter penelitian baku (03_MODEL_ENTITY.md) pada builder yang sudah
     * men-join game_sessions, participants, dan challenge_nodes.
     *
     * Filter yang tabelnya tidak ikut di-join disebutkan di $skip agar dilewati.
     *
     * @param array<string, mixed> $filters
     * @param list<string>         $skip
     */
    function apply_research_filters(
        CodeIgniter\Database\BaseBuilder $builder,
        array $filters,
        array $skip = [],
    ): CodeIgniter\Database\BaseBuilder {
        $map = [
            'study_id'       => 'game_sessions.study_id',
            'participant_id' => 'game_sessions.participant_id',
            'level_id'      => 'challenge_nodes.level_id',
            'node_id'       => 'challenge_nodes.id',
            'school_id'     => 'participants.school_id',
            'class_level'   => 'participants.class_level',
            'province_code' => 'participants.province_code',
            'locale'        => 'game_sessions.locale',
        ];

        foreach ($map as $key => $column) {
            if (in_array($key, $skip, true)) {
                continue;
            }

            if (isset($filters[$key]) && $filters[$key] !== '' && $filters[$key] !== null) {
                $builder->where($column, $filters[$key]);
            }
        }

        if (! in_array('date_from', $skip, true) && ! empty($filters['date_from'])) {
            $builder->where('game_sessions.started_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (! in_array('date_to', $skip, true) && ! empty($filters['date_to'])) {
            $builder->where('game_sessions.started_at <=', $filters['date_to'] . ' 23:59:59');
        }

        return $builder;
    }
}

if (! function_exists('safe_internal_url')) {
    /**
     * URL tujuan untuk redirect yang berasal dari input pengguna (`redirect_to`).
     *
     * Hanya path internal yang diterima. Skema (`https:`, `javascript:`),
     * URL protocol-relative (`//jahat.example`), backslash, dan karakter baris
     * baru ditolak dan diganti $fallback — aturan 11 pada 04_CONTROLLER_ROUTE.md.
     */
    function safe_internal_url(?string $target, string $fallback = '/'): string
    {
        $target = trim((string) $target);

        $unsafe = $target === ''
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $target) === 1
            || preg_match('#^[a-z][a-z0-9+.\-]*:#i', $target) === 1
            || str_starts_with($target, '//');

        return site_url($unsafe ? ltrim($fallback, '/') : ltrim($target, '/'));
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
