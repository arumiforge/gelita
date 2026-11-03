<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * The game is closed outside a field session (research revision 10, part 2:
 * 28 §7, K13, §19.1 `FieldGateTest`).
 *
 * @internal
 */
final class FieldGateTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testPagesWithoutAFieldSessionGoToTheTokenScreen(): void
    {
        $this->signIn(1, 1);   // game session 1 belongs to no field session

        $this->assertRedirectsTo('token', $this->send(Method::GET, 'peta'));
        $this->assertRedirectsTo('token', $this->send(Method::GET, 'pustaka'));
        $this->assertRedirectsTo('token', $this->send(Method::GET, 'profil'));

        $this->signIn(1);      // signed in, no game session at all
        $this->assertRedirectsTo('token', $this->send(Method::GET, 'gerbang'));

        $page = $this->send(Method::GET, 'token');
        $page->assertOK();
        $this->assertStringContainsString(esc(lang('Game.tokenHelp')), (string) $page->getBody());
        $this->assertStringContainsString('S03-A01', (string) $page->getBody(), 'the research code above the box');
    }

    public function testAClosedFieldSessionShowsTheGoodbyeScreenAndApiAnswers409(): void
    {
        $fs = $this->fieldSession($this->conn, 2, 'ABCDEF');
        $this->conn->table('game_sessions')->where('id', 1)->update(['field_session_id' => $fs['id'], 'meeting_no' => 2, 'status' => 'active']);
        $this->signIn(1, 1);

        $this->send(Method::GET, 'peta')->assertOK();

        $this->conn->table('field_sessions')->update(['closed_at' => date('Y-m-d H:i:s', time() - 5)]);

        $this->assertRedirectsTo('sampai-jumpa', $this->send(Method::GET, 'peta'));
        $this->assertSame('paused', $this->conn->table('game_sessions')->where('id', 1)->get()->getRow('status'));

        $bye = $this->send(Method::GET, 'sampai-jumpa');
        $bye->assertOK();
        $html = (string) $bye->getBody();
        $this->assertStringContainsString(esc(lang('Game.sessionClosedBody')), $html);
        $this->assertStringNotContainsString(base_url('pustaka'), $html, 'no library link');
        $this->assertStringNotContainsString('hud-profile', $html, 'no profile link');

        $api = $this->send(Method::GET, 'api/progress');
        $api->assertStatus(409);
        $this->assertStringContainsString('"code": "FIELD_SESSION_CLOSED"', (string) $api->getBody());

        // Queued events, heartbeat, logout, and password change stay open (K13)
        $this->assertNotSame(409, $this->send(Method::POST, 'api/session/heartbeat')->response()->getStatusCode());
        $this->assertNotSame(409, $this->send(Method::POST, 'api/events', ['events' => []])->response()->getStatusCode());
        $this->assertNotSame(409, $this->send(Method::POST, 'api/audio-events', ['events' => []])->response()->getStatusCode());
        $this->send(Method::GET, 'ganti-sandi')->assertOK();
        $this->assertRedirectsTo('/', $this->send(Method::GET, 'keluar'));

        // One pause event, whatever the number of requests (28 §6.6)
        $this->assertSame(1, $this->conn->table('game_event_logs')->where('event_type', 'session_paused')->like('payload_json', 'field_session_closed')->countAllResults());
    }

    public function testAClosedStudyOpensTheGame(): void
    {
        $this->conn->table('research_studies')->update(['status' => 'closed']);
        $this->conn->table('research_studies')->insert(['code' => 'LAIN', 'name' => 'Lain', 'status' => 'draft']);
        $this->signIn(1, 1);

        // No active study: the field-mode check is off (the page itself may still need an active study)
        $this->assertNotSame(site_url('token'), $this->send(Method::GET, 'peta')->getRedirectUrl());
    }

    public function testFieldModeOffKeepsTheGameOpen(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $this->signIn(1, 1);

        $this->send(Method::GET, 'peta')->assertOK();
        $this->assertRedirectsTo('gerbang', $this->send(Method::GET, 'token'));
    }
}
