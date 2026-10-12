<?php

namespace App\Controllers\Admin;

use App\Libraries\AssetChecklist;
use App\Libraries\ImageSlots;
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
    /** Pesan galat kolom berkas unggahan (bawaan CodeIgniter menyebut nama field). */
    private const FILE_ERRORS = [
        'uploaded' => 'Pilih berkas yang akan diunggah.',
        'max_size' => 'Berkas terlalu besar (paling besar 64 MB).',
    ];

    public function index(): string
    {
        $status = (string) ($this->request->getGet('status') ?? '');
        $status = in_array($status, ['asli', 'mockup', 'kosong'], true) ? $status : '';
        $type   = (string) ($this->request->getGet('jenis') ?? '');
        $group  = (string) ($this->request->getGet('kelompok') ?? '');
        $region = (string) ($this->request->getGet('wilayah') ?? '');
        $node   = (string) ($this->request->getGet('tantangan') ?? '');
        $query  = trim((string) ($this->request->getGet('cari') ?? ''));
        $assets = model(MediaAssetModel::class)->orderBy('asset_key', 'ASC')->findAll();

        return $this->panel('admin/media/index', lang('Admin.menuMedia'), [
            'assets'     => $assets,
            'assetSizes' => config('Gelita')->assetSizes,
            'displaySizes' => config('Gelita')->assetDisplaySizes,
            'usage'      => (new MediaUsage())->forMedia(),
            'scan'       => session('media_scan'),
            'status'     => $status,
            'type'       => in_array($type, ['image', 'video'], true) ? $type : '',
            'group'      => isset(ImageSlots::GROUPS[$group]) ? $group : '',
            'region'     => in_array($region, config('Gelita')->levelPrefixes, true) ? $region : '',
            'node'       => preg_match('/^[a-z]{3}-\d{1,2}$/', $node) === 1 ? $node : '',
            'query'      => mb_substr($query, 0, 80),
            // Asset package Stage 6: images/audio changed since the package students have
            'packageChanged' => service('assetPackages')->changedSinceActive(),
        ]);
    }

    /**
     * Detail of one image slot: what the picture should show, sizes,
     * format, every screen it appears on (to check it after uploading), map
     * pin positions, and an upload form that comes back here.
     */
    public function show(int $mediaId): string|RedirectResponse
    {
        $row = model(MediaAssetModel::class)->find($mediaId);

        if ($row === null || $row['asset_type'] === 'audio') {
            return $this->back('admin/media', 'Berkas tidak ditemukan.');
        }

        $key    = (string) $row['asset_key'];
        $db     = db_connect();
        $region = ImageSlots::regionOf($key);
        $ids    = [];
        $level  = $region === null ? null : $db->table('levels')->where('code', $region)->get()->getRowArray();

        if ($level !== null) {
            $ids['level_id'] = (int) $level['id'];

            if (preg_match('/^challenge\.(?:item\.)?[a-z]+-(\d+)/', $key, $m)) {
                $nodeId = $db->table('challenge_nodes')->select('id')->where('level_id', $level['id'])
                    ->where('sequence', (int) $m[1])->get()->getRow('id');

                if ($nodeId !== null) {
                    $ids['node_id'] = (int) $nodeId;
                }
            }
        }

        // Neighbours in the same group, for stepping through slots one by one
        $group    = ImageSlots::groupOf($key);
        $siblings = array_values(array_filter(
            model(MediaAssetModel::class)->select('id, asset_key, asset_type')->orderBy('asset_key', 'ASC')->findAll(),
            static fn (array $r): bool => $r['asset_type'] !== 'audio' && ImageSlots::groupOf((string) $r['asset_key']) === $group,
        ));
        $position = array_search($mediaId, array_map('intval', array_column($siblings, 'id')), true);

        return $this->panel('admin/media/show', 'Detail gambar ' . $key, [
            'row'      => $row,
            'slot'     => ImageSlots::describe($key, $ids),
            'required' => (new MediaStore())->requiredSize($key),
            'display'  => $this->displaySize($key),
            'uses'     => (new MediaUsage())->forMedia()[$mediaId] ?? [],
            'pins'     => $this->pins($key, $level),
            'pose'     => $this->poseFrames($key),
            'prev'     => $position !== false && $position > 0 ? $siblings[$position - 1] : null,
            'next'     => $position !== false && isset($siblings[$position + 1]) ? $siblings[$position + 1] : null,
            'counter'  => $position === false ? null : [$position + 1, count($siblings)],
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

        return $this->panel('admin/media/checklist', lang('Admin.menuChecklist'), [
            'groups'  => $groups,
            'summary' => $checklist->summary($groups),
        ]);
    }

    public function upload(): RedirectResponse
    {
        // The slot detail page sends its own path to come back to it
        $back = (string) ($this->request->getPost('back') ?? '');
        $back = preg_match('#^admin/media/\d+$#', $back) === 1 ? $back : 'admin/media';

        if (! $this->validate([
            'asset_key' => ['label' => 'Kode berkas', 'rules' => 'required|max_length[160]'],
            'file'      => ['label' => 'Berkas', 'rules' => 'uploaded[file]|max_size[file,65536]', 'errors' => self::FILE_ERRORS],
        ])) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $file     = $this->request->getFile('file');
        $assetKey = trim((string) $this->request->getPost('asset_key'));

        if ($file === null) {
            return $this->back($back, 'Berkas gagal diunggah. Coba pilih dan unggah lagi.');
        }

        $result = (new MediaStore())->store($file, $assetKey, ['image', 'video'], $this->staffId());

        if ($result['error'] !== null) {
            return $this->back($back, $result['error']);
        }

        return $this->done($back, "Berkas {$assetKey} sudah tersimpan.");
    }

    /**
     * Upload many frames of one character pose at once (slot detail page of
     * any frame of that pose). Files are taken in name order (natural sort,
     * so "jaka-2" comes before "jaka-10") and stored as frames
     * `start`, `start + 1`, … up to the pose's frame count. Each file goes
     * through MediaStore::storeCopy(), so type, size, and resizing follow the
     * same rules as a single upload.
     */
    public function uploadFrames(int $mediaId): RedirectResponse
    {
        $row = model(MediaAssetModel::class)->find($mediaId);

        if ($row === null || preg_match('/^char\.(jaka|kedu)\.([a-z]+)\.\d+$/', (string) $row['asset_key'], $m) !== 1) {
            return $this->back('admin/media', 'Halaman gambar tokoh tidak ditemukan.');
        }

        $back      = 'admin/media/' . $mediaId;
        $max       = (int) (config('Gelita')->characterAnimations["{$m[1]}.{$m[2]}"]['frames'] ?? 1);
        $start     = max(1, min($max, (int) ($this->request->getPost('start') ?? 1)));
        $files     = array_values(array_filter(
            $this->request->getFileMultiple('frames') ?? [],
            static fn ($file): bool => $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE,
        ));

        if ($files === []) {
            return $this->back($back, 'Pilih gambar-gambar gerak yang akan diunggah.');
        }

        usort($files, static fn ($a, $b): int => strnatcasecmp($a->getClientName(), $b->getClientName()));

        if ($start + count($files) - 1 > $max) {
            return $this->back($back, sprintf('Pose ini paling banyak %d gambar. Mulai dari gambar ke-%d hanya muat %d berkas, tetapi yang dipilih %d.', $max, $start, $max - $start + 1, count($files)));
        }

        $store  = new MediaStore();
        $saved  = [];
        $errors = [];

        foreach ($files as $i => $file) {
            $number = $start + $i;

            // isValid() also checks is_uploaded_file()
            if (! $file->isValid()) {
                $errors[] = 'gambar ' . $number . ' (' . $file->getClientName() . '): ' . MediaStore::uploadErrorText($file->getError());

                continue;
            }

            $result = $store->storeCopy($file->getTempName(), "char.{$m[1]}.{$m[2]}.{$number}", ['image'], $this->staffId(), true);

            if ($result['error'] !== null) {
                $errors[] = 'gambar ' . $number . ' (' . $file->getClientName() . '): ' . $result['error'];
            } else {
                $saved[] = $number;
            }
        }

        if ($saved !== []) {
            service('contentRepository')->flush();

            model(AuditLogModel::class)->record('media_upload', [
                'staff_user_id' => $this->staffId(),
                'target_type'   => 'media_asset',
                'target_id'     => (string) $mediaId,
                'metadata'      => ['asset_key' => "char.{$m[1]}.{$m[2]}", 'frames' => $saved, 'via' => 'frames'],
            ]);
        }

        $limit = (int) ini_get('max_file_uploads');
        $note  = $limit > 0 && count($files) >= $limit
            ? sprintf(' Server menerima paling banyak %d berkas sekali unggah; unggah sisanya lagi mulai dari gambar ke-%d.', $limit, $start + count($files))
            : '';

        if ($errors !== []) {
            return $this->back($back, ($saved === [] ? 'Tidak ada gambar yang tersimpan. ' : sprintf('%d gambar tersimpan. ', count($saved)))
                . 'Belum tersimpan: ' . implode('; ', $errors) . '.' . $note);
        }

        return $this->done($back, sprintf('%d gambar gerak tersimpan (gambar %d–%d).', count($saved), $saved[0], $saved[count($saved) - 1]) . $note);
    }

    public function deactivate(int $mediaId): RedirectResponse
    {
        $media = model(MediaAssetModel::class);

        if ($media->find($mediaId) === null) {
            return $this->back('admin/media', 'Berkas tidak ditemukan.');
        }

        $media->update($mediaId, ['is_active' => 0]);
        service('contentRepository')->flush();

        model(AuditLogModel::class)->record('media_deactivate', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'media_asset',
            'target_id'     => (string) $mediaId,
        ]);

        $back = (string) ($this->request->getPost('back') ?? '');

        return $this->done($back === 'admin/media/' . $mediaId ? $back : 'admin/media', 'Berkas dinonaktifkan; permainan kembali memakai tampilan pengganti.');
    }

    public function audioIndex(): string
    {
        $rows = db_connect()->table('audio_assets aa')
            ->select('aa.*, ma.asset_key, ma.storage_path, ma.sha256, ma.is_active AS media_active')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->orderBy('aa.context_code', 'ASC')
            ->orderBy('aa.locale', 'ASC')
            ->get()
            ->getResultArray();

        $catalog = new NarrationCatalog();

        return $this->panel('admin/media/audio', 'Rekaman suara', [
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
            'asset_key'    => ['label' => 'Kode rekaman', 'rules' => 'required|max_length[160]'],
            'locale'       => ['label' => 'Bahasa', 'rules' => 'required|valid_locale'],
            'context_code' => ['label' => 'Tempat diputar', 'rules' => 'required|max_length[80]'],
            'transcript'   => ['label' => 'Teks rekaman', 'rules' => 'required|min_length[3]'],
            'file'         => ['label' => 'Berkas rekaman', 'rules' => 'uploaded[file]|max_size[file,65536]', 'errors' => self::FILE_ERRORS],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');

        if ($file === null || ! $file->isValid()) {
            return $this->back($back, 'Berkas gagal diunggah. Coba pilih dan unggah lagi.');
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
            return $this->back($back, 'Rekaman belum dapat disimpan: ' . $this->modelErrors($audio));
        }

        model(AuditLogModel::class)->record('audio_upload', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'audio_asset',
            'target_id'     => (string) $audioId,
            'metadata'      => ['asset_key' => $assetKey, 'locale' => $this->request->getPost('locale')],
        ]);

        return $this->done($back, 'Rekaman tersimpan dan menunggu persetujuan. Dengarkan dulu, lalu tekan Setujui agar terdengar oleh siswa.');
    }

    public function approveAudio(int $audioId): RedirectResponse
    {
        $audio = model(AudioAssetModel::class);

        if ($audio->find($audioId) === null) {
            return $this->back('admin/media/audio', 'Rekaman tidak ditemukan.');
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

        return $this->done('admin/media/audio', 'Rekaman disetujui dan kini terdengar oleh siswa.');
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

    /** @return array{0: int, 1: int}|null display size on the 1280 × 720 dp stage */
    /**
     * Every frame of the character pose a key belongs to, for the slot
     * detail page: number, image URL (null when not uploaded), and the
     * media_assets id when a row exists (to open that frame's own page).
     *
     * @return array{frames: list<array{n: int, src: string|null, id: int|null}>, fps: float, loop: bool, max: int}|null
     */
    private function poseFrames(string $key): ?array
    {
        if (preg_match('/^char\.(jaka|kedu)\.([a-z]+)\.\d+$/', $key, $m) !== 1) {
            return null;
        }

        $animation = config('Gelita')->characterAnimations["{$m[1]}.{$m[2]}"] ?? ['frames' => 1, 'seconds' => 2.0, 'loop' => true];
        $max       = max(1, (int) $animation['frames']);
        $ids       = array_column(
            model(MediaAssetModel::class)->select('id, asset_key')->like('asset_key', "char.{$m[1]}.{$m[2]}.", 'after')->findAll(),
            'id',
            'asset_key',
        );
        $frames = [];

        for ($i = 1; $i <= $max; $i++) {
            $frameKey = "char.{$m[1]}.{$m[2]}.{$i}";
            $frames[] = ['n' => $i, 'src' => media_key_src($frameKey), 'id' => isset($ids[$frameKey]) ? (int) $ids[$frameKey] : null];
        }

        $uploaded = count(array_filter(array_column($frames, 'src')));

        return [
            'frames' => $frames,
            'fps'    => character_fps($uploaded, $animation),
            'loop'   => (bool) ($animation['loop'] ?? true),
            'max'    => $max,
        ];
    }

    private function displaySize(string $key): ?array
    {
        foreach (config('Gelita')->assetDisplaySizes as $pattern => $size) {
            if ($pattern === $key || fnmatch($pattern, $key)) {
                return $size;
            }
        }

        return null;
    }

    /**
     * Pin positions drawn on top of a map image (percent of the image, from
     * the database, so pins moved by the admin are included).
     *
     * @param array<string, mixed>|null $level
     *
     * @return list<array{label: string, x: float, y: float}>
     */
    private function pins(string $key, ?array $level): array
    {
        $db = db_connect();

        if ($key === 'map.kedu') {
            $rows = $db->table('levels')->select('name_id AS label, map_x AS x, map_y AS y')->orderBy('sequence', 'ASC')->get()->getResultArray();
        } elseif ($level !== null && $key === 'map.region.' . $level['code']) {
            $rows = $db->table('challenge_nodes')->select("sequence, title_id, map_x AS x, map_y AS y")
                ->where('level_id', $level['id'])->where('is_active', 1)->orderBy('sequence', 'ASC')->get()->getResultArray();
            $rows = array_map(static fn (array $r): array => ['label' => $r['sequence'] . '. ' . $r['title_id'], 'x' => $r['x'], 'y' => $r['y']], $rows);
        } else {
            return [];
        }

        return array_map(static fn (array $r): array => ['label' => (string) $r['label'], 'x' => (float) $r['x'], 'y' => (float) $r['y']], $rows);
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
