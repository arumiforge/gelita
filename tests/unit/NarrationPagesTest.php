<?php

use App\Database\Seeds\ContentSeeder;
use App\Libraries\MediaStore;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use Tests\Support\Database\NarrationTables;

/**
 * Halaman admin Narasi (`/admin/konten/narasi`), Unggah narasi, unduhan
 * daftar rekaman, persetujuan massal, dan Kelengkapan aset, lewat HTTP.
 *
 * Baris staf disuntikkan ke service staffContext (tanpa tabel staff_users);
 * basis data SQLite ringkas dari trait NarrationTables.
 *
 * @internal
 */
final class NarrationPagesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use NarrationTables;

    /** Hash CSRF (mode session) di sesi. */
    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    /**
     * Token POST: tokenRandomize aktif, jadi token = (hash XOR kunci) . kunci;
     * kunci nol menghasilkan hash itu sendiri.
     */
    private const CSRF = ['gelita_csrf' => self::CSRF_HASH . '00000000000000000000000000000000'];

    private BaseConnection $sqlite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite = Database::connect('tests');
        $this->createNarrationTables($this->sqlite);
        (new ContentSeeder(config('Database'), $this->sqlite))->seedDialogues();
        $this->injectStaff('admin');

        // Service security menyimpan hash CSRF test sebelumnya; muat ulang dari sesi uji ini
        Config\Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        $this->dropNarrationTables();

        parent::tearDown();
    }

    public function testNarrationPageListsEveryLineWithAudioStatus(): void
    {
        $draft    = $this->audio('audio.narasi.id.intro-01', 'id', 'draft');
        $approved = $this->audio('audio.narasi.en.intro-01', 'en', 'approved');
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 1])
            ->update(['audio_id_asset_id' => $draft, 'audio_en_asset_id' => $approved]);

        $result = $this->asAdmin()->call(Method::GET, 'admin/konten/narasi');
        $result->assertOK();
        $html = html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5);

        $this->assertSame(112, substr_count($html, 'Sunting</a>'));
        $this->assertStringContainsString('<code>kenal-wonosobo-04</code>', $html);
        $this->assertStringContainsString('Kenali wilayah · Magelang', $html);
        $this->assertStringContainsString('0/112 disetujui, 1 menunggu persetujuan, 111 belum ada', $html);
        $this->assertStringContainsString('1/112 disetujui, 0 menunggu persetujuan, 111 belum ada', $html);
        $this->assertStringContainsString('admin/konten/dialog/0?konteks=intro#slide-1', $html);
        $this->assertStringContainsString('audio-preview-sm', $html);
        // Tombol massal hanya untuk bahasa yang punya draft
        $this->assertStringContainsString('Setujui semua rekaman bahasa Indonesia (1)', $html);
        $this->assertStringNotContainsString('Setujui semua rekaman bahasa Inggris', $html);
        $this->assertStringContainsString('admin/konten/narasi/daftar-rekaman', $html);
    }

    public function testClueRecordingsGetTheirOwnGroupAndCountInTheChecklist(): void
    {
        $nodeId = $this->seedHuntNode($this->sqlite);
        $draft  = $this->audio('audio.narasi.id.petunjuk-tmg-4-01', 'id', 'draft', 'hunt_clue');
        $this->sqlite->table('challenge_items')->where('item_key', 'tmg-4-x01')->update(['audio_prompt_id' => $draft]);
        $itemId = (int) $this->sqlite->table('challenge_items')->where('item_key', 'tmg-4-x01')->get()->getRow('id');

        $html = html_entity_decode((string) $this->asAdmin()->call(Method::GET, 'admin/konten/narasi')->getBody(), ENT_QUOTES | ENT_HTML5);

        // 112 baris naskah + 4 petunjuk target tmg-4 (Dok 2 §11.2: 4 per node cari)
        $this->assertSame(116, substr_count($html, 'Sunting</a>'));
        $this->assertStringContainsString('Petunjuk Cari Objek · Temanggung (tmg-4)', $html);
        $this->assertStringContainsString('<code>petunjuk-tmg-4-04</code>', $html);
        $this->assertStringNotContainsString('petunjuk-tmg-4-05', $html, 'pengecoh tanpa petunjuk');
        $this->assertStringContainsString('Temukan ikan uceng, ikan sungai kecil yang menjadi makanan khas Temanggung.', $html);
        $this->assertStringContainsString('admin/konten/node/' . $nodeId . '#item-' . $itemId, $html);
        $this->assertStringContainsString('Teks petunjuk berasal dari bank soal', $html);
        $this->assertStringContainsString('0/116 disetujui, 1 menunggu persetujuan, 115 belum ada', $html);
        $this->assertStringContainsString('Setujui semua rekaman bahasa Indonesia (1)', $html);

        $checklist = html_entity_decode((string) $this->asAdmin()->call(Method::GET, 'admin/media/kelengkapan')->getBody(), ENT_QUOTES | ENT_HTML5);
        $this->assertStringContainsString('0/116 disetujui, 1 menunggu persetujuan, 115 belum ada', $checklist);
        $this->assertStringContainsString('0/116 disetujui, 0 menunggu persetujuan, 116 belum ada', $checklist);
    }

    public function testResearcherReadsNarrationButCannotUpload(): void
    {
        $this->injectStaff('peneliti');
        $page = (string) $this->withSession(['staff_id' => 7, 'staff_role' => 'peneliti'])->call(Method::GET, 'admin/konten/narasi')->getBody();

        $this->assertStringNotContainsString('admin/konten/narasi/unggah', $page, 'no upload button (content.edit)');
        $this->assertStringNotContainsString('admin/konten/narasi/setujui', $page, 'no approve-all form');

        $this->expectException(CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession(['staff_id' => 7, 'staff_role' => 'peneliti'])->call(Method::GET, 'admin/konten/narasi/unggah');
    }

    public function testRecordingListDownloadsAsXlsx(): void
    {
        $result = $this->asAdmin()->call(Method::GET, 'admin/konten/narasi/daftar-rekaman');
        $response = $result->response();
        $this->assertInstanceOf(CodeIgniter\HTTP\DownloadResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        // Header dan isi DownloadResponse baru disusun saat dikirim
        $response->buildHeaders();
        ob_start();
        $response->sendBody();
        $body = (string) ob_get_clean();
        $this->assertStringContainsString('gelita-daftar-rekaman-', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringStartsWith('PK', (string) $body, 'paket XLSX (zip)');

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $body);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        unlink($path);

        foreach (['kode_berkas', 'teks_en', 'audio_id', 'penutup-05', 'Mbah Kedu', 'belum ada'] as $text) {
            $this->assertStringContainsString($text, $sheet);
        }

        $this->assertSame(1, $this->sqlite->table('audit_logs')->where('action', 'narration_list_download')->countAllResults());
        $this->assertSame([], glob(WRITEPATH . 'exports/daftar-rekaman-narasi-*') ?: [], 'berkas sementara dihapus');
    }

    public function testBulkApprovalFromThePanel(): void
    {
        $narration = $this->audio('audio.narasi.id.peta-01', 'id', 'draft');
        $mission   = $this->audio('audio.mission.tmg-1.id', 'id', 'draft', 'mission.tmg-1');

        $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/setujui', ['locale' => 'id'] + self::CSRF)
            ->assertRedirectTo(site_url('admin/konten/narasi'));

        $this->assertSame('approved', $this->approvalOf($narration));
        $this->assertSame('draft', $this->approvalOf($mission));
        $this->assertSame('7', (string) $this->sqlite->table('audio_assets')->where('id', $narration)->get()->getRow('approved_by'));
        $this->assertSame(1, $this->sqlite->table('audit_logs')->where('action', 'audio_approve_bulk')->countAllResults());

        // Dari halaman Audio kembali ke halaman Audio; bahasa tak dikenal ditolak
        $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/setujui', ['locale' => 'en', 'back' => 'audio'] + self::CSRF)
            ->assertRedirectTo(site_url('admin/media/audio'));
        $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/setujui', ['locale' => 'fr'] + self::CSRF)
            ->assertSessionHas('error', 'Bahasa tidak dikenal.');
    }

    /**
     * "Batalkan semua rekaman" for one language (owner, 17 October 2026): lines
     * and clues lose their recording, imported narration files are deleted,
     * other languages and other audio stay.
     */
    public function testCancelAllRecordingsOfOneLanguage(): void
    {
        $nodeId   = $this->seedHuntNode($this->sqlite);
        $intro    = $this->audio('audio.narasi.id.intro-01', 'id', 'approved');
        $clue     = $this->audio('audio.narasi.id.petunjuk-tmg-4-01', 'id', 'draft', 'hunt_clue');
        $loose    = $this->audio('audio.narasi.id.peta-01', 'id', 'draft');   // imported, not linked yet
        $english  = $this->audio('audio.narasi.en.intro-01', 'en', 'approved');
        $mission  = $this->audio('audio.mission.tmg-1.id', 'id', 'approved', 'mission.tmg-1');
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 1])
            ->update(['audio_id_asset_id' => $intro, 'audio_en_asset_id' => $english]);
        // Mission audio picked by hand for another line: unlinked only, it stays approved for its mission card
        $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 2])
            ->update(['audio_id_asset_id' => $mission]);
        $this->sqlite->table('challenge_items')->where('item_key', 'tmg-4-x01')->update(['audio_prompt_id' => $clue]);

        // Real files (in the test upload folder): only the imported narration files of this language may go
        $file = FCPATH . MediaStore::uploadDir() . 'audio.narasi.id.intro-01.mp3';
        $keep = FCPATH . MediaStore::uploadDir() . 'audio.narasi.en.intro-01.mp3';
        is_dir(dirname($file)) || mkdir(dirname($file), 0775, true);
        file_put_contents($file, 'ID');
        file_put_contents($keep, 'EN');

        try {
            $html = html_entity_decode((string) $this->asAdmin()->call(Method::GET, 'admin/konten/narasi')->getBody(), ENT_QUOTES | ENT_HTML5);
            $this->assertStringContainsString('Batalkan semua rekaman bahasa Indonesia (4)', $html);
            $this->assertStringContainsString('Batalkan semua rekaman bahasa Inggris (1)', $html);

            $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/batalkan', ['locale' => 'id'] + self::CSRF)
                ->assertRedirectTo(site_url('admin/konten/narasi'));

            $this->assertSame('4 rekaman bahasa Indonesia dibatalkan dan 1 berkas lama dihapus. Semua baris kini berstatus belum ada; unggah rekaman baru dengan nama berkas yang sama.', session('message'));
            $this->assertFileDoesNotExist($file);
            $this->assertFileExists($keep, 'other language kept');
        } finally {
            @unlink($file);
            @unlink($keep);
        }

        $line = $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 1])->get()->getRowArray();
        $this->assertNull($line['audio_id_asset_id']);
        $this->assertNull($this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 2])->get()->getRow('audio_id_asset_id'));
        $this->assertSame($english, (int) $line['audio_en_asset_id']);
        $this->assertNull($this->sqlite->table('challenge_items')->where('item_key', 'tmg-4-x01')->get()->getRow('audio_prompt_id'));

        foreach ([$intro, $clue, $loose] as $id) {
            $this->assertSame('rejected', $this->approvalOf($id), 'bulk approval skips cancelled recordings');
        }

        $this->assertSame('approved', $this->approvalOf($english));
        $this->assertSame('approved', $this->approvalOf($mission), 'mission narration is not a script recording');
        $this->assertSame(0, $this->sqlite->table('media_assets')->like('asset_key', 'audio.narasi.id.', 'after')->where('is_active', 1)->countAllResults());
        $this->assertSame(1, (int) $this->sqlite->table('media_assets')->where('asset_key', 'audio.mission.tmg-1.id')->get()->getRow('is_active'));

        $audit = $this->sqlite->table('audit_logs')->where('action', 'narration_cancel_bulk')->get()->getRowArray();
        $this->assertSame(['locale' => 'id', 'count' => 4, 'files_deleted' => 1], array_slice(json_decode((string) $audit['metadata_json'], true), 0, 3));

        // Nothing left: no button, a second run changes nothing, unknown language rejected
        $html = html_entity_decode((string) $this->asAdmin()->call(Method::GET, 'admin/konten/narasi')->getBody(), ENT_QUOTES | ENT_HTML5);
        $this->assertStringNotContainsString('Batalkan semua rekaman bahasa Indonesia', $html);
        $this->assertStringContainsString('0/116 disetujui, 0 menunggu persetujuan, 116 belum ada', $html);
        $this->assertStringContainsString('admin/konten/node/' . $nodeId, $html);
        $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/batalkan', ['locale' => 'id'] + self::CSRF)
            ->assertSessionHas('message', 'Tidak ada rekaman bahasa Indonesia yang perlu dibatalkan.');
        $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/batalkan', ['locale' => 'fr'] + self::CSRF)
            ->assertSessionHas('error', 'Bahasa tidak dikenal.');
    }

    /** The voice-actor script: one compact table, every line in game order, per language. */
    public function testScriptForTheVoiceActors(): void
    {
        $this->seedHuntNode($this->sqlite);
        $pdf = new App\Libraries\NarrationScriptPdf();

        $id = $pdf->html('id');
        $this->assertSame(116, substr_count($id, '<td class="tick">☐</td>'), '112 script lines + 4 clues of tmg-4');
        $this->assertStringContainsString('<td class="code">intro-01</td>', $id);
        $this->assertStringContainsString('Petunjuk Cari Objek · Temanggung (tmg-4) · 4 baris', $id);
        $this->assertStringContainsString('<b>Mbah Kedu</b>', $id);
        $this->assertStringContainsString('Teks bahasa Indonesia', $id);
        $this->assertStringNotContainsString('Teks bahasa Inggris', $id);
        $this->assertStringContainsString('116 baris (', $id);
        $this->assertLessThan(strpos($id, '>penutup-01<'), strpos($id, '>intro-01<'), 'game order');

        $intro = $this->sqlite->table('dialogues')->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 1])->get()->getRowArray();
        $this->assertStringContainsString(esc((string) $intro['text_en']), $pdf->html('en'));
        $this->assertStringNotContainsString(esc((string) $intro['text_id']), $pdf->html('en'));

        $both = $pdf->html('semua');
        $this->assertStringContainsString(esc((string) $intro['text_id']), $both);
        $this->assertStringContainsString(esc((string) $intro['text_en']), $both);
        $this->assertSame('id', App\Libraries\NarrationScriptPdf::normalize('xx'));

        // Download from the panel: a real PDF, audited, temporary file removed
        $response = $this->asAdmin()->call(Method::GET, 'admin/konten/narasi/naskah-pdf', ['bahasa' => 'en'])->response();
        $this->assertInstanceOf(CodeIgniter\HTTP\DownloadResponse::class, $response);
        $response->buildHeaders();
        ob_start();
        $response->sendBody();
        $body = (string) ob_get_clean();

        $this->assertStringStartsWith('%PDF', $body);
        $this->assertStringContainsString('gelita-naskah-rekaman-en-', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('application/pdf', $response->getHeaderLine('Content-Type'));
        $this->assertSame('naskah.en', $this->sqlite->table('audit_logs')->where('action', 'narration_script_download')->get()->getRow('target_id'));
        $this->assertSame([], glob(WRITEPATH . 'exports/naskah-rekaman-*') ?: []);

        $html = html_entity_decode((string) $this->asAdmin()->call(Method::GET, 'admin/konten/narasi')->getBody(), ENT_QUOTES | ENT_HTML5);
        $this->assertStringContainsString('admin/konten/narasi/naskah-pdf?bahasa=id', $html);
        $this->assertStringContainsString('admin/konten/narasi/naskah-pdf?bahasa=semua', $html);
    }

    public function testUploadPageExplainsLimitsAndShowsTheReport(): void
    {
        $report = [
            'locale' => 'id', 'source' => 'upload', 'dry_run' => false, 'files' => 2,
            'created' => ['intro-01'], 'replaced' => [], 'unchanged' => [], 'kept' => [],
            'unknown' => [['file' => 'intro-1.mp3', 'reason' => 'nama berkas tidak sesuai pola nama rekaman (lihat Unduh daftar rekaman)', 'suggestion' => 'intro-01.mp3']],
            'failed' => [], 'missing' => ['intro-02'],
        ];

        $result = $this->asAdmin(['narration_report' => $report])->call(Method::GET, 'admin/konten/narasi/unggah');
        $result->assertOK();
        $html = html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5);

        $this->assertStringContainsString('name="files[]" multiple accept="audio/*"', $html);
        $this->assertStringContainsString('upload_max_filesize', $html);
        $this->assertStringContainsString('max_file_uploads', $html);
        $this->assertStringContainsString('Config\Gelita::$maxUploadBytes', $html);
        $this->assertStringContainsString('data-max-files="' . (int) ini_get('max_file_uploads') . '"', $html);
        $this->assertStringContainsString('<code>intro-01.mp3</code>', $html, 'saran nama');
        $this->assertStringContainsString('1 baris naskah belum punya rekaman bahasa Indonesia', $html);
        $this->assertStringContainsString('admin/konten/narasi/impor-folder', $html);
    }

    public function testUploadWithoutFilesOrLocaleIsRejected(): void
    {
        $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/unggah', ['locale' => 'xx'] + self::CSRF)
            ->assertSessionHas('error', 'Pilih bahasa rekaman.');
        $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/unggah', ['locale' => 'id'] + self::CSRF)
            ->assertSessionHas('error');
    }

    public function testFolderImportFromThePanelIsAudited(): void
    {
        $dir = FCPATH . 'assets/audio/narasi/en/';
        $new = ! is_dir($dir);
        // A recording already in the folder (e.g. a voice-over take) is used as it is
        // and never overwritten; without one, the test writes a tiny WAV and removes it.
        $own = (glob($dir . 'penutup-02.*') ?: []) === [];

        if ($new) {
            mkdir($dir, 0775, true);
        }

        if ($own) {
            file_put_contents($dir . 'penutup-02.wav', 'RIFF' . pack('V', 44) . 'WAVEfmt ' . pack('VvvVVvv', 16, 1, 1, 8000, 8000, 1, 8) . 'data' . pack('V', 8) . str_repeat("\x80", 8));
        }

        try {
            $result = $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/impor-folder', ['locale' => 'en'] + self::CSRF);
            $result->assertRedirectTo(site_url('admin/konten/narasi/unggah'));
            // Folder konvensi berisi rekaman lain: cukup periksa berkas penutup-02
            $this->assertStringStartsWith('Narasi EN: ', (string) session('message'));
            $this->assertSame(1, $this->sqlite->table('media_assets')->where('asset_key', 'audio.narasi.en.penutup-02')->countAllResults());
        } finally {
            if ($own) {
                unlink($dir . 'penutup-02.wav');
            }

            if ($new) {
                rmdir($dir);
                @rmdir(dirname($dir));
            }
        }

        $audit = $this->sqlite->table('audit_logs')->where('action', 'narration_import')->get()->getRowArray();
        $this->assertSame('narasi.en', $audit['target_id']);
        $this->assertSame('folder', json_decode((string) $audit['metadata_json'], true)['via']);
    }

    public function testChecklistMarksMissingAssetsWithSizesAndLinks(): void
    {
        $result = $this->asAdmin()->call(Method::GET, 'admin/media/kelengkapan');
        $result->assertOK();
        $html = html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5);

        foreach (['ui.logo-hero', 'ui.btn-start.en', 'bg.loading', 'map.kedu', 'char.kedu.worried.1…24', 'Peta wilayah Magelang', 'map.region.magelang', 'Rekaman narasi EN', 'public/assets/audio/music/map.mp3', 'Efek jawaban benar'] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        $this->assertStringContainsString('1600 × 600 px', $html);
        $this->assertStringContainsString('700 × 900 px', $html);
        $this->assertStringContainsString('1400 × 900 px', $html, 'peta wilayah');
        $this->assertStringContainsString('0/112 disetujui, 0 menunggu persetujuan, 112 belum ada', $html);
        $this->assertStringContainsString('admin/media?asset_key=ui.logo-hero', $html);
        $this->assertStringContainsString('admin/konten/level/2', $html);
    }

    // -------------------------------------------------------------- bantuan

    /** @param array<string, mixed> $extra */
    private function asAdmin(array $extra = []): self
    {
        return $this->withSession(['staff_id' => 7, 'staff_role' => 'admin', 'gelita_csrf' => self::CSRF_HASH] + $extra);
    }

    private function audio(string $key, string $locale, string $status, ?string $context = null): int
    {
        $this->sqlite->table('media_assets')->insert(['asset_key' => $key, 'asset_type' => 'audio', 'storage_path' => MediaStore::uploadDir() . $key . '.mp3', 'mime_type' => 'audio/mpeg', 'is_active' => 1]);
        $this->sqlite->table('audio_assets')->insert([
            'media_asset_id' => $this->sqlite->insertID(), 'locale' => $locale, 'context_code' => $context ?? 'intro',
            'transcript' => 'Uji', 'approval_status' => $status,
        ]);

        return (int) $this->sqlite->insertID();
    }

    private function approvalOf(int $audioId): string
    {
        return (string) $this->sqlite->table('audio_assets')->where('id', $audioId)->get()->getRow('approval_status');
    }

    /** null = hapus suntikan agar test lain kembali membaca database. */
    private function injectStaff(?string $role): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($role === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 7, 'username' => 'admin_uji', 'email' => null, 'role' => $role, 'display_name' => 'Admin Uji',
                'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
