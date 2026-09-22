<?php

namespace App\Models;

use App\Entities\Level;
use CodeIgniter\Model;

class LevelModel extends Model
{
    protected $table         = 'levels';
    protected $primaryKey    = 'id';
    protected $returnType    = Level::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'sequence', 'code', 'name_id', 'name_en', 'difficulty',
        'focus_id', 'focus_en', 'cp_id', 'cp_en', 'tp_id', 'tp_en', 'intro_id', 'intro_en',
        'map_media_id', 'background_media_id', 'badge_media_id', 'map_x', 'map_y', 'is_active',
    ];
    protected $validationRules = [
        'sequence'   => 'required|is_natural_no_zero|is_unique[levels.sequence,id,{id}]',
        'code'       => 'required|max_length[40]|is_unique[levels.code,id,{id}]',
        'name_id'    => 'required|max_length[100]',
        'name_en'    => 'required|max_length[100]',
        'difficulty' => 'required|in_list[mudah,sedang,sulit]',
    ];

    /** @return list<Level> */
    public function ordered(): array
    {
        return $this->where('is_active', 1)->orderBy('sequence', 'ASC')->findAll();
    }

    public function findByCode(string $code): ?Level
    {
        return $this->where('code', trim($code))->first();
    }

    public function findBySequence(int $sequence): ?Level
    {
        return $this->where('sequence', $sequence)->where('is_active', 1)->first();
    }
}
