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
    /** Slot UI & latar umum yang dibaca view lewat asset_key. */
    public const UI_SLOTS = [
        'ui.logo-hero'    => 'Logo landscape halaman awal',
        'ui.btn-start'    => 'Tombol Mulai bergambar (Indonesia)',
        'ui.btn-start.en' => 'Tombol Mulai bergambar (Inggris)',
        'ui.logo'         => 'Logo panel admin & halaman masuk',
        'map.kedu'        => 'Peta Karesidenan Kedu',
        'bg.loading'      => 'Latar tirai pemuatan (Membuka Peta Kedu)',
        'bg.welcome'      => 'Latar halaman awal',
        'bg.auth'         => 'Latar halaman masuk & daftar',
        'bg.intro'        => 'Latar cerita pembuka',
        'bg.map'          => 'Latar peta Kedu',
        'bg.reflection'   => 'Latar Balai Refleksi',
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

    /** Ukuran slot yang tidak tercakup pola Config\Gelita::$assetSizes. */
    private const FALLBACK_SIZES = [
        'map.region'   => 'map.region',
        'reward.badge' => 'reward.badge',
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

        foreach (self::UI_SLOTS as $key => $label) {
            $items[] = $this->slotItem($key, $label, site_url('admin/media') . '?asset_key=' . rawurlencode($key));
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
                'label'      => ($who === 'kedu' ? 'Mbah Kedu' : 'Jaka') . ' · pose ' . $pose,
                'key'        => 'char.' . $name . '.1…' . $frames,
                'size'       => $this->sizeText('char.' . $name . '.1'),
                'status'     => $status,
                'note'       => $note . ($pose !== 'idle' && $status === 'missing' ? ' (sementara tampil dengan pose diam)' : ''),
                'link'       => site_url('admin/media') . '?asset_key=' . rawurlencode('char.' . $name . '.' . ($missing[0] ?? $wrong[0] ?? 1)),
                'link_label' => 'Unggah gambar',
            ];
        }

        return [
            'title' => 'Gambar gerak tokoh (per pose)',
            'help'  => 'Setiap pose butuh semua gambar geraknya (PNG berlatar transparan). Pose yang belum lengkap tampil dengan pose diam; bila itu pun belum ada, tampil inisial nama tokoh.',
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
                    'link'       => $link,
                    'link_label' => 'Buka wilayah',
                ];
            }
        }

        return [
            'title' => 'Latar & peta wilayah',
            'help'  => 'Diunggah dari halaman wilayah (menu Konten). Bila belum ada, dialog dan peta wilayah memakai latar warna polos.',
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
        if (! $this->db->tableExists('library_media')) {
            return null;
        }

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
                'size'       => 'MP3 mono 64–96 kbps',
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
            'link_label' => 'Unggah gambar',
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
        $size = (new MediaStore())->requiredSize($key);

        if ($size !== null) {
            return $size;
        }

        foreach (self::FALLBACK_SIZES as $prefix => $pattern) {
            if (str_starts_with($key, $prefix . '.')) {
                return config('Gelita')->assetSizes[$pattern] ?? null;
            }
        }

        return config('Gelita')->assetSizes[$key] ?? null;
    }

    private function sizeText(string $key): string
    {
        $size = $this->requiredSize($key);

        return $size === null ? 'ukuran bebas' : $size[0] . ' × ' . $size[1] . ' px';
    }
}
