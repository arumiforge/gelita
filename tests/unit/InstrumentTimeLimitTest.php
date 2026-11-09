<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * Hidden time limits (research revision 10, part 8: 34 §7.4, §7.6, K3, K9,
 * §15.1 `InstrumentTimeLimitTest`). The clock is faked by moving the stored
 * start of a block back.
 *
 * @internal
 */
final class InstrumentTimeLimitTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
        $this->conn->table('research_studies')->update(['instrument_settings' => json_encode(['limit_p1' => 2, 'limit_p23' => 3])]);
        $this->meeting($this->conn, 1);
        $this->go(Method::POST, 'tes/mulai');
        $this->go(Method::POST, 'tes/bagian/mb/mulai');
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    private function ago(string $block, int $minutes): void
    {
        $this->conn->table('instrument_blocks')->where('block', $block)->update(['started_at' => date('Y-m-d H:i:s', time() - $minutes * 60)]);
    }

    public function testTheLimitComesFromTheStudyAndClosesThePart(): void
    {
        $this->assertSame(2, (int) $this->conn->table('instrument_blocks')->get()->getRow('limit_minutes'), 'a copy of the setting');

        $page = (string) $this->go(Method::GET, 'tes/bagian/mb/1')->getBody();
        $this->assertMatchesRegularExpression('/data-close-in="1[01][0-9]" data-remind-in="0"/', $page, 'seconds only, no clock on the page');
        $this->go(Method::POST, 'tes/bagian/mb/1', ['tujuan' => 'berikutnya']);   // shown, left empty
        $this->go(Method::GET, 'tes/bagian/mb/2');
        $this->go(Method::POST, 'tes/bagian/mb/2', ['a' => ['tes-mb-A02' => 'D'], 'tujuan' => 'berikutnya']);

        $this->ago('p1', 3);
        $response = $this->go(Method::POST, 'tes/bagian/mb/3', ['a' => ['tes-mb-A03' => ['1' => 'B', '2' => 'S', '3' => 'B']], 'tujuan' => 'berikutnya']);
        $this->assertRedirectsTo('tes', $response);
        $this->assertSame(lang('Instrument.partClosed'), $_SESSION['instrument_notice'] ?? null);

        $part = $this->conn->table('instrument_parts')->where('part', 'mb')->get()->getRowArray();
        $this->assertSame([1, null], [(int) $part['closed_by_limit'], $part['submitted_at']]);
        $this->assertSame('limit', $this->conn->table('instrument_blocks')->where('block', 'p1')->get()->getRow('closed_reason'));

        $rows = array_column($this->conn->table('instrument_responses')->whereIn('unit', ['tes-mb-A01', 'tes-mb-A02', 'tes-mb-A03', 'tes-mb-A16'])->get()->getResultArray(), null, 'unit');
        $this->assertSame([0, '0'], [(int) $rows['tes-mb-A01']['not_reached'], (string) $rows['tes-mb-A01']['score']], 'shown but empty: skipped');
        $this->assertSame([0, '1'], [(int) $rows['tes-mb-A02']['not_reached'], (string) $rows['tes-mb-A02']['score']]);
        $this->assertNull($rows['tes-mb-A03']['response'], 'an answer after the limit is not kept');
        $this->assertSame([1, null], [(int) $rows['tes-mb-A16']['not_reached'], $rows['tes-mb-A16']['score']], 'never shown: not reached');
        $this->assertSame(1, (int) $part['raw_score']);
    }

    public function testBlockP23ClosesBothPartsAndPart3NeverOpened(): void
    {
        $this->go(Method::POST, 'tes/bagian/mb/kirim');
        $this->go(Method::POST, 'tes/bagian/ae/mulai');
        $this->ago('p23', 4);

        $this->assertRedirectsTo('tes/angket/0', $this->go(Method::GET, 'tes'), 'the sitting closed, on to the questionnaire');
        $parts = array_column($this->conn->table('instrument_parts')->get()->getResultArray(), null, 'part');
        $this->assertSame([1, 1], [(int) $parts['ae']['closed_by_limit'], (int) $parts['bd']['closed_by_limit']]);
        $this->assertSame(8, $this->conn->table('instrument_responses')->where('part_id', $parts['bd']['id'])->where('not_reached', 1)->countAllResults());
        $this->assertNotNull($this->sittingRow($this->conn, 'pre', 'test')['submitted_at']);
    }

    public function testExtraMinutesMoveTheLimitButNotAClosedBlock(): void
    {
        $block = (int) $this->conn->table('instrument_blocks')->get()->getRow('id');
        service('instruments')->extendTime($block, 5, 'perangkat mati', 2);
        $this->ago('p1', 3);

        $this->assertSame(200, $this->go(Method::GET, 'tes/bagian/mb/1')->response()->getStatusCode(), 'still open: 2 + 5 minutes');

        $this->ago('p1', 8);
        $this->go(Method::GET, 'tes/bagian/mb/1');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(lang('Admin.instrumentExtendClosed'));
        service('instruments')->extendTime($block, 1, 'lagi', 2);
    }

    public function testTheRetentionRunClosesHangingBlocks(): void
    {
        $this->ago('p1', 3);
        // RetentionService::run() calls this (`closed_test_blocks`)
        $this->assertSame(1, service('instruments')->closeOverdueBlocks());
        $this->assertSame(0, service('instruments')->closeOverdueBlocks());
        $this->assertSame(1, (int) $this->conn->table('instrument_parts')->where('part', 'mb')->get()->getRow('closed_by_limit'));
    }
}
