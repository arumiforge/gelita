<?php

use App\Database\Migrations\AddConsentAndDataRights;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\ResetTables;

/**
 * Migration 2026-01-01-003800 (research revision 10, part 4: 30 §15,
 * §19.1 `ConsentDataRightsMigrationTest`), run on the schema before it.
 *
 * @internal
 */
final class ConsentDataRightsMigrationTest extends CIUnitTestCase
{
    use ResetTables;

    private const NEW_COLUMNS = [
        'participants'           => ['deactivated_at', 'assent_version', 'corrected_at'],
        'research_studies'       => ['closed_at', 'consent_register_exported_at', 'feedback_names_checked_at', 'identities_replaced_at'],
        'data_deletion_requests' => ['kind', 'participant_code', 'received_at', 'verified_at', 'received_via', 'confirmed_by', 'confirmed_at', 'completed_at', 'checklist_json'],
        'data_exports'           => ['purged_by_request_id'],
    ];

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createResetTables();
        $forge      = Database::forge('tests');
        $forge->dropTable('participant_consents', true);

        foreach (self::NEW_COLUMNS as $table => $columns) {
            $forge->dropColumn($table, $columns);
        }
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testUpAddsTablesAndColumnsAndLeavesRetentionAndSessionsAlone(): void
    {
        $this->conn->table('research_studies')->insert(['code' => 'LAMA', 'name' => 'Lama', 'status' => 'active', 'retention_days' => 1825]);
        $this->conn->table('ci_sessions')->insert(['id' => 'raw', 'ip_address' => '203.0.113.7', 'data' => '']);

        $this->migration()->up();

        $this->assertTrue($this->conn->tableExists('participant_consents'));
        $this->assertEqualsCanonicalizing(
            ['participant_id', 'study_id', 'parent_consent_on', 'recording_consent', 'recording_withdrawn_on', 'risk_items_consent', 'risk_items_withdrawn_on', 'status', 'status_on', 'entered_by', 'updated_by', 'created_at', 'updated_at'],
            $this->conn->getFieldNames('participant_consents'),
        );

        foreach (self::NEW_COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue($this->conn->fieldExists($column, $table), "{$table}.{$column}");
            }
        }

        $this->assertSame(1825, (int) $this->conn->table('research_studies')->get()->getRow('retention_days'));
        $this->assertSame(1, $this->conn->table('ci_sessions')->countAllResults(), 'ci_sessions is not emptied (30 §22 no. 8)');

        // Defaults: consents No, status active (K6)
        $this->conn->table('participants')->insert(['participant_code' => 'S03-A01', 'username' => 'a', 'password_hash' => 'x']);
        $this->conn->table('participant_consents')->insert(['participant_id' => 1, 'study_id' => 1]);
        $row = $this->conn->table('participant_consents')->get()->getRowArray();
        $this->assertSame([0, 0, 'active'], [(int) $row['recording_consent'], (int) $row['risk_items_consent'], $row['status']]);

        $this->migration()->down();

        $this->assertFalse($this->conn->tableExists('participant_consents'));
        $this->assertFalse($this->conn->fieldExists('deactivated_at', 'participants'));
        $this->assertFalse($this->conn->fieldExists('kind', 'data_deletion_requests'));
    }

    private function migration(): AddConsentAndDataRights
    {
        require_once APPPATH . 'Database/Migrations/2026-01-01-003800_AddConsentAndDataRights.php';

        return new AddConsentAndDataRights(Database::forge('tests'));
    }
}
