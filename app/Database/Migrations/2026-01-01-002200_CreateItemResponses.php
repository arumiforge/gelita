<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateItemResponses extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => $this->id(),
            'challenge_attempt_id' => $this->ref(),
            'challenge_item_id'    => $this->ref(),
            'display_order'        => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'first_answer_json'    => $this->json(),
            'final_answer_json'    => $this->json(),
            'reason_text'          => ['type' => 'TEXT', 'null' => true],
            'reason_review_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'first_pass_correct'   => $this->nullableFlag(),
            'is_correct'           => $this->nullableFlag(),
            'change_count'         => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'hint_used'            => $this->flag(0),
            'wrong_click_count'    => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'started_at'           => $this->datetime(),
            'answered_at'          => $this->datetime(),
            'duration_ms'          => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        // juga berfungsi sebagai index challenge_attempt_id
        $this->forge->addUniqueKey(['challenge_attempt_id', 'challenge_item_id']);
        $this->forge->addKey('challenge_item_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('challenge_attempt_id', 'challenge_attempts', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id', '', 'RESTRICT');
        $this->createGelitaTable('item_responses');
    }

    public function down(): void
    {
        $this->dropGelitaTable('item_responses');
    }
}
