<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\ChallengeOptionModel;
use App\Models\HintModel;
use App\Models\LearningIndicatorModel;
use App\Models\LevelModel;
use App\Models\MediaAssetModel;
use App\Models\ReadingPassageModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Memuat workbook bank soal (XLSX) ke tabel konten dalam satu transaction.
 * Format 8 sheet ditetapkan di 07_FEATURE_INTEGRATION.md (FITUR 12a).
 */
class ContentImportService
{
    /** Awalan node_ref → kode level */
    private const LEVEL_PREFIX = ['tmg' => 'temanggung', 'mgl' => 'magelang', 'wnb' => 'wonosobo'];

    private const INTERACTION_TYPES = [
        'puzzle_arrange', 'ordering', 'fill_blank_bank', 'fill_blank_free',
        'verdict_card', 'verdict_reason', 'single_choice', 'source_trust', 'find_object',
    ];

    /** Header per sheet; kolom di luar daftar ini diabaikan. */
    private const SHEETS = [
        'nodes'       => ['node_ref', 'title_id', 'title_en', 'instruction_id', 'instruction_en', 'description_id', 'description_en', 'items_per_round', 'verdict_options', 'require_reason', 'use_word_bank', 'distractor_count'],
        'distractors' => ['node_ref', 'text_id', 'text_en'],
        'passages'    => ['passage_key', 'level_code', 'title_id', 'title_en', 'body_id', 'body_en', 'media_asset_key', 'reference_source'],
        'items'       => ['item_key', 'node_ref', 'sequence', 'interaction_type', 'indicator', 'prompt_id', 'prompt_en', 'source_text_id', 'source_text_en', 'passage_key', 'answer_id', 'answer_en', 'sample_reason_id', 'sample_reason_en', 'x', 'y', 'w', 'decoy', 'wrong_feedback_id', 'wrong_feedback_en', 'digital_pillar', 'media_asset_key', 'scorable', 'review_status', 'review_note', 'reference_source'],
        'options'     => ['option_key', 'item_key', 'label_id', 'label_en', 'is_correct', 'feedback_id', 'feedback_en', 'media_asset_key', 'display_order'],
        'pieces'      => ['item_key', 'piece_key', 'text_id', 'text_en'],
        'sources'     => ['item_key', 'label_id', 'label_en', 'kind', 'text_id', 'text_en'],
        'hints'       => ['node_ref', 'item_key', 'sequence', 'text_id', 'text_en'],
    ];

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

        model(AuditLogModel::class)->record('content_import', [
            'staff_user_id' => $staffId,
            'target_type'   => 'workbook',
            'target_id'     => basename($xlsxPath),
            'metadata'      => ['file_sha256' => hash_file('sha256', $xlsxPath) ?: null, 'written' => $written],
        ]);

        return ['ok' => true, 'written' => $written, 'errors' => [], 'warnings' => $parsed['warnings']];
    }

    /** Workbook templat kosong dengan header dan satu baris contoh per sheet. */
    public function template(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach (self::SHEETS as $name => $headers) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($name);
            $sheet->fromArray($headers, null, 'A1');

            $example = $this->exampleRow($name);

            if ($example !== []) {
                $sheet->fromArray($example, null, 'A2');
            }
        }

        $path = WRITEPATH . 'uploads/gelita-bank-soal-template.xlsx';

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $writer = new XlsxWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($path);

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
            throw new \RuntimeException("Workbook tidak ditemukan: {$xlsxPath}");
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

            if (! $book->sheetNameExists($name)) {
                $warnings[] = ['sheet' => $name, 'row' => 0, 'message' => 'Sheet tidak ada di workbook, dilewati.'];
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
        ];

        foreach (['nodes', 'distractors', 'passages', 'items', 'options', 'pieces', 'sources', 'hints'] as $name) {
            foreach ($sheets[$name] as $row) {
                $message = $this->validateRow($name, $row, $context);

                if ($message !== null) {
                    $errors[] = ['sheet' => $name, 'row' => $row['__row'], 'message' => $message];
                }
            }
        }

        foreach ($sheets['items'] as $row) {
            if (($row['review_status'] ?? '') === 'needs_verification') {
                $warnings[] = [
                    'sheet'   => 'items',
                    'row'     => $row['__row'],
                    'message' => "Item {$row['item_key']} masih berstatus needs_verification.",
                ];
            }

            $mediaKey = trim((string) ($row['media_asset_key'] ?? ''));

            if ($mediaKey !== '' && ! isset($media[$mediaKey])) {
                $warnings[] = [
                    'sheet'   => 'items',
                    'row'     => $row['__row'],
                    'message' => "media_asset_key '{$mediaKey}' belum terdaftar di media_assets.",
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
                ?? ($row['text_id'] === '' ? 'text_id wajib diisi.' : null),
            'passages' => $this->validatePassage($row, $context),
            'items'    => $this->validateItem($row, $context),
            'options'  => $this->validateReference($row['item_key'] ?? '', $context['items'], 'item_key')
                ?? ($row['label_id'] === '' ? 'label_id wajib diisi.' : null),
            'pieces' => $this->validateReference($row['item_key'] ?? '', $context['items'], 'item_key')
                ?? ($row['piece_key'] === '' ? 'piece_key wajib diisi.' : null),
            'sources' => $this->validateReference($row['item_key'] ?? '', $context['items'], 'item_key')
                ?? (! in_array($row['kind'], ['official', 'anonymous', 'chain_message', 'blog'], true)
                    ? "kind '{$row['kind']}' tidak dikenali."
                    : null),
            'hints'  => $this->validateHint($row, $context),
            default  => null,
        };
    }

    private function validateNodeRef(string $nodeRef, array $context): ?string
    {
        $nodeRef = trim($nodeRef);

        if ($nodeRef === '') {
            return 'node_ref wajib diisi.';
        }

        if (! isset($context['nodes'][$nodeRef])) {
            return "node_ref '{$nodeRef}' tidak cocok dengan challenge_nodes mana pun.";
        }

        return null;
    }

    private function validatePassage(array $row, array $context): ?string
    {
        if (trim((string) $row['passage_key']) === '') {
            return 'passage_key wajib diisi.';
        }

        if (! isset($context['levels'][$row['level_code']])) {
            return "level_code '{$row['level_code']}' tidak dikenali.";
        }

        if (trim((string) $row['body_id']) === '' || trim((string) $row['body_en']) === '') {
            return 'body_id dan body_en wajib diisi.';
        }

        return null;
    }

    private function validateItem(array $row, array $context): ?string
    {
        if (trim((string) $row['item_key']) === '') {
            return 'item_key wajib diisi.';
        }

        $nodeError = $this->validateNodeRef($row['node_ref'] ?? '', $context);

        if ($nodeError !== null) {
            return $nodeError;
        }

        if (! in_array($row['interaction_type'], self::INTERACTION_TYPES, true)) {
            return "interaction_type '{$row['interaction_type']}' tidak dikenali.";
        }

        if ($row['indicator'] !== '' && ! isset($context['indicators'][$row['indicator']])) {
            return "indicator '{$row['indicator']}' tidak terdaftar di learning_indicators.";
        }

        $passageKey = trim((string) $row['passage_key']);

        if ($passageKey !== ''
            && ! isset($context['passages'][$passageKey])
            && model(ReadingPassageModel::class)->findByKey($passageKey) === null) {
            return "passage_key '{$passageKey}' tidak ada di sheet passages maupun di database.";
        }

        if (in_array($row['interaction_type'], ['verdict_card', 'verdict_reason'], true)
            && ! in_array(mb_strtolower((string) $row['answer_id']), ['benar', 'salah', 'pendapat'], true)) {
            return "answer_id untuk {$row['interaction_type']} harus benar, salah, atau pendapat.";
        }

        if (in_array($row['interaction_type'], ['fill_blank_bank', 'fill_blank_free'], true)
            && trim((string) $row['answer_id']) === '') {
            return 'answer_id wajib diisi untuk butir isian.';
        }

        // Item yang sudah pernah dijawab tidak boleh berganti kunci, agar data lama tetap sebanding.
        $existing = model(ChallengeItemModel::class)->findByKey((string) $row['item_key']);

        if ($existing !== null && model(ChallengeItemModel::class)->hasResponses($existing->id)) {
            $incoming = $this->answerKeyFor($row, []);

            if (json_encode($incoming, JSON_UNESCAPED_UNICODE) !== json_encode($existing->answerKey(), JSON_UNESCAPED_UNICODE)) {
                return "Item {$row['item_key']} sudah punya jawaban peserta; kunci jawabannya tidak boleh diubah.";
            }
        }

        return null;
    }

    private function validateHint(array $row, array $context): ?string
    {
        $nodeRef = trim((string) $row['node_ref']);
        $itemKey = trim((string) $row['item_key']);

        if ($nodeRef === '' && $itemKey === '') {
            return 'Isi salah satu: node_ref atau item_key.';
        }

        if ($nodeRef !== '') {
            $error = $this->validateNodeRef($nodeRef, $context);

            if ($error !== null) {
                return $error;
            }
        }

        if ($itemKey !== '') {
            $error = $this->validateReference($itemKey, $context['items'], 'item_key');

            if ($error !== null && model(ChallengeItemModel::class)->findByKey($itemKey) === null) {
                return $error;
            }
        }

        if (trim((string) $row['text_id']) === '') {
            return 'text_id wajib diisi.';
        }

        return null;
    }

    private function validateReference(string $value, array $known, string $label): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return "{$label} wajib diisi.";
        }

        return isset($known[$value]) ? null : "{$label} '{$value}' tidak ada di sheet terkait.";
    }

    // -------------------------------------------------------------- tulis

    /** @return array<string, int> */
    private function write(array $parsed, string $mode): array
    {
        $sheets  = $parsed['sheets'];
        $nodes   = $parsed['nodes'];
        $media   = $parsed['media'];
        $written = array_fill_keys(array_keys(self::SHEETS), 0);

        $written['nodes'] = $this->writeNodes($sheets['nodes'], $sheets['distractors'], $nodes);

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

        return $written;
    }

    /**
     * @param list<array<string, string>> $rows
     * @param list<array<string, string>> $distractorRows
     * @param array<string, int>          $nodes
     */
    private function writeNodes(array $rows, array $distractorRows, array $nodes): int
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
            ], static fn ($value): bool => $value !== '') + ['config_json' => $config]);

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
                'media_asset_id'   => $media[$row['media_asset_key']] ?? null,
                'reference_source' => $this->nullIfBlank($row['reference_source']),
                'is_active'        => 1,
            ];

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
                'media_asset_id'    => $media[$row['media_asset_key']] ?? null,
                'indicator_id'      => $indicators[$row['indicator']] ?? null,
                'reference_source'  => $this->nullIfBlank($row['reference_source']),
                'review_status'     => $row['review_status'] !== '' ? $row['review_status'] : 'draft',
                'review_note'       => $this->nullIfBlank($row['review_note']),
                'scorable'          => $row['scorable'] !== '' ? (int) $row['scorable'] : 1,
                'is_active'         => 1,
            ];

            $existing = $model->findByKey($itemKey);

            if ($existing !== null) {
                if ($mode === 'insert') {
                    throw new \RuntimeException("Item {$itemKey} sudah ada; mode 'insert' tidak menimpa baris.");
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
                'media_asset_id'    => $media[$row['media_asset_key']] ?? null,
                'is_correct'        => (int) ($row['is_correct'] !== '' ? $row['is_correct'] : 0),
                'feedback_id'       => $this->nullIfBlank($row['feedback_id']),
                'feedback_en'       => $this->nullIfBlank($row['feedback_en']),
                'display_order'     => $row['display_order'] !== '' ? (int) $row['display_order'] : 1,
            ];

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
                    'message' => "Node {$ref} hanya punya {$items} butir, persis sebesar items_per_round. Bank soal tidak punya cadangan.",
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
            $prefix    = $levelCode === null ? null : array_search($levelCode, self::LEVEL_PREFIX, true);

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
                $prefix = array_search($code, self::LEVEL_PREFIX, true);

                return $prefix === false ? null : $prefix . '-' . $sequence;
            }
        }

        return null;
    }

    /** @return list<string> */
    private function exampleRow(string $sheet): array
    {
        return match ($sheet) {
            'nodes'       => ['tmg-2', 'Teka-teki Rumpang', 'Fill in the Blanks', 'Isi bagian rumpang.', 'Fill in the blanks.', '', '', '4', '', '0', '1', '2'],
            'distractors' => ['tmg-2', 'salju', 'snow'],
            'passages'    => ['tmg-teks-a', 'temanggung', 'Tanah Subur', 'Fertile Land', 'Isi teks bacaan…', 'Passage text…', '', 'Dinas Pendidikan Temanggung'],
            'items'       => ['tmg-2-01', 'tmg-2', '1', 'fill_blank_bank', 'literasi', 'Gunung ___ ada di Temanggung.', 'Mount ___ is in Temanggung.', '', '', 'tmg-teks-a', 'Sumbing', 'Sumbing', '', '', '', '', '', '', '', '', '', '', '1', 'draft', '', ''],
            'options'     => ['tmg-5-01-a', 'tmg-5-01', 'Sindoro dan Sumbing', 'Sindoro and Sumbing', '1', 'Tepat!', 'Correct!', '', '1'],
            'pieces'      => ['wnb-1-01', 'a', 'Siapkan bahan.', 'Prepare the ingredients.'],
            'sources'     => ['wnb-3-01', 'Sumber A', 'Source A', 'official', 'Pengumuman resmi pengelola.', 'Official site notice.'],
            'hints'       => ['tmg-2', '', '1', 'Baca kalimatnya sampai habis.', 'Read the whole sentence.'],
            default       => [],
        };
    }

    private function nullIfBlank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
