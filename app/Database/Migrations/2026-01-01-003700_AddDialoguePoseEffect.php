<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Menambahkan `dialogues.pose` dan `dialogues.effect` (docs/naskah-cerita.md,
 * "Kamus pose dan efek").
 *
 * - `pose`: gambar tokoh yang tampil (slot `char.{jaka|kedu}.{pose}.{n}`).
 *   NULL = `idle`; narator tidak punya gambar.
 * - `effect`: efek layar saat baris itu muncul (`fog`, `glow`, …). NULL =
 *   tanpa efek.
 *
 * Nilai sah ada di Config\Gelita::$characterPoses dan $dialogueEffects, dan
 * dijaga DialogueModel, bukan ENUM: kamus pose akan bertambah seiring aset
 * tokoh baru tanpa perlu migration lagi. Baris yang sudah ada dibiarkan NULL;
 * `php spark gelita:story:update` mengisinya dari naskah.
 */
class AddDialoguePoseEffect extends GelitaMigration
{
    private const TABLE = 'dialogues';

    public function up(): void
    {
        $columns = [];

        if (! $this->hasColumn('pose')) {
            $columns['pose'] = ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'character_code'];
        }

        if (! $this->hasColumn('effect')) {
            $columns['effect'] = ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'pose'];
        }

        if ($columns !== []) {
            $this->forge->addColumn(self::TABLE, $columns);
        }
    }

    public function down(): void
    {
        foreach (['effect', 'pose'] as $column) {
            if ($this->hasColumn($column)) {
                $this->forge->dropColumn(self::TABLE, $column);
            }
        }
    }

    private function hasColumn(string $column): bool
    {
        $this->db->resetDataCache();

        return $this->db->fieldExists($column, self::TABLE);
    }
}
