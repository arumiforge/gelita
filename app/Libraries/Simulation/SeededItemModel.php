<?php

namespace App\Libraries\Simulation;

use App\Models\ChallengeItemModel;
use Random\Randomizer;

/**
 * ChallengeItemModel dengan pemilihan variasi berbenih, hanya untuk
 * `gelita:simulasi`: seed yang sama memberi variasi soal yang sama. Aturan
 * pemilihannya (variasi yang sudah didapat dihindari) tetap milik
 * ChallengeItemModel::pickVariant().
 */
class SeededItemModel extends ChallengeItemModel
{
    private ?Randomizer $randomizer = null;

    public function useRandomizer(Randomizer $randomizer): void
    {
        $this->randomizer = $randomizer;
    }

    protected function randomIndex(int $count): int
    {
        return $this->randomizer?->getInt(0, $count - 1) ?? parent::randomIndex($count);
    }
}
