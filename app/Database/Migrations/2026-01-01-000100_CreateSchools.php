<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Sekolah: direktori resmi (NPSN, dari gelita:schools:import) dan nama ketikan siswa
 * yang belum terverifikasi. `match_key` = nama ternormalisasi untuk pencarian;
 * `merged_into_id` menunjuk sekolah resmi bila ketikan sudah digabung admin.
 */
class CreateSchools extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => $this->id(),
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
            'is_active'        => $this->flag(1),
            'is_verified'      => $this->flag(0),
            'match_key'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'merged_into_id'   => $this->ref(true),
        ] + $this->timestamps());

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('name');
        $this->forge->addKey('country_code');
        $this->forge->addKey('province_code');
        $this->forge->addKey('district_code');
        $this->forge->addKey(['district_code', 'match_key'], false, false, 'schools_district_match_key');
        $this->forge->addKey('match_key', false, false, 'schools_match_key');
        $this->forge->addKey(['is_verified', 'is_active'], false, false, 'schools_verified_active');
        $this->forge->addForeignKey('merged_into_id', 'schools', 'id', '', 'SET NULL');
        $this->createGelitaTable('schools');
    }

    public function down(): void
    {
        $this->dropGelitaTable('schools');
    }
}
