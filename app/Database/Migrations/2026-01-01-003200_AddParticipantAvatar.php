<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Display plan Phase B (docs/rencana-perbaikan-tampilan.md): the avatar a
 * student picks at sign-up or in the profile, as the number N of the slot
 * `avatar.N`. NULL = the initial of the name (today's look).
 *
 * Not research data: never exported and never logged as an event.
 */
class AddParticipantAvatar extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addColumn('participants', [
            'avatar_no' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'class_level'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('participants', 'avatar_no');
    }
}
