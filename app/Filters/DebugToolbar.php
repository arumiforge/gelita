<?php

namespace App\Filters;

use CodeIgniter\Filters\DebugToolbar as BaseDebugToolbar;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Debug Toolbar (hanya aktif bila CI_DEBUG, yaitu environment development)
 * yang tidak pernah menyimpan kata sandi.
 *
 * Toolbar bawaan menyalin seluruh `$request->getPost()` ke
 * `writable/debugbar/*.json` tanpa syarat — `toolbar.collectVarData` hanya
 * mengatur data view, bukan isi formulir. Tanpa filter ini, setiap kiriman
 * `/daftar`, `/masuk`, `/ganti-sandi`, dan `/admin/login` meninggalkan kata
 * sandi mentah di disk (melanggar aturan 17 di 01_DATABASE.md dan aturan 10
 * di 02_PROJECT_FOUNDATION.md). Nilai field kredensial disamarkan sebelum
 * toolbar mengambil potret request. Controller sudah selesai berjalan saat
 * after-filter ini dipanggil, jadi penyamaran tidak memengaruhi logika apa pun.
 */
class DebugToolbar extends BaseDebugToolbar
{
    /** Nama field yang memuat kredensial, dicocokkan tanpa peduli huruf besar-kecil. */
    private const SECRET_FIELD = '/pass|sandi|secret|token/i';

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($request instanceof IncomingRequest) {
            $post = $request->getPost();

            if (is_array($post) && $post !== []) {
                $request->setGlobal('post', $this->redact($post));
            }
        }

        return parent::after($request, $response, $arguments);
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    private function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redact($value);
            } elseif (preg_match(self::SECRET_FIELD, (string) $key) === 1) {
                $data[$key] = '[disamarkan]';
            }
        }

        return $data;
    }
}
