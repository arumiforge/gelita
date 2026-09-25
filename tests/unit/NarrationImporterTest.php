<?php

use App\Libraries\MediaStore;
use App\Libraries\NarrationCatalog;
use App\Libraries\NarrationImporter;
use App\Libraries\StorySync;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Database;
use Tests\Support\Database\NarrationTables;

/**
 * Impor rekaman narasi (NarrationImporter, `gelita:narration:import`) dan
 * persetujuan massal (NarrationCatalog).
 *
 * Memakai grup basis data `tests` (SQLite di memori) dengan tabel versi
 * ringkas; baris `dialogues` diisi StorySync dari naskah (88 baris). Berkas
 * folder ditulis ke folder uji di bawah public/ dan salinan unggahan ke
 * public/assets/uploads/, lalu dibersihkan.
 *
 * @internal
 */
final class NarrationImporterTest extends CIUnitTestCase
{
    use NarrationTables;
    use StreamFilterTrait;

    private BaseConnection $sqlite;

    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite = Database::connect('tests');
        $this->folder = 'assets/audio/narasi-uji-' . bin2hex(random_bytes(4)) . '/';
        $this->createNarrationTables($this->sqlite);

        (new StorySync($this->sqlite))->run();
    }

    protected function tearDown(): void
    {
        $this->removeTree(FCPATH . $this->folder);

        foreach (glob(FCPATH . MediaStore::UPLOAD_DIR . 'audio.narasi.*') ?: [] as $file) {
            @unlink($file);
        }

        $this->dropNarrationTables();

        parent::tearDown();
    }

    // ------------------------------------------------------------- nama berkas

    public function testEveryFileNamePatternMapsToItsLine(): void
    {
        $importer = $this->importer();
        $cases    = [
            'intro-01.mp3'             => ['intro', null, 1],
            'peta-03.mp3'              => ['map_intro', null, 3],
            'kenal-temanggung-02.mp3'  => ['region_intro', 'temanggung', 2],
            'dialog-magelang-16.ogg'   => ['level_open', 'magelang', 16],
            'tuntas-wonosobo-04.m4a'   => ['level_done', 'wonosobo', 4],
            'penutup-05.wav'           => ['ending', null, 5],
            'DIALOG-Temanggung-07.MP3' => ['level_open', 'temanggung', 7],
        ];

        foreach ($cases as $file => [$context, $level, $sequence]) {
            $match = $importer->match($file);
            $this->assertNull($match['error'], $file);

            $line = $importer->lines()[$match['code']];
            $this->assertSame($context, $line['context_code'], $file);
            $this->assertSame($level, $line['level_code'], $file);
            $this->assertSame($sequence, (int) $line['sequence'], $file);
        }

        // Kode berkas setiap baris naskah = kolom `code` data cerita
        $this->assertSame(array_column(StorySync::storyData(), 'code'), array_keys($importer->lines()));
    }

    public function testUnknownNamesAreReportedWithASuggestion(): void
    {
        $importer = $this->importer();

        $typo = $importer->match('dialog-temangung-03.mp3');
        $this->assertNull($typo['code']);
        $this->assertStringContainsString('wilayah "temangung" tidak ada', $typo['error']);
        $this->assertSame('dialog-temanggung-03.mp3', $typo['suggestion']);

        $digits = $importer->match('intro-1.mp3');
        $this->assertStringContainsString('tidak sesuai pola', $digits['error']);
        $this->assertSame('intro-01.mp3', $digits['suggestion']);

        $beyond = $importer->match('intro-12.mp3');
        $this->assertStringContainsString('intro-12 tidak ada di naskah', $beyond['error']);

        $extension = $importer->match('intro-02.flac');
        $this->assertStringContainsString('ekstensi .flac tidak didukung', $extension['error']);
        $this->assertSame('intro-02.mp3', $extension['suggestion']);

        $this->assertNull($importer->match('rekaman-baru.mp3')['suggestion'], 'nama yang jauh tidak diberi saran');
    }

    public function testUnknownRegionFromTheLevelsTable(): void
    {
        // Kode wilayah dibaca dari tabel levels, bukan ditulis mati
        $this->sqlite->table('levels')->where('code', 'wonosobo')->update(['code' => 'dieng']);
        $importer = $this->importer();

        $this->assertStringContainsString('wilayah "wonosobo" tidak ada', (string) $importer->match('kenal-wonosobo-01.mp3')['error']);
        $this->assertNull($importer->match('kenal-dieng-01.mp3')['error']);
    }

    // ---------------------------------------------------------------- impor

    public function testFolderImportCreatesDraftAudioAndLinksTheRightLocale(): void
    {
        $this->folderFile('id', 'intro-05.wav', 1);
        $this->folderFile('en', 'intro-05.wav', 2);
        $this->folderFile('id', 'kenal-magelang-02.wav', 3);
        $this->folderFile('id', 'catatan.txt', 4);

        $importer = $this->importer();
        $id       = $importer->importFolder('id', false, 9);
        $en       = $importer->importFolder('en', false, 9);

        $this->assertSame(['intro-05', 'kenal-magelang-02'], $id['created']);
        $this->assertSame(['intro-05'], $en['created']);
        $this->assertSame('catatan.txt', $id['unknown'][0]['file']);
        $this->assertCount(86, $id['missing']);
        $this->assertCount(87, $en['missing']);
        $this->assertContains('penutup-05', $id['missing']);

        $line    = $this->line(null, 'intro', 5);
        $audioId = $this->audioByKey('audio.narasi.id.intro-05');
        $audioEn = $this->audioByKey('audio.narasi.en.intro-05');

        $this->assertSame((int) $audioId['id'], (int) $line['audio_id_asset_id']);
        $this->assertSame((int) $audioEn['id'], (int) $line['audio_en_asset_id']);

        $this->assertSame('draft', $audioId['approval_status']);
        $this->assertSame('mbah_kedu', $audioId['character_code']);
        $this->assertSame('intro', $audioId['context_code']);
        $this->assertSame('own_recording', $audioId['production_method']);
        $this->assertSame($line['text_id'], $audioId['transcript']);
        $this->assertSame($line['text_en'], $audioEn['transcript']);
        $this->assertSame(1000, (int) $audioId['duration_ms']);

        // Berkas folder didaftarkan di tempatnya
        $media = $this->sqlite->table('media_assets')->where('asset_key', 'audio.narasi.id.intro-05')->get()->getRowArray();
        $this->assertSame($this->folder . 'id/intro-05.wav', $media['storage_path']);
        $this->assertSame('audio', $media['asset_type']);
        $this->assertSame('id', $media['locale']);

        // Narator tanpa tokoh
        $this->folderFile('id', 'intro-01.wav', 5);
        $this->importer()->importFolder('id');
        $this->assertNull($this->audioByKey('audio.narasi.id.intro-01')['character_code']);
    }

    public function testReimportReplacesTheRecordingAndResetsItToDraft(): void
    {
        $this->folderFile('id', 'peta-02.wav', 1);
        $this->importer()->importFolder('id');
        $this->sqlite->table('audio_assets')->update(['approval_status' => 'approved', 'approved_by' => 3, 'approved_at' => '2026-09-01 10:00:00']);

        // Isi sama persis: persetujuan tidak hilang
        $same = $this->importer()->importFolder('id');
        $this->assertSame(['peta-02'], $same['unchanged']);
        $this->assertSame('approved', $this->audioByKey('audio.narasi.id.peta-02')['approval_status']);

        // Rekaman berubah: baris audio yang sama, kembali draft
        $before = $this->audioByKey('audio.narasi.id.peta-02');
        $this->folderFile('id', 'peta-02.wav', 7, 12000);
        $report = $this->importer()->importFolder('id');
        $after  = $this->audioByKey('audio.narasi.id.peta-02');

        $this->assertSame(['peta-02'], $report['replaced']);
        $this->assertSame($before['id'], $after['id']);
        $this->assertSame('draft', $after['approval_status']);
        $this->assertNull($after['approved_by']);
        $this->assertNull($after['approved_at']);
        $this->assertSame(1500, (int) $after['duration_ms']);
        $this->assertSame(1, $this->sqlite->table('audio_assets')->countAllResults());
    }

    public function testUploadedFilesAreCopiedToUploadsAndReportedPerName(): void
    {
        $files = [
            ['name' => 'tuntas-wonosobo-02.wav', 'path' => $this->tempWav(1)],
            ['name' => 'tuntas-wonosobo-2.wav', 'path' => $this->tempWav(2)],
            ['name' => 'TUNTAS-WONOSOBO-02.wav', 'path' => $this->tempWav(3)],
            ['name' => 'penutup-01.wav', 'path' => '', 'error' => 'melebihi upload_max_filesize'],
        ];

        $report = $this->importer()->importFiles($files, 'en', false, 4);

        $this->assertSame(['tuntas-wonosobo-02'], $report['created']);
        $this->assertSame('tuntas-wonosobo-02.wav', $report['unknown'][0]['suggestion']);
        $this->assertStringStartsWith('ganda', $report['unknown'][1]['reason']);
        $this->assertSame('penutup-01.wav', $report['failed'][0]['file']);

        $media = $this->sqlite->table('media_assets')->where('asset_key', 'audio.narasi.en.tuntas-wonosobo-02')->get()->getRowArray();
        $this->assertSame(MediaStore::UPLOAD_DIR . 'audio.narasi.en.tuntas-wonosobo-02.wav', $media['storage_path']);
        $this->assertFileExists(FCPATH . $media['storage_path']);

        $line = $this->line('wonosobo', 'level_done', 2);
        $this->assertNull($line['audio_id_asset_id'], 'unggahan EN tidak menyentuh kolom ID');
        $this->assertSame((int) $this->audioByKey('audio.narasi.en.tuntas-wonosobo-02')['id'], (int) $line['audio_en_asset_id']);
    }

    public function testNonAudioUploadIsRejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'nar');
        file_put_contents($path, '<?php echo "bukan audio";');

        $report = $this->importer()->importFiles([['name' => 'intro-02.mp3', 'path' => $path]], 'id');
        @unlink($path);

        $this->assertSame([], $report['created']);
        $this->assertStringContainsString('tidak diizinkan', $report['failed'][0]['error']);
        $this->assertSame(0, $this->sqlite->table('media_assets')->countAllResults());
    }

    public function testFolderDoesNotOverwriteANewerPanelUpload(): void
    {
        $this->folderFile('id', 'intro-03.wav', 1);
        touch(FCPATH . $this->folder . 'id/intro-03.wav', time() - 3600);

        $this->importer()->importFiles([['name' => 'intro-03.wav', 'path' => $this->tempWav(9)]], 'id');
        $report = $this->importer()->importFolder('id');

        $this->assertSame(['intro-03'], $report['kept']);
        $this->assertStringStartsWith(MediaStore::UPLOAD_DIR, $this->sqlite->table('media_assets')->get()->getRowArray()['storage_path']);
    }

    public function testDryRunWritesNothing(): void
    {
        $this->folderFile('id', 'intro-01.wav', 1);
        $this->folderFile('id', 'dialog-temanggung-01.wav', 2);
        $uploads = glob(FCPATH . MediaStore::UPLOAD_DIR . '*') ?: [];

        $folder = $this->importer()->importFolder('id', true);
        $upload = $this->importer()->importFiles([['name' => 'peta-01.wav', 'path' => $this->tempWav(3)]], 'id', true);

        $this->assertSame(['intro-01', 'dialog-temanggung-01'], $folder['created']);
        $this->assertSame(['peta-01'], $upload['created']);
        $this->assertCount(86, $folder['missing']);
        $this->assertSame(0, $this->sqlite->table('media_assets')->countAllResults());
        $this->assertSame(0, $this->sqlite->table('audio_assets')->countAllResults());
        $this->assertSame(0, $this->sqlite->table('dialogues')->where('audio_id_asset_id IS NOT NULL')->countAllResults());
        $this->assertSame(0, $this->sqlite->table('audit_logs')->countAllResults());
        $this->assertSame($uploads, glob(FCPATH . MediaStore::UPLOAD_DIR . '*') ?: []);
    }

    public function testCommandDryRunAndLocaleOption(): void
    {
        $commands = service('commands')->getCommands();
        $this->assertArrayHasKey('gelita:narration:import', $commands);
        $this->assertStringContainsString('db_sync_timezone()', (string) file_get_contents(APPPATH . 'Commands/NarrationImport.php'));

        command('gelita:narration:import --locale=fr');
        $this->assertStringContainsString('--locale harus salah satu dari', $this->getStreamFilterBuffer());

        // Folder konvensi di repositori kosong: tidak ada yang ditulis
        $this->resetStreamFilterBuffer();
        command('gelita:narration:import --dry-run');
        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('Uji coba (--dry-run)', $output);
        $this->assertStringContainsString('88 baris naskah belum punya rekaman ID', $output);
        $this->assertStringContainsString('88 baris naskah belum punya rekaman EN', $output);
        $this->assertSame(0, $this->sqlite->table('audit_logs')->countAllResults());
    }

    // ---------------------------------------------------- persetujuan massal

    public function testBulkApprovalOnlyTouchesNarrationDraftsOfThatLocale(): void
    {
        $this->folderFile('id', 'intro-01.wav', 1);
        $this->folderFile('id', 'intro-02.wav', 2);
        $this->folderFile('en', 'intro-01.wav', 3);
        $importer = $this->importer();
        $importer->importFolder('id');
        $importer->importFolder('en');

        // Audio lain: narasi pembuka misi (bukan naskah) dan audio manual yang ditautkan ke baris naskah
        $mission = $this->audioRow('audio.mission.tmg-1.id', 'mission.tmg-1', 'id');
        $manual  = $this->audioRow('audio.jaka.peta.1.id', 'map_intro.1', 'id');
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'map_intro', 'sequence' => 1])->update(['audio_id_asset_id' => $manual]);

        $count = (new NarrationCatalog($this->sqlite, $importer))->approveDrafts('id', 5);

        $this->assertSame(3, $count);
        $this->assertSame('approved', $this->audioByKey('audio.narasi.id.intro-01')['approval_status']);
        $this->assertSame('5', (string) $this->audioByKey('audio.narasi.id.intro-02')['approved_by']);
        $this->assertNotNull($this->audioByKey('audio.narasi.id.intro-02')['approved_at']);
        $this->assertSame('approved', $this->sqlite->table('audio_assets')->where('id', $manual)->get()->getRow('approval_status'));
        $this->assertSame('draft', $this->sqlite->table('audio_assets')->where('id', $mission)->get()->getRow('approval_status'), 'audio misi tidak ikut');
        $this->assertSame('draft', $this->audioByKey('audio.narasi.en.intro-01')['approval_status'], 'bahasa lain tidak ikut');

        $audit = $this->sqlite->table('audit_logs')->where('action', 'audio_approve_bulk')->get()->getRowArray();
        $this->assertSame('5', (string) $audit['staff_user_id']);
        $this->assertSame(3, json_decode((string) $audit['metadata_json'], true)['count']);
    }

    public function testCatalogReportsProgressAndTheRecordingList(): void
    {
        $this->folderFile('id', 'intro-01.wav', 1);
        $this->folderFile('id', 'intro-02.wav', 2);
        $importer = $this->importer();
        $importer->importFolder('id');
        $catalog = new NarrationCatalog($this->sqlite, $importer);
        $catalog->approveDrafts('id', null);
        $this->folderFile('id', 'intro-03.wav', 3);
        $importer->importFolder('id');

        $rows     = $catalog->rows();
        $progress = $catalog->progress($rows);

        $this->assertSame(['total' => 88, 'approved' => 2, 'draft' => 1, 'none' => 85], $progress['id']);
        $this->assertSame(['total' => 88, 'approved' => 0, 'draft' => 0, 'none' => 88], $progress['en']);
        $this->assertSame('approved', $rows[0]['audio']['id']['status']);
        $this->assertSame('none', $rows[0]['audio']['en']['status']);

        $groups = $catalog->groups($rows);
        $this->assertCount(12, $groups, 'intro, peta, 3×kenal, 3×dialog, 3×tuntas, penutup');
        $this->assertSame('Kenali wilayah · Temanggung', $groups[2]['label']);

        $list = $catalog->recordingList();
        $this->assertSame(['kode_berkas', 'konteks', 'wilayah', 'urutan', 'tokoh', 'pose', 'efek', 'judul_id', 'judul_en', 'teks_id', 'teks_en', 'audio_id', 'audio_en'], $list['headers']);
        $this->assertCount(88, $list['rows']);
        $this->assertSame(['intro-03', 'intro', null, 3, 'Narator', null, 'fog'], array_slice($list['rows'][2], 0, 7));
        $this->assertSame(['draft', 'belum ada'], array_slice($list['rows'][2], 11));

        $path = $catalog->writeRecordingList(WRITEPATH . 'exports/uji-daftar-rekaman.xlsx');
        $zip  = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);
        $this->assertStringContainsString('dialog-wonosobo-16', $sheet);
    }

    // ---------------------------------------------------------- durasi MP3

    public function testMp3DurationFromCbrFramesAndXingHeader(): void
    {
        $store = new MediaStore();
        $frame = "\xFF\xFB\x90\x00" . str_repeat("\x00", 413);   // MPEG1 Layer III 128 kbps 44,1 kHz = 417 byte
        $cbr   = tempnam(sys_get_temp_dir(), 'mp3') . '.mp3';
        file_put_contents($cbr, 'ID3' . "\x04\x00\x00\x00\x00\x00\x0A" . str_repeat("\x00", 10) . str_repeat($frame, 100));

        $this->assertEqualsWithDelta(2606, $store->durationMs($cbr), 10);
        $this->assertSame('audio/mpeg', $store->detectMime($cbr));

        $xing = substr($frame, 0, 36) . 'Xing' . pack('N', 1) . pack('N', 1000);
        $vbr  = tempnam(sys_get_temp_dir(), 'mp3') . '.mp3';
        file_put_contents($vbr, $xing . str_repeat("\x00", 417 - strlen($xing)) . str_repeat($frame, 10));

        $this->assertSame(26122, $store->durationMs($vbr));
        @unlink($cbr);
        @unlink($vbr);
    }

    // -------------------------------------------------------------- bantuan

    private function importer(): NarrationImporter
    {
        return new NarrationImporter($this->sqlite, null, $this->folder);
    }

    /** WAV PCM 8 kHz 8-bit mono: 8000 byte data = 1 detik; $seed membedakan isinya. */
    private function wavBytes(int $seed, int $dataBytes = 8000): string
    {
        return 'RIFF' . pack('V', 36 + $dataBytes) . 'WAVE'
            . 'fmt ' . pack('V', 16) . pack('v', 1) . pack('v', 1) . pack('V', 8000) . pack('V', 8000) . pack('v', 1) . pack('v', 8)
            . 'data' . pack('V', $dataBytes) . str_repeat(chr(128 + $seed % 100), $dataBytes);
    }

    private function folderFile(string $locale, string $name, int $seed, int $dataBytes = 8000): void
    {
        $dir = FCPATH . $this->folder . $locale . '/';

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($dir . $name, str_ends_with($name, '.wav') ? $this->wavBytes($seed, $dataBytes) : 'catatan');
    }

    private function tempWav(int $seed): string
    {
        $path = tempnam(sys_get_temp_dir(), 'nar');
        file_put_contents($path, $this->wavBytes($seed));

        return $path;
    }

    private function audioRow(string $key, string $context, string $locale): int
    {
        $this->sqlite->table('media_assets')->insert(['asset_key' => $key, 'asset_type' => 'audio', 'storage_path' => 'assets/uploads/' . $key . '.mp3', 'mime_type' => 'audio/mpeg']);
        $this->sqlite->table('audio_assets')->insert([
            'media_asset_id' => $this->sqlite->insertID(), 'locale' => $locale, 'context_code' => $context,
            'transcript' => 'Uji', 'approval_status' => 'draft',
        ]);

        return (int) $this->sqlite->insertID();
    }

    /** @return array<string, mixed> */
    private function audioByKey(string $key): array
    {
        return $this->sqlite->table('audio_assets aa')->select('aa.*')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->where('ma.asset_key', $key)->get()->getRowArray();
    }

    /** @return array<string, mixed> */
    private function line(?string $level, string $context, int $sequence): array
    {
        $levelId = $level === null ? null : (int) $this->sqlite->table('levels')->where('code', $level)->get()->getRow('id');

        return $this->sqlite->table('dialogues')->where(['level_id' => $levelId, 'context_code' => $context, 'sequence' => $sequence])->get()->getRowArray();
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
