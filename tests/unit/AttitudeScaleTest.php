<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\InstrumentWorld;

/**
 * The attitude scale in Balai Refleksi during a study (research revision 10,
 * part 8: 34 §9, §10.3, K12; 17; §15.1 `AttitudeScaleTest`).
 *
 * @internal
 */
final class AttitudeScaleTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
        $this->conn->table('participant_progress')->ignore(true)->insert(['participant_id' => 1, 'study_id' => 1]);
        $this->conn->table('participant_progress')->where('participant_id', 1)->update(['completed_nodes' => 99]);
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    private function moduleDone(): void
    {
        foreach (['test' => 1, 'risk' => 2] as $kind => $version) {
            $this->conn->table('instrument_sittings')->insert(['participant_id' => 1, 'study_id' => 1, 'occasion' => 'post', 'kind' => $kind, 'form' => $kind === 'test' ? 'B' : null, 'version_id' => $version, 'risk_offered' => $kind === 'risk' ? 0 : null, 'started_at' => date('Y-m-d H:i:s'), 'submitted_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function endingSeen(int $session = 1): void
    {
        $this->conn->table('game_event_logs')->insert(['event_uuid' => 'e-end-' . $session, 'client_event_id' => 'end' . $session, 'session_id' => $session, 'participant_id' => 1, 'event_type' => 'dialogue_completed', 'payload_json' => '{"context":"ending","total":5}', 'occurred_at' => date('Y-m-d H:i:s')]);
    }

    public function testTheOrderBeforeBalaiRefleksi(): void
    {
        $this->meeting($this->conn, 2);
        $this->assertRedirectsTo('peta', $this->go(Method::GET, 'refleksi'));
        $this->assertSame(lang('Game.reflectionLastMeeting'), $_SESSION['error'] ?? null, 'P2–P4 (K12)');

        $this->meeting($this->conn, 5);
        $this->assertRedirectsTo('tes', $this->go(Method::GET, 'refleksi'), 'the module first');

        $this->moduleDone();
        $this->assertRedirectsTo('penutup', $this->go(Method::GET, 'refleksi'), 'then the closing story');

        $this->endingSeen();
        $this->assertRedirectsTo('refleksi/sikap', $this->go(Method::GET, 'refleksi'), 'then the scale');
        $this->assertRedirectsTo('refleksi/sikap', $this->go(Method::POST, 'refleksi', ['rating' => '5']), 'feedback waits too');
    }

    public function testTheScaleSkipSendOnceThenPraise(): void
    {
        $this->meeting($this->conn, 5);
        $this->moduleDone();
        $this->endingSeen();

        $page = (string) $this->go(Method::GET, 'refleksi/sikap')->getBody();
        $this->assertStringContainsString('Pendapatmu tentang budaya Kedu', $page);
        $this->assertStringNotContainsString(esc(lang('Game.reflectionLead')), $page, 'no praise before sending');

        $this->assertStringContainsString('Aku suka makan buah.', (string) $this->go(Method::GET, 'refleksi/sikap/contoh')->getBody());
        $this->assertRedirectsTo('refleksi/sikap/1', $this->go(Method::POST, 'refleksi/sikap/0', ['pilihan' => '2', 'aksi' => 'lanjut']));

        $item = (string) $this->go(Method::GET, 'refleksi/sikap/1')->getBody();
        $this->assertStringContainsString('Aku bangga pada budaya daerah Kedu.', $item);
        $this->assertSame(4, substr_count($item, 'name="pilihan"'), 'four choices, no middle');
        $this->go(Method::POST, 'refleksi/sikap/1', ['pilihan' => '4', 'aksi' => 'lanjut']);

        // Choose, then Lewati: empty again and one change
        $this->go(Method::GET, 'refleksi/sikap/2');
        $this->go(Method::POST, 'refleksi/sikap/2', ['pilihan' => '3', 'aksi' => 'lanjut']);
        $this->go(Method::GET, 'refleksi/sikap/2');
        $this->assertRedirectsTo('refleksi/sikap/3', $this->go(Method::POST, 'refleksi/sikap/2', ['pilihan' => '3', 'aksi' => 'lewati']));

        for ($no = 3; $no <= 12; $no++) {
            $this->go(Method::GET, 'refleksi/sikap/' . $no);
            $this->go(Method::POST, 'refleksi/sikap/' . $no, ['pilihan' => '1', 'aksi' => 'lanjut']);
        }

        $this->assertStringContainsString(esc(lang('Game.attitudeReview', [11, 1])), (string) $this->go(Method::GET, 'refleksi/sikap/kirim')->getBody());
        $this->assertRedirectsTo('refleksi', $this->go(Method::POST, 'refleksi/sikap/kirim'));

        $rows = array_column($this->conn->table('instrument_responses')->get()->getResultArray(), null, 'unit');
        $this->assertSame(['2', '4', null, '1'], [$rows['skp_ct_01']['response'], $rows['skp_01']['response'], $rows['skp_02']['response'], $rows['skp_12']['response']]);
        $this->assertSame(1, (int) $rows['skp_02']['answer_changes']);
        $sitting = $this->sittingRow($this->conn, 'p5', 'attitude');
        $this->assertNotNull($sitting['submitted_at']);
        $this->assertSame(0, (int) $sitting['is_makeup']);

        $page = (string) $this->go(Method::GET, 'refleksi')->getBody();
        $this->assertStringContainsString(esc(lang('Game.reflectionLead')), $page);
        $this->assertStringContainsString(esc(lang('Game.attitudeThanks')), $page);
        $this->assertRedirectsTo('refleksi', $this->go(Method::GET, 'refleksi/sikap'), 'sent once, never reopened');

        $sheet = iterator_to_array(service('rawDataService')->rows('sikap', ['study_id' => 1], \App\Services\StaffScope::of(true, true)), false);
        $this->assertSame(['2', '4', '-', 1], [$sheet[0]['skp_ct_01'], $sheet[0]['skp_01'], $sheet[0]['skp_02'], $sheet[0]['skp_changes']]);
    }

    public function testAMakeupWithinSevenDaysOnly(): void
    {
        $main = $this->meeting($this->conn, 5);
        $this->conn->table('field_sessions')->where('id', $main)->update(['session_date' => date('Y-m-d', strtotime('-6 days')), 'closed_at' => date('Y-m-d H:i:s')]);
        $this->meeting($this->conn, 5, true, ['makeup_no' => 1, 'main_session_id' => $main]);
        $this->moduleDone();
        $this->endingSeen();

        $this->go(Method::GET, 'refleksi/sikap');
        $this->assertSame(1, (int) $this->sittingRow($this->conn, 'p5', 'attitude')['is_makeup'], 'sikap_makeup');

        $this->conn->table('field_sessions')->where('id', $main)->update(['session_date' => date('Y-m-d', strtotime('-8 days'))]);
        $this->assertRedirectsTo('peta', $this->go(Method::GET, 'refleksi'), 'after 7 days closed');
    }
}
