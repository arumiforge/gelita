<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Media Pustaka Kedu tanpa batas jumlah per halaman, masing-masing dari berkas
 * unggahan (`media_asset_id`) ATAU tautan luar (`external_url`: YouTube, Google
 * Drive, Vimeo, Wikimedia Commons, atau berkas gambar/video langsung).
 * Keterangan gambar dwibahasa dan kredit pemilik disimpan per media.
 */
class CreateLibraryMedia extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => $this->id(),
            'library_page_id' => $this->ref(false),
            'sequence'        => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'media_kind'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'image'],
            'media_asset_id'  => $this->ref(true),
            'external_url'    => ['type' => 'VARCHAR', 'constraint' => 1000, 'null' => true],
            'poster_media_id' => $this->ref(true),
            'caption_id'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'caption_en'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'credit'          => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'is_active'       => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['library_page_id', 'sequence']);
        $this->forge->addForeignKey('library_page_id', 'library_pages', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('media_asset_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('poster_media_id', 'media_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('library_media');
    }

    public function down(): void
    {
        $this->dropGelitaTable('library_media');
    }
}
