<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Menambahkan `challenge_items.bloom_level` (C1–C6): tingkat berpikir soal
 * menurut Taksonomi Bloom revisi (Anderson & Krathwohl). Dipakai soal Isi
 * Rumpang bacaan untuk siswa kelas 5 SD: Temanggung C1–C2, Magelang C2–C3,
 * Wonosobo C3–C4 (App\Libraries\DifficultyRules). Kolom ini opsional untuk
 * jenis soal lain; baris lama dibiarkan NULL.
 *
 * Diisi lewat kolom `bloom_level` sheet items workbook bank soal atau
 * pilihan "Tingkat berpikir" di editor soal. Tampil di Hasil per soal dan
 * berkas Excel.
 */
class AddChallengeItemBloomLevel extends GelitaMigration
{
    private const TABLE = 'challenge_items';

    private const COLUMN = 'bloom_level';

    public function up(): void
    {
        if ($this->hasColumn()) {
            return;
        }

        $this->forge->addColumn(self::TABLE, [
            self::COLUMN => ['type' => 'VARCHAR', 'constraint' => 2, 'null' => true, 'after' => 'indicator_id'],
        ]);

        $this->db->query('ALTER TABLE `' . self::TABLE . '` ADD INDEX `' . self::TABLE . '_' . self::COLUMN . '` (`' . self::COLUMN . '`)');
    }

    public function down(): void
    {
        if (! $this->hasColumn()) {
            return;
        }

        $this->db->query('ALTER TABLE `' . self::TABLE . '` DROP INDEX `' . self::TABLE . '_' . self::COLUMN . '`');
        $this->forge->dropColumn(self::TABLE, self::COLUMN);
    }

    private function hasColumn(): bool
    {
        $this->db->resetDataCache();

        return $this->db->fieldExists(self::COLUMN, self::TABLE);
    }
}
