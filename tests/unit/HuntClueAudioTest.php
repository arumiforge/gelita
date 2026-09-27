<?php

use App\Controllers\Admin\ContentController;
use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Entities\Level;
use App\Libraries\MediaUsage;
use App\Models\ChallengeItemModel;
use App\Services\ContentRepository;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Services;
use Tests\Support\Database\NarrationTables;

/**
 * Narasi petunjuk arena `cari`: kolom butir (migration 003800, model,
 * entity), tombol ▶ di view `cari`, pemilih audio di form butir admin, dan
 * kolom "Dipakai di" halaman Audio.
 *
 * Memakai grup basis data `tests` (SQLite di memori) dengan tabel ringkas
 * NarrationTables; ContentRepository diganti tiruan agar view tantangan
 * dapat dirender tanpa bank soal.
 *
 * @internal
 */
final class HuntClueAudioTest extends CIUnitTestCase
{
    use NarrationTables;

    private BaseConnection $sqlite;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'form', 'gelita', 'content', 'ui']);

        $this->sqlite = Database::connect('tests');
        $this->createNarrationTables($this->sqlite);

        Services::injectMock('contentRepository', new class () extends ContentRepository {
            public function __construct()
            {
            }

            public function nodesForLevel(int $levelId): array
            {
                return [];
            }

            public function mediaKeyMap(): array
            {
                return [];
            }

            public function mediaMap(): array
            {
                return [];
            }
        });
        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        $this->dropNarrationTables();
        parent::tearDown();
    }

    // ------------------------------------------------------ skema & model

    public function testMigrationAddsNullableAudioColumnsWithSetNullForeignKeys(): void
    {
        $migrations = glob(APPPATH . 'Database/Migrations/*.php') ?: [];
        sort($migrations);
        $latest = (string) end($migrations);

        $this->assertStringEndsWith('2026-01-01-003800_AddChallengeItemPromptAudio.php', $latest);

        $source = (string) file_get_contents($latest);
        $this->assertStringContainsString("['audio_prompt_id', 'audio_prompt_en_id']", $source);
        $this->assertStringContainsString("\$this->ref(true) + ['after' => \$after]", $source, 'nullable, di samping media_asset_id');
        $this->assertStringContainsString("addForeignKey(\$column, 'audio_assets', 'id', '', 'SET NULL')", $source);
    }

    public function testModelAndEntityCarryThePromptAudio(): void
    {
        $fields = (new ReflectionProperty(ChallengeItemModel::class, 'allowedFields'))->getValue(model(ChallengeItemModel::class, false));
        $this->assertContains('audio_prompt_id', $fields);
        $this->assertContains('audio_prompt_en_id', $fields);

        $item = new ChallengeItem();
        $item->injectRawData(['id' => 1, 'audio_prompt_id' => '12', 'audio_prompt_en_id' => null]);

        $this->assertSame(12, $item->audio_prompt_id);
        $this->assertSame(12, $item->promptAudioId('id'));
        $this->assertNull($item->promptAudioId('en'), 'tidak jatuh ke rekaman bahasa lain');
        $this->assertArrayNotHasKey('audio_prompt_id', $item->toPlayerArray('id'), 'payload engine lain tidak berubah');
    }

    // -------------------------------------------------------------- pemain

    public function testCariViewAddsTheVoiceTemplateOnlyWhenACluehasAudio(): void
    {
        $plain  = $this->cariPage([
            ['item_id' => 11, 'text' => 'Temukan tembakau.', 'audio' => null, 'audio_id' => null],
        ]);
        $voiced = $this->cariPage([
            ['item_id' => 11, 'text' => 'Temukan tembakau.', 'audio' => null, 'audio_id' => null],
            ['item_id' => 12, 'text' => 'Temukan candi.', 'audio' => '/assets/audio/narasi/id/petunjuk-tmg-4-02.mp3', 'audio_id' => 5],
        ]);

        // Tanpa rekaman yang disetujui: tidak ada markup audio sama sekali
        $this->assertStringNotContainsString('tpl-clue-voice', $plain);
        $this->assertStringNotContainsString('audio-player', $plain);
        $this->assertStringContainsString('<p class="clue-text" id="clue-text" aria-live="polite">Temukan tembakau.</p>', $plain);

        // Dengan rekaman: template inert (tanpa JS tidak tampil), tombol ▶/⏸ berlabel
        $this->assertMatchesRegularExpression('/<template id="tpl-clue-voice">\s*<div class="audio-player clue-audio">/', $voiced);
        $this->assertStringContainsString('data-action="play" aria-label="' . esc(lang('Game.clueListen'), 'attr') . '"', $voiced);
        $this->assertMatchesRegularExpression('/data-action="pause"[^>]*hidden/', $voiced);
        $this->assertStringNotContainsString('<noscript><audio', $voiced, 'tanpa JS tetap petunjuk teks saja');
        $this->assertStringNotContainsString('data-src=', $voiced, 'URL hanya di payload, dipasang engines/cari.js');
        $this->assertStringContainsString('petunjuk-tmg-4-02.mp3', $voiced, 'payload membawa URL');
        $this->assertStringContainsString('<p class="clue-text" id="clue-text" aria-live="polite">Temukan tembakau.</p>', $voiced);
    }

    public function testEngineScriptPlaysFirstClueManuallyAndLaterCluesAutomatically(): void
    {
        $js = (string) file_get_contents(FCPATH . 'assets/js/engines/cari.js');

        $this->assertStringContainsString("import { Sfx, narrationPlayer, pauseNarration } from '../core/audio.js';", $js);
        $this->assertStringContainsString("narrationPlayer(node, { attemptId: ctx.attemptId, shared: true })", $js);
        $this->assertStringContainsString('showVoice(current); // petunjuk pertama: tombol ▶ saja, tanpa putar otomatis', $js);
        $this->assertStringContainsString('goTo(next, { autoplay: true })', $js, 'setelah jawaban benar');
        $this->assertStringContainsString('goTo(Number(jump.dataset.clue))', $js, 'lompat tanpa putar otomatis');
        $this->assertStringContainsString('pauseNarration();', $js, 'ganti petunjuk menghentikan narasi');
        // autoplay() menolak berbunyi sebelum interaksi atau saat suara dimatikan
        $this->assertMatchesRegularExpression('/autoplay\(\) \{\s*if \(!unlocked \|\| !enabled/', (string) file_get_contents(FCPATH . 'assets/js/core/audio.js'));
    }

    // --------------------------------------------------------------- admin

    public function testItemFormOffersPromptAudioOnlyOnCariNodes(): void
    {
        $audioId = $this->audio('audio.narasi.id.petunjuk-tmg-4-01', 'id', 'approved');
        $item    = new ChallengeItem();
        $item->injectRawData([
            'id' => 21, 'challenge_node_id' => 4, 'item_key' => 'tmg-4-01', 'sequence' => 1, 'interaction_type' => 'find_object',
            'prompt_id' => 'Temukan daun tembakau.', 'review_status' => 'draft', 'scorable' => 1, 'is_active' => 1,
            'audio_prompt_id' => $audioId,
        ]);

        $cari = $this->itemForm($this->node('cari'), $item, ['find_object']);
        $this->assertStringContainsString('name="audio_prompt_id"', $cari);
        $this->assertStringContainsString('name="audio_prompt_en_id"', $cari);
        $this->assertMatchesRegularExpression('/<option value="' . $audioId . '" selected>\s*ID · hunt_clue · audio\.narasi\.id\.petunjuk-tmg-4-01 \(disetujui\)/', $cari);
        $this->assertStringContainsString('petunjuk-{node}-NN.mp3', $cari);

        $other = $this->itemForm($this->node('pilihan'), null, ['single_choice']);
        $this->assertStringNotContainsString('audio_prompt_id', $other);
    }

    public function testItemPayloadKeepsPromptAudioForFindObjectOnly(): void
    {
        $audioId = $this->audio('audio.narasi.en.petunjuk-tmg-4-01', 'en', 'draft');

        $find = $this->itemPayload(['interaction_type' => 'find_object', 'audio_prompt_id' => '', 'audio_prompt_en_id' => (string) $audioId]);
        $this->assertIsArray($find);
        $this->assertNull($find['audio_prompt_id']);
        $this->assertSame($audioId, $find['audio_prompt_en_id']);

        $this->assertSame('Audio yang dipilih tidak ditemukan.', $this->itemPayload(['interaction_type' => 'find_object', 'audio_prompt_id' => '999']));

        // Jenis lain tidak pernah membawa audio petunjuk, walau dikirim
        $choice = $this->itemPayload(['interaction_type' => 'single_choice', 'audio_prompt_id' => (string) $audioId]);
        $this->assertNull($choice['audio_prompt_id']);
        $this->assertNull($choice['audio_prompt_en_id']);
    }

    public function testAudioUsageNamesTheItem(): void
    {
        $this->seedHuntNode($this->sqlite);
        $id = $this->audio('audio.narasi.id.petunjuk-tmg-4-01', 'id', 'approved');
        $en = $this->audio('audio.narasi.en.petunjuk-tmg-4-01', 'en', 'draft');
        $this->sqlite->table('challenge_items')->where('item_key', 'tmg-4-01')->update(['audio_prompt_id' => $id, 'audio_prompt_en_id' => $en]);

        $uses = (new MediaUsage())->forAudio();

        $this->assertSame(['Petunjuk tmg-4-01 (ID)'], $uses[$id]);
        $this->assertSame(['Petunjuk tmg-4-01 (EN)'], $uses[$en]);
    }

    // ------------------------------------------------------------- bantuan

    /** @param list<array<string, mixed>> $clues */
    private function cariPage(array $clues): string
    {
        $level = new Level();
        $level->injectRawData(['id' => 1, 'code' => 'temanggung', 'name_id' => 'Temanggung', 'sequence' => 1]);

        $attempt = new ChallengeAttempt();
        $attempt->injectRawData(['id' => 501, 'challenge_node_id' => 4]);

        return view('game/challenge/cari', [
            'node'     => $this->node('cari'),
            'level'    => $level,
            'attempt'  => $attempt,
            'sequence' => 4,
            'locale'   => 'id',
            'payload'  => [
                'node'    => ['scene' => null],
                'objects' => [['ref' => 'aaaaaaaaaaaaaaaaaaaa', 'x' => 10.0, 'y' => 20.0, 'w' => 12.0, 'media' => null]],
                'clues'   => $clues,
                'hints'   => [],
            ],
        ]);
    }

    private function node(string $engine): ChallengeNode
    {
        $node = new ChallengeNode();
        $node->injectRawData([
            'id' => 4, 'level_id' => 1, 'sequence' => 4, 'engine_type' => $engine,
            'title_id' => 'Temukan Budaya Temanggung', 'is_active' => 1,
        ]);

        return $node;
    }

    /** @param list<string> $interactions */
    private function itemForm(ChallengeNode $node, ?ChallengeItem $item, array $interactions): string
    {
        return view('admin/content/items', [
            'item'         => $item,
            'node'         => $node,
            'interactions' => $interactions,
            'passages'     => [],
            'indicators'   => [],
        ]);
    }

    /**
     * ContentController::itemPayload() dengan POST buatan.
     *
     * @param array<string, string> $post
     *
     * @return array<string, mixed>|string
     */
    private function itemPayload(array $post): array|string
    {
        $request = Services::incomingrequest(null, false);
        $request->setMethod('post');
        $request->setGlobal('post', $post + ['prompt_id' => 'Temukan.', 'media_item_key' => '']);

        $controller = new ContentController();
        $controller->initController($request, Services::response(), Services::logger());

        return (new ReflectionMethod(ContentController::class, 'itemPayload'))->invoke($controller, 'tmg-4-01');
    }

    private function audio(string $key, string $locale, string $status): int
    {
        $this->sqlite->table('media_assets')->insert(['asset_key' => $key, 'asset_type' => 'audio', 'storage_path' => 'assets/uploads/' . $key . '.mp3', 'mime_type' => 'audio/mpeg', 'locale' => $locale]);
        $this->sqlite->table('audio_assets')->insert([
            'media_asset_id' => $this->sqlite->insertID(), 'locale' => $locale, 'context_code' => 'hunt_clue',
            'transcript' => 'Uji', 'approval_status' => $status,
        ]);

        return (int) $this->sqlite->insertID();
    }
}
