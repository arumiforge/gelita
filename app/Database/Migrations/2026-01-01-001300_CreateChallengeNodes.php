<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateChallengeNodes extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => $this->id(),
            'level_id'            => $this->ref(),
            'sequence'            => ['type' => 'TINYINT', 'unsigned' => true],
            'engine_type'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'variant_code'        => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'title_id'            => ['type' => 'VARCHAR', 'constraint' => 250],
            'title_en'            => ['type' => 'VARCHAR', 'constraint' => 250],
            'instruction_id'      => ['type' => 'TEXT', 'null' => true],
            'instruction_en'      => ['type' => 'TEXT', 'null' => true],
            'description_id'      => ['type' => 'TEXT', 'null' => true],
            'description_en'      => ['type' => 'TEXT', 'null' => true],
            'indicator_id'        => $this->ref(true),
            'scoring_profile_id'  => $this->ref(true),
            'background_media_id' => $this->ref(true),
            'scene_media_id'      => $this->ref(true),
            'audio_intro_id'      => $this->ref(true),
            'audio_intro_en_id'   => $this->ref(true),
            'config_json'         => $this->json(),
            'map_x'               => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '50.00'],
            'map_y'               => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '50.00'],
            'content_version'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1'],
            'is_active'           => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        // (level_id, sequence) juga berfungsi sebagai index level_id
        $this->forge->addUniqueKey(['level_id', 'sequence']);
        $this->forge->addKey('engine_type');
        $this->forge->addKey('variant_code');
        $this->forge->addKey('content_version');
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('level_id', 'levels', 'id', '', 'RESTRICT');
        $this->forge->addForeignKey('indicator_id', 'learning_indicators', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('scoring_profile_id', 'scoring_profiles', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('background_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('scene_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('audio_intro_id', 'audio_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('audio_intro_en_id', 'audio_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('challenge_nodes');
    }

    public function down(): void
    {
        $this->dropGelitaTable('challenge_nodes');
    }
}
