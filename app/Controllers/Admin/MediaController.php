<?php

namespace App\Controllers\Admin;

use App\Libraries\MediaIntegrity;
use App\Models\AudioAssetModel;
use App\Models\AuditLogModel;
use App\Models\MediaAssetModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Aset gambar/video dan aset audio beserta persetujuannya.
 *
 * Dimensi yang dilaporkan browser tidak dipercaya: server membaca ulang
 * ukuran berkas dengan getimagesize(). Berkas disimpan dengan nama resmi
 * turunan `asset_key`, bukan nama asli unggahan.
 */
class MediaController extends BaseAdminController
{
    private const UPLOAD_DIR = 'assets/uploads/';

    private const IMAGE_MIMES = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];

    private const AUDIO_MIMES = ['audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/mp4'];

    private const VIDEO_MIMES = ['video/mp4', 'video/webm'];

    public function index(): string
    {
        return $this->panel('admin/media/index', 'Media', [
            'assets'     => model(MediaAssetModel::class)->orderBy('asset_key', 'ASC')->findAll(),
            'assetSizes' => config('Gelita')->assetSizes,
            'scan'       => session('media_scan'),
        ]);
    }

    public function upload(): RedirectResponse
    {
        $back = 'admin/media';

        if (! $this->validate([
            'asset_key' => 'required|max_length[160]',
            'file'      => 'uploaded[file]|max_size[file,65536]',
        ])) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $file     = $this->request->getFile('file');
        $assetKey = trim((string) $this->request->getPost('asset_key'));

        if ($file === null || ! $file->isValid()) {
            return $this->back($back, 'Berkas unggahan tidak valid.');
        }

        $mime = (string) $file->getMimeType();
        $type = $this->assetTypeFor($mime);

        if ($type === null) {
            return $this->back($back, "Jenis berkas {$mime} tidak diizinkan.");
        }

        // ukuran wajib per slot aset; getimagesize() memeriksa ulang di server
        $required = $this->requiredSize($assetKey);
        $size     = $type === 'image' ? @getimagesize($file->getTempName()) : false;

        if ($type === 'image' && $size === false && $mime !== 'image/svg+xml') {
            return $this->back($back, 'Berkas bukan gambar yang dapat dibaca.');
        }

        if ($required !== null && is_array($size)
            && ((int) $size[0] !== $required[0] || (int) $size[1] !== $required[1])) {
            return $this->back($back, sprintf(
                'Ukuran gambar %d×%d tidak sesuai ketentuan %d×%d untuk %s.',
                $size[0],
                $size[1],
                $required[0],
                $required[1],
                $assetKey,
            ));
        }

        $stored = $this->storeWithOfficialName($file, $assetKey);

        if ($stored === null) {
            return $this->back($back, 'Berkas gagal disimpan.');
        }

        $mediaId = $this->upsertAsset($assetKey, $type, $stored, $mime, is_array($size) ? $size : null);

        if ($mediaId === null) {
            return $this->back($back, 'Aset ditolak: ' . $this->modelErrors(model(MediaAssetModel::class)));
        }

        service('contentRepository')->flush();

        model(AuditLogModel::class)->record('media_upload', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'media_asset',
            'target_id'     => (string) $mediaId,
            'metadata'      => ['asset_key' => $assetKey, 'mime' => $mime],
        ]);

        return $this->done($back, "Aset {$assetKey} tersimpan.");
    }

    public function deactivate(int $mediaId): RedirectResponse
    {
        $media = model(MediaAssetModel::class);

        if ($media->find($mediaId) === null) {
            return $this->back('admin/media', 'Aset tidak ditemukan.');
        }

        $media->update($mediaId, ['is_active' => 0]);
        service('contentRepository')->flush();

        model(AuditLogModel::class)->record('media_deactivate', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'media_asset',
            'target_id'     => (string) $mediaId,
        ]);

        return $this->done('admin/media', 'Aset dinonaktifkan.');
    }

    public function audioIndex(): string
    {
        $rows = db_connect()->table('audio_assets aa')
            ->select('aa.*, ma.asset_key, ma.storage_path, ma.is_active AS media_active')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->orderBy('aa.context_code', 'ASC')
            ->orderBy('aa.locale', 'ASC')
            ->get()
            ->getResultArray();

        return $this->panel('admin/media/audio', 'Audio', [
            'rows'       => $rows,
            'characters' => config('Gelita')->characters,
            'locales'    => config('Gelita')->locales,
        ]);
    }

    /** Audio baru selalu berstatus `draft`: belum terdengar oleh pemain. */
    public function uploadAudio(): RedirectResponse
    {
        $back = 'admin/media/audio';

        $rules = [
            'asset_key'   => 'required|max_length[160]',
            'locale'      => 'required|valid_locale',
            'context_code' => 'required|max_length[80]',
            'transcript'  => 'required|min_length[3]',
            'file'        => 'uploaded[file]|max_size[file,65536]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');

        if ($file === null || ! $file->isValid()) {
            return $this->back($back, 'Berkas unggahan tidak valid.');
        }

        $mime = (string) $file->getMimeType();

        if (! in_array($mime, self::AUDIO_MIMES, true)) {
            return $this->back($back, "Jenis berkas audio {$mime} tidak diizinkan.");
        }

        $assetKey = trim((string) $this->request->getPost('asset_key'));
        $stored   = $this->storeWithOfficialName($file, $assetKey);

        if ($stored === null) {
            return $this->back($back, 'Berkas gagal disimpan.');
        }

        $mediaId = $this->upsertAsset($assetKey, 'audio', $stored, $mime, null);

        if ($mediaId === null) {
            return $this->back($back, 'Aset audio ditolak: ' . $this->modelErrors(model(MediaAssetModel::class)));
        }

        $audio   = model(AudioAssetModel::class);
        $audioId = $audio->insert([
            'media_asset_id'    => $mediaId,
            'locale'            => (string) $this->request->getPost('locale'),
            'character_code'    => $this->nullIfBlank($this->request->getPost('character_code')),
            'context_code'      => (string) $this->request->getPost('context_code'),
            'transcript'        => (string) $this->request->getPost('transcript'),
            'production_method' => (string) ($this->request->getPost('production_method') ?: 'own_recording'),
            'voice_profile'     => $this->nullIfBlank($this->request->getPost('voice_profile')),
            'duration_ms'       => $this->durationMs(FCPATH . $stored),
            'approval_status'   => 'draft',
        ], true);

        if ($audioId === false) {
            return $this->back($back, 'Audio ditolak: ' . $this->modelErrors($audio));
        }

        model(AuditLogModel::class)->record('audio_upload', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'audio_asset',
            'target_id'     => (string) $audioId,
            'metadata'      => ['asset_key' => $assetKey, 'locale' => $this->request->getPost('locale')],
        ]);

        return $this->done($back, 'Audio tersimpan sebagai draft. Setujui dulu agar terdengar pemain.');
    }

    public function approveAudio(int $audioId): RedirectResponse
    {
        $audio = model(AudioAssetModel::class);

        if ($audio->find($audioId) === null) {
            return $this->back('admin/media/audio', 'Audio tidak ditemukan.');
        }

        $audio->update($audioId, [
            'approval_status' => 'approved',
            'approved_by'     => $this->staffId(),
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);

        service('contentRepository')->flush();

        model(AuditLogModel::class)->record('audio_approve', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'audio_asset',
            'target_id'     => (string) $audioId,
        ]);

        return $this->done('admin/media/audio', 'Audio disetujui.');
    }

    /**
     * Pindai keselarasan baris `media_assets` dengan berkas di disk:
     * berkas hilang, ukuran berubah, atau sha256 tidak lagi cocok.
     */
    public function scan(): RedirectResponse
    {
        $findings = (new MediaIntegrity())->check();

        session()->setFlashdata('media_scan', [
            'at'       => date('Y-m-d H:i:s'),
            'findings' => $findings,
        ]);

        model(AuditLogModel::class)->record('media_scan', [
            'staff_user_id' => $this->staffId(),
            'metadata'      => ['findings' => count($findings)],
        ]);

        return redirect()->to(site_url('admin/media'));
    }

    // -------------------------------------------------------------- bantuan

    /** Ukuran wajib slot aset; pola `bg.*` dicocokkan dengan fnmatch(). */
    private function requiredSize(string $assetKey): ?array
    {
        foreach (config('Gelita')->assetSizes as $pattern => $size) {
            if ($pattern === $assetKey || fnmatch($pattern, $assetKey)) {
                return $size;
            }
        }

        return null;
    }

    private function assetTypeFor(string $mime): ?string
    {
        if (in_array($mime, self::IMAGE_MIMES, true)) {
            return 'image';
        }

        if (in_array($mime, self::AUDIO_MIMES, true)) {
            return 'audio';
        }

        if (in_array($mime, self::VIDEO_MIMES, true)) {
            return 'video';
        }

        return null;
    }

    /**
     * Simpan ke public/assets/uploads/{asset_key}.{ext} dengan nama resmi.
     *
     * @return string|null storage_path relatif terhadap public/
     */
    private function storeWithOfficialName(UploadedFile $file, string $assetKey): ?string
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

        return self::UPLOAD_DIR . $name;
    }

    /**
     * Menyimpan atau memperbarui baris `media_assets`.
     *
     * `asset_key` hanya dikirim saat menambah: pada update, aturan
     * `is_unique[...,id,{id}]` milik model tidak dapat mengecualikan baris yang
     * sedang disunting (data update tidak memuat kolom id), sehingga aset yang
     * diunggah ulang akan selalu ditolak sebagai duplikat dirinya sendiri.
     *
     * @param array{0: int, 1: int}|null $size
     *
     * @return int|null id aset, atau null bila model menolak
     */
    private function upsertAsset(string $assetKey, string $type, string $storagePath, string $mime, ?array $size): ?int
    {
        $media    = model(MediaAssetModel::class);
        $existing = $media->findByKey($assetKey);
        $absolute = FCPATH . $storagePath;

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

    /**
     * Durasi audio dibaca server-side dari header berkas.
     *
     * WAV PCM menyimpan bitrate di header `fmt `, jadi durasinya dapat dihitung
     * tanpa dependensi tambahan. Format terkompresi (MP3/OGG/M4A) butuh
     * pembacaan bingkai; selama itu belum ada, durasinya dibiarkan NULL —
     * bukan ditebak.
     */
    private function durationMs(string $path): ?int
    {
        if (! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'wav') {
            return null;
        }

        $header = file_get_contents($path, false, null, 0, 44);

        if ($header === false || strlen($header) < 44 || substr($header, 0, 4) !== 'RIFF') {
            return null;
        }

        $fmt = unpack('VbyteRate', substr($header, 28, 4));
        $data = unpack('Vsize', substr($header, 40, 4));

        if (empty($fmt['byteRate'])) {
            return null;
        }

        return (int) round(($data['size'] ?? 0) / $fmt['byteRate'] * 1000);
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
