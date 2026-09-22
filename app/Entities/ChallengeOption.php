<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use CodeIgniter\Entity\Entity;

class ChallengeOption extends Entity
{
    use Bilingual;

    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'id'                => 'int',
        'challenge_item_id' => 'int',
        'media_asset_id'    => '?int',
        'is_correct'        => 'boolean',
        'display_order'     => 'int',
    ];

    /** Bentuk aman untuk browser: TANPA is_correct dan TANPA feedback. */
    public function toPlayerArray(string $locale): array
    {
        return [
            'option_key' => $this->option_key,
            'label'      => $this->text('label', $locale),
            'media'      => $this->media_asset_id ? media_src($this->media_asset_id) : null,
        ];
    }
}
