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

    /** Minimal part 8 tables (34 §11.1) so the risk-item scope has something to delete. */
    private function createRiskTables(\CodeIgniter\Database\BaseConnection $db): void
    {
        $forge = \Config\Database::forge('tests');
        $forge->addField(['id' => ['type' => 'INTEGER', 'auto_increment' => true], 'participant_id' => ['type' => 'INTEGER'], 'study_id' => ['type' => 'INTEGER'],
            'field_session_id' => ['type' => 'INTEGER', 'null' => true], 'kind' => ['type' => 'VARCHAR', 'constraint' => 14], 'is_preview' => ['type' => 'INTEGER', 'default' => 0],
            'risk_offered' => ['type' => 'INTEGER', 'null' => true], 'risk_reconsent' => ['type' => 'INTEGER', 'null' => true]]);
        $forge->addPrimaryKey('id');
        $forge->createTable('instrument_sittings', true);

        foreach (['instrument_blocks', 'instrument_parts'] as $table) {
            $forge->addField(['id' => ['type' => 'INTEGER', 'auto_increment' => true], 'sitting_id' => ['type' => 'INTEGER']]);
            $forge->addPrimaryKey('id');
            $forge->createTable($table, true);
        }

        $forge->addField(['id' => ['type' => 'INTEGER', 'auto_increment' => true], 'sitting_id' => ['type' => 'INTEGER'], 'part_id' => ['type' => 'INTEGER', 'null' => true],
            'unit' => ['type' => 'VARCHAR', 'constraint' => 40], 'response' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true], 'answered_at' => ['type' => 'DATETIME', 'null' => true]]);
        $forge->addPrimaryKey('id');
        $forge->createTable('instrument_responses', true);
    }

    private function dropRiskTables(): void
    {
        foreach (['instrument_responses', 'instrument_parts', 'instrument_blocks', 'instrument_sittings'] as $table) {
            \Config\Database::forge('tests')->dropTable($table, true);
        }
    }

    /** A `risk` sitting with answers for ang-21 (`risk_upset`) and ang-23 (`support_none`). */
    private function riskAnswers(\CodeIgniter\Database\BaseConnection $db, int $participantId, string $upset, string $none = '0', ?int $fieldSessionId = null, int $preview = 0): int
    {
        $db->table('instrument_sittings')->insert(['participant_id' => $participantId, 'study_id' => 1, 'field_session_id' => $fieldSessionId, 'kind' => 'risk', 'is_preview' => $preview, 'risk_offered' => 1, 'risk_reconsent' => 1]);
        $sitting = (int) $db->insertID();
        $db->table('instrument_parts')->insert(['sitting_id' => $sitting]);
        $db->table('instrument_responses')->insertBatch([
            ['sitting_id' => $sitting, 'unit' => 'risk_upset', 'response' => $upset, 'answered_at' => date('Y-m-d H:i:s')],
            ['sitting_id' => $sitting, 'unit' => 'support_none', 'response' => $none, 'answered_at' => date('Y-m-d H:i:s')],
        ]);

        return $sitting;
    }
}
