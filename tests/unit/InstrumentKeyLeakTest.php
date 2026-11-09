<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * Keys, scores, and error codes never reach the browser (research revision
 * 10, part 8: 34 §7.5, §15.1 `InstrumentKeyLeakTest`).
 *
 * @internal
 */
final class InstrumentKeyLeakTest extends CIUnitTestCase
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

    private function assertClean(string $page, string $where): void
    {
        foreach (['key_json', 'INF-', 'DIG-', 'BUD-', 'other_pair', '"scores"', 'tes-mb-B', 'tes-ae-B', 'tes-bd-B', 'raw_score', 'not_reached'] as $leak) {
            $this->assertStringNotContainsString($leak, $page, "{$where}: {$leak}");
        }
    }

    public function testNoKeyOnAnyTestQuestionnaireRiskOrAttitudePage(): void
    {
        service('consentService')->apply(1, 1, ['risk_items_consent' => '1'], 2);
        $this->meeting($this->conn, 1);
        $this->go(Method::POST, 'tes/mulai');
        $this->assertClean((string) $this->go(Method::GET, 'tes/contoh/mb')->getBody(), 'examples');
        $this->go(Method::POST, 'tes/bagian/mb/mulai');

        foreach (['mb' => 16, 'ae' => 8, 'bd' => 8] as $part => $count) {
            if ($part !== 'mb') {
                $this->go(Method::POST, 'tes/bagian/' . $part . '/mulai');
            }

            for ($no = 1; $no <= $count; $no++) {
                $page = (string) $this->go(Method::GET, 'tes/bagian/' . $part . '/' . $no)->getBody();
                $this->assertStringContainsString(lang('Instrument.itemNumber', [$no]), $page, 'the item page itself');
                $this->assertClean($page, "{$part} {$no}");
            }

            $this->assertClean((string) $this->go(Method::GET, 'tes/bagian/' . $part . '/periksa')->getBody(), "{$part} review");
            $this->go(Method::POST, 'tes/bagian/' . $part . '/kirim');
        }

        for ($no = 0; $no <= 8; $no++) {
            $this->assertClean((string) $this->go(Method::GET, 'tes/angket/' . $no)->getBody(), "angket {$no}");
        }

        $status = strip_tags((string) $this->go(Method::GET, 'api/instrument/status')->getBody());
        $this->assertSame(['started', 'form_ready'], array_keys((array) json_decode($status, true)));

        // P5 risk items
        $this->conn->table('instrument_sittings')->insert(['participant_id' => 1, 'study_id' => 1, 'occasion' => 'post', 'kind' => 'test', 'form' => 'B', 'version_id' => 1, 'started_at' => date('Y-m-d H:i:s'), 'submitted_at' => date('Y-m-d H:i:s')]);
        $this->meeting($this->conn, 5);
        $this->assertClean((string) $this->go(Method::GET, 'tes/tambahan')->getBody(), 'T11');
        $this->go(Method::POST, 'tes/tambahan', ['jawab' => 'ya']);

        for ($no = 1; $no <= 3; $no++) {
            $page = (string) $this->go(Method::GET, 'tes/tambahan/' . $no)->getBody();
            $this->assertStringContainsString(lang('Instrument.questionOf', [$no, 3]), $page);
            $this->assertClean($page, "risk {$no}");
        }
    }
}
