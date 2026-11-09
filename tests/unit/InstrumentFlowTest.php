<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * One student through the module (research revision 10, part 8: 34 §4,
 * §7.3, §15.1 `InstrumentFlowTest`): P1 from the opening screen to `/intro`,
 * P5 to `/penutup`, going back to an earlier item, sending closes a part, and
 * answers after sending are refused.
 *
 * @internal
 */
final class InstrumentFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    public function testP1FromTheOpeningScreenToTheOpeningStory(): void
    {
        $this->meeting($this->conn, 1);

        $page = (string) $this->go(Method::GET, 'tes')->getBody();
        $this->assertStringContainsString('Halo! Di sini ada soal membaca', $page);
        $this->assertStringContainsString('S03-A01', $page);
        $this->assertStringContainsString('bootstrap-5.3.8/bootstrap.min.css', $page);

        $this->assertRedirectsTo('tes/contoh/mb', $this->go(Method::POST, 'tes/mulai'));
        $this->assertSame('A', $this->sittingRow($this->conn, 'pre', 'test')['form']);
        $this->assertNotNull($this->conn->table('participant_seats')->where('participant_id', 1)->get()->getRow('locked_at'), 'K10: seat locked');

        $this->assertStringContainsString('Jadwal Menyiram Kebun Sekolah', (string) $this->go(Method::GET, 'tes/contoh/mb')->getBody());
        $this->assertRedirectsTo('tes/bagian/mb/1', $this->go(Method::POST, 'tes/bagian/mb/mulai', ['a' => ['tes-ct-01' => 'D']]));
        $this->assertSame('D', $this->conn->table('instrument_responses')->where('unit', 'tes-ct-01')->get()->getRow('response'), 'example answer kept');

        // Item 1 then back from item 2 to item 1
        $item = (string) $this->go(Method::GET, 'tes/bagian/mb/1')->getBody();
        $this->assertStringContainsString('Mengenal Randai', $item);
        $this->assertRedirectsTo('tes/bagian/mb/2', $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'B'], 'tujuan' => 'berikutnya']));
        $this->go(Method::GET, 'tes/bagian/mb/2');
        $this->assertRedirectsTo('tes/bagian/mb/1', $this->go(Method::POST, 'tes/bagian/mb/2', ['tujuan' => 'sebelumnya']));
        $this->assertStringContainsString('value="B" checked', (string) $this->go(Method::GET, 'tes/bagian/mb/1')->getBody());

        $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'B'], 'tujuan' => '16']);
        $this->assertStringContainsString('2, 3', (string) $this->go(Method::GET, 'tes/bagian/mb/periksa')->getBody());
        $this->assertRedirectsTo('tes', $this->go(Method::POST, 'tes/bagian/mb/kirim'));
        $this->assertRedirectsTo('tes/jeda', $this->go(Method::GET, 'tes'));

        // After sending, answers are refused (PART_CLOSED) and nothing changes
        $this->go(Method::POST, 'tes/bagian/mb/1', ['a' => ['tes-mb-A01' => 'C'], 'tujuan' => 'berikutnya']);
        $this->assertSame('B', $this->conn->table('instrument_responses')->where('unit', 'tes-mb-A01')->get()->getRow('response'));
        $part = $this->conn->table('instrument_parts')->where('part', 'mb')->get()->getRowArray();
        $this->assertSame(1, (int) $part['raw_score']);
        $this->assertSame(1, (int) $part['answered_count']);

        $this->go(Method::GET, 'tes/contoh/ae');
        $this->passPart('ae');
        $this->assertRedirectsTo('tes/contoh/bd', $this->go(Method::GET, 'tes'));
        $this->passPart('bd');

        $this->assertNotNull($this->sittingRow($this->conn, 'pre', 'test')['submitted_at']);
        $this->assertSame(16, (int) $this->conn->table('instrument_parts')->where('part', 'ae')->get()->getRow('raw_score'), 'D2 2 per scenario');
        $this->assertSame(8, (int) $this->conn->table('instrument_parts')->where('part', 'bd')->get()->getRow('raw_score'));

        // The questionnaire straight after
        $this->assertRedirectsTo('tes/angket/0', $this->go(Method::GET, 'tes'));
        $this->assertStringContainsString('Ini bukan tes', (string) $this->go(Method::GET, 'tes/angket/0')->getBody());
        $this->assertRedirectsTo('tes', $this->go(Method::GET, 'intro'), 'the opening story waits for the module (§5.2)');
        $this->go(Method::GET, 'tes/angket/1');
        $this->go(Method::POST, 'tes/angket/1', ['a' => ['phone_access' => '2', 'home_phone' => '1'], 'm' => ['ang-02' => '1'], 'tujuan' => 'berikutnya']);
        $this->go(Method::GET, 'tes/angket/8');
        $this->assertRedirectsTo('tes', $this->go(Method::POST, 'tes/angket/selesai', ['a' => ['books_home' => '3']]));

        $rows = array_column($this->conn->table('instrument_responses r')->select('r.unit, r.response')->join('instrument_sittings s', 's.id = r.sitting_id')->where('s.kind', 'questionnaire')->get()->getResultArray(), 'response', 'unit');
        $this->assertCount(41, $rows);
        $this->assertSame(['2', '1', '0', '3'], [$rows['phone_access'], $rows['home_phone'], $rows['home_tablet'], $rows['books_home']]);
        $this->assertNull($rows['act_video']);

        $this->assertRedirectsTo('tes/selesai', $this->go(Method::GET, 'tes'));
        $this->assertStringContainsString(site_url('intro'), (string) $this->go(Method::GET, 'tes/selesai')->getBody());
    }

    public function testP5TestThenT12ThenTheClosingStory(): void
    {
        $this->meeting($this->conn, 5);
        $this->go(Method::POST, 'tes/mulai');
        $this->assertSame('B', $this->sittingRow($this->conn, 'post', 'test')['form'], 'post_form');

        foreach (['mb', 'ae', 'bd'] as $part) {
            $this->passPart($part, 'B');
        }

        // No risk-items consent: straight to T12
        $this->assertRedirectsTo('tes/pesan', $this->go(Method::GET, 'tes'));
        $this->assertSame(0, (int) $this->sittingRow($this->conn, 'post', 'risk')['risk_offered']);
        $this->assertStringContainsString(esc(lang('Game.riskThanksP1')), (string) $this->go(Method::GET, 'tes/pesan')->getBody());
        $this->assertStringContainsString(site_url('penutup'), (string) $this->go(Method::GET, 'tes/selesai')->getBody());
    }
}
