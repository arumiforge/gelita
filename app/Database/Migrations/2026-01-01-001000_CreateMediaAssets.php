<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateMediaAssets extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => $this->id(),
            'asset_key'    => ['type' => 'VARCHAR', 'constraint' => 160],
            'asset_type'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'storage_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'mime_type'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'sha256'       => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'width_px'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'height_px'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'locale'       => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'credit'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'version'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1'],
            'is_active'    => $this->flag(1),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('asset_key');
        $this->forge->addKey('asset_type');
        $this->forge->addKey('sha256');
        $this->createGelitaTable('media_assets');
    }

    public function down(): void
    {
        $this->dropGelitaTable('media_assets');
    }
}
