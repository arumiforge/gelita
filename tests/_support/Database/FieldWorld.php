<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\TestResponse;
use Config\Services;

/**
 * The grade 5 world (GradeFiveWorld) with field mode on (research revision
 * 10, part 2: 28): study `field_mode = 1`, `unlock_mode = sequential`,
 * research school `S03` (id 1) with class group `A`, a second school `S04`,
 * a researcher (staff id 2), and student 1 (`jaka5`) moved to `S03-A01` in
 * class group A. Student 1 keeps game session 1, which belongs to no field
 * session.
 *
 * Classes using it also use FeatureTestTrait for send().
 */
trait FieldWorld
{
    use GradeFiveWorld;

    /** CSRF hash (session mode); a POST token = hash . zero key (tokenRandomize). */
    private string $csrfHash = '0123456789abcdef0123456789abcdef';

    private function createFieldWorld(): BaseConnection
    {
        $db = $this->createGradeFiveWorld();

        $db->table('research_studies')->update(['field_mode' => 1, 'unlock_mode' => 'sequential', 'meeting_plan' => 'lima_pertemuan']);
        $db->table('schools')->insertBatch([
            ['id' => 1, 'name' => 'SD Lapangan', 'research_code' => 'S03', 'is_verified' => 1, 'is_active' => 1],
            ['id' => 2, 'name' => 'SD Lain', 'research_code' => 'S04', 'is_verified' => 1, 'is_active' => 1],
        ]);
        $db->table('staff_users')->insert([
            'username' => 'peneliti_uji', 'password_hash' => 'x', 'role' => 'peneliti', 'display_name' => 'Peneliti', 'is_active' => 1,
        ]);
        $db->table('participants')->where('id', 1)->update(['participant_code' => 'S03-A01', 'school_id' => 1, 'rombel' => 'A', 'intro_seen_at' => date('Y-m-d H:i:s')]);
        $db->table('participant_code_counters')->insert(['school_id' => 1, 'rombel' => 'A', 'next_no' => 2]);

        $this->resetFieldServices();
        $_SESSION = [];

        return $db;
    }

    private function resetFieldServices(): void
    {
        foreach (['fieldSessions', 'gameContext', 'participantCodes', 'security', 'sessionService', 'challengeService', 'scoringService', 'eventService', 'contentRepository', 'staffContext', 'analyticsService'] as $name) {
            Services::resetSingle($name);
        }
    }

    /**
     * A field session row and its token, open by default (opened 30 minutes
     * ago, closes in an hour).
     *
     * @param array<string, mixed> $overrides
     *
     * @return array{id: int, token: string}
     */
    private function fieldSession(BaseConnection $db, int $meetingNo, string $token, array $overrides = []): array
    {
        $makeup = (int) ($overrides['makeup_no'] ?? 0);
        $db->table('field_sessions')->insert($overrides + [
            'study_id'     => 1,
            'code'         => \App\Services\FieldSessionService::sessionCode('S03', (string) ($overrides['rombel'] ?? 'A'), $meetingNo, $makeup),
            'school_id'    => 1,
            'rombel'       => 'A',
            'meeting_no'   => $meetingNo,
            'makeup_no'    => 0,
            'session_date' => date('Y-m-d'),
            'opens_at'     => date('Y-m-d H:i:s', time() - 1800),
            'closes_at'    => date('Y-m-d H:i:s', time() + 3600),
        ]);
        $id = (int) $db->insertID();
        $db->table('field_session_tokens')->insert(['field_session_id' => $id, 'token' => $token, 'created_at' => date('Y-m-d H:i:s')]);

        return ['id' => $id, 'token' => $token];
    }

    /** A student of school $schoolId and class group $rombel with a game session in the study (none bound). */
    private function student(BaseConnection $db, string $code, string $username, int $schoolId = 1, string $rombel = 'A'): int
    {
        $db->table('participants')->insert([
            'participant_code' => $code, 'username' => $username, 'password_hash' => password_hash('Kopi-Kedu7!', PASSWORD_DEFAULT),
            'display_name' => strtoupper($username), 'school_id' => $schoolId, 'rombel' => $rombel,
        ]);
        $id = (int) $db->insertID();
        $db->table('game_sessions')->insert([
            'session_code' => 'S-' . $username, 'participant_id' => $id, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'completed',
            'ended_at' => date('Y-m-d H:i:s', time() - 86400),
        ]);

        return $id;
    }

    /** One request as the signed-in student (or guest) in $_SESSION. */
    private function send(string $method, string $path, array $params = []): TestResponse
    {
        $this->resetFieldServices();
        unset($_SESSION['errors'], $_SESSION['_ci_old_input'], $_SESSION['__ci_vars']);

        $session = ['gelita_csrf' => $this->csrfHash] + $_SESSION;

        if ($method === Method::POST) {
            $params['gelita_csrf'] = $this->csrfHash . str_repeat('0', 32);
        }

        $response = $this->withSession($session)->call($method, $path, $method === Method::POST ? $params : null);
        $this->resetFieldServices();

        return $response;
    }

    private function signIn(int $participantId, ?int $gameSessionId = null): void
    {
        $_SESSION = ['participant_id' => $participantId] + ($gameSessionId === null ? [] : ['game_session_id' => $gameSessionId]);
    }

    private function assertRedirectsTo(string $path, TestResponse $response, string $message = ''): void
    {
        $this->assertTrue($response->isRedirect(), trim($message . ' (no redirect: ' . $response->response()->getStatusCode() . ')'));
        $this->assertSame(site_url($path), $response->getRedirectUrl(), $message);
    }
}
