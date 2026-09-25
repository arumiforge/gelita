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
        'ui.btn-start.en' => 'Tombol Mulai bergambar (English)',
        'ui.logo'         => 'Logo panel admin & halaman masuk',
        'map.kedu'        => 'Peta Karesidenan Kedu',
        'bg.loading'      => 'Latar tirai pemuatan (Membuka Peta Kedu)',
        'bg.welcome'      => 'Latar halaman awal',
        'bg.auth'         => 'Latar halaman masuk & daftar',
        'bg.intro'        => 'Latar cerita pembuka',
        'bg.map'          => 'Latar peta Kedu',
        'bg.reflection'   => 'Latar Balai Refleksi',
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
            'title' => 'Tampilan & latar umum',
            'help'  => 'Tanpa berkas, halaman memakai gradien, tombol CSS, atau judul teks.',
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
                . ($missing !== [] ? ' · belum: ' . implode(', ', $missing) : '')
                . ($wrong !== [] ? ' · ukuran salah: ' . implode(', ', $wrong) : '');

            $items[] = [
                'label'      => ($who === 'kedu' ? 'Mbah Kedu' : 'Jaka') . ' · pose ' . $pose,
                'key'        => 'char.' . $name . '.1…' . $frames,
                'size'       => $this->sizeText('char.' . $name . '.1'),
                'status'     => $status,
                'note'       => $note . ($pose !== 'idle' && $status === 'missing' ? ' (tampil sebagai pose idle)' : ''),
                'link'       => site_url('admin/media') . '?asset_key=' . rawurlencode('char.' . $name . '.' . ($missing[0] ?? $wrong[0] ?? 1)),
                'link_label' => 'Unggah di Media',
            ];
        }

        return [
            'title' => 'Frame pose tokoh',
            'help'  => 'Setiap pose butuh semua frame (PNG transparan). Pose yang belum lengkap tampil sebagai pose idle, lalu monogram.',
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
                    'note'       => $row === null ? 'belum dipasang di wilayah' : ($status === 'missing' ? 'aset terpasang tanpa berkas' : ''),
                    'link'       => $link,
                    'link_label' => 'Sunting wilayah',
                ];
            }
        }

        return [
            'title' => 'Latar & peta wilayah',
            'help'  => 'Diunggah dari editor wilayah. Tanpa latar, dialog dan peta wilayah memakai gradien.',
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
                'label'      => 'Pustaka ' . $row['name_id'] . ' hlm ' . $row['page'] . ' · video ' . $row['sequence'],
                'key'        => $poster['asset_key'] ?? '—',
                'size'       => $this->sizeText('library.image'),
                'status'     => 'missing',
                'note'       => $row['external_url'] !== null && $row['external_url'] !== ''
                    ? 'video tautan: jalankan php spark gelita:library:thumbnails atau unggah poster'
                    : 'video berkas: unggah poster',
                'link'       => site_url('admin/konten/pustaka/' . (int) $row['level_id']),
                'link_label' => 'Sunting Pustaka',
            ];
        }

        return [
            'title' => 'Poster video Pustaka',
            'help'  => $rows === [] ? 'Belum ada video di Pustaka.' : 'Video tanpa poster tampil dengan kartu polos.',
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
                'label'      => 'Rekaman narasi ' . strtoupper($locale),
                'key'        => 'audio.narasi.' . $locale . '.*',
                'size'       => 'MP3 mono 64–96 kbps',
                'status'     => $p['none'] > 0 ? 'missing' : ($p['draft'] > 0 ? 'wrong' : 'ok'),
                'note'       => sprintf('%d/%d disetujui, %d draft, %d belum ada', $p['approved'], $p['total'], $p['draft'], $p['none']),
                'link'       => site_url('admin/konten/narasi'),
                'link_label' => 'Buka Narasi',
            ];
        }

        return [
            'title' => 'Audio narasi naskah',
            'help'  => 'Tanpa rekaman yang disetujui, teks tetap tampil dan slide dilanjutkan manual. Draft belum terdengar pemain.',
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
            'note'       => $state === 'wrong' ? 'sekarang ' . $row['width_px'] . '×' . $row['height_px'] : '',
            'link'       => $link,
            'link_label' => 'Unggah di Media',
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
