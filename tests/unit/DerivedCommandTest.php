<?php

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Tests\Support\Database\DerivedWorld;

/**
 * Part 9 (35 §13): `gelita:turunan:hitung --cek` writes nothing;
 * `gelita:turunan:hitung` makes a run; `gelita:turunan:ambang --buka` needs a
 * reason and writes the audit `study_thresholds_unlocked`.
 *
 * @internal
 */
final class DerivedCommandTest extends CIUnitTestCase
{
    use DerivedWorld;
    use StreamFilterTrait;

    private BaseConnection $conn;
    private string $code;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createDerivedWorld();
        $this->code = (string) $this->conn->table('research_studies')->get()->getRow('code');
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCheckWritesNothingAndComputeMakesARun(): void
    {
        command('gelita:turunan:hitung --studi=' . $this->code . ' --cek');
        $this->assertStringContainsString('Stale: yes', $this->getStreamFilterBuffer());
        $this->assertSame(0, $this->conn->table('derived_runs')->countAllResults());

        $this->resetStreamFilterBuffer();
        command('gelita:turunan:hitung --studi=' . $this->code);
        $this->assertStringContainsString('done', $this->getStreamFilterBuffer());
        $this->assertSame(1, $this->conn->table('derived_runs')->where('via', 'command')->where('status', 'done')->countAllResults());

        // Fresh: the command still makes a new run, like the button
        command('gelita:turunan:hitung --studi=' . $this->code);
        $this->assertSame(2, (int) $this->conn->table('research_studies')->get()->getRow('derived_run_id'));
    }

    public function testUnlockNeedsAReasonAndIsAudited(): void
    {
        $this->conn->table('research_studies')->update(['thresholds_locked_at' => '2026-11-20 08:00:00']);

        command('gelita:turunan:ambang --studi=' . $this->code . ' --buka');
        $this->assertNotNull($this->conn->table('research_studies')->get()->getRow('thresholds_locked_at'), 'no reason, no unlock');

        command('gelita:turunan:ambang --studi=' . $this->code . ' --buka --alasan=penyimpangan-D01');
        $this->assertNull($this->conn->table('research_studies')->get()->getRow('thresholds_locked_at'));

        $audit = $this->conn->table('audit_logs')->where('action', 'study_thresholds_unlocked')->get()->getRowArray();
        $this->assertSame('penyimpangan-D01', json_decode((string) $audit['metadata_json'], true)['reason']);
    }
}
