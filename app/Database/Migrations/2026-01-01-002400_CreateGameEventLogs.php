<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Tabel tulis-berat. Index tunggal yang sudah tercakup prefix index
 * komposit (session_id, participant_id, challenge_node_id, event_type)
 * sengaja tidak dibuat ulang.
 * participant_id, level_id, challenge_*_id tanpa FK agar log tetap
 * utuh untuk audit.
 */
class CreateGameEventLogs extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => $this->id(),
            'event_uuid'           => ['type' => 'CHAR', 'constraint' => 36],
            'client_event_id'      => ['type' => 'VARCHAR', 'constraint' => 120],
            'session_id'           => $this->ref(),
            'participant_id'       => $this->ref(),
            'level_id'             => $this->ref(true),
            'challenge_node_id'    => $this->ref(true),
            'challenge_attempt_id' => $this->ref(true),
            'challenge_item_id'    => $this->ref(true),
            'event_type'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'sequence_no'          => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 0],
            'occurred_at'          => $this->datetime(false),
            'server_received_at'   => $this->datetimeNow(),
            'payload_json'         => $this->json(),
            'deleted_at'           => $this->datetime(),
            'deleted_by'           => $this->ref(true),
            'delete_reason'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('event_uuid');
        $this->forge->addUniqueKey(['session_id', 'client_event_id']);
        $this->forge->addKey(['session_id', 'sequence_no']);
        $this->forge->addKey(['participant_id', 'event_type', 'occurred_at']);
        $this->forge->addKey(['challenge_node_id', 'event_type', 'occurred_at']);
        $this->forge->addKey(['event_type', 'occurred_at']);
        $this->forge->addKey('level_id');
        $this->forge->addKey('challenge_attempt_id');
        $this->forge->addKey('challenge_item_id');
        $this->forge->addKey('deleted_at');
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('deleted_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createGelitaTable('game_event_logs');
    }

    public function down(): void
    {
        $this->dropGelitaTable('game_event_logs');
    }
}
