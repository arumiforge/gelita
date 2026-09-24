<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Media Pustaka Kedu tanpa batas jumlah per halaman.
 *
 * `library_pages` hanya punya empat slot tetap (2 gambar, 1 video, 1 poster).
 * Tabel ini menampung sebanyak apa pun gambar/video per halaman, masing-masing
 * dari berkas unggahan (`media_asset_id`) ATAU tautan luar (`external_url`:
 * YouTube, Google Drive, Vimeo, Wikimedia Commons, atau berkas gambar/video
 * langsung). Keterangan gambar dwibahasa dan kredit pemilik disimpan per media.
 *
 * Isi empat slot lama disalin ke sini sekali; kolom lama tidak dihapus
 * sehingga rollback tidak kehilangan data.
 */
class CreateLibraryMedia extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => $this->id(),
            'library_page_id' => $this->ref(),
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

        $this->copyLegacySlots();
    }

    public function down(): void
    {
        $this->dropGelitaTable('library_media');
    }

    /** Slot lama image_a, image_b, video (+ poster) → baris library_media. */
    private function copyLegacySlots(): void
    {
        $pages = $this->db->table('library_pages')
            ->select('id, image_a_media_id, image_b_media_id, video_media_id, poster_media_id')
            ->get()
            ->getResultArray();

        foreach ($pages as $page) {
            $sequence = 0;

            foreach (['image_a_media_id' => 'image', 'image_b_media_id' => 'image', 'video_media_id' => 'video'] as $column => $kind) {
                if ($page[$column] === null) {
                    continue;
                }

                $this->db->table('library_media')->insert([
                    'library_page_id' => $page['id'],
                    'sequence'        => ++$sequence,
                    'media_kind'      => $kind,
                    'media_asset_id'  => $page[$column],
                    'poster_media_id' => $kind === 'video' ? $page['poster_media_id'] : null,
                    'is_active'       => 1,
                ]);
            }
        }
    }
}
