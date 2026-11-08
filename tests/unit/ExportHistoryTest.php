<?php

use App\Models\AuditLogModel;
use App\Models\DataExportModel;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * "Riwayat unduhan Anda" on /admin/ekspor (research revision 10, part 3:
 * 29 §9): `ketua` sees only their own downloads, with the dictionary
 * version, the number of students, and how often each was downloaded.
 *
 * @internal
 */
final class ExportHistoryTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRawDataWorld();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testKetuaSeesOwnHistoryOnly(): void
    {
        $db = db_connect();
        $db->table('staff_users')->insert(['username' => 'ketua_dua', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua Dua', 'is_active' => 1]);
        $exports = model(DataExportModel::class);

        $mine = (int) $exports->insert(['requested_by' => 2, 'format' => 'csv', 'scope_json' => ['filters' => ['study_id' => 1], 'sheets' => ['peserta']],
            'anonymized' => 1, 'status' => 'done', 'row_count' => 7, 'dictionary_version' => 'kamus-1.0', 'participant_count' => 23,
            'file_sha256' => str_repeat('ab', 32), 'expires_at' => date('Y-m-d H:i:s', time() - 60)], true);
        $exports->insert(['requested_by' => 4, 'format' => 'xlsx', 'scope_json' => ['filters' => ['study_id' => 1]],
            'anonymized' => 1, 'status' => 'done', 'row_count' => 99, 'participant_count' => 987654, 'file_sha256' => str_repeat('cd', 32)], true);

        foreach ([1, 2] as $_) {
            model(AuditLogModel::class)->record('export_download', ['staff_user_id' => 2, 'target_type' => 'data_export', 'target_id' => (string) $mine]);
        }

        Services::resetSingle('security');
        $page = (string) $this->withSession(['staff_id' => 2, 'staff_role' => 'ketua', 'staff_name' => 'Ketua'])->call(Method::GET, 'admin/ekspor')->getBody();

        $this->assertStringContainsString(lang('Admin.exportHistoryMine'), $page);
        $this->assertStringContainsString('kamus-1.0', $page);
        $this->assertMatchesRegularExpression('~<td class="is-num">\s*23\s*</td>~', $page, 'number of students');
        $this->assertStringContainsString(substr(str_repeat('ab', 32), 0, 12), $page);
        $this->assertStringContainsString(lang('Admin.exportFileGone'), $page, 'expired file');
        $this->assertMatchesRegularExpression('~kamus-1\.0\s*</td>\s*<td class="is-num">\s*2\s*</td>~', $page, 'downloaded twice');
        $this->assertStringNotContainsString('987654', $page, 'another staff member\'s download');
        $this->assertStringNotContainsString(substr(str_repeat('cd', 32), 0, 12), $page);
    }
}
