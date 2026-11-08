<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * The log tables before 003600, and a small set of old rows (shared with
 * LogCountCommandTest).
 */
trait OldLogShape
{
    private function oldLogShape(BaseConnection $db): void
    {
        $forge = Database::forge('tests');
        $forge->dropColumn('item_tries', 'ready_ms');
        $forge->dropColumn('data_exports', ['dictionary_version', 'participant_count']);
        $forge->addColumn('device_logs', [
            'user_agent'        => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'device_brand'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'device_model'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'os_platform'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'client_hints_json' => ['type' => 'TEXT', 'null' => true],
            'pixel_ratio'       => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'touch_points'      => ['type' => 'INTEGER', 'null' => true],
            'language'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'timezone'          => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
        ]);
    }

    private function seedOldLogs(BaseConnection $db): void
    {
        $n   = 0;
        $add = static function (int $session, ?int $level, string $type, array $payload, string $at, int $seq) use ($db, &$n): void {
            $n++;
            $db->table('game_event_logs')->insert([
                'event_uuid' => sprintf('00000000-0000-4000-8000-%012d', $n), 'client_event_id' => 'c' . $n,
                'session_id' => $session, 'participant_id' => $session, 'level_id' => $level, 'event_type' => $type,
                'sequence_no' => $seq, 'occurred_at' => $at, 'payload_json' => json_encode($payload),
            ]);
        };

        // Session 1, region 1: slides 1–4, then the story again to the end (only the first last slide counts)
        foreach ([1, 2, 3, 4] as $i) {
            $add(1, 1, 'dialogue_advanced', ['index' => $i, 'total' => 4, 'context' => 'region_intro'], '2026-10-01 08:00:0' . $i, $i);
        }
        $add(1, 1, 'dialogue_advanced', ['index' => 4, 'total' => 4, 'context' => 'region_intro'], '2026-10-01 09:00:00', 9);
        $add(1, 2, 'dialogue_advanced', ['index' => 4, 'total' => 4, 'context' => 'region_intro'], '2026-10-01 10:00:00', 10);
        // Session 2, region 1, written with spaces (MySQL 8 JSON)
        $db->table('game_event_logs')->insert([
            'event_uuid' => '00000000-0000-4000-8000-000000000099', 'client_event_id' => 'c99', 'session_id' => 2, 'participant_id' => 2,
            'level_id' => 1, 'event_type' => 'dialogue_advanced', 'sequence_no' => 1, 'occurred_at' => '2026-10-02 08:00:00',
            'payload_json' => '{"index": 4, "total": 4, "context": "region_intro"}',
        ]);
        $add(2, 2, 'dialogue_advanced', ['index' => 2, 'total' => 4, 'context' => 'region_intro'], '2026-10-02 08:05:00', 2);

        $add(1, null, 'answer_selected', ['action' => 'set'], '2026-10-01 08:10:00', 11);
        $add(1, null, 'answer_selected', ['action' => 'set'], '2026-10-01 08:10:01', 12);
        $add(1, null, 'audio_pause', ['listened_ms' => 900], '2026-10-01 08:10:02', 13);
        $add(1, null, 'answer_submitted', ['try_no' => 1, 'correct' => false], '2026-10-01 08:11:00', 14);
        $add(1, null, 'answer_changed', ['try_no' => 2, 'correct' => true], '2026-10-01 08:11:05', 15);
        $add(1, null, 'hint_opened', [], '2026-10-01 08:12:00', 16);

        $db->table('device_logs')->insert(['session_id' => 1, 'participant_id' => 1, 'via' => 'login', 'os_name' => 'Android', 'user_agent' => 'Mozilla/5.0', 'device_brand' => 'Samsung']);
        $db->table('device_logs')->insert(['session_id' => 2, 'participant_id' => 2, 'via' => 'login', 'os_name' => 'Windows']);
    }
}
