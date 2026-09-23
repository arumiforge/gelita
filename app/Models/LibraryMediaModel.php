<?php

namespace App\Models;

use App\Entities\LibraryMedia;
use CodeIgniter\Model;

class LibraryMediaModel extends Model
{
    protected $table         = 'library_media';
    protected $primaryKey    = 'id';
    protected $returnType    = LibraryMedia::class;
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'library_page_id', 'sequence', 'media_kind', 'media_asset_id', 'external_url',
        'poster_media_id', 'caption_id', 'caption_en', 'credit', 'is_active',
    ];
    protected $validationRules = [
        'library_page_id' => 'required|is_natural_no_zero',
        'sequence'        => 'required|is_natural_no_zero',
        'media_kind'      => 'required|in_list[image,video]',
        'external_url'    => 'permit_empty|max_length[1000]',
        'caption_id'      => 'permit_empty|max_length[500]',
        'caption_en'      => 'permit_empty|max_length[500]',
        'credit'          => 'permit_empty|max_length[300]',
    ];

    /**
     * Media aktif untuk banyak halaman sekaligus — satu query.
     *
     * @param list<int> $pageIds
     *
     * @return array<int, list<LibraryMedia>> keyed by library_page_id
     */
    public function forPages(array $pageIds, bool $activeOnly = true): array
    {
        $pageIds = array_values(array_unique(array_filter(array_map('intval', $pageIds))));

        if ($pageIds === []) {
            return [];
        }

        $builder = $this->whereIn('library_page_id', $pageIds);

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        $out = [];

        foreach ($builder->orderBy('library_page_id', 'ASC')->orderBy('sequence', 'ASC')->orderBy('id', 'ASC')->findAll() as $media) {
            $out[$media->library_page_id][] = $media;
        }

        return $out;
    }
}
