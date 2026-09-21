<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateChallengeOptions extends GelitaMigration
{
    public function up(): void
    {
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
        // (challenge_item_id, option_key) juga berfungsi sebagai index challenge_item_id
        $this->forge->addUniqueKey(['challenge_item_id', 'option_key']);
        $this->forge->addKey('display_order');
        $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('media_asset_id', 'media_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('challenge_options');
    }

    public function down(): void
    {
        $this->dropGelitaTable('challenge_options');
    }
}
