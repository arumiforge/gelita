<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * level_id NULL = cerita pembuka/penutup global. MySQL/MariaDB mengizinkan
 * NULL berulang pada UNIQUE, jadi keunikan dialog global dijaga seeder/Model.
 */
class CreateDialogues extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => $this->id(),
            'level_id'            => $this->ref(true),
            'context_code'        => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'level_open'],
            'sequence'            => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'character_code'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'title_id'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'title_en'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'text_id'             => ['type' => 'LONGTEXT'],
            'text_en'             => ['type' => 'LONGTEXT'],
            'audio_id_asset_id'   => $this->ref(true),
            'audio_en_asset_id'   => $this->ref(true),
            'background_media_id' => $this->ref(true),
            'is_active'           => $this->flag(1),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['level_id', 'context_code', 'sequence']);
        $this->forge->addKey('context_code');
        $this->forge->addKey('sequence');
        $this->forge->addForeignKey('level_id', 'levels', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('audio_id_asset_id', 'audio_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('audio_en_asset_id', 'audio_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('background_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('dialogues');
    }

    public function down(): void
    {
        $this->dropGelitaTable('dialogues');
    }
}
