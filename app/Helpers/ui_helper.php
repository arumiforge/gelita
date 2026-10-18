<?php

/**
 * Helper tampilan GELITA. Dimuat global lewat Config\Autoload::$helpers.
 *
 * Ikon digambar sebagai SVG inline bergaris (24×24, `currentColor`), sehingga
 * tidak ada font ikon atau CDN, warnanya mengikuti teks, dan tetap tajam di
 * layar proyektor. Ikon selalu dekoratif (`aria-hidden`); makna disampaikan
 * lewat teks atau `aria-label` pada elemen induknya.
 */

if (! function_exists('icon')) {
    function icon(string $name, string $class = ''): string
    {
        static $paths = [
            'home'      => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
            'left'      => '<path d="M15 18l-6-6 6-6"/>',
            'right'     => '<path d="M9 18l6-6-6-6"/>',
            'up'        => '<path d="M18 15l-6-6-6 6"/>',
            'down'      => '<path d="M6 9l6 6 6-6"/>',
            'sound'     => '<path d="M4 9h4l5-4v14l-5-4H4z"/><path d="M16.5 8.5a5 5 0 0 1 0 7"/><path d="M19 6a8.5 8.5 0 0 1 0 12"/>',
            'sound-off' => '<path d="M4 9h4l5-4v14l-5-4H4z"/><path d="M17 9l5 6M22 9l-5 6"/>',
            'fullscreen'      => '<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>',
            'fullscreen-exit' => '<path d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"/>',
            'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
            'lock'      => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
            'check'     => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
            // Kenali (Peta Kedu, page redesign 2): open book and magnifier, from the catalogue mockup icon.kenali
            'kenali'    => '<path d="M10 5Q6 2.5 2 4.5V18q4-2 8 .5V5zm0 0q4-2.5 8-.5v5M10 18.5q1.5-1 3-1M4.5 8l3 .5M4.5 11.5l3 .5"/><circle cx="16" cy="14" r="4.5"/><path d="M19.5 17.5l3 4"/>',
            'cross'     => '<path d="M6 6l12 12M18 6L6 18"/>',
            'star'      => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
            'book'      => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 21V5"/><path d="M8 7h7"/>',
            'map'       => '<path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/>',
            'play'      => '<path d="M8 5v14l11-7z" fill="currentColor"/>',
            'pause'     => '<path d="M8 5v14M16 5v14" stroke-width="3"/>',
            'replay'    => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/>',
            'text'      => '<path d="M4 6h16M4 12h16M4 18h10"/>',
            'hint'      => '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"/>',
            'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'building'  => '<path d="M3 21h18"/><path d="M5 21V9l7-5 7 5v12"/><path d="M9 21v-6h6v6"/>',
            'question'  => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6"/><path d="M12 17h.01"/>',
            'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
            'user-plus' => '<circle cx="10" cy="8" r="4"/><path d="M2 21a8 8 0 0 1 13.3-6"/><path d="M19 15v6M16 18h6"/>',
            'login'     => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/>',
            'users'     =>'<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M18 14a6.5 6.5 0 0 1 3.5 6"/>',
            'lantern'   => '<path d="M12 2v3"/><path d="M8 7h8l-1 12H9z"/><path d="M7 21h10"/><path d="M12 11v4"/>',
            'download'  => '<path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/>',
            'upload'    => '<path d="M12 21V9"/><path d="M7 14l5-5 5 5"/><path d="M5 3h14"/>',
            'eye'       => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'search'    => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
            'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6"/><path d="M12 7h.01"/>',
            'warn'      => '<path d="M12 3l10 18H2z"/><path d="M12 10v5"/><path d="M12 18h.01"/>',
            'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'chart'     => '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
            'list'      => '<path d="M9 6h12M9 12h12M9 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
            'target'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
            'trend'     => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
            'message'   => '<path d="M21 12a8 8 0 0 1-11.6 7.1L3 21l1.9-6.4A8 8 0 1 1 21 12z"/>',
            'image'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 17l-5-5-9 8"/>',
            'flask'     => '<path d="M9 3h6"/><path d="M10 3v6L4.5 19a1.5 1.5 0 0 0 1.3 2h12.4a1.5 1.5 0 0 0 1.3-2L14 9V3"/><path d="M7 15h10"/>',
            'shield'    => '<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6z"/>',
            'key'       => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9"/><path d="M16 7l3 3"/>',
            'edit'      => '<path d="M4 20h4L19 9l-4-4L4 16z"/>',
            'trash'     => '<path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 13h10l1-13"/><path d="M9 7V4h6v3"/>',
            'puzzle'    => '<path d="M4 8h4a2 2 0 1 1 4 0h4v4a2 2 0 1 1 0 4v4H4z"/>',
            'sparkle'   => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5L18 18M18 6l-2.5 2.5M8.5 15.5L6 18"/>',
            'link'      => '<path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1"/><path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1"/>',
            'database'  => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
            'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1"/><path d="M9 12l2 2 4-4"/>',
            'tag'       => '<path d="M3 12V4h8l10 10-8 8z"/><path d="M7.5 7.5h.01"/>',
            'filter'    => '<path d="M3 5h18l-7 8v6l-4 2v-8z"/>',
            'sort'      => '<path d="M8 4v16M4 8l4-4 4 4"/><path d="M16 20V4M12 16l4 4 4-4"/>',
            'columns'   => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16M15 4v16"/>',
            'video'     => '<rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3"/>',
        ];

        $path = $paths[$name] ?? $paths['info'];

        return '<svg class="icon' . ($class === '' ? '' : ' ' . esc($class, 'attr')) . '" viewBox="0 0 24 24"'
            . ' width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"'
            . ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . $path . '</svg>';
    }
}

if (! function_exists('app_icon_src')) {
    /**
     * URL of an app icon slot (`app.favicon`, `app.icon-192`, …) with its
     * `?v=` hash, or the static file in git when the slot is empty. Never a
     * package URL: the browser reads icons outside the game.
     */
    function app_icon_src(string $slot, string $fallback): string
    {
        $path = service('contentRepository')->mediaKeyMap()[$slot] ?? null;

        return base_url($path ?? $fallback);
    }
}

if (! function_exists('curtain_attrs')) {
    /**
     * Atribut tautan bertirai (core/curtain.js, initCurtains()): jenis tirai,
     * isi slot teksnya, dan aset yang dipramuat. Tirai jenis itu harus ada di
     * halaman sebagai <template id="tpl-curtain-{kind}">; tanpa template atau
     * tanpa JavaScript tautan berpindah halaman seperti biasa.
     *
     * @param array<string, string> $text    slot → teks, mis. ['title' => 'Menuju Magelang…']
     * @param list<string>          $preload URL aset halaman tujuan
     * @param string|null           $bg      background picture faded in behind the curtain (`data-curtain-bg`)
     */
    function curtain_attrs(string $kind, array $text = [], array $preload = [], ?string $bg = null): string
    {
        $preload = array_values(array_unique(array_filter($preload, static fn ($url): bool => is_string($url) && $url !== '')));
        $html    = ' data-curtain="' . esc($kind, 'attr') . '"';

        if ($text !== []) {
            $html .= ' data-curtain-text="' . esc(json_encode($text, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr') . '"';
        }

        if ($preload !== []) {
            $html .= ' data-curtain-preload="' . esc(json_encode($preload, JSON_UNESCAPED_SLASHES), 'attr') . '"';
        }

        if ($bg !== null && $bg !== '') {
            $html .= ' data-curtain-bg="' . esc($bg, 'attr') . '"';
        }

        return $html;
    }
}

if (! function_exists('region_curtain_attrs')) {
    /**
     * curtain_attrs() of a "Menuju {wilayah}…" link from the `curtain` array of
     * GameProgress::regionCurtain() (text, preload, bg). Empty without it.
     *
     * @param array{text: array<string, string>, preload: list<string>, bg?: string|null}|null $curtain
     */
    function region_curtain_attrs(?array $curtain): string
    {
        return $curtain === null ? '' : curtain_attrs('region', $curtain['text'], $curtain['preload'], $curtain['bg'] ?? null);
    }
}

if (! function_exists('lang_or')) {
    /**
     * lang() dengan cadangan: kunci yang belum diterjemahkan tampil sebagai
     * $fallback (mis. kode mentah dari database), bukan sebagai nama kunci.
     */
    function lang_or(string $key, string $fallback, array $args = []): string
    {
        $text = lang($key, $args);

        return $text === $key || $text === '' ? $fallback : $text;
    }
}

if (! function_exists('engine_label')) {
    /**
     * Nama jenis tantangan untuk pemain. Varian yang punya nama sendiri
     * (kunci `Game.variant_{kode}`) didahulukan dari nama engine.
     */
    function engine_label(string $engine, ?string $variant = null): string
    {
        $base = lang_or('Game.engine_' . $engine, $engine);

        return $variant === null || $variant === '' ? $base : lang_or('Game.variant_' . $variant, $base);
    }
}

if (! function_exists('admin_label')) {
    /**
     * Label panel admin untuk kode yang tersimpan di database — status sesi,
     * alasan selesai, jenis soal, jenis catatan aktivitas, dan sebagainya — dari
     * kelompok `$group` di Language/id/Admin.php. Panel admin selalu
     * berbahasa Indonesia, jadi locale dipatok 'id'. Kode yang belum punya
     * label tampil apa adanya; nilai kosong menjadi tanda pisah.
     */
    function admin_label(string $group, ?string $code): string
    {
        if ($code === null || $code === '') {
            return '—';
        }

        $labels = lang('Admin.' . $group, [], 'id');

        return is_array($labels) && isset($labels[$code]) ? (string) $labels[$code] : $code;
    }
}

if (! function_exists('class_label')) {
    /**
     * Kelas siswa (participants.class_level) → "Kelas 5 SD/MI". Pendaftaran
     * hanya menerima kelas SD (Config\Gelita::$registrationClass). Nilai
     * kosong menjadi tanda pisah.
     */
    function class_label(?string $code, ?string $locale = 'id'): string
    {
        $code = trim((string) $code);

        if ($code === '') {
            return '—';
        }

        return ctype_digit($code) && (int) $code >= 1 && (int) $code <= 6
            ? lang('Game.classSd', [(int) $code], $locale)
            : $code;
    }
}

if (! function_exists('fmt_pct')) {
    /**
     * Angka 0–100 (atau rasio 0–1 bila $ratio) → "73,5%" gaya Indonesia.
     */
    function fmt_pct(float|int|string|null $value, bool $ratio = false, int $decimals = 1): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $number = (float) $value * ($ratio ? 100 : 1);

        return fmt_num($number, $decimals) . '%';
    }
}

if (! function_exists('fmt_num')) {
    /** Angka dengan pemisah sesuai bahasa: "1.234,5" (id) atau "1,234.5" (en). */
    function fmt_num(float|int|string|null $value, int $decimals = 0, ?string $locale = null): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $locale ??= service('request')->getLocale() ?: 'id';

        return $locale === 'en'
            ? number_format((float) $value, $decimals, '.', ',')
            : number_format((float) $value, $decimals, ',', '.');
    }
}

if (! function_exists('fmt_date')) {
    /**
     * Tanggal-waktu database → "22 Sep 2026 19:40". $withSeconds untuk linimasa.
     * Nilai kosong → tanda pisah.
     */
    function fmt_date(mixed $value, bool $withSeconds = false, ?string $locale = null): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $time = $value instanceof DateTimeInterface ? $value->getTimestamp() : strtotime((string) $value);

        if ($time === false) {
            return (string) $value;
        }

        $locale ??= service('request')->getLocale() ?: 'id';
        $months = $locale === 'en'
            ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return date('j', $time) . ' ' . $months[(int) date('n', $time) - 1] . ' '
            . date('Y H:i' . ($withSeconds ? ':s' : ''), $time);
    }
}
