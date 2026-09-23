<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use App\Libraries\MediaLink;
use CodeIgniter\Entity\Entity;

/**
 * Satu gambar/video di halaman Pustaka Kedu: berkas unggahan atau tautan luar.
 */
class LibraryMedia extends Entity
{
    use Bilingual;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'              => 'int',
        'library_page_id' => 'int',
        'sequence'        => 'int',
        'media_asset_id'  => '?int',
        'poster_media_id' => '?int',
        'is_active'       => 'boolean',
    ];

    /**
     * Bentuk siap-render, atau null bila medianya tidak dapat ditampilkan
     * (berkas belum diunggah / nonaktif, atau tautan tidak sah).
     *
     * @return array{kind: string, provider: string, src: string|null, embed: string|null,
     *               href: string|null, label: string, poster: string|null}|null
     */
    public function resolve(): ?array
    {
        $poster = media_exists($this->poster_media_id) ? media_src($this->poster_media_id) : null;

        if ($this->media_asset_id !== null) {
            if (! media_exists($this->media_asset_id)) {
                return null;
            }

            $src = media_src($this->media_asset_id);

            return [
                'kind'     => $this->media_kind === 'video' ? 'video' : 'image',
                'provider' => 'upload',
                'src'      => $src,
                'embed'    => null,
                'href'     => $src,
                'label'    => '',
                'poster'   => $poster,
            ];
        }

        $link = MediaLink::parse((string) $this->external_url, (string) $this->media_kind);

        return $link === null ? null : $link + ['poster' => $poster];
    }
}
