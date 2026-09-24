<?php

/**
 * Helper konten dwibahasa & media. Dimuat global lewat Config\Autoload::$helpers.
 */

if (! function_exists('tr')) {
    /**
     * Ambil $row[$field.'_'.$locale]; bila kosong jatuh ke $field.'_id'.
     * Contoh: tr($node, 'title') → title_en bila locale en dan terisi, selain itu title_id.
     */
    function tr(array|object|null $row, string $field, ?string $locale = null): string
    {
        if ($row === null) {
            return '';
        }

        $data   = is_object($row) ? (method_exists($row, 'toRawArray') ? $row->toRawArray() : (array) $row) : $row;
        $locale ??= service('request')->getLocale() ?: 'id';

        $value = $data[$field . '_' . $locale] ?? null;

        if ($value === null || $value === '') {
            $value = $data[$field . '_id'] ?? '';
        }

        return (string) $value;
    }
}

if (! function_exists('node_ref')) {
    /**
     * Kode tantangan workbook dari kode wilayah + urutan: ('magelang', 4) → 'mgl-4'.
     * Wilayah di luar Config\Gelita::$levelPrefixes memakai kodenya sendiri.
     */
    function node_ref(string $levelCode, int $sequence): string
    {
        $prefix = array_search($levelCode, config('Gelita')->levelPrefixes, true);

        return ($prefix === false ? $levelCode : $prefix) . '-' . $sequence;
    }
}

if (! function_exists('rich_text')) {
    /**
     * Format ringan untuk teks panjang Pustaka Kedu, ditulis admin tanpa HTML:
     *
     *   baris kosong        → paragraf baru
     *   `## Subjudul`       → <h3>
     *   `- butir`           → daftar berpoin
     *   `> Tahukah kamu? …` → kotak fakta (<aside class="book-fact">)
     *   `Sumber: …`         → catatan rujukan kecil
     *   `**tebal**`         → <strong>
     *   `*miring*`          → <em> (istilah asing/daerah)
     *
     * Seluruh teks di-escape LEBIH DULU; tag hanya disusun dari penanda di
     * atas, jadi isi database tidak pernah dapat menyisipkan HTML.
     */
    function rich_text(string $text): string
    {
        $inline = static function (string $line): string {
            $html = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', esc($line)) ?? esc($line);

            // *miring*: bintang tunggal yang menempel pada kata, bukan sisa **tebal**
            return preg_replace('/(?<![*\w])\*(?=\S)([^*]+?)(?<=\S)\*(?![*\w])/u', '<em>$1</em>', $html) ?? $html;
        };

        $html  = '';
        $para  = [];
        $list  = [];
        $quote = [];

        $flush = static function () use (&$html, &$para, &$list, &$quote, $inline): void {
            if ($para !== []) {
                $html .= '<p>' . implode('<br>', array_map($inline, $para)) . '</p>';
                $para = [];
            }

            if ($list !== []) {
                $html .= '<ul>' . implode('', array_map(static fn (string $item): string => '<li>' . $inline($item) . '</li>', $list)) . '</ul>';
                $list = [];
            }

            if ($quote !== []) {
                $html .= '<aside class="book-fact">' . implode('<br>', array_map($inline, $quote)) . '</aside>';
                $quote = [];
            }
        };

        foreach (preg_split('/\R/u', trim($text)) ?: [] as $raw) {
            $line = trim($raw);

            if ($line === '') {
                $flush();

                continue;
            }

            if (preg_match('/^#{2,3}\s+(.+)$/u', $line, $m)) {
                $flush();
                $html .= '<h3>' . $inline($m[1]) . '</h3>';
            } elseif (preg_match('/^[-*•]\s+(.+)$/u', $line, $m)) {
                if ($para !== [] || $quote !== []) {
                    $flush();
                }
                $list[] = $m[1];
            } elseif (preg_match('/^>\s?(.*)$/u', $line, $m)) {
                if ($para !== [] || $list !== []) {
                    $flush();
                }
                $quote[] = $m[1];
            } elseif (preg_match('/^(Sumber|Rujukan|Source|Sources|References?)\s*:/iu', $line)) {
                $flush();
                $html .= '<p class="book-source">' . $inline($line) . '</p>';
            } else {
                if ($list !== [] || $quote !== []) {
                    $flush();
                }
                $para[] = $line;
            }
        }

        $flush();

        return $html;
    }
}

if (! function_exists('media_src')) {
    /**
     * URL media aktif dari peta media ber-cache (ContentRepository::mediaMap()).
     * Bila tidak ada / nonaktif → $fallback, atau assets/ui/placeholder.svg.
     */
    function media_src(?int $mediaAssetId, ?string $fallback = null): string
    {
        $placeholder = base_url($fallback ?? 'assets/ui/placeholder.svg');

        if ($mediaAssetId === null || $mediaAssetId <= 0) {
            return $placeholder;
        }

        $path = service('contentRepository')->mediaMap()[$mediaAssetId] ?? null;

        return $path ? base_url($path) : $placeholder;
    }
}

if (! function_exists('media_exists')) {
    /** Media aktif dengan id ini ada? Dipakai view yang punya tampilan pengganti sendiri. */
    function media_exists(?int $mediaAssetId): bool
    {
        return $mediaAssetId !== null && $mediaAssetId > 0
            && isset(service('contentRepository')->mediaMap()[$mediaAssetId]);
    }
}

if (! function_exists('media_first')) {
    /**
     * URL media aktif pertama dari beberapa kandidat id (mis. latar node, lalu
     * latar wilayah). String kosong bila tidak ada satu pun — layout lalu
     * memakai gradien CSS alih-alih gambar pengganti.
     */
    function media_first(?int ...$mediaAssetIds): string
    {
        foreach ($mediaAssetIds as $id) {
            if (media_exists($id)) {
                return media_src($id);
            }
        }

        return '';
    }
}

if (! function_exists('media_key_src')) {
    /**
     * URL media aktif menurut asset_key resmi (mis. `bg.welcome`, `char.jaka.idle.1`).
     * NULL bila aset belum diunggah/nonaktif — view memilih penggantinya sendiri
     * (gradien CSS untuk latar, monogram untuk karakter) alih-alih kotak rusak.
     */
    function media_key_src(string $assetKey): ?string
    {
        $path = service('contentRepository')->mediaKeyMap()[$assetKey] ?? null;

        return $path ? base_url($path) : null;
    }
}

if (! function_exists('media_key_src_locale')) {
    /**
     * media_key_src() untuk gambar bertulisan yang punya varian bahasa
     * (05_VIEW_UI.md §Kondisi aset hilang): `{kunci}.{locale}` dicoba lebih
     * dulu, lalu kunci dasar (varian Indonesia). `ui.btn-start` + en →
     * `ui.btn-start.en`, bila tidak ada → `ui.btn-start`.
     */
    function media_key_src_locale(string $assetKey, ?string $locale = null): ?string
    {
        $locale ??= service('request')->getLocale() ?: 'id';

        if ($locale !== 'id' && ($localized = media_key_src($assetKey . '.' . $locale)) !== null) {
            return $localized;
        }

        return media_key_src($assetKey);
    }
}

if (! function_exists('media_catalog')) {
    /**
     * Seluruh baris media_assets (aktif maupun belum berberkas) per id, untuk
     * pemilih media di panel admin. Dibaca sekali per request.
     *
     * @return array<int, array<string, mixed>>
     */
    function media_catalog(bool $refresh = false): array
    {
        static $rows = null;

        if ($rows === null || $refresh) {
            $rows = [];

            foreach (model(\App\Models\MediaAssetModel::class)->orderBy('asset_key', 'ASC')->findAll() as $row) {
                $rows[(int) $row['id']] = $row;
            }
        }

        return $rows;
    }
}

if (! function_exists('audio_catalog')) {
    /**
     * Seluruh audio_assets + asset_key, untuk pemilih audio di panel admin.
     *
     * @return array<int, array<string, mixed>>
     */
    function audio_catalog(): array
    {
        static $rows = null;

        if ($rows === null) {
            $rows = [];
            $list = db_connect()->table('audio_assets aa')
                ->select('aa.id, aa.locale, aa.context_code, aa.character_code, aa.approval_status, ma.asset_key')
                ->join('media_assets ma', 'ma.id = aa.media_asset_id')
                ->orderBy('aa.context_code', 'ASC')
                ->orderBy('aa.locale', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($list as $row) {
                $rows[(int) $row['id']] = $row;
            }
        }

        return $rows;
    }
}

if (! function_exists('audio_src')) {
    /**
     * URL audio; NULL bila approval_status != 'approved' atau media nonaktif.
     *
     * `audio_assets` tidak punya kolom `is_active` (lihat migration 001100);
     * status tayang aset audio ditentukan `approval_status` miliknya sendiri
     * dan `is_active` pada `media_assets` induknya.
     */
    function audio_src(?int $audioAssetId): ?string
    {
        if ($audioAssetId === null || $audioAssetId <= 0) {
            return null;
        }

        $row = db_connect()->table('audio_assets aa')
            ->select('ma.storage_path')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->where('aa.id', $audioAssetId)
            ->where('aa.approval_status', 'approved')
            ->where('ma.is_active', 1)
            ->get()
            ->getRow();

        return $row ? base_url($row->storage_path) : null;
    }
}

if (! function_exists('stars_html')) {
    /**
     * Bintang 0..$max sebagai ikon + teks alternatif. Warna bukan satu-satunya
     * penanda: bintang kosong bergaris, bintang penuh terisi, dan pembaca layar
     * membaca "2 / 3".
     */
    function stars_html(int $stars, int $max = 3): string
    {
        if (! function_exists('icon')) {
            helper('ui');
        }

        $max   = max(1, $max);
        $stars = max(0, min($max, $stars));
        $html  = '<span class="stars" role="img" aria-label="' . esc($stars . ' / ' . $max) . '">';

        for ($i = 1; $i <= $max; $i++) {
            $html .= '<span class="star' . ($i <= $stars ? ' is-on' : '') . '">' . icon('star') . '</span>';
        }

        return $html . '</span>';
    }
}
