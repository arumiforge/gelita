<?php

use App\Libraries\InstrumentImporter;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * The digital-access questionnaire (research revision 10, part 8: 34 §7.7,
 * §10.2, K7; 16 → Pemasukan data; §15.1 `QuestionnaireTest`).
 *
 * @internal
 */
final class QuestionnaireTest extends CIUnitTestCase
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

    /** @return array<string, ?string> */
    private function answers(): array
    {
        return array_column($this->conn->table('instrument_responses r')->select('r.unit, r.response')
            ->join('instrument_sittings s', 's.id = r.sitting_id')->where('s.kind', 'questionnaire')->orderBy('r.id', 'ASC')->get()->getResultArray(), 'response', 'unit');
    }

    public function testOneUnitPerVariableAndTickAllBoxes(): void
    {
        $this->meeting($this->conn, 1);
        $this->playData($this->conn, 1, 1);   // K11: straight to the questionnaire, late

        $this->go(Method::GET, 'tes');
        $page = (string) $this->go(Method::GET, 'tes/angket/1')->getBody();
        $this->assertStringContainsString(lang('Instrument.sectionOf', [1, 8]), $page);
        $this->assertSame(InstrumentImporter::QUESTIONNAIRE_UNITS, array_keys($this->answers()), '41 units, ang-01 to ang-17 in order');

        // ang-02: one ticked → 1/0 per box; "Tidak ada satu pun" never clears the others
        $this->go(Method::POST, 'tes/angket/1', ['a' => ['home_tablet' => '1', 'home_device_none' => '1', 'home_internet' => '9'], 'm' => ['ang-02' => '1'], 'tujuan' => 'berikutnya']);
        $a = $this->answers();
        $this->assertSame(['0', '1', '0', '0', '1', '9'], [$a['home_phone'], $a['home_tablet'], $a['home_computer'], $a['home_console'], $a['home_device_none'], $a['home_internet']]);
        $this->assertNull($a['phone_access'], 'left out');

        // None ticked: every box empty again
        $this->go(Method::POST, 'tes/angket/1', ['m' => ['ang-02' => '1'], 'tujuan' => 'berikutnya']);
        $a = $this->answers();
        $this->assertSame([null, null], [$a['home_phone'], $a['home_tablet']]);

        // A table item: one unit per row (ang-07); an unknown code is not kept
        $this->go(Method::POST, 'tes/angket/3', ['a' => ['act_video' => '2', 'act_game' => '5'], 'tujuan' => 'berikutnya']);
        $a = $this->answers();
        $this->assertSame(['2', null], [$a['act_video'], $a['act_game']]);

        $this->go(Method::POST, 'tes/angket/selesai', ['a' => ['books_home' => '2']]);
        $sitting = $this->sittingRow($this->conn, 'pre', 'questionnaire');
        $this->assertNotNull($sitting['submitted_at']);
        $this->assertSame(1, (int) $sitting['late'], 'questionnaire_late after playing (16 no. 9)');
        $this->assertSame('2', $this->answers()['books_home']);
        $this->assertRedirectsTo('tes/selesai', $this->go(Method::GET, 'tes'));
    }

    public function testTheSheetWritesADashForAnEmptyAnswer(): void
    {
        $this->meeting($this->conn, 1);
        $this->playData($this->conn, 1, 1);
        $this->go(Method::GET, 'tes/angket/1');
        $this->go(Method::POST, 'tes/angket/selesai', ['a' => ['books_home' => '4']]);

        $rows = iterator_to_array(service('rawDataService')->rows('angket', ['study_id' => 1], \App\Services\StaffScope::of(true, true)), false);
        $this->assertCount(1, $rows);
        $this->assertSame(['S03-A01', '4', '-', 1], [$rows[0]['participant_code'], $rows[0]['books_home'], $rows[0]['phone_access'], $rows[0]['questionnaire_late']]);
    }
}
