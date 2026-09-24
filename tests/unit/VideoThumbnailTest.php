<?php

use App\Entities\LibraryMedia;
use App\Libraries\MediaLink;
use App\Libraries\MediaStore;
use App\Libraries\VideoThumbnail;
use App\Models\LibraryMediaModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCURLRequest;
use Config\App;

/**
 * Thumbnail video Pustaka diunduh SERVER (perangkat siswa tidak menghubungi
 * domain luar sebelum Putar ditekan).
 *
 * - alamat thumbnail per penyedia (YouTube maxres → hq, Vimeo oEmbed, Drive);
 * - respons HTML (Drive privat), bukan gambar, atau terlalu besar ditolak;
 * - galat jaringan tidak pernah melempar;
 * - poster terpasang ke library_media hanya bila berhasil.
 *
 * Tanpa jaringan sungguhan: HTTP diganti MockCURLRequest yang menjawab per
 * URL; tanpa database: MediaStore dan LibraryMediaModel diganti tiruan.
 *
 * @internal
 */
final class VideoThumbnailTest extends CIUnitTestCase
{
    private const YT = 'https://www.youtube.com/watch?v=IXDk03NgHqQ';

    /** URL → [status, content-type, isi] | 'error' */
    public array $responses = [];

    /** URL yang diminta, berurutan */
    public array $requested = [];

    /** Panggilan storeBytes tiruan: [asset_key, ukuran] */
    public array $stored = [];

    /** Panggilan LibraryMediaModel::update tiruan: [id, data] */
    public array $updates = [];

    protected function tearDown(): void
    {
        Factories::reset('models');
        parent::tearDown();
    }

    // ------------------------------------------------------------ alamat

    public function testCandidateUrlsPerProvider(): void
    {
        $thumbs = $this->thumbnail();

        $this->assertSame([
            'https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg',
            'https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg',
        ], $thumbs->candidates(MediaLink::parse(self::YT)));

        $this->assertSame(
            ['https://drive.google.com/thumbnail?id=1AbCdEfGhIjKlMnOp_qrs&sz=w1280'],
            $thumbs->candidates(MediaLink::parse('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp_qrs/view', 'video')),
        );

        $this->responses['https://vimeo.com/api/oembed.json?width=1280&url=https%3A%2F%2Fvimeo.com%2F123456789']
            = [200, 'application/json', json_encode(['thumbnail_url' => 'https://i.vimeocdn.com/video/555-abc_1280'])];

        $this->assertSame(
            ['https://i.vimeocdn.com/video/555-abc_1280'],
            $thumbs->candidates(MediaLink::parse('https://vimeo.com/123456789')),
        );
        $this->assertSame([], $thumbs->candidates(MediaLink::parse('https://example.org/a.jpg')));
    }

    public function testVimeoThumbnailOutsideVimeoCdnIsIgnored(): void
    {
        $this->responses['https://vimeo.com/api/oembed.json?width=1280&url=https%3A%2F%2Fvimeo.com%2F123456789']
            = [200, 'application/json', json_encode(['thumbnail_url' => 'http://169.254.169.254/latest/meta-data'])];

        $this->assertSame([], $this->thumbnail()->candidates(MediaLink::parse('https://vimeo.com/123456789')));
    }

    // ------------------------------------------------------------ unduh

    public function testYoutubeUsesMaxresWhenAvailable(): void
    {
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg'] = [200, 'image/jpeg', $this->jpeg(1280, 720)];

        $image = $this->thumbnail()->download(self::YT);

        $this->assertNotNull($image);
        $this->assertSame('image/jpeg', $image['mime']);
        $this->assertSame('https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg', $image['source']);
        $this->assertCount(1, $this->requested);
    }

    public function testYoutubeFallsBackFromMaxresToHq(): void
    {
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg'] = [404, 'image/jpeg', $this->jpeg(120, 90)];
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg']     = [200, 'image/jpeg', $this->jpeg(480, 360)];

        $image = $this->thumbnail()->download(self::YT);

        $this->assertSame('https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg', $image['source'] ?? null);
    }

    public function testYoutubeGreyPlaceholderCountsAsMissing(): void
    {
        // maxres yang tidak ada kadang tetap 200 dengan gambar abu-abu 120×90
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg'] = [200, 'image/jpeg', $this->jpeg(120, 90)];
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg']     = [200, 'image/jpeg', $this->jpeg(480, 360)];

        $this->assertSame('https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg', $this->thumbnail()->download(self::YT)['source'] ?? null);
    }

    public function testPrivateDriveHtmlPageIsRejected(): void
    {
        $this->responses['https://drive.google.com/thumbnail?id=1AbCdEfGhIjKlMnOp_qrs&sz=w1280']
            = [200, 'text/html; charset=utf-8', '<!doctype html><title>Masuk - Akun Google</title>'];

        $this->assertNull($this->thumbnail()->download('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp_qrs/view'));
    }

    public function testImageHeaderWithNonImageBodyIsRejected(): void
    {
        $this->responses['https://drive.google.com/thumbnail?id=1AbCdEfGhIjKlMnOp_qrs&sz=w1280'] = [200, 'image/png', '<html>bukan gambar</html>'];

        $this->assertNull($this->thumbnail()->download('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp_qrs/view'));
    }

    public function testSvgAndOversizedResponsesAreRejected(): void
    {
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg'] = [200, 'image/svg+xml', '<svg xmlns="http://www.w3.org/2000/svg"/>'];
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg']     = [200, 'image/jpeg', $this->jpeg(480, 360) . str_repeat("\0", VideoThumbnail::MAX_BYTES)];

        $this->assertNull($this->thumbnail()->download(self::YT));
    }

    public function testNetworkErrorDoesNotThrow(): void
    {
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg'] = 'error';
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg']     = 'error';

        $this->assertNull($this->thumbnail()->download(self::YT));
        $this->assertCount(2, $this->requested, 'cadangan hq tetap dicoba');
    }

    public function testNonVideoLinksAreNeverRequested(): void
    {
        $thumbs = $this->thumbnail();

        $this->assertNull($thumbs->download('https://commons.wikimedia.org/wiki/File:Borobudur.jpg'));
        $this->assertNull($thumbs->download('javascript:alert(1)'));
        $this->assertSame([], $this->requested);
    }

    // ------------------------------------------------------------ poster

    public function testFillPosterStoresTheThumbnailAndLinksIt(): void
    {
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg'] = [200, 'image/jpeg', $this->jpeg(1280, 720)];
        $this->fakeModel();

        $this->assertSame('filled', $this->thumbnail()->fillPoster($this->media(7, self::YT)));

        $this->assertCount(1, $this->stored);
        $this->assertSame('library.thumb.youtube.ixdk03nghqq-' . substr(sha1('IXDk03NgHqQ'), 0, 8), $this->stored[0][0]);
        $this->assertSame([[7, ['poster_media_id' => 99]]], $this->updates);
    }

    public function testFillPosterFailureKeepsTheRowUntouched(): void
    {
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/maxresdefault.jpg'] = 'error';
        $this->responses['https://i.ytimg.com/vi/IXDk03NgHqQ/hqdefault.jpg']     = [200, 'text/html', '<html></html>'];
        $this->fakeModel();

        $thumbs = $this->thumbnail();

        $this->assertSame('failed', $thumbs->fillPoster($this->media(7, self::YT)));
        $this->assertSame([], $this->stored);
        $this->assertSame([], $this->updates);
        $this->assertCount(1, $thumbs->errors());
    }

    public function testFillPosterSkipsRowsThatDoNotNeedIt(): void
    {
        $thumbs = $this->thumbnail();

        $this->assertSame('skipped', $thumbs->fillPoster($this->media(1, self::YT, poster: 5)), 'poster sudah ada');
        $this->assertSame('skipped', $thumbs->fillPoster($this->media(2, 'https://commons.wikimedia.org/wiki/File:Candi.webm')), 'video berkas langsung');
        $this->assertSame('skipped', $thumbs->fillPoster($this->media(3, null)), 'unggahan');
        $this->assertSame([], $this->requested);
    }

    public function testAssetKeyIsStableAndCaseSafe(): void
    {
        $this->assertNotSame(VideoThumbnail::assetKey('youtube', 'abcDEF12345'), VideoThumbnail::assetKey('youtube', 'abcdef12345'));
        $this->assertMatchesRegularExpression('/^library\.thumb\.youtube\.[a-z0-9.-]+-[0-9a-f]{8}$/', VideoThumbnail::assetKey('youtube', 'IXDk03NgHqQ'));
    }

    // ------------------------------------------------------------ bantuan

    private function thumbnail(): VideoThumbnail
    {
        $test = $this;

        $http = new class ($test) extends MockCURLRequest {
            public function __construct(private VideoThumbnailTest $test)
            {
                parent::__construct(new App(), new URI('https://example.com/'));
            }

            protected function sendRequest(array $curlOptions = []): string
            {
                $this->response = clone $this->responseOrig;

                $url                     = (string) $curlOptions[CURLOPT_URL];
                $this->test->requested[] = $url;
                $route                   = $this->test->responses[$url] ?? [404, 'text/html', 'Not Found'];

                if ($route === 'error') {
                    throw HTTPException::forCurlError('28', 'Operation timed out');
                }

                [$status, $type, $body] = $route;

                return "HTTP/1.1 {$status} X\r\nContent-Type: {$type}\r\n\r\n{$body}";
            }
        };

        $store = new class ($test) extends MediaStore {
            public function __construct(private VideoThumbnailTest $test)
            {
            }

            public function storeBytes(string $bytes, string $assetKey, ?int $staffId = null, string $via = 'download'): array
            {
                $this->test->stored[] = [$assetKey, strlen($bytes)];

                return ['id' => 99, 'type' => 'image', 'error' => null];
            }
        };

        return new VideoThumbnail($http, $store);
    }

    private function fakeModel(): void
    {
        $test = $this;

        Factories::injectMock('models', LibraryMediaModel::class, new class ($test) extends LibraryMediaModel {
            public function __construct(private VideoThumbnailTest $test)
            {
            }

            public function update($id = null, $row = null): bool
            {
                $this->test->updates[] = [$id, $row];

                return true;
            }
        });
    }

    private function media(int $id, ?string $url, ?int $poster = null): LibraryMedia
    {
        $media = new LibraryMedia();
        $media->injectRawData([
            'id' => $id, 'library_page_id' => 1, 'sequence' => 1, 'media_kind' => 'video',
            'media_asset_id' => $url === null ? 3 : null, 'external_url' => $url, 'poster_media_id' => $poster,
        ]);

        return $media;
    }

    private function jpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 150, 90));
        ob_start();
        imagejpeg($image, null, 60);

        return (string) ob_get_clean();
    }
}
