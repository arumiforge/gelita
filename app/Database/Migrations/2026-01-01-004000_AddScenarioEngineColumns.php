<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Research revision 10, part 6 (docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md §11):
 * the Skenario Digital engine and the instruction narration.
 *
 * - `item_tries`: the action and reason of one scenario try (`action_key`,
 *   `action_score`, `reason_key`, `reason_code`) and its client times in ms
 *   since the challenge opened (`reason_ready_ms`, `action_last_input_ms`,
 *   `reason_last_input_ms`, §6.4). `ready_ms` belongs to part 3 (migration
 *   003600) and is not created here (§11).
 * - `challenge_nodes`: `audio_instruction_id` / `audio_instruction_en_id`,
 *   the instruction narration (context `challenge_instruction`, §8.2).
 *
 * Every new column is nullable, so existing rows stay valid.
 */
class AddScenarioEngineColumns extends GelitaMigration
{
    private const TRY_COLUMNS = ['action_key', 'action_score', 'reason_key', 'reason_code', 'reason_ready_ms', 'action_last_input_ms', 'reason_last_input_ms'];

    public function up(): void
    {
        $this->forge->addColumn('item_tries', [
            'action_key'           => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'action_score'         => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'reason_key'           => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'reason_code'          => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'reason_ready_ms'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action_last_input_ms' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'reason_last_input_ms' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('reason_code', false, false, 'item_tries_reason_code');
        $this->forge->processIndexes('item_tries');

        $this->forge->addColumn('challenge_nodes', [
            'audio_instruction_id'    => $this->ref(true) + ['after' => 'audio_intro_en_id'],
            'audio_instruction_en_id' => $this->ref(true) + ['after' => 'audio_instruction_id'],
        ]);
        $this->forge->addForeignKey('audio_instruction_id', 'audio_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('audio_instruction_en_id', 'audio_assets', 'id', '', 'SET NULL');
        $this->forge->processIndexes('challenge_nodes');
    }

    public function down(): void
    {
        foreach ($this->db->getForeignKeyData('challenge_nodes') as $key) {
            if (array_intersect((array) $key->column_name, ['audio_instruction_id', 'audio_instruction_en_id']) !== []) {
                $this->forge->dropForeignKey('challenge_nodes', $key->constraint_name);
            }
        }

        $this->forge->dropColumn('challenge_nodes', ['audio_instruction_id', 'audio_instruction_en_id']);
        $this->forge->dropKey('item_tries', 'item_tries_reason_code', false);
        $this->forge->dropColumn('item_tries', self::TRY_COLUMNS);
    }
}
