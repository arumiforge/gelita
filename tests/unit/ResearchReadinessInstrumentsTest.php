<?php

use App\Libraries\ResearchReadiness;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * The research readiness list names missing test-module content (owner,
 * 20 November 2026): without it students only see "Tesnya belum siap".
 *
 * @internal
 */
final class ResearchReadinessInstrumentsTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content', 'ui']);
        $this->conn = $this->createGradeFiveWorld();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testImportedContentPassesAndAMissingInstrumentIsNamed(): void
    {
        $item = $this->item();
        $this->assertTrue($item['ok'], $item['detail']);
        $this->assertSame(lang('Admin.readyInstrumentsOk'), $item['detail']);

        $this->conn->table('instrument_versions')->whereIn('instrument', ['angket', 'sikap'])->update(['is_active' => 0]);

        $check = (new ResearchReadiness())->check();
        $item  = array_column($check['items'], null, 'key')['instruments'];
        $this->assertFalse($item['ok']);
        $this->assertFalse($check['ready']);
        $this->assertFalse($check['instruments_ok']);
        $this->assertSame(lang('Admin.readyInstrumentsMissing', ['angket, skala sikap']), $item['detail']);
    }

    /** @return array{key: string, label: string, ok: bool, detail: string, link: string} */
    private function item(): array
    {
        return array_column((new ResearchReadiness())->check()['items'], null, 'key')['instruments'];
    }
}
