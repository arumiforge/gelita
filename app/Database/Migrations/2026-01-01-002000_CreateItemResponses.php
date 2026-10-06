<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Jawaban akhir per soal dalam satu pengerjaan. `try_count` = jumlah
 * percobaan soal itu; `solved_on_try` = percobaan ke berapa soal itu benar.
 */
class CreateItemResponses extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => $this->id(),
            'challenge_attempt_id' => $this->ref(false),
            'challenge_item_id'    => $this->ref(false),
            'display_order'        => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'first_answer_json'    => $this->json(true),
            'final_answer_json'    => $this->json(true),
            'first_pass_correct'   => $this->nullableFlag(),
            'is_correct'           => $this->nullableFlag(),
            'change_count'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'try_count'            => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'solved_on_try'        => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'hint_used'            => $this->flag(0),
            'wrong_click_count'    => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'started_at'           => $this->datetime(true),
            'answered_at'          => $this->datetime(true),
            'duration_ms'          => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['challenge_attempt_id', 'challenge_item_id']);
        $this->forge->addKey('challenge_item_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('challenge_attempt_id', 'challenge_attempts', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id');
        $this->createGelitaTable('item_responses');
    }

    public function down(): void
    {
        $this->dropGelitaTable('item_responses');
    }
}
