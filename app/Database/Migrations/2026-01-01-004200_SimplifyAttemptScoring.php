<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Perombakan kelas 5 · Dok 1 §5.3 — skor 0–100 dan profil skor dihapus.
 *
 * Pengerjaan (`challenge_attempts`) kini mencatat variasi yang dimainkan,
 * alasan selesai (`solved` | `tries_exhausted`), dan salinan batas percobaan.
 * Jawaban per soal (`item_responses`) mencatat jumlah percobaan dan percobaan
 * ke berapa soal menjadi benar. Kolom alasan (benar–salah beralasan) dibuang.
 * `game_releases.scoring_version` tetap; isinya kode aturan bintang.
 */
class SimplifyAttemptScoring extends GelitaMigration
{
    private const ATTEMPT_DROPPED = [
        'scoring_profile_id', 'first_pass_accuracy', 'final_accuracy', 'retry_count',
        'independence', 'score', 'scoring_version',
    ];

    public function up(): void
    {
        // 1. challenge_attempts
        $this->dropColumnsIfExist('challenge_attempts', self::ATTEMPT_DROPPED);
        $this->addColumnsIfMissing('challenge_attempts', [
            'variant_group'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'attempt_no'],
            'completion_reason' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'status'],
            'max_tries'         => ['type' => 'TINYINT', 'unsigned' => true, 'null' => false, 'default' => 3, 'after' => 'check_count'],
        ]);

        // 2. challenge_nodes
        $this->dropColumnsIfExist('challenge_nodes', ['scoring_profile_id']);

        // 3. scoring_profiles
        $this->dropGelitaTable('scoring_profiles');

        // 4. session_progress
        $this->dropColumnsIfExist('session_progress', ['total_score']);

        // 5. item_responses
        $this->dropColumnsIfExist('item_responses', ['reason_text', 'reason_review_status']);
        $this->addColumnsIfMissing('item_responses', [
            'try_count'     => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false, 'default' => 0, 'after' => 'change_count'],
            'solved_on_try' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true, 'after' => 'try_count'],
        ]);
    }

    public function down(): void
    {
        $this->dropColumnsIfExist('item_responses', ['try_count', 'solved_on_try']);
        $this->addColumnsIfMissing('item_responses', [
            'reason_text'          => ['type' => 'TEXT', 'null' => true, 'after' => 'final_answer_json'],
            'reason_review_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'reason_text'],
        ]);

        $this->addColumnsIfMissing('session_progress', [
            'total_score' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => false, 'default' => '0.00', 'after' => 'completed_levels'],
        ]);

        if (! $this->db->tableExists('scoring_profiles')) {
            $this->forge->addField([
                'id'                              => $this->id(),
                'code'                            => ['type' => 'VARCHAR', 'constraint' => 50],
                'version'                         => ['type' => 'VARCHAR', 'constraint' => 20],
                'first_pass_weight'               => ['type' => 'DECIMAL', 'constraint' => '6,4', 'default' => '0.7000'],
                'final_weight'                    => ['type' => 'DECIMAL', 'constraint' => '6,4', 'default' => '0.2000'],
                'independence_weight'             => ['type' => 'DECIMAL', 'constraint' => '6,4', 'default' => '0.1000'],
                'hint_penalty_per_use'            => ['type' => 'DECIMAL', 'constraint' => '8,4', 'default' => '10.0000'],
                'retry_penalty_per_extra_attempt' => ['type' => 'DECIMAL', 'constraint' => '8,4', 'default' => '5.0000'],
                'three_star_min_score'            => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '85.00'],
                'three_star_min_first_pass'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '80.00'],
                'two_star_min_score'              => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '65.00'],
                'is_active'                       => $this->flag(1),
                'created_at'                      => $this->datetimeNow(),
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['code', 'version']);
            $this->createGelitaTable('scoring_profiles');
        }

        $this->addColumnsIfMissing('challenge_nodes', [
            'scoring_profile_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'indicator_id'],
        ]);
        $this->addForeignKeyIfMissing('challenge_nodes', 'scoring_profile_id');

        $this->dropColumnsIfExist('challenge_attempts', ['variant_group', 'completion_reason', 'max_tries']);
        $this->addColumnsIfMissing('challenge_attempts', [
            'first_pass_accuracy' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => false, 'default' => '0.00', 'after' => 'final_correct'],
            'final_accuracy'      => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => false, 'default' => '0.00', 'after' => 'first_pass_accuracy'],
            'retry_count'         => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false, 'default' => 0, 'after' => 'hint_count'],
            'independence'        => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => false, 'default' => '100.00', 'after' => 'audio_use_count'],
            'score'               => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => false, 'default' => '0.00', 'after' => 'independence'],
            'scoring_profile_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'stars'],
            'scoring_version'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'scoring_profile_id'],
        ]);
        $this->addForeignKeyIfMissing('challenge_attempts', 'scoring_profile_id');
    }

    private function addForeignKeyIfMissing(string $table, string $column): void
    {
        $exists = $this->db->query(
            'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column],
        )->getRowArray();

        if ($exists === null) {
            $this->db->query('ALTER TABLE `' . $table . '` ADD CONSTRAINT `' . $table . '_' . $column . '_foreign`
                FOREIGN KEY (`' . $column . '`) REFERENCES `scoring_profiles` (`id`) ON DELETE SET NULL');
        }
    }
}
