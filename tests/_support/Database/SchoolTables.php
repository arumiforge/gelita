<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Tabel ringkas (SQLite, grup `tests`) untuk uji direktori sekolah: schools,
 * participants, staff_users, dan audit_logs. Kolom yang dibaca/ditulis kode
 * sama dengan migration MySQL (CreateSchools + ExtendSchoolsDirectory).
 */
trait SchoolTables
{
    /** @var list<string> */
    private array $schoolTables = ['participants', 'staff_users', 'audit_logs', 'schools'];

    private function createSchoolTables(BaseConnection $db): void
    {
        $forge = Database::forge('tests');
        $this->dropSchoolTables();

        $forge->addField([
            'id'               => ['type' => 'INTEGER', 'auto_increment' => true],
            'code'             => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 200],
            'level'            => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'stage'            => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'country_code'     => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true, 'default' => 'ID'],
            'province_code'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'district_code'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'subdistrict_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'village_name'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'is_active'        => ['type' => 'INTEGER', 'default' => 1],
            'is_verified'      => ['type' => 'INTEGER', 'default' => 0],
            'match_key'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'merged_into_id'   => ['type' => 'INTEGER', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ])->addPrimaryKey('id')->addUniqueKey('code')->createTable('schools');

        $forge->addField([
            'id'                   => ['type' => 'INTEGER', 'auto_increment' => true],
            'username'             => ['type' => 'VARCHAR', 'constraint' => 30],
            'school_id'            => ['type' => 'INTEGER', 'null' => true],
            'school_name_snapshot' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
        ])->addPrimaryKey('id')->createTable('participants');

        $forge->addField([
            'id'        => ['type' => 'INTEGER', 'auto_increment' => true],
            'username'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'school_id' => ['type' => 'INTEGER', 'null' => true],
        ])->addPrimaryKey('id')->createTable('staff_users');

        $forge->addField([
            'id'            => ['type' => 'INTEGER', 'auto_increment' => true],
            'staff_user_id' => ['type' => 'INTEGER', 'null' => true],
            'action'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'target_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'target_id'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'metadata_json' => ['type' => 'TEXT', 'null' => true],
            'ip_hash'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'occurred_at'   => ['type' => 'DATETIME', 'null' => true],
        ])->addPrimaryKey('id')->createTable('audit_logs');
    }

    private function dropSchoolTables(): void
    {
        $forge = Database::forge('tests');

        foreach ($this->schoolTables as $table) {
            $forge->dropTable($table, true);
        }
    }

    /**
     * Berkas CSV uji dengan format docs/sekolah (sekolah-jateng.csv).
     *
     * @param list<list<string>> $rows npsn, nama, bentuk, jenjang, status, kode_kabupaten, kecamatan, desa
     */
    private function writeSchoolCsv(array $rows): string
    {
        $file   = WRITEPATH . 'sekolah-uji-' . bin2hex(random_bytes(4)) . '.csv';
        $handle = fopen($file, 'wb');
        fputcsv($handle, ['npsn', 'nama', 'bentuk', 'jenjang', 'status', 'kode_kabupaten', 'kecamatan', 'desa'], ',', '"', '');

        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }

        fclose($handle);

        return $file;
    }
}
