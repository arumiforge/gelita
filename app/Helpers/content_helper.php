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
