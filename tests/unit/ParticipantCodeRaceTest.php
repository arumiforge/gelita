<?php

use App\Services\ParticipantCodeService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Database\FieldWorld;

/**
 * Two registrations in one class group at the same moment get different
 * numbers (research revision 10, part 2: 28 §10.2, K11, §19.1
 * `ParticipantCodeRaceTest`; pattern of RegisterWizardTest::
 * testRegistrationCommittedAlongsideGetsItsOwnCode). SQLite has no row locks,
 * so the rival's write lands between this request's read and its UPDATE.
 *
 * @internal
 */
final class ParticipantCodeRaceTest extends CIUnitTestCase
{
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        Services::reset();
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testARegistrationCommittedAlongsideGetsTheNextNumber(): void
    {
        $fs = $this->fieldSession($this->conn, 1, 'GHJKMN');

        Services::injectMock('participantCodes', new class () extends ParticipantCodeService {
            private bool $raced = false;

            protected function counter(int $schoolId, string $rombel): ?array
            {
                $row = parent::counter($schoolId, $rombel);

                if (! $this->raced && $row !== null) {
                    // The rival registration takes this number first
                    $this->raced = true;
                    db_connect()->table('participant_code_counters')->where('id', $row['id'])->update(['next_no' => (int) $row['next_no'] + 1]);
                    db_connect()->table('participants')->insert([
                        'participant_code' => participant_code((int) $row['next_no'], 'S03', 'A'), 'username' => 'siswa_lain', 'password_hash' => 'x',
                    ]);
                }

                return $row;   // stale: still the number the rival took
            }
        });

        $result = service('sessionService')->registerAndStart([
            'username' => 'sekar_ayu', 'password' => 'Kopi-Kedu7!', 'assent' => true,
            'field_session' => model(\App\Models\FieldSessionModel::class)->find($fs['id']),
        ]);

        $codes = array_column($this->conn->table('participants')->where('id >', 1)->orderBy('id')->get()->getResultArray(), 'participant_code', 'username');
        $this->assertSame(['siswa_lain' => 'S03-A02', 'sekar_ayu' => 'S03-A03'], $codes);
        $this->assertSame('S03-A03', $result['participant']->participant_code);
        $this->assertSame(4, (int) $this->conn->table('participant_code_counters')->get()->getRow('next_no'));
    }

    public function testCodesNeverRepeatAcrossStudies(): void
    {
        $codes = service('participantCodes');

        $this->assertSame('S03-A02', $codes->next(1, 'A', 'S03'));
        // A later test at the same school continues the numbers (28 §10.2 no. 4)
        $this->conn->table('research_studies')->update(['status' => 'closed']);
        $this->conn->table('research_studies')->insert(['code' => 'KG3B', 'name' => 'Uji berikutnya', 'status' => 'active', 'field_mode' => 1]);
        $this->assertSame('S03-A03', $codes->next(1, 'A', 'S03'));
        $this->assertSame('S03-B01', $codes->next(1, 'B', 'S03'));
    }
}
