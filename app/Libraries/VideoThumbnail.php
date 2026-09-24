<?php

namespace App\Libraries;

use App\Entities\LibraryMedia;
use App\Models\LibraryMediaModel;
use CodeIgniter\HTTP\CURLRequest;

/**
 * Poster video Pustaka dari thumbnail penyedia, diunduh SERVER satu kali.
 *
 * Kartu video YouTube/Vimeo/Drive di Pustaka butuh gambar sampul, tetapi
 * perangkat siswa tidak boleh menghubungi domain luar sebelum tombol Putar
 * ditekan (aturan 10 docs/05, komentar MediaLink). Jadi thumbnail diambil di
 * sini, disimpan lewat MediaStore sebagai aset gambar biasa
 * (`library.thumb.*`), lalu dipasang di `library_media.poster_media_id`.
 * Siswa hanya pernah meminta berkas itu dari server GELITA sendiri.
 *
 * Sumber per penyedia:
 * - YouTube: i.ytimg.com/vi/{id}/maxresdefault.jpg, cadangan hqdefault.jpg
 *   (maxres tidak ada untuk video lama/beresolusi rendah).
 * - Vimeo  : oEmbed vimeo.com/api/oembed.json → `thumbnail_url` (i.vimeocdn.com).
 * - Drive  : drive.google.com/thumbnail?id={id}&sz=w1280 (dialihkan ke
 *   *.googleusercontent.com). Berkas privat mengembalikan halaman HTML
 *   masuk Google, bukan gambar — karena itu Content-Type dan isi diperiksa.
 *
 * Tidak pernah melempar: galat jaringan, respons bukan gambar, atau berkas
 * terlalu besar cukup dicatat log_message() dan baris itu dilewati, sehingga
 * penyimpanan konten tidak pernah gagal karena thumbnail.
 */
class VideoThumbnail
{
    /** Penyedia yang dilayani; video unggahan & berkas langsung tidak butuh ini. */
    public const PROVIDERS = ['youtube', 'vimeo', 'drive'];

    /** Batas ukuran satu thumbnail. Thumbnail 1280px biasanya < 300 KB. */
    public const MAX_BYTES = 4 * 1024 * 1024;

    /** Detik; permintaan ini berjalan di tengah request admin. */
    public const TIMEOUT = 6;

    public const CONNECT_TIMEOUT = 3;

    private ?CURLRequest $http;

    private ?MediaStore $store;

    /** @var list<string> alasan kegagalan terakhir, untuk pesan admin/CLI */
    private array $errors = [];

    public function __construct(?CURLRequest $http = null, ?MediaStore $store = null)
    {
        $this->http  = $http;
        $this->store = $store;
    }

    /**
     * Unduh thumbnail untuk satu tautan video.
     *
     * @return array{bytes: string, mime: string, source: string}|null null bila
     *         bukan tautan YouTube/Vimeo/Drive atau tidak ada gambar yang sah
     */
    public function download(string $url): ?array
    {
        $link = MediaLink::parse($url, 'video');

        if ($link === null || ! in_array($link['provider'], self::PROVIDERS, true) || empty($link['video_id'])) {
            return null;
        }

        foreach ($this->candidates($link) as $candidate) {
            $image = $this->fetchImage($candidate);

            if ($image !== null) {
                return $image + ['source' => $candidate];
            }
        }

        return null;
    }

    /**
     * Alamat thumbnail yang dicoba berurutan untuk hasil MediaLink::parse().
     * Vimeo butuh satu permintaan oEmbed lebih dulu untuk mengetahui alamatnya.
     *
     * @param array<string, mixed> $link
     *
     * @return list<string>
     */
    public function candidates(array $link): array
    {
        $id = (string) ($link['video_id'] ?? '');

        return match ($link['provider'] ?? '') {
            'youtube' => [
                'https://i.ytimg.com/vi/' . $id . '/maxresdefault.jpg',
                'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg',
            ],
            'vimeo' => array_values(array_filter([$this->vimeoThumbnail((string) $link['href'])])),
            'drive' => ['https://drive.google.com/thumbnail?id=' . rawurlencode($id) . '&sz=w1280'],
            default => [],
        };
    }

    /**
     * Isi poster satu baris `library_media` yang berupa video luar.
     *
     * @return string filled | skipped (bukan video luar / poster sudah ada) | failed
     */
    public function fillPoster(LibraryMedia $media, bool $force = false, ?int $staffId = null): string
    {
        $url  = trim((string) $media->external_url);
        $link = $url === '' ? null : MediaLink::parse($url, (string) $media->media_kind);

        if ($link === null || $link['kind'] !== 'video' || ! in_array($link['provider'], self::PROVIDERS, true)) {
            return 'skipped';
        }

        if ($media->poster_media_id !== null && ! $force) {
            return 'skipped';
        }

        $image = $this->download($url);

        if ($image === null) {
            return $this->failed($media, $link['label'] . ': thumbnail tidak dapat diunduh (video privat, dihapus, atau server tanpa akses internet).');
        }

        $key    = self::assetKey($link['provider'], (string) $link['video_id']);
        $stored = $this->store()->storeBytes($image['bytes'], $key, $staffId, 'video_thumbnail');

        if ($stored['error'] !== null) {
            return $this->failed($media, $stored['error']);
        }

        $model = model(LibraryMediaModel::class);

        if (! $model->update($media->id, ['poster_media_id' => $stored['id']])) {
            return $this->failed($media, 'Poster ditolak: ' . implode(' ', $model->errors()));
        }

        service('contentRepository')->flush();

        return 'filled';
    }

    /**
     * Isi poster untuk banyak baris sekaligus: seluruh video luar yang
     * posternya kosong, atau hanya $ids (baris yang baru disimpan admin/impor).
     *
     * @param list<int>|null $ids
     *
     * @return array{filled: int, skipped: int, failed: int, errors: list<string>}
     */
    public function fillMissing(?array $ids = null, bool $force = false, ?int $staffId = null): array
    {
        $summary      = ['filled' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];
        $this->errors = [];

        if ($ids !== null && $ids === []) {
            return $summary;
        }

        $builder = model(LibraryMediaModel::class)
            ->where('media_kind', 'video')
            ->where('external_url IS NOT NULL', null, false);

        if ($ids !== null) {
            $builder->whereIn('id', array_values(array_unique(array_map('intval', $ids))));
        }

        if (! $force) {
            $builder->where('poster_media_id', null);
        }

        foreach ($builder->orderBy('id', 'ASC')->findAll() as $media) {
            $summary[$this->fillPoster($media, $force, $staffId)]++;
        }

        $summary['errors'] = $this->errors;

        return $summary;
    }

    /**
     * asset_key poster: satu per video, sehingga video yang sama di beberapa
     * halaman memakai satu berkas. Id YouTube peka huruf besar-kecil,
     * sedangkan asset_key dan nama berkas tidak boleh bergantung pada itu —
     * maka ditambah potongan sha1 dari id aslinya.
     */
    public static function assetKey(string $provider, string $videoId): string
    {
        return 'library.thumb.' . MediaStore::slug($provider) . '.'
            . MediaStore::slug($videoId) . '-' . substr(sha1($videoId), 0, 8);
    }

    /** @return list<string> */
    public function errors(): array
    {
        return $this->errors;
    }

    // -------------------------------------------------------------- bantuan

    /**
     * GET satu alamat dan kembalikan isinya hanya bila benar gambar raster:
     * status 200, Content-Type image/*, ukuran ≤ MAX_BYTES, dan dapat dibaca
     * getimagesizefromstring(). SVG ditolak.
     *
     * @return array{bytes: string, mime: string}|null
     */
    private function fetchImage(string $url): ?array
    {
        $response = $this->get($url, 'image/*');

        if ($response === null) {
            return null;
        }

        [$status, $type, $body] = $response;

        if ($status !== 200 || ! str_starts_with($type, 'image/') || str_contains($type, 'svg')) {
            log_message('info', 'Thumbnail {url} ditolak: status {status}, tipe {type}.', ['url' => $url, 'status' => $status, 'type' => $type ?: '-']);

            return null;
        }

        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            log_message('info', 'Thumbnail {url} ditolak: ukuran {size} byte.', ['url' => $url, 'size' => strlen($body)]);

            return null;
        }

        $size = @getimagesizefromstring($body);

        if (! is_array($size) || ! in_array($size['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            log_message('info', 'Thumbnail {url} ditolak: isi bukan gambar.', ['url' => $url]);

            return null;
        }

        // YouTube kadang mengirim gambar abu-abu 120×90 untuk maxres yang tidak ada
        if ((int) $size[0] <= 120 && (int) $size[1] <= 90) {
            return null;
        }

        return ['bytes' => $body, 'mime' => (string) $size['mime']];
    }

    /** Alamat thumbnail Vimeo dari oEmbed; hanya https di *.vimeocdn.com yang diterima. */
    private function vimeoThumbnail(string $href): ?string
    {
        $response = $this->get('https://vimeo.com/api/oembed.json?width=1280&url=' . rawurlencode($href), 'application/json');

        if ($response === null || $response[0] !== 200) {
            return null;
        }

        $data = json_decode($response[2], true);
        $url  = is_array($data) ? (string) ($data['thumbnail_url'] ?? '') : '';
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (parse_url($url, PHP_URL_SCHEME) !== 'https' || ! str_ends_with('.' . $host, '.vimeocdn.com')) {
            return null;
        }

        return $url;
    }

    /**
     * @return array{0: int, 1: string, 2: string}|null [status, content-type, isi]; null bila galat jaringan
     */
    private function get(string $url, string $accept): ?array
    {
        try {
            $response = $this->http()->get($url, [
                'timeout'         => self::TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'http_errors'     => false,
                'allow_redirects' => ['max' => 3, 'strict' => true, 'protocols' => ['https']],
                'headers'         => ['Accept' => $accept, 'User-Agent' => 'GELITA/1.0 (thumbnail Pustaka)'],
            ]);

            $type = strtolower(trim(explode(';', $response->getHeaderLine('Content-Type'))[0]));

            return [$response->getStatusCode(), $type, (string) $response->getBody()];
        } catch (\Throwable $e) {
            log_message('warning', 'Thumbnail {url} gagal diunduh: {msg}', ['url' => $url, 'msg' => $e->getMessage()]);

            return null;
        }
    }

    private function failed(LibraryMedia $media, string $reason): string
    {
        log_message('warning', 'Poster media pustaka {id} tidak terisi: {reason}', ['id' => $media->id, 'reason' => $reason]);

        $this->errors[] = 'Media pustaka #' . $media->id . ': ' . $reason;

        return 'failed';
    }

    private function http(): CURLRequest
    {
        // Instans baru tiap kali: CURLRequest menyimpan opsi antarpermintaan
        return $this->http ?? service('curlrequest', [], null, null, false);
    }

    private function store(): MediaStore
    {
        return $this->store ??= new MediaStore();
    }
}
