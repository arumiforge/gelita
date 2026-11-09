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
        $db->table('game_event_logs')->insert(['event_uuid' => 'e-' . $participantId . '-' . $sessionId, 'client_event_id' => 'c' . $sessionId, 'session_id' => $sessionId, 'participant_id' => $participantId, 'event_type' => 'screen_viewed', 'payload_json' => '{}', 'occurred_at' => date('Y-m-d H:i:s')]);
        $db->table('participant_feedback')->insert(['participant_id' => $participantId, 'session_id' => $sessionId, 'rating' => 4, 'suggestion' => 'Seru']);
        $db->table('participant_progress')->ignore(true)->insert(['participant_id' => $participantId, 'study_id' => 1, 'completed_nodes' => 1, 'total_stars' => 2]);
    }

    /** Files a parent's request for S03-A01 as $role through the service; returns its id (0 when none was made). */
    private function fileRequest(string $kind, string $role = 'peneliti', string $code = 'S03-A01'): int
    {
        $staff  = ['admin' => 1, 'peneliti' => 2, 'ketua' => 3][$role];
        $result = service('dataRequests')->create([
            'participant_code' => $code, 'kind' => $kind, 'received_at' => date('Y-m-d H:i', time() - 7200),
            'received_via' => 'paper', 'verified' => '1', 'verified_at' => date('Y-m-d H:i', time() - 3600),
        ], $staff, $role);

        return (int) ($result['id'] ?? 0);
    }

    /** @return array<string, mixed> */
    private function requestRow(int $id): array
    {
        return model(\App\Models\DataDeletionRequestModel::class)->find($id);
    }

    /**
     * A `risk` sitting (real schema, part 8: 34 §11.1) with answers for ang-21
     * (`risk_upset`) and ang-23 (`support_none`); the other units of the part stay empty.
     */
    private function riskAnswers(\CodeIgniter\Database\BaseConnection $db, int $participantId, string $upset, string $none = '0', ?int $fieldSessionId = null, int $preview = 0): int
    {
        $version = (int) $db->table('instrument_versions')->where('instrument', 'angket')->where('is_active', 1)->get()->getRow('id');
        $now     = date('Y-m-d H:i:s');
        $db->table('instrument_sittings')->insert([
            'participant_id' => $participantId, 'study_id' => 1, 'field_session_id' => $fieldSessionId, 'occasion' => 'post', 'kind' => 'risk',
            'version_id' => $version, 'is_preview' => $preview, 'risk_offered' => 1, 'risk_reconsent' => 1, 'risk_reconsent_at' => $now,
            'started_at' => $now, 'submitted_at' => $now, 'updated_at' => $now,
        ]);
        $sitting = (int) $db->insertID();
        $db->table('instrument_parts')->insert(['sitting_id' => $sitting, 'part' => 'risk', 'opened_at' => $now, 'submitted_at' => $now]);
        $part = (int) $db->insertID();

        foreach ($db->table('instrument_items')->where('version_id', $version)->where('part', 'risk')->get()->getResultArray() as $item) {
            foreach ((array) json_decode((string) $item['units_json'], true) as $unit) {
                $response = ['risk_upset' => $upset, 'support_none' => $none][$unit] ?? null;
                $db->table('instrument_responses')->insert([
                    'part_id' => $part, 'sitting_id' => $sitting, 'item_id' => (int) $item['id'], 'unit' => $unit,
                    'response' => $response, 'answered_at' => $response === null ? null : $now, 'updated_at' => $now,
                ]);
            }
        }

        return $sitting;
    }
}
