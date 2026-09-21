<?php

namespace App\Database\Seeds;

class LearningIndicatorSeeder extends GelitaSeeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'literasi', 'name_id' => 'Literasi membaca', 'name_en' => 'Reading literacy', 'domain' => 'kognitif'],
            ['code' => 'budaya', 'name_id' => 'Pengetahuan budaya', 'name_en' => 'Cultural knowledge', 'domain' => 'kognitif'],
            ['code' => 'sikap', 'name_id' => 'Sikap menjaga warisan', 'name_en' => 'Heritage stewardship', 'domain' => 'afektif'],
        ];

        foreach ($rows as $row) {
            $code = $row['code'];
            unset($row['code']);
            $this->insertIfMissing('learning_indicators', ['code' => $code], $row + ['is_active' => 1]);
        }
    }
}
