<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Menambahkan index gabungan `game_event_logs (session_id, occurred_at)`.
 *
 * Index ini termasuk "INDEX gabungan wajib" di 01_DATABASE.md §24 dan
 * prioritas 2 Index Strategy, tetapi tidak ikut dibuat migration 002400.
 * Linimasa sesi diurutkan `occurred_at` lalu `sequence_no` (aturan 8), dan
 * index inilah yang melayaninya. 002400 tidak diubah — sama seperti koreksi
 * 003100/003200, perbaikan skema datang sebagai migration baru.
 */
class AddEventLogSessionTimeIndex extends GelitaMigration
{
    private const TABLE = 'game_event_logs';
    private const KEY   = 'session_id_occurred_at';

    public function up(): void
    {
        if ($this->hasKey()) {
            return;
        }

        $this->forge->addKey(['session_id', 'occurred_at'], false, false, self::KEY);
        $this->forge->processIndexes(self::TABLE);
    }

    public function down(): void
    {
        if ($this->hasKey()) {
            $this->forge->dropKey(self::TABLE, self::KEY, false);
        }
    }

    private function hasKey(): bool
    {
        $this->db->resetDataCache();

        return array_key_exists(self::KEY, $this->db->getIndexData(self::TABLE));
    }
}
