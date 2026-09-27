<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Menambahkan narasi petunjuk arena `cari`: `challenge_items.audio_prompt_id`
 * dan `audio_prompt_en_id` (FK `audio_assets`, SET NULL), meniru pasangan
 * `challenge_nodes.audio_intro_id` / `audio_intro_en_id`.
 *
 * Hanya dipakai butir `find_object` yang menjadi target (petunjuk yang
 * dibacakan Mbah Kedu); objek jebakan tidak punya petunjuk. Transkripnya
 * teks `prompt_id` / `prompt_en` butir itu. Rekaman diimpor lewat
 * NarrationImporter dengan nama berkas `petunjuk-{node}-NN` (mis.
 * `petunjuk-tmg-4-01.mp3`) atau dipilih manual di form butir. Baris yang
 * sudah ada dibiarkan NULL: tanpa audio yang disetujui, petunjuk tetap
 * tampil sebagai teks seperti sebelumnya.
 */
class AddChallengeItemPromptAudio extends GelitaMigration
{
    private const TABLE = 'challenge_items';

    private const COLUMNS = ['audio_prompt_id', 'audio_prompt_en_id'];

    public function up(): void
    {
        $columns = [];
        $after   = 'media_asset_id';

        foreach (self::COLUMNS as $column) {
            if (! $this->hasColumn($column)) {
                $columns[$column] = $this->ref(true) + ['after' => $after];
            }

            $after = $column;
        }

        if ($columns === []) {
            return;
        }

        $this->forge->addColumn(self::TABLE, $columns);

        foreach (array_keys($columns) as $column) {
            $this->forge->addForeignKey($column, 'audio_assets', 'id', '', 'SET NULL');
        }

        $this->forge->processIndexes(self::TABLE);
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $column) {
            if (! $this->hasColumn($column)) {
                continue;
            }

            $name = self::TABLE . '_' . $column . '_foreign';
            $this->forge->dropForeignKey(self::TABLE, $name);
            // index implisit yang dibuat InnoDB untuk FK
            $this->db->query('ALTER TABLE `' . self::TABLE . '` DROP INDEX `' . $name . '`');
            $this->forge->dropColumn(self::TABLE, $column);
        }
    }

    private function hasColumn(string $column): bool
    {
        $this->db->resetDataCache();

        return $this->db->fieldExists($column, self::TABLE);
    }
}
