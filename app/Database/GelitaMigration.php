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
}
