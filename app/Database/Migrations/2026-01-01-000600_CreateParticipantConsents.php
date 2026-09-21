<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateParticipantConsents extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                        => $this->id(),
            'participant_id'            => $this->ref(),
            'study_id'                  => $this->ref(true),
            'consent_version'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'participant_consented'     => $this->flag(0),
            'parent_guardian_consented' => $this->flag(0),
            'guardian_name'             => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'consent_text_snapshot'     => ['type' => 'TEXT'],
            'consented_at'              => $this->datetime(),
            'withdrawn_at'              => $this->datetime(),
            'metadata_json'             => $this->json(),
            'created_at'                => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('participant_id');
        $this->forge->addKey('consent_version');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'SET NULL');
        $this->createGelitaTable('participant_consents');
    }

    public function down(): void
    {
        $this->dropGelitaTable('participant_consents');
    }
}
