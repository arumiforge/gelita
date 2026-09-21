<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class CreateScoringProfiles extends GelitaMigration
{
    public function up(): void
    {
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

    public function down(): void
    {
        $this->dropGelitaTable('scoring_profiles');
    }
}
