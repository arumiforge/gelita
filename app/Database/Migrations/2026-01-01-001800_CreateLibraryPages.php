<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateLibraryPages extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => $this->id(),
            'level_id'         => $this->ref(),
            'sequence'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'title_id'         => ['type' => 'VARCHAR', 'constraint' => 250],
            'title_en'         => ['type' => 'VARCHAR', 'constraint' => 250],
            'body_id'          => ['type' => 'LONGTEXT'],
            'body_en'          => ['type' => 'LONGTEXT'],
            'image_a_media_id' => $this->ref(true),
            'image_b_media_id' => $this->ref(true),
            'video_media_id'   => $this->ref(true),
            'poster_media_id'  => $this->ref(true),
            'is_active'        => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        // (level_id, sequence) juga berfungsi sebagai index level_id
        $this->forge->addUniqueKey(['level_id', 'sequence']);
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('level_id', 'levels', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('image_a_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('image_b_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('video_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('poster_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('library_pages');
    }

    public function down(): void
    {
        $this->dropGelitaTable('library_pages');
    }
}
