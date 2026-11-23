<?php

use App\Commands\SoundInstall;
use App\Libraries\AssetChecklist;
use App\Libraries\SoundInstaller;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Music and sound effects made with ElevenLabs (owner, 15 November 2026: done
 * the same way as the backup narration). SoundInstaller copies the mastered
 * takes of ai-studio/elevenlabs-sound/out/{music,sfx}/ over the game's files
 * and lists them in produksi.json with sha256 and model; names outside
 * AssetChecklist::SOUNDS are never copied. Runs on two temp folders.
 *
 * @internal
 */
final class SoundInstallerTest extends CIUnitTestCase
{
    private const SETTINGS = <<<'MD'
        # Settings

        ElevenLabs music model: Eleven Music (`music_v1`)
        ElevenLabs sound-effects model: Sound Effects v2 (`eleven_text_to_sound_v2`)
        MD;

    private string $work;
    private string $audio;

    protected function setUp(): void
    {
        parent::setUp();

        $base        = sys_get_temp_dir() . '/gelita-sound-' . bin2hex(random_bytes(4));
        $this->work  = $base . '/work/';
        $this->audio = $base . '/audio/';
        mkdir($this->work . 'out/music', 0777, true);
        mkdir($this->work . 'out/sfx', 0777, true);
        mkdir($this->audio . 'music', 0777, true);
        mkdir($this->audio . 'sfx', 0777, true);
        file_put_contents($this->work . 'settings.md', self::SETTINGS);
        file_put_contents($this->audio . 'music/map.mp3', self::mp3('bundled gamelan'));
    }

    protected function tearDown(): void
    {
        $this->remove(dirname($this->work));
        parent::tearDown();
    }

    /** A file that starts like an MPEG audio frame and is long enough to pass the check. */
    private static function mp3(string $seed): string
    {
        return "\xFF\xFB\x90\x00" . str_repeat($seed, (int) ceil(2000 / strlen($seed)));
    }

    private function take(string $name, string $seed): void
    {
        file_put_contents($this->work . 'out/' . $name . '.mp3', self::mp3($seed));
    }

    private function remove(string $dir): void
    {
        foreach (glob($dir . '/{,.}[!.]*', GLOB_BRACE) ?: [] as $path) {
            is_dir($path) ? $this->remove($path) : unlink($path);
        }

        @rmdir($dir);
    }

    /** @return array<string, mixed> */
    private function manifest(): array
    {
        return json_decode((string) file_get_contents($this->audio . SoundInstaller::MANIFEST), true);
    }

    public function testSettingsNameTheModelPerKind(): void
    {
        $this->assertSame(
            ['music' => 'Eleven Music (music_v1)', 'sfx' => 'Sound Effects v2 (eleven_text_to_sound_v2)'],
            SoundInstaller::parseSettings(self::SETTINGS),
        );
        $this->assertSame([], SoundInstaller::parseSettings("ElevenLabs music model:\nElevenLabs sound-effects model:   \n"));
    }

    public function testTakesReplaceTheGameFilesAndAreListedInTheManifest(): void
    {
        $this->take('music/map', 'new map');
        $this->take('sfx/click', 'new click');

        $report = (new SoundInstaller($this->audio))->install($this->work);

        $this->assertSame([], $report['errors']);
        $this->assertSame(['music/map', 'sfx/click'], $report['installed']);
        $this->assertTrue($report['written']);
        $this->assertFileEquals($this->work . 'out/music/map.mp3', $this->audio . 'music/map.mp3');
        $this->assertFileEquals($this->work . 'out/sfx/click.mp3', $this->audio . 'sfx/click.mp3');

        $files = $this->manifest()['files'];
        $this->assertSame(['music/map', 'sfx/click'], array_keys($files));
        $this->assertSame(hash_file('sha256', $this->audio . 'music/map.mp3'), $files['music/map']['sha256']);
        $this->assertSame('Eleven Music (music_v1)', $files['music/map']['model']);
        $this->assertSame('Sound Effects v2 (eleven_text_to_sound_v2)', $files['sfx/click']['model']);
    }

    public function testRunningAgainChangesNothingAndANewTakeReplacesTheEarlierOne(): void
    {
        $this->take('music/map', 'first');
        (new SoundInstaller($this->audio))->install($this->work);
        $stamp = filemtime($this->audio . SoundInstaller::MANIFEST);

        $again = (new SoundInstaller($this->audio))->install($this->work);
        $this->assertSame(['music/map'], $again['unchanged']);
        $this->assertFalse($again['written']);
        $this->assertSame($stamp, filemtime($this->audio . SoundInstaller::MANIFEST));

        $this->take('music/map', 'second');
        $next = (new SoundInstaller($this->audio))->install($this->work);
        $this->assertSame(['music/map'], $next['replaced']);
        $this->assertSame(hash_file('sha256', $this->work . 'out/music/map.mp3'), $this->manifest()['files']['music/map']['sha256']);
    }

    public function testADryRunWritesNothing(): void
    {
        $this->take('music/map', 'new map');
        $before = (string) file_get_contents($this->audio . 'music/map.mp3');

        $report = (new SoundInstaller($this->audio))->install($this->work, true);

        $this->assertSame(['music/map'], $report['installed']);
        $this->assertTrue($report['written']);
        $this->assertSame($before, file_get_contents($this->audio . 'music/map.mp3'));
        $this->assertFileDoesNotExist($this->audio . SoundInstaller::MANIFEST);
    }

    public function testUnknownNamesAreReportedNotCopied(): void
    {
        $this->take('music/peta', 'x');
        $this->take('sfx/corect', 'x');
        $this->take('music/welcome', 'ok');

        $report = (new SoundInstaller($this->audio))->install($this->work);

        $this->assertSame([
            ['file' => 'music/peta.mp3', 'suggestion' => null],
            ['file' => 'sfx/corect.mp3', 'suggestion' => 'sfx/correct'],
        ], $report['unknown']);
        $this->assertSame(['music/welcome'], $report['installed']);
        $this->assertFileDoesNotExist($this->audio . 'music/peta.mp3');
        $this->assertFileDoesNotExist($this->audio . 'sfx/corect.mp3');
    }

    public function testAMissingModelOrABrokenFileStopsBeforeAnythingIsCopied(): void
    {
        file_put_contents($this->work . 'settings.md', "ElevenLabs music model: Eleven Music (`music_v1`)\nElevenLabs sound-effects model:\n");
        $this->take('music/map', 'new map');
        $this->take('sfx/click', 'click');

        $report = (new SoundInstaller($this->audio))->install($this->work);
        $this->assertCount(1, $report['errors']);
        $this->assertStringContainsString('efek suara', $report['errors'][0]);
        $this->assertSame([], $report['installed']);
        $this->assertSame(self::mp3('bundled gamelan'), file_get_contents($this->audio . 'music/map.mp3'));

        file_put_contents($this->work . 'settings.md', self::SETTINGS);
        file_put_contents($this->work . 'out/sfx/click.mp3', '<html>not a sound</html>');
        $broken = (new SoundInstaller($this->audio))->install($this->work);
        $this->assertSame(['click.mp3 bukan berkas MP3 yang utuh.'], array_map(static fn (string $e): string => substr($e, 4), $broken['errors']));
        $this->assertFileDoesNotExist($this->audio . SoundInstaller::MANIFEST);
    }

    public function testTheWorkspaceListsEverySoundOfTheGame(): void
    {
        $dir  = ROOTPATH . SoundInstall::DEFAULT_DIR . '/';
        $rows = array_map(static fn (string $line): array => str_getcsv($line, ',', '"', ''), file($dir . 'sounds.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []);
        $head = array_shift($rows);

        $this->assertSame(['kode', 'jenis', 'putar_ulang', 'durasi_detik', 'kbps', 'lufs', 'puncak_db', 'layar', 'suasana', 'prompt'], $head);
        $this->assertSame(array_keys(AssetChecklist::SOUNDS), array_column($rows, 0), 'one row per sound, in checklist order');

        foreach ($rows as $row) {
            [$code, $kind, $loop, , $kbps, $lufs, $peak] = $row;
            $this->assertSame(explode('/', $code)[0], $kind, $code);
            $this->assertSame($kind === 'music' ? 'ya' : 'tidak', $loop, $code . ': music loops, effects do not');
            $this->assertContains($kbps, ['96', '128'], $code);
            $this->assertTrue(($lufs === '') !== ($peak === ''), $code . ': a loudness or a peak target, not both');
            $this->assertNotSame('', $row[9], $code . ' has a prompt');
        }

        $this->assertNotSame([], SoundInstaller::parseSettings((string) file_get_contents($dir . SoundInstaller::SETTINGS)));
        $this->assertTrue(is_executable($dir . 'master.sh'));
    }

    public function testBrowsersGetACacheVersionOnceSoundsWereInstalled(): void
    {
        helper('gelita');
        $manifest = FCPATH . 'assets/audio/' . SoundInstaller::MANIFEST;

        $this->assertSame(is_file($manifest) ? substr((string) sha1_file($manifest), 0, 10) : '', sound_files_version());
        $this->assertSame(sound_files_version(), js_config('id')['audioVersion']);

        $audio = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
        $this->assertStringContainsString('CONFIG.audioVersion ? `?v=${encodeURIComponent(CONFIG.audioVersion)}`', $audio);
        $this->assertStringContainsString("audioVersion: typeof parsed.audioVersion === 'string'", (string) file_get_contents(FCPATH . 'assets/js/core/config.js'));
    }
}
