<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Satu pengerjaan tantangan. `variant_group` = variasi terpilih;
 * `max_tries` disalin dari Config\Gelita::$maxTries saat dibuka;
 * `completion_reason` = solved | tries_exhausted.
 */
class CreateChallengeAttempts extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                     => $this->id(),
            'session_id'             => $this->ref(false),
            'challenge_node_id'      => $this->ref(false),
            'attempt_no'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'variant_group'          => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'status'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'in_progress'],
            'completion_reason'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'selected_item_ids_json' => $this->json(false),
            'scorable_items'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'first_pass_correct'     => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'final_correct'          => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'check_count'            => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'max_tries'              => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 3],
            'hint_count'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'answer_change_count'    => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'audio_use_count'        => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'stars'                  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'started_at'             => $this->datetimeNow(),
            'completed_at'           => $this->datetime(true),
            'duration_ms'            => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'             => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['session_id', 'challenge_node_id', 'attempt_no']);
        $this->forge->addKey(['challenge_node_id', 'status']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('challenge_node_id', 'challenge_nodes', 'id');
        $this->createGelitaTable('challenge_attempts');
    }

    public function down(): void
    {
        $this->dropGelitaTable('challenge_attempts');
    }
}
