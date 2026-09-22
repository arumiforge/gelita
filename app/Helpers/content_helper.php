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
    /** Bintang 0..3; ikon + teks alternatif (warna bukan satu-satunya penanda) */
    function stars_html(int $stars): string
    {
        $stars = max(0, min(3, $stars));
        $html  = '<span class="stars" role="img" aria-label="' . esc($stars . ' / 3') . '">';

        for ($i = 1; $i <= 3; $i++) {
            $html .= '<span class="star' . ($i <= $stars ? ' is-on' : '') . '" aria-hidden="true">'
                . ($i <= $stars ? '★' : '☆') . '</span>';
        }

        return $html . '</span>';
    }
}
