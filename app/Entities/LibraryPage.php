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

    /** @var list<LibraryMedia> dimuat ContentRepository::libraryPages() */
    private array $eagerMedia = [];

    /** @param list<LibraryMedia> $media */
    public function setLoadedMedia(array $media): static
    {
        $this->eagerMedia = $media;

        return $this;
    }

    /** @return list<LibraryMedia> */
    public function loadedMedia(): array
    {
        return $this->eagerMedia;
    }

    /**
     * Galeri halaman siap-render dalam urutan `sequence`: berkas yang belum
     * diunggah dan tautan yang tidak sah sudah dibuang.
     *
     * @return list<array<string, mixed>>
     */
    public function gallery(string $locale): array
    {
        $out = [];

        foreach ($this->eagerMedia as $media) {
            $resolved = $media->resolve();

            if ($resolved === null) {
                continue;
            }

            $out[] = $resolved + [
                'id'      => $media->id,
                'caption' => $media->text('caption', $locale),
                'credit'  => (string) ($media->credit ?? ''),
            ];
        }

        return $out;
    }
}
