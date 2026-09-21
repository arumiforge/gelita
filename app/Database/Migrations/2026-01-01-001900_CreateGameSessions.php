<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateGameSessions extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                     => $this->id(),
            'session_code'           => ['type' => 'VARCHAR', 'constraint' => 64],
            'participant_id'         => $this->ref(),
            'study_id'               => $this->ref(),
            'phase_id'               => $this->ref(),
            'release_id'             => $this->ref(),
            'locale'                 => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => 'id'],
            'status'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'started_at'             => $this->datetimeNow(),
            'last_active_at'         => $this->datetimeNow(),
            'ended_at'               => $this->datetime(),
            'duration_ms'            => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 0],
            'last_level_id'          => $this->ref(true),
            'last_challenge_node_id' => $this->ref(true),
            'device_type'            => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'os_name'                => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'browser_name'           => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'screen_size'            => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_touch'               => $this->nullableFlag(),
            'app_client_version'     => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'ip_hash'                => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'created_at'             => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('session_code');
        // juga berfungsi sebagai index participant_id
        $this->forge->addKey(['participant_id', 'study_id', 'phase_id', 'status']);
        $this->forge->addKey('study_id');
        $this->forge->addKey('phase_id');
        $this->forge->addKey('status');
        $this->forge->addKey('started_at');
        $this->forge->addKey('last_active_at');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('phase_id', 'research_phases', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('release_id', 'game_releases', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('last_level_id', 'levels', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('last_challenge_node_id', 'challenge_nodes', 'id', '', 'SET NULL');
        $this->createGelitaTable('game_sessions');
    }

    public function down(): void
    {
        $this->dropGelitaTable('game_sessions');
    }
}
