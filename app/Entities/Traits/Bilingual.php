<?php

namespace App\Entities\Traits;

/**
 * Akses teks dwibahasa pada Entity konten.
 * Kolom disimpan berpasangan: `<field>_id` (Indonesia) dan `<field>_en` (Inggris).
 */
trait Bilingual
{
    /**
     * Mengambil nilai dwibahasa. Bila versi locale kosong, jatuh ke Indonesia.
     * text('title') → title_en bila locale 'en' dan terisi, selain itu title_id.
     */
    public function text(string $field, ?string $locale = null): string
    {
        $locale = $locale ?: (service('request')->getLocale() ?: 'id');
        $value  = (string) ($this->attributes[$field . '_' . $locale] ?? '');

        if (trim($value) === '') {
            $value = (string) ($this->attributes[$field . '_id'] ?? '');
        }

        return $value;
    }
}
