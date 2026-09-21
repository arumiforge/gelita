<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use RuntimeException;

class StaffUserSeeder extends GelitaSeeder
{
    public function run(): void
    {
        if ($this->findId('staff_users', ['username' => 'admin']) !== null) {
            $this->info('Akun admin sudah ada — kata sandi tidak diubah.');

            return;
        }

        $password = getenv('GELITA_ADMIN_PASSWORD');
        if ($password === false || $password === '') {
            $password = (string) env('GELITA_ADMIN_PASSWORD', '');
        }

        if (trim($password) === '') {
            $message = 'GELITA_ADMIN_PASSWORD kosong. Jalankan: '
                . "GELITA_ADMIN_PASSWORD='SandiKuatAnda123!' php spark db:seed DatabaseSeeder";

            if (is_cli()) {
                CLI::error($message);
            }

            throw new RuntimeException($message);
        }

        $this->db->table('staff_users')->insert([
            'username'      => 'admin',
            'email'         => null,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => 'admin',
            'display_name'  => 'Administrator',
            'school_id'     => null,
            'is_active'     => 1,
        ]);

        $this->info('Akun admin dibuat (username: admin).');
    }
}
