<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Helper bersama seeder GELITA. Semua seeder idempotent: aman dijalankan ulang.
 * Baris yang sudah ada TIDAK ditimpa agar hasil suntingan admin / impor bank soal tetap utuh.
 */
abstract class GelitaSeeder extends Seeder
{
    protected function findId(string $table, array $where): ?int
    {
        $row = $this->db->table($table)->select('id')->where($where)->get(1)->getRowArray();

        return $row === null ? null : (int) $row['id'];
    }

    /** Insert ($where + $data) bila belum ada; mengembalikan id baris. */
    protected function insertIfMissing(string $table, array $where, array $data = []): int
    {
        $id = $this->findId($table, $where);

        if ($id !== null) {
            return $id;
        }

        $this->db->table($table)->insert($where + $data);

        return (int) $this->db->insertID();
    }

    protected function toJson(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    protected function info(string $message): void
    {
        if (is_cli() && ! $this->silent) {
            CLI::write('  ' . $message, 'dark_gray');
        }
    }

    /** @return array<string,int> code => id */
    protected function levelIds(): array
    {
        $ids = [];

        foreach ($this->db->table('levels')->select('id, code')->get()->getResultArray() as $row) {
            $ids[$row['code']] = (int) $row['id'];
        }

        return $ids;
    }
}
