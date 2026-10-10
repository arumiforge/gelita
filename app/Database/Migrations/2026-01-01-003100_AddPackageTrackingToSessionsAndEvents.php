<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Asset package Stage 5 (docs/rencana-paket-aset.md §7.1, §7.3): research
 * data integrity.
 *
 * - `game_sessions.app_client_version` is widened to hold
 *   `{app_version}+{package_code}`; `package_code` and `package_mode`
 *   (`lengkap` | `daring` | `nonaktif`, §8.7) are added.
 * - `game_event_logs.clock_offset_ms` = server_received_at − client_sent_at
 *   of the batch that carried the event (raw `occurred_at` stays untouched).
 *
 * Sessions created before this migration never had an asset package, so they
 * are backfilled with `package_mode = nonaktif` and the app version of their
 * release.
 */
class AddPackageTrackingToSessionsAndEvents extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->modifyColumn('game_sessions', [
            'app_client_version' => ['name' => 'app_client_version', 'type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);

        $this->forge->addColumn('game_sessions', [
            'package_code' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'app_client_version'],
            'package_mode' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'package_code'],
        ]);

        $this->forge->addColumn('game_event_logs', [
            'clock_offset_ms' => ['type' => 'BIGINT', 'null' => true, 'after' => 'server_received_at'],
        ]);

        $sessions = $this->db->prefixTable('game_sessions');
        $releases = $this->db->prefixTable('game_releases');

        $this->db->query("UPDATE {$sessions} SET package_mode = 'nonaktif' WHERE package_mode IS NULL");
        $this->db->query(
            "UPDATE {$sessions} SET app_client_version = "
            . "(SELECT app_version FROM {$releases} WHERE {$releases}.id = {$sessions}.release_id) "
            . 'WHERE app_client_version IS NULL',
        );
    }

    public function down(): void
    {
        $this->forge->dropColumn('game_event_logs', 'clock_offset_ms');
        $this->forge->dropColumn('game_sessions', ['package_code', 'package_mode']);

        $sessions = $this->db->prefixTable('game_sessions');
        $this->db->query("UPDATE {$sessions} SET app_client_version = LEFT(app_client_version, 30)");

        $this->forge->modifyColumn('game_sessions', [
            'app_client_version' => ['name' => 'app_client_version', 'type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ]);
    }
}
