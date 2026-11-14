<?php

use App\Database\Seeds\ContentSeeder;
use App\Libraries\NarrationImporter;
use App\Libraries\SyntheticNarrationInstaller;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use Tests\Support\Database\NarrationTables;

/**
 * Installing backup narration made with ElevenLabs (SyntheticNarrationInstaller,
 * `gelita:narration:install`): takes are copied into the narration folder and
 * listed in produksi.json, so the import stores them as `tts`; real recordings
 * are never replaced unless asked.
 *
 * Same set-up as NarrationImporterTest: SQLite tables, the story script's
 * lines (intro-01 Narator, intro-05 Mbah Kedu, intro-06 Jaka), a test folder
 * under public/, and a workspace folder in the system temp directory.
 *
 * @internal
 */
final class SyntheticNarrationInstallerTest extends CIUnitTestCase
{
    use NarrationTables;
    use StreamFilterTrait;

    private const VOICES = <<<'MD'
        # Voices

        ElevenLabs model: v4 (`eleven_v4`)

        | Speaker (`tokoh`) | Character | Indonesian voice (name, voice ID) | English voice (name, voice ID) | Settings (stability, similarity, style) |
        |---|---|---|---|---|
        | Narator | Neutral storyteller | Sari (`aaa111`) | Claire (`bbb222`) | |
        | Jaka | Boy | | | |
        | Mbah Kedu | Elderly man | Mbah Kedu (`H1jaNlN43es4HneQ6wdS`) | | stability 0.5 |

        ## Pronunciation fixes

        | Written | Typed into ElevenLabs | Language | Used in (file codes) |
        |---|---|---|---|
        | Sindoro | Sin-do-ro | id | intro-01 |
        MD;

    private BaseConnection $sqlite;

    private string $folder;

    private string $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite    = Database::connect('tests');
        $this->folder    = 'assets/audio/narasi-uji-' . bin2hex(random_bytes(4)) . '/';
        $this->workspace = sys_get_temp_dir() . '/gelita-elevenlabs-' . bin2hex(random_bytes(4)) . '/';
        $this->createNarrationTables($this->sqlite);

        (new ContentSeeder(config('Database'), $this->sqlite))->seedDialogues();

        mkdir($this->workspace . 'out/id', 0775, true);
        mkdir($this->workspace . 'out/en', 0775, true);
        file_put_contents($this->workspace . 'voices.md', self::VOICES);
    }

    protected function tearDown(): void
    {
        $this->removeTree(FCPATH . $this->folder);
        $this->removeTree($this->workspace);
        $this->dropNarrationTables();

        parent::tearDown();
    }

    public function testVoiceSheetGivesModelAndOneVoicePerSpeakerAndLanguage(): void
    {
        $sheet = SyntheticNarrationInstaller::parseVoices(self::VOICES);

        $this->assertSame('v4 (eleven_v4)', $sheet['model']);
        $this->assertSame(['narator', 'jaka', 'mbah kedu'], array_keys($sheet['voices']), 'the pronunciation table is not read as voices');
        $this->assertSame(['id' => 'Sari (aaa111)', 'en' => 'Claire (bbb222)', 'settings' => ''], $sheet['voices']['narator']);
        $this->assertSame(['id' => '', 'en' => '', 'settings' => ''], $sheet['voices']['jaka']);
        $this->assertSame('ElevenLabs v4 (eleven_v4) · Mbah Kedu (H1jaNlN43es4HneQ6wdS) · stability 0.5', SyntheticNarrationInstaller::voiceProfile($sheet['model'], $sheet['voices']['mbah kedu'], 'id'));

        $empty = SyntheticNarrationInstaller::parseVoices("ElevenLabs model: _not chosen yet_\n");
        $this->assertSame(['model' => '', 'voices' => []], $empty, 'the placeholder in italics is not a model');
    }

    public function testTheWorkspaceVoiceSheetKeepsTheFormatTheInstallerReads(): void
    {
        $sheet = SyntheticNarrationInstaller::parseVoices((string) file_get_contents(ROOTPATH . 'ai-studio/elevenlabs-voice/voices.md'));

        $this->assertSame(['narator', 'jaka', 'mbah kedu'], array_keys($sheet['voices']));
        $this->assertSame(['id', 'en', 'settings'], array_keys($sheet['voices']['mbah kedu']));
    }

    public function testTakesAreCopiedListedInTheManifestAndImportedAsSynthetic(): void
    {
        $this->take('id', 'intro-01.wav', 1);
        $this->take('id', 'intro-05.wav', 2);
        $this->take('en', 'INTRO-01.WAV', 3);
        $this->take('id', 'intro-1.wav', 4);
        $this->take('id', 'intro-01.txt', 5);

        $report = $this->installer()->install($this->workspace);

        $this->assertSame([], $report['errors']);
        $this->assertSame(['id/intro-01', 'id/intro-05', 'en/intro-01'], $report['installed']);
        $this->assertSame(['id/intro-01.txt', 'id/intro-1.wav'], array_column($report['unknown'], 'file'));
        $this->assertTrue($report['written']);
        $this->assertFileEquals($this->workspace . 'out/en/INTRO-01.WAV', FCPATH . $this->folder . 'en/intro-01.wav');
        $this->assertFileDoesNotExist(FCPATH . $this->folder . 'id/intro-1.wav');

        $manifest = json_decode((string) file_get_contents(FCPATH . $this->folder . NarrationImporter::MANIFEST), true);
        $this->assertSame(['intro-01', 'intro-05'], array_keys($manifest['files']['id']));
        $this->assertSame([
            'sha256'        => hash_file('sha256', $this->workspace . 'out/id/intro-05.wav'),
            'voice_profile' => 'ElevenLabs v4 (eleven_v4) · Mbah Kedu (H1jaNlN43es4HneQ6wdS) · stability 0.5',
        ], $manifest['files']['id']['intro-05']);

        $importer = $this->importer();
        $this->assertSame(['intro-01', 'intro-05'], $importer->importFolder('id')['created']);
        $importer->importFolder('en');

        $kedu = $this->audioByKey('audio.narasi.id.intro-05');
        $this->assertSame('tts', $kedu['production_method']);
        $this->assertSame('ElevenLabs v4 (eleven_v4) · Mbah Kedu (H1jaNlN43es4HneQ6wdS) · stability 0.5', $kedu['voice_profile']);
        $this->assertSame('draft', $kedu['approval_status']);
        $this->assertSame('ElevenLabs v4 (eleven_v4) · Claire (bbb222)', $this->audioByKey('audio.narasi.en.intro-01')['voice_profile']);

        // A second run changes nothing
        $again = $this->installer()->install($this->workspace);
        $this->assertSame(['id/intro-01', 'id/intro-05', 'en/intro-01'], $again['unchanged']);
        $this->assertSame([], $again['installed']);
        $this->assertFalse($again['written']);
    }

    public function testNothingIsInstalledWhileAVoiceOrTheModelIsMissing(): void
    {
        $this->take('id', 'intro-01.wav', 1);
        $this->take('id', 'intro-06.wav', 2);   // Jaka: no voice in the sheet
        $this->take('en', 'intro-05.wav', 3);   // Mbah Kedu: no English voice

        $report = $this->installer()->install($this->workspace);

        $this->assertSame(['Suara Jaka (ID) belum diisi di voices.md.', 'Suara Mbah Kedu (EN) belum diisi di voices.md.'], $report['errors']);
        $this->assertSame([], $report['installed']);
        $this->assertFalse($report['written']);
        $this->assertDirectoryDoesNotExist(FCPATH . $this->folder);

        unlink($this->workspace . 'out/id/intro-06.wav');
        unlink($this->workspace . 'out/en/intro-05.wav');
        file_put_contents($this->workspace . 'voices.md', str_replace('v4 (`eleven_v4`)', '_not chosen yet_', self::VOICES));

        $report = $this->installer()->install($this->workspace);

        $this->assertSame(['Model ElevenLabs belum diisi di voices.md (baris "ElevenLabs model:").'], $report['errors']);
        $this->assertDirectoryDoesNotExist(FCPATH . $this->folder);
    }

    public function testARealRecordingIsKeptUnlessOverwriteIsAsked(): void
    {
        // intro-01: a voice actor's file in the folder; intro-05: a voice actor's upload from the panel
        $this->folderFile('id', 'intro-01.wav', 50);
        $actor = hash_file('sha256', FCPATH . $this->folder . 'id/intro-01.wav');
        $this->sqlite->table('media_assets')->insert([
            'asset_key' => 'audio.narasi.id.intro-05', 'asset_type' => 'audio', 'storage_path' => 'assets/uploads-test/kedu.mp3',
            'mime_type' => 'audio/mpeg', 'sha256' => str_repeat('a', 64), 'is_active' => 1,
        ]);
        $this->sqlite->table('audio_assets')->insert([
            'media_asset_id' => $this->sqlite->insertID(), 'locale' => 'id', 'context_code' => 'intro',
            'transcript' => 'Uji', 'approval_status' => 'approved', 'production_method' => 'own_recording',
        ]);

        $this->take('id', 'intro-01.wav', 1);
        $this->take('id', 'intro-05.wav', 2);
        $this->take('id', 'intro-04.wav', 3);

        $report = $this->installer()->install($this->workspace);

        $this->assertSame(['id/intro-04'], $report['installed']);
        $this->assertSame(['id/intro-01', 'id/intro-05'], array_column($report['kept'], 'file'));
        $this->assertStringContainsString('folder narasi (intro-01.wav)', $report['kept'][0]['reason']);
        $this->assertStringContainsString('suara asli', $report['kept'][1]['reason']);
        $this->assertSame($actor, hash_file('sha256', FCPATH . $this->folder . 'id/intro-01.wav'));
        $this->assertFileDoesNotExist(FCPATH . $this->folder . 'id/intro-05.wav');

        $manifest = json_decode((string) file_get_contents(FCPATH . $this->folder . NarrationImporter::MANIFEST), true);
        $this->assertSame(['intro-04'], array_keys($manifest['files']['id']), 'a kept line is not listed as synthetic');

        $forced = $this->installer()->install($this->workspace, false, true);

        $this->assertSame(['id/intro-01', 'id/intro-05'], $forced['replaced']);
        $this->assertSame([], $forced['kept']);
        $this->assertFileEquals($this->workspace . 'out/id/intro-01.wav', FCPATH . $this->folder . 'id/intro-01.wav');
    }

    public function testANewTakeReplacesTheEarlierSyntheticTake(): void
    {
        $this->take('id', 'intro-01.wav', 1);
        $this->installer()->install($this->workspace);
        $this->importer()->importFolder('id');
        $this->sqlite->table('audio_assets')->update(['approval_status' => 'approved']);

        $this->take('id', 'intro-01.wav', 9);
        $report = $this->installer()->install($this->workspace);

        $this->assertSame(['id/intro-01'], $report['replaced']);
        $this->assertSame([], $report['kept']);
        $this->assertFileEquals($this->workspace . 'out/id/intro-01.wav', FCPATH . $this->folder . 'id/intro-01.wav');

        touch(FCPATH . $this->folder . 'id/intro-01.wav', time() + 5);
        $this->assertSame(['intro-01'], $this->importer()->importFolder('id')['replaced']);

        $audio = $this->audioByKey('audio.narasi.id.intro-01');
        $this->assertSame('tts', $audio['production_method']);
        $this->assertSame('draft', $audio['approval_status'], 'a changed take is listened to again');

        // The next take comes in another format: the earlier file does not stay beside it
        unlink($this->workspace . 'out/id/intro-01.wav');
        file_put_contents($this->workspace . 'out/id/intro-01.mp3', 'take three');
        $report = $this->installer()->install($this->workspace);

        $this->assertSame(['id/intro-01'], $report['replaced']);
        $this->assertFileExists(FCPATH . $this->folder . 'id/intro-01.mp3');
        $this->assertFileDoesNotExist(FCPATH . $this->folder . 'id/intro-01.wav');
    }

    public function testDryRunWritesNothing(): void
    {
        $this->take('id', 'intro-01.wav', 1);

        $report = $this->installer()->install($this->workspace, true);

        $this->assertSame(['id/intro-01'], $report['installed']);
        $this->assertTrue($report['dry_run']);
        $this->assertFalse($report['written']);
        $this->assertDirectoryDoesNotExist(FCPATH . $this->folder);
    }

    public function testATakeImportedBeforeItWasListedIsCorrectedToSynthetic(): void
    {
        // Copied by hand and imported without a manifest: stored as a real voice
        $this->take('id', 'intro-01.wav', 1);
        mkdir(FCPATH . $this->folder . 'id', 0775, true);
        copy($this->workspace . 'out/id/intro-01.wav', FCPATH . $this->folder . 'id/intro-01.wav');
        $this->importer()->importFolder('id');
        $this->sqlite->table('audio_assets')->update(['approval_status' => 'approved']);
        $this->assertSame('own_recording', $this->audioByKey('audio.narasi.id.intro-01')['production_method']);

        $report = $this->installer()->install($this->workspace);
        $this->assertSame(['id/intro-01'], $report['unchanged']);
        $this->assertTrue($report['written'], 'the manifest was written');

        $this->assertSame(['intro-01'], $this->importer()->importFolder('id')['unchanged']);

        $audio = $this->audioByKey('audio.narasi.id.intro-01');
        $this->assertSame('tts', $audio['production_method']);
        $this->assertSame('ElevenLabs v4 (eleven_v4) · Sari (aaa111)', $audio['voice_profile']);
        $this->assertSame('approved', $audio['approval_status'], 'the same recording keeps its approval');
    }

    public function testCommandReportsAMissingWorkspace(): void
    {
        command('gelita:narration:install --dir=' . $this->workspace . 'tidak-ada');

        $this->assertStringContainsString('tidak-ada/out/ tidak ada.', $this->getStreamFilterBuffer());
    }

    private function installer(): SyntheticNarrationInstaller
    {
        return new SyntheticNarrationInstaller($this->importer(), $this->sqlite);
    }

    private function importer(): NarrationImporter
    {
        return new NarrationImporter($this->sqlite, null, $this->folder);
    }

    /** WAV PCM 8 kHz 8-bit mono, one second; $seed makes the content differ. */
    private function wavBytes(int $seed): string
    {
        return 'RIFF' . pack('V', 36 + 8000) . 'WAVE'
            . 'fmt ' . pack('V', 16) . pack('v', 1) . pack('v', 1) . pack('V', 8000) . pack('V', 8000) . pack('v', 1) . pack('v', 8)
            . 'data' . pack('V', 8000) . str_repeat(chr(128 + $seed % 100), 8000);
    }

    private function take(string $locale, string $name, int $seed): void
    {
        file_put_contents($this->workspace . 'out/' . $locale . '/' . $name, $this->wavBytes($seed));
    }

    private function folderFile(string $locale, string $name, int $seed): void
    {
        $dir = FCPATH . $this->folder . $locale . '/';

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($dir . $name, $this->wavBytes($seed));
    }

    /** @return array<string, mixed> */
    private function audioByKey(string $key): array
    {
        return $this->sqlite->table('audio_assets aa')->select('aa.*')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->where('ma.asset_key', $key)->get()->getRowArray();
    }

    private function removeTree(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
