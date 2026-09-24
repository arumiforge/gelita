<?php

use App\Libraries\StorySync;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;

/**
 * `php spark gelita:story:update` — memperbarui cerita server yang sudah
 * berjalan tanpa menimpa suntingan admin.
 *
 * Memakai grup basis data `tests` (SQLite di memori) dengan tabel `levels`,
 * `dialogues`, dan `audit_logs` versi ringkas: kolom yang dibaca/ditulis
 * StorySync sama dengan migration MySQL, termasuk UNIQUE
 * (level_id, context_code, sequence) yang juga membiarkan NULL berulang.
 *
 * @internal
 */
final class StoryUpdateCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private BaseConnection $sqlite;

    /** @var array<string, array{0: string, 1: string}> */
    private array $legacy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite     = Database::connect('tests');
        $this->legacy = require APPPATH . 'Database/Seeds/data/story-legacy.php';

        $forge = Database::forge('tests');

        foreach (['dialogues', 'levels', 'audit_logs'] as $table) {
            $forge->dropTable($table, true);
        }

        $forge->addField([
            'id'   => ['type' => 'INTEGER', 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 30],
        ])->addPrimaryKey('id')->createTable('levels');

        $forge->addField([
            'id'                  => ['type' => 'INTEGER', 'auto_increment' => true],
            'level_id'            => ['type' => 'INTEGER', 'null' => true],
            'context_code'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'sequence'            => ['type' => 'INTEGER'],
            'character_code'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'pose'                => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'effect'              => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'title_id'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'title_en'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'text_id'             => ['type' => 'TEXT'],
            'text_en'             => ['type' => 'TEXT', 'null' => true],
            'audio_id_asset_id'   => ['type' => 'INTEGER', 'null' => true],
            'audio_en_asset_id'   => ['type' => 'INTEGER', 'null' => true],
            'background_media_id' => ['type' => 'INTEGER', 'null' => true],
            'is_active'           => ['type' => 'INTEGER', 'default' => 1],
        ])->addPrimaryKey('id')->addUniqueKey(['level_id', 'context_code', 'sequence'])->createTable('dialogues');

        $forge->addField([
            'id'            => ['type' => 'INTEGER', 'auto_increment' => true],
            'staff_user_id' => ['type' => 'INTEGER', 'null' => true],
            'action'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'target_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'target_id'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'metadata_json' => ['type' => 'TEXT', 'null' => true],
            'ip_hash'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'occurred_at'   => ['type' => 'DATETIME', 'null' => true],
        ])->addPrimaryKey('id')->createTable('audit_logs');

        foreach (['temanggung', 'magelang', 'wonosobo'] as $code) {
            $this->sqlite->table('levels')->insert(['code' => $code]);
        }
    }

    protected function tearDown(): void
    {
        $forge = Database::forge('tests');

        foreach (['dialogues', 'levels', 'audit_logs'] as $table) {
            $forge->dropTable($table, true);
        }

        parent::tearDown();
    }

    public function testCommandIsDiscovered(): void
    {
        $commands = service('commands')->getCommands();

        $this->assertArrayHasKey('gelita:story:update', $commands);
        $this->assertSame('App\Commands\StoryUpdate', $commands['gelita:story:update']['class']);
    }

    public function testCommandSyncsTimezone(): void
    {
        $source = (string) file_get_contents(APPPATH . 'Commands/StoryUpdate.php');

        $this->assertStringContainsString('db_sync_timezone()', $source);
    }

    public function testEmptyTableGetsTheWholeStory(): void
    {
        command('gelita:story:update');

        $this->assertSame(88, $this->sqlite->table('dialogues')->where('is_active', 1)->countAllResults());
        $this->assertStringContainsString('Cerita: 88 disisipkan, 0 diperbarui, 0 dilewati, 0 dinonaktifkan', $this->getStreamFilterBuffer());

        $first = $this->row(null, 'intro', 1);
        $this->assertSame('narator', $first['character_code']);
        $this->assertNull($first['pose']);
        $this->assertSame('glow', $first['effect']);
        $this->assertSame('Dataran yang Bercahaya', $first['title_id']);

        // Tercatat di audit_logs sebagai perubahan konten dari CLI
        $audit = $this->sqlite->table('audit_logs')->get()->getRowArray();
        $this->assertSame('content_update', $audit['action']);
        $this->assertStringContainsString('"inserted":88', (string) $audit['metadata_json']);
    }

    public function testLegacySeederTextIsReplacedAndAudioIsKept(): void
    {
        $this->seedLegacy();
        $levelId = $this->levelId('temanggung');
        $this->sqlite->table('dialogues')
            ->where(['level_id' => $levelId, 'context_code' => 'level_open', 'sequence' => 1])
            ->update(['audio_id_asset_id' => 41, 'audio_en_asset_id' => 42, 'background_media_id' => 7]);

        command('gelita:story:update');

        $row = $this->row($levelId, 'level_open', 1);
        $this->assertStringStartsWith('Kita sudah sampai, Le. Temanggung', $row['text_id']);
        $this->assertSame('mbah_kedu', $row['character_code']);
        $this->assertSame('worried', $row['pose']);
        $this->assertSame('fog', $row['effect']);
        $this->assertSame(41, (int) $row['audio_id_asset_id'], 'audio Indonesia tidak tersentuh');
        $this->assertSame(42, (int) $row['audio_en_asset_id'], 'audio Inggris tidak tersentuh');
        $this->assertSame(7, (int) $row['background_media_id'], 'latar tidak tersentuh');

        // 18 baris warisan diperbarui, 70 baris baru disisipkan
        $this->assertStringContainsString('Cerita: 70 disisipkan, 18 diperbarui, 0 dilewati, 0 dinonaktifkan', $this->getStreamFilterBuffer());
        $this->assertSame(9, $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro'])->countAllResults());
    }

    public function testAdminEditIsSkippedAndReported(): void
    {
        $this->seedLegacy();
        $edited = 'Suntingan guru: Jaka menyalakan lentera.';
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 2])->update(['text_id' => $edited]);

        command('gelita:story:update');

        $this->assertSame($edited, $this->row(null, 'intro', 2)['text_id']);
        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('intro #2', $output);
        $this->assertStringContainsString('--force', $output);
        $this->assertStringContainsString('1 dilewati', $output);
    }

    public function testLineEndingsFromTheEditorAreNotAnEdit(): void
    {
        $this->seedLegacy();
        [$textId] = $this->legacy['_|intro|1'];
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 1])->update(['text_id' => $textId . "\r\n"]);

        command('gelita:story:update');

        $this->assertStringStartsWith('Dahulu kala', $this->row(null, 'intro', 1)['text_id']);
    }

    public function testForceOverwritesAdminEdit(): void
    {
        $this->seedLegacy();
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 2])->update(['text_id' => 'Suntingan guru.']);

        command('gelita:story:update --force');

        $this->assertStringStartsWith('Cahaya itu lahir dari ilmu dan budaya', $this->row(null, 'intro', 2)['text_id']);
        $this->assertStringContainsString('0 dilewati', $this->getStreamFilterBuffer());
    }

    public function testRowsBeyondTheStoryAreDeactivatedNotDeleted(): void
    {
        command('gelita:story:update');
        $levelId = $this->levelId('temanggung');
        $this->sqlite->table('dialogues')->insert([
            'level_id' => $levelId, 'context_code' => 'level_done', 'sequence' => 5,
            'character_code' => 'jaka', 'text_id' => 'Slide tambahan', 'text_en' => 'Extra slide', 'is_active' => 1,
        ]);
        $this->resetStreamFilterBuffer();

        command('gelita:story:update');

        $extra = $this->row($levelId, 'level_done', 5);
        $this->assertNotNull($extra, 'baris tidak dihapus');
        $this->assertSame(0, (int) $extra['is_active']);
        $this->assertStringContainsString('temanggung/level_done #5', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('0 disisipkan, 0 diperbarui, 0 dilewati, 1 dinonaktifkan, 88 sudah sesuai', $this->getStreamFilterBuffer());
    }

    public function testDuplicateGlobalSequenceKeepsTheOldestRow(): void
    {
        command('gelita:story:update');
        // MySQL mengizinkan (NULL, 'ending', 1) berulang; SQLite juga
        $this->sqlite->table('dialogues')->insert([
            'level_id' => null, 'context_code' => 'ending', 'sequence' => 1,
            'character_code' => 'narator', 'text_id' => 'Ganda', 'text_en' => 'Duplicate', 'is_active' => 1,
        ]);

        (new StorySync($this->sqlite))->run();

        $rows = $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'ending', 'sequence' => 1])
            ->orderBy('id', 'ASC')->get()->getResultArray();
        $this->assertCount(2, $rows);
        $this->assertSame([1, 0], array_map(static fn (array $r): int => (int) $r['is_active'], $rows));
        $this->assertStringStartsWith('Di puncak bukit', $rows[0]['text_id']);
    }

    public function testMetadataFollowsTheStoryWhenTextAlreadyMatches(): void
    {
        command('gelita:story:update');
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'map_intro', 'sequence' => 3])
            ->update(['pose' => null, 'effect' => null]);

        $report = (new StorySync($this->sqlite))->run();

        $this->assertSame(['map_intro #3'], $report['updated']);
        $row = $this->row(null, 'map_intro', 3);
        $this->assertSame('happy', $row['pose']);
        $this->assertSame('glow', $row['effect']);
    }

    public function testDryRunWritesNothing(): void
    {
        $this->seedLegacy();

        command('gelita:story:update --dry-run');

        $this->assertSame(18, $this->sqlite->table('dialogues')->countAllResults());
        $this->assertSame($this->legacy['_|intro|1'][0], $this->row(null, 'intro', 1)['text_id']);
        $this->assertStringContainsString('--dry-run', $this->getStreamFilterBuffer());
        $this->assertSame(0, $this->sqlite->table('audit_logs')->countAllResults());
    }

    public function testInactiveRowStaysInactiveWhenUpdated(): void
    {
        $this->seedLegacy();
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 3])->update(['is_active' => 0]);

        command('gelita:story:update');

        $row = $this->row(null, 'intro', 3);
        $this->assertStringStartsWith('Namun perlahan', $row['text_id']);
        $this->assertSame(0, (int) $row['is_active'], 'keputusan admin menyembunyikan slide dihormati');
    }

    // ------------------------------------------------------------ bantuan

    /** Isi tabel seperti ContentSeeder lama: 18 baris teks warisan. */
    private function seedLegacy(): void
    {
        $characters = ['_|intro|1' => 'narator', '_|intro|2' => 'mbah_kedu', '_|intro|3' => 'jaka', '_|intro|4' => 'mbah_kedu'];

        foreach ($this->legacy as $key => [$textId, $textEn]) {
            [$level, $context, $sequence] = explode('|', $key);

            $this->sqlite->table('dialogues')->insert([
                'level_id'       => $level === '_' ? null : $this->levelId($level),
                'context_code'   => $context,
                'sequence'       => (int) $sequence,
                'character_code' => $characters[$key] ?? ((int) $sequence === 1 ? 'mbah_kedu' : 'jaka'),
                'text_id'        => $textId,
                'text_en'        => $textEn,
                'is_active'      => 1,
            ]);
        }
    }

    private function levelId(string $code): int
    {
        return (int) $this->sqlite->table('levels')->where('code', $code)->get()->getRow()->id;
    }

    /** @return array<string, mixed>|null baris id terkecil */
    private function row(?int $levelId, string $context, int $sequence): ?array
    {
        return $this->sqlite->table('dialogues')
            ->where(['level_id' => $levelId, 'context_code' => $context, 'sequence' => $sequence])
            ->orderBy('id', 'ASC')
            ->get(1)
            ->getRowArray();
    }
}
