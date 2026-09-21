<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateSessionProgress extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'session_id'              => $this->ref(),
            'current_level_id'        => $this->ref(true),
            'current_node_id'         => $this->ref(true),
            'unlocked_level_sequence' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'completed_nodes'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'completed_levels'        => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'total_score'             => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '0.00'],
            'total_stars'             => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'updated_at'              => $this->datetimeNowOnUpdate(),
        ]);

        $this->forge->addPrimaryKey('session_id');
        $this->forge->addKey('updated_at');
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('current_level_id', 'levels', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('current_node_id', 'challenge_nodes', 'id', '', 'SET NULL');
        $this->createGelitaTable('session_progress');
    }

    public function down(): void
    {
        $this->dropGelitaTable('session_progress');
    }
}
