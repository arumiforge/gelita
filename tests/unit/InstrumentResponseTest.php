<?php

use App\Models\InstrumentResponseModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * Answers and view times (research revision 10, part 8: 34 §7.5, §15.1
 * `InstrumentResponseTest`).
 *
 * @internal
 */
final class InstrumentResponseTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
        $this->meeting($this->conn, 1);
        $this->go(Method::POST, 'tes/mulai');
        $this->go(Method::POST, 'tes/bagian/mb/mulai');
    }

    protected function tearDown(): void
    {
        Factories::reset('models');
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    /** @return array<string, mixed> */
    private function row(string $unit): array
    {
        return $this->conn->table('instrument_responses')->where('unit', $unit)->get()->getRowArray();
    }

    public function testServerViewTimesAndAnswerChanges(): void
    {
        $this->go(Method::GET, 'tes/bagian/mb/1');
        $this->conn->table('instrument_responses')->where('unit', 'tes-mb-A01')->update(['shown_at' => date('Y-m-d H:i:s', time() - 10)]);
        $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'A'], 'tujuan' => 'berikutnya']);

        $row = $this->row('tes-mb-A01');
        $this->assertGreaterThanOrEqual(9000, (int) $row['time_on_item_ms']);
        $this->assertLessThan(15000, (int) $row['time_on_item_ms']);
        $this->assertSame((int) $row['time_on_item_ms'], (int) $row['last_view_ms']);
        $this->assertSame([1, 0, null], [(int) $row['view_count'], (int) $row['answer_changes'], $row['shown_at']]);
        $this->assertNotNull($row['first_shown_at']);
        $this->assertNotNull($row['updated_at'], '34 §18 no. 6');

        // A second view adds its time; changing a stored answer counts once
        $this->go(Method::GET, 'tes/bagian/mb/1');
        $this->conn->table('instrument_responses')->where('unit', 'tes-mb-A01')->update(['shown_at' => date('Y-m-d H:i:s', time() - 4)]);
        $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'B'], 'tujuan' => 'berikutnya']);
        $second = $this->row('tes-mb-A01');
        $this->assertGreaterThan((int) $row['time_on_item_ms'] + 3000, (int) $second['time_on_item_ms']);
        $this->assertLessThan(6000, (int) $second['last_view_ms'], 'the last view only');
        $this->assertSame([2, 1, 'B'], [(int) $second['view_count'], (int) $second['answer_changes'], $second['response']]);

        // Saving without a new view adds nothing
        $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'B'], 'tujuan' => 'berikutnya']);
        $this->assertSame((int) $second['time_on_item_ms'], (int) $this->row('tes-mb-A01')['time_on_item_ms']);

        // The last item shown is where another device goes on (§7.5)
        $this->assertRedirectsTo('tes/bagian/mb/1', $this->go(Method::GET, 'tes'));
    }

    public function testTwoTabsTheLoserIsAnsweredFromTheNewestState(): void
    {
        $this->go(Method::GET, 'tes/bagian/mb/1');
        $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'C'], 'tujuan' => 'berikutnya']);

        // Stale data: the other tab read the row before the save above
        $stale = new class () extends InstrumentResponseModel {
            public function findAll(?int $limit = null, int $offset = 0)
            {
                return array_map(static fn (array $row): array => ['revision' => (int) $row['revision'] - 1, 'response' => null] + $row, parent::findAll($limit, $offset));
            }
        };
        Factories::injectMock('models', InstrumentResponseModel::class, $stale);

        $response = $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'D'], 'tujuan' => 'berikutnya']);
        $this->assertRedirectsTo('tes', $response);
        $this->assertSame(lang('Instrument.notSaved'), $_SESSION['instrument_notice'] ?? null);
        $this->assertSame('C', $this->row('tes-mb-A01')['response'], 'nothing changed');
    }
}
