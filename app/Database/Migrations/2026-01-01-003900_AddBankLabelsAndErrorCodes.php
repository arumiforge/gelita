<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Research revision 10, part 5 (docs/revisi-10-analitik/31_IMPLEMENTASI_BANK_SOAL_BARU.md §15):
 * labels, rounds, error codes, and surface load on the question bank, plus
 * the error code of every scored try and the variant source of every attempt.
 *
 * - `challenge_items`: `round` (Cari Objek 1 or 2), developer/expert labels
 *   (`dimension`, `subaspect`, `akm_level` + source, `pirls_process`,
 *   `bloom_level_source`), `testlet_key`, `error_code` (Benar atau Salah
 *   statements and round-1 decoys), `d2_topic`, `d3_clue_type`,
 *   `explanation_*` (Skenario Digital), `surface_json` (SurfaceLoad, frozen
 *   at import).
 * - `reading_passages`: `text_genre`, `layout` (display template of a digital text).
 * - `item_tries`: `error_code` (derived by the server when the try is
 *   graded, never by the client) and `manual_code` (filled by the L5 import
 *   of part 3 only).
 * - `challenge_attempts`: `variant_source` (`booklet`, `random`, `retake`)
 *   and `booklet_at_open` (B1–B4 or NULL).
 *
 * Every new column is nullable, so existing rows stay valid.
 */
class AddBankLabelsAndErrorCodes extends GelitaMigration
{
    private const ITEM_COLUMNS = [
        'round', 'dimension', 'subaspect', 'akm_level', 'akm_level_source', 'pirls_process', 'bloom_level_source',
        'testlet_key', 'error_code', 'd2_topic', 'd3_clue_type', 'explanation_id', 'explanation_en', 'surface_json',
    ];

    public function up(): void
    {
        $this->forge->addColumn('challenge_items', [
            'round'              => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'dimension'          => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'subaspect'          => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'akm_level'          => ['type' => 'VARCHAR', 'constraint' => 2, 'null' => true],
            'akm_level_source'   => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'pirls_process'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'bloom_level_source' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'testlet_key'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'error_code'         => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'd2_topic'           => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'd3_clue_type'       => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'explanation_id'     => ['type' => 'TEXT', 'null' => true],
            'explanation_en'     => ['type' => 'TEXT', 'null' => true],
            'surface_json'       => $this->json(true),
        ]);
        $this->forge->addKey(['challenge_node_id', 'variant_group', 'round'], false, false, 'challenge_items_node_variant_round');
        $this->forge->processIndexes('challenge_items');

        $this->forge->addColumn('reading_passages', [
            'text_genre' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'layout'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ]);

        $this->forge->addColumn('item_tries', [
            'error_code'  => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'manual_code' => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
        ]);
        $this->forge->addKey('error_code', false, false, 'item_tries_error_code');
        $this->forge->processIndexes('item_tries');

        $this->forge->addColumn('challenge_attempts', [
            'variant_source'  => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'booklet_at_open' => ['type' => 'CHAR', 'constraint' => 2, 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropKey('item_tries', 'item_tries_error_code', false);
        $this->forge->dropColumn('item_tries', ['error_code', 'manual_code']);
        $this->forge->dropKey('challenge_items', 'challenge_items_node_variant_round', false);
        $this->forge->dropColumn('challenge_items', self::ITEM_COLUMNS);
        $this->forge->dropColumn('reading_passages', ['text_genre', 'layout']);
        $this->forge->dropColumn('challenge_attempts', ['variant_source', 'booklet_at_open']);
    }
}
