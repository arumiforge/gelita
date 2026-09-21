<?php

namespace App\Database\Seeds;

class ResearchStudySeeder extends GelitaSeeder
{
    public function run(): void
    {
        $studyId = $this->insertIfMissing('research_studies', ['code' => 'STUDI-KEDU-2026'], [
            'name'                => 'Studi GELITA Kedu 2026',
            'year_label'          => '2026',
            'status'              => 'active',
            'retention_days'      => 1825,
            'default_locale'      => 'id',
            'unlock_mode'         => 'sequential',
            'item_selection_mode' => 'fixed',
            'require_consent'     => 1,
            'active_phase_code'   => 'umum',
            'allow_phase_choice'  => 0,
        ]);

        $phases = [
            ['sequence' => 1, 'code' => 'umum', 'label_id' => 'Umum', 'label_en' => 'General'],
            ['sequence' => 2, 'code' => 'pretest', 'label_id' => 'Pretest', 'label_en' => 'Pretest'],
            ['sequence' => 3, 'code' => 'posttest', 'label_id' => 'Posttest', 'label_en' => 'Posttest'],
        ];

        foreach ($phases as $phase) {
            $this->insertIfMissing('research_phases', ['study_id' => $studyId, 'code' => $phase['code']], [
                'sequence'  => $phase['sequence'],
                'label_id'  => $phase['label_id'],
                'label_en'  => $phase['label_en'],
                'is_active' => 1,
            ]);
        }
    }
}
