<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\ChallengeOptionModel;
use App\Models\DialogueModel;
use App\Models\LearningIndicatorModel;
use App\Models\LevelModel;
use App\Models\LibraryPageModel;
use App\Models\ReadingPassageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Kelola konten permainan: level, node, butir, opsi, teks bacaan, pustaka,
 * dialog, dan impor workbook bank soal. Hanya role `admin`.
 *
 * Setiap perubahan mem-flush cache ContentRepository dan menulis `audit_logs`.
 */
class ContentController extends BaseAdminController
{
    /** interaction_type yang sah untuk tiap engine (01_DATABASE.md). */
    private const ENGINE_INTERACTIONS = [
        'puzzle'  => ['puzzle_arrange', 'ordering'],
        'rumpang' => ['fill_blank_bank', 'fill_blank_free'],
        'boleh'   => ['verdict_card', 'verdict_reason'],
        'pilihan' => ['single_choice', 'source_trust'],
        'cari'    => ['find_object'],
    ];

    /** Berkas pratinjau impor; disimpan di luar public/. */
    private const UPLOAD_DIR = WRITEPATH . 'uploads/';

    // ------------------------------------------------------------ ringkasan

    public function index(): string
    {
        $content = service('contentRepository');
        $levels  = [];

        foreach ($content->levels() as $level) {
            $nodes = $content->nodesForLevel($level->id);
            $bank  = [];

            foreach ($nodes as $node) {
                $bank[$node->id] = count($content->itemBank($node->id));
            }

            $levels[] = ['level' => $level, 'nodes' => $nodes, 'bank' => $bank];
        }

        return $this->panel('admin/content/index', 'Konten', [
            'levels'  => $levels,
            'version' => $content->version(),
        ]);
    }

    // ---------------------------------------------------------------- level

    public function level(int $levelId): string
    {
        $level = model(LevelModel::class)->find($levelId);

        if ($level === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        return $this->panel('admin/content/level', 'Sunting wilayah', [
            'level' => $level,
            'nodes' => model(ChallengeNodeModel::class)->forLevel($levelId),
        ]);
    }

    public function updateLevel(int $levelId): RedirectResponse
    {
        $levels = model(LevelModel::class);

        if ($levels->find($levelId) === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        // name_en wajib karena LevelModel mewajibkannya; tanpa aturan ini
        // simpanan akan ditolak model secara diam-diam.
        $rules = [
            'name_id'    => 'required|max_length[100]',
            'name_en'    => 'required|max_length[100]',
            'difficulty' => 'required|in_list[mudah,sedang,sulit]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('admin/konten/level/' . $levelId))
                ->with('errors', $this->validator->getErrors());
        }

        $saved = $levels->update($levelId, $this->bilingual(['name', 'focus', 'cp', 'tp', 'intro']) + [
            'difficulty' => (string) $this->request->getPost('difficulty'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        if (! $saved) {
            return $this->back('admin/konten/level/' . $levelId, 'Wilayah ditolak: ' . $this->modelErrors($levels));
        }

        $this->afterContentChange('level', $levelId);

        return $this->done('admin/konten/level/' . $levelId, 'Wilayah diperbarui.');
    }

    // ----------------------------------------------------------------- node

    public function node(int $nodeId): string
    {
        $nodes = model(ChallengeNodeModel::class);
        $node  = $nodes->find($nodeId);

        if ($node === null) {
            throw PageNotFoundException::forPageNotFound("Node {$nodeId} tidak ditemukan.");
        }

        $items = model(ChallengeItemModel::class)
            ->where('challenge_node_id', $nodeId)
            ->orderBy('sequence', 'ASC')
            ->findAll();

        $options = model(ChallengeOptionModel::class)->forItems(
            array_map(static fn ($item): int => $item->id, $items),
        );

        return $this->panel('admin/content/node', 'Sunting tantangan', [
            'node'         => $node,
            'level'        => model(LevelModel::class)->find($node->level_id),
            'items'        => $items,
            'options'      => $options,
            'indicators'   => model(LearningIndicatorModel::class)->map(),
            'passages'     => model(ReadingPassageModel::class)->forLevel($node->level_id),
            'interactions' => self::ENGINE_INTERACTIONS[$node->engine_type] ?? [],
            'locked'       => $this->nodeHasAttempts($nodeId),
        ]);
    }

    public function updateNode(int $nodeId): RedirectResponse
    {
        $nodes = model(ChallengeNodeModel::class);
        $node  = $nodes->find($nodeId);

        if ($node === null) {
            throw PageNotFoundException::forPageNotFound("Node {$nodeId} tidak ditemukan.");
        }

        $back = 'admin/konten/node/' . $nodeId;

        // title_en wajib: ChallengeNodeModel menolak baris tanpa judul Inggris
        if (! $this->validate([
            'title_id'    => 'required|max_length[150]',
            'title_en'    => 'required|max_length[150]',
            'engine_type' => 'required|valid_engine_type',
        ])) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $engine = (string) $this->request->getPost('engine_type');

        // Mengubah engine setelah ada attempt akan membuat data lama tidak sebanding
        if ($engine !== $node->engine_type && $this->nodeHasAttempts($nodeId)) {
            return $this->back($back, 'Jenis tantangan tidak dapat diubah: node ini sudah punya percobaan.');
        }

        $config = $this->jsonField('config_json');

        if ($config === false) {
            return $this->back($back, 'config_json bukan JSON yang valid.');
        }

        $saved = $nodes->update($nodeId, $this->bilingual(['title', 'instruction', 'description']) + [
            'engine_type'  => $engine,
            'variant_code' => $this->nullIfBlank($this->request->getPost('variant_code')),
            'config_json'  => $config,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        if (! $saved) {
            return $this->back($back, 'Tantangan ditolak: ' . $this->modelErrors($nodes));
        }

        $this->afterContentChange('node', $nodeId);

        return $this->done($back, 'Tantangan diperbarui.');
    }

    // ----------------------------------------------------------------- item

    public function createItem(int $nodeId): RedirectResponse
    {
        $node = model(ChallengeNodeModel::class)->find($nodeId);

        if ($node === null) {
            throw PageNotFoundException::forPageNotFound("Node {$nodeId} tidak ditemukan.");
        }

        $back    = 'admin/konten/node/' . $nodeId;
        $allowed = self::ENGINE_INTERACTIONS[$node->engine_type] ?? [];

        $rules = [
            'item_key'         => 'required|max_length[60]|is_unique[challenge_items.item_key]',
            'interaction_type' => 'required|in_list[' . implode(',', $allowed) . ']',
            'prompt_id'        => 'permit_empty|max_length[1000]',
            'sequence'         => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $payload = $this->itemPayload();

        if ($payload === null) {
            return $this->back($back, 'answer_key_json atau config_json bukan JSON yang valid.');
        }

        if ((int) $payload['scorable'] === 1 && $payload['answer_key_json'] === null) {
            return $this->back($back, 'Butir yang dinilai wajib punya answer_key_json.');
        }

        $items  = model(ChallengeItemModel::class);
        $itemId = $items->insert($payload + [
            'challenge_node_id' => $nodeId,
            'item_key'          => (string) $this->request->getPost('item_key'),
            'interaction_type'  => (string) $this->request->getPost('interaction_type'),
        ], true);

        if ($itemId === false) {
            return $this->back($back, 'Butir ditolak: ' . $this->modelErrors($items));
        }

        $this->afterContentChange('item', (int) $itemId);

        return $this->done($back, 'Butir ditambahkan.');
    }

    public function updateItem(int $itemId): RedirectResponse
    {
        $items = model(ChallengeItemModel::class);
        $item  = $items->find($itemId);

        if ($item === null) {
            throw PageNotFoundException::forPageNotFound("Butir {$itemId} tidak ditemukan.");
        }

        $node    = model(ChallengeNodeModel::class)->find($item->challenge_node_id);
        $back    = 'admin/konten/node/' . $item->challenge_node_id;
        $allowed = self::ENGINE_INTERACTIONS[$node?->engine_type] ?? [];

        if (! $this->validate([
            'interaction_type' => 'required|in_list[' . implode(',', $allowed) . ']',
        ])) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $payload = $this->itemPayload();

        if ($payload === null) {
            return $this->back($back, 'answer_key_json atau config_json bukan JSON yang valid.');
        }

        if ((int) $payload['scorable'] === 1 && $payload['answer_key_json'] === null) {
            return $this->back($back, 'Butir yang dinilai wajib punya answer_key_json.');
        }

        $saved = $items->update($itemId, $payload + [
            'interaction_type' => (string) $this->request->getPost('interaction_type'),
        ]);

        if (! $saved) {
            return $this->back($back, 'Butir ditolak: ' . $this->modelErrors($items));
        }

        $this->afterContentChange('item', $itemId);

        return $this->done($back, 'Butir diperbarui.');
    }

    public function deleteItem(int $itemId): RedirectResponse
    {
        $items = model(ChallengeItemModel::class);
        $item  = $items->find($itemId);

        if ($item === null) {
            throw PageNotFoundException::forPageNotFound("Butir {$itemId} tidak ditemukan.");
        }

        $back = 'admin/konten/node/' . $item->challenge_node_id;

        // Butir yang sudah pernah dijawab adalah data penelitian: nonaktifkan, jangan hapus
        if ($items->hasResponses($itemId)) {
            $items->update($itemId, ['is_active' => 0]);
            $this->afterContentChange('item', $itemId);

            return $this->done($back, 'Butir sudah pernah dijawab, jadi dinonaktifkan — bukan dihapus.');
        }

        $items->delete($itemId);
        $this->afterContentChange('item', $itemId);

        return $this->done($back, 'Butir dihapus.');
    }

    public function saveOptions(int $itemId): RedirectResponse
    {
        $item = model(ChallengeItemModel::class)->find($itemId);

        if ($item === null) {
            throw PageNotFoundException::forPageNotFound("Butir {$itemId} tidak ditemukan.");
        }

        $back    = 'admin/konten/node/' . $item->challenge_node_id;
        $rows    = (array) ($this->request->getPost('options') ?? []);
        $options = model(ChallengeOptionModel::class);
        $correct = 0;
        $saved   = 0;

        // Butir pilihan memakai satu radio `correct_option` agar tepat satu opsi
        // dapat ditandai benar; butir lain menandainya per baris.
        $correctKey = trim((string) ($this->request->getPost('correct_option') ?? ''));

        // Kumpulkan dulu, baru tulis: aturan "tepat satu opsi benar" harus
        // memutuskan sebelum ada baris yang tersentuh.
        $payloads = [];

        foreach ($rows as $index => $row) {
            $key   = trim((string) ($row['option_key'] ?? ''));
            $label = trim((string) ($row['label_id'] ?? ''));

            if ($key === '' || $label === '') {
                continue;
            }

            $isCorrect = $correctKey === ''
                ? (! empty($row['is_correct']) ? 1 : 0)
                : (int) ($key === $correctKey);
            $correct += $isCorrect;

            $payloads[$key] = [
                'challenge_item_id' => $itemId,
                'option_key'        => $key,
                'label_id'          => $label,
                'label_en'          => $this->nullIfBlank($row['label_en'] ?? null),
                'feedback_id'       => $this->nullIfBlank($row['feedback_id'] ?? null),
                'feedback_en'       => $this->nullIfBlank($row['feedback_en'] ?? null),
                'is_correct'        => $isCorrect,
                'display_order'     => (int) ($row['display_order'] ?? $index + 1),
            ];
        }

        if (in_array($item->interaction_type, ['single_choice', 'source_trust'], true) && $correct !== 1) {
            return $this->back($back, 'Butir pilihan wajib punya tepat satu opsi benar.');
        }

        foreach ($payloads as $key => $payload) {
            $existing = $options->findByKey($itemId, $key);

            $written = $existing === null
                ? $options->insert($payload, false)
                : $options->update($existing->id, $payload);

            if ($written === false) {
                return $this->back($back, "Opsi {$key} ditolak: " . $this->modelErrors($options));
            }

            $saved++;
        }

        $this->afterContentChange('options', $itemId);

        return $this->done($back, "{$saved} opsi disimpan.");
    }

    // ------------------------------------------------------- impor bank soal

    public function importForm(): string
    {
        return $this->panel('admin/content/import', 'Impor bank soal', [
            'preview' => session('import_preview'),
            'history' => model(AuditLogModel::class)
                ->where('action', 'content_import')
                ->orderBy('occurred_at', 'DESC')
                ->findAll(10),
        ]);
    }

    public function importPreview(): RedirectResponse
    {
        if (! $this->validate(['file' => 'uploaded[file]|ext_in[file,xlsx]|max_size[file,20480]'])) {
            return redirect()->to(site_url('admin/konten/impor-bank'))
                ->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');
        $path = $this->storeUpload($file);

        if ($path === null) {
            return $this->back('admin/konten/impor-bank', 'Berkas gagal disimpan sementara.');
        }

        $result = service('contentImportService')->preview($path);

        session()->set('import_preview', $result + ['file' => basename($path), 'at' => date('Y-m-d H:i:s')]);

        return redirect()->to(site_url('admin/konten/impor-bank'));
    }

    public function importRun(): RedirectResponse
    {
        $preview = session('import_preview');
        $back    = 'admin/konten/impor-bank';

        if (! is_array($preview) || empty($preview['ok'])) {
            return $this->back($back, 'Jalankan pratinjau tanpa galat lebih dulu.');
        }

        $path = self::UPLOAD_DIR . basename((string) $preview['file']);

        if (! is_file($path)) {
            session()->remove('import_preview');

            return $this->back($back, 'Berkas pratinjau sudah tidak ada. Unggah ulang.');
        }

        $result = service('contentImportService')->import($path, $this->staffId());

        @unlink($path);
        session()->remove('import_preview');

        if (! $result['ok']) {
            return redirect()->to(site_url($back))->with('import_result', $result)
                ->with('error', 'Impor dibatalkan: ada galat pada workbook.');
        }

        service('contentRepository')->flush();

        return redirect()->to(site_url($back))->with('import_result', $result)
            ->with('message', 'Impor bank soal selesai.');
    }

    public function importTemplate(): DownloadResponse
    {
        $path = service('contentImportService')->template();

        return $this->response->download($path, null)->setFileName('gelita-bank-soal.xlsx');
    }

    // -------------------------------------------------------- teks & bacaan

    public function passages(int $levelId): string
    {
        $level = model(LevelModel::class)->find($levelId);

        if ($level === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        return $this->panel('admin/content/passages', 'Teks bacaan', [
            'level'    => $level,
            'passages' => model(ReadingPassageModel::class)->forLevel($levelId),
            'usage'    => $this->passageUsage($levelId),
        ]);
    }

    public function savePassages(int $levelId): RedirectResponse
    {
        $back     = 'admin/konten/bacaan/' . $levelId;
        $passages = model(ReadingPassageModel::class);
        $usage    = $this->passageUsage($levelId);
        $saved    = 0;

        foreach ((array) ($this->request->getPost('passages') ?? []) as $row) {
            $key = trim((string) ($row['passage_key'] ?? ''));

            if ($key === '') {
                continue;
            }

            $existing = $passages->findByKey($key);

            if (! empty($row['_delete'])) {
                if ($existing === null) {
                    continue;
                }

                if (($usage[$existing->id] ?? 0) > 0) {
                    return $this->back($back, "Teks bacaan {$key} masih dirujuk butir soal, jadi tidak dihapus.");
                }

                $passages->delete($existing->id);

                continue;
            }

            $payload = [
                'level_id'         => $levelId,
                'title_id'         => trim((string) ($row['title_id'] ?? '')),
                'title_en'         => $this->nullIfBlank($row['title_en'] ?? null),
                'body_id'          => (string) ($row['body_id'] ?? ''),
                'body_en'          => $this->nullIfBlank($row['body_en'] ?? null),
                'reference_source' => $this->nullIfBlank($row['reference_source'] ?? null),
                'is_active'        => empty($row['is_active']) ? 0 : 1,
            ];

            // `passage_key` hanya dikirim saat menambah: pada update, aturan
            // is_unique[...,id,{id}] milik model tidak dapat mengecualikan
            // baris yang sedang disunting, sehingga selalu ditolak duplikat.
            $written = $existing === null
                ? $passages->insert($payload + ['passage_key' => $key], false)
                : $passages->update($existing->id, $payload);

            if ($written === false) {
                return $this->back($back, "Teks bacaan {$key} ditolak: " . $this->modelErrors($passages));
            }

            $saved++;
        }

        $this->afterContentChange('passages', $levelId);

        return $this->done($back, "{$saved} teks bacaan disimpan.");
    }

    public function library(int $levelId): string
    {
        $level = model(LevelModel::class)->find($levelId);

        if ($level === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        return $this->panel('admin/content/library', 'Pustaka Kedu', [
            'level' => $level,
            'pages' => model(LibraryPageModel::class)->forLevel($levelId),
        ]);
    }

    public function saveLibrary(int $levelId): RedirectResponse
    {
        $pages = model(LibraryPageModel::class);
        $saved = 0;

        foreach ((array) ($this->request->getPost('pages') ?? []) as $index => $row) {
            $title = trim((string) ($row['title_id'] ?? ''));

            if ($title === '') {
                continue;
            }

            $payload = [
                'level_id'  => $levelId,
                'sequence'  => (int) ($row['sequence'] ?? $index + 1),
                'title_id'  => $title,
                'title_en'  => $this->nullIfBlank($row['title_en'] ?? null),
                'body_id'   => (string) ($row['body_id'] ?? ''),
                'body_en'   => $this->nullIfBlank($row['body_en'] ?? null),
                'is_active' => empty($row['is_active']) ? 0 : 1,
            ];

            $id      = (int) ($row['id'] ?? 0);
            $written = $id > 0 ? $pages->update($id, $payload) : $pages->insert($payload, false);

            if ($written === false) {
                return $this->back(
                    'admin/konten/pustaka/' . $levelId,
                    'Halaman "' . $title . '" ditolak: ' . $this->modelErrors($pages),
                );
            }

            $saved++;
        }

        $this->afterContentChange('library', $levelId);

        return $this->done('admin/konten/pustaka/' . $levelId, "{$saved} halaman pustaka disimpan.");
    }

    public function dialogues(int $levelId): string
    {
        $level = model(LevelModel::class)->find($levelId);

        if ($level === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        return $this->panel('admin/content/dialogues', 'Dialog wilayah', [
            'level'      => $level,
            'dialogues'  => model(DialogueModel::class)->forLevel($levelId, 'level_open'),
            'characters' => config('Gelita')->characters,
        ]);
    }

    public function saveDialogues(int $levelId): RedirectResponse
    {
        $dialogues = model(DialogueModel::class);
        $saved     = 0;

        foreach ((array) ($this->request->getPost('dialogues') ?? []) as $index => $row) {
            $text = trim((string) ($row['text_id'] ?? ''));

            if ($text === '') {
                continue;
            }

            $payload = [
                'level_id'       => $levelId,
                'context_code'   => (string) ($row['context_code'] ?? 'level_open'),
                'sequence'       => (int) ($row['sequence'] ?? $index + 1),
                'character_code' => (string) ($row['character_code'] ?? 'jaka'),
                'title_id'       => $this->nullIfBlank($row['title_id'] ?? null),
                'title_en'       => $this->nullIfBlank($row['title_en'] ?? null),
                'text_id'        => $text,
                'text_en'        => $this->nullIfBlank($row['text_en'] ?? null),
                'is_active'      => empty($row['is_active']) ? 0 : 1,
            ];

            $id      = (int) ($row['id'] ?? 0);
            $written = $id > 0 ? $dialogues->update($id, $payload) : $dialogues->insert($payload, false);

            if ($written === false) {
                return $this->back(
                    'admin/konten/dialog/' . $levelId,
                    'Dialog slide ' . $payload['sequence'] . ' ditolak: ' . $this->modelErrors($dialogues),
                );
            }

            $saved++;
        }

        $this->afterContentChange('dialogues', $levelId);

        return $this->done('admin/konten/dialog/' . $levelId, "{$saved} dialog disimpan.");
    }

    // ----------------------------------------------------------- verifikasi

    public function verify(): string
    {
        return $this->panel('admin/content/verify', 'Verifikasi konten', [
            'findings' => $this->runVerification(),
        ]);
    }

    /**
     * Pemeriksaan kelengkapan konten sebelum rilis.
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    private function runVerification(): array
    {
        $content  = service('contentRepository');
        $levels   = $content->levels();
        $findings = [];
        $answers  = [];

        if (count($levels) !== 3) {
            $findings[] = $this->finding('error', 'struktur', 'Jumlah wilayah aktif ' . count($levels) . ', seharusnya 3.');
        }

        foreach ($levels as $level) {
            $nodes = $content->nodesForLevel($level->id);
            $scope = 'wilayah ' . $level->code;

            if (count($nodes) !== 5) {
                $findings[] = $this->finding('error', $scope, 'Jumlah tantangan ' . count($nodes) . ', seharusnya 5.');
            }

            $passageIds = [];

            foreach ($content->passagesForLevel($level->id) as $passage) {
                $passageIds[$passage->id] = true;
            }

            foreach ($nodes as $node) {
                $nodeScope = $scope . ' · node ' . $node->sequence;
                $bank      = $content->itemBank($node->id);
                $perRound  = $node->itemsPerRound();

                if (! in_array($node->engine_type, config('Gelita')->engineTypes, true)) {
                    $findings[] = $this->finding('error', $nodeScope, "engine_type '{$node->engine_type}' tidak dikenali.");
                }

                if (count($bank) < $perRound) {
                    $findings[] = $this->finding('error', $nodeScope, 'Bank soal ' . count($bank) . " butir, minimal {$perRound}.");
                }

                $distractors = count($node->distractors('id'));
                $needed      = (int) $node->config('distractor_count');

                if ($node->engine_type === 'rumpang' && $needed > 0 && $distractors < $needed) {
                    $findings[] = $this->finding('error', $nodeScope, "Pengecoh rumpang {$distractors}, minimal {$needed}.");
                }

                foreach ($bank as $item) {
                    $itemScope = $nodeScope . ' · ' . $item->item_key;

                    if ($item->scorable && $item->answerKey() === []) {
                        $findings[] = $this->finding('error', $itemScope, 'Butir dinilai tetapi tanpa answer_key_json.');
                    }

                    if (in_array($item->interaction_type, ['single_choice', 'source_trust'], true)) {
                        $correct = 0;

                        foreach ($item->loadedOptions() as $option) {
                            $correct += $option->is_correct ? 1 : 0;
                        }

                        if ($correct !== 1) {
                            $findings[] = $this->finding('error', $itemScope, "Opsi benar {$correct}, seharusnya tepat 1.");
                        }
                    }

                    if (in_array($item->interaction_type, ['verdict_card', 'verdict_reason'], true)
                        && ! in_array((string) $item->verdict(), $node->verdictOptions(), true)) {
                        $findings[] = $this->finding('error', $itemScope, 'Kunci verdict di luar verdict_options node.');
                    }

                    if ($item->passage_id !== null && ! isset($passageIds[$item->passage_id])) {
                        $findings[] = $this->finding('error', $itemScope, 'passage_id berasal dari wilayah lain.');
                    }

                    if ($item->review_status === 'needs_verification') {
                        $findings[] = $this->finding('warning', $itemScope, 'Butir masih berstatus needs_verification.');
                    }

                    $signature = $this->answerSignature($item);

                    if ($signature !== null) {
                        if (isset($answers[$signature]) && $answers[$signature] !== $node->id) {
                            $findings[] = $this->finding(
                                'warning',
                                $itemScope,
                                'Jawaban kembar dengan butir di node lain — analisis butir akan menghitung konsep ganda.',
                            );
                        }

                        $answers[$signature] ??= $node->id;
                    }
                }
            }
        }

        return $findings;
    }

    // -------------------------------------------------------------- bantuan

    /** @return array{level: string, scope: string, message: string} */
    private function finding(string $level, string $scope, string $message): array
    {
        return ['level' => $level, 'scope' => $scope, 'message' => $message];
    }

    private function answerSignature(\App\Entities\ChallengeItem $item): ?string
    {
        $key = $item->answerKey();

        if ($key === []) {
            return null;
        }

        return $item->interaction_type . '|' . json_encode($key, JSON_UNESCAPED_UNICODE);
    }

    private function nodeHasAttempts(int $nodeId): bool
    {
        return model(ChallengeAttemptModel::class)->where('challenge_node_id', $nodeId)->countAllResults() > 0;
    }

    /**
     * Pasangan kolom `<field>_id` / `<field>_en` dari POST.
     *
     * @param list<string> $fields
     *
     * @return array<string, ?string>
     */
    private function bilingual(array $fields): array
    {
        $out = [];

        foreach ($fields as $field) {
            $out[$field . '_id'] = (string) $this->request->getPost($field . '_id');
            $out[$field . '_en'] = $this->nullIfBlank($this->request->getPost($field . '_en'));
        }

        return $out;
    }

    /**
     * Payload butir dari POST, atau null bila salah satu kolom JSON rusak.
     *
     * @return array<string, mixed>|null
     */
    private function itemPayload(): ?array
    {
        $answerKey = $this->jsonField('answer_key_json');
        $config    = $this->jsonField('config_json');

        if ($answerKey === false || $config === false) {
            return null;
        }

        return $this->bilingual(['prompt', 'source_text']) + [
            'sequence'         => (int) ($this->request->getPost('sequence') ?? 0),
            'passage_id'       => $this->idOrNull($this->request->getPost('passage_id')),
            'indicator_id'     => $this->idOrNull($this->request->getPost('indicator_id')),
            'answer_key_json'  => $answerKey,
            'config_json'      => $config,
            'reference_source' => $this->nullIfBlank($this->request->getPost('reference_source')),
            'review_status'    => (string) ($this->request->getPost('review_status') ?: 'draft'),
            'review_note'      => $this->nullIfBlank($this->request->getPost('review_note')),
            'scorable'         => $this->request->getPost('scorable') ? 1 : 0,
            'is_active'        => $this->request->getPost('is_active') ? 1 : 0,
        ];
    }

    /**
     * Kolom JSON dari textarea: null bila kosong, false bila tidak valid.
     *
     * @return array<string, mixed>|false|null
     */
    private function jsonField(string $field): array|false|null
    {
        $raw = trim((string) $this->request->getPost($field));

        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : false;
    }

    /** @return array<int, int> passage_id → jumlah butir yang merujuknya */
    private function passageUsage(int $levelId): array
    {
        $rows = db_connect()->table('challenge_items ci')
            ->select('ci.passage_id, COUNT(*) AS total', false)
            ->join('reading_passages rp', 'rp.id = ci.passage_id')
            ->where('rp.level_id', $levelId)
            ->groupBy('ci.passage_id')
            ->get()
            ->getResultArray();

        $usage = [];

        foreach ($rows as $row) {
            $usage[(int) $row['passage_id']] = (int) $row['total'];
        }

        return $usage;
    }

    private function afterContentChange(string $targetType, int $targetId): void
    {
        service('contentRepository')->flush();

        model(AuditLogModel::class)->record('content_update', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => $targetType,
            'target_id'     => (string) $targetId,
        ]);
    }

    /** Berkas pratinjau disimpan di writable/uploads/, di luar public/. */
    private function storeUpload(?\CodeIgniter\HTTP\Files\UploadedFile $file): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return null;
        }

        if (! is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0775, true);
        }

        $name = $file->getRandomName();
        $file->move(self::UPLOAD_DIR, $name);

        return self::UPLOAD_DIR . $name;
    }

    private function idOrNull($value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
