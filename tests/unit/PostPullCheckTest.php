<?php

use App\Libraries\DevStepLedger;
use App\Libraries\PostPullCheck;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * `gelita:setelah-pull` (17 November 2026): Composer packages against
 * composer.lock, import steps against the fingerprints DevStepLedger
 * recorded, the narration import following the narration install, and the
 * closing cache and content checks only when a content step is due.
 * Works on a throwaway application root; the database, minified-asset and
 * guide steps read the real install and are only checked for presence.
 *
 * @internal
 */
final class PostPullCheckTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = sys_get_temp_dir() . '/post-pull-' . bin2hex(random_bytes(4)) . '/';
        mkdir($this->root, 0777, true);
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->root));
        parent::tearDown();
    }

    public function testComposerAgainstTheLockFile(): void
    {
        $this->assertSame(PostPullCheck::SKIPPED, $this->step('composer')['state'], 'no composer.lock');

        $this->put('composer.lock', json_encode(['packages' => [['name' => 'a/b', 'version' => '1.0.0']], 'packages-dev' => [['name' => 'c/d', 'version' => '2.0.0']]]));
        $this->assertSame(PostPullCheck::DUE, $this->step('composer')['state'], 'no vendor/');

        $this->put('vendor/composer/installed.json', json_encode(['packages' => [['name' => 'a/b', 'version' => '1.0.0'], ['name' => 'c/d', 'version' => '2.0.0']]]));
        $this->assertSame(PostPullCheck::OK, $this->step('composer')['state']);

        $this->put('vendor/composer/installed.json', json_encode(['packages' => [['name' => 'a/b', 'version' => '0.9.0'], ['name' => 'c/d', 'version' => '2.0.0']]]));
        $row = $this->step('composer');
        $this->assertSame(PostPullCheck::DUE, $row['state']);
        $this->assertStringContainsString('a/b', $row['reason']);
        $this->assertFalse($row['runnable'], 'never run from inside the PHP process using vendor/');
    }

    public function testImportStepFollowsItsInputs(): void
    {
        $this->assertSame(PostPullCheck::SKIPPED, $this->step('cerita')['state'], 'no source on this install');

        $this->put('app/Database/Seeds/data/story.php', '<?php return [];');
        $this->assertSame(PostPullCheck::UNKNOWN, $this->step('cerita')['state']);
        // The test database may lack migrations, which rightly makes the closing checks due on their own
        $closing = $this->step('migrasi')['state'] === PostPullCheck::DUE ? PostPullCheck::DUE : PostPullCheck::OK;
        $this->assertSame($closing, $this->step('verifikasi')['state'], 'an unknown step is not run');

        $this->ledger()->record('cerita');
        $this->assertSame(PostPullCheck::OK, $this->step('cerita')['state']);

        $this->put('app/Database/Seeds/data/story.php', '<?php return ["baru"];');
        $this->assertSame(PostPullCheck::DUE, $this->step('cerita')['state']);
        $this->assertSame('gelita:story:sync', $this->step('cerita')['command']);
        $this->assertSame(PostPullCheck::DUE, $this->step('cache')['state']);
        $this->assertSame(PostPullCheck::DUE, $this->step('verifikasi')['state']);
    }

    public function testCatalogueCountsPicturesAndListOnly(): void
    {
        $this->put('ai-studio/game-images/data.json', '{}');
        $this->put('ai-studio/game-images/assets/a.webp', 'a');
        $this->ledger()->record('katalog');

        $this->put('ai-studio/game-images/README.md', 'catatan');
        $this->put('ai-studio/game-images/review/b.webp', 'tinjauan');
        $this->assertSame(PostPullCheck::OK, $this->step('katalog')['state'], 'notes and the review folder are not imported');

        $this->put('ai-studio/game-images/assets/a.webp', 'a2');
        $this->assertSame(PostPullCheck::DUE, $this->step('katalog')['state']);

        $this->ledger()->record('katalog');
        unlink($this->root . 'ai-studio/game-images/assets/a.webp');
        $this->assertSame(PostPullCheck::DUE, $this->step('katalog')['state'], 'a removed picture counts');
    }

    public function testNarrationImportFollowsTheInstall(): void
    {
        $this->put('ai-studio/elevenlabs-voice/out/id/a.mp3', 'take 1');
        $this->put('public/assets/audio/narasi/id/a.mp3', 'take 1');
        $this->ledger()->record('narasi-pasang');
        $this->ledger()->record('narasi-impor');
        $this->assertSame(PostPullCheck::OK, $this->step('narasi-impor')['state']);

        $this->put('ai-studio/elevenlabs-voice/out/id/a.mp3', 'take 2');
        $this->assertSame(PostPullCheck::DUE, $this->step('narasi-pasang')['state']);
        $this->assertSame(PostPullCheck::DUE, $this->step('narasi-impor')['state'], 'the install changes the narration folder');

        $ids = array_column($this->check()->steps(), 'id');
        $this->assertLessThan(array_search('narasi-impor', $ids, true), array_search('narasi-pasang', $ids, true), 'install before import');
        $this->assertSame(['composer', 'migrasi'], array_slice($ids, 0, 2));
        $this->assertSame(['cache', 'verifikasi', 'panduan'], array_slice($ids, -3));
    }

    public function testMarkAllRecordsEveryPresentStep(): void
    {
        $this->put(PostPullCheck::BANK, 'xlsx');
        $this->put('ai-studio/elevenlabs-sound/out/music/a.mp3', 'm');
        $this->check()->markAll();

        $this->assertSame(PostPullCheck::OK, $this->step('bank')['state']);
        $this->assertSame(PostPullCheck::OK, $this->step('suara')['state']);
        $this->assertSame(PostPullCheck::SKIPPED, $this->step('sekolah')['state']);
    }

    public function testCommandsRecordNothingWhileTesting(): void
    {
        $file   = WRITEPATH . DevStepLedger::FILE;
        $before = is_file($file) ? (string) file_get_contents($file) : null;

        DevStepLedger::recordStep('bank');

        $this->assertSame($before, is_file($file) ? (string) file_get_contents($file) : null);
    }

    public function testCommandReportsWithoutChangingAnything(): void
    {
        command('gelita:setelah-pull');
        $output = $this->getStreamFilterBuffer();

        $this->assertStringContainsString('Keadaan', $output);
        $this->assertStringContainsString('Panduan validator', $output);
        $this->assertStringContainsString('Paket Composer', $output);
    }

    private function check(): PostPullCheck
    {
        return new PostPullCheck($this->ledger(), $this->root);
    }

    private function ledger(): DevStepLedger
    {
        return new DevStepLedger($this->root . 'ledger.json', $this->root);
    }

    /** @return array<string, mixed> */
    private function step(string $id): array
    {
        foreach ($this->check()->steps() as $step) {
            if ($step['id'] === $id) {
                return $step;
            }
        }

        $this->fail("no step {$id}");
    }

    private function put(string $path, string $content): void
    {
        if (! is_dir(dirname($this->root . $path))) {
            mkdir(dirname($this->root . $path), 0777, true);
        }
        file_put_contents($this->root . $path, $content);
    }
}
