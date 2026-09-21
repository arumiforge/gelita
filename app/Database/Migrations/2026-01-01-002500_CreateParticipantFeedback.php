<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateParticipantFeedback extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => $this->id(),
            'participant_id' => $this->ref(),
            'session_id'     => $this->ref(true),
            'rating'         => ['type' => 'TINYINT', 'unsigned' => true],
            'liked_most'     => ['type' => 'TEXT', 'null' => true],
            'hardest_part'   => ['type' => 'TEXT', 'null' => true],
            'new_learning'   => ['type' => 'TEXT', 'null' => true],
            'suggestion'     => ['type' => 'TEXT', 'null' => true],
            'submitted_at'   => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('participant_id');
        $this->forge->addKey('session_id');
        $this->forge->addKey('submitted_at');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'SET NULL');
        $this->createGelitaTable('participant_feedback');
    }

    public function down(): void
    {
        $this->dropGelitaTable('participant_feedback');
    }
}
