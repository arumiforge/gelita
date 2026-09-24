<?php

use App\Libraries\MediaLink;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tautan media luar Pustaka Kedu: hanya bentuk yang dikenali yang dirender,
 * YouTube lewat domain nocookie, dan skema selain http(s) selalu ditolak.
 *
 * @internal
 */
final class MediaLinkTest extends CIUnitTestCase
{
    public function testYoutubeVariantsBecomeNocookieEmbed(): void
    {
        foreach ([
            'https://www.youtube.com/watch?v=IXDk03NgHqQ',
            'https://youtu.be/IXDk03NgHqQ',
            'https://m.youtube.com/watch?v=IXDk03NgHqQ&feature=share',
            'https://www.youtube.com/shorts/IXDk03NgHqQ',
            'https://www.youtube.com/embed/IXDk03NgHqQ',
        ] as $url) {
            $link = MediaLink::parse($url);

            $this->assertSame('youtube', $link['provider'], $url);
            $this->assertSame('video', $link['kind'], $url);
            $this->assertNull($link['src'], $url);
            $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/IXDk03NgHqQ?', $link['embed'], $url);
            $this->assertSame('https://www.youtube.com/watch?v=IXDk03NgHqQ', $link['href'], $url);
        }
    }

    public function testYoutubeStartTimeIsKept(): void
    {
        $link = MediaLink::parse('https://youtu.be/IXDk03NgHqQ?t=1m30s');

        $this->assertStringContainsString('&start=90', $link['embed']);
        $this->assertStringEndsWith('&t=90s', $link['href']);
    }

    public function testInvalidYoutubeIdFallsBackToPlainLink(): void
    {
        $link = MediaLink::parse('https://www.youtube.com/watch?v=<script>');

        $this->assertNotSame('youtube', $link['provider'] ?? null);
    }

    public function testDriveImageUsesThumbnailAndVideoUsesPreview(): void
    {
        $url   = 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp_qrs/view?usp=sharing';
        $image = MediaLink::parse($url, 'image');
        $video = MediaLink::parse($url, 'video');

        $this->assertSame('drive', $image['provider']);
        $this->assertSame('https://drive.google.com/thumbnail?id=1AbCdEfGhIjKlMnOp_qrs&sz=w1600', $image['src']);
        $this->assertNull($image['embed']);

        $this->assertSame('video', $video['kind']);
        $this->assertNull($video['src']);
        $this->assertSame('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp_qrs/preview', $video['embed']);

        $this->assertSame('drive', MediaLink::parse('https://drive.google.com/open?id=1AbCdEfGhIjKlMnOp_qrs')['provider']);
    }

    public function testVimeoIsDoNotTrack(): void
    {
        $link = MediaLink::parse('https://vimeo.com/123456789');

        $this->assertSame('vimeo', $link['provider']);
        $this->assertSame('https://player.vimeo.com/video/123456789?dnt=1', $link['embed']);
    }

    public function testCommonsFilePageResolvesToScaledFile(): void
    {
        $link = MediaLink::parse('https://commons.wikimedia.org/wiki/File:Candi_Borobudur_(Borobudur_Temple).jpg');

        $this->assertSame('commons', $link['provider']);
        $this->assertSame('image', $link['kind']);
        $this->assertStringStartsWith('https://commons.wikimedia.org/wiki/Special:FilePath/', $link['src']);
        $this->assertStringEndsWith('?width=1280', $link['src']);
        $this->assertStringStartsWith('https://commons.wikimedia.org/wiki/File:', $link['href']);

        $video = MediaLink::parse('https://commons.wikimedia.org/wiki/File:Tari_Lengger.webm');

        $this->assertSame('video', $video['kind']);
        $this->assertStringEndsNotWith('?width=1280', $video['src']);
    }

    public function testDirectFilesAndOtherPages(): void
    {
        $image = MediaLink::parse('https://example.org/foto/candi.JPG', 'video');
        $video = MediaLink::parse('https://example.org/klip.mp4');
        $page  = MediaLink::parse('https://example.org/artikel/temanggung');

        $this->assertSame(['file', 'image'], [$image['provider'], $image['kind']]);
        $this->assertSame(['file', 'video'], [$video['provider'], $video['kind']]);
        $this->assertSame(['link', 'image', null, null], [$page['provider'], $page['kind'], $page['src'], $page['embed']]);
        $this->assertSame('example.org', $page['label']);
    }

    public function testRejectsNonHttpAndMalformedUrls(): void
    {
        foreach ([
            '',
            'javascript:alert(1)',
            'data:image/png;base64,AAAA',
            'ftp://example.org/a.jpg',
            'file:///etc/passwd',
            'bukan tautan',
            'https://example.org/' . str_repeat('a', 1000),
        ] as $url) {
            $this->assertNull(MediaLink::parse($url), $url);
        }
    }
}
