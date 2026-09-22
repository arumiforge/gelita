<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use CodeIgniter\Entity\Entity;

class ReadingPassage extends Entity
{
    use Bilingual;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'             => 'int',
        'level_id'       => 'int',
        'media_asset_id' => '?int',
        'is_active'      => 'boolean',
    ];

    /** Bentuk yang dikirim ke pemain: teks bacaan tidak memuat kunci apa pun. */
    public function toPlayerArray(string $locale): array
    {
        return [
            'id'    => $this->id,
            'key'   => $this->passage_key,
            'title' => $this->text('title', $locale),
            'body'  => $this->text('body', $locale),
            'media' => $this->media_asset_id ? media_src($this->media_asset_id) : null,
        ];
    }
}
