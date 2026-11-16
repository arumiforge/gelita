<?php

use App\Libraries\MediaStore;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * finfo on Windows (Laragon) reads a WebP written by GD as
 * application/octet-stream, which made `gelita:katalog:impor` refuse the
 * re-encoded pictures ("Jenis berkas ini tidak diizinkan di sini"). The
 * reported type is passed in to stand for that finfo answer.
 *
 * @internal
 */
final class MediaStoreSniffTest extends CIUnitTestCase
{
    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    public function testImagesAreRecognisedFromTheirFirstBytes(): void
    {
        $store = new MediaStore();
        $image = imagecreatetruecolor(8, 8);

        foreach (['webp' => 'image/webp', 'png' => 'image/png', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif'] as $format => $mime) {
            $path = $this->file($format);
            ('image' . $format)($image, $path);

            $this->assertSame($mime, $store->detectMime($path, 'application/octet-stream'), $format);
            $this->assertSame($mime, $store->detectMime($path), $format . ' through finfo');
        }
    }

    public function testOtherFilesKeepTheirType(): void
    {
        $store = new MediaStore();

        $text = $this->file('txt');
        file_put_contents($text, 'RIFF but not a picture at all');
        $this->assertSame('application/octet-stream', $store->detectMime($text, 'application/octet-stream'));

        // A known type from finfo is never second-guessed by the signature
        $png = $this->file('png');
        imagepng(imagecreatetruecolor(4, 4), $png);
        $this->assertSame('image/png', $store->detectMime($png, 'image/png'));

        // Audio still wins: RIFF…WAVE is a recording, not a picture
        $wav = $this->file('wav');
        file_put_contents($wav, 'RIFF' . pack('V', 36) . 'WAVEfmt ' . str_repeat("\x00", 24));
        $this->assertSame('audio/wav', $store->detectMime($wav, 'application/octet-stream'));
    }

    private function file(string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sniff') . '.' . $extension;
        $this->files[] = $path;

        return $path;
    }
}
