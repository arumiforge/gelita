<?php

namespace App\Controllers\Admin;

use App\Libraries\ContentVerifier;
use App\Libraries\MediaLink;
use App\Libraries\MediaStore;
use App\Models\AuditLogModel;
use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\ChallengeOptionModel;
use App\Models\DialogueModel;
use App\Models\LearningIndicatorModel;
use App\Models\AudioAssetModel;
use App\Models\LevelModel;
use App\Models\LibraryMediaModel;
use App\Models\LibraryPageModel;
use App\Models\ReadingPassageModel;
use App\Models\ScoringProfileModel;
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
        $level  = $levels->find($levelId);

        if ($level === null) {
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

        $media = $this->mediaFields([
            'map_media_id'        => ['media_map', 'map.region.' . $level->code],
            'background_media_id' => ['media_bg', 'bg.' . $level->code . '.region'],
            'badge_media_id'      => ['media_badge', 'reward.badge.' . $level->code],
        ]);

        if (is_string($media)) {
            return $this->back('admin/konten/level/' . $levelId, $media);
        }

        $saved = $levels->update($levelId, $this->bilingual(['name', 'focus', 'cp', 'tp', 'intro']) + $media + [
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

        // Dikelompokkan per butir: editor opsi di view membaca $options[$item->id]
        $options = [];

        foreach (model(ChallengeOptionModel::class)->forItems(
            array_map(static fn ($item): int => $item->id, $items),
        ) as $option) {
            $options[$option->challenge_item_id][] = $option;
        }

        $level = model(LevelModel::class)->find($node->level_id);

        return $this->panel('admin/content/node', 'Sunting tantangan', [
            'node'         => $node,
            'level'        => $level,
            'ref'          => node_ref((string) ($level?->code ?? ''), $node->sequence),
            'items'        => $items,
            'options'      => $options,
            'indicators'   => model(LearningIndicatorModel::class)->map(),
            'passages'     => model(ReadingPassageModel::class)->forLevel($node->level_id),
            'profiles'     => model(ScoringProfileModel::class)->orderBy('code', 'ASC')->orderBy('version', 'ASC')->findAll(),
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

        // Field terpandu (cfg[...]) menimpa kunci yang sama di JSON mentah
        $config = $this->guidedConfig($config ?? []);

        $ref   = $this->nodeRef($node->level_id, $node->sequence);
        $media = $this->mediaFields([
            'scene_media_id'      => ['media_scene', 'challenge.' . $ref . '.scene'],
            'background_media_id' => ['media_bg', 'challenge.' . $ref . '.bg'],
        ]);

        if (is_string($media)) {
            return $this->back($back, $media);
        }

        $audio = $this->audioFields(['audio_intro_id', 'audio_intro_en_id']);

        if (is_string($audio)) {
            return $this->back($back, $audio);
        }

        $saved = $nodes->update($nodeId, $this->bilingual(['title', 'instruction', 'description']) + $media + $audio + [
            'engine_type'        => $engine,
            'variant_code'       => $this->nullIfBlank($this->request->getPost('variant_code')),
            'indicator_id'       => $this->idOrNull($this->request->getPost('indicator_id')),
            'scoring_profile_id' => $this->idOrNull($this->request->getPost('scoring_profile_id')),
            'config_json'        => $config === [] ? null : $config,
            'is_active'          => $this->request->getPost('is_active') ? 1 : 0,
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

        $payload = $this->itemPayload((string) $this->request->getPost('item_key'));

        if (is_string($payload)) {
            return $this->back($back, $payload);
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

        $payload = $this->itemPayload((string) $item->item_key);

        if (is_string($payload)) {
            return $this->back($back, $payload);
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

            $media = (new MediaStore())->resolve(
                (string) ($row['media_key'] ?? ''),
                $this->request->getFile('option_media.' . $index),
                'challenge.option.' . MediaStore::slug(str_starts_with($key, (string) $item->item_key) ? $key : $item->item_key . '-' . $key),
                ['image'],
                $this->staffId(),
            );

            if ($media['error'] !== null) {
                return $this->back($back, "Gambar opsi {$key}: " . $media['error']);
            }

            $payloads[$key] = [
                'challenge_item_id' => $itemId,
                'option_key'        => $key,
                'label_id'          => $label,
                'label_en'          => $this->nullIfBlank($row['label_en'] ?? null),
                'feedback_id'       => $this->nullIfBlank($row['feedback_id'] ?? null),
                'feedback_en'       => $this->nullIfBlank($row['feedback_en'] ?? null),
                'is_correct'        => $isCorrect,
                'media_asset_id'    => $media['id'],
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

        foreach ((array) ($this->request->getPost('passages') ?? []) as $index => $row) {
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

            $media = (new MediaStore())->resolve(
                (string) ($row['media_key'] ?? ''),
                $this->request->getFile('passage_media.' . $index),
                'passage.' . MediaStore::slug($key),
                ['image'],
                $this->staffId(),
            );

            if ($media['error'] !== null) {
                return $this->back($back, "Gambar bacaan {$key}: " . $media['error']);
            }

            $payload = [
                'level_id'         => $levelId,
                'media_asset_id'   => $media['id'],
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

        // Halaman & media nonaktif ikut tampil agar dapat diaktifkan kembali
        $pages = model(LibraryPageModel::class)->where('level_id', $levelId)->orderBy('sequence', 'ASC')->findAll();

        return $this->panel('admin/content/library', 'Pustaka Kedu', [
            'level' => $level,
            'pages' => $pages,
            'media' => model(LibraryMediaModel::class)->forPages(
                array_map(static fn ($page): int => $page->id, $pages),
                false,
            ),
        ]);
    }

    /**
     * Menyimpan seluruh halaman Pustaka satu wilayah beserta medianya dalam
     * satu transaction. Urutan halaman dapat ditukar: urutan lama dipindah
     * sementara ke nomor tinggi dulu agar indeks unik (level_id, sequence)
     * tidak menolak pertukaran.
     */
    public function saveLibrary(int $levelId): RedirectResponse
    {
        $level = model(LevelModel::class)->find($levelId);

        if ($level === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        $back  = 'admin/konten/pustaka/' . $levelId;
        $pages = model(LibraryPageModel::class);
        $rows  = (array) ($this->request->getPost('pages') ?? []);
        $owned = array_map(static fn ($page): int => $page->id, $pages->where('level_id', $levelId)->findAll());
        $db    = db_connect();
        $saved = 0;
        $media = 0;

        $db->transBegin();

        try {
            $parked = 0;

            foreach ($rows as $row) {
                $id = (int) ($row['id'] ?? 0);

                if ($id > 0 && ! in_array($id, $owned, true)) {
                    throw new \RuntimeException('Halaman pustaka bukan milik wilayah ini.');
                }

                // sequence SMALLINT UNSIGNED: nomor parkir 60000+ tetap di bawah batas 65535
                if ($id > 0) {
                    $pages->builder()->where('id', $id)->update(['sequence' => 60000 + $parked++]);
                }
            }

            foreach ($rows as $index => $row) {
                $id    = (int) ($row['id'] ?? 0);
                $title = trim((string) ($row['title_id'] ?? ''));

                if ($id > 0 && ! empty($row['_delete'])) {
                    $pages->delete($id);   // media halaman ikut terhapus (FK CASCADE)

                    continue;
                }

                if ($title === '') {
                    if ($id > 0) {
                        throw new \RuntimeException('Judul Indonesia halaman ' . ($index + 1) . ' wajib diisi.');
                    }

                    continue;
                }

                $payload = [
                    'level_id'  => $levelId,
                    'sequence'  => max(1, (int) ($row['sequence'] ?? $index + 1)),
                    'title_id'  => $title,
                    'title_en'  => $this->nullIfBlank($row['title_en'] ?? null),
                    'body_id'   => (string) ($row['body_id'] ?? ''),
                    // library_pages.body_en NOT NULL: kosong disimpan '' dan permainan
                    // memakai teks Indonesia (Bilingual::text)
                    'body_en'   => trim((string) ($row['body_en'] ?? '')),
                    'is_active' => empty($row['is_active']) ? 0 : 1,
                ];

                $written = $id > 0 ? $pages->update($id, $payload) : $pages->insert($payload, false);

                if ($written === false) {
                    throw new \RuntimeException('Halaman "' . $title . '" ditolak: ' . $this->modelErrors($pages));
                }

                $pageId = $id > 0 ? $id : (int) $pages->getInsertID();
                $media += $this->saveLibraryMedia($pageId, (string) $level->code, $payload['sequence'], $index, (array) ($row['media'] ?? []));
                $saved++;
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            return $this->back($back, $e->getMessage());
        }

        $this->afterContentChange('library', $levelId);

        return $this->done($back, "{$saved} halaman pustaka dan {$media} media disimpan.");
    }

    /**
     * Media satu halaman Pustaka. Tiap baris berupa berkas (asset_key terdaftar
     * atau unggahan baru) ATAU tautan luar; baris yang dikosongkan dihapus.
     *
     * @param list<array<string, mixed>>|array<int, array<string, mixed>> $rows
     */
    private function saveLibraryMedia(int $pageId, string $levelCode, int $pageSequence, int $pageIndex, array $rows): int
    {
        $model = model(LibraryMediaModel::class);
        $store = new MediaStore();
        $count = 0;

        foreach ($rows as $index => $row) {
            $id       = (int) ($row['id'] ?? 0);
            $existing = $id > 0 ? $model->find($id) : null;

            if ($id > 0 && ($existing === null || $existing->library_page_id !== $pageId)) {
                throw new \RuntimeException('Media pustaka tidak ditemukan di halaman ini.');
            }

            $url     = trim((string) ($row['external_url'] ?? ''));
            $kind    = ($row['media_kind'] ?? 'image') === 'video' ? 'video' : 'image';
            $label   = 'Media ' . ($index + 1) . ' halaman ' . $pageSequence;
            $file    = $this->request->getFile("library_file.{$pageIndex}.{$index}");
            $hasFile = $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
            $key     = trim((string) ($row['media_key'] ?? ''));
            // Sumber dipilih eksplisit; kotak sumber lain tetap terkirim walau tersembunyi
            $source  = (string) ($row['source'] ?? ($url !== '' ? 'url' : 'upload'));
            $isUrl   = $source === 'url';
            $isEmpty = $isUrl ? $url === '' : ($key === '' && ! $hasFile);

            if (! empty($row['_delete']) || $isEmpty) {
                if ($existing !== null) {
                    $model->delete($id);
                }

                continue;
            }

            $mediaId = null;

            if ($isUrl) {
                $link = MediaLink::parse($url, $kind);

                if ($link === null) {
                    throw new \RuntimeException("{$label}: tautan harus alamat http(s) yang sah.");
                }

                $kind = $link['kind'];
            } else {
                $url    = '';
                $result = $store->resolve(
                    $key,
                    $file,
                    'library.' . MediaStore::slug($levelCode) . '.p' . $pageSequence . '.' . ($index + 1),
                    [$kind],
                    $this->staffId(),
                );

                if ($result['error'] !== null) {
                    throw new \RuntimeException("{$label}: " . $result['error']);
                }

                $mediaId = $result['id'];
            }

            $poster = $store->resolve(
                (string) ($row['poster_key'] ?? ''),
                $this->request->getFile("library_poster.{$pageIndex}.{$index}"),
                'library.' . MediaStore::slug($levelCode) . '.p' . $pageSequence . '.' . ($index + 1) . '.poster',
                ['image'],
                $this->staffId(),
            );

            if ($poster['error'] !== null) {
                throw new \RuntimeException("{$label} (poster): " . $poster['error']);
            }

            $payload = [
                'library_page_id' => $pageId,
                'sequence'        => max(1, (int) ($row['sequence'] ?? $index + 1)),
                'media_kind'      => $kind,
                'media_asset_id'  => $mediaId,
                'external_url'    => $url === '' ? null : $url,
                'poster_media_id' => $kind === 'video' ? $poster['id'] : null,
                'caption_id'      => $this->nullIfBlank($row['caption_id'] ?? null),
                'caption_en'      => $this->nullIfBlank($row['caption_en'] ?? null),
                'credit'          => $this->nullIfBlank($row['credit'] ?? null),
                'is_active'       => array_key_exists('is_active', $row) ? (empty($row['is_active']) ? 0 : 1) : 1,
            ];

            $written = $existing !== null ? $model->update($id, $payload) : $model->insert($payload, false);

            if ($written === false) {
                throw new \RuntimeException("{$label} ditolak: " . $this->modelErrors($model));
            }

            $count++;
        }

        return $count;
    }

    /**
     * Slide narasi dan dialog satu konteks (docs/naskah-cerita.md). $levelId
     * = 0 untuk konteks global (`intro`, `map_intro`, `ending`, level_id
     * NULL); selain itu konteks wilayah (`region_intro`, `level_open`,
     * `level_done`). Konteks dipilih lewat `?konteks=`; bawaannya `intro`
     * untuk global dan `level_open` untuk wilayah. Slide nonaktif tetap
     * tampil di editor agar dapat diaktifkan kembali.
     */
    public function dialogues(int $levelId): string
    {
        $level = $levelId === 0 ? null : model(LevelModel::class)->find($levelId);

        if ($levelId !== 0 && $level === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        $context = $this->dialogueContext($level === null, $this->request->getGet('konteks'));

        $rows = model(DialogueModel::class)
            ->where('level_id', $level?->id)
            ->where('context_code', $context)
            ->orderBy('sequence', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $config = config('Gelita');

        return $this->panel('admin/content/dialogues', $level === null ? 'Cerita & narasi umum' : 'Dialog wilayah', [
            'level'      => $level,
            'context'    => $context,
            'contexts'   => $config->dialogueContexts[$level === null ? 'global' : 'level'],
            'dialogues'  => $rows,
            'characters' => ['narator', ...$config->characters],
            'poses'      => $config->characterPoses,
            'effects'    => $config->dialogueEffects,
        ]);
    }

    public function saveDialogues(int $levelId): RedirectResponse
    {
        if ($levelId !== 0 && model(LevelModel::class)->find($levelId) === null) {
            throw PageNotFoundException::forPageNotFound("Level {$levelId} tidak ditemukan.");
        }

        $context   = $this->dialogueContext($levelId === 0, $this->request->getPost('context'));
        $back      = 'admin/konten/dialog/' . $levelId . '?konteks=' . $context;
        $dialogues = model(DialogueModel::class);
        $saved     = 0;

        foreach ((array) ($this->request->getPost('dialogues') ?? []) as $index => $row) {
            $text = trim((string) ($row['text_id'] ?? ''));

            if ($text === '') {
                continue;
            }

            $audio = [];

            foreach (['audio_id_asset_id', 'audio_en_asset_id'] as $column) {
                $audio[$column] = $this->audioIdOrNull($row[$column] ?? null);

                if ($audio[$column] === false) {
                    return $this->back($back, 'Audio yang dipilih untuk slide ' . ($index + 1) . ' tidak ditemukan.');
                }
            }

            $payload = [
                'level_id'       => $levelId === 0 ? null : $levelId,
                'context_code'   => $context,
                'sequence'       => (int) ($row['sequence'] ?? $index + 1),
                'character_code' => (string) ($row['character_code'] ?? 'jaka'),
                'pose'           => $this->nullIfBlank($row['pose'] ?? null),
                'effect'         => $this->nullIfBlank($row['effect'] ?? null),
                'title_id'       => $this->nullIfBlank($row['title_id'] ?? null),
                'title_en'       => $this->nullIfBlank($row['title_en'] ?? null),
                'text_id'        => $text,
                'text_en'        => $this->nullIfBlank($row['text_en'] ?? null),
                'is_active'      => empty($row['is_active']) ? 0 : 1,
            ] + $audio;

            $id      = (int) ($row['id'] ?? 0);
            $written = $id > 0 ? $dialogues->update($id, $payload) : $dialogues->insert($payload, false);

            if ($written === false) {
                return $this->back(
                    $back,
                    'Dialog slide ' . $payload['sequence'] . ' ditolak: ' . $this->modelErrors($dialogues),
                );
            }

            $saved++;
        }

        $this->afterContentChange('dialogues', $levelId);

        return $this->done($back, "{$saved} dialog disimpan.");
    }

    // ----------------------------------------------------------- verifikasi

    public function verify(): string
    {
        return $this->panel('admin/content/verify', 'Verifikasi konten', [
            'findings' => (new ContentVerifier())->run(),
        ]);
    }

    // -------------------------------------------------------------- bantuan

    /**
     * Konteks dialog yang diminta bila sah untuk cakupannya (global atau
     * wilayah). Selain itu dipakai bawaannya: `intro` untuk global,
     * `level_open` untuk wilayah.
     */
    private function dialogueContext(bool $global, mixed $requested): string
    {
        $allowed = config('Gelita')->dialogueContexts[$global ? 'global' : 'level'];

        if (is_string($requested) && in_array($requested, $allowed, true)) {
            return $requested;
        }

        return $global ? 'intro' : 'level_open';
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
     * Payload butir dari POST, atau pesan galat bila kolom JSON rusak atau
     * gambar butir ditolak.
     *
     * @return array<string, mixed>|string
     */
    private function itemPayload(string $itemKey): array|string
    {
        $answerKey = $this->jsonField('answer_key_json');
        $config    = $this->jsonField('config_json');

        if ($answerKey === false || $config === false) {
            return 'answer_key_json atau config_json bukan JSON yang valid.';
        }

        $media = $this->mediaFields([
            'media_asset_id' => ['media_item', 'challenge.item.' . MediaStore::slug($itemKey)],
        ]);

        if (is_string($media)) {
            return $media;
        }

        return $this->bilingual(['prompt', 'source_text']) + $media + [
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

    /**
     * Menggabungkan field config terpandu dari form node (`cfg[...]`) ke config
     * JSON. Hanya kunci yang dikirim form yang diubah, sehingga kunci lain di
     * JSON mentah tetap utuh.
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function guidedConfig(array $config): array
    {
        $guided = $this->request->getPost('cfg');

        if (! is_array($guided)) {
            return $config;
        }

        foreach (['items_per_round', 'distractor_count', 'grid'] as $key) {
            if (isset($guided[$key]) && trim((string) $guided[$key]) !== '') {
                $config[$key] = max(0, (int) $guided[$key]);
            }
        }

        foreach (['allow_retry', 'use_word_bank', 'require_reason', 'shuffle_options', 'show_decoys'] as $key) {
            if (array_key_exists($key, $guided)) {
                $config[$key] = (bool) (int) $guided[$key];
            }
        }

        if (isset($guided['verdict_options']) && is_array($guided['verdict_options'])) {
            $verdicts = array_values(array_intersect(['benar', 'salah', 'pendapat'], $guided['verdict_options']));

            if ($verdicts !== []) {
                $config['verdict_options'] = $verdicts;
            }
        }

        if (isset($guided['distractors']) && is_array($guided['distractors'])) {
            $distractors = [];

            foreach ($guided['distractors'] as $row) {
                $id = trim((string) ($row['id'] ?? ''));

                if ($id !== '') {
                    $distractors[] = ['id' => $id, 'en' => trim((string) ($row['en'] ?? '')) ?: $id];
                }
            }

            $config['distractors'] = $distractors;
        }

        return $config;
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

    /**
     * Kolom media dari components/media-field: `{field}_key` + berkas `{field}_file`.
     *
     * @param array<string, array{0: string, 1: string}> $spec kolom → [nama field, asset_key bawaan]
     * @param list<string>                              $types
     *
     * @return array<string, int|null>|string kolom → id media, atau pesan galat
     */
    private function mediaFields(array $spec, array $types = ['image']): array|string
    {
        $store = new MediaStore();
        $out   = [];

        foreach ($spec as $column => [$field, $defaultKey]) {
            $result = $store->resolveField($this->request, $field . '_key', $field . '_file', $defaultKey, $types, $this->staffId());

            if ($result['error'] !== null) {
                return $result['error'];
            }

            $out[$column] = $result['id'];
        }

        return $out;
    }

    /**
     * Kolom audio (select id audio_assets) dari POST.
     *
     * @param list<string> $columns
     *
     * @return array<string, int|null>|string
     */
    private function audioFields(array $columns): array|string
    {
        $out = [];

        foreach ($columns as $column) {
            $id = $this->audioIdOrNull($this->request->getPost($column));

            if ($id === false) {
                return 'Audio yang dipilih tidak ditemukan.';
            }

            $out[$column] = $id;
        }

        return $out;
    }

    /** Id audio_assets yang sah, null bila kosong, false bila tidak ada. */
    private function audioIdOrNull($value): int|false|null
    {
        $id = (int) $value;

        if ($id <= 0) {
            return null;
        }

        return model(AudioAssetModel::class)->find($id) === null ? false : $id;
    }

    private function nodeRef(int $levelId, int $sequence): string
    {
        return node_ref((string) (model(LevelModel::class)->find($levelId)?->code ?? 'level' . $levelId), $sequence);
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
