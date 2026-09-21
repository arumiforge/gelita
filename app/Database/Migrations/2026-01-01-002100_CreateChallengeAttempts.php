<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateChallengeAttempts extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                     => $this->id(),
            'session_id'             => $this->ref(),
            'challenge_node_id'      => $this->ref(),
            'attempt_no'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'status'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'in_progress'],
            'selected_item_ids_json' => $this->json(false),
            'scorable_items'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'first_pass_correct'     => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'final_correct'          => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'first_pass_rate'        => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => '0.00'],
            'final_rate'             => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => '0.00'],
            'hint_count'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'retry_count'            => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'independence'           => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => '100.00'],
            'score'                  => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '0.00'],
            'stars'                  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'scoring_profile_id'     => $this->ref(true),
            'scoring_version'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'started_at'             => $this->datetimeNow(),
            'completed_at'           => $this->datetime(),
            'duration_ms'            => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'             => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        // juga berfungsi sebagai index session_id
        $this->forge->addUniqueKey(['session_id', 'challenge_node_id', 'attempt_no']);
        // juga berfungsi sebagai index challenge_node_id
        $this->forge->addKey(['challenge_node_id', 'status']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('challenge_node_id', 'challenge_nodes', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('scoring_profile_id', 'scoring_profiles', 'id', '', 'SET NULL');
        $this->createGelitaTable('challenge_attempts');
    }

    public function down(): void
    {
        $this->dropGelitaTable('challenge_attempts');
    }
}
