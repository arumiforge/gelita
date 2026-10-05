<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Perombakan kelas 5 · Dok 1 §5.5 — soal dikelompokkan per variasi
 * (`a`, `b`, `c`); setiap pengerjaan memakai satu variasi acak. Tabel
 * `challenge_options` (hanya untuk kuis pilihan yang dihapus) dibuang.
 */
class AddVariantGroupsDropOptions extends GelitaMigration
{
    private const INDEX = 'challenge_items_node_variant_active';

    public function up(): void
    {
        $this->addColumnsIfMissing('challenge_items', [
            'variant_group' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'sequence'],
        ]);
        $this->addIndexIfMissing('challenge_items', self::INDEX, ['challenge_node_id', 'variant_group', 'is_active']);

        $this->dropGelitaTable('challenge_options');
    }

    public function down(): void
    {
        $this->db->resetDataCache();

        if (! $this->db->tableExists('challenge_options')) {
            $this->forge->addField([
                'id'                => $this->id(),
                'challenge_item_id' => $this->ref(),
                'option_key'        => ['type' => 'VARCHAR', 'constraint' => 60],
                'label_id'          => ['type' => 'TEXT'],
                'label_en'          => ['type' => 'TEXT'],
                'media_asset_id'    => $this->ref(true),
                'is_correct'        => $this->flag(0),
                'feedback_id'       => ['type' => 'TEXT', 'null' => true],
                'feedback_en'       => ['type' => 'TEXT', 'null' => true],
                'display_order'     => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['challenge_item_id', 'option_key']);
            $this->forge->addKey('display_order');
            $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id', '', 'CASCADE');
            $this->forge->addForeignKey('media_asset_id', 'media_assets', 'id', '', 'SET NULL');
            $this->createGelitaTable('challenge_options');
        }

        if ($this->db->fieldExists('variant_group', 'challenge_items')) {
            $this->dropIndexIfExists('challenge_items', self::INDEX);
            $this->dropColumnsIfExist('challenge_items', ['variant_group']);
        }
    }
}
