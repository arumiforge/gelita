<?php

namespace App\Database\Seeds;

class ScoringProfileSeeder extends GelitaSeeder
{
    public function run(): void
    {
        $this->insertIfMissing('scoring_profiles', ['code' => 'GELITA_V2', 'version' => '2.0'], [
            'first_pass_weight'               => '0.7000',
            'final_weight'                    => '0.2000',
            'independence_weight'             => '0.1000',
            'hint_penalty_per_use'            => '10.0000',
            'retry_penalty_per_extra_attempt' => '5.0000',
            'three_star_min_score'            => '85.00',
            'three_star_min_first_pass'       => '80.00',
            'two_star_min_score'              => '65.00',
            'is_active'                       => 1,
        ]);
    }
}
