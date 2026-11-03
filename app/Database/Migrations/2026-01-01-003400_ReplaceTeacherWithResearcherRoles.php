<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Research revision 10, part 1 (docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md §6.1):
 * staff roles become `admin`, `peneliti`, `ketua`; the `guru` role goes.
 *
 * up(), in one transaction:
 * 1. `guru` accounts' exports are deleted, files first (`data_exports.requested_by`
 *    has no ON DELETE and would hold back the account);
 * 2. their data deletion requests are deleted (same reason);
 * 3. the `guru` accounts are deleted; other references become NULL through
 *    their ON DELETE SET NULL keys (audit log, audio approvals, deleted event
 *    rows, asset packages), so the audit history stays with an empty actor;
 * 4. `staff_users.school_id` is dropped with its foreign key;
 * 5. `staff_users.role` loses its default (`guru`).
 *
 * No guard: the owner decided on 3 November 2026 that `guru` accounts are
 * always deleted whenever this migration runs, on any server, because teachers
 * have no place in the system any more (27 K16; replaces the earlier guard
 * with `gelita.allowTeacherRemoval`).
 *
 * down() brings back the column and the default only. Deleted accounts,
 * exports, and requests do not come back.
 */
class ReplaceTeacherWithResearcherRoles extends GelitaMigration
{
    public function up(): void
    {
        $teacherIds = array_map('intval', array_column(
            $this->db->table('staff_users')->select('id')->where('role', 'guru')->get()->getResultArray(),
            'id',
        ));

        $exports = $teacherIds === [] ? [] : $this->db->table('data_exports')
            ->select('id, file_path')
            ->whereIn('requested_by', $teacherIds)
            ->get()
            ->getResultArray();

        $this->db->transException(true)->transStart();

        if ($teacherIds !== []) {
            foreach ($exports as $export) {
                $this->deleteExportFile((string) ($export['file_path'] ?? ''));
            }

            if ($exports !== []) {
                $this->db->table('data_exports')->whereIn('id', array_column($exports, 'id'))->delete();
            }

            $this->db->table('data_deletion_requests')->whereIn('requested_by', $teacherIds)->delete();

            // ON DELETE SET NULL is not enforced on SQLite without foreign key
            // support, so the nullable references are cleared explicitly.
            foreach ([
                ['audit_logs', 'staff_user_id'],
                ['audio_assets', 'approved_by'],
                ['data_deletion_requests', 'approved_by'],
                ['game_event_logs', 'deleted_by'],
                ['asset_packages', 'built_by'],
            ] as [$table, $column]) {
                $this->db->table($table)->whereIn($column, $teacherIds)->update([$column => null]);
            }

            $this->db->table('staff_users')->whereIn('id', $teacherIds)->delete();
        }

        $this->db->transComplete();

        foreach ($this->db->getForeignKeyData('staff_users') as $key) {
            if (in_array('school_id', (array) $key->column_name, true)) {
                $this->forge->dropForeignKey('staff_users', $key->constraint_name);
            }
        }

        $this->forge->dropColumn('staff_users', 'school_id');
        $this->forge->modifyColumn('staff_users', [
            'role' => ['name' => 'role', 'type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
        ]);
    }

    public function down(): void
    {
        $this->forge->addColumn('staff_users', [
            'school_id' => $this->ref(true) + ['after' => 'display_name'],
        ]);
        $this->forge->modifyColumn('staff_users', [
            'role' => ['name' => 'role', 'type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'guru'],
        ]);
        $this->forge->addForeignKey('school_id', 'schools', 'id', '', 'SET NULL');
        $this->forge->processIndexes('staff_users');
    }

    /** Only files under writable/ are removed; a missing file is fine. */
    private function deleteExportFile(string $path): void
    {
        if ($path === '' || ! is_file($path)) {
            return;
        }

        $real     = realpath($path);
        $writable = realpath(WRITEPATH);

        if ($real !== false && $writable !== false && str_starts_with($real, $writable . DIRECTORY_SEPARATOR)) {
            unlink($real);
        }
    }
}
