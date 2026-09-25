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
    public function store(UploadedFile $file, string $assetKey, array $allowedTypes = ['image', 'video'], ?int $staffId = null, bool $quiet = false): array
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

        if (in_array('audio', $allowedTypes, true)) {
            $mime = $this->detectMime($file->getTempName(), $mime);
        }

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

        $existing = model(MediaAssetModel::class)->findByKey($assetKey);
        $stored   = $this->moveWithOfficialName($file, $assetKey, $type === 'audio' ? $this->extensionFor($mime, $file->getClientExtension()) : null);

        if ($stored === null) {
            return $this->fail('Berkas gagal disimpan.');
        }

        return $this->commit($existing, $assetKey, $type, $stored, $mime, is_array($size) ? $size : null, $staffId, 'upload', $quiet);
    }

    /**
     * Simpan gambar yang diunduh server (bukan unggahan browser) sebagai
     * `asset_key`, mis. thumbnail video Pustaka (VideoThumbnail). Aturannya
     * sama dengan store(): jenis dibaca dari isi berkas, bukan dari header
     * penyedia; hanya gambar raster yang dapat dibaca getimagesize() (SVG
     * dari luar tidak pernah diterima); batas ukuran unggahan tetap berlaku.
     *
     * @return array{id: int|null, type: string|null, error: string|null}
     */
    public function storeBytes(string $bytes, string $assetKey, ?int $staffId = null, string $via = 'download'): array
    {
        $assetKey = trim($assetKey);

        if ($assetKey === '' || mb_strlen($assetKey) > 160) {
            return $this->fail('asset_key wajib diisi, maksimal 160 karakter.');
        }

        if ($bytes === '' || strlen($bytes) > config('Gelita')->maxUploadBytes) {
            return $this->fail('Berkas kosong atau melebihi batas unggahan.');
        }

        $size = @getimagesizefromstring($bytes);
        $mime = is_array($size) ? (string) ($size['mime'] ?? '') : '';

        if (! is_array($size) || $mime === 'image/svg+xml' || $this->typeForMime($mime) !== 'image') {
            return $this->fail('Berkas bukan gambar yang dapat dibaca.');
        }

        $dir = FCPATH . self::UPLOAD_DIR;

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return $this->fail('Berkas gagal disimpan.');
        }

        $extension = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? 'bin';
        $name      = preg_replace('/[^a-z0-9._-]+/i', '-', $assetKey) . '.' . $extension;

        if (file_put_contents($dir . $name, $bytes, LOCK_EX) !== strlen($bytes)) {
            @unlink($dir . $name);

            return $this->fail('Berkas gagal disimpan.');
        }

        $existing = model(MediaAssetModel::class)->findByKey($assetKey);

        return $this->commit($existing, $assetKey, 'image', self::UPLOAD_DIR . $name, $mime, [(int) $size[0], (int) $size[1]], $staffId, $via);
    }

    /**
     * Daftarkan berkas yang sudah ada di bawah public/ apa adanya, tanpa
     * dipindah, mis. rekaman narasi yang disalin operator ke
     * public/assets/audio/narasi/{id|en}/ (NarrationImporter). storage_path
     * menjadi $relativePath; aturan jenis, batas ukuran, dan ukuran wajib
     * gambar sama dengan store().
     *
     * $quiet = true: cache konten tidak di-flush dan tidak ada jejak audit per
     * berkas; pemanggil yang memproses banyak berkas melakukannya sekali.
     *
     * @param list<string> $allowedTypes image | video | audio
     *
     * @return array{id: int|null, type: string|null, error: string|null}
     */
    public function registerFile(string $relativePath, string $assetKey, array $allowedTypes = ['audio'], ?int $staffId = null, bool $quiet = false, ?string $locale = null): array
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $inspected    = $this->inspect(FCPATH . $relativePath, $assetKey, $allowedTypes);

        if (is_string($inspected)) {
            return $this->fail($inspected);
        }

        $existing = model(MediaAssetModel::class)->findByKey(trim($assetKey));

        return $this->commit($existing, trim($assetKey), $inspected['type'], $relativePath, $inspected['mime'], $inspected['size'], $staffId, 'folder', $quiet, $locale);
    }

    /**
     * Salin berkas lokal (mis. berkas sementara unggahan yang sudah diperiksa
     * is_uploaded_file() oleh controller) ke public/assets/uploads/ dengan
     * nama resmi turunan `asset_key`. Dipakai unggahan banyak berkas sekaligus,
     * yang tidak dapat memakai store() karena berkasnya diproses di luar
     * objek UploadedFile.
     *
     * @param list<string> $allowedTypes image | video | audio
     *
     * @return array{id: int|null, type: string|null, error: string|null}
     */
    public function storeCopy(string $sourcePath, string $assetKey, array $allowedTypes = ['audio'], ?int $staffId = null, bool $quiet = false, ?string $locale = null): array
    {
        $inspected = $this->inspect($sourcePath, $assetKey, $allowedTypes);

        if (is_string($inspected)) {
            return $this->fail($inspected);
        }

        $dir = FCPATH . self::UPLOAD_DIR;

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return $this->fail('Berkas gagal disimpan.');
        }

        $assetKey  = trim($assetKey);
        $extension = $this->extensionFor($inspected['mime'], (string) pathinfo($sourcePath, PATHINFO_EXTENSION));
        $name      = preg_replace('/[^a-z0-9._-]+/i', '-', $assetKey) . '.' . $extension;

        if (! @copy($sourcePath, $dir . $name)) {
            return $this->fail('Berkas gagal disimpan.');
        }

        $existing = model(MediaAssetModel::class)->findByKey($assetKey);

        return $this->commit($existing, $assetKey, $inspected['type'], self::UPLOAD_DIR . $name, $inspected['mime'], $inspected['size'], $staffId, 'upload', $quiet, $locale);
    }

    /**
     * Jenis MIME berkas lokal dari isinya. finfo tidak selalu mengenali audio
     * (MP3 tanpa tag ID3 terbaca application/octet-stream, M4A terbaca
     * video/mp4), jadi bila hasil finfo bukan audio, tanda tangan awal berkas
     * diperiksa: ID3 / sinkronisasi bingkai MPEG, OggS, RIFF…WAVE, dan ftyp
     * M4A. Ekstensi nama berkas tidak pernah dipercaya sendirian.
     */
    public function detectMime(string $path, ?string $reported = null): string
    {
        $mime = $reported ?? '';

        if ($mime === '' && is_file($path)) {
            $mime = (string) (@mime_content_type($path) ?: '');
        }

        if ($this->typeForMime($mime) === 'audio') {
            return $mime;
        }

        return $this->sniffAudio($path) ?? $mime;
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
     * tanpa dependensi tambahan. MP3 dibaca dari bingkai pertamanya
     * (mp3DurationMs). OGG dan M4A butuh pembacaan kontainer; selama itu
     * belum ada, durasinya dibiarkan NULL — bukan ditebak.
     */
    public function durationMs(string $path): ?int
    {
        if (! is_file($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'mp3') {
            return $this->mp3DurationMs($path);
        }

        if ($extension !== 'wav') {
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

    /**
     * Durasi MP3 dari header bingkai pertama (setelah tag ID3v2).
     *
     * - Header Xing/Info (VBR maupun CBR dari LAME) atau VBRI menyimpan jumlah
     *   bingkai: durasi = bingkai × sampel per bingkai ÷ laju sampel.
     * - Tanpa keduanya, berkas dianggap CBR: durasi = ukuran data × 8 ÷ bitrate.
     *
     * Header yang tidak terbaca menghasilkan NULL, bukan tebakan.
     */
    public function mp3DurationMs(string $path): ?int
    {
        $size = @filesize($path);
        $fh   = @fopen($path, 'rb');

        if ($fh === false || ! $size) {
            return null;
        }

        $head   = (string) fread($fh, 10);
        $offset = 0;

        if (strlen($head) === 10 && str_starts_with($head, 'ID3')) {
            // Ukuran tag ID3v2: 4 byte syncsafe (7 bit per byte), + footer bila ada
            $offset = 10 + ((ord($head[6]) & 0x7F) << 21 | (ord($head[7]) & 0x7F) << 14 | (ord($head[8]) & 0x7F) << 7 | (ord($head[9]) & 0x7F));
            $offset += (ord($head[5]) & 0x10) ? 10 : 0;
        }

        fseek($fh, $offset);
        $window = (string) fread($fh, 16384);
        fclose($fh);

        $bitrates = [
            // [versi MPEG 1][layer 3], [versi MPEG 2/2.5][layer 3]
            1 => [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320],
            2 => [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160],
        ];
        $rates = [3 => [44100, 48000, 32000], 2 => [22050, 24000, 16000], 0 => [11025, 12000, 8000]];

        for ($i = 0, $n = strlen($window) - 4; $i < $n; $i++) {
            if (ord($window[$i]) !== 0xFF || (ord($window[$i + 1]) & 0xE0) !== 0xE0) {
                continue;
            }

            $b1      = ord($window[$i + 1]);
            $b2      = ord($window[$i + 2]);
            $b3      = ord($window[$i + 3]);
            $version = ($b1 >> 3) & 0x03;   // 3 = MPEG1, 2 = MPEG2, 0 = MPEG2.5
            $layer   = ($b1 >> 1) & 0x03;   // 1 = Layer III
            $bIndex  = ($b2 >> 4) & 0x0F;
            $rIndex  = ($b2 >> 2) & 0x03;

            if ($version === 1 || $layer !== 1 || $bIndex === 0 || $bIndex === 15 || $rIndex === 3) {
                continue;
            }

            $kbps       = $bitrates[$version === 3 ? 1 : 2][$bIndex];
            $sampleRate = $rates[$version][$rIndex];
            $samples    = $version === 3 ? 1152 : 576;
            $mono       = (($b3 >> 6) & 0x03) === 3;
            $sideInfo   = $version === 3 ? ($mono ? 17 : 32) : ($mono ? 9 : 17);
            $tag        = substr($window, $i + 4 + $sideInfo, 4);

            if (($tag === 'Xing' || $tag === 'Info') && strlen($window) >= $i + 4 + $sideInfo + 12) {
                $flags = unpack('N', substr($window, $i + 4 + $sideInfo + 4, 4))[1];

                if ($flags & 0x01) {
                    $frames = unpack('N', substr($window, $i + 4 + $sideInfo + 8, 4))[1];

                    return $frames > 0 ? (int) round($frames * $samples / $sampleRate * 1000) : null;
                }
            }

            if (substr($window, $i + 36, 4) === 'VBRI' && strlen($window) >= $i + 36 + 18) {
                $frames = unpack('N', substr($window, $i + 36 + 14, 4))[1];

                return $frames > 0 ? (int) round($frames * $samples / $sampleRate * 1000) : null;
            }

            $dataBytes = $size - $offset - $i;

            return $dataBytes > 0 ? (int) round($dataBytes * 8 / ($kbps * 1000) * 1000) : null;
        }

        return null;
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
    private function moveWithOfficialName(UploadedFile $file, string $assetKey, ?string $extension = null): ?string
    {
        $dir = FCPATH . self::UPLOAD_DIR;

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return null;
        }

        $extension ??= strtolower($file->guessExtension() ?: $file->getExtension() ?: 'bin');
        $name      = preg_replace('/[^a-z0-9._-]+/i', '-', $assetKey) . '.' . $extension;
        $target    = $dir . $name;

        if (is_file($target)) {
            unlink($target);
        }

        $file->move($dir, $name, true);

        return is_file($target) ? self::UPLOAD_DIR . $name : null;
    }

    /**
     * Langkah akhir store()/storeBytes(): baris `media_assets`, berkas lama
     * berekstensi lain, cache konten, dan jejak audit.
     *
     * @param array<string, mixed>|null  $existing
     * @param array{0: int, 1: int}|null $size
     *
     * @return array{id: int|null, type: string|null, error: string|null}
     */
    private function commit(?array $existing, string $assetKey, string $type, string $stored, string $mime, ?array $size, ?int $staffId, string $via = 'upload', bool $quiet = false, ?string $locale = null): array
    {
        $mediaId = $this->upsert($existing, $assetKey, $type, $stored, $mime, $size, $locale);

        if ($mediaId === null) {
            return $this->fail('Aset ditolak: ' . implode(' ', model(MediaAssetModel::class)->errors()));
        }

        // Berkas lama berekstensi lain (mis. .png diganti .jpg) tidak dibiarkan menjadi yatim
        if ($existing !== null && $existing['storage_path'] !== $stored
            && str_starts_with((string) $existing['storage_path'], self::UPLOAD_DIR)
            && is_file(FCPATH . $existing['storage_path'])) {
            @unlink(FCPATH . $existing['storage_path']);
        }

        if ($quiet) {
            return ['id' => $mediaId, 'type' => $type, 'error' => null];
        }

        service('contentRepository')->flush();

        model(AuditLogModel::class)->record('media_upload', [
            'staff_user_id' => $staffId,
            'target_type'   => 'media_asset',
            'target_id'     => (string) $mediaId,
            'metadata'      => ['asset_key' => $assetKey, 'mime' => $mime] + ($via !== 'upload' ? ['via' => $via] : []),
        ]);

        return ['id' => $mediaId, 'type' => $type, 'error' => null];
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
    private function upsert(?array $existing, string $assetKey, string $type, string $storagePath, string $mime, ?array $size, ?string $locale = null): ?int
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
            'locale'       => $locale ?? self::localeFromKey($assetKey),
            'is_active'    => 1,
        ];

        if ($existing === null) {
            $inserted = $media->insert($payload + ['asset_key' => $assetKey], true);

            return $inserted === false ? null : (int) $inserted;
        }

        return $media->update((int) $existing['id'], $payload) ? (int) $existing['id'] : null;
    }

    /**
     * Varian bahasa gambar bertulisan ditandai akhiran kunci `.en` / `-en`
     * (mis. `ui.btn-start.en`), sama dengan aturan MediaAssetSeeder untuk
     * nama berkas. Kunci tanpa akhiran berlaku untuk semua bahasa (NULL).
     */
    public static function localeFromKey(string $assetKey): ?string
    {
        return preg_match('/[-_.](id|en)$/', strtolower(trim($assetKey)), $m) ? $m[1] : null;
    }

    /**
     * Pemeriksaan bersama registerFile()/storeCopy(): kunci, keberadaan dan
     * ukuran berkas, jenis dari isi berkas, serta ukuran wajib gambar.
     *
     * @param list<string> $allowedTypes
     *
     * @return array{mime: string, type: string, size: array{0: int, 1: int}|null}|string pesan galat
     */
    private function inspect(string $path, string $assetKey, array $allowedTypes): array|string
    {
        $assetKey = trim($assetKey);

        if ($assetKey === '' || mb_strlen($assetKey) > 160) {
            return 'asset_key wajib diisi, maksimal 160 karakter.';
        }

        if (! is_file($path)) {
            return 'Berkas tidak ditemukan.';
        }

        $bytes = (int) filesize($path);

        if ($bytes === 0 || $bytes > config('Gelita')->maxUploadBytes) {
            return sprintf('Berkas %s kosong atau melebihi batas %d MB.', basename($path), (int) (config('Gelita')->maxUploadBytes / 1048576));
        }

        $mime = $this->detectMime($path);
        $type = $this->typeForMime($mime);

        if ($type === null || ! in_array($type, $allowedTypes, true)) {
            return sprintf('Jenis berkas %s tidak diizinkan di sini (hanya %s).', $mime === '' ? 'tak dikenal' : $mime, implode(', ', $allowedTypes));
        }

        $size = null;

        if ($type === 'image') {
            $read = @getimagesize($path);

            if ($read === false && $mime !== 'image/svg+xml') {
                return 'Berkas bukan gambar yang dapat dibaca.';
            }

            $size     = is_array($read) ? [(int) $read[0], (int) $read[1]] : null;
            $required = $this->requiredSize($assetKey);

            if ($required !== null && $size !== null && $size !== $required) {
                return sprintf('Ukuran gambar %d×%d tidak sesuai ketentuan %d×%d untuk %s.', $size[0], $size[1], $required[0], $required[1], $assetKey);
            }
        }

        return ['mime' => $mime, 'type' => $type, 'size' => $size];
    }

    /** Tanda tangan awal berkas audio → MIME, atau null bila bukan audio yang dikenal. */
    private function sniffAudio(string $path): ?string
    {
        $head = is_file($path) ? (string) @file_get_contents($path, false, null, 0, 12) : '';

        return match (true) {
            strlen($head) < 4                                                       => null,
            str_starts_with($head, 'ID3'),
            ord($head[0]) === 0xFF && (ord($head[1]) & 0xE6) === 0xE2               => 'audio/mpeg',
            str_starts_with($head, 'OggS')                                          => 'audio/ogg',
            str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WAVE'        => 'audio/wav',
            substr($head, 4, 4) === 'ftyp' && in_array(substr($head, 8, 4), ['M4A ', 'M4B '], true) => 'audio/mp4',
            default                                                                 => null,
        };
    }

    /** Ekstensi berkas audio dari MIME-nya; jenis lain memakai ekstensi yang diberikan. */
    private function extensionFor(string $mime, string $fallback): string
    {
        return [
            'audio/mpeg'  => 'mp3',
            'audio/ogg'   => 'ogg',
            'audio/wav'   => 'wav',
            'audio/x-wav' => 'wav',
            'audio/wave'  => 'wav',
            'audio/mp4'   => 'm4a',
            'audio/x-m4a' => 'm4a',
        ][$mime] ?? (strtolower(preg_replace('/[^a-z0-9]+/i', '', $fallback) ?? '') ?: 'bin');
    }

    /** @return array{id: null, type: null, error: string} */
    private function fail(string $message): array
    {
        return ['id' => null, 'type' => null, 'error' => $message];
    }
}
