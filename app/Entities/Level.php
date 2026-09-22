<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use CodeIgniter\Entity\Entity;

class Level extends Entity
{
    use Bilingual;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                  => 'int',
        'sequence'            => 'int',
        'map_media_id'        => '?int',
        'background_media_id' => '?int',
        'badge_media_id'      => '?int',
        'map_x'               => 'float',
        'map_y'               => 'float',
        'is_active'           => 'boolean',
    ];
}
