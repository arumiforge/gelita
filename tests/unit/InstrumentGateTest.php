<?php

use App\Services\InstrumentGate;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * When the test module opens (research revision 10, part 8: 34 §5.1, §5.5,
 * §7.7, §10, §15.1 `InstrumentGateTest`).
 *
 * @internal
 */
final class InstrumentGateTest extends CIUnitTestCase
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

    private function step(): string
    {
        $this->forgetInstruments();

        return service('instrumentGate')->current()['step'] ?? InstrumentGate::STEP_CLOSED;
    }

    public function testClosedOutsideFieldModeOutsideASessionAndWithoutATestKey(): void
    {
        $this->signIn(1, 1);
        $this->assertSame('closed', $this->step(), 'no field session bound');

        $this->meeting($this->conn, 2);
        $this->assertSame('closed', $this->step(), 'P2 has no test key');
        $this->assertStringNotContainsString('Halo! Di sini', (string) $this->go(Method::GET, 'tes')->getBody());

        $this->meeting($this->conn, 1);
        $this->assertSame('test', $this->step());

        $this->conn->table('research_studies')->update(['meeting_plan' => 'satu_sesi']);
        $this->assertSame('closed', $this->step(), 'satu_sesi has no test (28 K19)');
        $this->conn->table('research_studies')->update(['meeting_plan' => 'lima_pertemuan']);

        $this->conn->table('participants')->where('id', 1)->update(['deactivated_at' => date('Y-m-d H:i:s')]);
        $this->assertSame('closed', $this->step(), 'deactivated (30 §5.3)');
        $this->conn->table('participants')->where('id', 1)->update(['deactivated_at' => null]);

        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $this->assertSame('closed', $this->step(), 'field mode off (K4)');
        $page = (string) $this->go(Method::GET, 'tes')->getBody();
        $this->assertStringContainsString(esc(lang('Instrument.closed')), $page);
        $this->assertRedirectsTo('tes', $this->go(Method::GET, 'tes/contoh/mb'));
    }

    public function testOpenAtP5AndInMakeupsWithinTheWindow(): void
    {
        $main = $this->meeting($this->conn, 5);
        $this->assertSame('test', $this->step());
        $this->conn->table('field_sessions')->where('id', $main)->update(['session_date' => date('Y-m-d', strtotime('-2 days')), 'closed_at' => date('Y-m-d H:i:s')]);

        $this->meeting($this->conn, 5, true, ['makeup_no' => 1, 'main_session_id' => $main]);
        $this->assertSame('test', $this->step(), 'make-up 2 days later');

        $this->conn->table('field_sessions')->where('id', $main)->update(['session_date' => date('Y-m-d', strtotime('-5 days'))]);
        $this->assertSame('risk', $this->step(), 'after 3 days the test stays closed; the risk items still come (§10.3)');
    }

    public function testTheQuestionnaireContinuesAtP2UntilTheMainP5Opens(): void
    {
        $this->meeting($this->conn, 1);
        $context = service('instrumentGate')->current();
        service('instruments')->openQuestionnaire($context);

        $this->meeting($this->conn, 2);
        $this->assertSame('questionnaire', $this->step(), 'carried over (§7.7)');
        $this->assertRedirectsTo('tes', $this->go(Method::GET, 'peta'), 'the game waits for the questionnaire');

        $this->fieldSession($this->conn, 5, 'PQRSTU');
        $this->forgetInstruments();
        $this->assertSame('closed', $this->step(), 'not after the main P5 opened (§10.2)');
    }

    public function testThePreviewIsForTestAccountsOnly(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        config('Gelita')->instrumentPreview = true;
        $this->signIn(1, 1);
        $this->assertSame('closed', $this->step(), 'not a uji.* account');

        $this->conn->table('participants')->where('id', 1)->update(['username' => 'uji.validator']);
        $this->assertSame('test', $this->step());

        $this->go(Method::POST, 'tes/mulai', ['occasion' => 'pre', 'form' => 'B']);
        $sitting = $this->conn->table('instrument_sittings')->get()->getRowArray();
        $this->assertSame(['B', 1], [$sitting['form'], (int) $sitting['is_preview']]);
    }
}
