<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;
use App\Libraries\ResearchLogCleanup;

/**
 * Research revision 10, part 3 (docs/revisi-10-analitik/29_IMPLEMENTASI_DATA_PENELITIAN.md
 * §5.8, §12, K3): log cleanup and the new raw-data columns.
 *
 * up():
 * 1–3. ResearchLogCleanup::run(): `dialogue_completed` made from the
 *      `dialogue_advanced` rows that reached the last slide; `answer_changed`
 *      renamed to `answer_submitted` with `changed: true`; `answer_selected`,
 *      `dialogue_advanced`, and `audio_pause` rows deleted (chunks of 2,000 ids);
 * 4. the device_logs columns marked "Dibuang" in 29 §5.5 are dropped;
 * 5. new columns: `item_tries.ready_ms`, `data_exports.dictionary_version`,
 *    `data_exports.participant_count`;
 * 6. one audit row `research_log_cleanup` (no actor, `via: migration`) with
 *    the count of every step.
 *
 * No guard: the owner decided on 3 November 2026 that this migration always
 * runs, like 003400 (the production server holds no study data yet). Count
 * the rows first with `php spark gelita:log:hitung`. THE DELETION IS PERMANENT.
 *
 * down() brings back the dropped columns (empty), drops the new columns, and
 * turns the migration's `dialogue_completed` rows back into one
 * `dialogue_advanced` each. The per-slide events, `answer_selected`,
 * `audio_pause`, and the content of the device columns do not come back;
 * renamed `answer_changed` rows stay `answer_submitted`.
 */
class CleanResearchLogs extends GelitaMigration
{
    public function up(): void
    {
        $cleanup = new ResearchLogCleanup($this->db);
        $before  = $cleanup->count();
        $result  = $cleanup->run();

        $present = array_values(array_intersect(ResearchLogCleanup::DROPPED_DEVICE_COLUMNS, $this->db->getFieldNames('device_logs')));

        if ($present !== []) {
            $this->forge->dropColumn('device_logs', $present);
        }

        $this->forge->addColumn('item_tries', [
            'ready_ms' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addColumn('data_exports', [
            'dictionary_version' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'participant_count'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);

        $this->db->table('audit_logs')->insert([
            'staff_user_id' => null,
            'action'        => 'research_log_cleanup',
            'target_type'   => 'game_event_logs',
            'metadata_json' => json_encode([
                'via'                      => 'migration',
                'dialogue_completed'       => $result['dialogue_completed'],
                'answer_changed_renamed'   => $result['answer_changed'],
                'deleted'                  => $result['deleted'],
                'device_logs_with_details' => $before['device_logs_with_details'],
                'device_columns_dropped'   => $present,
            ], JSON_UNESCAPED_UNICODE),
            'occurred_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('data_exports', ['dictionary_version', 'participant_count']);
        $this->forge->dropColumn('item_tries', ['ready_ms']);
        $this->forge->addColumn('device_logs', [
            'user_agent'        => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true, 'after' => 'via'],
            'device_brand'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'device_model'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'os_platform'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'client_hints_json' => $this->json(true),
            'pixel_ratio'       => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'touch_points'      => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'language'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'timezone'          => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
        ]);

        $rows = $this->db->table('game_event_logs')->select('id, payload_json')
            ->where('event_type', 'dialogue_completed')->like('client_event_id', 'mig-dc-', 'after')
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $payload = json_decode((string) $row['payload_json'], true) ?: [];
            $total   = (int) ($payload['total'] ?? 0);

            $this->db->table('game_event_logs')->where('id', $row['id'])->update([
                'event_type'   => 'dialogue_advanced',
                'payload_json' => json_encode(['index' => $total, 'total' => $total, 'context' => $payload['context'] ?? null], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
}
