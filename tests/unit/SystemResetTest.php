<?php

use App\Commands\SystemReset;
use App\Libraries\MediaStore;
use App\Models\MediaAssetModel;
use App\Services\SystemResetService;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Reset total untuk penelitian kelas 5 (`php spark gelita:reset`, Dok 1 §7)
 * di atas skema lengkap SQLite: data uji coba dan pengaturan dikosongkan,
 * 12 tantangan dan nilai bawaan dibangun ulang, Pustaka/cerita/level/media
 * tidak tersentuh, sekolah belum terverifikasi dan (opsional) akun guru
 * dibuang, dan setiap reset tercatat di audit_logs.
 *
 * @internal
 */
final class SystemResetTest extends CIUnitTestCase
{
    use ResetTables;

    private BaseConnection $conn;
    private string $exportsDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn       = $this->createResetTables();
        $this->exportsDir = sys_get_temp_dir() . '/gelita-reset-' . bin2hex(random_bytes(4));
        mkdir($this->exportsDir);
        file_put_contents($this->exportsDir . '/ekspor-lama.xlsx', 'PK');
        file_put_contents($this->exportsDir . '/index.html', '');

        $this->seedTrial();

        foreach (['contentRepository', 'schoolDirectory', 'eventService'] as $name) {
            Services::resetSingle($name);
        }
    }

    protected function tearDown(): void
    {
        CLI::resetInputOutput();
        array_map('unlink', glob($this->exportsDir . '/*') ?: []);
        rmdir($this->exportsDir);

        $this->dropResetTables();
        parent::tearDown();
    }

    public function testDryRunShowsThePlanAndChangesNothing(): void
    {
        $before = $this->counts();

        [$code, $output] = $this->command(['dry-run' => null]);

        $this->assertSame(EXIT_SUCCESS, $code);
        $this->assertSame($before, $this->counts(), '--dry-run tidak menulis apa pun');
        $this->assertStringContainsString('--dry-run: tidak ada yang diubah.', $output);
        $this->assertMatchesRegularExpression('/participants\s*\|\s*2\b/', $output);
        $this->assertStringContainsString('Akan DIBANGUN ULANG: 12 tantangan → 12 tantangan', $output);
        $this->assertStringContainsString('Akan DIHAPUS: 2 sekolah belum terverifikasi', $output);

        $plan = (new SystemResetService($this->conn, $this->exportsDir))->plan();
        $this->assertSame(array_values(SystemResetService::GROUP_A), array_keys($plan['clear']));
        $this->assertSame(['admin' => 1, 'guru' => 2], $plan['staff']);
        $this->assertSame(12, $plan['nodes_after']);
    }

    /** `--bank=JALUR` dan `--bank JALUR` sama-sama terbaca (parser CodeIgniter tidak memecah `=`). */
    public function testBankOptionAcceptsBothForms(): void
    {
        $bank = ROOTPATH . 'docs/pustaka/gelita-pustaka.xlsx';   // berkas lain yang ada, bukan bawaan

        foreach ([['bank=' . $bank => null], ['bank' => $bank]] as $params) {
            [$code, $output] = $this->command(['dry-run' => null] + $params);

            $this->assertSame(EXIT_SUCCESS, $code);
            $this->assertStringContainsString('Bank soal: ' . $bank, $output);
        }

        $before = $this->counts();

        foreach ([['bank=/tidak/ada/bank.xlsx' => null], ['bank' => '/tidak/ada/bank.xlsx']] as $params) {
            [$code, $output] = $this->command(['ya' => null] + $params);

            $this->assertSame(EXIT_ERROR, $code, json_encode($params));
            $this->assertStringContainsString('Workbook --bank tidak ditemukan: /tidak/ada/bank.xlsx', $output);
        }

        $this->assertSame($before, $this->counts(), 'jalur salah: tidak ada yang diubah');
    }

    public function testWithoutTheConfirmationWordNothingHappens(): void
    {
        $before = $this->counts();

        [$code, $output] = $this->command([], ['reset']);

        $this->assertSame(EXIT_ERROR, $code);
        $this->assertSame($before, $this->counts());
        $this->assertStringContainsString('Ketik RESET-KELAS5 untuk melanjutkan', $output);
        $this->assertStringContainsString('Konfirmasi tidak cocok', $output);
        $this->assertFileExists($this->exportsDir . '/ekspor-lama.xlsx');
    }

    public function testGuardsStopTheResetBeforeAnythingIsWritten(): void
    {
        // Kelas aktif dalam 10 menit terakhir → tolak tanpa --paksa
        $this->conn->table('game_sessions')->where('id', 1)->update(['status' => 'active', 'last_active_at' => date('Y-m-d H:i:s', time() - 120)]);
        $before = $this->counts();

        [$code, $output] = $this->command(['ya' => null]);
        $this->assertSame(EXIT_ERROR, $code);
        $this->assertStringContainsString('--paksa', $output);
        $this->assertSame($before, $this->counts());

        // Struktur database belum lengkap → berhenti
        $this->conn->table('migrations')->where('version', SystemResetService::REQUIRED_MIGRATION)->delete();
        $before = $this->counts();

        [$code, $output] = $this->command(['ya' => null, 'paksa' => null]);
        $this->assertSame(EXIT_ERROR, $code);
        $this->assertStringContainsString('php spark migrate', $output);
        $this->assertSame($before, $this->counts());
    }

    public function testResetEmptiesTrialDataAndKeepsStoryLibraryAndMedia(): void
    {
        $kept = $this->keptHashes();

        $result = $this->service()->run(['tanpa_bank' => true, 'tanpa_mockup' => true]);


        foreach (SystemResetService::GROUP_A as $table) {
            $expected = $table === 'audit_logs' ? 1 : 0;
            $this->assertSame($expected, $this->conn->table($table)->countAllResults(), "{$table} dikosongkan");
        }

        $this->assertSame($kept, $this->keptHashes(), 'levels, dialogues, library_*, media_assets, audio_assets tidak berubah');
        $this->assertFileDoesNotExist($this->exportsDir . '/ekspor-lama.xlsx', 'berkas ekspor lama ikut dihapus');
        $this->assertFileExists($this->exportsDir . '/index.html');

        // Konten tantangan dan pengaturan dibangun ulang dari seeder
        $this->assertSame(12, $result['nodes']);
        $this->assertSame(12, $this->conn->table('challenge_nodes')->where('is_active', 1)->countAllResults());
        $this->assertSame(
            [[1, 'puzzle'], [2, 'rumpang'], [3, 'boleh'], [4, 'cari']],
            array_map(static fn (array $r): array => [(int) $r['sequence'], $r['engine_type']], $this->conn->table('challenge_nodes')->where('level_id', 2)->orderBy('sequence')->get()->getResultArray()),
        );
        $this->assertSame(0, $this->conn->table('challenge_items')->countAllResults(), '--tanpa-bank');
        $this->assertSame(0, $this->conn->table('hints')->countAllResults());
        $this->assertSame([['STUDI-KEDU-KELAS5-2026', 'active']], array_map('array_values', $this->conn->table('research_studies')->select('code, status')->get()->getResultArray()));
        $this->assertSame([['GELITA-KELAS5-2026', 'bintang-1', '1']], array_map(
            static fn (array $r): array => [$r['release_code'], $r['scoring_version'], (string) $r['is_active']],
            $this->conn->table('game_releases')->get()->getResultArray(),
        ));
        $this->assertSame(['budaya', 'literasi', 'sikap'], array_column($this->conn->table('learning_indicators')->orderBy('code')->get()->getResultArray(), 'code'));
        $this->assertSame('skipped', $result['bank']['status']);
        $this->assertSame('skipped', $result['mockups']['status']);
        $this->assertSame('GLT-000001', $result['next_code']);

        // Sekolah: resmi tetap, ketikan uji coba (dan aliasnya) hilang, rujukan staf di-NULL-kan
        $this->assertSame(['SD Negeri Resmi 1', 'SD Negeri Resmi 2'], array_column($this->conn->table('schools')->orderBy('id')->get()->getResultArray(), 'name'));
        $this->assertSame(2, $result['schools_removed']);
        $this->assertNull($this->staff('guru_ketik')['school_id']);
        $this->assertSame(1, (int) $this->staff('guru_resmi')['school_id']);

        // Akun staf tetap, kunci login dibuka
        $this->assertSame(['admin_uji', 'guru_ketik', 'guru_resmi'], array_column($this->conn->table('staff_users')->orderBy('username')->get()->getResultArray(), 'username'));
        $this->assertSame([0, null], [(int) $this->staff('admin_uji')['failed_login_count'], $this->staff('admin_uji')['locked_until']]);
        $this->assertSame(0, $result['teachers_removed']);

        // Satu baris audit
        $audit = $this->conn->table('audit_logs')->get()->getRowArray();
        $this->assertSame(['system_reset', null, 'system', 'kelas5'], [$audit['action'], $audit['staff_user_id'], $audit['target_type'], $audit['target_id']]);
        $meta = json_decode((string) $audit['metadata_json'], true);
        $this->assertSame(2, $meta['cleared']['participants']);
        $this->assertSame(12, $meta['cleared']['challenge_nodes']);
        $this->assertTrue($meta['options']['tanpa_bank']);
        $this->assertArrayHasKey('host', $meta);
        $this->assertArrayHasKey('bank_sha256', $meta);

        // Aman diulang
        $again = $this->service()->run(['tanpa_bank' => true, 'tanpa_mockup' => true]);
        $this->assertSame(12, $again['nodes']);
        $this->assertSame(1, $this->conn->table('research_studies')->countAllResults());
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'system_reset')->countAllResults(), 'audit lama ikut dikosongkan');
        $this->assertSame($kept, $this->keptHashes());
    }

    public function testRemoveTeachersKeepsEveryAdmin(): void
    {
        $result = $this->service()->run(['tanpa_bank' => true, 'tanpa_mockup' => true, 'hapus_guru' => true]);

        $this->assertSame(2, $result['teachers_removed']);
        $this->assertSame([['admin_uji', 'admin']], array_map('array_values', $this->conn->table('staff_users')->select('username, role')->get()->getResultArray()));
    }

    /**
     * Jalur bawaan `gelita:reset`: workbook bank soal kelas 5 di repositori
     * (Dok 2 §7) diimpor utuh, tanpa menyentuh Pustaka.
     */
    public function testResetImportsTheGradeFiveWorkbook(): void
    {
        $library = $this->keptHashes();
        $result  = $this->service()->run(['tanpa_mockup' => true]);
        $bank    = ROOTPATH . SystemResetService::DEFAULT_BANK;

        $this->assertSame('imported', $result['bank']['status'], json_encode($result['warnings']));
        $this->assertSame(hash_file('sha256', $bank), $result['bank']['sha256']);
        $this->assertSame(12, $this->conn->table('challenge_nodes')->where('is_active', 1)->countAllResults());
        $this->assertSame(1, $this->conn->table('research_studies')->where('status', 'active')->countAllResults());
        $this->assertSame(1, $this->conn->table('research_studies')->countAllResults());

        // Bank draft 2: 147 active items (129 scored + 18 decoys) and 4 retired, 9 passages, 36 hints
        $this->assertSame(147, $this->conn->table('challenge_items')->where('is_active', 1)->countAllResults());
        $this->assertSame(4, $this->conn->table('challenge_items')->where('is_active', 0)->countAllResults());
        $this->assertSame(18, $this->conn->table('challenge_items')->where('scorable', 0)->countAllResults());
        $this->assertSame(9, $this->conn->table('reading_passages')->countAllResults());
        $this->assertSame(36, $this->conn->table('hints')->countAllResults());
        $this->assertEqualsCanonicalizing(['needs_verification', 'verified'], array_column($this->conn->table('challenge_items')->select('review_status')->distinct()->get()->getResultArray(), 'review_status'));

        $rumpang = json_decode((string) $this->node('temanggung', 2)['config_json'], true);
        $this->assertSame('Gunung Kembar', $rumpang['variants']['a']['title_id'] ?? null);
        $this->assertTrue($rumpang['as_reading'] && $rumpang['use_word_bank']);
        $this->assertSame(['a', 'b', 'c'], array_keys($rumpang['variants']));
        $this->assertSame('Situs Liyangan', json_decode((string) $this->node('temanggung', 3)['config_json'], true)['variants']['a']['title_id'] ?? null);
        $this->assertSame(
            [['text_id' => 'enam', 'text_en' => 'six'], ['text_id' => 'arca', 'text_en' => 'statues']],
            json_decode((string) $this->node('magelang', 2)['config_json'], true)['variants']['a']['distractors'] ?? null,
        );

        $statement = $this->conn->table('challenge_items')->where('item_key', 'wnb-3-c06')->get()->getRowArray();
        $this->assertSame(['verdict' => 'salah'], json_decode((string) $statement['answer_key_json'], true));
        $this->assertSame('menentukan_solusi', json_decode((string) $statement['config_json'], true)['skill'] ?? null);
        $this->assertSame('C5', $statement['bloom_level']);

        // 66 slot gambar tantangan (45 + 21 for Susun Gambar d–j), masing-masing dengan nama dan isi gambar yang diharapkan
        $slots = $this->conn->table('media_assets')->like('asset_key', 'challenge.', 'after')
            ->where('label IS NOT NULL', null, false)->where('description IS NOT NULL', null, false)->countAllResults();
        $this->assertSame(66, $slots);
        $this->assertSame('Kuda kepang bambu', $this->conn->table('media_assets')->where('asset_key', 'challenge.item.tmg-4-03')->get()->getRow('label'));

        $kept = $this->keptHashes();
        unset($library['media_assets'], $kept['media_assets']);   // slot gambar tantangan baru dibuat impor
        $this->assertSame($library, $kept, 'workbook kelas 5 tanpa Pustaka: Pustaka, cerita, dan level tidak tersentuh');
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'system_reset')->countAllResults());
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'content_import')->countAllResults());
        $this->assertNotSame([], $result['findings'], 'verifikasi konten dijalankan');
        $this->assertSame(0, $result['errors'], json_encode(array_values(array_filter($result['findings'], static fn (array $f): bool => $f['level'] === 'error'))));
    }

    /**
     * Langkah 8 (Dok 2 §10): ke-66 slot gambar tantangan langsung berisi
     * gambar contoh; reset berikutnya tidak membuatnya ulang dan gambar asli
     * tetap.
     */
    public function testResetFillsEveryChallengeSlotWithMockups(): void
    {
        $dir = MediaStore::uploadDir() . 'mockup-reset-' . bin2hex(random_bytes(4)) . '/';

        try {
            $service = new SystemResetService($this->conn, $this->exportsDir, $dir);
            $result  = $service->run();

            $this->assertSame('done', $result['mockups']['status']);
            $this->assertSame(66, $result['mockups']['count']);
            $this->assertSame([], $result['mockups']['failed']);
            $this->assertSame(66, model(MediaAssetModel::class)->placeholderCount());
            $this->assertSame(0, $result['errors'], 'mockup hanya peringatan pada verifikasi biasa');
            $this->assertStringContainsString('66 gambar masih gambar contoh', implode(' ', array_column($result['findings'], 'message')));

            // Satu slot diganti gambar asli (seperti unggahan admin)
            $this->conn->table('media_assets')->where('asset_key', 'challenge.wnb-4.scene')->update(['is_placeholder' => 0, 'credit' => null]);

            $again = $service->run();
            $this->assertSame(0, $again['mockups']['count']);
            $this->assertSame(65, $again['mockups']['existing']);
            $this->assertSame(1, $again['mockups']['kept']);
            $this->assertSame('0', (string) $this->conn->table('media_assets')->where('asset_key', 'challenge.wnb-4.scene')->get()->getRow('is_placeholder'));
        } finally {
            array_map('unlink', glob(FCPATH . $dir . '*') ?: []);

            if (is_dir(FCPATH . $dir)) {
                rmdir(FCPATH . $dir);
            }
        }
    }

    public function testMissingWorkbookOutsideDevelopmentOnlyWarns(): void
    {
        $result = $this->service()->run(['bank' => '/tidak/ada/bank.xlsx', 'tanpa_mockup' => true]);

        $this->assertSame('missing', $result['bank']['status']);
        $this->assertStringContainsString('tidak ditemukan', implode(' ', $result['warnings']));
        $this->assertSame(12, $result['nodes']);
    }

    // ------------------------------------------------------------ bantu

    private function service(): SystemResetService
    {
        return new SystemResetService($this->conn, $this->exportsDir);
    }

    /**
     * @param array<string, mixed> $params
     * @param list<string>         $inputs jawaban prompt
     *
     * @return array{0: int, 1: string}
     */
    private function command(array $params, array $inputs = []): array
    {
        $io = new MockInputOutput();
        $io->setInputs($inputs);
        CLI::setInputOutput($io);

        try {
            $code = (new SystemReset(Services::logger(), Services::commands()))->run($params);
        } finally {
            CLI::resetInputOutput();
        }

        return [(int) $code, $io->getOutput()];
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        $out = [];

        foreach (array_keys(self::$resetSchema) as $table) {
            $out[$table] = $this->conn->table($table)->countAllResults();
        }

        return $out;
    }

    /** @return array<string, string> isi tabel kelompok E */
    private function keptHashes(): array
    {
        $out = [];

        foreach (SystemResetService::KEPT as $table) {
            $out[$table] = hash('sha256', json_encode($this->conn->table($table)->orderBy('id')->get()->getResultArray()));
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private function staff(string $username): array
    {
        return $this->conn->table('staff_users')->where('username', $username)->get()->getRowArray();
    }

    /** @return array<string, mixed> */
    private function node(string $levelCode, int $sequence): array
    {
        $levelId = (int) $this->conn->table('levels')->where('code', $levelCode)->get()->getRow('id');

        return $this->conn->table('challenge_nodes')->where('level_id', $levelId)->where('sequence', $sequence)->get()->getRowArray();
    }

    /**
     * Keadaan sesudah uji coba: 3 wilayah dengan 4 tantangan, cerita,
     * Pustaka, media, siswa dan jejak bermainnya, pengaturan lama, sekolah
     * resmi + ketikan, satu admin dan dua guru.
     */
    private function seedTrial(): void
    {
        $db = $this->conn;

        foreach (['2026-01-01-002800', SystemResetService::REQUIRED_MIGRATION] as $i => $version) {
            $db->table('migrations')->insert(['version' => $version, 'class' => 'M' . $i, 'group' => 'default', 'namespace' => 'App', 'time' => 1, 'batch' => 1]);
        }

        foreach (['temanggung' => 'mudah', 'magelang' => 'sedang', 'wonosobo' => 'sulit'] as $code => $difficulty) {
            $db->table('levels')->insert([
                'sequence' => $db->table('levels')->countAllResults() + 1, 'code' => $code, 'name_id' => ucfirst($code), 'name_en' => ucfirst($code),
                'difficulty' => $difficulty, 'focus_id' => 'Fokus', 'focus_en' => 'Focus',
            ]);
        }

        $db->table('media_assets')->insertBatch([
            ['asset_key' => 'library.tmg.candi', 'asset_type' => 'image', 'storage_path' => 'assets/img/candi.webp', 'mime_type' => 'image/webp'],
            ['asset_key' => 'audio.narasi.id.intro-01', 'asset_type' => 'audio', 'storage_path' => 'assets/audio/intro-01.mp3', 'mime_type' => 'audio/mpeg'],
        ]);
        $db->table('audio_assets')->insert(['media_asset_id' => 2, 'locale' => 'id', 'context_code' => 'intro', 'transcript' => 'Selamat datang.', 'approval_status' => 'approved', 'approved_by' => 1]);
        $db->table('dialogues')->insertBatch([
            ['level_id' => null, 'context_code' => 'intro', 'sequence' => 1, 'character_code' => 'jaka', 'text_id' => 'Halo!', 'text_en' => 'Hello!'],
            ['level_id' => 1, 'context_code' => 'kenal', 'sequence' => 1, 'character_code' => 'mbah_kedu', 'text_id' => 'Ini Temanggung.', 'text_en' => 'This is Temanggung.'],
        ]);
        $db->table('library_pages')->insert(['level_id' => 1, 'sequence' => 1, 'title_id' => 'Candi', 'title_en' => 'Temple', 'body_id' => 'Isi.', 'body_en' => 'Body.']);
        $db->table('library_media')->insert(['library_page_id' => 1, 'sequence' => 1, 'media_kind' => 'image', 'media_asset_id' => 1]);

        // Pengaturan lama
        $db->table('learning_indicators')->insert(['code' => 'literasi', 'name_id' => 'Literasi lama', 'name_en' => 'Old literacy']);
        $db->table('research_studies')->insert(['code' => 'STUDI-UJI-COBA', 'name' => 'Uji coba', 'status' => 'active']);
        $db->table('game_releases')->insert(['release_code' => 'GELITA-1.0', 'app_version' => '1.0', 'content_version' => '1', 'asset_version' => '1', 'scoring_version' => '1.0', 'is_active' => 1]);

        // 4 tantangan uji coba per wilayah, isinya sudah disunting
        for ($level = 1; $level <= 3; $level++) {
            foreach (['puzzle', 'rumpang', 'boleh', 'cari'] as $i => $engine) {
                $db->table('challenge_nodes')->insert(['level_id' => $level, 'sequence' => $i + 1, 'engine_type' => $engine, 'title_id' => 'Uji coba', 'title_en' => 'Trial']);
            }
        }

        $db->table('challenge_items')->insert(['challenge_node_id' => 1, 'item_key' => 'tmg-1-a01', 'interaction_type' => 'puzzle_arrange']);
        $db->table('hints')->insert(['challenge_node_id' => 1, 'sequence' => 1, 'text_id' => 'Petunjuk', 'text_en' => 'Hint']);
        $db->table('reading_passages')->insert(['level_id' => 1, 'passage_key' => 'tmg-3-a', 'body_id' => 'Bacaan', 'body_en' => 'Reading']);

        // Sekolah: dua resmi, satu ketikan uji coba + aliasnya
        $db->table('schools')->insertBatch([
            ['code' => '20307000', 'name' => 'SD Negeri Resmi 1', 'stage' => 'sd', 'province_code' => '33', 'is_verified' => 1, 'merged_into_id' => null],
            ['code' => '20307001', 'name' => 'SD Negeri Resmi 2', 'stage' => 'sd', 'province_code' => '33', 'is_verified' => 1, 'merged_into_id' => null],
            ['code' => null, 'name' => 'sd negri resmi 1', 'stage' => null, 'province_code' => '33', 'is_verified' => 0, 'merged_into_id' => null],
            ['code' => null, 'name' => 'SDN resmi satu', 'stage' => null, 'province_code' => '33', 'is_verified' => 0, 'merged_into_id' => 3],
        ]);

        $db->table('staff_users')->insertBatch([
            ['username' => 'admin_uji', 'password_hash' => 'x', 'role' => 'admin', 'display_name' => 'Admin', 'school_id' => null, 'failed_login_count' => 4, 'locked_until' => '2030-01-01 00:00:00'],
            ['username' => 'guru_ketik', 'password_hash' => 'x', 'role' => 'guru', 'display_name' => 'Guru 1', 'school_id' => 3, 'failed_login_count' => 0, 'locked_until' => null],
            ['username' => 'guru_resmi', 'password_hash' => 'x', 'role' => 'guru', 'display_name' => 'Guru 2', 'school_id' => 1, 'failed_login_count' => 0, 'locked_until' => null],
        ]);

        // Siswa uji coba dan jejak bermainnya
        $db->table('participants')->insertBatch([
            ['participant_code' => 'GLT-000001', 'username' => 'uji1', 'password_hash' => 'x', 'school_id' => 3],
            ['participant_code' => 'GLT-000002', 'username' => 'uji2', 'password_hash' => 'x', 'school_id' => 1],
        ]);
        $db->table('game_sessions')->insert(['session_code' => 'S-1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'status' => 'completed', 'last_active_at' => '2026-09-01 08:00:00']);
        $db->table('session_progress')->insert(['session_id' => 1]);
        $db->table('challenge_attempts')->insert(['session_id' => 1, 'challenge_node_id' => 1, 'selected_item_ids_json' => '[1]', 'status' => 'completed']);
        $db->table('item_responses')->insert(['challenge_attempt_id' => 1, 'challenge_item_id' => 1]);
        $db->table('item_tries')->insert(['challenge_attempt_id' => 1, 'challenge_item_id' => 1, 'try_no' => 1, 'answer_json' => '{}']);
        $db->table('audio_usage_events')->insert(['session_id' => 1, 'audio_asset_id' => 1, 'action' => 'play']);
        $db->table('game_event_logs')->insert(['event_uuid' => 'e-1', 'client_event_id' => 'c-1', 'session_id' => 1, 'participant_id' => 1, 'event_type' => 'session_started', 'occurred_at' => '2026-09-01 08:00:00']);
        $db->table('device_logs')->insert(['session_id' => 1, 'participant_id' => 1, 'via' => 'register']);
        $db->table('participant_feedback')->insert(['participant_id' => 1, 'rating' => 5]);
        $db->table('data_exports')->insert(['requested_by' => 1, 'format' => 'xlsx', 'scope_json' => '{}']);
        $db->table('data_deletion_requests')->insert(['requested_by' => 1, 'scope_json' => '{}']);
        $db->table('audit_logs')->insert(['staff_user_id' => 1, 'action' => 'login']);
        $db->table('ci_sessions')->insert(['id' => 'sesi-guru', 'ip_address' => '127.0.0.1', 'data' => 'x']);
    }
}
