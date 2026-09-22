<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Menyelaraskan `challenge_attempts` dengan spesifikasi 01_DATABASE.md §21.
 *
 * Migration 002100 memakai nama `first_pass_rate`/`final_rate` dan belum memuat
 * tiga kolom metrik proses yang dipakai ChallengeService & ScoringService pada
 * tahap 3: `check_count`, `answer_change_count`, `audio_use_count`.
 */
class AlignChallengeAttemptMetrics extends GelitaMigration
{
    public function up(): void
    {
        if ($this->db->fieldExists('first_pass_rate', 'challenge_attempts')) {
            $this->forge->modifyColumn('challenge_attempts', [
                'first_pass_rate' => [
                    'name'       => 'first_pass_accuracy',
                    'type'       => 'DECIMAL',
                    'constraint' => '6,2',
                    'default'    => '0.00',
                ],
                'final_rate' => [
                    'name'       => 'final_accuracy',
                    'type'       => 'DECIMAL',
                    'constraint' => '6,2',
                    'default'    => '0.00',
                ],
            ]);
        }

        $columns = [];

        if (! $this->db->fieldExists('check_count', 'challenge_attempts')) {
            $columns['check_count'] = ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0, 'after' => 'final_accuracy'];
        }
        if (! $this->db->fieldExists('answer_change_count', 'challenge_attempts')) {
            $columns['answer_change_count'] = ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0, 'after' => 'retry_count'];
        }
        if (! $this->db->fieldExists('audio_use_count', 'challenge_attempts')) {
            $columns['audio_use_count'] = ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0, 'after' => 'answer_change_count'];
        }

        if ($columns !== []) {
            $this->forge->addColumn('challenge_attempts', $columns);
        }
    }

    public function down(): void
    {
        foreach (['audio_use_count', 'answer_change_count', 'check_count'] as $column) {
            if ($this->db->fieldExists($column, 'challenge_attempts')) {
                $this->forge->dropColumn('challenge_attempts', $column);
            }
        }

        if ($this->db->fieldExists('first_pass_accuracy', 'challenge_attempts')) {
            $this->forge->modifyColumn('challenge_attempts', [
                'first_pass_accuracy' => [
                    'name'       => 'first_pass_rate',
                    'type'       => 'DECIMAL',
                    'constraint' => '6,2',
                    'default'    => '0.00',
                ],
                'final_accuracy' => [
                    'name'       => 'final_rate',
                    'type'       => 'DECIMAL',
                    'constraint' => '6,2',
                    'default'    => '0.00',
                ],
            ]);
        }
    }
}
