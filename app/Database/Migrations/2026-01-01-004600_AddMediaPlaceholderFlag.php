<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Perombakan kelas 5 · Dok 1 §5.7 — penanda gambar contoh (mockup) buatan
 * sistem, plus nama pendek dan deskripsi isi gambar yang diharapkan dari bank
 * soal. Unggahan admin selalu `is_placeholder = 0`.
 */
class AddMediaPlaceholderFlag extends GelitaMigration
{
    private const INDEX = 'media_assets_is_placeholder';

    public function up(): void
    {
        $this->addColumnsIfMissing('media_assets', [
            'is_placeholder' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0, 'after' => 'is_active'],
            'label'          => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'credit'],
            'description'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'label'],
        ]);
        $this->addIndexIfMissing('media_assets', self::INDEX, ['is_placeholder']);
    }

    public function down(): void
    {
        $this->db->resetDataCache();

        if ($this->db->fieldExists('is_placeholder', 'media_assets')) {
            $this->dropIndexIfExists('media_assets', self::INDEX);
        }

        $this->dropColumnsIfExist('media_assets', ['is_placeholder', 'label', 'description']);
    }
}
