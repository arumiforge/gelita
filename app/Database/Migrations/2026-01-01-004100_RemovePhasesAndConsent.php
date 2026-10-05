<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Perombakan kelas 5 · Dok 1 §5.2 — satu tahap bermain, tanpa izin wali.
 *
 * Membuang fase penelitian (`research_phases`, `game_sessions.phase_id`,
 * pengaturan fase/consent/pemilihan soal di `research_studies`) dan tabel
 * `participant_consents`. Persetujuan siswa kini satu kolom
 * `participants.assented_at`. Tidak ada pemindahan data lama (penelitian
 * belum dimulai; sisa uji coba dibersihkan `gelita:reset`).
 *
 * `down()` hanya mengembalikan struktur, bukan isi.
 */
class RemovePhasesAndConsent extends GelitaMigration
{
    private const STUDY_COLUMNS = ['active_phase_code', 'allow_phase_choice', 'item_selection_mode', 'require_consent'];

    public function up(): void
    {
        $this->db->resetDataCache();

        // 1. game_sessions: index baru dulu, karena index gabungan lama juga
        //    menjadi index FK participant_id (InnoDB menolak menghapusnya).
        if ($this->db->fieldExists('phase_id', 'game_sessions')) {
            $this->addIndexIfMissing('game_sessions', 'participant_id_study_id_status', ['participant_id', 'study_id', 'status']);
            $this->dropForeignKeyOn('game_sessions', 'phase_id');
            $this->dropIndexOnColumns('game_sessions', ['participant_id', 'study_id', 'phase_id', 'status']);
            $this->dropIndexOnColumns('game_sessions', ['phase_id']);
            $this->dropColumnsIfExist('game_sessions', ['phase_id']);
        } else {
            $this->addIndexIfMissing('game_sessions', 'participant_id_study_id_status', ['participant_id', 'study_id', 'status']);
        }

        // 2. research_phases
        $this->dropGelitaTable('research_phases');

        // 3. research_studies
        $this->dropColumnsIfExist('research_studies', self::STUDY_COLUMNS);

        // 4. participant_consents
        $this->dropGelitaTable('participant_consents');

        // 5. participants.assented_at
        $this->addColumnsIfMissing('participants', [
            'assented_at' => ['type' => 'DATETIME', 'constraint' => 6, 'null' => true, 'after' => 'intro_seen_at'],
        ]);
    }

    public function down(): void
    {
        $this->db->resetDataCache();

        $this->dropColumnsIfExist('participants', ['assented_at']);

        $this->addColumnsIfMissing('research_studies', [
            'item_selection_mode' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'fixed', 'after' => 'unlock_mode'],
            'require_consent'     => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1, 'after' => 'item_selection_mode'],
            'active_phase_code'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'umum', 'after' => 'require_consent'],
            'allow_phase_choice'  => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0, 'after' => 'active_phase_code'],
        ]);

        if (! $this->db->tableExists('research_phases')) {
            $this->forge->addField([
                'id'        => $this->id(),
                'study_id'  => $this->ref(),
                'code'      => ['type' => 'VARCHAR', 'constraint' => 20],
                'sequence'  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
                'label_id'  => ['type' => 'VARCHAR', 'constraint' => 150],
                'label_en'  => ['type' => 'VARCHAR', 'constraint' => 150],
                'is_active' => $this->flag(1),
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['study_id', 'code']);
            $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'CASCADE');
            $this->createGelitaTable('research_phases');
        }

        if (! $this->db->tableExists('participant_consents')) {
            $this->forge->addField([
                'id'                        => $this->id(),
                'participant_id'            => $this->ref(),
                'study_id'                  => $this->ref(true),
                'consent_version'           => ['type' => 'VARCHAR', 'constraint' => 50],
                'participant_consented'     => $this->flag(0),
                'parent_guardian_consented' => $this->flag(0),
                'guardian_name'             => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'consent_text_snapshot'     => ['type' => 'TEXT'],
                'consented_at'              => $this->datetime(),
                'withdrawn_at'              => $this->datetime(),
                'metadata_json'             => $this->json(),
                'created_at'                => $this->datetimeNow(),
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('participant_id');
            $this->forge->addKey('consent_version');
            $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
            $this->forge->addForeignKey('study_id', 'research_studies', 'id', '', 'SET NULL');
            $this->createGelitaTable('participant_consents');
        }

        $this->db->resetDataCache();

        if (! $this->db->fieldExists('phase_id', 'game_sessions')) {
            $this->forge->addColumn('game_sessions', [
                'phase_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'study_id'],
            ]);
            $this->addIndexIfMissing('game_sessions', 'phase_id', ['phase_id']);
            $this->db->query('ALTER TABLE `game_sessions` ADD CONSTRAINT `game_sessions_phase_id_foreign`
                FOREIGN KEY (`phase_id`) REFERENCES `research_phases` (`id`) ON DELETE SET NULL');
            $this->addIndexIfMissing('game_sessions', 'participant_id_study_id_phase_id_status', ['participant_id', 'study_id', 'phase_id', 'status']);
        }

        $this->dropIndexIfExists('game_sessions', 'participant_id_study_id_status');
    }
}
