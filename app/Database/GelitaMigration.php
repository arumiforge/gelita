<?php

namespace App\Database;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * Pola bersama seluruh migration GELITA (01_DATABASE.md, Konvensi Umum):
 * InnoDB · utf8mb4_unicode_ci · PK BIGINT UNSIGNED · DATETIME(6).
 *
 * Berkas ini sengaja diletakkan di luar folder Migrations agar tidak
 * dibaca MigrationRunner sebagai migration.
 */
abstract class GelitaMigration extends Migration
{
    protected const TABLE_ATTRIBUTES = [
        'ENGINE'  => 'InnoDB',
        'CHARSET' => 'utf8mb4',
        'COLLATE' => 'utf8mb4_unicode_ci',
    ];

    /** BIGINT UNSIGNED AUTO_INCREMENT (dipasangkan dengan addPrimaryKey('id')) */
    protected function id(): array
    {
        return ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true];
    }

    /** BIGINT UNSIGNED untuk kolom FK / referensi */
    protected function ref(bool $nullable = false): array
    {
        return ['type' => 'BIGINT', 'unsigned' => true, 'null' => $nullable];
    }

    /** TINYINT(1) NOT NULL untuk flag 0/1 */
    protected function flag(int $default): array
    {
        return ['type' => 'TINYINT', 'constraint' => 1, 'default' => $default];
    }

    /** TINYINT(1) NULL untuk nilai benar/salah yang belum diketahui */
    protected function nullableFlag(): array
    {
        return ['type' => 'TINYINT', 'constraint' => 1, 'null' => true];
    }

    protected function json(bool $nullable = true): array
    {
        return ['type' => 'JSON', 'null' => $nullable];
    }

    /** DATETIME(6) tanpa default */
    protected function datetime(bool $nullable = true): array
    {
        return ['type' => 'DATETIME', 'constraint' => 6, 'null' => $nullable];
    }

    /** DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) */
    protected function datetimeNow(): array
    {
        return [
            'type'       => 'DATETIME',
            'constraint' => 6,
            'null'       => false,
            'default'    => new RawSql('CURRENT_TIMESTAMP(6)'),
        ];
    }

    /** DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) */
    protected function datetimeNowOnUpdate(): array
    {
        return [
            'type'       => 'DATETIME',
            'constraint' => 6,
            'null'       => false,
            'default'    => new RawSql('CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6)'),
        ];
    }

    /** created_at + updated_at */
    protected function timestamps(): array
    {
        return [
            'created_at' => $this->datetimeNow(),
            'updated_at' => $this->datetimeNowOnUpdate(),
        ];
    }

    protected function createGelitaTable(string $table): void
    {
        $this->forge->createTable($table, true, self::TABLE_ATTRIBUTES);
    }

    protected function dropGelitaTable(string $table): void
    {
        $this->forge->dropTable($table, true);
    }

    /**
     * Hapus FK pada (tabel, kolom) bila ada; nama constraint dibaca dari
     * information_schema, bukan ditebak dari pola penamaan Forge.
     */
    protected function dropForeignKeyOn(string $table, string $column): void
    {
        $rows = $this->db->query(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column],
        )->getResultArray();

        foreach ($rows as $row) {
            $this->db->query('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $row['CONSTRAINT_NAME'] . '`');
        }
    }

    /** Hapus index bernama $name bila ada (SHOW INDEX). */
    protected function dropIndexIfExists(string $table, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            $this->db->query('ALTER TABLE `' . $table . '` DROP INDEX `' . $name . '`');
        }
    }

    /**
     * Hapus setiap index yang kolomnya persis $columns (urutan sama), apa pun
     * namanya. Dipakai untuk index gabungan buatan Forge yang namanya tidak
     * pasti di semua pemasangan.
     *
     * @param list<string> $columns
     */
    protected function dropIndexOnColumns(string $table, array $columns): void
    {
        foreach ($this->indexColumns($table) as $name => $cols) {
            if ($name !== 'PRIMARY' && $cols === $columns) {
                $this->db->query('ALTER TABLE `' . $table . '` DROP INDEX `' . $name . '`');
            }
        }
    }

    /** Tambah index (bukan unik) bila belum ada index dengan nama itu. */
    protected function addIndexIfMissing(string $table, string $name, array $columns, bool $unique = false): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        $cols = implode(', ', array_map(static fn (string $c): string => '`' . $c . '`', $columns));
        $this->db->query('ALTER TABLE `' . $table . '` ADD ' . ($unique ? 'UNIQUE ' : '') . 'INDEX `' . $name . '` (' . $cols . ')');
    }

    protected function indexExists(string $table, string $name): bool
    {
        return array_key_exists($name, $this->indexColumns($table));
    }

    /**
     * @return array<string, list<string>> nama index => kolom (urut Seq_in_index)
     */
    protected function indexColumns(string $table): array
    {
        $out = [];

        foreach ($this->db->query('SHOW INDEX FROM `' . $table . '`')->getResultArray() as $row) {
            $out[$row['Key_name']][(int) $row['Seq_in_index']] = $row['Column_name'];
        }

        foreach ($out as $name => $cols) {
            ksort($cols);
            $out[$name] = array_values($cols);
        }

        return $out;
    }

    /** Hapus kolom yang masih ada (FK pada kolom itu dihapus lebih dulu). */
    protected function dropColumnsIfExist(string $table, array $columns): void
    {
        $this->db->resetDataCache();

        if (! $this->db->tableExists($table)) {
            return;
        }

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, $table)) {
                $this->dropForeignKeyOn($table, $column);
                $this->forge->dropColumn($table, $column);
                $this->db->resetDataCache();
            }
        }
    }

    /**
     * Tambah kolom yang belum ada. `null` wajib disebut eksplisit: pada ALTER,
     * Forge menganggap kolom tanpa `null` sebagai NULLABLE (lihat 003200).
     *
     * @param array<string, array<string, mixed>> $fields
     */
    protected function addColumnsIfMissing(string $table, array $fields): void
    {
        $this->db->resetDataCache();

        foreach ($fields as $column => $definition) {
            if (! $this->db->fieldExists($column, $table)) {
                $this->forge->addColumn($table, [$column => $definition]);
                $this->db->resetDataCache();
            }
        }
    }
}
