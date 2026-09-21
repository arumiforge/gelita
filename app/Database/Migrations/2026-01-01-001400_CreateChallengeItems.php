<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateChallengeItems extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => $this->id(),
            'challenge_node_id' => $this->ref(),
            'item_key'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'sequence'          => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'interaction_type'  => ['type' => 'VARCHAR', 'constraint' => 40],
            'prompt_id'         => ['type' => 'TEXT', 'null' => true],
            'prompt_en'         => ['type' => 'TEXT', 'null' => true],
            'source_text_id'    => ['type' => 'LONGTEXT', 'null' => true],
            'source_text_en'    => ['type' => 'LONGTEXT', 'null' => true],
            'passage_id'        => $this->ref(true),
            'answer_key_json'   => $this->json(),
            'media_asset_id'    => $this->ref(true),
            'indicator_id'      => $this->ref(true),
            'config_json'       => $this->json(),
            'reference_source'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'review_status'     => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'review_note'       => ['type' => 'TEXT', 'null' => true],
            'scorable'          => $this->flag(1),
            'is_active'         => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        // (challenge_node_id, item_key) juga berfungsi sebagai index challenge_node_id
        $this->forge->addUniqueKey(['challenge_node_id', 'item_key']);
        $this->forge->addKey('sequence');
        $this->forge->addKey('interaction_type');
        $this->forge->addKey('review_status');
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('challenge_node_id', 'challenge_nodes', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('passage_id', 'reading_passages', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('media_asset_id', 'media_assets', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('indicator_id', 'learning_indicators', 'id', '', 'SET NULL');
        $this->createGelitaTable('challenge_items');
    }

    public function down(): void
    {
        $this->dropGelitaTable('challenge_items');
    }
}
