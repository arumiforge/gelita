<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * `item_tries`: satu baris = satu percobaan satu soal (tekan "Cek jawabanku",
 * atau satu ketuk benda pada Cari Objek). Data mentah utama dimensi
 * correctness, attempts, dan time. Hanya ditambah (insert-only).
 */
class CreateItemTries extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => $this->id(),
            'challenge_attempt_id' => $this->ref(false),
            'challenge_item_id'    => $this->ref(false),
            'try_no'               => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'check_no'             => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'answer_json'          => $this->json(false),
            'is_correct'           => $this->flag(0),
            'hint_opened_before'   => $this->flag(0),
            'first_input_ms'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'last_input_ms'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'occurred_at'          => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['challenge_attempt_id', 'challenge_item_id', 'try_no']);
        $this->forge->addKey(['challenge_item_id', 'is_correct']);
        $this->forge->addForeignKey('challenge_attempt_id', 'challenge_attempts', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id');
        $this->createGelitaTable('item_tries');
    }

    public function down(): void
    {
        $this->dropGelitaTable('item_tries');
    }
}
