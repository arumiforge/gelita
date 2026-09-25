<?php

use App\Libraries\StorySync;
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
        (new StorySync($this->sqlite))->run();
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

        $this->assertSame(88, substr_count($html, 'Sunting</a>'));
        $this->assertStringContainsString('<code>kenal-wonosobo-04</code>', $html);
        $this->assertStringContainsString('Kenali wilayah · Magelang', $html);
        $this->assertStringContainsString('0/88 disetujui, 1 draft, 87 belum ada', $html);
        $this->assertStringContainsString('1/88 disetujui, 0 draft, 87 belum ada', $html);
        $this->assertStringContainsString('admin/konten/dialog/0?konteks=intro#slide-1', $html);
        $this->assertStringContainsString('audio-preview-sm', $html);
        // Tombol massal hanya untuk bahasa yang punya draft
        $this->assertStringContainsString('Setujui semua narasi draft ID (1)', $html);
        $this->assertStringNotContainsString('Setujui semua narasi draft EN', $html);
        $this->assertStringContainsString('admin/konten/narasi/daftar-rekaman', $html);
    }

    public function testTeacherCannotOpenNarrationPages(): void
    {
        $this->injectStaff('guru');
        $this->expectException(CodeIgniter\Exceptions\PageNotFoundException::class);

        $this->withSession(['staff_id' => 7, 'staff_role' => 'guru'])->call(Method::GET, 'admin/konten/narasi');
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

    public function testUploadPageExplainsLimitsAndShowsTheReport(): void
    {
        $report = [
            'locale' => 'id', 'source' => 'upload', 'dry_run' => false, 'files' => 2,
            'created' => ['intro-01'], 'replaced' => [], 'unchanged' => [], 'kept' => [],
            'unknown' => [['file' => 'intro-1.mp3', 'reason' => 'nama tidak sesuai pola kode berkas naskah', 'suggestion' => 'intro-01.mp3']],
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
        $this->assertStringContainsString('1 baris naskah belum punya rekaman ID', $html);
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

        if (glob($dir . 'penutup-02.*') ?: []) {
            $this->markTestSkipped('Rekaman sungguhan penutup-02 sudah ada di folder narasi EN; uji ini tidak menimpanya.');
        }

        if ($new) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($dir . 'penutup-02.wav', 'RIFF' . pack('V', 44) . 'WAVEfmt ' . pack('VvvVVvv', 16, 1, 1, 8000, 8000, 1, 8) . 'data' . pack('V', 8) . str_repeat("\x80", 8));

        try {
            $result = $this->asAdmin()->call(Method::POST, 'admin/konten/narasi/impor-folder', ['locale' => 'en'] + self::CSRF);
            $result->assertRedirectTo(site_url('admin/konten/narasi/unggah'));
            // Folder konvensi bisa berisi rekaman lain di mesin pengembang: cukup periksa berkas uji ini
            $this->assertStringStartsWith('Narasi EN: ', (string) session('message'));
            $this->assertSame(1, $this->sqlite->table('media_assets')->where('asset_key', 'audio.narasi.en.penutup-02')->countAllResults());
        } finally {
            unlink($dir . 'penutup-02.wav');

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

        foreach (['ui.logo-hero', 'ui.btn-start.en', 'bg.loading', 'map.kedu', 'char.kedu.worried.1…3', 'Peta wilayah Magelang', 'map.region.magelang', 'Rekaman narasi EN', 'public/assets/audio/music/map.mp3', 'Efek jawaban benar'] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        $this->assertStringContainsString('1600 × 600 px', $html);
        $this->assertStringContainsString('700 × 900 px', $html);
        $this->assertStringContainsString('1400 × 900 px', $html, 'peta wilayah');
        $this->assertStringContainsString('0/88 disetujui, 0 draft, 88 belum ada', $html);
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
        $this->sqlite->table('media_assets')->insert(['asset_key' => $key, 'asset_type' => 'audio', 'storage_path' => 'assets/uploads/' . $key . '.mp3', 'mime_type' => 'audio/mpeg', 'is_active' => 1]);
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
                'school_id' => null, 'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
