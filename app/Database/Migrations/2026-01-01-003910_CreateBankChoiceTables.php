<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Research revision 10, part 5 (31 §15): bank tables for choices that are not
 * items themselves. All four hold bank content, never student data.
 *
 * - `item_error_codes`: the error code of every wrong choice of one item:
 *   cloze blank × word (`word`, normalised Indonesian word), round-1 clue ×
 *   object (`object`, item_key of the tapped object), round-2 clue × post
 *   part (`part`, part_key). `is_main` marks the main lures of 08.
 * - `post_scenes` / `post_parts`: the *Periksa Unggahan* post of one variant
 *   of Cari Objek round 2, and its tap parts as boxes in percent of the 16:9
 *   scene (K8).
 * - `scenario_options`: actions (score 0/1/2, DIG-* code, feedback) and
 *   reasons (R-* code) of one Skenario Digital item. Read by the scenario
 *   engine of part 6 (32 §5.1).
 */
class CreateBankChoiceTables extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => $this->id(),
            'challenge_item_id' => $this->ref(false),
            'choice_type'       => ['type' => 'VARCHAR', 'constraint' => 10],
            'choice_ref'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'error_code'        => ['type' => 'VARCHAR', 'constraint' => 12],
            'is_main'           => $this->flag(0),
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['challenge_item_id', 'choice_type', 'choice_ref']);
        $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id', '', 'CASCADE');
        $this->createGelitaTable('item_error_codes');

        $this->forge->addField([
            'id'                => $this->id(),
            'challenge_node_id' => $this->ref(false),
            'variant_group'     => ['type' => 'VARCHAR', 'constraint' => 10],
            'title_id'          => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'title_en'          => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'text_genre'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'media_asset_id'    => $this->ref(true),
            'reference_source'  => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
        ] + $this->timestamps());
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['challenge_node_id', 'variant_group']);
        $this->forge->addForeignKey('challenge_node_id', 'challenge_nodes', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('media_asset_id', 'media_assets', 'id', '', 'SET NULL');
        $this->createGelitaTable('post_scenes');

        $this->forge->addField([
            'id'            => $this->id(),
            'post_scene_id' => $this->ref(false),
            'part_key'      => ['type' => 'VARCHAR', 'constraint' => 10],
            'kind'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'label_id'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'label_en'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'text_id'       => ['type' => 'TEXT', 'null' => true],
            'text_en'       => ['type' => 'TEXT', 'null' => true],
            'x'             => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'y'             => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'w'             => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'h'             => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'sequence'      => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['post_scene_id', 'part_key']);
        $this->forge->addForeignKey('post_scene_id', 'post_scenes', 'id', '', 'CASCADE');
        $this->createGelitaTable('post_parts');

        $this->forge->addField([
            'id'                => $this->id(),
            'challenge_item_id' => $this->ref(false),
            'kind'              => ['type' => 'VARCHAR', 'constraint' => 10],
            'option_key'        => ['type' => 'VARCHAR', 'constraint' => 10],
            'sequence'          => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'text_id'           => ['type' => 'TEXT'],
            'text_en'           => ['type' => 'TEXT'],
            'score'             => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'code'              => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'feedback_id'       => ['type' => 'TEXT', 'null' => true],
            'feedback_en'       => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['challenge_item_id', 'kind', 'option_key']);
        $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id', '', 'CASCADE');
        $this->createGelitaTable('scenario_options');
    }

    public function down(): void
    {
        $this->dropGelitaTable('scenario_options');
        $this->dropGelitaTable('post_parts');
        $this->dropGelitaTable('post_scenes');
        $this->dropGelitaTable('item_error_codes');
    }
}
