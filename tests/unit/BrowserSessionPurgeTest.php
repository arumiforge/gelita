<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * The retention run cleans `ci_sessions` only with the research privacy
 * switch on (research revision 10, part 4: 30 §12, §19.1 `BrowserSessionPurgeTest`).
 *
 * @internal
 */
final class BrowserSessionPurgeTest extends CIUnitTestCase
{
    use ResetTables;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createResetTables();
        $old   = date('Y-m-d H:i:s', time() - 5 * 3600);
        $fresh = date('Y-m-d H:i:s', time() - 60);
        $hash  = str_repeat('ab', 32);
        $this->conn->table('ci_sessions')->insertBatch([
            ['id' => 'expired-hash', 'ip_address' => $hash, 'timestamp' => $old, 'data' => ''],
            ['id' => 'live-hash', 'ip_address' => $hash, 'timestamp' => $fresh, 'data' => ''],
            ['id' => 'live-raw', 'ip_address' => '203.0.113.7', 'timestamp' => $fresh, 'data' => ''],
        ]);
    }

    protected function tearDown(): void
    {
        config('Gelita')->researchPrivacy = false;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testSwitchOnRemovesExpiredAndRawIpRows(): void
    {
        config('Gelita')->researchPrivacy = true;

        $result = $this->retention()->run();

        $this->assertSame(2, $result['expired_browser_sessions']);
        $this->assertSame(['live-hash'], array_column($this->conn->table('ci_sessions')->get()->getResultArray(), 'id'));
    }

    public function testSwitchOffLeavesCiSessionsAlone(): void
    {
        $result = $this->retention()->run();

        $this->assertArrayNotHasKey('expired_browser_sessions', $result);
        $this->assertSame(3, $this->conn->table('ci_sessions')->countAllResults());
    }

    /** run() without the idle-attempt step, whose MySQL-only SQL (GREATEST) does not run on SQLite. */
    private function retention(): App\Services\RetentionService
    {
        return new class () extends App\Services\RetentionService {
            public function abandonStaleAttempts(int $hours): int
            {
                return 0;
            }
        };
    }
}
