<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateGameReleases extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => $this->id(),
            'release_code'    => ['type' => 'VARCHAR', 'constraint' => 80],
            'app_version'     => ['type' => 'VARCHAR', 'constraint' => 30],
            'content_version' => ['type' => 'VARCHAR', 'constraint' => 30],
            'asset_version'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'scoring_version' => ['type' => 'VARCHAR', 'constraint' => 30],
            'published_at'    => $this->datetime(),
            'notes'           => ['type' => 'TEXT', 'null' => true],
            'is_active'       => $this->flag(0),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('release_code');
        $this->forge->addKey('published_at');
        $this->createGelitaTable('game_releases');
    }

    public function down(): void
    {
        $this->dropGelitaTable('game_releases');
    }
}
