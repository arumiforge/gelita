<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use CodeIgniter\Entity\Entity;

class LibraryPage extends Entity
{
    use Bilingual;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'               => 'int',
        'level_id'         => 'int',
        'sequence'         => 'int',
        'image_a_media_id' => '?int',
        'image_b_media_id' => '?int',
        'video_media_id'   => '?int',
        'poster_media_id'  => '?int',
        'is_active'        => 'boolean',
    ];
}
