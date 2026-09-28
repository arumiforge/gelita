<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\ChallengeOptionModel;
use App\Models\HintModel;
use App\Models\LearningIndicatorModel;
use App\Libraries\BankWorkbookGuide;
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
 * Format sheet ditetapkan di 07_FEATURE_INTEGRATION.md (FITUR 12a); arti
 * tiap kolom (dipakai sheet PETUNJUK di templat) ada di BankWorkbookGuide.
 *
 * Sheet `library` dan `library_media` (Pustaka Kedu) opsional: workbook
 * lama tanpa keduanya tetap terbaca seperti sebelumnya.
 */
class ContentImportService
{
    private const INTERACTION_TYPES = [
        'puzzle_arrange', 'ordering', 'fill_blank_bank', 'fill_blank_free',
        'verdict_card', 'verdict_reason', 'single_choice', 'source_trust', 'find_object',
    ];

    /** @var list<int> id library_media video luar tanpa poster yang ditulis impor ini */
    private array $thumbnailQueue = [];

    /** Header per sheet; kolom di luar daftar ini diabaikan. Sheet lain (mis. PETUNJUK) juga diabaikan. */
    public const SHEETS = [
        'nodes'       => ['node_ref', 'title_id', 'title_en', 'instruction_id', 'instruction_en', 'description_id', 'description_en', 'items_per_round', 'verdict_options', 'require_reason', 'use_word_bank', 'distractor_count', 'scene_media_key', 'background_media_key'],
        'distractors' => ['node_ref', 'text_id', 'text_en'],
        'passages'    => ['passage_key', 'level_code', 'title_id', 'title_en', 'body_id', 'body_en', 'media_asset_key', 'reference_source'],
        'items'       => ['item_key', 'node_ref', 'sequence', 'interaction_type', 'indicator', 'prompt_id', 'prompt_en', 'source_text_id', 'source_text_en', 'passage_key', 'answer_id', 'answer_en', 'sample_reason_id', 'sample_reason_en', 'x', 'y', 'w', 'decoy', 'wrong_feedback_id', 'wrong_feedback_en', 'digital_pillar', 'media_asset_key', 'scorable', 'review_status', 'review_note', 'reference_source'],
        'options'     => ['option_key', 'item_key', 'label_id', 'label_en', 'is_correct', 'feedback_id', 'feedback_en', 'media_asset_key', 'display_order'],
        'pieces'      => ['item_key', 'piece_key', 'text_id', 'text_en'],
        'sources'     => ['item_key', 'label_id', 'label_en', 'kind', 'text_id', 'text_en'],
        'hints'       => ['node_ref', 'item_key', 'sequence', 'text_id', 'text_en'],
        'library'       => ['level_code', 'sequence', 'title_id', 'title_en', 'body_id', 'body_en', 'is_active'],
        'library_media' => ['level_code', 'page_sequence', 'sequence', 'media_kind', 'media_asset_key', 'external_url', 'poster_media_key', 'caption_id', 'caption_en', 'credit'],
    ];

    /** Sheet yang boleh tidak ada tanpa peringatan (fitur yang ditambahkan belakangan). */
    private const OPTIONAL_SHEETS = ['library', 'library_media'];

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
            'subtitle' => 'Baris yang sudah terisi di setiap sheet hanyalah contoh: boleh dihapus atau ditimpa. Baca sheet PETUNJUK ini dulu sebelum mengisi.',
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

        foreach (self::SHEETS as $name => $headers) {
            $sheets[$name] = $book->sheetNameExists($name)
                ? $this->readSheet($book->getSheetByName($name)->toArray(null, true, false, false), $headers)
                : [];

            if (! $book->sheetNameExists($name) && ! in_array($name, self::OPTIONAL_SHEETS, true)) {
                $warnings[] = ['sheet' => $name, 'row' => 0, 'message' => 'Sheet ' . $name . ' tidak ada di berkas, jadi dilewati.'];
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
            'options'    => $this->groupBy($sheets['options'], 'item_key'),
            'library'    => $this->libraryKeys($sheets['library']),
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

        $summary = $this->summarize($sheets, $warnings);

        return [
            'sheets'     => $sheets,
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
            'nodes'       => $this->validateNodeRef($row['node_ref'] ?? '', $context),
            'distractors' => $this->validateNodeRef($row['node_ref'] ?? '', $context)
                ?? ($row['text_id'] === '' ? self::missing('distractors', 'text_id') : null),
            'passages' => $this->validatePassage($row, $context),
            'items'    => $this->validateItem($row, $context),
            'options'  => $this->validateReference($row['item_key'] ?? '', $context['items'], 'options')
                ?? ($row['label_id'] === '' ? self::missing('options', 'label_id') : null),
            'pieces' => $this->validateReference($row['item_key'] ?? '', $context['items'], 'pieces')
                ?? ($row['piece_key'] === '' ? self::missing('pieces', 'piece_key') : null),
            'sources' => $this->validateReference($row['item_key'] ?? '', $context['items'], 'sources')
                ?? (! in_array($row['kind'], ['official', 'anonymous', 'chain_message', 'blog'], true)
                    ? self::unknown('sources', 'kind', $row['kind'], 'Pilih official, anonymous, chain_message, atau blog.')
                    : null),
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

        if (! in_array($row['interaction_type'], self::INTERACTION_TYPES, true)) {
            return $row['interaction_type'] === ''
                ? self::missing('items', 'interaction_type')
                : self::unknown('items', 'interaction_type', $row['interaction_type'], 'Pilih salah satu dari daftar pilihan di sel tersebut.');
        }

        if ($row['indicator'] !== '' && ! isset($context['indicators'][$row['indicator']])) {
            return self::unknown('items', 'indicator', $row['indicator'], 'Pakai ' . implode(', ', array_keys($context['indicators'])) . ', atau kosongkan.');
        }

        $passageKey = trim((string) $row['passage_key']);

        if ($passageKey !== ''
            && ! isset($context['passages'][$passageKey])
            && model(ReadingPassageModel::class)->findByKey($passageKey) === null) {
            return "Kode bacaan (passage_key) '{$passageKey}' tidak ditemukan, baik di sheet passages maupun di bacaan yang sudah ada.";
        }

        if (in_array($row['interaction_type'], ['verdict_card', 'verdict_reason'], true)
            && ! in_array(mb_strtolower((string) $row['answer_id']), ['benar', 'salah', 'pendapat'], true)) {
            return 'Kunci jawaban (answer_id) untuk soal benar/salah harus diisi benar, salah, atau pendapat.';
        }

        if (in_array($row['interaction_type'], ['fill_blank_bank', 'fill_blank_free'], true)
            && trim((string) $row['answer_id']) === '') {
            return 'Kunci jawaban (answer_id) wajib diisi untuk soal isi rumpang.';
        }

        // Item yang sudah pernah dijawab tidak boleh berganti kunci, agar data lama tetap sebanding.
        $existing = model(ChallengeItemModel::class)->findByKey((string) $row['item_key']);

        if ($existing !== null && model(ChallengeItemModel::class)->hasResponses($existing->id)) {
            // kunci pilihan ganda diturunkan dari opsi di workbook, sama seperti saat menulis
            $incoming = $this->answerKeyFor($row, $context['options'][trim((string) $row['item_key'])] ?? []);

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
            'options'       => ['media_asset_key'],
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

        $written['nodes'] = $this->writeNodes($sheets['nodes'], $sheets['distractors'], $nodes, $media);

        $passageIds        = $this->writePassages($sheets['passages'], $parsed['levels'], $media);
        $written['passages'] = count($passageIds);

        $piecesByItem  = $this->groupBy($sheets['pieces'], 'item_key');
        $sourcesByItem = $this->groupBy($sheets['sources'], 'item_key');
        $optionsByItem = $this->groupBy($sheets['options'], 'item_key');

        $itemIds = $this->writeItems(
            $sheets['items'],
            $nodes,
            $parsed['indicators'],
            $media,
            $passageIds,
            $piecesByItem,
            $sourcesByItem,
            $optionsByItem,
            $mode,
        );

        $written['items']   = count($itemIds);
        $written['options'] = $this->writeOptions($sheets['options'], $itemIds, $media);
        $written['pieces']  = count($sheets['pieces']);
        $written['sources'] = count($sheets['sources']);
        $written['hints']   = $this->writeHints($sheets['hints'], $nodes, $itemIds);

        $written['distractors'] = count($sheets['distractors']);

        $written['media_slots'] = count($media) - count($parsed['media']);

        $pageIds                  = $this->writeLibrary($sheets['library'], $parsed['levels']);
        $written['library']       = count($pageIds);
        $written['library_media'] = $this->writeLibraryMedia($sheets['library_media'], $parsed['levels'], $pageIds, $media);

        return $written;
    }

    /**
     * @param list<array<string, string>> $rows
     * @param list<array<string, string>> $distractorRows
     * @param array<string, int>          $nodes
     * @param array<string, int>          $media
     */
    private function writeNodes(array $rows, array $distractorRows, array $nodes, array $media): int
    {
        $model       = model(ChallengeNodeModel::class);
        $distractors = $this->groupBy($distractorRows, 'node_ref');
        $count       = 0;

        foreach ($rows as $row) {
            $nodeId = $nodes[$row['node_ref']];
            $node   = $model->find($nodeId);
            $config = $node->config_json ?: [];

            foreach (['items_per_round' => 'int', 'distractor_count' => 'int',
                'require_reason' => 'bool', 'use_word_bank' => 'bool'] as $key => $type) {
                if ($row[$key] !== '') {
                    $config[$key] = $type === 'int' ? (int) $row[$key] : (bool) (int) $row[$key];
                }
            }

            if ($row['verdict_options'] !== '') {
                $config['verdict_options'] = array_values(array_filter(array_map(
                    static fn (string $value): string => mb_strtolower(trim($value)),
                    explode(',', $row['verdict_options']),
                )));
            }

            if (isset($distractors[$row['node_ref']])) {
                $config['distractors'] = array_map(static fn (array $d): array => [
                    'text_id' => $d['text_id'],
                    'text_en' => $d['text_en'] !== '' ? $d['text_en'] : $d['text_id'],
                ], $distractors[$row['node_ref']]);
            }

            $model->update($nodeId, array_filter([
                'title_id'       => $row['title_id'],
                'title_en'       => $row['title_en'],
                'instruction_id' => $row['instruction_id'],
                'instruction_en' => $row['instruction_en'],
                'description_id' => $row['description_id'],
                'description_en' => $row['description_en'],
            ], static fn ($value): bool => $value !== '')
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
        array $piecesByItem,
        array $sourcesByItem,
        array $optionsByItem,
        string $mode,
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

            $data = [
                'challenge_node_id' => $nodes[$row['node_ref']],
                'item_key'          => $itemKey,
                'sequence'          => $row['sequence'] !== '' ? (int) $row['sequence'] : 1,
                'interaction_type'  => $row['interaction_type'],
                'prompt_id'         => $this->nullIfBlank($row['prompt_id']),
                'prompt_en'         => $this->nullIfBlank($row['prompt_en']),
                'source_text_id'    => $this->nullIfBlank($row['source_text_id']),
                'source_text_en'    => $this->nullIfBlank($row['source_text_en']),
                'passage_id'        => $passageId,
                'answer_key_json'   => $this->answerKeyFor($row, $optionsByItem[$itemKey] ?? []),
                'config_json'       => $this->itemConfigFor($row, $piecesByItem[$itemKey] ?? [], $sourcesByItem[$itemKey] ?? []),
                'indicator_id'      => $indicators[$row['indicator']] ?? null,
                'reference_source'  => $this->nullIfBlank($row['reference_source']),
                'review_status'     => $row['review_status'] !== '' ? $row['review_status'] : 'draft',
                'review_note'       => $this->nullIfBlank($row['review_note']),
                'scorable'          => $row['scorable'] !== '' ? (int) $row['scorable'] : 1,
                'is_active'         => 1,
            ] + $this->mediaColumns($row, ['media_asset_key' => 'media_asset_id'], $media);

            $existing = $model->findByKey($itemKey);

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
     * @param array<string, int> $itemIds
     * @param array<string, int> $media
     */
    private function writeOptions(array $rows, array $itemIds, array $media): int
    {
        $model = model(ChallengeOptionModel::class);
        $count = 0;

        foreach ($rows as $row) {
            $itemId = $itemIds[$row['item_key']] ?? null;

            if ($itemId === null) {
                continue;
            }

            $data = [
                'challenge_item_id' => $itemId,
                'option_key'        => $row['option_key'],
                'label_id'          => $row['label_id'],
                'label_en'          => $row['label_en'] !== '' ? $row['label_en'] : $row['label_id'],
                'is_correct'        => (int) ($row['is_correct'] !== '' ? $row['is_correct'] : 0),
                'feedback_id'       => $this->nullIfBlank($row['feedback_id']),
                'feedback_en'       => $this->nullIfBlank($row['feedback_en']),
                'display_order'     => $row['display_order'] !== '' ? (int) $row['display_order'] : 1,
            ] + $this->mediaColumns($row, ['media_asset_key' => 'media_asset_id'], $media);

            $existing = $model->findByKey($itemId, $row['option_key']);

            if ($existing !== null) {
                $model->update($existing->id, $data);
            } else {
                $model->insert($data, false);
            }

            $count++;
        }

        return $count;
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
     * dibuat sebagai slot kosong (is_active = 0, berkas belum ada). Unggahan
     * berikutnya dengan asset_key yang sama mengisi baris ini (MediaStore),
     * sehingga tautan konten tidak perlu dibuat ulang.
     *
     * @param array<string, list<array<string, string>>> $sheets
     * @param array<string, int>                         $media
     *
     * @return array<string, int> peta asset_key => id, termasuk slot baru
     */
    private function createMediaSlots(array $sheets, array $media): array
    {
        $wanted = [];

        foreach (['nodes' => ['scene_media_key', 'background_media_key'], 'passages' => ['media_asset_key'],
            'items' => ['media_asset_key'], 'options' => ['media_asset_key'], 'library_media' => ['media_asset_key', 'poster_media_key']] as $sheet => $columns) {
            foreach ($sheets[$sheet] as $row) {
                foreach ($columns as $column) {
                    $key = trim((string) ($row[$column] ?? ''));

                    if ($key !== '' && ! isset($media[$key])) {
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
            ], true);

            if ($id === false) {
                throw new \RuntimeException("Tempat berkas dengan kode '{$key}' tidak dapat disiapkan: " . implode(' ', $model->errors()));
            }

            $media[$key] = (int) $id;
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
     * @param list<array<string, string>> $options
     *
     * @return array<string, mixed>
     */
    private function answerKeyFor(array $row, array $options): array
    {
        $answerId = trim((string) $row['answer_id']);
        $answerEn = trim((string) $row['answer_en']);

        return match ($row['interaction_type']) {
            'puzzle_arrange' => ['order' => $answerId !== ''
                ? array_map('intval', $this->splitList($answerId))
                : range(0, 8)],
            'ordering'        => ['order' => $this->splitList($answerId)],
            'fill_blank_bank' => ['text_id' => $answerId, 'text_en' => $answerEn !== '' ? $answerEn : $answerId],
            'fill_blank_free' => [
                'accept_id'      => $this->splitAccepted($answerId),
                'accept_en'      => $this->splitAccepted($answerEn !== '' ? $answerEn : $answerId),
                'case_sensitive' => false,
            ],
            'verdict_card' => ['verdict' => mb_strtolower($answerId)],
            'verdict_reason' => array_filter([
                'verdict'          => mb_strtolower($answerId),
                'sample_reason_id' => $this->nullIfBlank($row['sample_reason_id']),
                'sample_reason_en' => $this->nullIfBlank($row['sample_reason_en']),
            ], static fn ($value): bool => $value !== null),
            'single_choice', 'source_trust' => ['option_key' => $this->correctOptionKey($options)],
            'find_object' => ['target' => mb_strtolower($answerId) !== 'decoy'],
            default       => [],
        };
    }

    /**
     * @param list<array<string, string>> $pieces
     * @param list<array<string, string>> $sources
     *
     * @return array<string, mixed>|null
     */
    private function itemConfigFor(array $row, array $pieces, array $sources): ?array
    {
        $config = [];

        foreach (['x', 'y', 'w'] as $key) {
            if ($row[$key] !== '') {
                $config[$key] = (float) $row[$key];
            }
        }

        if ($row['decoy'] !== '') {
            $config['decoy'] = (bool) (int) $row['decoy'];
        }

        foreach (['wrong_feedback_id', 'wrong_feedback_en', 'digital_pillar'] as $key) {
            if ($row[$key] !== '') {
                $config[$key] = $row[$key];
            }
        }

        if ($pieces !== []) {
            $config['pieces'] = array_map(static fn (array $piece): array => [
                'key'     => $piece['piece_key'],
                'text_id' => $piece['text_id'],
                'text_en' => $piece['text_en'] !== '' ? $piece['text_en'] : $piece['text_id'],
            ], $pieces);
        }

        if ($sources !== []) {
            $config['sources'] = array_map(static fn (array $source): array => [
                'label_id' => $source['label_id'],
                'label_en' => $source['label_en'] !== '' ? $source['label_en'] : $source['label_id'],
                'kind'     => $source['kind'],
                'text_id'  => $source['text_id'],
                'text_en'  => $source['text_en'] !== '' ? $source['text_en'] : $source['text_id'],
            ], $sources);
        }

        return $config === [] ? null : $config;
    }

    /** @param list<array<string, string>> $options */
    private function correctOptionKey(array $options): ?string
    {
        foreach ($options as $option) {
            if ((int) ($option['is_correct'] ?? 0) === 1) {
                return (string) $option['option_key'];
            }
        }

        return null;
    }

    /** @return list<string> */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $v): bool => $v !== ''));
    }

    /** Jawaban alternatif fill_blank_free dipisah tanda `|`. @return list<string> */
    private function splitAccepted(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode('|', $value)), static fn (string $v): bool => $v !== ''));
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

        foreach ($sheets['items'] as $row) {
            $perNode[$row['node_ref']]['items'] = ($perNode[$row['node_ref']]['items'] ?? 0) + 1;
        }

        foreach ($sheets['hints'] as $row) {
            $ref = $row['node_ref'] !== '' ? $row['node_ref'] : '(item)';
            $perNode[$ref]['hints'] = ($perNode[$ref]['hints'] ?? 0) + 1;
        }

        foreach ($sheets['distractors'] as $row) {
            $perNode[$row['node_ref']]['distractors'] = ($perNode[$row['node_ref']]['distractors'] ?? 0) + 1;
        }

        $optionsPerItem = $this->groupBy($sheets['options'], 'item_key');

        foreach ($sheets['items'] as $row) {
            $perNode[$row['node_ref']]['options'] = ($perNode[$row['node_ref']]['options'] ?? 0)
                + count($optionsPerItem[$row['item_key']] ?? []);
        }

        $nodes = model(ChallengeNodeModel::class)->allActive();

        foreach ($nodes as $node) {
            $ref     = $this->nodeRefFor($node->level_id, $node->sequence);
            $minimum = $node->itemsPerRound();
            $items   = $perNode[$ref]['items'] ?? 0;

            if ($ref !== null && $items > 0 && $items === $minimum) {
                $warnings[] = [
                    'sheet'   => 'items',
                    'row'     => 0,
                    'message' => "Tantangan {$ref} hanya punya {$items} soal, sama persis dengan jumlah soal per permainan (items_per_round). Tambahkan soal cadangan (sebaiknya 1,5–2 kali lipat) agar siswa tidak selalu mendapat soal yang sama.",
                ];
            }
        }

        return [
            'summary' => [
                'nodes'       => count($sheets['nodes']),
                'passages'    => count($sheets['passages']),
                'items'       => count($sheets['items']),
                'options'     => count($sheets['options']),
                'hints'       => count($sheets['hints']),
                'distractors' => count($sheets['distractors']),
                'library'       => count($sheets['library']),
                'library_media' => count($sheets['library_media']),
                'per_node'    => $perNode,
            ],
            'warnings' => $warnings,
        ];
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

    /** @return list<string> */
    /**
     * Baris contoh templat, saling konsisten antar-sheet: setiap `item_key`
     * yang dirujuk sheet options/pieces/sources punya barisnya di sheet
     * items, sehingga templat yang diunggah apa adanya lolos pratinjau.
     *
     * @param list<string> $headers
     *
     * @return list<list<string>>
     */
    private function exampleRows(string $sheet, array $headers): array
    {
        $rows = match ($sheet) {
            'nodes' => [[
                'node_ref' => 'tmg-2', 'title_id' => 'Teka-teki Rumpang', 'title_en' => 'Fill in the Blanks',
                'instruction_id' => 'Isi bagian rumpang.', 'instruction_en' => 'Fill in the blanks.',
                'items_per_round' => '4', 'require_reason' => '0', 'use_word_bank' => '1', 'distractor_count' => '2',
            ]],
            'distractors' => [['node_ref' => 'tmg-2', 'text_id' => 'salju', 'text_en' => 'snow']],
            'passages'    => [[
                'passage_key' => 'tmg-teks-a', 'level_code' => 'temanggung', 'title_id' => 'Tanah Subur',
                'title_en' => 'Fertile Land', 'body_id' => 'Isi teks bacaan…', 'body_en' => 'Passage text…',
                'reference_source' => 'Dinas Pendidikan Temanggung',
            ]],
            'items' => [
                [
                    'item_key' => 'tmg-2-01', 'node_ref' => 'tmg-2', 'sequence' => '1', 'interaction_type' => 'fill_blank_bank',
                    'indicator' => 'literasi', 'prompt_id' => 'Gunung ___ ada di Temanggung.', 'prompt_en' => 'Mount ___ is in Temanggung.',
                    'passage_key' => 'tmg-teks-a', 'answer_id' => 'Sumbing', 'answer_en' => 'Sumbing', 'scorable' => '1', 'review_status' => 'draft',
                ],
                [
                    'item_key' => 'tmg-5-01', 'node_ref' => 'tmg-5', 'sequence' => '1', 'interaction_type' => 'single_choice',
                    'indicator' => 'literasi', 'prompt_id' => 'Di antara dua gunung apa Temanggung berada?',
                    'prompt_en' => 'Between which two mountains does Temanggung lie?', 'passage_key' => 'tmg-teks-a',
                    'scorable' => '1', 'review_status' => 'draft',
                ],
                [
                    'item_key' => 'wnb-1-01', 'node_ref' => 'wnb-1', 'sequence' => '1', 'interaction_type' => 'ordering',
                    'indicator' => 'literasi', 'prompt_id' => 'Susun langkahnya.', 'prompt_en' => 'Put the steps in order.',
                    'answer_id' => 'a', 'scorable' => '1', 'review_status' => 'draft',
                ],
                [
                    'item_key' => 'wnb-3-01', 'node_ref' => 'wnb-3', 'sequence' => '1', 'interaction_type' => 'verdict_card',
                    'indicator' => 'sikap', 'prompt_id' => 'Pengunjung boleh memanjat candi.', 'prompt_en' => 'Visitors may climb the temple.',
                    'answer_id' => 'salah', 'scorable' => '1', 'review_status' => 'draft',
                ],
            ],
            'options' => [[
                'option_key' => 'tmg-5-01-a', 'item_key' => 'tmg-5-01', 'label_id' => 'Sindoro dan Sumbing',
                'label_en' => 'Sindoro and Sumbing', 'is_correct' => '1', 'feedback_id' => 'Tepat!', 'feedback_en' => 'Correct!',
                'display_order' => '1',
            ]],
            'pieces'  => [['item_key' => 'wnb-1-01', 'piece_key' => 'a', 'text_id' => 'Siapkan bahan.', 'text_en' => 'Prepare the ingredients.']],
            'sources' => [[
                'item_key' => 'wnb-3-01', 'label_id' => 'Sumber A', 'label_en' => 'Source A', 'kind' => 'official',
                'text_id' => 'Pengumuman resmi pengelola.', 'text_en' => 'Official site notice.',
            ]],
            'hints' => [['node_ref' => 'tmg-2', 'sequence' => '1', 'text_id' => 'Baca kalimatnya sampai habis.', 'text_en' => 'Read the whole sentence.']],
            'library' => [[
                'level_code' => 'temanggung', 'sequence' => '1', 'title_id' => 'Mengenal Temanggung', 'title_en' => 'Getting to Know Temanggung',
                'body_id'    => "Temanggung berada di antara Gunung Sindoro dan Gunung Sumbing.\n\n## Tanah yang subur\n- Kopi\n- Tembakau\n\n> Tahukah kamu? Hari jadi Temanggung diperingati tiap 10 November.\n\nSumber: Pemerintah Kabupaten Temanggung",
                'body_en'    => "Temanggung lies between Mount Sindoro and Mount Sumbing.\n\n## Fertile land\n- Coffee\n- Tobacco\n\n> Did you know? Temanggung celebrates its anniversary every 10 November.\n\nSource: Temanggung Regency Government",
                'is_active'  => '1',
            ]],
            'library_media' => [[
                'level_code' => 'temanggung', 'page_sequence' => '1', 'sequence' => '1', 'media_kind' => 'image',
                'external_url' => 'https://commons.wikimedia.org/wiki/File:Sindoro_sumbing.jpg',
                'caption_id' => 'Gunung Sindoro dan Sumbing', 'caption_en' => 'Mount Sindoro and Mount Sumbing',
                'credit' => 'Wikimedia Commons (lisensi bebas; pengarang di halaman berkas)',
            ]],
            default => [],
        };

        return array_map(
            static fn (array $row): array => array_map(static fn (string $column): string => $row[$column] ?? '', $headers),
            $rows,
        );
    }

    private function nullIfBlank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
