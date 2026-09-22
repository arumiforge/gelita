<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Memperbaiki nullability lima kolom metrik `challenge_attempts` yang dibentuk
 * migration 003100.
 *
 * `Forge::_processFields()` hanya memaksakan `NOT NULL` saat CREATE TABLE; pada
 * ALTER (addColumn/modifyColumn) atribut `null` yang tidak disebutkan membuat
 * kolom menjadi NULLABLE. Akibatnya 003100 menghasilkan:
 *
 *   first_pass_accuracy · final_accuracy · check_count ·
 *   answer_change_count · audio_use_count
 *
 * sebagai NULL padahal 01_DATABASE.md §21 (dan seluruh pembaca tahap 3:
 * ChallengeService, ScoringService, EventService, AnalyticsService) menganggap
 * kolom ini selalu terisi. Migration ini menegakkan NOT NULL beserta default
 * yang dimaksud, tanpa mengubah 002100 maupun 003100.
 */
class EnforceChallengeAttemptMetricDefaults extends GelitaMigration
{
    private const TABLE = 'challenge_attempts';

    /** kolom => [definisi NOT NULL, default untuk backfill baris lama] */
    private const METRICS = [
        'first_pass_accuracy' => [
            ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => false, 'default' => '0.00'],
            '0.00',
        ],
        'final_accuracy' => [
            ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => false, 'default' => '0.00'],
            '0.00',
        ],
        'check_count' => [
            ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false, 'default' => 0],
            0,
        ],
        'answer_change_count' => [
            ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false, 'default' => 0],
            0,
        ],
        'audio_use_count' => [
            ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false, 'default' => 0],
            0,
        ],
    ];

    public function up(): void
    {
        $this->applyNullability(false);
    }

    /**
     * Mengembalikan kelima kolom ke keadaan yang ditinggalkan 003100 (NULLABLE
     * dengan default yang sama) sehingga `down()` 003100 tetap dapat berjalan.
     */
    public function down(): void
    {
        $this->applyNullability(true);
    }

    private function applyNullability(bool $nullable): void
    {
        // BaseConnection menyimpan daftar kolom per tabel di $dataCache dan Forge
        // tidak pernah membersihkannya sesudah ALTER. Tanpa reset ini, 003200
        // yang berjalan pada koneksi yang sama dengan 003100 masih membaca nama
        // kolom versi lama (first_pass_rate/final_rate, tanpa *_count) sehingga
        // seluruh pemeriksaan fieldExists() di bawah meleset.
        $this->db->resetDataCache();

        if (! $this->db->tableExists(self::TABLE)) {
            return;
        }

        foreach (self::METRICS as $column => [$definition, $fallback]) {
            if (! $this->db->fieldExists($column, self::TABLE)) {
                continue;
            }

            // Baris lama boleh berisi NULL selama kolom masih nullable; isi
            // dulu dengan default agar ALTER ... NOT NULL tidak ditolak pada
            // MySQL mode STRICT.
            if (! $nullable) {
                $this->db->table(self::TABLE)
                    ->where($column . ' IS NULL', null, false)
                    ->update([$column => $fallback]);
            }

            $definition['null'] = $nullable;

            $this->forge->modifyColumn(self::TABLE, [$column => $definition]);
        }
    }
}
