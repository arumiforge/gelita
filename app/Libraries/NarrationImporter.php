<?php

namespace App\Libraries;

use App\Models\AudioAssetModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Impor rekaman narasi naskah cerita (docs/naskah-cerita.md) ke permainan.
 *
 * Nama berkas = kode berkas baris naskah, mis. `intro-01.mp3`,
 * `kenal-magelang-03.mp3`. Kodenya dipetakan ke baris `dialogues` aktif:
 *
 *   intro-NN              → intro        (global)
 *   peta-NN               → map_intro    (global)
 *   kenal-{wilayah}-NN    → region_intro
 *   dialog-{wilayah}-NN   → level_open
 *   tuntas-{wilayah}-NN   → level_done
 *   penutup-NN            → ending       (global)
 *
 * NN = `sequence` dua digit. Kode wilayah dibaca dari tabel `levels`, jadi
 * wilayah baru tidak perlu mengubah kelas ini.
 *
 * Narasi petunjuk arena `cari` memakai pola yang sama, tetapi barisnya
 * butir bank soal, bukan `dialogues`:
 *
 *   petunjuk-{node}-NN    → challenge_items.audio_prompt_id / audio_prompt_en_id
 *
 * {node} = kode tantangan (node_ref(), mis. `tmg-4`) setiap node `cari`
 * aktif. NN = urutan petunjuk target (find_object aktif, dinilai, bukan
 * jebakan) menurut urutan butir (`sequence`, lalu id), dimulai dari 01.
 * Transkripnya teks `prompt_id` / `prompt_en` butir, tokohnya Mbah Kedu,
 * context_code audio `hunt_clue`. Objek jebakan tidak punya petunjuk,
 * jadi tidak punya kode berkas.
 *
 * Dua sumber berkas, satu aturan:
 * - folder public/assets/audio/narasi/{id|en}/ (`php spark
 *   gelita:narration:import`, atau tombol "Impor dari folder" di panel):
 *   berkas didaftarkan di tempatnya, tidak disalin;
 * - unggahan banyak berkas dari panel (Admin\NarrationController): berkas
 *   disalin ke public/assets/uploads/ lewat MediaStore, sama seperti
 *   unggahan media lain, sehingga ikut backup.
 *
 * Untuk setiap berkas yang cocok: `media_assets` dengan asset_key
 * `audio.narasi.{locale}.{kode}` dibuat atau diganti (MediaStore),
 * `audio_assets` dibuat atau diganti (transkrip = teks baris pada bahasa itu,
 * tokoh dari baris, narator = NULL, status `draft`), lalu ditautkan ke
 * `dialogues.audio_{locale}_asset_id` (atau kolom audio petunjuk butir).
 *
 * Rekaman yang berubah kembali ke draft dan harus disetujui ulang. Berkas
 * yang isinya (sha256) sama dengan rekaman terpasang dilewati sehingga
 * persetujuannya tidak hilang saat perintah dijalankan ulang. Dari folder,
 * rekaman yang diunggah lewat panel dan lebih baru daripada berkas folder
 * juga tidak ditimpa.
 *
 * Cara produksi (`audio_assets.production_method`): rekaman bawaan
 * repositori adalah suara sintetis (TTS) dan tercantum di manifest
 * `produksi.json` di folder narasi, per bahasa dan kode, beserta sha256 dan
 * profil suaranya. Berkas yang isinya sama persis dengan entri manifest
 * tercatat `tts` + `voice_profile`; selain itu `own_recording`. Rekaman
 * pengisi suara yang menggantikan berkas bawaan (dari folder maupun panel)
 * otomatis tercatat sebagai rekaman sendiri karena isinya berbeda.
 */
final class NarrationImporter
{
    /** Folder konvensi rekaman, relatif terhadap public/ */
    public const FOLDER = 'assets/audio/narasi/';

    /**
     * Ekstensi yang diterima (jenis audio MediaStore). Urutan = prioritas
     * bila satu kode punya lebih dari satu berkas di folder.
     */
    public const EXTENSIONS = ['mp3', 'm4a', 'ogg', 'wav'];

    /** Konteks dialog → awalan kode berkas. */
    public const PREFIXES = [
        'intro'        => 'intro',
        'map_intro'    => 'peta',
        'region_intro' => 'kenal',
        'level_open'   => 'dialog',
        'level_done'   => 'tuntas',
        'ending'       => 'penutup',
    ];

    /** Awalan kode yang tidak memuat wilayah. */
    private const GLOBAL_PREFIXES = ['intro', 'peta', 'penutup'];

    /** Awalan kode berkas narasi petunjuk arena `cari`: `petunjuk-{node}-NN`. */
    public const CLUE_PREFIX = 'petunjuk';

    /** context_code audio petunjuk (audio_assets) dan konteks barisnya di halaman Narasi. */
    public const CLUE_CONTEXT = 'hunt_clue';

    /** Kolom tautan audio petunjuk per bahasa pada `challenge_items`. */
    public const CLUE_COLUMNS = ['id' => 'audio_prompt_id', 'en' => 'audio_prompt_en_id'];

    /** Manifest rekaman sintetis bawaan, di folder narasi (bukan subfolder bahasa). */
    public const MANIFEST = 'produksi.json';

    private BaseConnection $db;

    private MediaStore $store;

    private string $folder;

    /** @var array<string, array<string, mixed>>|null kode → baris dialogues */
    private ?array $lines = null;

    /** @var list<string>|null */
    private ?array $levelCodes = null;

    /** @var list<string>|null kode node `cari` aktif, mis. `tmg-4` */
    private ?array $clueNodes = null;

    /** @var array<string, array<string, mixed>>|null bahasa → kode → entri manifest produksi */
    private ?array $manifest = null;

    public function __construct(?BaseConnection $db = null, ?MediaStore $store = null, string $folder = self::FOLDER)
    {
        $this->db     = $db ?? db_connect();
        $this->store  = $store ?? new MediaStore();
        $this->folder = rtrim(str_replace('\\', '/', $folder), '/') . '/';

        helper('content');   // node_ref()
    }

    /** Kode berkas satu baris naskah, mis. `dialog-magelang-07`; null untuk konteks lain. */
    public static function codeFor(string $context, ?string $levelCode, int $sequence): ?string
    {
        $prefix = self::PREFIXES[$context] ?? null;

        if ($prefix === null || $sequence < 1) {
            return null;
        }

        $global = in_array($prefix, self::GLOBAL_PREFIXES, true);

        if ($global !== ($levelCode === null)) {
            return null;
        }

        return $prefix . ($global ? '' : '-' . $levelCode) . '-' . sprintf('%02d', $sequence);
    }

    /** Kode berkas petunjuk ke-$number (mulai 1) satu node `cari`, mis. `petunjuk-tmg-4-01`. */
    public static function clueCode(string $nodeRef, int $number): string
    {
        return self::CLUE_PREFIX . '-' . $nodeRef . '-' . sprintf('%02d', $number);
    }

    public static function assetKey(string $locale, string $code): string
    {
        return 'audio.narasi.' . $locale . '.' . $code;
    }

    /** Folder rekaman satu bahasa, relatif terhadap public/. */
    public function folderFor(string $locale): string
    {
        return $this->folder . $locale . '/';
    }

    /**
     * Cara produksi sebuah berkas menurut manifest produksi.json: `tts` bila
     * isinya (sha256) sama dengan entri manifest untuk kode itu, selain itu
     * `own_recording`.
     *
     * @return array{production_method: string, voice_profile: string|null}
     */
    public function production(string $locale, string $code, string $sha256): array
    {
        if ($this->manifest === null) {
            $path           = FCPATH . $this->folder . self::MANIFEST;
            $data           = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
            $this->manifest = is_array($data) && is_array($data['files'] ?? null) ? $data['files'] : [];
        }

        $entry = $this->manifest[$locale][$code] ?? null;

        if (is_array($entry) && is_string($entry['sha256'] ?? null) && hash_equals(strtolower($entry['sha256']), $sha256)) {
            $profile = trim((string) ($entry['voice_profile'] ?? ''));

            return ['production_method' => 'tts', 'voice_profile' => $profile === '' ? null : mb_substr($profile, 0, 200)];
        }

        return ['production_method' => 'own_recording', 'voice_profile' => null];
    }

    /**
     * Baris naskah aktif per kode berkas, urut naskah: konteks, wilayah, urutan;
     * petunjuk arena `cari` (clueLines()) di akhir.
     *
     * @return array<string, array<string, mixed>> kolom dialogues + `code`, `level_code`, `level_name`, `table`
     */
    public function lines(): array
    {
        if ($this->lines !== null) {
            return $this->lines;
        }

        $rows = $this->db->table('dialogues d')
            ->select('d.*, l.code AS level_code, l.name_id AS level_name, l.sequence AS level_sequence')
            ->join('levels l', 'l.id = d.level_id', 'left')
            ->where('d.is_active', 1)
            ->whereIn('d.context_code', array_keys(self::PREFIXES))
            ->orderBy('d.id', 'ASC')
            ->get()
            ->getResultArray();

        $order = array_flip(array_keys(self::PREFIXES));

        usort($rows, static fn (array $a, array $b): int => [
            $order[$a['context_code']], (int) ($a['level_sequence'] ?? 0), (int) $a['sequence'], (int) $a['id'],
        ] <=> [
            $order[$b['context_code']], (int) ($b['level_sequence'] ?? 0), (int) $b['sequence'], (int) $b['id'],
        ]);

        $this->lines = [];

        foreach ($rows as $row) {
            if ($row['level_id'] !== null && $row['level_code'] === null) {
                continue;   // wilayahnya sudah tidak ada
            }

            $code = self::codeFor((string) $row['context_code'], $row['level_code'] === null ? null : (string) $row['level_code'], (int) $row['sequence']);

            // Nomor urut ganda pada baris global: id terkecil yang dipakai (sama dengan StorySync)
            if ($code !== null && ! isset($this->lines[$code])) {
                $this->lines[$code] = $row + ['code' => $code, 'table' => 'dialogues'];
            }
        }

        $this->lines += $this->clueLines();

        return $this->lines;
    }

    /**
     * Petunjuk arena `cari` sebagai baris narasi, urut wilayah, node, butir.
     * Bentuknya sama dengan baris dialogues agar katalog dan laporan tidak
     * perlu membedakannya: `text_*` = prompt butir, `audio_{locale}_asset_id`
     * = kolom CLUE_COLUMNS; `table`, `node_id`, `node_ref`, dan `item_key`
     * menunjuk butirnya.
     *
     * @return array<string, array<string, mixed>>
     */
    private function clueLines(): array
    {
        $rows = $this->db->table('challenge_items ci')
            ->select('ci.id, ci.item_key, ci.prompt_id, ci.prompt_en, ci.config_json, ci.audio_prompt_id, ci.audio_prompt_en_id')
            ->select('n.id AS node_id, n.sequence AS node_sequence, n.level_id, l.code AS level_code, l.name_id AS level_name, l.sequence AS level_sequence')
            ->join('challenge_nodes n', 'n.id = ci.challenge_node_id')
            ->join('levels l', 'l.id = n.level_id')
            ->where('n.engine_type', 'cari')
            ->where('n.is_active', 1)
            ->where('ci.is_active', 1)
            ->where('ci.scorable', 1)
            ->where('ci.interaction_type', 'find_object')
            ->orderBy('l.sequence', 'ASC')
            ->orderBy('n.sequence', 'ASC')
            ->orderBy('ci.sequence', 'ASC')
            ->orderBy('ci.id', 'ASC')
            ->get()
            ->getResultArray();

        $lines   = [];
        $counter = [];

        foreach ($rows as $row) {
            $config = json_decode((string) ($row['config_json'] ?? ''), true);

            // Jebakan tidak punya petunjuk (ChallengeItem::isDecoy())
            if (is_array($config) && ($config['decoy'] ?? null) === true) {
                continue;
            }

            $nodeRef = node_ref((string) $row['level_code'], (int) $row['node_sequence']);
            $number  = $counter[$nodeRef] = ($counter[$nodeRef] ?? 0) + 1;
            $code    = self::clueCode($nodeRef, $number);

            $lines[$code] = [
                'id'                => (int) $row['id'],
                'table'             => 'challenge_items',
                'level_id'          => (int) $row['level_id'],
                'level_code'        => (string) $row['level_code'],
                'level_name'        => (string) $row['level_name'],
                'level_sequence'    => (int) $row['level_sequence'],
                'context_code'      => self::CLUE_CONTEXT,
                'sequence'          => $number,
                'character_code'    => 'mbah_kedu',
                'pose'              => null,
                'effect'            => null,
                'title_id'          => null,
                'title_en'          => null,
                'text_id'           => (string) $row['prompt_id'],
                'text_en'           => $row['prompt_en'],
                'audio_id_asset_id' => $row['audio_prompt_id'],
                'audio_en_asset_id' => $row['audio_prompt_en_id'],
                'node_id'           => (int) $row['node_id'],
                'node_ref'          => $nodeRef,
                'item_key'          => (string) $row['item_key'],
                'code'              => $code,
            ];
        }

        return $lines;
    }

    /**
     * Cocokkan nama berkas dengan baris naskah (huruf besar/kecil diabaikan).
     *
     * @return array{code: string, error: null, suggestion: null}|array{code: null, error: string, suggestion: string|null}
     */
    public function match(string $filename): array
    {
        $name      = strtolower(trim(basename(str_replace('\\', '/', $filename))));
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $base      = pathinfo($name, PATHINFO_FILENAME);
        $lines     = $this->lines();

        if (! in_array($extension, self::EXTENSIONS, true)) {
            return $this->unknown(
                $extension === '' ? 'nama berkas tanpa jenis berkas (mis. .mp3)' : 'jenis berkas .' . $extension . ' tidak didukung (yang diterima: ' . implode(', ', self::EXTENSIONS) . ')',
                in_array($base, array_keys($lines), true) ? $base . '.mp3' : null,
            );
        }

        if (preg_match('/^(intro|peta|penutup)-(\d{2})$/', $base, $m) === 1) {
            $code = $base;
        } elseif (preg_match('/^(kenal|dialog|tuntas)-(.+)-(\d{2})$/', $base, $m) === 1) {
            if (! in_array($m[2], $this->levelCodes(), true)) {
                return $this->unknown('wilayah "' . $m[2] . '" tidak ada di permainan', $this->suggest($base, $extension));
            }

            $code = $base;
        } elseif (preg_match('/^' . self::CLUE_PREFIX . '-(.+)-(\d{2})$/', $base, $m) === 1) {
            if (! in_array($m[1], $this->clueNodes(), true)) {
                return $this->unknown('tantangan "' . $m[1] . '" bukan tantangan Cari Objek yang aktif', $this->suggest($base, $extension));
            }

            $code = $base;
        } else {
            return $this->unknown('nama berkas tidak sesuai pola nama rekaman (lihat Unduh daftar rekaman)', $this->suggest($base, $extension));
        }

        if (! isset($lines[$code])) {
            $where = str_starts_with($code, self::CLUE_PREFIX . '-') ? 'di petunjuk target (soal Cari Objek yang aktif)' : 'di naskah cerita';

            return $this->unknown('baris ' . $code . ' tidak ada ' . $where, $this->suggest($base, $extension));
        }

        return ['code' => $code, 'error' => null, 'suggestion' => null];
    }

    /**
     * Impor seluruh berkas di public/assets/audio/narasi/{locale}/.
     *
     * @return array<string, mixed> lihat report()
     */
    public function importFolder(string $locale, bool $dryRun = false, ?int $staffId = null): array
    {
        $report = $this->report($locale, 'folder', $dryRun);
        $dir    = FCPATH . $this->folderFor($locale);
        $names  = is_dir($dir) ? (scandir($dir) ?: []) : [];
        $byCode = [];

        foreach ($names as $name) {
            if (str_starts_with($name, '.') || ! is_file($dir . $name)) {
                continue;
            }

            $report['files']++;
            $match = $this->match($name);

            if ($match['code'] === null) {
                $report['unknown'][] = ['file' => $name, 'reason' => $match['error'], 'suggestion' => $match['suggestion']];

                continue;
            }

            $byCode[$match['code']][] = $name;
        }

        foreach ($byCode as $code => $files) {
            // Satu kode, beberapa berkas: ambil menurut prioritas ekstensi, laporkan sisanya
            usort($files, static fn (string $a, string $b): int => array_search(strtolower(pathinfo($a, PATHINFO_EXTENSION)), self::EXTENSIONS, true)
                <=> array_search(strtolower(pathinfo($b, PATHINFO_EXTENSION)), self::EXTENSIONS, true));

            foreach (array_slice($files, 1) as $duplicate) {
                $report['unknown'][] = ['file' => $duplicate, 'reason' => 'berkas ganda: ' . $code . ' sudah diambil dari ' . $files[0], 'suggestion' => null];
            }

            $this->importOne($code, $dir . $files[0], $files[0], $this->folderFor($locale) . $files[0], $locale, $dryRun, $staffId, $report);
        }

        return $this->finish($report);
    }

    /**
     * Impor berkas unggahan. Controller wajib sudah memeriksa bahwa setiap
     * `path` adalah berkas unggahan sah (UploadedFile::isValid()).
     *
     * @param list<array{name: string, path: string, error?: string|null}> $files nama asli + berkas sementara
     *
     * @return array<string, mixed> lihat report()
     */
    public function importFiles(array $files, string $locale, bool $dryRun = false, ?int $staffId = null): array
    {
        $report = $this->report($locale, 'upload', $dryRun);
        $seen   = [];

        foreach ($files as $file) {
            $report['files']++;
            $name = basename(str_replace('\\', '/', (string) $file['name']));

            if (! empty($file['error'])) {
                $report['failed'][] = ['file' => $name, 'error' => (string) $file['error']];

                continue;
            }

            $match = $this->match($name);

            if ($match['code'] === null) {
                $report['unknown'][] = ['file' => $name, 'reason' => $match['error'], 'suggestion' => $match['suggestion']];

                continue;
            }

            if (isset($seen[$match['code']])) {
                $report['unknown'][] = ['file' => $name, 'reason' => 'berkas ganda: ' . $match['code'] . ' sudah diambil dari ' . $seen[$match['code']], 'suggestion' => null];

                continue;
            }

            $seen[$match['code']] = $name;
            $this->importOne($match['code'], (string) $file['path'], $name, null, $locale, $dryRun, $staffId, $report);
        }

        return $this->finish($report);
    }

    // -------------------------------------------------------------- bantuan

    /**
     * @param string|null          $relative path relatif public/ bila berkas didaftarkan di tempat (folder)
     * @param array<string, mixed> $report
     */
    private function importOne(string $code, string $source, string $name, ?string $relative, string $locale, bool $dryRun, ?int $staffId, array &$report): void
    {
        $line   = $this->lines()[$code];
        $key    = self::assetKey($locale, $code);
        $media  = $this->db->table('media_assets')->where('asset_key', $key)->get()->getRowArray();
        $audio  = $media === null ? null : $this->db->table('audio_assets')->where('media_asset_id', (int) $media['id'])->get()->getRowArray();
        $sha    = is_file($source) ? hash_file('sha256', $source) : null;

        if ($sha === null) {
            $report['failed'][] = ['file' => $name, 'error' => 'berkas tidak dapat dibaca'];

            return;
        }

        // Rekaman yang sama persis dan berkasnya masih ada: status persetujuannya dipertahankan.
        // Berkas terpasang yang hilang dari disk dipulihkan (diganti) dari berkas ini.
        if ($media !== null && $audio !== null && (string) $media['sha256'] === $sha && (int) $media['is_active'] === 1
            && is_file(FCPATH . $media['storage_path']) && hash_file('sha256', FCPATH . $media['storage_path']) === $sha) {
            $this->link($line, $locale, (int) $audio['id'], $dryRun, $report);
            $report['unchanged'][] = $code;

            return;
        }

        // Dari folder: rekaman panel yang lebih baru dari berkas folder tidak ditimpa
        if ($relative !== null && $media !== null && $media['storage_path'] !== $relative
            && is_file(FCPATH . $media['storage_path'])
            && strtotime((string) ($media['updated_at'] ?? '')) >= (int) filemtime($source)) {
            if ($audio !== null) {
                $this->link($line, $locale, (int) $audio['id'], $dryRun, $report);
            }

            $report['kept'][] = $code;

            return;
        }

        $category = $audio === null ? 'created' : 'replaced';

        if ($dryRun) {
            $report[$category][] = $code;
            $report['linked'][$code] = true;

            return;
        }

        $stored = $relative !== null
            ? $this->store->registerFile($relative, $key, ['audio'], $staffId, true, $locale)
            : $this->store->storeCopy($source, $key, ['audio'], $staffId, true, $locale);

        if ($stored['error'] !== null) {
            $report['failed'][] = ['file' => $name, 'error' => $stored['error']];

            return;
        }

        $mediaId = (int) $stored['id'];
        $path    = (string) $this->db->table('media_assets')->select('storage_path')->where('id', $mediaId)->get()->getRow('storage_path');
        $text    = trim((string) ($line['text_' . $locale] ?? '')) ?: (string) $line['text_id'];
        $models  = model(AudioAssetModel::class, false);
        $payload = [
            'media_asset_id'    => $mediaId,
            'locale'            => $locale,
            'character_code'    => in_array($line['character_code'], config('Gelita')->characters, true) ? (string) $line['character_code'] : null,
            'context_code'      => (string) $line['context_code'],
            'transcript'        => $text,
            ...$this->production($locale, $code, $sha),
            'duration_ms'       => $this->store->durationMs(FCPATH . $path),
            'approval_status'   => 'draft',
            'approved_by'       => null,
            'approved_at'       => null,
        ];

        // audio_assets.media_asset_id unik: rekaman yang diganti memakai baris yang sama
        $existing = $models->where('media_asset_id', $mediaId)->first();
        $audioId  = $existing === null
            ? $models->insert($payload, true)
            : ($models->update((int) $existing['id'], $payload) ? (int) $existing['id'] : false);

        if ($audioId === false) {
            $report['failed'][] = ['file' => $name, 'error' => 'rekaman belum dapat disimpan: ' . implode(' ', $models->errors())];

            return;
        }

        $report['written'] = true;
        $this->link($line, $locale, (int) $audioId, false, $report);
        $report[$category][] = $code;
    }

    /**
     * Tautkan audio ke baris: `dialogues.audio_{locale}_asset_id`, atau kolom
     * CLUE_COLUMNS butir untuk petunjuk arena `cari`.
     *
     * @param array<string, mixed> $line
     * @param array<string, mixed> $report
     */
    private function link(array $line, string $locale, int $audioId, bool $dryRun, array &$report): void
    {
        $report['linked'][$line['code']] = true;
        $column = 'audio_' . $locale . '_asset_id';

        if ((int) ($line[$column] ?? 0) === $audioId) {
            return;
        }

        if (! $dryRun) {
            $table = ($line['table'] ?? 'dialogues') === 'challenge_items' ? 'challenge_items' : 'dialogues';
            $this->db->table($table)->where('id', (int) $line['id'])->update([
                $table === 'challenge_items' ? self::CLUE_COLUMNS[$locale] : $column => $audioId,
            ]);
            $this->lines[$line['code']][$column] = $audioId;
            $report['written'] = true;
        }
    }

    /**
     * Laporan kosong.
     *
     * @return array{
     *     locale: string, source: string, dry_run: bool, files: int,
     *     created: list<string>, replaced: list<string>, unchanged: list<string>, kept: list<string>,
     *     unknown: list<array{file: string, reason: string, suggestion: string|null}>,
     *     failed: list<array{file: string, error: string}>, missing: list<string>,
     *     linked: array<string, true>, written: bool
     * }
     */
    private function report(string $locale, string $source, bool $dryRun): array
    {
        return [
            'locale'    => $locale,
            'source'    => $source,
            'dry_run'   => $dryRun,
            'files'     => 0,
            'created'   => [],
            'replaced'  => [],
            'unchanged' => [],
            'kept'      => [],
            'unknown'   => [],
            'failed'    => [],
            'missing'   => [],
            'linked'    => [],
            'written'   => false,
        ];
    }

    /**
     * Baris naskah yang (setelah impor ini) masih belum punya rekaman pada
     * bahasa itu, lalu flush cache konten bila ada yang ditulis.
     *
     * @param array<string, mixed> $report
     *
     * @return array<string, mixed>
     */
    private function finish(array $report): array
    {
        $column = 'audio_' . $report['locale'] . '_asset_id';
        $order  = array_flip(array_keys($this->lines()));

        // Urut naskah, bukan urut nama berkas
        foreach (['created', 'replaced', 'unchanged', 'kept'] as $key) {
            usort($report[$key], static fn (string $a, string $b): int => $order[$a] <=> $order[$b]);
        }

        foreach ($this->lines() as $code => $line) {
            if (empty($line[$column]) && ! isset($report['linked'][$code])) {
                $report['missing'][] = $code;
            }
        }

        if ($report['written']) {
            service('contentRepository')->flush();
        }

        unset($report['linked']);

        return $report;
    }

    /** @return list<string> kode node `cari` aktif (node_ref()) */
    private function clueNodes(): array
    {
        if ($this->clueNodes !== null) {
            return $this->clueNodes;
        }

        $rows = $this->db->table('challenge_nodes n')
            ->select('n.sequence, l.code')
            ->join('levels l', 'l.id = n.level_id')
            ->where('n.engine_type', 'cari')
            ->where('n.is_active', 1)
            ->get()
            ->getResultArray();

        return $this->clueNodes = array_map(static fn (array $row): string => node_ref((string) $row['code'], (int) $row['sequence']), $rows);
    }

    /** @return list<string> */
    private function levelCodes(): array
    {
        return $this->levelCodes ??= array_map('strval', array_column(
            $this->db->table('levels')->select('code')->get()->getResultArray(),
            'code',
        ));
    }

    /** Kode baris terdekat (jarak Levenshtein kecil) sebagai saran nama berkas. */
    private function suggest(string $base, string $extension): ?string
    {
        $normalized = trim((string) preg_replace('/[\s_]+/', '-', $base), '-');
        $normalized = (string) preg_replace_callback('/-(\d)$/', static fn (array $m): string => '-0' . $m[1], $normalized);
        $limit      = min(5, max(2, intdiv(strlen($base), 4)));
        $best       = null;
        $bestD      = PHP_INT_MAX;

        foreach (array_keys($this->lines()) as $code) {
            $d = levenshtein($normalized, $code);

            if ($d <= $limit && $d < $bestD) {
                [$best, $bestD] = [$code, $d];
            }
        }

        return $best === null ? null : $best . '.' . ($extension !== '' ? $extension : 'mp3');
    }

    /** @return array{code: null, error: string, suggestion: string|null} */
    private function unknown(string $reason, ?string $suggestion): array
    {
        return ['code' => null, 'error' => $reason, 'suggestion' => $suggestion];
    }
}
