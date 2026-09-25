<?php

namespace App\Controllers\Admin;

use App\Libraries\AssetChecklist;
use App\Libraries\MediaIntegrity;
use App\Libraries\MediaStore;
use App\Libraries\MediaUsage;
use App\Libraries\NarrationCatalog;
use App\Models\AudioAssetModel;
use App\Models\AuditLogModel;
use App\Models\MediaAssetModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Aset gambar/video dan aset audio beserta persetujuannya.
 *
 * Penyimpanan berkas (nama resmi turunan `asset_key`, dimensi dibaca ulang
 * server, ukuran wajib per slot) ada di App\Libraries\MediaStore, yang juga
 * dipakai editor konten untuk mengunggah gambar tantangan langsung dari
 * formnya.
 */
class MediaController extends BaseAdminController
{
    public function index(): string
    {
        return $this->panel('admin/media/index', 'Media', [
            'assets'     => model(MediaAssetModel::class)->orderBy('asset_key', 'ASC')->findAll(),
            'assetSizes' => config('Gelita')->assetSizes,
            'usage'      => (new MediaUsage())->forMedia(),
            'scan'       => session('media_scan'),
        ]);
    }

    /**
     * Daftar kelengkapan aset: slot yang belum berberkas, frame pose tokoh,
     * latar & peta wilayah, poster video Pustaka, dan rekaman narasi.
     */
    public function checklist(): string
    {
        $checklist = new AssetChecklist();
        $groups    = $checklist->groups();

        return $this->panel('admin/media/checklist', 'Kelengkapan aset', [
            'groups'  => $groups,
            'summary' => $checklist->summary($groups),
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

        if ($file === null) {
            return $this->back($back, 'Berkas unggahan tidak valid.');
        }

        $result = (new MediaStore())->store($file, $assetKey, ['image', 'video'], $this->staffId());

        if ($result['error'] !== null) {
            return $this->back($back, $result['error']);
        }

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

        $catalog = new NarrationCatalog();

        return $this->panel('admin/media/audio', 'Audio', [
            'rows'       => $rows,
            'drafts'     => array_combine(
                config('Gelita')->locales,
                array_map(static fn (string $locale): int => count($catalog->draftIds($locale)), config('Gelita')->locales),
            ),
            'usage'      => (new MediaUsage())->forAudio(),
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

        $assetKey = trim((string) $this->request->getPost('asset_key'));
        $store    = new MediaStore();
        $result   = $store->store($file, $assetKey, ['audio'], $this->staffId());

        if ($result['error'] !== null) {
            return $this->back($back, $result['error']);
        }

        $mediaId = (int) $result['id'];
        $stored  = (string) model(MediaAssetModel::class)->find($mediaId)['storage_path'];

        $audio   = model(AudioAssetModel::class);
        $payload = [
            'media_asset_id'    => $mediaId,
            'locale'            => (string) $this->request->getPost('locale'),
            'character_code'    => $this->nullIfBlank($this->request->getPost('character_code')),
            'context_code'      => (string) $this->request->getPost('context_code'),
            'transcript'        => (string) $this->request->getPost('transcript'),
            'production_method' => (string) ($this->request->getPost('production_method') ?: 'own_recording'),
            'voice_profile'     => $this->nullIfBlank($this->request->getPost('voice_profile')),
            'duration_ms'       => $store->durationMs(FCPATH . $stored),
            'approval_status'   => 'draft',
            'approved_by'       => null,
            'approved_at'       => null,
        ];

        // audio_assets.media_asset_id unik: mengunggah ulang asset_key yang sama
        // mengganti rekamannya dan mengembalikan statusnya ke draf.
        $existing = $audio->where('media_asset_id', $mediaId)->first();
        $audioId  = $existing === null
            ? $audio->insert($payload, true)
            : ($audio->update((int) $existing['id'], $payload) ? (int) $existing['id'] : false);

        service('contentRepository')->flush();

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

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
