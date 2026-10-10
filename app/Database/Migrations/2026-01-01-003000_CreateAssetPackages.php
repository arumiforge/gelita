<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Asset packages (docs/rencana-paket-aset.md §5.4): one row per package built
 * by `gelita:paket:bangun` or the admin panel. The files themselves live in
 * public/assets/p/ (content-addressed media) and public/assets/k/{code_version}/
 * (code); the manifest is public/paket/{package_code}.json.
 *
 * A release uses a package when `game_releases.asset_version` equals its
 * `package_code`. Status codes are stored values: `membangun` (batch build in
 * progress), `dibangun` (built, not yet published), `terbit` (published),
 * `ditarik` (withdrawn).
 */
class CreateAssetPackages extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => $this->id(),
            'package_code'    => ['type' => 'VARCHAR', 'constraint' => 30],
            'app_version'     => ['type' => 'VARCHAR', 'constraint' => 30],
            'code_version'    => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'content_version' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'manifest_sha256' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'total_bytes'     => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 0],
            'file_count'      => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'groups_json'     => $this->json(true),
            'warnings_json'   => $this->json(true),
            'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'membangun'],
            'built_by'        => $this->ref(true),
            'built_at'        => $this->datetime(true),
            'published_at'    => $this->datetime(true),
            'notes'           => ['type' => 'TEXT', 'null' => true],
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('package_code');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('built_by', 'staff_users', 'id', '', 'SET NULL');
        $this->createGelitaTable('asset_packages');
    }

    public function down(): void
    {
        $this->dropGelitaTable('asset_packages');
    }
}
