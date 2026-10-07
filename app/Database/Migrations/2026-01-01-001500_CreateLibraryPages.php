<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Halaman Pustaka Kedu per wilayah. Gambar dan video halaman ada di
 * `library_media`.
 */
class CreateLibraryPages extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => $this->id(),
            'level_id'   => $this->ref(false),
            'sequence'   => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'title_id'   => ['type' => 'VARCHAR', 'constraint' => 250],
            'title_en'   => ['type' => 'VARCHAR', 'constraint' => 250],
            'body_id'    => ['type' => 'LONGTEXT'],
            'body_en'    => ['type' => 'LONGTEXT'],
            'is_active'  => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['level_id', 'sequence']);
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('level_id', 'levels', 'id', '', 'CASCADE');
        $this->createGelitaTable('library_pages');
    }

    public function down(): void
    {
        $this->dropGelitaTable('library_pages');
    }
}
