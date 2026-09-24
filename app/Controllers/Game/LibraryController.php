<?php

namespace App\Controllers\Game;

use App\Entities\LibraryPage;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Pustaka Kedu — bacaan pendamping tiap wilayah.
 *
 * `/pustaka` (index) menampilkan rak Pustaka Kedu: satu kartu per wilayah.
 * `/pustaka/{code}` adalah buku satu wilayah, "Pustaka {wilayah}".
 *
 * Pustaka sebuah wilayah baru terbuka setelah SEMUA tantangan wilayah itu
 * selesai pada sesi ini, apa pun unlock mode studinya
 * (GameProgress::libraryStatus()). Sebelum itu `/pustaka/{code}` merender
 * halaman terkunci — bukan redirect — agar penjelasannya tetap terbaca tanpa
 * JavaScript; `library_opened` hanya dicatat bila isi buku benar-benar tampil.
 *
 * Membuka pustaka tidak memengaruhi skor, bintang, atau status node sama sekali.
 */
class LibraryController extends BaseGameController
{
    public function index(): string
    {
        $session = $this->session();
        $locale  = $session->resolvedLocale();
        $content = service('contentRepository');
        $regions = [];

        foreach ($this->levelOverview($session) as $row) {
            $pages = $content->libraryPages($row['id']);

            $regions[] = $row + [
                'library' => $this->libraryStatus($row['status']),
                'pages'   => count($pages),
                'cover'   => $this->cover($pages, $row['background'] ?? null, $locale),
            ];
        }

        return view('game/library-index', $this->hudData() + ['regions' => $regions]);
    }

    public function show(string $code): string|RedirectResponse
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);

        if (! $this->levelUnlocked($session, $level->sequence)) {
            return redirect()->to(site_url('peta'))->with('error', lang('Game.levelLocked'));
        }

        $access = $this->libraryAccess($session, $level);
        $data   = $this->hudData() + [
            'level'  => $level,
            'pages'  => service('contentRepository')->libraryPages($level->id),
            'access' => $access,
        ];

        if ($access['status'] !== 'open') {
            return view('game/library-locked', $data);
        }

        service('eventService')->record($session, 'library_opened', [], ['level_id' => $level->id]);

        return view('game/library', $data);
    }

    /**
     * Sampul kartu rak: gambar pertama galeri halaman pertama, lalu latar
     * wilayah; null → view memakai gradien.
     *
     * @param list<LibraryPage> $pages
     */
    private function cover(array $pages, ?string $background, string $locale): ?string
    {
        $first = $pages[0] ?? null;

        foreach ($first?->gallery($locale) ?? [] as $media) {
            if ($media['kind'] === 'image' && $media['src'] !== null) {
                return $media['src'];
            }
        }

        return $background;
    }
}
