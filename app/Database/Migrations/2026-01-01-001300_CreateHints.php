<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Minimal salah satu dari challenge_node_id / challenge_item_id wajib terisi.
 * Aturan ini divalidasi di Model (tanpa CHECK constraint, demi MariaDB lama).
 */
class CreateHints extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => $this->id(),
            'challenge_node_id' => $this->ref(true),
            'challenge_item_id' => $this->ref(true),
            'sequence'          => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'text_id'           => ['type' => 'TEXT'],
            'text_en'           => ['type' => 'TEXT'],
            'is_active'         => $this->flag(1),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('challenge_node_id');
        $this->forge->addKey('challenge_item_id');
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('challenge_node_id', 'challenge_nodes', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('challenge_item_id', 'challenge_items', 'id', '', 'CASCADE');
        $this->createGelitaTable('hints');
    }

    public function down(): void
    {
        $this->dropGelitaTable('hints');
    }
}
