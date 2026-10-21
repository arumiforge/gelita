<?php

use App\Commands\StorySync as StorySyncCommand;
use App\Database\Seeds\ContentSeeder;
use App\Libraries\StorySync;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use Config\Database;
use Config\Services;
use Tests\Support\Database\NarrationTables;

/**
 * `php spark gelita:story:sync`: a server installed with an older script gets
 * the current lines of data/story.php, without overwriting text that was
 * edited in the panel (unless --timpa) and without touching recordings.
 *
 * @internal
 */
final class StorySyncTest extends CIUnitTestCase
{
    use NarrationTables;

    private BaseConnection $sqlite;

    /** @var array<string, list<array<string, ?string>>> */
    private array $previous;

    /** @var array<string, array<string, mixed>> */
    private array $script = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite = Database::connect('tests');
        $this->createNarrationTables($this->sqlite);
        (new ContentSeeder(config('Database'), $this->sqlite))->seedDialogues();

        $this->previous = require APPPATH . 'Database/Seeds/data/story-previous.php';

        foreach (require APPPATH . 'Database/Seeds/data/story.php' as $line) {
            $this->script[$line['code']] = $line;
        }

        $this->installOldScript();
    }

    protected function tearDown(): void
    {
        CLI::resetInputOutput();
        $this->dropNarrationTables();
        parent::tearDown();
    }

    public function testPreviousVersionsBelongToChangedScriptLines(): void
    {
        // 32 lines of draft 2, peta-03 of the Peta Kedu redesign, and intro-07 of the one HUD (18 October 2026)
        $this->assertCount(34, $this->previous);

        foreach ($this->previous as $code => $versions) {
            $this->assertArrayHasKey($code, $this->script, "{$code} is not a script line");
            $this->assertNotSame([], $versions);

            foreach ($versions as $version) {
                $this->assertSame(StorySync::FIELDS, array_keys($version), $code);
                $this->assertNotSame(
                    array_intersect_key($this->script[$code], array_flip(StorySync::FIELDS)),
                    $version,
                    "{$code}: an earlier version equals the current script",
                );
            }
        }
    }

    public function testPlanSortsLinesWithoutWriting(): void
    {
        $this->editInPanel('intro-03', 'Teks yang disunting admin.');
        $this->deleteLine('penutup-05');
        $before = $this->sqlite->table('dialogues')->get()->getResultArray();

        $status = array_column((new StorySync($this->sqlite))->plan(), 'status', 'code');

        $this->assertSame('update', $status['intro-01']);
        $this->assertSame('edited', $status['intro-03']);
        $this->assertSame('missing', $status['penutup-05']);
        $this->assertSame('same', $status['intro-06']);
        $counts = array_count_values($status);
        ksort($counts);
        $this->assertSame(['edited' => 1, 'missing' => 1, 'same' => 56, 'update' => 33], $counts);
        $this->assertSame($before, $this->sqlite->table('dialogues')->get()->getResultArray(), 'plan() writes nothing');
    }

    public function testApplyUpdatesKnownVersionsAndKeepsPanelEdits(): void
    {
        $this->editInPanel('intro-03', 'Teks yang disunting admin.');
        $this->deleteLine('penutup-05');
        $this->sqlite->table('dialogues')->where('id', $this->rowOf('intro-01')['id'])
            ->update(['audio_id_asset_id' => 7, 'pose' => null, 'is_active' => 0]);

        $sync   = new StorySync($this->sqlite);
        $result = $sync->apply($sync->plan());

        $this->assertCount(33, $result['updated']);
        $this->assertSame(['penutup-05'], $result['inserted']);
        $this->assertSame([], $result['overwritten']);
        $this->assertSame(['intro-01'], $result['stale_audio']);

        $intro = $this->rowOf('intro-01');
        $this->assertSame($this->script['intro-01']['text_id'], $intro['text_id']);
        $this->assertSame($this->script['intro-01']['text_en'], $intro['text_en']);
        $this->assertSame(7, (int) $intro['audio_id_asset_id'], 'recordings stay linked');
        $this->assertSame(0, (int) $intro['is_active'], 'only title and text are written');
        $this->assertSame('Candi, Telaga, dan Tradisi', $this->rowOf('kenal-wonosobo-02')['title_id']);
        $this->assertSame('Teks yang disunting admin.', $this->rowOf('intro-03')['text_id']);
        $this->assertSame($this->script['penutup-05']['text_id'], $this->rowOf('penutup-05')['text_id']);

        $again = array_count_values(array_column($sync->plan(), 'status'));
        $this->assertSame(['same' => 90, 'edited' => 1], $again, 'a second run changes nothing');
    }

    public function testOverwriteReplacesPanelEdits(): void
    {
        $this->editInPanel('intro-03', 'Teks yang disunting admin.');

        $sync   = new StorySync($this->sqlite);
        $result = $sync->apply($sync->plan(), true);

        $this->assertSame(['intro-03'], $result['overwritten']);
        $this->assertSame($this->script['intro-03']['text_id'], $this->rowOf('intro-03')['text_id']);
        $this->assertSame(['same' => 91], array_count_values(array_column($sync->plan(), 'status')));
    }

    public function testRowChangedAfterPlanIsNotOverwritten(): void
    {
        $sync = new StorySync($this->sqlite);
        $plan = $sync->plan();

        $this->editInPanel('intro-01', 'Disimpan admin saat perintah berjalan.');
        $result = $sync->apply($plan);

        $this->assertSame(['intro-01'], $result['stale']);
        $this->assertSame('Disimpan admin saat perintah berjalan.', $this->rowOf('intro-01')['text_id']);
    }

    public function testCommandDryRunThenWrite(): void
    {
        $this->editInPanel('intro-03', 'Teks yang disunting admin.');
        $this->sqlite->table('dialogues')->where('id', $this->rowOf('intro-02')['id'])->update(['audio_en_asset_id' => 3]);

        $io = new MockInputOutput();
        CLI::setInputOutput($io);
        $command = new StorySyncCommand(Services::logger(), Services::commands());

        $this->assertSame(EXIT_SUCCESS, $command->run(['dry-run' => null]));
        $this->assertStringContainsString('91 baris naskah: 57 sudah sama, 33 diperbarui, 0 ditambahkan, 1 disunting di panel (dibiarkan), 0 dilewati.', $io->getOutput());
        $this->assertStringContainsString('intro-02', $io->getOutput());
        $this->assertStringContainsString('Setujui semua rekaman', $io->getOutput());
        $this->assertNotSame($this->script['intro-01']['text_id'], $this->rowOf('intro-01')['text_id'], 'dry run writes nothing');
        $this->assertSame(0, $this->sqlite->table('audit_logs')->countAllResults());

        CLI::resetInputOutput();
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        $this->assertSame(EXIT_SUCCESS, $command->run([]));
        $this->assertStringContainsString('33 baris ditulis.', $io->getOutput());
        $this->assertSame($this->script['intro-01']['text_id'], $this->rowOf('intro-01')['text_id']);

        $audit = $this->sqlite->table('audit_logs')->get()->getRowArray();
        $this->assertSame('story_sync', $audit['action']);
        $this->assertSame(33, json_decode((string) $audit['metadata_json'], true)['updated']);
    }

    /** Puts every changed line back to its earlier version, as on a server installed before the revision. */
    private function installOldScript(): void
    {
        foreach ($this->previous as $code => $versions) {
            $this->sqlite->table('dialogues')->where('id', $this->rowOf($code)['id'])->update(end($versions));
        }
    }

    private function editInPanel(string $code, string $text): void
    {
        $this->sqlite->table('dialogues')->where('id', $this->rowOf($code)['id'])->update(['text_id' => $text]);
    }

    private function deleteLine(string $code): void
    {
        $this->sqlite->table('dialogues')->where('id', $this->rowOf($code)['id'])->delete();
    }

    /** @return array<string, mixed> */
    private function rowOf(string $code): array
    {
        $line  = $this->script[$code];
        $level = $line['level'] === null ? null
            : (int) $this->sqlite->table('levels')->where('code', $line['level'])->get()->getRow('id');

        return $this->sqlite->table('dialogues')
            ->where('level_id', $level)
            ->where('context_code', $line['context'])
            ->where('sequence', $line['sequence'])
            ->get()->getRowArray();
    }
}
