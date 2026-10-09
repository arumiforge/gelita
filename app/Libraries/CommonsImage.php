<?php

namespace App\Libraries;

use App\Entities\LibraryMedia;
use App\Models\LibraryMediaModel;
use App\Models\MediaAssetModel;
use CodeIgniter\HTTP\CURLRequest;

/**
 * Local copies of the library's Wikimedia Commons images (owner decision P4a,
 * docs/rencana-paket-aset.md §2.2 and §6.1 step 2).
 *
 * Library images that are only a Commons link are loaded by every student
 * device straight from Wikimedia, so they cannot be packaged. The package
 * builder calls copy() for each such row: the server downloads the same
 * 1280 px rendition MediaLink already shows, stores it through MediaStore as
 * an ordinary image `media_assets` row (`library.commons.*`, credit copied
 * from the library row), and sets `library_media.media_asset_id`. The
 * Commons link stays in `external_url`, so LibraryMedia::resolve() can keep
 * showing the source link next to the credit.
 *
 * Never throws: a network error or a non-image answer is returned as an
 * error string, the row keeps its link, and the build only warns.
 */
class CommonsImage
{
    public const TIMEOUT = 20;

    public const CONNECT_TIMEOUT = 5;

    private ?CURLRequest $http;

    private ?MediaStore $store;

    public function __construct(?CURLRequest $http = null, ?MediaStore $store = null)
    {
        $this->http  = $http;
        $this->store = $store;
    }

    /**
     * Active library image rows that still point to a Commons link only.
     *
     * @return list<LibraryMedia>
     */
    public function pending(): array
    {
        $rows = model(LibraryMediaModel::class)
            ->where('is_active', 1)
            ->where('media_kind', 'image')
            ->where('media_asset_id', null)
            ->where('external_url IS NOT NULL', null, false)
            ->orderBy('id', 'ASC')
            ->findAll();

        return array_values(array_filter($rows, static function (LibraryMedia $media): bool {
            $link = MediaLink::parse((string) $media->external_url, 'image');

            return $link !== null && $link['provider'] === 'commons' && $link['kind'] === 'image';
        }));
    }

    /**
     * Download and attach one row.
     *
     * @return array{media_asset_id: int|null, error: string|null}
     */
    public function copy(LibraryMedia $media, ?int $staffId = null): array
    {
        $link = MediaLink::parse((string) $media->external_url, 'image');

        if ($link === null || $link['provider'] !== 'commons' || $link['kind'] !== 'image' || empty($link['src'])) {
            return ['media_asset_id' => null, 'error' => 'bukan tautan gambar Wikimedia Commons'];
        }

        $bytes = $this->download((string) $link['src']);

        if (is_string($bytes['error'])) {
            return ['media_asset_id' => null, 'error' => $bytes['error']];
        }

        $stored = $this->store()->storeBytes((string) $bytes['body'], self::assetKey((string) $link['href']), $staffId, 'library_commons');

        if ($stored['error'] !== null) {
            return ['media_asset_id' => null, 'error' => $stored['error']];
        }

        $credit = trim((string) $media->credit);
        model(MediaAssetModel::class)->update((int) $stored['id'], [
            'credit' => mb_substr($credit !== '' ? $credit : 'Wikimedia Commons', 0, 500),
        ]);

        if (! model(LibraryMediaModel::class)->update($media->id, ['media_asset_id' => $stored['id']])) {
            return ['media_asset_id' => null, 'error' => 'baris Pustaka tidak dapat diperbarui'];
        }

        service('contentRepository')->flush();

        return ['media_asset_id' => (int) $stored['id'], 'error' => null];
    }

    /**
     * asset_key for one Commons file: `library.commons.{name}-{sha1[0:8]}`.
     * File names are case-sensitive on Commons, keys are not, hence the hash.
     */
    public static function assetKey(string $commonsHref): string
    {
        $file = rawurldecode((string) preg_replace('~^.*/wiki/File:~', '', $commonsHref));
        $name = MediaStore::slug((string) pathinfo($file, PATHINFO_FILENAME));

        return 'library.commons.' . substr($name !== '' ? $name : 'gambar', 0, 120) . '-' . substr(sha1($file), 0, 8);
    }

    /** @return array{body: string|null, error: string|null} */
    protected function download(string $url): array
    {
        try {
            $response = $this->http()->get($url, [
                'timeout'         => self::TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'http_errors'     => false,
                'allow_redirects' => ['max' => 5, 'strict' => true, 'protocols' => ['https']],
                'headers'         => ['Accept' => 'image/*', 'User-Agent' => 'GELITA/1.0 (salinan gambar Pustaka)'],
            ]);
        } catch (\Throwable $e) {
            log_message('warning', 'Commons image {url} could not be downloaded: {msg}', ['url' => $url, 'msg' => $e->getMessage()]);

            return ['body' => null, 'error' => 'gagal diunduh (server tidak tersambung ke internet?)'];
        }

        $type = strtolower(trim(explode(';', $response->getHeaderLine('Content-Type'))[0]));
        $body = (string) $response->getBody();

        if ($response->getStatusCode() !== 200 || ! str_starts_with($type, 'image/') || str_contains($type, 'svg')) {
            return ['body' => null, 'error' => sprintf('jawaban Commons bukan gambar (status %d, %s)', $response->getStatusCode(), $type ?: '-')];
        }

        if ($body === '') {
            return ['body' => null, 'error' => 'berkas kosong'];
        }

        return ['body' => $body, 'error' => null];
    }

    private function http(): CURLRequest
    {
        return $this->http ?? service('curlrequest', [], null, null, false);
    }

    private function store(): MediaStore
    {
        return $this->store ??= new MediaStore();
    }
}
