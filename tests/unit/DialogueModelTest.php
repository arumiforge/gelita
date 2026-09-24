<?php

use App\Models\DialogueModel;
use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * DialogueModel::global() dan ContentRepository::dialogues(null, $context):
 * konteks global (`intro`, `map_intro`, `ending`) masing-masing berdiri
 * sendiri — sebelum Tahap 2, dialogues(null, …) selalu mengembalikan intro.
 * Juga validasi pose dan efek. SQLite di memori (grup `tests`).
 *
 * @internal
 */
final class DialogueModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $forge = Database::forge('tests');
        $forge->dropTable('dialogues', true);
        $forge->addField([
            'id'             => ['type' => 'INTEGER', 'auto_increment' => true],
            'level_id'       => ['type' => 'INTEGER', 'null' => true],
            'context_code'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'sequence'       => ['type' => 'INTEGER'],
            'character_code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'pose'           => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'effect'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'title_id'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'title_en'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'text_id'        => ['type' => 'TEXT'],
            'text_en'        => ['type' => 'TEXT'],
            'is_active'      => ['type' => 'INTEGER', 'default' => 1],
        ])->addPrimaryKey('id')->createTable('dialogues');

        $rows = [
            [null, 'intro', 2, 1, 'Intro dua'],
            [null, 'intro', 1, 1, 'Intro satu'],
            [null, 'map_intro', 1, 1, 'Peta satu'],
            [null, 'map_intro', 2, 0, 'Peta nonaktif'],
            [null, 'ending', 1, 1, 'Penutup satu'],
            [3, 'map_intro', 1, 1, 'Bukan global'],
        ];

        foreach ($rows as [$level, $context, $sequence, $active, $text]) {
            Database::connect('tests')->table('dialogues')->insert([
                'level_id' => $level, 'context_code' => $context, 'sequence' => $sequence,
                'character_code' => 'jaka', 'text_id' => $text, 'text_en' => $text, 'is_active' => $active,
            ]);
        }

        service('contentRepository')->flush();
    }

    protected function tearDown(): void
    {
        Database::forge('tests')->dropTable('dialogues', true);
        service('contentRepository')->flush();
        parent::tearDown();
    }

    public function testGlobalReturnsOnlyActiveRowsOfThatContextInOrder(): void
    {
        $model = model(DialogueModel::class);

        $this->assertSame(['Intro satu', 'Intro dua'], array_column($model->global('intro'), 'text_id'));
        $this->assertSame(['Peta satu'], array_column($model->global('map_intro'), 'text_id'));
        $this->assertSame(['Penutup satu'], array_column($model->global('ending'), 'text_id'));
        $this->assertSame([], $model->global('level_open'));
    }

    public function testRepositoryHonoursTheGlobalContext(): void
    {
        // Versi konten tetap: tabel game_releases tidak ada di SQLite uji
        $repo = new class () extends ContentRepository {
            public function version(): string
            {
                return 'uji-dialog';
            }
        };

        $this->assertSame(['Peta satu'], array_column($repo->dialogues(null, 'map_intro'), 'text_id'));
        $this->assertSame(['Intro satu', 'Intro dua'], array_column($repo->dialogues(null, 'intro'), 'text_id'));
    }

    public function testIntroMethodIsGone(): void
    {
        $this->assertFalse(method_exists(DialogueModel::class, 'intro'), 'digantikan global(\'intro\')');
    }

    public function testPoseMustBelongToTheSpeaker(): void
    {
        $model = model(DialogueModel::class);
        $base  = ['context_code' => 'intro', 'sequence' => 9, 'text_id' => 'a', 'text_en' => 'b'];

        $this->assertNotFalse($model->insert($base + ['character_code' => 'jaka', 'pose' => 'determined', 'effect' => 'glow']));
        $this->assertFalse($model->insert($base + ['sequence' => 10, 'character_code' => 'jaka', 'pose' => 'weak']));
        $this->assertArrayHasKey('pose', $model->errors());
        $this->assertFalse($model->insert($base + ['sequence' => 11, 'character_code' => 'narator', 'pose' => 'idle']));
        $this->assertFalse($model->insert($base + ['sequence' => 12, 'character_code' => 'mbah_kedu', 'effect' => 'sparkle']));
        $this->assertArrayHasKey('effect', $model->errors());
        $this->assertNotFalse($model->insert($base + ['sequence' => 13, 'character_code' => 'narator', 'pose' => null, 'effect' => null]));
    }
}
