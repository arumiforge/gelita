<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;

/**
 * FieldWorld plus what part 4 (30) needs: a head researcher (staff id 3),
 * the research privacy switch, and helpers to act as a staff role and to
 * give a student a consent row. Staff: 1 admin, 2 peneliti, 3 ketua.
 */
trait ConsentWorld
{
    use FieldWorld;

    private function createConsentWorld(): BaseConnection
    {
        $db = $this->createFieldWorld();
        $db->table('staff_users')->insert(['id' => 3, 'username' => 'ketua_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1]);
        config('Gelita')->researchPrivacy = true;

        return $db;
    }

    private function dropConsentWorld(): void
    {
        config('Gelita')->researchPrivacy = false;
        $_SESSION = [];
        service('cache')->clean();
        $this->dropResetTables();
    }

    private function asStaff(string $role): void
    {
        $_SESSION = ['staff_id' => ['admin' => 1, 'peneliti' => 2, 'ketua' => 3][$role], 'staff_role' => $role, 'staff_last_seen' => time()];
    }

    /** @param array<string, mixed> $values */
    private function consent(BaseConnection $db, int $participantId, array $values = []): void
    {
        service('consentService')->createRow($participantId, 1, $values + ['parent_consent_on' => date('Y-m-d', strtotime('-10 days'))]);
    }

    /** One completed attempt with a response and a try for student $participantId's session $sessionId. */
    private function playData(BaseConnection $db, int $participantId, int $sessionId): void
    {
        $db->table('challenge_attempts')->insert(['session_id' => $sessionId, 'challenge_node_id' => 1, 'status' => 'completed', 'stars' => 2, 'selected_item_ids_json' => '[]']);
        $attempt = (int) $db->insertID();
        $item    = (int) $db->table('challenge_items')->select('id')->limit(1)->get()->getRow('id');
        $db->table('item_responses')->insert(['challenge_attempt_id' => $attempt, 'challenge_item_id' => $item]);
        $db->table('item_tries')->insert(['challenge_attempt_id' => $attempt, 'challenge_item_id' => $item, 'try_no' => 1, 'answer_json' => '{}']);
        $db->table('game_event_logs')->insert(['event_uuid' => 'e-' . $participantId . '-' . $sessionId, 'client_event_id' => 'c' . $sessionId, 'session_id' => $sessionId, 'participant_id' => $participantId, 'event_type' => 'screen_viewed', 'payload_json' => '{}']);
        $db->table('participant_feedback')->insert(['participant_id' => $participantId, 'session_id' => $sessionId, 'rating' => 4, 'suggestion' => 'Seru']);
        $db->table('participant_progress')->ignore(true)->insert(['participant_id' => $participantId, 'study_id' => 1, 'completed_nodes' => 1, 'total_stars' => 2]);
    }
}
