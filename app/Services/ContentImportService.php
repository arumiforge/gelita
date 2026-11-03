<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\HintModel;
use App\Models\ItemErrorCodeModel;
use App\Models\PostPartModel;
use App\Models\PostSceneModel;
use App\Models\ScenarioOptionModel;
use App\Libraries\ErrorCodes;
use App\Libraries\SurfaceLoad;
use App\Models\LearningIndicatorModel;
use App\Libraries\BankRules;
use App\Libraries\BankSource;
use App\Libraries\BankState;
use App\Libraries\BankWorkbookGuide;
use App\Libraries\DifficultyRules;
use App\Libraries\MediaLink;
use App\Libraries\MediaStore;
use App\Models\LevelModel;
use App\Models\LibraryMediaModel;
use App\Models\LibraryPageModel;
use App\Models\MediaAssetModel;
use App\Models\ReadingPassageModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Memuat workbook bank soal (XLSX) ke tabel konten dalam satu transaction.
 * Format sheet bank soal kelas 5 ditetapkan Dok 2 §7.3; arti tiap kolom
 * (dipakai sheet PETUNJUK dan KAMUS_KOLOM) ada di BankWorkbookGuide.
 *
 * Selain pemeriksaan per baris, keadaan SESUDAH impor setiap tantangan yang
 * disentuh workbook (isi database ditimpa baris workbook) diperiksa dengan
 * aturan bank soal kelas 5 (App\Libraries\BankRules, Dok 2 §7.5); galatnya
 * menolak impor.
 *
 * Sheet `library` dan `library_media` (Pustaka Kedu) tetap didukung untuk
 * docs/pustaka/gelita-pustaka.xlsx; workbook bank soal kelas 5 tidak
 * memilikinya, sehingga impornya tidak pernah menyentuh Pustaka.
 */
class ContentImportService
{
    /** @var list<int> id library_media video luar tanpa poster yang ditulis impor ini */
    private array $thumbnailQueue = [];

    /** Header per sheet; kolom di luar daftar ini diabaikan. Sheet lain (mis. PETUNJUK) juga diabaikan. */
    public const SHEETS = [
        'nodes'       => ['node_ref', 'title_id', 'title_en', 'instruction_id', 'instruction_en', 'description_id', 'description_en', 'distractor_count', 'scene_media_key', 'scene_media_label', 'scene_media_note', 'background_media_key', 'variant_code', 'indicator'],
        'variants'    => ['node_ref', 'variant_group', 'title_id', 'title_en'],
        'distractors' => ['node_ref', 'variant_group', 'text_id', 'text_en'],
        'passages'    => ['passage_key', 'level_code', 'title_id', 'title_en', 'body_id', 'body_en', 'media_asset_key', 'reference_source', 'text_genre', 'layout'],
        'items'       => ['item_key', 'node_ref', 'variant_group', 'sequence', 'interaction_type', 'indicator', 'bloom_level', 'skill', 'prompt_id', 'prompt_en', 'passage_key', 'answer_id', 'answer_en', 'x', 'y', 'w', 'decoy', 'wrong_feedback_id', 'wrong_feedback_en', 'media_asset_key', 'media_label', 'media_note', 'scorable', 'is_active', 'review_status', 'review_note', 'reference_source',
            'round', 'dimension', 'subaspect', 'akm_level', 'pirls_process', 'label_source', 'testlet_key', 'error_code', 'd3_clue_type', 'd2_topic'],
        'hints'       => ['node_ref', 'item_key', 'sequence', 'text_id', 'text_en'],
        // Research revision 10, part 5 (31 §6.3)
        'cloze_matrix'     => ['node_ref', 'variant_group', 'item_key', 'word_id', 'error_code'],
        'object_codes'     => ['item_key', 'object_key', 'error_code'],
        'post_scenes'      => ['node_ref', 'variant_group', 'title_id', 'title_en', 'text_genre', 'media_asset_key', 'media_label', 'media_note', 'reference_source'],
        'post_parts'       => ['node_ref', 'variant_group', 'part_key', 'kind', 'label_id', 'label_en', 'text_id', 'text_en', 'x', 'y', 'w', 'h'],
        'part_codes'       => ['item_key', 'part_key', 'error_code', 'main'],
        'scenarios'        => ['item_key', 'd2_topic', 'steps', 'explanation_id', 'explanation_en'],
        'scenario_options' => ['item_key', 'kind', 'option_key', 'sequence', 'text_id', 'text_en', 'score', 'code', 'feedback_id', 'feedback_en'],
        'indicators'       => ['code', 'name_id', 'name_en', 'description_id', 'description_en', 'domain'],
        'library'       => ['level_code', 'sequence', 'title_id', 'title_en', 'body_id', 'body_en', 'is_active'],
        'library_media' => ['level_code', 'page_sequence', 'sequence', 'media_kind', 'media_asset_key', 'external_url', 'poster_media_key', 'caption_id', 'caption_en', 'credit'],
    ];

    /** Sheet Pustaka: workbook bank soal tidak memilikinya, workbook Pustaka hanya keduanya. */
    private const LIBRARY_SHEETS = ['library', 'library_media'];

    /** Sheets of 31 §6.3: optional, an older bank workbook has none of them. */
    private const OPTIONAL_SHEETS = ['cloze_matrix', 'object_codes', 'post_scenes', 'post_parts', 'part_codes', 'scenarios', 'scenario_options', 'indicators'];

    /** Indicator codes of the old bank, made inactive (not deleted) once a workbook brings the `indicators` sheet (31 §13.1). */
    private const OLD_INDICATORS = ['budaya', 'literasi', 'sikap'];


    /** Pesan untuk jenis soal di luar Config\Gelita::$interactionTypes (Dok 2 §6.5). */
    public const UNKNOWN_TYPE = 'Jenis soal tidak dikenal.';

    /**
     * Membaca workbook, memvalidasi seluruh baris, TANPA menulis database.
     *
     * @return array{ok: bool, summary: array<string, mixed>,
     *               errors: list<array{sheet: string, row: int, message: string}>,
     *               warnings: list<array{sheet: string, row: int, message: string}>}
     */
    public function preview(string $xlsxPath): array
    {
        $parsed = $this->parse($xlsxPath);

        return [
            'ok'       => $parsed['errors'] === [],
            'summary'  => $parsed['summary'],
            'errors'   => $parsed['errors'],
            'warnings' => $parsed['warnings'],
        ];
    }

    /**
     * Menjalankan impor dalam satu transaction; gagal satu baris → batal semua.
     * Kunci jawaban item yang sudah pernah dijawab tidak boleh berubah.
     *
     * @return array{ok: bool, written: array<string, int>,
     *               errors: list<array{sheet: string, row: int, message: string}>,
     *               warnings: list<array{sheet: string, row: int, message: string}>}
     */
    public function import(string $xlsxPath, int $staffId, string $mode = 'upsert'): array
    {
        if (! in_array($mode, ['upsert', 'insert'], true)) {
            throw new \InvalidArgumentException("Mode impor '{$mode}' tidak dikenali.");
        }

        $parsed = $this->parse($xlsxPath);

        if ($parsed['errors'] !== []) {
            return ['ok' => false, 'written' => [], 'errors' => $parsed['errors'], 'warnings' => $parsed['warnings']];
        }

        $db = db_connect();
        $db->transBegin();

        $this->thumbnailQueue = [];

        try {
            $written = $this->write($parsed, $mode);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            return [
                'ok'       => false,
                'written'  => [],
                'errors'   => [['sheet' => '-', 'row' => 0, 'message' => $e->getMessage()]],
                'warnings' => $parsed['warnings'],
            ];
        }

        service('contentRepository')->flush();

        // Poster video luar diunduh server setelah commit; gagal = peringatan saja
        $thumbs = service('videoThumbnail')->fillMissing($this->thumbnailQueue, false, $staffId);

        foreach ($thumbs['errors'] as $message) {
            $parsed['warnings'][] = ['sheet' => 'library_media', 'row' => 0, 'message' => 'Gambar sampul video belum berhasil diambil — ' . rtrim($message, '.')
                . '. Isi kolom poster_media_key (gambar sampul video), atau minta petugas teknis menjalankan `php spark gelita:library:thumbnails` nanti.'];
        }

        model(AuditLogModel::class)->record('content_import', [
            'staff_user_id' => $staffId,
            'target_type'   => 'workbook',
            'target_id'     => basename($xlsxPath),
            'metadata'      => ['file_sha256' => hash_file('sha256', $xlsxPath) ?: null, 'written' => $written],
        ]);

        return ['ok' => true, 'written' => $written, 'errors' => [], 'warnings' => $parsed['warnings']];
    }

    /**
     * Workbook templat: sheet PETUNJUK (cara mengisi, arti tiap sheet dan
     * kolom dalam bahasa Indonesia), header berwarna dengan catatan per kolom,
     * daftar pilihan untuk kolom berkode, dan baris contoh yang saling
     * konsisten sehingga templat yang diunggah apa adanya lolos pratinjau.
     */
    public function template(): string
    {
        $rows = [];

        foreach (self::SHEETS as $name => $headers) {
            $rows[$name] = $this->exampleRows($name, $headers);
        }

        $path = WRITEPATH . 'uploads/gelita-bank-soal-template.xlsx';

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        (new BankWorkbookGuide())->write($rows, $path, [
            'title'    => 'Templat Bank Soal GELITA',
            'subtitle' => 'Baris yang sudah terisi adalah contoh dari bank soal kelas 5 (Susun Gambar Temanggung, Isi Rumpang Magelang, Benar atau Salah Temanggung, Cari Objek Temanggung): boleh dihapus atau ditimpa. Sheet library dan library_media hanya untuk berkas Pustaka. Baca sheet PETUNJUK ini dulu sebelum mengisi.',
        ]);

        return $path;
    }

    // --------------------------------------------------------------- baca

    /**
     * @return array{sheets: array<string, list<array<string, string>>>,
     *               summary: array<string, mixed>,
     *               errors: list<array{sheet: string, row: int, message: string}>,
     *               warnings: list<array{sheet: string, row: int, message: string}>,
     *               nodes: array<string, int>, levels: array<string, int>,
     *               indicators: array<string, int>, media: array<string, int>}
     */
    private function parse(string $xlsxPath): array
    {
        if (! is_file($xlsxPath)) {
            throw new \RuntimeException("Berkas Excel tidak ditemukan: {$xlsxPath}");
        }

        $reader = IOFactory::createReaderForFile($xlsxPath);
        $reader->setReadDataOnly(true);
        $book = $reader->load($xlsxPath);

        $sheets   = [];
        $errors   = [];
        $warnings = [];

        // Workbook bank soal (punya sheet tantangan/soal) wajib lengkap; sheet
        // Pustaka selalu opsional, dan workbook Pustaka tidak butuh sheet soal.
        $isBank = array_filter(
            array_diff(array_keys(self::SHEETS), self::LIBRARY_SHEETS),
            static fn (string $name): bool => $book->sheetNameExists($name),
        ) !== [];

        foreach (self::SHEETS as $name => $headers) {
            $sheets[$name] = $book->sheetNameExists($name)
                ? array_map(
                    static fn (array $row): array => $row + ['__sheet' => $name],
                    $this->readSheet($book->getSheetByName($name)->toArray(null, true, false, false), $headers),
                )
                : [];

            if (! $book->sheetNameExists($name) && $isBank && ! in_array($name, [...self::LIBRARY_SHEETS, ...self::OPTIONAL_SHEETS], true)) {
                $warnings[] = ['sheet' => $name, 'row' => 0, 'message' => 'Sheet ' . $name . ' tidak ada di berkas, jadi dilewati.'];
            }
        }

        // Tingkat Bloom ditulis huruf besar (c2 → C2), variasi dan keterampilan
        // huruf kecil (A → a) sebelum diperiksa dan ditulis. Variasi kosong = `a`.
        foreach ($sheets['items'] as $index => $row) {
            $sheets['items'][$index]['bloom_level']      = strtoupper((string) $row['bloom_level']);
            $sheets['items'][$index]['variant_group']    = strtolower((string) $row['variant_group']) ?: 'a';
            $sheets['items'][$index]['skill']            = strtolower((string) $row['skill']);
            $sheets['items'][$index]['interaction_type'] = strtolower((string) $row['interaction_type']);
        }

        foreach ($sheets['items'] as $index => $row) {
            foreach (['dimension', 'akm_level', 'error_code', 'd3_clue_type', 'd2_topic'] as $column) {
                $sheets['items'][$index][$column] = strtoupper((string) $row[$column]);
            }

            $sheets['items'][$index]['label_source'] = strtolower((string) $row['label_source']);
        }

        foreach (['variants', 'distractors', 'cloze_matrix', 'post_scenes', 'post_parts'] as $name) {
            foreach ($sheets[$name] as $index => $row) {
                $sheets[$name][$index]['variant_group'] = strtolower((string) $row['variant_group']) ?: 'a';
            }
        }

        $levels     = $this->levelIdsByCode();
        $nodes      = $this->nodeIdsByRef($levels);
        $indicators = array_map(static fn ($row): int => (int) $row['id'], model(LearningIndicatorModel::class)->map());
        $media      = model(MediaAssetModel::class)->keyMap();

        // Indicators the workbook brings count as known before they are written
        foreach ($sheets['indicators'] as $row) {
            $indicators[trim((string) $row['code'])] ??= 0;
        }

        // Skenario Digital rows wait for their challenge (part 6): checked, not written
        [$sheets, $pending] = $this->setAsidePending($sheets, $nodes);

        foreach ($pending['refs'] as $ref => $count) {
            $warnings[] = ['sheet' => 'items', 'row' => 0, 'message' => "Tantangan {$ref} (Skenario Digital) belum ada di permainan, jadi {$count} skenario beserta pilihannya sudah diperiksa tetapi belum disimpan. Impor ulang berkas yang sama setelah tantangan kelima dipasang."];
        }

        $context = [
            'levels'     => $levels,
            'nodes'      => $nodes,
            'indicators' => $indicators,
            'media'      => $media,
            'passages'   => $this->collect($sheets['passages'], 'passage_key'),
            'items'      => $this->collect($sheets['items'], 'item_key'),
            'library'    => $this->libraryKeys($sheets['library']),
            'difficulty' => $this->difficultyByPrefix(),
        ];

        foreach (array_keys(self::SHEETS) as $name) {
            foreach ($sheets[$name] as $row) {
                $message = $this->validateRow($name, $row, $context);

                if ($message !== null) {
                    $errors[] = ['sheet' => $name, 'row' => $row['__row'], 'message' => $message];
                }
            }
        }

        $warnings = [...$warnings, ...$this->mediaWarnings($sheets, $media)];

        foreach ($this->duplicateLibraryPages($sheets['library']) as $row) {
            $errors[] = ['sheet' => 'library', 'row' => $row['__row'], 'message' => "Halaman Pustaka {$row['level_code']} nomor {$row['sequence']} ditulis lebih dari sekali. Satu nomor halaman cukup satu baris."];
        }

        foreach ($sheets['items'] as $row) {
            if (($row['review_status'] ?? '') === 'needs_verification') {
                $warnings[] = [
                    'sheet'   => 'items',
                    'row'     => $row['__row'],
                    'message' => "Soal {$row['item_key']} masih bertanda needs_verification (fakta perlu dicek). Soal tetap disimpan; ubah menjadi verified setelah faktanya dicek.",
                ];
            }
        }

        // Aturan bank soal kelas 5 atas keadaan sesudah impor (hanya bila
        // baris-barisnya sendiri sudah sah, agar pesan tidak bertumpuk)
        if ($errors === []) {
            foreach ([...$this->bankProblems($sheets, $context), ...$this->pendingProblems($pending['sheets'], $context)] as $problem) {
                if ($problem['level'] === 'error') {
                    $errors[] = $problem['finding'];
                } else {
                    $warnings[] = $problem['finding'];
                }
            }
        }

        $summary = $this->summarize($sheets, $warnings);

        return [
            'sheets'     => $sheets,
            'difficulty' => $context['difficulty'],
            'summary'    => $summary['summary'],
            'errors'     => $errors,
            'warnings'   => $summary['warnings'],
            'nodes'      => $nodes,
            'levels'     => $levels,
            'indicators' => $indicators,
            'media'      => $media,
        ];
    }

    /**
     * @param list<list<string|null>> $rows baris mentah sheet
     * @param list<string>            $headers
     *
     * @return list<array<string, string>>
     */
    private function readSheet(array $rows, array $headers): array
    {
        if ($rows === []) {
            return [];
        }

        $header = array_map(
            static fn ($value): string => mb_strtolower(trim((string) $value)),
            array_shift($rows),
        );

        $out = [];

        foreach ($rows as $index => $raw) {
            $row = ['__row' => $index + 2];

            foreach ($headers as $column) {
                $position     = array_search($column, $header, true);
                $row[$column] = $position === false ? '' : trim((string) ($raw[$position] ?? ''));
            }

            // baris kosong penuh dilewati
            if (implode('', array_diff_key($row, ['__row' => true])) === '') {
                continue;
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $context
     */
    private function validateRow(string $sheet, array $row, array $context): ?string
    {
        return match ($sheet) {
            'nodes'       => $this->validateNodeRef($row['node_ref'] ?? '', $context) ?? $this->validateNodeSettings($row, $context),
            'variants' => $this->validateNodeRef($row['node_ref'] ?? '', $context)
                ?? $this->validateVariantGroup('variants', (string) $row['variant_group']),
            'distractors' => $this->validateNodeRef($row['node_ref'] ?? '', $context)
                ?? $this->validateVariantGroup('distractors', (string) $row['variant_group'])
                ?? ($row['text_id'] === '' ? self::missing('distractors', 'text_id') : null),
            'passages' => $this->validatePassage($row, $context),
            'items'    => $this->validateItem($row, $context),
            'hints'  => $this->validateHint($row, $context),
            'cloze_matrix', 'object_codes', 'part_codes' => $this->validateCode($sheet, $row, $context),
            'post_scenes', 'post_parts' => $this->validateNodeRef($row['node_ref'] ?? '', $context)
                ?? $this->validateVariantGroup($sheet, (string) $row['variant_group'])
                ?? $this->validatePost($sheet, $row),
            'scenarios', 'scenario_options' => $this->validateScenario($sheet, $row, $context),
            'indicators' => trim((string) $row['code']) === '' || trim((string) $row['name_id']) === '' || trim((string) $row['name_en']) === ''
                ? 'Kolom code, name_id, dan name_en indikator wajib diisi.' : null,
            'library'       => $this->validateLibraryPage($row, $context),
            'library_media' => $this->validateLibraryMedia($row, $context),
            default  => null,
        };
    }

    private function validateNodeRef(string $nodeRef, array $context): ?string
    {
        $nodeRef = trim($nodeRef);

        if ($nodeRef === '') {
            return 'Kolom node_ref (kode tantangan) belum diisi.';
        }

        if (! isset($context['nodes'][$nodeRef])) {
            return "Kode tantangan (node_ref) '{$nodeRef}' tidak ada di permainan. Pakai tmg-1 … tmg-5, mgl-1 … mgl-5, atau wnb-1 … wnb-5.";
        }

        return null;
    }

    /** Kode variasi: huruf kecil/angka, paling banyak 10 (a, b, c). */
    private function validateVariantGroup(string $sheet, string $group): ?string
    {
        return preg_match('/^[a-z0-9]{1,10}$/', $group) === 1
            ? null
            : "Kolom variant_group (variasi) di sheet {$sheet} hanya boleh huruf kecil atau angka (mis. a, b, c).";
    }

    private function validateNodeSettings(array $row, array $context): ?string
    {
        if ($row['distractor_count'] !== '' && ! ctype_digit((string) $row['distractor_count'])) {
            return 'Kolom distractor_count (jumlah kata pengecoh) harus angka bulat 0 ke atas, atau dikosongkan.';
        }

        if (mb_strlen((string) $row['scene_media_label']) > 120 || mb_strlen((string) $row['scene_media_note']) > 500) {
            return 'Nama gambar adegan paling banyak 120 huruf dan isi gambar adegan paling banyak 500 huruf.';
        }

        if ($row['variant_code'] !== '' && preg_match('/^[a-z0-9_]{1,60}$/', (string) $row['variant_code']) !== 1) {
            return 'Kolom variant_code (kode variasi tampilan) hanya boleh berisi huruf kecil, angka, dan garis bawah (paling banyak 60).';
        }

        if ($row['indicator'] !== '' && ! isset($context['indicators'][$row['indicator']])) {
            return self::unknown('nodes', 'indicator', $row['indicator'], 'Pakai ' . implode(', ', array_keys($context['indicators'])) . ', atau kosongkan.');
        }

        return null;
    }

    private function validatePassage(array $row, array $context): ?string
    {
        if (trim((string) $row['passage_key']) === '') {
            return self::missing('passages', 'passage_key');
        }

        if (! isset($context['levels'][$row['level_code']])) {
            return self::unknownLevel($row['level_code']);
        }

        if (trim((string) $row['body_id']) === '' || trim((string) $row['body_en']) === '') {
            return 'Isi bacaan bahasa Indonesia (body_id) dan bahasa Inggris (body_en) keduanya wajib diisi.';
        }

        if ($row['text_genre'] !== '' && ! in_array($row['text_genre'], DifficultyRules::GENRES, true)) {
            return self::unknown('passages', 'text_genre', $row['text_genre'], 'Pakai ' . implode(', ', DifficultyRules::GENRES) . '.');
        }

        if ($row['layout'] !== '' && ! in_array($row['layout'], DifficultyRules::LAYOUTS, true)) {
            return self::unknown('passages', 'layout', $row['layout'], 'Pakai ' . implode(', ', DifficultyRules::LAYOUTS) . '.');
        }

        return null;
    }

    /** A row of `cloze_matrix`, `object_codes`, or `part_codes` (31 §6.3, §7.1). */
    private function validateCode(string $sheet, array $row, array $context): ?string
    {
        $key = trim((string) $row['item_key']);

        if ($key === '' || (! isset($context['items'][$key]) && model(ChallengeItemModel::class)->findByKey($key) === null)) {
            return "Kode soal (item_key) '{$key}' tidak ditemukan di sheet items.";
        }

        $code = strtoupper(trim((string) $row['error_code']));

        if ($code === '' || ErrorCodes::forbiddenInBank($code)) {
            return $code === '' ? self::missing($sheet, 'error_code') : "Kode kesalahan '{$code}' tidak dikenal atau tidak boleh ada di bank (*-LAIN, NON-*). Pakai kode dari daftar kode dan arti (03).";
        }

        if ($sheet === 'object_codes' && ! isset($context['items'][trim((string) $row['object_key'])])) {
            return "Benda (object_key) '{$row['object_key']}' tidak ditemukan di sheet items.";
        }

        if ($sheet === 'part_codes' && ! in_array((string) $row['main'], ['', '0', '1'], true)) {
            return 'Kolom main (pengecoh utama?) hanya boleh 0 atau 1.';
        }

        return null;
    }

    private function validatePost(string $sheet, array $row): ?string
    {
        if ($sheet === 'post_scenes') {
            return null;
        }

        if (trim((string) $row['part_key']) === '' || ! in_array($row['kind'], BankRules::PART_KINDS, true)) {
            return 'Bagian ketuk wajib punya part_key dan kind (' . implode(', ', BankRules::PART_KINDS) . ').';
        }

        foreach (['x', 'y', 'w', 'h'] as $column) {
            if (! is_numeric($row[$column])) {
                return ucfirst(self::column('post_parts', $column)) . ' wajib angka (persen adegan).';
            }
        }

        return null;
    }

    private function validateScenario(string $sheet, array $row, array $context): ?string
    {
        $key = trim((string) $row['item_key']);

        if (! isset($context['items'][$key]) && model(ChallengeItemModel::class)->findByKey($key) === null) {
            return "Kode skenario (item_key) '{$key}' tidak ditemukan di sheet items.";
        }

        if ($sheet === 'scenarios') {
            return in_array((string) $row['steps'], ['1', '2'], true) ? null : 'Kolom steps (jumlah langkah) hanya boleh 1 atau 2.';
        }

        if (! in_array($row['kind'], ['action', 'reason'], true) || trim((string) $row['option_key']) === '' || trim((string) $row['text_id']) === '') {
            return 'Pilihan skenario wajib punya kind (action atau reason), option_key, dan text_id.';
        }

        if ($row['kind'] === 'action' && ! in_array((string) $row['score'], ['0', '1', '2'], true)) {
            return 'Skor tindakan (score) hanya boleh 0, 1, atau 2.';
        }

        $code = strtoupper(trim((string) $row['code']));

        if ($code !== '' && ErrorCodes::forbiddenInBank($code)) {
            return "Kode '{$code}' tidak dikenal atau tidak boleh ada di bank.";
        }

        // Action scores are keys: an answered scenario keeps them (31 §7.1)
        $stored = model(ChallengeItemModel::class)->findByKey($key);

        if ($row['kind'] === 'action' && $stored !== null && model(ChallengeItemModel::class)->hasResponses($stored->id)) {
            $old = model(ScenarioOptionModel::class)->where('challenge_item_id', $stored->id)->where('kind', 'action')->where('option_key', trim((string) $row['option_key']))->first();

            if ($old !== null && (string) $old['score'] !== (string) $row['score']) {
                return "Skenario {$key} sudah pernah dijawab siswa, jadi skor tindakan {$row['option_key']} tidak boleh diubah.";
            }
        }

        return null;
    }

    private function validateItem(array $row, array $context): ?string
    {
        if (trim((string) $row['item_key']) === '') {
            return self::missing('items', 'item_key');
        }

        $nodeError = $this->validateNodeRef($row['node_ref'] ?? '', $context);

        if ($nodeError !== null) {
            return $nodeError;
        }

        if (! in_array($row['interaction_type'], config('Gelita')->interactionTypes, true)) {
            return $row['interaction_type'] === ''
                ? self::missing('items', 'interaction_type')
                : self::UNKNOWN_TYPE . " Soal {$row['item_key']} berjenis '{$row['interaction_type']}'; pakai " . implode(', ', config('Gelita')->interactionTypes) . '.';
        }

        $variantError = $this->validateVariantGroup('items', (string) $row['variant_group']);

        if ($variantError !== null) {
            return $variantError;
        }

        if ($row['indicator'] !== '' && ! isset($context['indicators'][$row['indicator']])) {
            return self::unknown('items', 'indicator', $row['indicator'], 'Pakai ' . implode(', ', array_keys($context['indicators'])) . ', atau kosongkan.');
        }

        if ($row['bloom_level'] !== '' && ! DifficultyRules::isBloom($row['bloom_level'])) {
            return self::unknown('items', 'bloom_level', $row['bloom_level'], 'Pakai C1, C2, C3, C4, C5, atau C6, atau kosongkan.');
        }

        if ($row['skill'] !== '' && ! DifficultyRules::isSkill($row['skill'])) {
            return self::unknown('items', 'skill', $row['skill'], 'Pakai ' . implode(', ', array_keys(DifficultyRules::SKILLS)) . '.');
        }

        if (mb_strlen((string) $row['media_label']) > 120 || mb_strlen((string) $row['media_note']) > 500) {
            return 'Nama gambar (media_label) paling banyak 120 huruf dan isi gambar (media_note) paling banyak 500 huruf.';
        }

        if (! in_array((string) $row['is_active'], ['', '0', '1'], true)) {
            return 'Kolom is_active (soal dipakai?) hanya boleh 0 atau 1, atau dikosongkan.';
        }

        if ($row['label_source'] !== '' && preg_match('/^(developer|expert|(akm|bloom):(developer|expert)(,(akm|bloom):(developer|expert))?)$/', $row['label_source']) !== 1) {
            return self::unknown('items', 'label_source', $row['label_source'], 'Pakai developer, expert, akm:expert, atau bloom:expert.');
        }

        if ($row['error_code'] !== '' && ErrorCodes::forbiddenInBank($row['error_code'])) {
            return "Kode kesalahan '{$row['error_code']}' soal {$row['item_key']} tidak dikenal atau tidak boleh ada di bank (*-LAIN, NON-*).";
        }

        $passageKey = trim((string) $row['passage_key']);

        if ($passageKey !== ''
            && ! isset($context['passages'][$passageKey])
            && model(ReadingPassageModel::class)->findByKey($passageKey) === null) {
            return "Kode bacaan (passage_key) '{$passageKey}' tidak ditemukan, baik di sheet passages maupun di bacaan yang sudah ada.";
        }

        // Isi soal (kunci benar/salah, ___ rumpang, Bloom, posisi benda, …)
        // diperiksa per tantangan oleh bankProblems() memakai BankRules.

        $layout = null;

        if ($row['interaction_type'] === 'puzzle_arrange') {
            $layout = $this->puzzleLayoutFor($row, model(ChallengeItemModel::class)->findByKey((string) $row['item_key']), $context['difficulty']);

            if ($layout['grid'] < 2) {
                return "Kunci susun gambar {$row['item_key']} (answer_id) harus berisi nomor keping sebanyak n × n (9 untuk 3 × 3, 16 untuk 4 × 4). Kosongkan saja: sistem membuatnya otomatis.";
            }
        }

        // Item yang sudah pernah dijawab tidak boleh berganti kunci, agar jawaban yang sudah ada tetap sebanding.
        $existing = model(ChallengeItemModel::class)->findByKey((string) $row['item_key']);

        if ($existing !== null && model(ChallengeItemModel::class)->hasResponses($existing->id)) {
            $incoming = $this->answerKeyFor($row, $layout);

            if (json_encode($incoming, JSON_UNESCAPED_UNICODE) !== json_encode($existing->answerKey(), JSON_UNESCAPED_UNICODE)) {
                return "Soal {$row['item_key']} sudah pernah dijawab siswa, jadi kunci jawabannya tidak boleh diubah (supaya data penelitian tetap sebanding). Bila perlu kunci baru, buat soal baru dengan kode baru.";
            }
        }

        return null;
    }

    private function validateHint(array $row, array $context): ?string
    {
        $nodeRef = trim((string) $row['node_ref']);
        $itemKey = trim((string) $row['item_key']);

        if ($nodeRef === '' && $itemKey === '') {
            return 'Isi salah satu: kolom node_ref (petunjuk untuk satu tantangan) atau item_key (petunjuk untuk satu soal).';
        }

        if ($nodeRef !== '') {
            $error = $this->validateNodeRef($nodeRef, $context);

            if ($error !== null) {
                return $error;
            }
        }

        if ($itemKey !== '') {
            $error = $this->validateReference($itemKey, $context['items'], 'hints');

            if ($error !== null && model(ChallengeItemModel::class)->findByKey($itemKey) === null) {
                return $error;
            }
        }

        if (trim((string) $row['text_id']) === '') {
            return self::missing('hints', 'text_id');
        }

        return null;
    }

    private function validateLibraryPage(array $row, array $context): ?string
    {
        if (! isset($context['levels'][$row['level_code']])) {
            return self::unknownLevel($row['level_code']);
        }

        if (! ctype_digit((string) $row['sequence']) || (int) $row['sequence'] < 1 || (int) $row['sequence'] > 999) {
            return 'Nomor halaman (sequence) harus berupa angka 1–999.';
        }

        foreach (['title_id', 'title_en', 'body_id'] as $column) {
            if (trim((string) $row[$column]) === '') {
                return self::missing('library', $column);
            }
        }

        if (mb_strlen((string) $row['title_id']) > 250 || mb_strlen((string) $row['title_en']) > 250) {
            return 'Judul halaman terlalu panjang (paling banyak 250 huruf).';
        }

        if (! in_array((string) $row['is_active'], ['', '0', '1'], true)) {
            return 'Kolom is_active (tampil?) hanya boleh 0 atau 1. Bila kosong, halaman tampil.';
        }

        return null;
    }

    private function validateLibraryMedia(array $row, array $context): ?string
    {
        if (! isset($context['levels'][$row['level_code']])) {
            return self::unknownLevel($row['level_code']);
        }

        if (! ctype_digit((string) $row['page_sequence']) || (int) $row['page_sequence'] < 1) {
            return 'Nomor halaman (page_sequence) harus berupa angka 1 ke atas.';
        }

        if ($row['sequence'] !== '' && (! ctype_digit((string) $row['sequence']) || (int) $row['sequence'] < 1)) {
            return 'Urutan media (sequence) harus berupa angka 1 ke atas, atau dikosongkan.';
        }

        $pageKey = $row['level_code'] . '#' . (int) $row['page_sequence'];

        if (! isset($context['library'][$pageKey]) && model(LibraryPageModel::class)
            ->where('level_id', $context['levels'][$row['level_code']])
            ->where('sequence', (int) $row['page_sequence'])
            ->first() === null) {
            return "Halaman Pustaka {$row['level_code']} nomor {$row['page_sequence']} tidak ditemukan, baik di sheet library maupun di Pustaka yang sudah ada.";
        }

        if (! in_array($row['media_kind'], ['', 'image', 'video'], true)) {
            return self::unknown('library_media', 'media_kind', $row['media_kind'], 'Pakai image (gambar) atau video.');
        }

        $key = trim((string) $row['media_asset_key']);
        $url = trim((string) $row['external_url']);

        if (($key === '') === ($url === '')) {
            return 'Isi SALAH SATU saja: kolom media_asset_key (berkas yang sudah diunggah) atau external_url (tautan).';
        }

        if ($url !== '' && MediaLink::parse($url, $row['media_kind'] === 'video' ? 'video' : 'image') === null) {
            return 'Tautan (external_url) harus alamat web lengkap yang diawali http:// atau https://.';
        }

        foreach (['caption_id' => 500, 'caption_en' => 500, 'credit' => 300] as $column => $max) {
            if (mb_strlen((string) $row[$column]) > $max) {
                return ucfirst(self::column('library_media', $column)) . " terlalu panjang (paling banyak {$max} huruf).";
            }
        }

        return null;
    }

    /**
     * Peringatan media: kunci yang belum terdaftar tidak menggagalkan impor.
     * Saat impor kunci itu dibuat sebagai slot kosong (media_assets nonaktif)
     * dan langsung ditautkan; permainan memakai tampilan pengganti sampai
     * admin mengunggah berkas dengan asset_key yang sama.
     *
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, int>                         $media
     *
     * @return list<array{sheet: string, row: int, message: string}>
     */
    private function mediaWarnings(array $sheets, array $media): array
    {
        $warnings = [];
        $columns  = [
            'nodes'         => ['scene_media_key', 'background_media_key'],
            'passages'      => ['media_asset_key'],
            'items'         => ['media_asset_key'],
            'post_scenes'   => ['media_asset_key'],
            'library_media' => ['media_asset_key', 'poster_media_key'],
        ];

        foreach ($columns as $sheet => $keys) {
            foreach ($sheets[$sheet] as $row) {
                foreach ($keys as $column) {
                    $key = trim((string) ($row[$column] ?? ''));

                    if ($key !== '' && ! isset($media[$key])) {
                        $warnings[] = [
                            'sheet'   => $sheet,
                            'row'     => $row['__row'],
                            'message' => "Berkas dengan kode '{$key}' di " . self::column($sheet, $column) . ' belum diunggah. Tempatnya tetap disiapkan saat impor; unggah berkasnya di menu Gambar & mockup dengan kode berkas yang sama, maka langsung tampil tanpa impor ulang.',
                        ];
                    }
                }
            }
        }

        foreach ($sheets['library_media'] as $row) {
            $url  = trim((string) $row['external_url']);
            $link = $url === '' ? null : MediaLink::parse($url, $row['media_kind'] === 'video' ? 'video' : 'image');

            if ($link !== null && $link['provider'] === 'link') {
                $warnings[] = [
                    'sheet'   => 'library_media',
                    'row'     => $row['__row'],
                    'message' => "Tautan {$link['label']} bukan dari YouTube, Google Drive, Vimeo, Wikimedia Commons, atau alamat langsung berkas gambar/video, jadi tampil sebagai tombol tautan, bukan sebagai gambar/video.",
                ];
            }
        }

        return $warnings;
    }

    /**
     * @param list<array<string, string>> $rows
     *
     * @return array<string, true> "level_code#sequence" halaman di sheet library
     */
    private function libraryKeys(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            if (ctype_digit((string) $row['sequence'])) {
                $out[$row['level_code'] . '#' . (int) $row['sequence']] = true;
            }
        }

        return $out;
    }

    /**
     * @param list<array<string, string>> $rows
     *
     * @return list<array<string, string>> baris kedua dst. dari pasangan (level_code, sequence) yang sama
     */
    private function duplicateLibraryPages(array $rows): array
    {
        $seen = [];
        $out  = [];

        foreach ($rows as $row) {
            $key = $row['level_code'] . '#' . (int) $row['sequence'];

            if (isset($seen[$key])) {
                $out[] = $row;
            }

            $seen[$key] = true;
        }

        return $out;
    }

    /** Kolom item_key di sheet $sheet harus merujuk soal di sheet items. */
    private function validateReference(string $value, array $known, string $sheet): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return self::missing($sheet, 'item_key');
        }

        return isset($known[$value]) ? null : "Kode soal (item_key) '{$value}' tidak ditemukan di sheet items.";
    }

    /**
     * "kolom node_ref (kode tantangan)", "kolom title_id (judul halaman bahasa
     * Indonesia)": kode header di Excel beserta nama Indonesianya dari
     * BankWorkbookGuide, supaya guru dapat menemukan kolomnya.
     */
    private static function column(string $sheet, string $column): string
    {
        $label = BankWorkbookGuide::COLUMNS[$sheet][$column][0] ?? null;

        if ($label === null) {
            return 'kolom ' . $column;
        }

        return 'kolom ' . $column . ' (' . lcfirst((string) preg_replace('/\s*\((Indonesia|Inggris)\)$/u', ' bahasa $1', $label)) . ')';
    }

    private static function missing(string $sheet, string $column): string
    {
        return ucfirst(self::column($sheet, $column)) . ' belum diisi.';
    }

    private static function unknown(string $sheet, string $column, string $value, string $hint): string
    {
        return 'Isian ' . "'{$value}'" . ' di ' . self::column($sheet, $column) . ' tidak dikenal. ' . $hint;
    }

    private static function unknownLevel(string $value): string
    {
        return $value === ''
            ? 'Kolom level_code (wilayah) belum diisi. Pakai temanggung, magelang, atau wonosobo.'
            : "Wilayah (level_code) '{$value}' tidak dikenal. Pakai temanggung, magelang, atau wonosobo.";
    }

    // -------------------------------------------------------------- tulis

    /** @return array<string, int> */
    private function write(array $parsed, string $mode): array
    {
        $sheets  = $parsed['sheets'];
        $nodes   = $parsed['nodes'];
        $media   = $this->createMediaSlots($sheets, $parsed['media']);
        $written = array_fill_keys(array_keys(self::SHEETS), 0);

        $written['indicators'] = $this->writeIndicators($sheets['indicators']);
        $indicators            = array_map(static fn ($row): int => (int) $row['id'], model(LearningIndicatorModel::class)->map());
        $parsed['indicators']  = $indicators;

        $written['nodes'] = $this->writeNodes($sheets['nodes'], $sheets['variants'], $sheets['distractors'], $nodes, $media, $parsed['indicators']);

        $passageIds        = $this->writePassages($sheets['passages'], $parsed['levels'], $media);
        $written['passages'] = count($passageIds);

        $itemIds = $this->writeItems(
            $sheets['items'],
            $nodes,
            $parsed['indicators'],
            $media,
            $passageIds,
            $mode,
            $parsed['difficulty'],
        );

        $written['items'] = count($itemIds);
        $written['hints'] = $this->writeHints($sheets['hints'], $nodes, $itemIds);

        // Research revision 10, part 5 (31 §7.1)
        $written['post_scenes'] = $this->writePostScenes($sheets['post_scenes'], $sheets['post_parts'], $nodes, $media);
        $written['post_parts']  = count($sheets['post_parts']);
        $codes                  = $this->writeErrorCodes($sheets, $itemIds);
        $written['error_codes'] = $codes['written'];
        $written['codes_changed'] = $codes['changed'];
        $written['scenarios']   = $this->writeScenarios($sheets['scenarios'], $sheets['scenario_options'], $itemIds);
        $written['scenario_options'] = count($sheets['scenario_options']);
        $this->writeSurfaceLoad($sheets, $parsed['difficulty'], $itemIds);

        $written['variants']    = count($sheets['variants']);
        $written['distractors'] = count($sheets['distractors']);

        $written['media_slots'] = count($media) - count($parsed['media']);

        $pageIds                  = $this->writeLibrary($sheets['library'], $parsed['levels']);
        $written['library']       = count($pageIds);
        $written['library_media'] = $this->writeLibraryMedia($sheets['library_media'], $parsed['levels'], $pageIds, $media);

        return $written;
    }

    /**
     * config_json node: judul per variasi (sheet variants), pengecoh Isi
     * Rumpang per variasi (sheet distractors), dan jumlah pengecoh yang tampil.
     * Isi Rumpang selalu bacaan dengan bank kata (as_reading, use_word_bank),
     * nilainya tetap dari engine.
     *
     * @param list<array<string, string>> $rows
     * @param list<array<string, string>> $variantRows
     * @param list<array<string, string>> $distractorRows
     * @param array<string, int>          $nodes
     * @param array<string, int>          $media
     * @param array<string, int>          $indicators
     */
    private function writeNodes(array $rows, array $variantRows, array $distractorRows, array $nodes, array $media, array $indicators): int
    {
        $model       = model(ChallengeNodeModel::class);
        $titles      = $this->groupBy($variantRows, 'node_ref');
        $distractors = $this->groupBy($distractorRows, 'node_ref');
        $count       = 0;

        foreach ($rows as $row) {
            $nodeId = $nodes[$row['node_ref']];
            $node   = $model->find($nodeId);
            $config = $node->config_json ?: [];

            $config['selection'] = 'variant';

            if ($node->engine_type === 'rumpang') {
                $config['as_reading']    = true;
                $config['use_word_bank'] = true;
            }

            if ($row['distractor_count'] !== '') {
                $config['distractor_count'] = (int) $row['distractor_count'];
            }

            $variants = (array) ($config['variants'] ?? []);

            foreach ($titles[$row['node_ref']] ?? [] as $title) {
                $group              = $title['variant_group'];
                $variants[$group]   = array_filter([
                    'title_id' => $title['title_id'],
                    'title_en' => $title['title_en'] !== '' ? $title['title_en'] : $title['title_id'],
                ], static fn (string $value): bool => $value !== '') + array_intersect_key((array) ($variants[$group] ?? []), ['distractors' => true]);
            }

            if (isset($distractors[$row['node_ref']])) {
                $byGroup = [];

                foreach ($distractors[$row['node_ref']] as $d) {
                    $byGroup[$d['variant_group']][] = [
                        'text_id' => $d['text_id'],
                        'text_en' => $d['text_en'] !== '' ? $d['text_en'] : $d['text_id'],
                    ];
                }

                foreach ($byGroup as $group => $list) {
                    $variants[$group] = ['distractors' => $list] + (array) ($variants[$group] ?? []);
                }
            }

            ksort($variants);
            $config['variants'] = $variants;

            $model->update($nodeId, array_filter([
                'variant_code'   => $row['variant_code'],
                'title_id'       => $row['title_id'],
                'title_en'       => $row['title_en'],
                'instruction_id' => $row['instruction_id'],
                'instruction_en' => $row['instruction_en'],
                'description_id' => $row['description_id'],
                'description_en' => $row['description_en'],
            ], static fn ($value): bool => $value !== '')
                + ($row['indicator'] !== '' ? ['indicator_id' => $indicators[$row['indicator']]] : [])
                + $this->mediaColumns($row, ['scene_media_key' => 'scene_media_id', 'background_media_key' => 'background_media_id'], $media)
                + ['config_json' => $config]);

            $count++;
        }

        return $count;
    }

    /**
     * @param list<array<string, string>> $rows
     * @param array<string, int>          $levels
     * @param array<string, int>          $media
     *
     * @return array<string, int> passage_key => id
     */
    private function writePassages(array $rows, array $levels, array $media): array
    {
        $model = model(ReadingPassageModel::class);
        $out   = [];

        foreach ($rows as $row) {
            $data = [
                'level_id'         => $levels[$row['level_code']],
                'passage_key'      => $row['passage_key'],
                'title_id'         => $this->nullIfBlank($row['title_id']),
                'title_en'         => $this->nullIfBlank($row['title_en']),
                'body_id'          => $row['body_id'],
                'body_en'          => $row['body_en'],
                'reference_source' => $this->nullIfBlank($row['reference_source']),
                'text_genre'       => $this->nullIfBlank($row['text_genre']),
                'layout'           => $this->nullIfBlank($row['layout']),
                'is_active'        => 1,
            ] + $this->mediaColumns($row, ['media_asset_key' => 'media_asset_id'], $media);

            $existing = $model->findByKey($row['passage_key']);

            if ($existing !== null) {
                $model->update($existing->id, $data);
                $out[$row['passage_key']] = $existing->id;

                continue;
            }

            $model->insert($data, false);
            $out[$row['passage_key']] = (int) $model->getInsertID();
        }

        return $out;
    }

    /**
     * @return array<string, int> item_key => id
     */
    private function writeItems(
        array $rows,
        array $nodes,
        array $indicators,
        array $media,
        array $passageIds,
        string $mode,
        array $difficulty = [],
    ): array {
        $model     = model(ChallengeItemModel::class);
        $passages  = model(ReadingPassageModel::class);
        $out       = [];

        foreach ($rows as $row) {
            $itemKey    = $row['item_key'];
            $passageKey = trim((string) $row['passage_key']);
            $passageId  = $passageIds[$passageKey] ?? null;

            if ($passageId === null && $passageKey !== '') {
                $passageId = $passages->findByKey($passageKey)?->id;
            }

            // Soal yang sudah ada dicari lebih dulu: ukuran papan dan kunci
            // susun gambarnya tidak pernah dihitung ulang.
            $existing = $model->findByKey($itemKey);
            $layout   = $row['interaction_type'] === 'puzzle_arrange'
                ? $this->puzzleLayoutFor($row, $existing, $difficulty)
                : null;

            $data = [
                'challenge_node_id' => $nodes[$row['node_ref']],
                'item_key'          => $itemKey,
                'variant_group'     => $row['variant_group'],
                'sequence'          => $row['sequence'] !== '' ? (int) $row['sequence'] : 1,
                'interaction_type'  => $row['interaction_type'],
                'prompt_id'         => $this->nullIfBlank($row['prompt_id']),
                'prompt_en'         => $this->nullIfBlank($row['prompt_en']),
                'passage_id'        => $passageId,
                'answer_key_json'   => $this->answerKeyFor($row, $layout),
                'config_json'       => $this->itemConfigFor($row, $layout),
                'indicator_id'      => $indicators[$row['indicator']] ?? null,
                'bloom_level'       => $this->nullIfBlank($row['bloom_level']),
                'reference_source'  => $this->nullIfBlank($row['reference_source']),
                'review_status'     => $row['review_status'] !== '' ? $row['review_status'] : 'draft',
                'review_note'       => $this->nullIfBlank($row['review_note']),
                'scorable'          => $row['scorable'] !== '' ? (int) $row['scorable'] : 1,
            ] + $this->labelColumns($row) + $this->mediaColumns($row, ['media_asset_key' => 'media_asset_id'], $media);

            // is_active kosong: soal baru dipakai, soal yang sudah ada tidak diubah —
            // impor ulang tidak boleh menghidupkan lagi soal yang sudah diistirahatkan.
            if ($row['is_active'] !== '') {
                $data['is_active'] = (int) $row['is_active'];
            } elseif ($existing === null) {
                $data['is_active'] = 1;
            }

            if ($existing !== null) {
                if ($mode === 'insert') {
                    throw new \RuntimeException("Soal {$itemKey} sudah ada; mode 'insert' tidak menimpa soal yang sudah ada.");
                }

                $model->update($existing->id, $data);
                $out[$itemKey] = $existing->id;

                continue;
            }

            $model->insert($data, false);
            $out[$itemKey] = (int) $model->getInsertID();
        }

        return $out;
    }

    /**
     * @param array<string, int> $nodes
     * @param array<string, int> $itemIds
     */
    private function writeHints(array $rows, array $nodes, array $itemIds): int
    {
        $model = model(HintModel::class);
        $count = 0;

        foreach ($rows as $row) {
            $itemKey = trim((string) $row['item_key']);
            $itemId  = $itemKey !== ''
                ? ($itemIds[$itemKey] ?? model(ChallengeItemModel::class)->findByKey($itemKey)?->id)
                : null;

            $nodeRef  = trim((string) $row['node_ref']);
            $sequence = $row['sequence'] !== '' ? (int) $row['sequence'] : 1;

            $data = [
                'challenge_node_id' => $nodeRef !== '' ? $nodes[$nodeRef] : null,
                'challenge_item_id' => $itemId,
                'sequence'          => $sequence,
                'text_id'           => $row['text_id'],
                'text_en'           => $row['text_en'] !== '' ? $row['text_en'] : $row['text_id'],
                'is_active'         => 1,
            ];

            $existing = $model
                ->where('challenge_node_id', $data['challenge_node_id'])
                ->where('challenge_item_id', $data['challenge_item_id'])
                ->where('sequence', $sequence)
                ->first();

            if ($existing !== null) {
                $model->update($existing['id'], $data);
            } else {
                $model->insert($data, false);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Halaman Pustaka: upsert berdasarkan (level_code, sequence).
     *
     * @param list<array<string, string>> $rows
     * @param array<string, int>          $levels
     *
     * @return array<string, int> "level_code#sequence" => library_pages.id
     */
    private function writeLibrary(array $rows, array $levels): array
    {
        $model = model(LibraryPageModel::class);
        $out   = [];

        foreach ($rows as $row) {
            $levelId  = $levels[$row['level_code']];
            $sequence = (int) $row['sequence'];
            $data     = [
                'level_id'  => $levelId,
                'sequence'  => $sequence,
                'title_id'  => $row['title_id'],
                'title_en'  => $row['title_en'],
                'body_id'   => $row['body_id'],
                'body_en'   => $row['body_en'],
                'is_active' => $row['is_active'] === '0' ? 0 : 1,
            ];

            $existing = $model->where('level_id', $levelId)->where('sequence', $sequence)->first();

            if ($existing !== null) {
                if (! $model->update($existing->id, $data)) {
                    throw new \RuntimeException("Halaman Pustaka {$row['level_code']} nomor {$sequence} tidak dapat disimpan: " . implode(' ', $model->errors()));
                }

                $out[$row['level_code'] . '#' . $sequence] = $existing->id;

                continue;
            }

            if ($model->insert($data, false) === false) {
                throw new \RuntimeException("Halaman Pustaka {$row['level_code']} nomor {$sequence} tidak dapat disimpan: " . implode(' ', $model->errors()));
            }

            $out[$row['level_code'] . '#' . $sequence] = (int) $model->getInsertID();
        }

        return $out;
    }

    /**
     * Media Pustaka. Setiap halaman yang punya baris di sheet ini diganti
     * seluruh medianya dengan isi sheet, sehingga impor ulang tidak
     * menggandakan galeri. Halaman tanpa baris di sheet tidak disentuh.
     *
     * @param list<array<string, string>> $rows
     * @param array<string, int>          $levels
     * @param array<string, int>          $pageIds
     * @param array<string, int>          $media
     */
    private function writeLibraryMedia(array $rows, array $levels, array $pageIds, array $media): int
    {
        $model  = model(LibraryMediaModel::class);
        $pages  = model(LibraryPageModel::class);
        $count  = 0;
        $groups = [];

        foreach ($rows as $row) {
            $groups[$row['level_code'] . '#' . (int) $row['page_sequence']][] = $row;
        }

        foreach ($groups as $pageKey => $group) {
            [$levelCode, $pageSequence] = explode('#', $pageKey);

            $pageId = $pageIds[$pageKey]
                ?? $pages->where('level_id', $levels[$levelCode])->where('sequence', (int) $pageSequence)->first()?->id;

            if ($pageId === null) {
                continue;
            }

            $model->where('library_page_id', $pageId)->delete();

            foreach ($group as $index => $row) {
                $key = trim((string) $row['media_asset_key']);
                $url = trim((string) $row['external_url']);

                $kind = $row['media_kind'] === 'video' ? 'video' : 'image';

                if ($url !== '') {
                    $kind = MediaLink::parse($url, $kind)['kind'] ?? $kind;
                }

                $written = $model->insert([
                    'library_page_id' => $pageId,
                    'sequence'        => $row['sequence'] !== '' ? (int) $row['sequence'] : $index + 1,
                    'media_kind'      => $kind,
                    'media_asset_id'  => $key !== '' ? $media[$key] : null,
                    'external_url'    => $url !== '' ? $url : null,
                    'poster_media_id' => $media[trim((string) $row['poster_media_key'])] ?? null,
                    'caption_id'      => $this->nullIfBlank($row['caption_id']),
                    'caption_en'      => $this->nullIfBlank($row['caption_en']),
                    'credit'          => $this->nullIfBlank($row['credit']),
                    'is_active'       => 1,
                ], false);

                if ($written === false) {
                    throw new \RuntimeException("Media Pustaka di sheet library_media baris {$row['__row']} tidak dapat disimpan: " . implode(' ', $model->errors()));
                }

                if ($kind === 'video' && $url !== '' && ! isset($media[trim((string) $row['poster_media_key'])])) {
                    $this->thumbnailQueue[] = (int) $model->getInsertID();
                }

                $count++;
            }
        }

        return $count;
    }

    /**
     * Kunci media yang dirujuk workbook tetapi belum ada di media_assets
     * dibuat sebagai slot kosong (is_active = 0, berkas belum ada) dengan
     * nama (`label`) dan isi gambar yang diharapkan (`description`) dari
     * kolom media workbook. Unggahan berikutnya dengan asset_key yang sama
     * mengisi baris ini (MediaStore), sehingga tautan konten tidak perlu
     * dibuat ulang; sebelum itu `gelita:mockups` memasang gambar contoh.
     *
     * Slot yang sudah ada (mis. gambar asli yang sudah diunggah admin) tidak
     * diubah, selain mengisi label/description yang masih kosong.
     *
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, int>                         $media
     *
     * @return array<string, int> peta asset_key => id, termasuk slot baru
     */
    private function createMediaSlots(array $sheets, array $media): array
    {
        $wanted = [];
        $labels = [];

        foreach (['nodes' => ['scene_media_key', 'background_media_key'], 'passages' => ['media_asset_key'],
            'items' => ['media_asset_key'], 'post_scenes' => ['media_asset_key'], 'library_media' => ['media_asset_key', 'poster_media_key']] as $sheet => $columns) {
            foreach ($sheets[$sheet] as $row) {
                foreach ($columns as $column) {
                    $key = trim((string) ($row[$column] ?? ''));

                    if ($key === '') {
                        continue;
                    }

                    [$label, $note] = match (true) {
                        in_array($sheet, ['items', 'post_scenes'], true) && $column === 'media_asset_key' => [$row['media_label'], $row['media_note']],
                        $sheet === 'nodes' && $column === 'scene_media_key' => [$row['scene_media_label'], $row['scene_media_note']],
                        default                                             => ['', ''],
                    };

                    if (trim((string) $label) !== '' || trim((string) $note) !== '') {
                        $labels[$key] ??= ['label' => trim((string) $label), 'description' => trim((string) $note)];
                    }

                    if (! isset($media[$key])) {
                        $isVideo      = $sheet === 'library_media' && $column === 'media_asset_key' && $row['media_kind'] === 'video';
                        $wanted[$key] = $isVideo ? 'video' : 'image';
                    }
                }
            }
        }

        $model = model(MediaAssetModel::class);

        foreach ($wanted as $key => $type) {
            $id = $model->insert([
                'asset_key'    => $key,
                'asset_type'   => $type,
                'storage_path' => MediaStore::uploadDir() . (preg_replace('/[^a-z0-9._-]+/i', '-', $key) ?? $key) . ($type === 'video' ? '.mp4' : '.png'),
                'mime_type'    => $type === 'video' ? 'video/mp4' : 'image/png',
                'is_active'    => 0,
            ] + array_filter($labels[$key] ?? [], static fn (string $value): bool => $value !== ''), true);

            if ($id === false) {
                throw new \RuntimeException("Tempat berkas dengan kode '{$key}' tidak dapat disiapkan: " . implode(' ', $model->errors()));
            }

            $media[$key] = (int) $id;
            unset($labels[$key]);
        }

        // Slot yang sudah ada: hanya label/description yang masih kosong
        foreach ($labels as $key => $texts) {
            if (! isset($media[$key])) {
                continue;
            }

            foreach ($texts as $column => $value) {
                if ($value === '') {
                    continue;
                }

                db_connect()->table('media_assets')
                    ->where('id', $media[$key])
                    ->groupStart()->where($column, null)->orWhere($column, '')->groupEnd()
                    ->update([$column => $value]);
            }
        }

        return $media;
    }

    /**
     * Label and round columns of one item row (31 §6.1, §13.1). `label_source`
     * fills `akm_level_source` and `bloom_level_source`; `akm:expert` changes
     * only the AKM source.
     *
     * @param array<string, string> $row
     *
     * @return array<string, mixed>
     */
    private function labelColumns(array $row): array
    {
        $source = ['akm' => 'developer', 'bloom' => 'developer'];
        $given  = trim((string) $row['label_source']);

        if (in_array($given, ['developer', 'expert'], true)) {
            $source = ['akm' => $given, 'bloom' => $given];
        } elseif ($given !== '') {
            foreach (explode(',', $given) as $pair) {
                [$label, $value] = array_pad(explode(':', $pair, 2), 2, 'developer');
                $source[$label]  = $value;
            }
        }

        return [
            'round'              => $row['round'] !== '' ? (int) $row['round'] : null,
            'dimension'          => $this->nullIfBlank($row['dimension']),
            'subaspect'          => $this->nullIfBlank($row['subaspect']),
            'akm_level'          => $this->nullIfBlank($row['akm_level']),
            'akm_level_source'   => $row['akm_level'] !== '' ? $source['akm'] : null,
            'pirls_process'      => $this->nullIfBlank($row['pirls_process']),
            'bloom_level_source' => $row['bloom_level'] !== '' && $row['interaction_type'] !== 'puzzle_arrange' ? $source['bloom'] : null,
            'testlet_key'        => $this->nullIfBlank($row['testlet_key']),
            'error_code'         => $this->nullIfBlank($row['error_code']),
            'd3_clue_type'       => $this->nullIfBlank($row['d3_clue_type']),
            'd2_topic'           => $this->nullIfBlank($row['d2_topic']),
        ];
    }

    /**
     * Sheet `indicators` → `learning_indicators` (upsert by code). Once the
     * sheet is there, the old bank's indicators are made inactive, not
     * deleted (31 §15), so stored references stay valid.
     *
     * @param list<array<string, string>> $rows
     */
    private function writeIndicators(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        $codes = [];
        $table = db_connect()->table('learning_indicators');

        foreach ($rows as $row) {
            $code    = trim((string) $row['code']);
            $codes[] = $code;
            $data    = [
                'name_id'        => $row['name_id'],
                'name_en'        => $row['name_en'],
                'description_id' => $this->nullIfBlank($row['description_id']),
                'description_en' => $this->nullIfBlank($row['description_en']),
                'domain'         => $this->nullIfBlank($row['domain']),
                'is_active'      => 1,
            ];

            if ($table->where('code', $code)->countAllResults() > 0) {
                db_connect()->table('learning_indicators')->where('code', $code)->update($data);
            } else {
                db_connect()->table('learning_indicators')->insert(['code' => $code] + $data);
            }
        }

        $old = array_values(array_diff(self::OLD_INDICATORS, $codes));

        if ($old !== []) {
            db_connect()->table('learning_indicators')->whereIn('code', $old)->update(['is_active' => 0]);
        }

        return count($rows);
    }

    /**
     * Sheets `post_scenes` and `post_parts` (Periksa Unggahan): one post per
     * challenge and variant; its parts are replaced as a whole.
     *
     * @param list<array<string, string>> $scenes
     * @param list<array<string, string>> $parts
     * @param array<string, int>          $nodes
     * @param array<string, int>          $media
     */
    private function writePostScenes(array $scenes, array $parts, array $nodes, array $media): int
    {
        $sceneModel = model(PostSceneModel::class);
        $ids        = [];

        foreach ($scenes as $row) {
            $ids[$row['node_ref'] . '|' . $row['variant_group']] = $sceneModel->upsert($nodes[$row['node_ref']], $row['variant_group'], [
                'title_id'         => $this->nullIfBlank($row['title_id']),
                'title_en'         => $this->nullIfBlank($row['title_en']),
                'text_genre'       => $this->nullIfBlank($row['text_genre']),
                'reference_source' => $this->nullIfBlank($row['reference_source']),
            ] + $this->mediaColumns($row, ['media_asset_key' => 'media_asset_id'], $media));
        }

        $byScene = [];

        foreach ($parts as $part) {
            $byScene[$part['node_ref'] . '|' . $part['variant_group']][] = $part;
        }

        foreach ($byScene as $scene => $list) {
            [$ref, $group] = explode('|', $scene, 2);
            $sceneId       = $ids[$scene] ?? $sceneModel->upsert($nodes[$ref], $group, ['title_id' => null]);
            $rows          = [];

            foreach ($list as $n => $part) {
                $rows[] = [
                    'part_key' => strtoupper(trim($part['part_key'])),
                    'kind'     => trim($part['kind']),
                    'label_id' => $this->nullIfBlank($part['label_id']),
                    'label_en' => $this->nullIfBlank($part['label_en']),
                    'text_id'  => $part['text_id'],
                    'text_en'  => $part['text_en'] !== '' ? $part['text_en'] : $part['text_id'],
                    'x'        => (float) $part['x'],
                    'y'        => (float) $part['y'],
                    'w'        => (float) $part['w'],
                    'h'        => (float) $part['h'],
                    'sequence' => $n + 1,
                ];
            }

            model(PostPartModel::class)->replaceForScene($sceneId, $rows);
        }

        return count($scenes);
    }

    /**
     * Sheets `cloze_matrix`, `object_codes`, `part_codes` → `item_error_codes`.
     * Every item with rows here gets its codes replaced as a whole, so codes
     * revised by experts apply at once. Codes may change after an item was
     * answered: stored tries keep the code of their own time (31 §7.1, K5).
     *
     * @param array<string, int> $itemIds
     *
     * @return array{written: int, changed: int}
     */
    private function writeErrorCodes(array $sheets, array $itemIds): array
    {
        $written = 0;
        $changed = 0;
        $model   = model(ItemErrorCodeModel::class);

        foreach (BankSource::codesFromSheets($sheets) as $key => $codes) {
            $id = $itemIds[$key] ?? model(ChallengeItemModel::class)->findByKey((string) $key)?->id;

            if ($id === null) {
                continue;
            }

            $result = $model->replaceForItem((int) $id, array_map(static fn (array $c): array => [
                'choice_type' => $c['type'],
                'choice_ref'  => $c['ref'],
                'error_code'  => $c['code'],
                'is_main'     => $c['main'] ? 1 : 0,
            ], $codes));
            $written += $result['written'];
            $changed += $result['changed'];
        }

        return ['written' => $written, 'changed' => $changed];
    }

    /**
     * Sheets `scenarios` and `scenario_options` (31 K4): the explanation and
     * topic/steps (`config_json`) on the item, its options replaced.
     *
     * @param list<array<string, string>> $scenarios
     * @param list<array<string, string>> $options
     * @param array<string, int>          $itemIds
     */
    private function writeScenarios(array $scenarios, array $options, array $itemIds): int
    {
        $items = model(ChallengeItemModel::class);
        $count = 0;

        foreach ($scenarios as $row) {
            $item = $items->findByKey($row['item_key']);

            if ($item === null) {
                continue;
            }

            $config          = $item->config_json ?: [];
            $config['steps'] = (int) $row['steps'];
            $config['topic'] = strtoupper(trim($row['d2_topic']));

            $items->update($item->id, [
                'explanation_id' => $this->nullIfBlank($row['explanation_id']),
                'explanation_en' => $this->nullIfBlank($row['explanation_en'] !== '' ? $row['explanation_en'] : $row['explanation_id']),
                'config_json'    => $config,
            ]);
            $count++;
        }

        foreach ($this->groupBy($options, 'item_key') as $key => $list) {
            $id = $itemIds[$key] ?? $items->findByKey((string) $key)?->id;

            if ($id === null) {
                continue;
            }

            model(ScenarioOptionModel::class)->replaceForItem((int) $id, array_map(fn (array $o): array => [
                'kind'        => $o['kind'],
                'option_key'  => trim($o['option_key']),
                'sequence'    => $o['sequence'] !== '' ? (int) $o['sequence'] : 1,
                'text_id'     => $o['text_id'],
                'text_en'     => $o['text_en'] !== '' ? $o['text_en'] : $o['text_id'],
                'score'       => $o['score'] !== '' ? (int) $o['score'] : null,
                'code'        => $this->nullIfBlank(strtoupper(trim($o['code']))),
                'feedback_id' => $this->nullIfBlank($o['feedback_id']),
                'feedback_en' => $this->nullIfBlank($o['feedback_en'] !== '' ? $o['feedback_en'] : $o['feedback_id']),
            ], $list));
        }

        return $count;
    }

    /**
     * Surface load of every item of the workbook (SurfaceLoad, 31 §13.2),
     * computed from the bank content and frozen with it (K14).
     *
     * @param array<string, string> $difficulty
     * @param array<string, int>    $itemIds
     */
    private function writeSurfaceLoad(array $sheets, array $difficulty, array $itemIds): void
    {
        $normal = BankSource::nodesFromSheets($sheets, $difficulty, static fn (): bool => true);
        $items  = model(ChallengeItemModel::class);
        $grids  = [];

        foreach ($sheets['items'] as $row) {
            if ($row['interaction_type'] === 'puzzle_arrange' && isset($itemIds[$row['item_key']])) {
                $grids[$row['item_key']] = (int) ($items->find($itemIds[$row['item_key']])?->conf('grid') ?? 0);
            }
        }

        foreach ($normal['nodes'] as $node) {
            foreach (SurfaceLoad::forNode($node, $normal['passages'], array_filter($grids)) as $key => $load) {
                if (isset($itemIds[$key])) {
                    $items->update($itemIds[$key], ['surface_json' => $load === [] ? null : $load]);
                }
            }
        }
    }

    /**
     * Kolom FK media dari kolom kunci workbook. Sel kosong TIDAK menyentuh
     * kolomnya: gambar yang dipasang dari editor konten tidak hilang saat
     * workbook tanpa kunci media diimpor ulang.
     *
     * @param array<string, string> $row
     * @param array<string, string> $map    kolom workbook => kolom tabel
     * @param array<string, int>    $media
     *
     * @return array<string, int>
     */
    private function mediaColumns(array $row, array $map, array $media): array
    {
        $out = [];

        foreach ($map as $sheetColumn => $tableColumn) {
            $key = trim((string) ($row[$sheetColumn] ?? ''));

            if ($key !== '' && isset($media[$key])) {
                $out[$tableColumn] = $media[$key];
            }
        }

        return $out;
    }

    // -------------------------------------------------------------- bantu

    /**
     * Kunci ringkas workbook → answer_key_json (pemetaan di 07_FEATURE_INTEGRATION.md).
     *
     * @return array<string, mixed>
     */
    private function answerKeyFor(array $row, ?array $layout = null): array
    {
        $answerId = trim((string) $row['answer_id']);
        $answerEn = trim((string) $row['answer_en']);

        return match ($row['interaction_type']) {
            'puzzle_arrange' => ['order' => $layout['key'] ?? ($answerId !== ''
                ? array_map('intval', $this->splitList($answerId))
                : range(0, 8))],
            'fill_blank_bank' => ['text_id' => $answerId, 'text_en' => $answerEn !== '' ? $answerEn : $answerId],
            'verdict_card'    => ['verdict' => mb_strtolower($answerId)],
            'find_object'     => ['target' => mb_strtolower($answerId) !== 'decoy'],
            'find_part'       => ['part' => strtoupper($answerId)],
            default       => [],
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function itemConfigFor(array $row, ?array $layout = null): ?array
    {
        $config = $layout === null ? [] : ['grid' => $layout['grid'], 'shape' => $layout['shape']];

        foreach (['x', 'y', 'w'] as $key) {
            if ($row[$key] !== '') {
                $config[$key] = (float) $row[$key];
            }
        }

        if ($row['decoy'] !== '') {
            $config['decoy'] = (bool) (int) $row['decoy'];
        }

        foreach (['wrong_feedback_id', 'wrong_feedback_en'] as $key) {
            if ($row[$key] !== '') {
                $config[$key] = $row[$key];
            }
        }

        // Keterampilan Benar atau Salah (DifficultyRules::SKILLS)
        if ($row['skill'] !== '') {
            $config['skill'] = $row['skill'];
        }

        return $config === [] ? null : $config;
    }

    /** @return list<string> */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $v): bool => $v !== ''));
    }

    /**
     * @param list<array<string, string>> $rows
     *
     * @return array<string, list<array<string, string>>>
     */
    private function groupBy(array $rows, string $key): array
    {
        $out = [];

        foreach ($rows as $row) {
            $value = trim((string) ($row[$key] ?? ''));

            if ($value !== '') {
                $out[$value][] = $row;
            }
        }

        return $out;
    }

    /**
     * @param list<array<string, string>> $rows
     *
     * @return array<string, array<string, string>>
     */
    private function collect(array $rows, string $key): array
    {
        $out = [];

        foreach ($rows as $row) {
            $value = trim((string) ($row[$key] ?? ''));

            if ($value !== '') {
                $out[$value] = $row;
            }
        }

        return $out;
    }

    /**
     * @return array{summary: array<string, mixed>, warnings: list<array{sheet: string, row: int, message: string}>}
     */
    private function summarize(array $sheets, array $warnings): array
    {
        $perNode = [];
        $groups  = [];

        foreach ($sheets['items'] as $row) {
            $perNode[$row['node_ref']]['items'] = ($perNode[$row['node_ref']]['items'] ?? 0) + 1;
            $groups[$row['node_ref']][$row['variant_group']] = true;
        }

        foreach ($sheets['hints'] as $row) {
            $ref = $row['node_ref'] !== '' ? $row['node_ref'] : '(item)';
            $perNode[$ref]['hints'] = ($perNode[$ref]['hints'] ?? 0) + 1;
        }

        foreach ($sheets['distractors'] as $row) {
            $perNode[$row['node_ref']]['distractors'] = ($perNode[$row['node_ref']]['distractors'] ?? 0) + 1;
        }

        foreach ($groups as $ref => $set) {
            $perNode[$ref]['variants'] = count($set);
        }

        return [
            'summary' => [
                'nodes'         => count($sheets['nodes']),
                'passages'      => count($sheets['passages']),
                'items'         => count($sheets['items']),
                'variants'      => count($sheets['variants']),
                'hints'         => count($sheets['hints']),
                'distractors'   => count($sheets['distractors']),
                'error_codes'   => count($sheets['cloze_matrix']) + count($sheets['object_codes']) + count($sheets['part_codes']),
                'post_scenes'   => count($sheets['post_scenes']),
                'post_parts'    => count($sheets['post_parts']),
                'scenarios'     => count($sheets['scenarios']),
                'scenario_options' => count($sheets['scenario_options']),
                'indicators'    => count($sheets['indicators']),
                'library'       => count($sheets['library']),
                'library_media' => count($sheets['library_media']),
                'per_node'      => $perNode,
            ],
            'warnings' => $warnings,
        ];
    }

    /**
     * Rules of the grade 5 bank (BankRules, 31 §8.2) on the state AFTER the
     * import of every challenge the workbook touches: active items in the
     * database replaced by workbook rows with the same key, items with
     * is_active 0 left out, decoy words per variant replaced by the
     * distractors sheet, and error codes per item, posts per variant, and
     * scenarios per item replaced by the workbook. Findings point to their
     * workbook row.
     *
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, mixed>                       $context
     *
     * @return list<array{level: string, finding: array{sheet: string, row: int, message: string}}>
     */
    private function bankProblems(array $sheets, array $context): array
    {
        $touched = [];

        foreach (['nodes', 'variants', 'distractors', 'items', 'post_scenes', 'post_parts'] as $sheet) {
            foreach ($sheets[$sheet] as $row) {
                if (isset($context['nodes'][$row['node_ref']])) {
                    $touched[$row['node_ref']] = true;
                }
            }
        }

        if ($touched === []) {
            return [];
        }

        $itemRows = [];

        foreach ($sheets['items'] as $row) {
            $itemRows[$row['item_key']] ??= $row;
        }

        $out = [];

        foreach (array_count_values(array_column($sheets['items'], 'item_key')) as $key => $count) {
            if ($count > 1) {
                $out[] = ['level' => 'error', 'finding' => ['sheet' => 'items', 'row' => (int) $itemRows[$key]['__row'], 'message' => "Kode soal (item_key) {$key} ditulis lebih dari sekali."]];
            }
        }

        // is_active left empty keeps a stored item as it is (retired stays retired)
        $stored = [];
        $rows   = $sheets;

        foreach ($sheets['items'] as $index => $row) {
            $stored[$row['item_key']] = model(ChallengeItemModel::class)->findByKey((string) $row['item_key']);

            if ($row['is_active'] === '' && $stored[$row['item_key']] !== null && ! $stored[$row['item_key']]->is_active) {
                $rows['items'][$index]['is_active'] = '0';
            }
        }

        $incoming = BankSource::nodesFromSheets($rows, $context['difficulty'], static fn (array $row): bool => trim((string) $row['media_asset_key']) !== ''
            || (($stored[$row['item_key']] ?? null) !== null && $stored[$row['item_key']]->media_asset_id !== null));
        $state    = new BankState();
        $passages = array_merge($state->passages(), $incoming['passages']);

        foreach (array_keys($touched) as $ref) {
            $node   = model(ChallengeNodeModel::class)->find($context['nodes'][$ref]);
            $normal = $node === null ? null : $state->node($node);

            if ($normal === null) {
                continue;
            }

            if (isset($incoming['nodes'][$ref])) {
                $normal = $this->overlay($normal, $incoming['nodes'][$ref], $sheets);
            }

            foreach (BankRules::node($normal, $passages) as $problem) {
                $out[] = $this->finding($problem, $sheets, $itemRows, $ref);
            }
        }

        return $out;
    }

    /**
     * Stored node + workbook node of the same challenge = the state after import.
     *
     * @param array<string, mixed> $stored   BankState::node()
     * @param array<string, mixed> $incoming BankSource::nodesFromSheets() node
     *
     * @return array<string, mixed>
     */
    private function overlay(array $stored, array $incoming, array $sheets): array
    {
        $keys = [];

        // Workbook rows replace the stored items; rows with is_active 0 leave
        foreach ($sheets['items'] as $row) {
            if ($row['node_ref'] === $stored['ref']) {
                $keys[$row['item_key']] = true;
            }
        }

        foreach ($sheets['nodes'] as $row) {
            if ($row['node_ref'] === $stored['ref'] && $row['distractor_count'] !== '') {
                $stored['distractor_count'] = (int) $row['distractor_count'];
            }
        }

        foreach ($stored['variants'] as $group => $variant) {
            $stored['variants'][$group]['items'] = array_values(array_filter($variant['items'], static fn (array $item): bool => ! isset($keys[$item['key']])));
        }

        $distractorGroups = [];

        foreach ($sheets['distractors'] as $row) {
            if ($row['node_ref'] === $stored['ref']) {
                $distractorGroups[$row['variant_group']] = true;
            }
        }

        foreach ($incoming['variants'] as $group => $variant) {
            $stored['variants'][$group] ??= ['distractors' => [], 'items' => []];
            $stored['variants'][$group]['items'] = [...$stored['variants'][$group]['items'], ...$variant['items']];

            if (isset($distractorGroups[$group])) {
                $stored['variants'][$group]['distractors'] = $variant['distractors'];
            }

            usort($stored['variants'][$group]['items'], static fn (array $a, array $b): int => [$a['sequence'] ?? 0, $a['key']] <=> [$b['sequence'] ?? 0, $b['key']]);
        }

        foreach ($stored['variants'] as $group => $variant) {
            if ($variant['items'] === [] && $variant['distractors'] === []) {
                unset($stored['variants'][$group]);
            }
        }

        ksort($stored['variants'], SORT_STRING);

        $stored['codes']     = (array) $incoming['codes'] + (array) $stored['codes'];
        $stored['posts']     = (array) $incoming['posts'] + (array) $stored['posts'];
        $stored['scenarios'] = (array) $incoming['scenarios'] + (array) $stored['scenarios'];

        return $stored;
    }

    /**
     * One BankRules finding → its workbook row.
     *
     * @param array<string, array<string, string>> $itemRows
     *
     * @return array{level: string, finding: array{sheet: string, row: int, message: string}}
     */
    private function finding(array $problem, array $sheets, array $itemRows, string $ref): array
    {
        $row   = $problem['item'] !== null ? ($itemRows[$problem['item']] ?? null) : null;
        $sheet = 'items';

        if ($row === null) {
            $row = $this->firstRow($sheets['items'], $ref, $problem['variant'])
                ?? $this->firstRow($sheets['post_parts'], $ref, $problem['variant'])
                ?? $this->firstRow($sheets['distractors'], $ref, $problem['variant'])
                ?? $this->firstRow($sheets['nodes'], $ref, null);
            $sheet = $row === null ? '-' : ($row['__sheet'] ?? 'items');
        }

        return ['level' => $problem['level'], 'finding' => [
            'sheet'   => $sheet,
            'row'     => (int) ($row['__row'] ?? 0),
            'message' => 'Tantangan ' . $ref . ($problem['variant'] !== null ? ' variasi ' . $problem['variant'] : '')
                . ($problem['item'] !== null ? ', soal ' . $problem['item'] : '') . ': ' . $problem['message'],
        ]];
    }

    /**
     * Rows of a challenge that does not exist in the game yet and holds only
     * Skenario Digital items (`tmg-5` before part 6 installs it): set aside,
     * checked with BankRules, and not written (31 K4; 32 §5.1).
     *
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, int>                         $nodes  node_ref => id of the game
     *
     * @return array{0: array<string, list<array<string, string>>>, 1: array{refs: array<string, int>, sheets: array<string, list<array<string, string>>>}}
     */
    private function setAsidePending(array $sheets, array $nodes): array
    {
        $types = [];

        foreach ($sheets['items'] as $row) {
            if (! isset($nodes[$row['node_ref']])) {
                $types[$row['node_ref']][] = $row['interaction_type'];
            }
        }

        $refs = [];

        foreach ($types as $ref => $list) {
            if (array_values(array_unique($list)) === ['scenario_choice']) {
                $refs[$ref] = count($list);
            }
        }

        $aside = array_fill_keys(array_keys(self::SHEETS), []);

        if ($refs === []) {
            return [$sheets, ['refs' => [], 'sheets' => $aside]];
        }

        $keys = [];

        foreach ($sheets['items'] as $row) {
            if (isset($refs[$row['node_ref']])) {
                $keys[$row['item_key']] = true;
            }
        }

        foreach ($sheets as $name => $list) {
            if (in_array($name, self::LIBRARY_SHEETS, true)) {
                continue;
            }

            foreach ($list as $index => $row) {
                $pending = ($row['node_ref'] ?? '') !== '' ? isset($refs[$row['node_ref']]) : isset($keys[$row['item_key'] ?? '']);

                if ($pending) {
                    $aside[$name][] = $row;
                    unset($sheets[$name][$index]);
                }
            }

            $sheets[$name] = array_values($sheets[$name]);
        }

        return [$sheets, ['refs' => $refs, 'sheets' => $aside]];
    }

    /**
     * BankRules on the challenges set aside by setAsidePending().
     *
     * @return list<array{level: string, finding: array{sheet: string, row: int, message: string}}>
     */
    private function pendingProblems(array $sheets, array $context): array
    {
        if ($sheets['items'] === []) {
            return [];
        }

        $normal   = BankSource::nodesFromSheets($sheets, $context['difficulty'], static fn (): bool => true);
        $itemRows = $this->collect($sheets['items'], 'item_key');
        $out      = [];

        foreach ($normal['nodes'] as $ref => $node) {
            foreach (BankRules::node($node, $normal['passages']) as $problem) {
                $out[] = $this->finding($problem, $sheets, $itemRows, (string) $ref);
            }
        }

        return $out;
    }

    /**
     * Baris pertama sebuah sheet untuk tantangan (dan variasi) tertentu.
     *
     * @param list<array<string, mixed>> $rows
     *
     * @return array<string, mixed>|null
     */
    private function firstRow(array $rows, string $ref, ?string $group): ?array
    {
        foreach ($rows as $row) {
            if (($row['node_ref'] ?? null) === $ref && ($group === null || ($row['variant_group'] ?? null) === $group)) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Ukuran papan, bentuk keping, dan kunci soal Susun Gambar. Ditetapkan
     * sekali saat soal dibuat, mengikuti tingkat kesulitan wilayahnya
     * (DifficultyRules: mudah 3 × 3 kotak, sedang 4 × 4 kotak, sulit 4 × 4
     * jigsaw). Soal yang sudah ada tetap memakai ukuran dan kuncinya sendiri,
     * sehingga jawaban siswa sebelumnya tetap sebanding. Kunci di answer_id
     * dihormati; ukurannya = akar jumlah keping.
     *
     * @param array<string, string> $difficulty awalan kode tantangan → tingkat
     *
     * @return array{grid: int, shape: string, key: list<int>}
     */
    private function puzzleLayoutFor(array $row, ?\App\Entities\ChallengeItem $existing, array $difficulty): array
    {
        $prefix = explode('-', (string) $row['node_ref'])[0];
        $rule   = DifficultyRules::puzzle($difficulty[$prefix] ?? null);
        $answer = trim((string) $row['answer_id']);

        $storedKey   = $existing?->interaction_type === 'puzzle_arrange' ? $existing->answerKey('order') : null;
        $storedShape = $existing?->conf('shape');
        $shape       = in_array($storedShape, DifficultyRules::SHAPES, true) ? $storedShape : null;

        if ($answer !== '') {
            $key = array_map('intval', $this->splitList($answer));
        } elseif (is_array($storedKey) && $storedKey !== []) {
            $key = array_map('intval', array_values($storedKey));
        } else {
            $key = range(0, $rule['grid'] * $rule['grid'] - 1);
        }

        $grid = (int) round(sqrt(count($key)));

        if ($grid * $grid !== count($key)) {
            $grid = 0;   // bukan n × n keping: ditolak validateItem
        }

        // ukuran lain dari aturan wilayah = kotak
        $shape ??= $grid === $rule['grid'] ? $rule['shape'] : 'square';

        return ['grid' => $grid, 'shape' => $shape, 'key' => $key];
    }

    /** @return array<string, string> awalan kode tantangan (tmg, mgl, wnb) → tingkat kesulitan wilayah */
    private function difficultyByPrefix(): array
    {
        $prefixes = array_flip(config('Gelita')->levelPrefixes);
        $out      = [];

        foreach (model(LevelModel::class)->ordered() as $level) {
            $prefix = $prefixes[(string) $level->code] ?? null;

            if ($prefix !== null) {
                $out[$prefix] = (string) $level->difficulty;
            }
        }

        return $out;
    }

    /** @return array<string, int> kode level => id */
    private function levelIdsByCode(): array
    {
        $out = [];

        foreach (model(LevelModel::class)->ordered() as $level) {
            $out[(string) $level->code] = $level->id;
        }

        return $out;
    }

    /**
     * @param array<string, int> $levels
     *
     * @return array<string, int> node_ref => challenge_nodes.id
     */
    private function nodeIdsByRef(array $levels): array
    {
        $byLevel = array_flip($levels);
        $out     = [];

        foreach (model(ChallengeNodeModel::class)->allActive() as $node) {
            $levelCode = $byLevel[$node->level_id] ?? null;
            $prefix    = $levelCode === null ? null : array_search($levelCode, config('Gelita')->levelPrefixes, true);

            if ($prefix !== false && $prefix !== null) {
                $out[$prefix . '-' . $node->sequence] = $node->id;
            }
        }

        return $out;
    }

    private function nodeRefFor(int $levelId, int $sequence): ?string
    {
        foreach ($this->levelIdsByCode() as $code => $id) {
            if ($id === $levelId) {
                $prefix = array_search($code, config('Gelita')->levelPrefixes, true);

                return $prefix === false ? null : $prefix . '-' . $sequence;
            }
        }

        return null;
    }

    /**
     * Baris contoh templat: empat tantangan utuh dari bank soal kelas 5
     * (docs/bank-soal/data, satu dari setiap jenis), sehingga templat yang
     * diunggah apa adanya lolos pratinjau dan tidak mengubah apa pun pada
     * server yang memakai bank itu. Sheet Pustaka sengaja tanpa contoh agar
     * Pustaka tidak tertimpa tanpa sengaja.
     *
     * @param list<string> $headers
     *
     * @return list<list<string>>
     */
    private function exampleRows(string $sheet, array $headers): array
    {
        static $flat = null;

        if (in_array($sheet, self::LIBRARY_SHEETS, true) || ! is_dir(ROOTPATH . 'docs/bank-soal/data')) {
            return [];
        }

        $flat ??= BankSource::flatten(BankSource::load(ROOTPATH . 'docs/bank-soal/data'));

        // Bacaan tidak punya node_ref: kodenya {tantangan}-{variasi} (tmg-3-a → tmg-3)
        // Rows keyed by item (code sheets, scenarios) take the node of the item key; indicators are all shown
        $refOf = static fn (array $row): string => (string) ($row['node_ref']
            ?? implode('-', array_slice(explode('-', (string) ($row['passage_key'] ?? $row['item_key'] ?? '')), 0, 2)));
        $rows = array_values(array_filter(
            $flat['sheets'][$sheet] ?? [],
            static fn (array $row): bool => $sheet === 'indicators' || in_array($refOf($row), ['tmg-1', 'mgl-2', 'tmg-3', 'tmg-4', 'tmg-5'], true),
        ));

        return array_map(
            static fn (array $row): array => array_map(static fn (string $column): string => (string) ($row[$column] ?? ''), $headers),
            $rows,
        );
    }

    private function nullIfBlank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
