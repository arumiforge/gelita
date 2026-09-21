<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateAudioUsageEvents extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => $this->id(),
            'session_id'           => $this->ref(),
            'challenge_attempt_id' => $this->ref(true),
            'audio_asset_id'       => $this->ref(),
            'action'               => ['type' => 'VARCHAR', 'constraint' => 20],
            'play_index'           => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'listened_ms'          => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'completed'            => $this->flag(0),
            'occurred_at'          => $this->datetimeNow(),
            'server_received_at'   => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        // juga berfungsi sebagai index session_id
        $this->forge->addKey(['session_id', 'occurred_at']);
        $this->forge->addKey('challenge_attempt_id');
        $this->forge->addKey('audio_asset_id');
        $this->forge->addKey('occurred_at');
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('challenge_attempt_id', 'challenge_attempts', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('audio_asset_id', 'audio_assets', 'id', '', 'RESTRICT');
        $this->createGelitaTable('audio_usage_events');
    }

    public function down(): void
    {
        $this->dropGelitaTable('audio_usage_events');
    }
}
