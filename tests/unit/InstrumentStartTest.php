<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * Starting the test (research revision 10, part 8: 34 §5.3, §7.2, K10, K11,
 * §15.1 `InstrumentStartTest`).
 *
 * @internal
 */
final class InstrumentStartTest extends CIUnitTestCase
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

    public function testWaitsForTheStartButtonAndTheForm(): void
    {
        $id = $this->meeting($this->conn, 1, false);

        $page = (string) $this->go(Method::GET, 'tes')->getBody();
        $this->assertStringContainsString(esc(lang('Instrument.waitStart')), $page);
        $this->assertStringContainsString(' disabled>', $page);
        $this->go(Method::POST, 'tes/mulai');
        $this->assertSame(0, $this->conn->table('instrument_sittings')->countAllResults());
        $this->assertSame(['started' => false, 'form_ready' => true], $this->moduleStatus(), 'two booleans only');

        $this->conn->table('field_sessions')->where('id', $id)->update(['test_started_at' => date('Y-m-d H:i:s')]);
        $this->conn->table('participant_seats')->update(['pre_form' => '']);
        $this->assertStringContainsString(esc(lang('Instrument.waitForm')), (string) $this->go(Method::GET, 'tes')->getBody());
        $this->go(Method::POST, 'tes/mulai');
        $this->assertSame(0, $this->conn->table('instrument_sittings')->countAllResults(), 'no form, no sitting');

        $this->conn->table('participant_seats')->update(['pre_form' => 'B']);
        $this->assertSame(['started' => true, 'form_ready' => true], $this->moduleStatus());
        $this->go(Method::POST, 'tes/mulai');
        $this->assertSame('B', $this->sittingRow($this->conn, 'pre', 'test')['form'], 'copied from participant_seats');
    }

    public function testNoImportedContentShowsANoteAndTheVerifierFlagsIt(): void
    {
        // An upgraded server before gelita:instrumen:impor (9 November 2026, 04)
        $id = $this->meeting($this->conn, 1);
        $this->conn->table('field_sessions')->where('id', $id)->update(['test_started_at' => date('Y-m-d H:i:s')]);
        $this->conn->table('instrument_versions')->update(['is_active' => 0]);
        \Config\Services::resetSingle('instrumentRepository');
        $this->forgetInstruments();

        $page = (string) $this->go(Method::GET, 'tes')->getBody();
        $this->assertStringContainsString(esc(lang('Instrument.notReady')), $page);
        $this->assertStringNotContainsString(esc(lang('Instrument.start')) . '</button>', $page, 'no dead Mulai button');
        $this->assertStringNotContainsString('instrument/status.js', $page);

        $messages = static fn (array $findings): array => array_column(array_filter($findings, static fn (array $f): bool => $f['scope'] === 'Modul tes'), 'level');
        $this->assertSame(['warning'], $messages((new \App\Libraries\ContentVerifier())->run()));
        $this->assertSame(['error'], $messages((new \App\Libraries\ContentVerifier())->run(true)), 'an error when checking research readiness');

        $this->conn->table('instrument_versions')->update(['is_active' => 1]);
        $this->assertSame([], $messages((new \App\Libraries\ContentVerifier())->run()));
    }

    public function testTheSeatLocksOnceAndTwoStartsMakeOneSitting(): void
    {
        $this->meeting($this->conn, 1);
        $context = service('instrumentGate')->current();
        $first   = service('instruments')->startTest($context);
        $locked  = $this->conn->table('participant_seats')->get()->getRow('locked_at');
        $this->assertNotNull($locked);

        $this->forgetInstruments();
        $second = service('instruments')->startTest(service('instrumentGate')->current());
        $this->assertSame($first['id'], $second['id']);
        $this->assertSame(1, $this->conn->table('instrument_sittings')->countAllResults());
        $this->assertSame($locked, $this->conn->table('participant_seats')->get()->getRow('locked_at'), 'locked once');

        // The losing INSERT of a race reads the row that exists
        $this->conn->table('instrument_sittings')->ignore(true)->insert(['participant_id' => 1, 'study_id' => 1, 'occasion' => 'pre', 'kind' => 'test', 'is_preview' => 0, 'version_id' => 1, 'started_at' => date('Y-m-d H:i:s')]);
        $this->assertSame(1, $this->conn->table('instrument_sittings')->countAllResults());
    }

    public function testNoPreTestAfterPlaying(): void
    {
        $this->meeting($this->conn, 1);
        $this->playData($this->conn, 1, 1);

        $this->assertRedirectsTo('tes/angket/0', $this->go(Method::GET, 'tes'), 'K11: straight to the questionnaire');
        $this->assertStringContainsString(esc(lang('Instrument.noPreAfterPlay')), (string) $this->go(Method::GET, 'tes/angket/0')->getBody());
        $this->go(Method::GET, 'tes/angket/1');
        $this->assertNull($this->sittingRow($this->conn, 'pre', 'test'));
        $this->assertSame(1, (int) $this->sittingRow($this->conn, 'pre', 'questionnaire')['late'], 'questionnaire_late');
    }

    /** @return array<string, mixed> */
    private function moduleStatus(): array
    {
        return (array) json_decode(strip_tags((string) $this->go(Method::GET, 'api/instrument/status')->getBody()), true);
    }
}
