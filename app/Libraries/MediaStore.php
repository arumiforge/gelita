<?php

namespace App\Libraries;

use App\Models\AuditLogModel;
use App\Models\MediaAssetModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Penyimpanan berkas media bersama untuk halaman Media dan seluruh editor
 * konten (tantangan, butir, opsi, wilayah, bacaan, dialog, pustaka).
 *
 * Aturannya satu: berkas disimpan dengan nama resmi turunan `asset_key` di
 * public/assets/uploads/, dimensi gambar dibaca ulang server dengan
 * getimagesize() (angka dari browser tidak dipercaya), slot berukuran wajib
 * (Config\Gelita::$assetSizes) ditolak bila ukurannya lain, lalu baris
 * `media_assets` dibuat atau diperbarui.
 */
class MediaStore
{
    public const UPLOAD_DIR = 'assets/uploads/';

    public const IMAGE_MIMES = ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'];

    public const AUDIO_MIMES = ['audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/wave', 'audio/mp4', 'audio/x-m4a'];

    public const VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/ogg'];

    /** Nilai atribut `accept` input berkas per jenis aset. */
    public const ACCEPT = [
        'image' => 'image/png,image/jpeg,image/webp,image/gif,image/svg+xml',
        'video' => 'video/mp4,video/webm,video/ogg',
        'audio' => 'audio/mpeg,audio/ogg,audio/wav,audio/mp4',
    ];

    /**
     * Simpan satu berkas unggahan sebagai `asset_key`.
     *
     * @param list<string> $allowedTypes image | video | audio
     *
     * @return array{id: int|null, type: string|null, error: string|null}
     */
    public function store(UploadedFile $file, string $assetKey, array $allowedTypes = ['image', 'video'], ?int $staffId = null): array
    {
        $assetKey = trim($assetKey);

        if ($assetKey === '' || mb_strlen($assetKey) > 160) {
            return $this->fail('asset_key wajib diisi, maksimal 160 karakter.');
        }

        if (! $file->isValid()) {
            return $this->fail('Berkas unggahan tidak valid: ' . $file->getErrorString());
        }

        if ($file->getSize() > config('Gelita')->maxUploadBytes) {
            return $this->fail(sprintf('Berkas %s melebihi batas %d MB.', $file->getClientName(), (int) (config('Gelita')->maxUploadBytes / 1048576)));
        }

        $mime = (string) $file->getMimeType();
        $type = $this->typeForMime($mime);

        if ($type === null || ! in_array($type, $allowedTypes, true)) {
            return $this->fail(sprintf(
                'Jenis berkas %s tidak diizinkan di sini (hanya %s).',
                $mime,
                implode(', ', $allowedTypes),
            ));
        }

        $size = $type === 'image' ? @getimagesize($file->getTempName()) : false;

        if ($type === 'image' && $size === false && $mime !== 'image/svg+xml') {
            return $this->fail('Berkas bukan gambar yang dapat dibaca.');
        }

        $required = $this->requiredSize($assetKey);

        if ($required !== null && is_array($size)
            && ((int) $size[0] !== $required[0] || (int) $size[1] !== $required[1])) {
            return $this->fail(sprintf(
                'Ukuran gambar %d×%d tidak sesuai ketentuan %d×%d untuk %s.',
                $size[0],
                $size[1],
                $required[0],
                $required[1],
                $assetKey,
            ));
        }

        $media    = model(MediaAssetModel::class);
        $existing = $media->findByKey($assetKey);
        $stored   = $this->moveWithOfficialName($file, $assetKey);

        if ($stored === null) {
            return $this->fail('Berkas gagal disimpan.');
        }

        $mediaId = $this->upsert($existing, $assetKey, $type, $stored, $mime, is_array($size) ? $size : null);

        if ($mediaId === null) {
            return $this->fail('Aset ditolak: ' . implode(' ', $media->errors()));
        }

        // Berkas lama berekstensi lain (mis. .png diganti .jpg) tidak dibiarkan menjadi yatim
        if ($existing !== null && $existing['storage_path'] !== $stored
            && str_starts_with((string) $existing['storage_path'], self::UPLOAD_DIR)
            && is_file(FCPATH . $existing['storage_path'])) {
            @unlink(FCPATH . $existing['storage_path']);
        }

        service('contentRepository')->flush();

        model(AuditLogModel::class)->record('media_upload', [
            'staff_user_id' => $staffId,
            'target_type'   => 'media_asset',
            'target_id'     => (string) $mediaId,
            'metadata'      => ['asset_key' => $assetKey, 'mime' => $mime],
        ]);

        return ['id' => $mediaId, 'type' => $type, 'error' => null];
    }

    /**
     * Isi satu kolom media dari form editor konten: teks `asset_key` (aset
     * yang sudah ada) dan/atau berkas baru.
     *
     * - Ada berkas  → disimpan sebagai asset_key yang diketik, atau $defaultKey
     *                 bila kotak kunci kosong. Kunci yang sudah ada diganti
     *                 berkasnya — sama dengan mengunggah ulang di halaman Media.
     * - Tanpa berkas, kunci terisi → harus aset terdaftar dengan jenis sesuai.
     * - Keduanya kosong → media dilepas (NULL).
     *
     * @param list<string> $allowedTypes
     *
     * @return array{id: int|null, error: string|null}
     */
    public function resolveField(
        IncomingRequest $request,
        string $keyField,
        string $fileField,
        string $defaultKey,
        array $allowedTypes = ['image'],
        ?int $staffId = null,
    ): array {
        return $this->resolve(
            (string) ($request->getPost($keyField) ?? ''),
            $request->getFile($fileField),
            $defaultKey,
            $allowedTypes,
            $staffId,
        );
    }

    /**
     * resolveField() untuk nilai yang sudah diambil pemanggil (baris berulang).
     *
     * @param list<string> $allowedTypes
     *
     * @return array{id: int|null, error: string|null}
     */
    public function resolve(string $typed, ?UploadedFile $file, string $defaultKey, array $allowedTypes = ['image'], ?int $staffId = null): array
    {
        $typed = trim($typed);

        if ($file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $result = $this->store($file, $typed !== '' ? $typed : $defaultKey, $allowedTypes, $staffId);

            return ['id' => $result['id'], 'error' => $result['error']];
        }

        return $this->resolveKey($typed, $allowedTypes);
    }

    /**
     * asset_key → id aset aktif maupun nonaktif; '' → null.
     *
     * @param list<string> $allowedTypes
     *
     * @return array{id: int|null, error: string|null}
     */
    public function resolveKey(string $assetKey, array $allowedTypes = ['image']): array
    {
        $assetKey = trim($assetKey);

        if ($assetKey === '') {
            return ['id' => null, 'error' => null];
        }

        $row = model(MediaAssetModel::class)->findByKey($assetKey);

        if ($row === null) {
            return $this->fail("asset_key '{$assetKey}' belum terdaftar. Pilih dari daftar atau unggah berkasnya.");
        }

        $type = $row['asset_type'] === 'sprite_frame' ? 'image' : (string) $row['asset_type'];

        if (! in_array($type, $allowedTypes, true)) {
            return $this->fail("Aset '{$assetKey}' berjenis {$type}; di sini hanya " . implode(', ', $allowedTypes) . '.');
        }

        return ['id' => (int) $row['id'], 'error' => null];
    }

    public function typeForMime(string $mime): ?string
    {
        return match (true) {
            in_array($mime, self::IMAGE_MIMES, true) => 'image',
            in_array($mime, self::AUDIO_MIMES, true) => 'audio',
            in_array($mime, self::VIDEO_MIMES, true) => 'video',
            default                                  => null,
        };
    }

    /**
     * Ukuran wajib slot aset; pola `bg.*` dicocokkan dengan fnmatch().
     *
     * @return array{0: int, 1: int}|null
     */
    public function requiredSize(string $assetKey): ?array
    {
        foreach (config('Gelita')->assetSizes as $pattern => $size) {
            if ($pattern === $assetKey || fnmatch($pattern, $assetKey)) {
                return $size;
            }
        }

        return null;
    }

    /**
     * Durasi audio dibaca server-side dari header berkas.
     *
     * WAV PCM menyimpan bitrate di header `fmt `, jadi durasinya dapat dihitung
     * tanpa dependensi tambahan. Format terkompresi (MP3/OGG/M4A) butuh
     * pembacaan bingkai; selama itu belum ada, durasinya dibiarkan NULL —
     * bukan ditebak.
     */
    public function durationMs(string $path): ?int
    {
        if (! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'wav') {
            return null;
        }

        $header = file_get_contents($path, false, null, 0, 44);

        if ($header === false || strlen($header) < 44 || substr($header, 0, 4) !== 'RIFF') {
            return null;
        }

        $fmt  = unpack('VbyteRate', substr($header, 28, 4));
        $data = unpack('Vsize', substr($header, 40, 4));

        if (empty($fmt['byteRate'])) {
            return null;
        }

        return (int) round(($data['size'] ?? 0) / $fmt['byteRate'] * 1000);
    }

    /** Potongan aman untuk asset_key: huruf kecil, angka, titik, minus. */
    public static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9.-]+/', '-', $value) ?? '';

        return trim($value, '-.');
    }

    // -------------------------------------------------------------- bantuan

    /**
     * Simpan ke public/assets/uploads/{asset_key}.{ext} dengan nama resmi.
     *
     * @return string|null storage_path relatif terhadap public/
     */
    private function moveWithOfficialName(UploadedFile $file, string $assetKey): ?string
    {
        $dir = FCPATH . self::UPLOAD_DIR;

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return null;
        }

        $extension = strtolower($file->guessExtension() ?: $file->getExtension() ?: 'bin');
        $name      = preg_replace('/[^a-z0-9._-]+/i', '-', $assetKey) . '.' . $extension;
        $target    = $dir . $name;

        if (is_file($target)) {
            unlink($target);
        }

        $file->move($dir, $name, true);

        return is_file($target) ? self::UPLOAD_DIR . $name : null;
    }

    /**
     * Menyimpan atau memperbarui baris `media_assets`.
     *
     * `asset_key` hanya dikirim saat menambah: pada update, aturan
     * `is_unique[...,id,{id}]` milik model tidak dapat mengecualikan baris yang
     * sedang disunting (data update tidak memuat kolom id), sehingga aset yang
     * diunggah ulang akan selalu ditolak sebagai duplikat dirinya sendiri.
     *
     * @param array<string, mixed>|null  $existing
     * @param array{0: int, 1: int}|null $size
     */
    private function upsert(?array $existing, string $assetKey, string $type, string $storagePath, string $mime, ?array $size): ?int
    {
        $media    = model(MediaAssetModel::class);
        $absolute = FCPATH . $storagePath;

        // Frame karakter tetap berjenis sprite_frame saat berkasnya diganti
        if ($existing !== null && $existing['asset_type'] === 'sprite_frame' && $type === 'image') {
            $type = 'sprite_frame';
        }

        $payload = [
            'asset_type'   => $type,
            'storage_path' => $storagePath,
            'mime_type'    => $mime,
            'file_size'    => is_file($absolute) ? filesize($absolute) : null,
            'sha256'       => is_file($absolute) ? hash_file('sha256', $absolute) : null,
            'width_px'     => $size[0] ?? null,
            'height_px'    => $size[1] ?? null,
            'is_active'    => 1,
        ];

        if ($existing === null) {
            $inserted = $media->insert($payload + ['asset_key' => $assetKey], true);

            return $inserted === false ? null : (int) $inserted;
        }

        return $media->update((int) $existing['id'], $payload) ? (int) $existing['id'] : null;
    }

    /** @return array{id: null, type: null, error: string} */
    private function fail(string $message): array
    {
        return ['id' => null, 'type' => null, 'error' => $message];
    }
}
