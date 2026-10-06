<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\HintModel;
use App\Models\LearningIndicatorModel;
use App\Libraries\BankRules;
use App\Libraries\BankSource;
use App\Libraries\BankState;
use App\Libraries\BankWorkbookGuide;
use App\Libraries\DifficultyRules;
use App\Libraries\MediaLink;
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
        'passages'    => ['passage_key', 'level_code', 'title_id', 'title_en', 'body_id', 'body_en', 'media_asset_key', 'reference_source'],
        'items'       => ['item_key', 'node_ref', 'variant_group', 'sequence', 'interaction_type', 'indicator', 'bloom_level', 'skill', 'prompt_id', 'prompt_en', 'passage_key', 'answer_id', 'answer_en', 'x', 'y', 'w', 'decoy', 'wrong_feedback_id', 'wrong_feedback_en', 'media_asset_key', 'media_label', 'media_note', 'scorable', 'is_active', 'review_status', 'review_note', 'reference_source'],
        'hints'       => ['node_ref', 'item_key', 'sequence', 'text_id', 'text_en'],
        'library'       => ['level_code', 'sequence', 'title_id', 'title_en', 'body_id', 'body_en', 'is_active'],
        'library_media' => ['level_code', 'page_sequence', 'sequence', 'media_kind', 'media_asset_key', 'external_url', 'poster_media_key', 'caption_id', 'caption_en', 'credit'],
    ];

    /** Sheet Pustaka: workbook bank soal tidak memilikinya, workbook Pustaka hanya keduanya. */
    private const LIBRARY_SHEETS = ['library', 'library_media'];


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

            if (! $book->sheetNameExists($name) && $isBank && ! in_array($name, self::LIBRARY_SHEETS, true)) {
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

        foreach (['variants', 'distractors'] as $name) {
            foreach ($sheets[$name] as $index => $row) {
                $sheets[$name][$index]['variant_group'] = strtolower((string) $row['variant_group']) ?: 'a';
            }
        }

        $levels     = $this->levelIdsByCode();
        $nodes      = $this->nodeIdsByRef($levels);
        $indicators = array_map(static fn ($row): int => (int) $row['id'], model(LearningIndicatorModel::class)->map());
        $media      = model(MediaAssetModel::class)->keyMap();

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
            foreach ($this->bankProblems($sheets, $context) as $problem) {
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
                            'message' => "Berkas dengan kode '{$key}' di " . self::column($sheet, $column) . ' belum diunggah. Tempatnya tetap disiapkan saat impor; unggah berkasnya di menu Gambar & suara dengan kode berkas yang sama, maka langsung tampil tanpa impor ulang.',
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
            ] + $this->mediaColumns($row, ['media_asset_key' => 'media_asset_id'], $media);

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
            'items' => ['media_asset_key'], 'library_media' => ['media_asset_key', 'poster_media_key']] as $sheet => $columns) {
            foreach ($sheets[$sheet] as $row) {
                foreach ($columns as $column) {
                    $key = trim((string) ($row[$column] ?? ''));

                    if ($key === '') {
                        continue;
                    }

                    [$label, $note] = match (true) {
                        $sheet === 'items' && $column === 'media_asset_key' => [$row['media_label'], $row['media_note']],
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
                'storage_path' => 'assets/uploads/' . (preg_replace('/[^a-z0-9._-]+/i', '-', $key) ?? $key) . ($type === 'video' ? '.mp4' : '.png'),
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
                'library'       => count($sheets['library']),
                'library_media' => count($sheets['library_media']),
                'per_node'      => $perNode,
            ],
            'warnings' => $warnings,
        ];
    }

    /**
     * Aturan bank soal kelas 5 (BankRules, Dok 2 §7.5) atas keadaan SESUDAH
     * impor setiap tantangan yang disentuh workbook: soal aktif di database
     * ditimpa baris workbook (kode soal sama), soal ber-is_active 0 keluar,
     * pengecoh per variasi diganti isi sheet distractors, bacaan dari
     * database + sheet passages. Temuan dipetakan ke baris workbook-nya.
     *
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, mixed>                       $context
     *
     * @return list<array{level: string, finding: array{sheet: string, row: int, message: string}}>
     */
    private function bankProblems(array $sheets, array $context): array
    {
        $touched = [];

        foreach (['nodes', 'variants', 'distractors', 'items'] as $sheet) {
            foreach ($sheets[$sheet] as $row) {
                if (isset($context['nodes'][$row['node_ref']])) {
                    $touched[$row['node_ref']] = true;
                }
            }
        }

        if ($touched === []) {
            return [];
        }

        $state    = new BankState();
        $passages = $state->passages();

        foreach ($sheets['passages'] as $row) {
            $passages[$row['passage_key']] = ['level' => $row['level_code'], 'body_id' => $row['body_id'], 'body_en' => $row['body_en']];
        }

        $itemRows = [];

        foreach ($sheets['items'] as $row) {
            $itemRows[$row['item_key']] ??= $row;
        }

        $out = [];

        foreach (array_keys(array_count_values(array_column($sheets['items'], 'item_key'))) as $key) {
            if (count(array_keys(array_column($sheets['items'], 'item_key'), $key, true)) > 1) {
                $out[] = ['level' => 'error', 'finding' => ['sheet' => 'items', 'row' => (int) $itemRows[$key]['__row'], 'message' => "Kode soal (item_key) {$key} ditulis lebih dari sekali."]];
            }
        }

        foreach (array_keys($touched) as $ref) {
            $node = model(ChallengeNodeModel::class)->find($context['nodes'][$ref]);

            if ($node === null) {
                continue;
            }

            $normal = $state->node($node);

            if ($normal === null) {
                continue;
            }

            // Pengaturan tantangan dari sheet nodes
            foreach ($sheets['nodes'] as $row) {
                if ($row['node_ref'] === $ref && $row['distractor_count'] !== '') {
                    $normal['distractor_count'] = (int) $row['distractor_count'];
                }
            }

            // Pengecoh: variasi yang punya baris di sheet distractors diganti seluruhnya
            $byGroup = [];

            foreach ($sheets['distractors'] as $row) {
                if ($row['node_ref'] === $ref) {
                    $byGroup[$row['variant_group']][] = ['id' => $row['text_id'], 'en' => $row['text_en'] !== '' ? $row['text_en'] : $row['text_id']];
                }
            }

            foreach ($byGroup as $group => $list) {
                $normal['variants'][$group] ??= ['distractors' => [], 'items' => []];
                $normal['variants'][$group]['distractors'] = $list;
            }

            // Soal: database ditimpa baris workbook dengan kode yang sama
            $existing = [];

            foreach (model(ChallengeItemModel::class)->where('challenge_node_id', $node->id)->findAll() as $item) {
                $existing[(string) $item->item_key] = $item;
            }

            foreach ($normal['variants'] as $group => $variant) {
                $normal['variants'][$group]['items'] = array_values(array_filter(
                    $variant['items'],
                    static fn (array $item): bool => ! isset($itemRows[$item['key']]),
                ));
            }

            foreach ($sheets['items'] as $row) {
                if ($row['node_ref'] !== $ref || $itemRows[$row['item_key']] !== $row) {
                    continue;
                }

                $stored = $existing[$row['item_key']] ?? null;
                $active = $row['is_active'] !== '' ? $row['is_active'] === '1' : ($stored === null || $stored->is_active);

                if (! $active) {
                    continue;
                }

                $group = $row['variant_group'];
                $normal['variants'][$group] ??= ['distractors' => [], 'items' => []];
                $normal['variants'][$group]['items'][] = $this->normalItem($row, $stored, $context['media']);
            }

            foreach ($normal['variants'] as $group => $variant) {
                usort($normal['variants'][$group]['items'], static fn (array $a, array $b): int => [$a['sequence'] ?? 0, $a['key']] <=> [$b['sequence'] ?? 0, $b['key']]);
            }

            ksort($normal['variants'], SORT_STRING);

            foreach (BankRules::node($normal, $passages) as $problem) {
                $row   = $problem['item'] !== null ? ($itemRows[$problem['item']] ?? null) : null;
                $sheet = 'items';

                if ($row === null) {
                    $row = $this->firstRow($sheets['items'], $ref, $problem['variant'])
                        ?? $this->firstRow($sheets['distractors'], $ref, $problem['variant'])
                        ?? $this->firstRow($sheets['nodes'], $ref, null);
                    $sheet = $row === null ? '-' : ($row['__sheet'] ?? 'items');
                }

                $out[] = ['level' => $problem['level'], 'finding' => [
                    'sheet'   => $sheet,
                    'row'     => (int) ($row['__row'] ?? 0),
                    'message' => 'Tantangan ' . $ref . ($problem['variant'] !== null ? ' variasi ' . $problem['variant'] : '')
                        . ($problem['item'] !== null ? ', soal ' . $problem['item'] : '') . ': ' . $problem['message'],
                ]];
            }
        }

        return $out;
    }

    /**
     * Baris workbook → bentuk BankRules. Gambar dianggap ada bila kolom
     * kodenya terisi atau soal tersimpan sudah punya gambar.
     *
     * @param array<string, string> $row
     * @param array<string, int>    $media
     *
     * @return array<string, mixed>
     */
    private function normalItem(array $row, ?\App\Entities\ChallengeItem $stored, array $media): array
    {
        $type  = $row['interaction_type'];
        $decoy = $row['decoy'] !== '' ? $row['decoy'] === '1' : mb_strtolower($row['answer_id']) === 'decoy';

        return [
            'key'       => $row['item_key'],
            'sequence'  => $row['sequence'] !== '' ? (int) $row['sequence'] : 1,
            'type'      => $type,
            'prompt_id' => $row['prompt_id'],
            'prompt_en' => $row['prompt_en'],
            'answer_id' => match ($type) {
                'verdict_card' => mb_strtolower($row['answer_id']),
                'find_object'  => $decoy ? 'decoy' : 'target',
                default        => $row['answer_id'],
            },
            'answer_en' => $type === 'fill_blank_bank' ? ($row['answer_en'] !== '' ? $row['answer_en'] : $row['answer_id']) : '',
            'bloom'     => $row['bloom_level'],
            'skill'     => $row['skill'],
            'passage'   => $row['passage_key'] !== '' ? $row['passage_key'] : null,
            'x'         => $row['x'] !== '' ? (float) $row['x'] : null,
            'y'         => $row['y'] !== '' ? (float) $row['y'] : null,
            'w'         => $row['w'] !== '' ? (float) $row['w'] : null,
            'decoy'     => $decoy,
            'wrong_id'  => $row['wrong_feedback_id'],
            'media'     => trim($row['media_asset_key']) !== '' || ($stored !== null && $stored->media_asset_id !== null),
        ];
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
        $refOf = static fn (array $row): string => (string) ($row['node_ref']
            ?? implode('-', array_slice(explode('-', (string) ($row['passage_key'] ?? '')), 0, 2)));
        $rows = array_values(array_filter(
            $flat['sheets'][$sheet] ?? [],
            static fn (array $row): bool => in_array($refOf($row), ['tmg-1', 'mgl-2', 'tmg-3', 'tmg-4'], true),
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
