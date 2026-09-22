<?php

namespace App\Models\Traits;

/**
 * Meng-encode kolom JSON sebelum ditulis, sehingga pemanggil boleh mengirim array biasa.
 *
 * Model yang memakainya WAJIB mendeklarasikan sendiri:
 *
 *     protected array $jsonFields = ['config_json'];
 *
 * lalu mendaftarkan callback `encodeJson` / `encodeJsonBatch`.
 * Properti sengaja tidak dideklarasikan di trait: nilai awal yang berbeda
 * antara trait dan kelas pemakainya ditolak PHP sebagai komposisi tak sepadan.
 */
trait EncodesJson
{
    protected function encodeJson(array $data): array
    {
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = $this->encodeJsonRow($data['data']);
        }

        return $data;
    }

    protected function encodeJsonBatch(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        foreach ($data['data'] as $index => $row) {
            if (is_array($row)) {
                $data['data'][$index] = $this->encodeJsonRow($row);
            }
        }

        return $data;
    }

    private function encodeJsonRow(array $row): array
    {
        foreach ($this->jsonFields as $field) {
            if (isset($row[$field]) && (is_array($row[$field]) || is_object($row[$field]))) {
                $row[$field] = json_encode($row[$field], JSON_UNESCAPED_UNICODE);
            }
        }

        return $row;
    }
}
