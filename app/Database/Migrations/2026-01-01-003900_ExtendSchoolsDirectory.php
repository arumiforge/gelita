<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;
use App\Libraries\SchoolName;

/**
 * Menjadikan `schools` direktori sekolah resmi untuk pendaftaran (NPSN).
 *
 * `code` tetap NPSN. Baris dari data induk (`gelita:schools:import`) bertanda
 * `is_verified = 1`; nama yang diketik siswa tanpa NPSN yang cocok disimpan
 * sebagai `is_verified = 0` sampai admin menggabungkan atau mengesahkannya
 * (Panel → Sekolah). `match_key` = SchoolName::key(name) untuk mencocokkan
 * varian penulisan ("SD NEGERI 1 CENDONO" = "SD 1 CENDONO"); baris lama diisi
 * saat migrasi. `merged_into_id` mencatat hasil penggabungan.
 */
class ExtendSchoolsDirectory extends GelitaMigration
{
    private const TABLE = 'schools';

    /** nama indeks => kolom */
    private const KEYS = [
        'schools_district_match_key' => ['district_code', 'match_key'],
        'schools_match_key'          => ['match_key'],
        'schools_verified_active'    => ['is_verified', 'is_active'],
    ];

    private const FOREIGN_KEY = 'schools_merged_into_foreign';

    public function up(): void
    {
        $columns = [
            'level'            => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'name'],
            'stage'            => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'level'],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'stage'],
            'subdistrict_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'district_code'],
            'village_name'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'subdistrict_name'],
            'is_verified'      => $this->flag(0) + ['null' => false, 'after' => 'is_active'],
            'match_key'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true, 'after' => 'is_verified'],
            'merged_into_id'   => $this->ref(true) + ['after' => 'match_key'],
        ];

        $this->db->resetDataCache();

        foreach ($columns as $name => $definition) {
            if (! $this->db->fieldExists($name, self::TABLE)) {
                $this->forge->addColumn(self::TABLE, [$name => $definition]);
            }
        }

        $indexes = array_keys($this->db->getIndexData(self::TABLE));

        foreach (self::KEYS as $name => $fields) {
            if (! in_array($name, $indexes, true)) {
                $this->forge->addKey($fields, false, false, $name);
            }
        }

        if (! array_key_exists(self::FOREIGN_KEY, $this->db->getForeignKeyData(self::TABLE))) {
            $this->forge->addForeignKey('merged_into_id', self::TABLE, 'id', '', 'SET NULL', self::FOREIGN_KEY);
        }

        $this->forge->processIndexes(self::TABLE);

        $this->backfill();
    }

    public function down(): void
    {
        if (array_key_exists(self::FOREIGN_KEY, $this->db->getForeignKeyData(self::TABLE))) {
            $this->forge->dropForeignKey(self::TABLE, self::FOREIGN_KEY);
        }

        $indexes = array_keys($this->db->getIndexData(self::TABLE));

        foreach (array_keys(self::KEYS) as $name) {
            if (in_array($name, $indexes, true)) {
                $this->forge->dropKey(self::TABLE, $name);
            }
        }

        $this->db->resetDataCache();

        foreach (['merged_into_id', 'match_key', 'is_verified', 'village_name', 'subdistrict_name', 'status', 'stage', 'level'] as $name) {
            if ($this->db->fieldExists($name, self::TABLE)) {
                $this->forge->dropColumn(self::TABLE, $name);
            }
        }
    }

    /** Baris lama (semuanya ketikan siswa) mendapat kunci pencocokan. */
    private function backfill(): void
    {
        $rows = $this->db->table(self::TABLE)->select('id, name')->where('match_key', null)->get()->getResultArray();

        foreach ($rows as $row) {
            $this->db->table(self::TABLE)
                ->set('match_key', SchoolName::key((string) $row['name']))
                // ON UPDATE CURRENT_TIMESTAMP tidak boleh menandai semua sekolah "baru diubah"
                ->set('updated_at', 'updated_at', false)
                ->where('id', $row['id'])
                ->update();
        }
    }
}
