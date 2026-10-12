<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Asset package Stage 6 (docs/rencana-paket-aset.md §7.2): every study keeps
 * the asset package that was active when it became *berjalan*.
 *
 * `research_studies.package_code` is written by the server only (Penelitian
 * page, when the status changes to `active`) and never edited by hand. NULL on
 * a running study means it started while package mode was off.
 *
 * A study that is already running when this migration runs is locked to the
 * package its active release names, if that package exists in
 * `asset_packages`; otherwise it stays NULL (package mode was off).
 */
class AddPackageCodeToResearchStudies extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addColumn('research_studies', [
            'package_code' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'unlock_mode'],
        ]);

        $studies  = $this->db->prefixTable('research_studies');
        $releases = $this->db->prefixTable('game_releases');
        $packages = $this->db->prefixTable('asset_packages');

        $this->db->query(
            "UPDATE {$studies} SET package_code = ("
            . "SELECT r.asset_version FROM {$releases} r JOIN {$packages} p ON p.package_code = r.asset_version "
            . "WHERE r.is_active = 1 ORDER BY r.id DESC LIMIT 1) "
            . "WHERE status = 'active'",
        );
    }

    public function down(): void
    {
        $this->forge->dropColumn('research_studies', 'package_code');
    }
}
