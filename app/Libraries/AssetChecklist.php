<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Daftar kelengkapan aset (`/admin/media/kelengkapan`): apa yang belum
 * diunggah, berapa ukurannya, dan di mana mengunggahnya.
 *
 * Permainan tetap berjalan tanpa aset-aset ini (gradien, monogram, pose
 * `idle`, teks tanpa suara), jadi daftar ini tidak memblokir apa pun; ia
 * memberi tahu tim konten apa yang masih kurang sebelum sesi kelas.
 *
 * Status per butir: `missing` (belum ada berkas / slot nonaktif), `wrong`
 * (ada, tetapi ukuran gambarnya lain dari ketentuan), atau `ok`.
 */
final class AssetChecklist
{
    /**
     * Logo, button, background, and map slots the game reads by key, in
     * checklist order. Labels and descriptions come from ImageSlots; `bg.auth`
     * and `ui.placeholder` are left out because no screen reads them.
     */
    public const UI_SLOTS = [
        'ui.logo-hero', 'ui.btn-start', 'ui.btn-start.en', 'ui.logo', 'map.kedu',
        'bg.loading', 'bg.welcome', 'bg.intro', 'bg.map', 'bg.reflection',
    ];

    /**
     * Musik & efek suara yang diputar core/audio.js (Sfx) dari
     * public/assets/audio/{music|sfx}/{nama}.mp3. Bukan media_assets: berkas
     * disalin langsung ke folder itu (git atau salin berkas). Berkas yang tidak
     * ada membuat suara itu diam, permainan tetap berjalan.
     */
    public const SOUNDS = [
        'music/map'       => 'Musik Peta Kedu',
        'music/region'    => 'Musik peta wilayah',
        'music/challenge' => 'Musik tantangan',
        'sfx/click'       => 'Efek klik tombol',
        'sfx/correct'     => 'Efek jawaban benar',
        'sfx/wrong'       => 'Efek jawaban salah',
        'sfx/lock'        => 'Efek wilayah/tantangan terkunci',
        'sfx/shard'       => 'Efek serpihan kembali (layar selesai)',
        'sfx/region-done' => 'Efek wilayah tuntas (layar selesai penuntas)',
    ];

    /** Keterangan format per bunyi; efek lain: MP3 pendek (< 1 detik). */
    private const SOUND_SIZES = [
        'music/map'       => 'MP3, musik yang diputar berulang',
        'music/region'    => 'MP3, musik yang diputar berulang',
        'music/challenge' => 'MP3, musik yang diputar berulang',
        'sfx/shard'       => 'MP3 pendek (± 3 detik)',
        'sfx/region-done' => 'MP3 pendek (± 5 detik)',
    ];

    private BaseConnection $db;

    /** @var array<string, array<string, mixed>> asset_key → baris media_assets */
    private array $media = [];

    /** @var array<int, array<string, mixed>> id → baris media_assets */
    private array $mediaById = [];

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();

        foreach ($this->db->table('media_assets')->get()->getResultArray() as $row) {
            $this->media[(string) $row['asset_key']]  = $row;
            $this->mediaById[(int) $row['id']]         = $row;
        }
    }

    /**
     * @return list<array{title: string, help: string, items: list<array{label: string, key: string, size: string, status: string, note: string, link: string, link_label: string}>}>
     */
    public function groups(): array
    {
        return array_values(array_filter([
            $this->uiGroup(),
            $this->characterGroup(),
            $this->regionGroup(),
            $this->challengeGroup(),
            $this->soundGroup(),
            $this->posterGroup(),
            $this->narrationGroup(),
        ]));
    }

    /**
     * @param list<array<string, mixed>> $groups
     *
     * @return array{missing: int, wrong: int, ok: int}
     */
    public function summary(array $groups): array
    {
        $out = ['missing' => 0, 'wrong' => 0, 'ok' => 0];

        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {
                $out[$item['status']]++;
            }
        }

        return $out;
    }

    // -------------------------------------------------------------- kelompok

    /** @return array<string, mixed> */
    private function uiGroup(): array
    {
        $items = [];

        foreach (self::UI_SLOTS as $key) {
            $items[] = $this->slotItem($key, (string) ImageSlots::label($key), $this->detailLink($key));
        }

        return [
            'title' => 'Logo, tombol, dan latar umum',
            'help'  => 'Bila belum ada, halaman memakai latar warna polos, tombol biasa, atau judul berupa tulisan.',
            'items' => $items,
        ];
    }

    /** @return array<string, mixed> */
    private function characterGroup(): array
    {
        $items = [];

        foreach (config('Gelita')->characterAnimations as $name => $animation) {
            $frames  = (int) $animation['frames'];
            $missing = [];
            $wrong   = [];

            for ($i = 1; $i <= $frames; $i++) {
                $key   = 'char.' . $name . '.' . $i;
                $state = $this->state($key);

                if ($state === 'missing') {
                    $missing[] = $i;
                } elseif ($state === 'wrong') {
                    $wrong[] = $i;
                }
            }

            [$who, $pose] = explode('.', $name, 2);
            $status       = $missing !== [] ? 'missing' : ($wrong !== [] ? 'wrong' : 'ok');
            $note         = sprintf('%d/%d frame', $frames - count($missing), $frames)
                . ($missing !== [] ? ' · belum ada: ' . implode(', ', $missing) : '')
                . ($wrong !== [] ? ' · ukuran salah: ' . implode(', ', $wrong) : '');

            $items[] = [
                'label'      => ($who === 'kedu' ? 'Mbah Kedu' : 'Jaka') . ' · pose ' . ImageSlots::poseLabel($pose) . ' (' . $pose . ')',
                'key'        => 'char.' . $name . '.1…' . $frames,
                'size'       => $this->sizeText('char.' . $name . '.1'),
                'status'     => $status,
                'note'       => $note . ($pose !== 'idle' && in_array(1, $missing, true) ? ' (sementara tampil dengan pose diam)' : ''),
                'link'       => $this->detailLink('char.' . $name . '.' . ($missing[0] ?? $wrong[0] ?? 1)),
                'link_label' => 'Lihat & unggah',
            ];
        }

        return [
            'title' => 'Gambar gerak tokoh (per pose)',
            'help'  => 'Setiap pose butuh semua gambar geraknya (PNG berlatar transparan), yang diputar berurutan sebagai animasi. Gambar yang belum ada dilewati. Bila gambar 1 sebuah pose belum ada, tokoh tampil dengan pose diam; bila itu pun belum ada, tampil inisial nama tokoh.',
            'items' => $items,
        ];
    }

    /** @return array<string, mixed> */
    private function regionGroup(): array
    {
        $items  = [];
        $levels = $this->db->table('levels')->select('id, code, name_id, map_media_id, background_media_id, badge_media_id')
            ->orderBy('sequence', 'ASC')->get()->getResultArray();

        foreach ($levels as $level) {
            $link = site_url('admin/konten/level/' . (int) $level['id']);

            foreach ([
                'background_media_id' => ['Latar wilayah', 'bg.' . $level['code'] . '.region'],
                'map_media_id'        => ['Peta wilayah', 'map.region.' . $level['code']],
                'badge_media_id'      => ['Lencana wilayah', 'reward.badge.' . $level['code']],
            ] as $column => [$label, $defaultKey]) {
                $row    = $level[$column] === null ? null : ($this->mediaById[(int) $level[$column]] ?? null);
                $key    = $row === null ? $defaultKey : (string) $row['asset_key'];
                $status = $row === null ? 'missing' : $this->state($key);

                $items[] = [
                    'label'      => $label . ' ' . $level['name_id'],
                    'key'        => $key,
                    'size'       => $this->sizeText($key),
                    'status'     => $status,
                    'note'       => $row === null ? 'belum dipasang di wilayah' : ($status === 'missing' ? 'sudah dipasang, tetapi berkasnya belum diunggah' : ''),
                    'link'       => $row === null ? $link : site_url('admin/media/' . (int) $row['id']),
                    'link_label' => $row === null ? 'Buka wilayah' : 'Lihat & unggah',
                ];
            }
        }

        return [
            'title' => 'Latar & peta wilayah',
            'help'  => 'Unggah lewat Lihat & unggah, atau dari halaman wilayah (menu Konten). Bila belum ada, dialog dan peta wilayah memakai latar warna polos.',
            'items' => $items,
        ];
    }

    /**
     * Challenge images per challenge: sample (mockup) images still to be
     * replaced, empty slots, and wrong sizes.
     *
     * @return array<string, mixed>|null
     */
    private function challengeGroup(): ?array
    {
        $nodes = $this->db->table('challenge_nodes cn')
            ->select('cn.id, cn.sequence, cn.title_id, cn.scene_media_id, l.code, l.name_id')
            ->join('levels l', 'l.id = cn.level_id')
            ->where('cn.is_active', 1)
            ->orderBy('l.sequence', 'ASC')->orderBy('cn.sequence', 'ASC')
            ->get()->getResultArray();

        $items = [];

        foreach ($nodes as $node) {
            $ids = array_map('intval', array_column($this->db->table('challenge_items')->select('media_asset_id')
                ->where('challenge_node_id', $node['id'])->where('is_active', 1)->where('media_asset_id IS NOT NULL')
                ->get()->getResultArray(), 'media_asset_id'));

            if ($node['scene_media_id'] !== null) {
                $ids[] = (int) $node['scene_media_id'];
            }

            $ids = array_values(array_unique($ids));

            if ($ids === []) {
                continue;
            }

            $sample = $missing = $wrong = [];

            foreach ($ids as $id) {
                $row = $this->mediaById[$id] ?? null;

                if ($row === null) {
                    continue;
                }

                $key   = (string) $row['asset_key'];
                $state = $this->state($key);

                if ($state === 'missing') {
                    $missing[] = $key;
                } elseif ((int) ($row['is_placeholder'] ?? 0) === 1) {
                    $sample[] = $key;
                } elseif ($state === 'wrong') {
                    $wrong[] = $key;
                }
            }

            $real   = count($ids) - count($sample) - count($missing) - count($wrong);
            $status = $missing !== [] || $sample !== [] ? 'missing' : ($wrong !== [] ? 'wrong' : 'ok');
            $ref    = node_ref((string) $node['code'], (int) $node['sequence']);

            $items[] = [
                'label'      => 'Tantangan ' . $ref . ' · ' . $node['title_id'] . ' (' . $node['name_id'] . ')',
                'key'        => count($ids) . ' gambar',
                'size'       => $this->sizeText((string) ($this->mediaById[$ids[0]]['asset_key'] ?? '')),
                'status'     => $status,
                'note'       => sprintf('%d/%d gambar asli', $real, count($ids))
                    . ($sample !== [] ? ' · masih gambar contoh: ' . count($sample) : '')
                    . ($missing !== [] ? ' · belum ada: ' . count($missing) : '')
                    . ($wrong !== [] ? ' · ukuran salah: ' . implode(', ', $wrong) : ''),
                'link'       => site_url('admin/media') . '?kelompok=challenge&wilayah=' . rawurlencode((string) $node['code']) . '&tantangan=' . rawurlencode($ref),
                'link_label' => 'Lihat gambarnya',
            ];
        }

        return $items === [] ? null : [
            'title' => 'Gambar tantangan',
            'help'  => 'Gambar contoh (bertulisan GAMBAR CONTOH) harus diganti gambar asli sebelum pengambilan data, terutama Cari Objek dan pernyataan bergambar. Ukuran setiap gambar ada di halaman detailnya.',
            'items' => $items,
        ];
    }

    /** @return array<string, mixed> */
    private function soundGroup(): array
    {
        $items = [];

        foreach (self::SOUNDS as $name => $label) {
            $path    = 'assets/audio/' . $name . '.mp3';
            $items[] = [
                'label'      => $label,
                'key'        => 'public/' . $path,
                'size'       => self::SOUND_SIZES[$name] ?? 'MP3 pendek (< 1 detik)',
                'status'     => is_file(FCPATH . $path) ? 'ok' : 'missing',
                'note'       => 'sudah tersedia bawaan; untuk menggantinya, petugas teknis menimpa berkas bernama sama di server (tidak lewat panel)',
                'link'       => base_url($path),
                'link_label' => 'Periksa berkas',
            ];
        }

        return [
            'title' => 'Musik & efek suara',
            'help'  => 'Musik dan bunyi bawaan (musik gamelan buatan komputer) sudah tersedia dan diputar setelah siswa mengetuk layar. Bila sebuah berkas tidak ada, bagian itu hanya tanpa suara. Petugas teknis: cara mengganti berkas bawaan ada di docs/audio.md.',
            'items' => $items,
        ];
    }

    /** @return array<string, mixed>|null */
    private function posterGroup(): ?array
    {
        $rows = $this->db->table('library_media lm')
            ->select('lm.id, lm.sequence, lm.external_url, lm.poster_media_id, lp.sequence AS page, lp.level_id, l.name_id')
            ->join('library_pages lp', 'lp.id = lm.library_page_id')
            ->join('levels l', 'l.id = lp.level_id')
            ->where('lm.media_kind', 'video')
            ->where('lm.is_active', 1)
            ->orderBy('l.sequence', 'ASC')->orderBy('lp.sequence', 'ASC')->orderBy('lm.sequence', 'ASC')
            ->get()->getResultArray();

        $items = [];

        foreach ($rows as $row) {
            $poster = $row['poster_media_id'] === null ? null : ($this->mediaById[(int) $row['poster_media_id']] ?? null);

            if ($poster !== null && (int) $poster['is_active'] === 1) {
                continue;   // hanya yang masih kosong
            }

            $items[] = [
                'label'      => 'Pustaka ' . $row['name_id'] . ' halaman ' . $row['page'] . ' · video ke-' . $row['sequence'],
                'key'        => $poster['asset_key'] ?? 'gambar sampul belum dipasang',
                'size'       => $this->sizeText('library.image'),
                'status'     => 'missing',
                'note'       => $row['external_url'] !== null && $row['external_url'] !== ''
                    ? 'video dari tautan: unggah gambar sampul, atau minta petugas teknis mengambilnya otomatis'
                    : 'video berkas: unggah gambar sampul',
                'link'       => site_url('admin/konten/pustaka/' . (int) $row['level_id']),
                'link_label' => 'Sunting Pustaka',
            ];
        }

        return [
            'title' => 'Gambar sampul video Pustaka',
            'help'  => $rows === [] ? 'Belum ada video di Pustaka.' : 'Video tanpa gambar sampul tampil dengan kotak polos.',
            'items' => $items,
        ];
    }

    /** @return array<string, mixed> */
    private function narrationGroup(): array
    {
        $catalog  = new NarrationCatalog($this->db);
        $progress = $catalog->progress($catalog->rows());
        $items    = [];

        foreach ($progress as $locale => $p) {
            $items[] = [
                'label'      => 'Rekaman narasi ' . strtoupper($locale) . ' (bahasa ' . admin_label('locale', $locale) . ')',
                'key'        => 'audio.narasi.' . $locale . '.*',
                'size'       => 'MP3 mono 48 kbps',
                'status'     => $p['none'] > 0 ? 'missing' : ($p['draft'] > 0 ? 'wrong' : 'ok'),
                'note'       => sprintf('%d/%d disetujui, %d menunggu persetujuan, %d belum ada', $p['approved'], $p['total'], $p['draft'], $p['none']),
                'link'       => site_url('admin/konten/narasi'),
                'link_label' => 'Buka Rekaman narasi',
            ];
        }

        return [
            'title' => 'Rekaman narasi cerita',
            'help'  => 'Rekaman untuk semua baris cerita dan petunjuk tantangan Cari Objek. Tanpa rekaman yang disetujui, teksnya tetap tampil dan siswa melanjutkan slide sendiri. Rekaman yang menunggu persetujuan belum terdengar oleh siswa.',
            'items' => $items,
        ];
    }

    // -------------------------------------------------------------- bantuan

    /** Slot detail page when the slot has a row, else the upload box with the key filled in. */
    private function detailLink(string $key): string
    {
        $row = $this->media[$key] ?? null;

        return $row === null
            ? site_url('admin/media') . '?asset_key=' . rawurlencode($key) . '#unggah'
            : site_url('admin/media/' . (int) $row['id']);
    }

    /** @return array<string, string> */
    private function slotItem(string $key, string $label, string $link): array
    {
        $state = $this->state($key);
        $row   = $this->media[$key] ?? null;

        return [
            'label'      => $label,
            'key'        => $key,
            'size'       => $this->sizeText($key),
            'status'     => $state,
            'note'       => $state === 'wrong' ? 'ukurannya sekarang ' . $row['width_px'] . '×' . $row['height_px'] : '',
            'link'       => $link,
            'link_label' => 'Lihat & unggah',
        ];
    }

    /** missing | wrong | ok untuk satu asset_key. */
    private function state(string $key): string
    {
        $row = $this->media[$key] ?? null;

        if ($row === null || (int) $row['is_active'] !== 1 || ! is_file(FCPATH . $row['storage_path'])) {
            return 'missing';
        }

        $need = $this->requiredSize($key);

        if ($need !== null && $row['width_px'] !== null
            && ((int) $row['width_px'] !== $need[0] || (int) $row['height_px'] !== $need[1])) {
            return 'wrong';
        }

        return 'ok';
    }

    /** @return array{0: int, 1: int}|null */
    private function requiredSize(string $key): ?array
    {
        return (new MediaStore())->requiredSize($key);
    }

    private function sizeText(string $key): string
    {
        $size = $this->requiredSize($key);

        if ($size !== null) {
            return $size[0] . ' × ' . $size[1] . ' px';
        }

        $recommended = ImageSlots::describe($key)['recommended'] ?? null;

        return $recommended === null ? 'ukuran bebas' : 'disarankan ' . $recommended[0] . ' × ' . $recommended[1] . ' px';
    }
}
