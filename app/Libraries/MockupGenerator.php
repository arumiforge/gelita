<?php

namespace App\Libraries;

use App\Models\AuditLogModel;
use App\Models\MediaAssetModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Gambar contoh (mockup) untuk slot gambar tantangan (Dok 2 §10).
 *
 * Setiap slot gambar yang dipakai node/soal aktif (adegan Cari Objek, gambar
 * Susun Gambar, benda Cari Objek, gambar pernyataan Benar atau Salah) diisi
 * SVG buatan sistem agar permainan dapat diuji ujung ke ujung sebelum gambar
 * asli tersedia. Berkas ditulis ke public/assets/uploads/mockup/{asset_key}.svg,
 * didaftarkan lewat MediaStore::registerFile(), lalu barisnya ditandai
 * `is_placeholder = 1` dengan kredit {@see self::CREDIT}.
 *
 * Gambar asli (`is_placeholder = 0` dan berkasnya ada) tidak pernah ditimpa,
 * juga dengan `$force`. `$force` hanya membuat ulang mockup yang sudah ada.
 *
 * Rupa per jenis (§10.3): ukuran = ukuran wajib slot (Config\Gelita::$assetSizes),
 * `viewBox` + `width`/`height`, pita "GAMBAR CONTOH" di pojok, palet per
 * wilayah (Temanggung hijau–teal, Magelang batu–amber, Wonosobo biru
 * kabut–ungu). Benda target dan pengecoh Cari Objek bergaya identik.
 */
class MockupGenerator
{
    public const CREDIT = 'Gambar contoh GELITA (mockup)';

    /** Folder bawaan berkas mockup, relatif terhadap FCPATH. */
    public const DIR = MediaStore::UPLOAD_DIR . 'mockup/';

    public const FONT = 'Plus Jakarta Sans, Arial, sans-serif';

    /** Jenis slot → ukuran (sama dengan Config\Gelita::$assetSizes). */
    public const SIZES = [
        'puzzle'    => [1200, 1200],
        'scene'     => [1280, 720],
        'object'    => [240, 240],
        'statement' => [480, 360],
    ];

    /**
     * Palet per wilayah: langit, langit bawah, gunung jauh, gunung dekat,
     * aksen, tanah, gelap (teks), terang.
     */
    public const PALETTES = [
        'tmg' => ['sky' => '#d7f2ea', 'sky2' => '#f1fbf7', 'far' => '#7cc2b0', 'near' => '#2f7d6b', 'accent' => '#1fa58a', 'ground' => '#5e9f4b', 'dark' => '#134e4a', 'light' => '#e8f7f1'],
        'mgl' => ['sky' => '#f8e8cc', 'sky2' => '#fdf6ea', 'far' => '#b8a690', 'near' => '#7a6957', 'accent' => '#d9921f', 'ground' => '#b9803a', 'dark' => '#47372a', 'light' => '#fbf1e1'],
        'wnb' => ['sky' => '#dfe5f5', 'sky2' => '#f4f6fc', 'far' => '#a3afd6', 'near' => '#5c5d9e', 'accent' => '#8a6fc8', 'ground' => '#6f86bb', 'dark' => '#2c2a55', 'light' => '#eef0fa'],
        ''    => ['sky' => '#e5e7eb', 'sky2' => '#f9fafb', 'far' => '#9ca3af', 'near' => '#6b7280', 'accent' => '#4b5563', 'ground' => '#9ca3af', 'dark' => '#1f2937', 'light' => '#f3f4f6'],
    ];

    private BaseConnection $db;

    /** Folder tujuan relatif FCPATH, berakhiran `/`. */
    private string $dir;

    public function __construct(?BaseConnection $db = null, ?string $dir = null)
    {
        $this->db  = $db ?? db_connect();
        $this->dir = rtrim(str_replace('\\', '/', $dir ?? self::DIR), '/') . '/';
    }

    /** Jenis slot dari kunci aset (pola Dok 2 §10.3), atau null bila bukan slot mockup. */
    public static function kindFor(string $assetKey): ?string
    {
        return match (true) {
            fnmatch('challenge.*.scene', $assetKey)     => 'scene',
            fnmatch('challenge.item.*-1-*', $assetKey) => 'puzzle',
            fnmatch('challenge.item.*-4-*', $assetKey) => 'object',
            fnmatch('challenge.item.*-3-*', $assetKey) => 'statement',
            default                                    => null,
        };
    }

    /**
     * Slot gambar tantangan yang dipakai node/soal aktif, urut asset_key.
     *
     * @param string|null $nodeRef mis. `tmg-4`; null = semua node
     *
     * @return list<array{id: int, asset_key: string, kind: string, width: int, height: int, region: string,
     *                    node: string, label: string, description: string, title: string,
     *                    positions: list<array{x: float, y: float, w: float}>, status: string, row: array<string, mixed>}>
     *         status: `real` (gambar asli), `mockup` (gambar contoh yang sudah ada), `empty` (belum ada berkas)
     */
    public function slots(?string $nodeRef = null): array
    {
        $prefixes = array_flip(config('Gelita')->levelPrefixes);
        $nodes    = [];

        foreach ($this->db->table('challenge_nodes n')
            ->select('n.id, n.sequence, n.title_id, n.scene_media_id, n.config_json, l.code AS level_code')
            ->join('levels l', 'l.id = n.level_id')
            ->where('n.is_active', 1)
            ->get()->getResultArray() as $row) {
            $ref = ($prefixes[$row['level_code']] ?? $row['level_code']) . '-' . (int) $row['sequence'];

            if ($nodeRef === null || $nodeRef === $ref) {
                $nodes[(int) $row['id']] = $row + ['ref' => $ref];
            }
        }

        if ($nodes === []) {
            return [];
        }

        $uses      = [];   // media id => node id
        $positions = [];   // node id => list posisi benda

        foreach ($nodes as $id => $node) {
            if ($node['scene_media_id'] !== null) {
                $uses[(int) $node['scene_media_id']] ??= $id;
            }
        }

        foreach ($this->db->table('challenge_items')
            ->select('challenge_node_id, media_asset_id, config_json')
            ->where('is_active', 1)
            ->whereIn('challenge_node_id', array_keys($nodes))
            ->orderBy('sequence', 'ASC')
            ->get()->getResultArray() as $item) {
            $nodeId = (int) $item['challenge_node_id'];

            if ($item['media_asset_id'] !== null) {
                $uses[(int) $item['media_asset_id']] ??= $nodeId;
            }

            $config = json_decode((string) $item['config_json'], true);

            if (is_array($config) && isset($config['x'], $config['y'], $config['w'])) {
                $positions[$nodeId][] = ['x' => (float) $config['x'], 'y' => (float) $config['y'], 'w' => (float) $config['w']];
            }
        }

        if ($uses === []) {
            return [];
        }

        $slots = [];

        foreach ($this->db->table('media_assets')->whereIn('id', array_keys($uses))->get()->getResultArray() as $row) {
            $key  = (string) $row['asset_key'];
            $kind = self::kindFor($key);

            if ($kind === null) {
                continue;
            }

            $node   = $nodes[$uses[(int) $row['id']]];
            $region = preg_match('/(?:^|[.])(tmg|mgl|wnb)-/', $key, $m) ? $m[1] : ($prefixes[$node['level_code']] ?? '');
            $label  = trim((string) ($row['label'] ?? ''));

            $slots[] = [
                'id'          => (int) $row['id'],
                'asset_key'   => $key,
                'kind'        => $kind,
                'width'       => self::SIZES[$kind][0],
                'height'      => self::SIZES[$kind][1],
                'region'      => $region,
                'node'        => $node['ref'],
                'label'       => $label !== '' ? $label : $key,
                'description' => trim((string) ($row['description'] ?? '')),
                'title'       => $kind === 'scene' ? $this->variantTitle($node, $label !== '' ? $label : $key) : '',
                'positions'   => $kind === 'scene' ? self::uniquePositions($positions[(int) $node['id']] ?? []) : [],
                'status'      => $this->status($row),
                'row'         => $row,
            ];
        }

        usort($slots, static fn (array $a, array $b): int => strcmp($a['asset_key'], $b['asset_key']));

        return $slots;
    }

    /**
     * Kait `gelita:reset` (Dok 1 §7.3 langkah 8): buat mockup untuk slot yang
     * belum berisi gambar; gambar asli dan mockup yang masih utuh dibiarkan.
     *
     * @return array<string, mixed> lihat run()
     */
    public function generateMissing(?string $nodeRef = null): array
    {
        return $this->run(false, false, $nodeRef);
    }

    /**
     * @param bool $force  buat ulang mockup yang sudah ada (gambar asli tetap)
     * @param bool $dryRun hanya hitung, tanpa menulis
     *
     * @return array{count: int, created: int, refreshed: int, existing: int, kept: int, failed: list<string>,
     *               slots: list<array{asset_key: string, kind: string, node: string, action: string}>}
     *         action: create | refresh | existing | kept | failed
     */
    public function run(bool $force = false, bool $dryRun = false, ?string $nodeRef = null): array
    {
        $out = ['count' => 0, 'created' => 0, 'refreshed' => 0, 'existing' => 0, 'kept' => 0, 'failed' => [], 'slots' => []];

        foreach ($this->slots($nodeRef) as $slot) {
            $action = match ($slot['status']) {
                'real'   => 'kept',
                'mockup' => $force ? 'refresh' : 'existing',
                default  => 'create',
            };

            if (! $dryRun && in_array($action, ['create', 'refresh'], true)) {
                $error = $this->write($slot);

                if ($error !== null) {
                    $action          = 'failed';
                    $out['failed'][] = $slot['asset_key'] . ': ' . $error;
                }
            }

            match ($action) {
                'create'   => $out['created']++,
                'refresh'  => $out['refreshed']++,
                'existing' => $out['existing']++,
                'kept'     => $out['kept']++,
                default    => null,
            };

            $out['slots'][] = ['asset_key' => $slot['asset_key'], 'kind' => $slot['kind'], 'node' => $slot['node'], 'action' => $action];
        }

        // count = berkas yang benar-benar ditulis (0 pada dry-run)
        $out['count'] = $dryRun ? 0 : $out['created'] + $out['refreshed'];

        if (! $dryRun && $out['count'] > 0) {
            service('contentRepository')->flush();

            model(AuditLogModel::class)->record('media_mockups', [
                'target_type' => 'media_asset',
                'metadata'    => [
                    'created'   => $out['created'],
                    'refreshed' => $out['refreshed'],
                    'kept'      => $out['kept'],
                    'node'      => $nodeRef,
                    'failed'    => count($out['failed']),
                ],
            ]);
        }

        return $out;
    }

    // ------------------------------------------------------------------ SVG

    /**
     * SVG mockup satu slot (lihat slots() untuk bentuk $slot).
     *
     * @param array<string, mixed> $slot
     */
    public function svg(array $slot): string
    {
        $palette = self::PALETTES[$slot['region'] ?? ''] ?? self::PALETTES[''];
        [$w, $h] = self::SIZES[$slot['kind']];

        $body = match ($slot['kind']) {
            'puzzle'    => $this->puzzle($slot, $palette),
            'scene'     => $this->scene($slot, $palette),
            'object'    => $this->object($slot, $palette),
            'statement' => $this->statement($slot, $palette),
        };

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" font-family="%s" role="img" aria-label="%s">', $w, $h, $w, $h, self::FONT, self::e('Gambar contoh: ' . $slot['label']))
            . "\n<title>" . self::e('Gambar contoh: ' . $slot['label']) . "</title>\n"
            . $body
            . "</svg>\n";
    }

    /** Susun Gambar 1200 × 1200: lanskap datar yang berbeda di tiap bagian, tanpa angka urutan. */
    private function puzzle(array $slot, array $p): string
    {
        $region = $slot['region'];
        $s      = '<defs><linearGradient id="langit" x1="0" y1="0" x2="0" y2="1">'
            . "<stop offset=\"0\" stop-color=\"{$p['sky']}\"/><stop offset=\"1\" stop-color=\"{$p['sky2']}\"/></linearGradient></defs>\n"
            . "<rect width=\"1200\" height=\"1200\" fill=\"url(#langit)\"/>\n";

        // Matahari kiri atas, awan kanan atas
        $s .= '<g>';

        for ($i = 0; $i < 12; $i++) {
            $a  = deg2rad($i * 30);
            $s .= sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="#f4b63f" stroke-width="10" stroke-linecap="round"/>', 230 + cos($a) * 125, 230 + sin($a) * 125, 230 + cos($a) * 160, 230 + sin($a) * 160);
        }

        $s .= "<circle cx=\"230\" cy=\"230\" r=\"100\" fill=\"#f9c74f\"/></g>\n";
        $s .= '<g fill="#ffffff" opacity="0.95"><ellipse cx="900" cy="230" rx="150" ry="58"/><ellipse cx="830" cy="200" rx="80" ry="60"/>'
            . '<ellipse cx="960" cy="185" rx="95" ry="70"/><ellipse cx="760" cy="350" rx="90" ry="34"/><ellipse cx="1010" cy="380" rx="70" ry="28"/></g>' . "\n";

        // Gunung/candi di tengah sesuai wilayah
        $s .= match ($region) {
            'mgl'   => "<polygon points=\"0,760 260,470 520,760\" fill=\"{$p['far']}\"/><polygon points=\"760,760 1000,500 1200,700 1200,760\" fill=\"{$p['far']}\"/>\n"
                . $this->stupa(600, 760, 1.6, $p['near'], $p['dark']),
            'wnb'   => "<polygon points=\"-40,760 300,430 640,760\" fill=\"{$p['far']}\"/><polygon points=\"520,760 860,460 1240,760\" fill=\"{$p['near']}\"/>\n"
                . "<g fill=\"#ffffff\" opacity=\"0.55\"><rect x=\"0\" y=\"640\" width=\"1200\" height=\"36\" rx=\"18\"/><rect x=\"120\" y=\"700\" width=\"760\" height=\"26\" rx=\"13\"/></g>\n"
                . $this->temple(330, 760, 1.0, $p['dark'], $p['accent']),
            default => "<polygon points=\"40,760 380,380 720,760\" fill=\"{$p['near']}\"/><polygon points=\"480,760 820,420 1160,760\" fill=\"{$p['far']}\"/>\n"
                . "<polygon points=\"380,380 330,440 360,430 400,450 430,430\" fill=\"#ffffff\" opacity=\"0.8\"/><polygon points=\"820,420 770,480 800,470 840,490 870,470\" fill=\"#ffffff\" opacity=\"0.8\"/>\n",
        };

        // Pohon di kiri, rumah di kanan
        foreach ([[110, 700], [190, 730], [1040, 735]] as [$x, $y]) {
            $s .= sprintf('<rect x="%d" y="%d" width="16" height="50" fill="%s"/><circle cx="%d" cy="%d" r="42" fill="%s"/>', $x - 8, $y, $p['dark'], $x, $y - 10, $p['ground']);
        }

        $s .= sprintf("\n<g><rect x=\"900\" y=\"690\" width=\"110\" height=\"80\" fill=\"%s\"/><polygon points=\"885,690 955,640 1025,690\" fill=\"%s\"/><rect x=\"940\" y=\"720\" width=\"28\" height=\"50\" fill=\"%s\"/></g>\n", $p['light'], $p['accent'], $p['dark']);

        // Pita tanah berwarna
        $bands = [$p['ground'], $p['near'], $p['accent'], $p['dark']];

        foreach ($bands as $i => $color) {
            $s .= sprintf('<rect x="0" y="%d" width="1200" height="%d" fill="%s"/>', 760 + $i * 110, 110, $color);
        }

        // Petak sawah/kebun bergaris di pita tanah, beda pola kiri–kanan
        for ($i = 0; $i < 10; $i++) {
            $s .= sprintf('<rect x="%d" y="%d" width="70" height="18" rx="9" fill="#ffffff" opacity="0.25"/>', 40 + $i * 115, 800 + ($i % 2) * 30);
        }

        // Kartu judul di sepertiga bawah
        $lines = self::wrap($slot['label'], 30, 2);
        $cardH = 70 + count($lines) * 60;
        $s .= sprintf("\n<rect x=\"150\" y=\"%d\" width=\"900\" height=\"%d\" rx=\"28\" fill=\"#ffffff\" opacity=\"0.94\"/>", 880, $cardH);
        $s .= self::textLines($lines, 600, 950, 52, 60, $p['dark'], 'bold');

        // Bingkai dengan empat sudut berwarna berbeda
        $s .= "\n<rect x=\"14\" y=\"14\" width=\"1172\" height=\"1172\" fill=\"none\" stroke=\"{$p['dark']}\" stroke-width=\"28\"/>";

        foreach ([[0, 0, '#e63946'], [1080, 0, '#f4a261'], [0, 1080, '#2a9d8f'], [1080, 1080, '#6d597a']] as [$x, $y, $color]) {
            $s .= sprintf('<rect x="%d" y="%d" width="120" height="120" fill="%s"/>', $x, $y, $color);
        }

        $s .= sprintf("\n<text x=\"600\" y=\"1172\" text-anchor=\"middle\" font-size=\"22\" fill=\"#ffffff\">%s</text>\n", self::e($slot['asset_key']));

        return $s . $this->ribbon(1200, 1200, 26);
    }

    /** Adegan Cari Objek 1280 × 720: alas di bawah setiap posisi benda, spanduk judul variasi. */
    private function scene(array $slot, array $p): string
    {
        $s = '<defs><linearGradient id="langit" x1="0" y1="0" x2="0" y2="1">'
            . "<stop offset=\"0\" stop-color=\"{$p['sky']}\"/><stop offset=\"1\" stop-color=\"{$p['sky2']}\"/></linearGradient></defs>\n"
            . "<rect width=\"1280\" height=\"720\" fill=\"url(#langit)\"/>\n";

        $s .= match ($slot['region']) {
            'mgl'   => "<polygon points=\"820,470 1010,300 1200,470\" fill=\"{$p['far']}\"/>\n" . $this->stupa(330, 470, 1.0, $p['far'], $p['near']),
            'wnb'   => "<polygon points=\"0,470 260,250 520,470\" fill=\"{$p['far']}\"/><polygon points=\"760,470 1020,280 1280,470\" fill=\"{$p['far']}\"/>\n"
                . $this->temple(640, 470, 0.7, $p['near'], $p['far'])
                . "<g fill=\"#ffffff\" opacity=\"0.6\"><rect x=\"0\" y=\"400\" width=\"1280\" height=\"30\" rx=\"15\"/><rect x=\"200\" y=\"440\" width=\"900\" height=\"22\" rx=\"11\"/></g>\n",
            default => "<polygon points=\"180,470 450,230 720,470\" fill=\"{$p['far']}\"/><polygon points=\"560,470 830,250 1100,470\" fill=\"{$p['far']}\" opacity=\"0.8\"/>\n",
        };

        $s .= "<rect x=\"0\" y=\"470\" width=\"1280\" height=\"250\" fill=\"{$p['ground']}\" opacity=\"0.55\"/>\n";

        // Alas di bawah setiap posisi benda (kotak x, y, w %, tinggi w × 16/9 % = persegi)
        foreach ($slot['positions'] as $pos) {
            $bx  = $pos['x'] / 100 * 1280;
            $by  = $pos['y'] / 100 * 720;
            $bw  = $pos['w'] / 100 * 1280;
            $top = $by + $bw;

            $s .= sprintf('<ellipse cx="%.1f" cy="%.1f" rx="%.1f" ry="%.1f" fill="#000000" opacity="0.12"/>', $bx + $bw / 2, $top - 4, $bw * 0.42, $bw * 0.06);
            $s .= sprintf('<rect class="alas" x="%.1f" y="%.1f" width="%.1f" height="14" rx="4" fill="%s"/>', $bx - 12, $top, $bw + 24, $p['near']);
            $s .= sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="5" fill="#ffffff" opacity="0.35"/>', $bx - 12, $top, $bw + 24);
            $legs = min(36.0, max(0.0, 712 - $top - 14));

            if ($legs > 2) {
                $s .= sprintf('<rect x="%.1f" y="%.1f" width="8" height="%.1f" fill="%s"/>', $bx, $top + 14, $legs, $p['dark']);
                $s .= sprintf('<rect x="%.1f" y="%.1f" width="8" height="%.1f" fill="%s"/>', $bx + $bw - 8, $top + 14, $legs, $p['dark']);
            }

            $s .= "\n";
        }

        // Spanduk judul variasi di atas
        $title = self::wrap($slot['title'], 44, 1)[0] ?? '';
        $s .= "<rect x=\"340\" y=\"6\" width=\"600\" height=\"44\" rx=\"10\" fill=\"{$p['accent']}\"/>";
        $s .= sprintf("<text x=\"640\" y=\"36\" text-anchor=\"middle\" font-size=\"22\" font-weight=\"bold\" fill=\"#ffffff\">%s</text>\n", self::e($title));
        $s .= sprintf("<text x=\"1268\" y=\"712\" text-anchor=\"end\" font-size=\"13\" fill=\"%s\">%s</text>\n", $p['dark'], self::e($slot['asset_key']));

        return $s . $this->ribbon(1280, 720, 16);
    }

    /** Benda Cari Objek 240 × 240, latar transparan; target dan pengecoh identik. */
    private function object(array $slot, array $p): string
    {
        $s = '<defs><filter id="bayang" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="5"/></filter></defs>' . "\n"
            . "<circle cx=\"120\" cy=\"126\" r=\"104\" fill=\"#000000\" opacity=\"0.22\" filter=\"url(#bayang)\"/>\n"
            . "<circle cx=\"120\" cy=\"120\" r=\"104\" fill=\"#ffffff\" fill-opacity=\"0.9\" stroke=\"{$p['far']}\" stroke-width=\"3\"/>\n";

        $s .= '<g class="ikon">' . self::icon(abs(crc32($slot['asset_key'])) % 6, 120, 92, 36, $p['accent'], $p['dark']) . "</g>\n";

        $lines = self::wrap($slot['label'], 16, 2);
        $s .= self::textLines($lines, 120, count($lines) > 1 ? 156 : 166, 19, 22, $p['dark'], 'bold');

        $s .= "<rect x=\"62\" y=\"196\" width=\"116\" height=\"18\" rx=\"9\" fill=\"#e63946\"/>"
            . "<text x=\"120\" y=\"209\" text-anchor=\"middle\" font-size=\"10\" font-weight=\"bold\" fill=\"#ffffff\" letter-spacing=\"0.5\">GAMBAR CONTOH</text>\n";

        return $s;
    }

    /** Gambar pernyataan 480 × 360: bingkai foto, ikon kamera, label, satu baris keterangan. */
    private function statement(array $slot, array $p): string
    {
        $s = "<rect width=\"480\" height=\"360\" fill=\"{$p['light']}\"/>\n"
            . "<rect x=\"26\" y=\"24\" width=\"428\" height=\"312\" rx=\"10\" fill=\"#000000\" opacity=\"0.12\"/>\n"
            . "<rect x=\"20\" y=\"18\" width=\"428\" height=\"312\" rx=\"10\" fill=\"#ffffff\" stroke=\"{$p['far']}\" stroke-width=\"2\"/>\n"
            . "<rect x=\"36\" y=\"34\" width=\"396\" height=\"196\" rx=\"6\" fill=\"{$p['sky']}\"/>\n"
            . "<polygon points=\"36,230 150,130 250,230\" fill=\"{$p['far']}\"/><polygon points=\"180,230 320,110 432,210 432,230\" fill=\"{$p['near']}\"/>\n";

        // Ikon kamera
        $s .= "<g transform=\"translate(234 118)\"><rect x=\"-44\" y=\"-28\" width=\"88\" height=\"58\" rx=\"10\" fill=\"{$p['dark']}\"/>"
            . "<rect x=\"-18\" y=\"-38\" width=\"36\" height=\"14\" rx=\"4\" fill=\"{$p['dark']}\"/>"
            . "<circle cx=\"0\" cy=\"1\" r=\"20\" fill=\"#ffffff\"/><circle cx=\"0\" cy=\"1\" r=\"12\" fill=\"{$p['accent']}\"/></g>\n";

        $label = self::wrap($slot['label'], 30, 1)[0] ?? '';
        // Catatan dalam kurung untuk admin (mis. "sengaja tidak sesuai bacaan") tidak
        // ditampilkan: siswa melihat gambar ini dan catatan itu membocorkan kunci.
        $note = trim((string) preg_replace('/\s*\([^)]*\)/u', '', $slot['description']));
        $note = self::wrap($note !== '' ? $note : $slot['asset_key'], 52, 1)[0] ?? '';

        $s .= sprintf("<text x=\"234\" y=\"268\" text-anchor=\"middle\" font-size=\"22\" font-weight=\"bold\" fill=\"%s\">%s</text>\n", $p['dark'], self::e($label));
        $s .= sprintf("<text x=\"234\" y=\"298\" text-anchor=\"middle\" font-size=\"13\" fill=\"%s\">%s</text>\n", $p['near'], self::e($note));

        return $s . $this->ribbon(480, 360, 12);
    }

    /** Pita "GAMBAR CONTOH" diagonal di pojok kanan atas. */
    private function ribbon(int $w, int $h, int $font): string
    {
        $len = $font * 11;
        $off = $len * 0.36;

        return sprintf(
            '<g transform="translate(%.1f %.1f) rotate(45)"><rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#e63946"/>'
            . '<text x="0" y="%.1f" text-anchor="middle" font-size="%d" font-weight="bold" fill="#ffffff" letter-spacing="1">GAMBAR CONTOH</text></g>' . "\n",
            $w - $off,
            $off,
            -$len / 2 - $font,
            -$font,
            $len + 2 * $font,
            $font * 2,
            $font * 0.36,
            $font,
        );
    }

    private function stupa(float $cx, float $base, float $k, string $fill, string $dark): string
    {
        return sprintf(
            '<g transform="translate(%.1f %.1f) scale(%.2f)" fill="%s"><rect x="-160" y="-40" width="320" height="40"/><rect x="-130" y="-80" width="260" height="40"/>'
            . '<path d="M-100 -80 A100 90 0 0 1 100 -80 Z"/><rect x="-14" y="-205" width="28" height="40"/><polygon points="0,-260 -10,-205 10,-205"/>'
            . '<g fill="%s"><rect x="-120" y="-32" width="18" height="24"/><rect x="-60" y="-32" width="18" height="24"/><rect x="42" y="-32" width="18" height="24"/><rect x="102" y="-32" width="18" height="24"/></g></g>' . "\n",
            $cx,
            $base,
            $k,
            $fill,
            $dark,
        );
    }

    private function temple(float $cx, float $base, float $k, string $fill, string $accent): string
    {
        return sprintf(
            '<g transform="translate(%.1f %.1f) scale(%.2f)" fill="%s"><rect x="-90" y="-50" width="180" height="50"/><rect x="-70" y="-120" width="140" height="70"/>'
            . '<rect x="-50" y="-165" width="100" height="45"/><rect x="-30" y="-200" width="60" height="35"/><polygon points="0,-240 -18,-200 18,-200"/>'
            . '<rect x="-18" y="-105" width="36" height="55" fill="%s"/></g>' . "\n",
            $cx,
            $base,
            $k,
            $fill,
            $accent,
        );
    }

    /** Satu dari enam ikon geometris sederhana. */
    private static function icon(int $n, float $cx, float $cy, float $r, string $fill, string $dark): string
    {
        $poly = static function (int $sides, float $rot) use ($cx, $cy, $r): string {
            $pts = [];

            for ($i = 0; $i < $sides; $i++) {
                $a     = deg2rad($rot + $i * 360 / $sides);
                $pts[] = sprintf('%.1f,%.1f', $cx + cos($a) * $r, $cy + sin($a) * $r);
            }

            return implode(' ', $pts);
        };

        $star = [];

        for ($i = 0; $i < 10; $i++) {
            $a      = deg2rad(-90 + $i * 36);
            $rr     = $i % 2 === 0 ? $r : $r * 0.45;
            $star[] = sprintf('%.1f,%.1f', $cx + cos($a) * $rr, $cy + sin($a) * $rr);
        }

        return match ($n) {
            0       => sprintf('<polygon points="%s" fill="%s"/>', $poly(3, -90), $fill),
            1       => sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" rx="8" fill="%s"/>', $cx - $r * 0.8, $cy - $r * 0.8, $r * 1.6, $r * 1.6, $fill),
            2       => sprintf('<polygon points="%s" fill="%s"/>', $poly(4, -90), $fill),
            3       => sprintf('<polygon points="%s" fill="%s"/>', $poly(6, 0), $fill),
            4       => sprintf('<polygon points="%s" fill="%s"/>', implode(' ', $star), $fill),
            default => sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="none" stroke="%s" stroke-width="12"/><circle cx="%.1f" cy="%.1f" r="8" fill="%s"/>', $cx, $cy, $r * 0.8, $fill, $cx, $cy, $dark),
        } . "\n";
    }

    // ------------------------------------------------------------ berkas

    /** Tulis SVG dan daftarkan sebagai gambar contoh. Null = berhasil, selain itu pesan galat. */
    private function write(array $slot): ?string
    {
        $dir = FCPATH . $this->dir;

        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return 'folder ' . $this->dir . ' tidak dapat dibuat';
        }

        $relative = $this->dir . (preg_replace('/[^a-z0-9._-]+/i', '-', $slot['asset_key']) ?? $slot['asset_key']) . '.svg';

        if (@file_put_contents(FCPATH . $relative, $this->svg($slot)) === false) {
            return 'berkas ' . $relative . ' tidak dapat ditulis';
        }

        $stored = (new MediaStore())->registerFile($relative, $slot['asset_key'], ['image'], null, true);

        if ($stored['error'] !== null) {
            return $stored['error'];
        }

        $this->db->table('media_assets')->where('id', $stored['id'])->update([
            'is_placeholder' => 1,
            'is_active'      => 1,
            'credit'         => self::CREDIT,
            'width_px'       => $slot['width'],
            'height_px'      => $slot['height'],
        ]);

        return null;
    }

    /**
     * Status baris media: `real` bila bukan gambar contoh dan berkasnya ada
     * (gambar asli, aktif atau dinonaktifkan admin — tidak pernah ditimpa),
     * `mockup` bila gambar contoh yang berkasnya masih ada dan aktif, selain
     * itu `empty` (slot impor tanpa berkas, atau mockup yang berkasnya hilang).
     *
     * @param array<string, mixed> $row
     */
    private function status(array $row): string
    {
        $path   = (string) $row['storage_path'];
        $exists = $path !== '' && is_file(FCPATH . ltrim($path, '/'));

        if ((int) $row['is_placeholder'] === 1) {
            return $exists && (int) $row['is_active'] === 1 ? 'mockup' : 'empty';
        }

        return $exists ? 'real' : 'empty';
    }

    /** @param array<string, mixed> $node */
    private function variantTitle(array $node, string $fallback): string
    {
        $variants = (array) (json_decode((string) $node['config_json'], true)['variants'] ?? []);
        ksort($variants);

        foreach ($variants as $variant) {
            $title = trim((string) ($variant['title_id'] ?? ''));

            if ($title !== '') {
                return $title;
            }
        }

        return trim((string) $node['title_id']) !== '' ? (string) $node['title_id'] : $fallback;
    }

    /**
     * @param list<array{x: float, y: float, w: float}> $positions
     *
     * @return list<array{x: float, y: float, w: float}>
     */
    private static function uniquePositions(array $positions): array
    {
        $out = [];

        foreach ($positions as $pos) {
            $out[sprintf('%.2f|%.2f|%.2f', $pos['x'], $pos['y'], $pos['w'])] = $pos;
        }

        return array_values($out);
    }

    // ------------------------------------------------------------- teks

    /**
     * Pecah teks menjadi paling banyak $maxLines baris ≤ $width huruf; sisa
     * diganti elipsis.
     *
     * @return list<string>
     */
    public static function wrap(string $text, int $width, int $maxLines): array
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $line  = '';

        foreach ($words as $word) {
            if (mb_strlen($word) > $width) {
                $word = mb_substr($word, 0, $width - 1) . '…';
            }

            $candidate = $line === '' ? $word : $line . ' ' . $word;

            if (mb_strlen($candidate) <= $width) {
                $line = $candidate;

                continue;
            }

            $lines[] = $line;
            $line    = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        if (count($lines) > $maxLines) {
            $lines                = array_slice($lines, 0, $maxLines);
            $last                 = $lines[$maxLines - 1];
            $lines[$maxLines - 1] = (mb_strlen($last) >= $width ? mb_substr($last, 0, $width - 1) : $last) . '…';
        }

        return $lines;
    }

    /** @param list<string> $lines */
    private static function textLines(array $lines, float $x, float $y, int $size, int $lineHeight, string $fill, string $weight = 'normal'): string
    {
        $out = '';

        foreach ($lines as $i => $line) {
            $out .= sprintf('<text x="%.1f" y="%.1f" text-anchor="middle" font-size="%d" font-weight="%s" fill="%s">%s</text>', $x, $y + $i * $lineHeight, $size, $weight, $fill, self::e($line));
        }

        return $out . "\n";
    }

    private static function e(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
