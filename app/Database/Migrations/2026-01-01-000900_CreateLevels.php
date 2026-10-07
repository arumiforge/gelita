<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Tiga wilayah (Temanggung, Magelang, Wonosobo) dengan tingkat kesulitan,
 * tujuan pembelajaran, dan media peta/latar/lencana.
 */
class CreateLevels extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => $this->id(),
            'sequence'            => ['type' => 'TINYINT', 'unsigned' => true],
            'code'                => ['type' => 'VARCHAR', 'constraint' => 40],
            'name_id'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'name_en'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'difficulty'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'focus_id'            => ['type' => 'TEXT'],
            'focus_en'            => ['type' => 'TEXT'],
            'cp_id'               => ['type' => 'TEXT', 'null' => true],
            'cp_en'               => ['type' => 'TEXT', 'null' => true],
            'tp_id'               => ['type' => 'TEXT', 'null' => true],
            'tp_en'               => ['type' => 'TEXT', 'null' => true],
            'intro_id'            => ['type' => 'TEXT', 'null' => true],
            'intro_en'            => ['type' => 'TEXT', 'null' => true],
            'map_media_id'        => $this->ref(true),
            'background_media_id' => $this->ref(true),
            'badge_media_id'      => $this->ref(true),
            'map_x'               => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '50.00'],
            'map_y'               => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '50.00'],
            'is_active'           => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('sequence');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('difficulty');
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('map_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('background_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('badge_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('levels');
    }

    public function down(): void
    {
        $this->dropGelitaTable('levels');
    }
}
