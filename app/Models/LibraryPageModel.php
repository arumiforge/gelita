<?php

namespace App\Models;

use App\Entities\LibraryPage;
use CodeIgniter\Model;

class LibraryPageModel extends Model
{
    protected $table         = 'library_pages';
    protected $primaryKey    = 'id';
    protected $returnType    = LibraryPage::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'level_id', 'sequence', 'title_id', 'title_en', 'body_id', 'body_en',
        'image_a_media_id', 'image_b_media_id', 'video_media_id', 'poster_media_id', 'is_active',
    ];
    protected $validationRules = [
        'level_id' => 'required|is_natural_no_zero',
        'sequence' => 'required|is_natural_no_zero',
        'title_id' => 'required|max_length[250]',
        'title_en' => 'required|max_length[250]',
    ];

    /** @return list<LibraryPage> */
    public function forLevel(int $levelId): array
    {
        return $this->where('level_id', $levelId)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }
}
