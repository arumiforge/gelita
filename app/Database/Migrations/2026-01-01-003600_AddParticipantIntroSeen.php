<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Menambahkan `participants.intro_seen_at`: kapan peserta pertama kali
 * menyelesaikan cerita pembuka (`/intro/selesai`).
 *
 * Kolom ini memisahkan pemain baru (wajib menonton cerita pembuka, tanpa
 * tombol "Lewati") dari pemain lama (boleh memilih: lihat cerita pembuka atau
 * langsung ke peta). Peserta yang sudah punya progres — `completed_nodes > 0`
 * di sesi mana pun miliknya — diisi saat migrasi, agar mereka tidak dipaksa
 * menonton ulang. Waktunya diambil dari `created_at` peserta: waktu menonton
 * yang sebenarnya tidak tercatat.
 */
class AddParticipantIntroSeen extends GelitaMigration
{
    private const TABLE  = 'participants';
    private const COLUMN = 'intro_seen_at';

    public function up(): void
    {
        if (! $this->hasColumn()) {
            $this->forge->addColumn(self::TABLE, [
                self::COLUMN => $this->datetime() + ['after' => 'password_changed_at'],
            ]);
        }

        $this->backfill();
    }

    public function down(): void
    {
        if ($this->hasColumn()) {
            $this->forge->dropColumn(self::TABLE, self::COLUMN);
        }
    }

    /** Peserta yang sudah menyelesaikan minimal satu tantangan dianggap sudah menonton. */
    private function backfill(): void
    {
        $progressed = $this->db->table('game_sessions gs')
            ->select('gs.participant_id')
            ->join('session_progress sp', 'sp.session_id = gs.id')
            ->where('sp.completed_nodes >', 0)
            ->getCompiledSelect();

        $this->db->table(self::TABLE)
            ->set(self::COLUMN, 'created_at', false)
            // ON UPDATE CURRENT_TIMESTAMP tidak boleh menandai semua peserta lama "baru diubah"
            ->set('updated_at', 'updated_at', false)
            ->where(self::COLUMN, null)
            ->where("id IN ({$progressed})", null, false)
            ->update();
    }

    private function hasColumn(): bool
    {
        $this->db->resetDataCache();

        return $this->db->fieldExists(self::COLUMN, self::TABLE);
    }
}
