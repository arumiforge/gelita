<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateAudioAssets extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => $this->id(),
            'media_asset_id'    => $this->ref(),
            'locale'            => ['type' => 'VARCHAR', 'constraint' => 5],
            'character_code'    => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'context_code'      => ['type' => 'VARCHAR', 'constraint' => 80],
            'transcript'        => ['type' => 'LONGTEXT'],
            'production_method' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'own_recording'],
            'voice_profile'     => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'duration_ms'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approval_status'   => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'approved_by'       => $this->ref(true),
            'approved_at'       => $this->datetime(),
            'version'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1'],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('media_asset_id');
        $this->forge->addKey('locale');
        $this->forge->addKey('character_code');
        $this->forge->addKey('context_code');
        $this->forge->addKey('approval_status');
        $this->forge->addForeignKey('media_asset_id', 'media_assets', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createGelitaTable('audio_assets');
    }

    public function down(): void
    {
        $this->dropGelitaTable('audio_assets');
    }
}
