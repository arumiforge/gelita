<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Menambahkan `staff_users.must_change_password`, padanan
 * `participants.must_change_password`.
 *
 * Sandi yang diketahui admin — sandi sementara hasil reset di /admin/staf dan
 * sandi awal akun baru — wajib diganti pemiliknya sebelum panel dapat dipakai.
 * Akun yang sudah ada mendapat 0: sandinya dipilih sendiri atau oleh operator
 * saat seeding.
 *
 * `'null' => false` ditulis eksplisit: pada ALTER, Forge membuat kolom NULLABLE
 * bila atribut itu tidak disebutkan (lihat 003200).
 */
class AddStaffMustChangePassword extends GelitaMigration
{
    private const TABLE  = 'staff_users';
    private const COLUMN = 'must_change_password';

    public function up(): void
    {
        if ($this->hasColumn()) {
            return;
        }

        $this->forge->addColumn(self::TABLE, [
            self::COLUMN => $this->flag(0) + ['null' => false, 'after' => 'password_hash'],
        ]);
    }

    public function down(): void
    {
        if ($this->hasColumn()) {
            $this->forge->dropColumn(self::TABLE, self::COLUMN);
        }
    }

    private function hasColumn(): bool
    {
        $this->db->resetDataCache();

        return $this->db->fieldExists(self::COLUMN, self::TABLE);
    }
}
