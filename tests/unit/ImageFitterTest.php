<?php

use App\Libraries\ImageFitter;
use App\Libraries\MediaStore;
use App\Models\MediaAssetModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Fixed-size image slots accept a larger file with the same width-to-height
 * ratio and shrink it to the slot size; smaller files and other ratios are
 * refused (ImageFitter, MediaStore).
 *
 * @internal
 */
final class ImageFitterTest extends CIUnitTestCase
{
    use ResetTables;

    /** @var list<string> absolute paths removed after each test */
    private array $cleanup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createResetTables();
        Services::resetSingle('contentRepository');
    }

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->dropResetTables();
        parent::tearDown();
    }

    public function testExactAndLargerSameRatioAreAccepted(): void
    {
        $this->assertNull(ImageFitter::problem([1920, 1080], [1920, 1080], 'bg.test'));
        $this->assertNull(ImageFitter::problem([3840, 2160], [1920, 1080], 'bg.test'));
        $this->assertNull(ImageFitter::problem([1921, 1080], [1920, 1080], 'bg.test'), 'within the 1% ratio tolerance');
        $this->assertNull(ImageFitter::problem([2400, 2400], [1200, 1200], 'challenge.item.tmg-1-01'));
    }

    public function testSmallerIsRefused(): void
    {
        $error = ImageFitter::problem([1280, 720], [1920, 1080], 'bg.test');

        $this->assertNotNull($error);
        $this->assertStringContainsString('lebih kecil', $error);
        $this->assertStringContainsString('1920 × 1080', $error);

        $this->assertNotNull(ImageFitter::problem([1920, 1079], [1920, 1080], 'bg.test'), 'one pixel short is still smaller');
    }

    public function testOtherRatioIsRefusedEvenWhenLarger(): void
    {
        $error = ImageFitter::problem([1920, 1200], [1920, 1080], 'bg.test');

        $this->assertNotNull($error);
        $this->assertStringContainsString('Bentuk gambar', $error);
        $this->assertNotNull(ImageFitter::problem([2000, 1080], [1920, 1080], 'bg.test'), 'about 4% wider');
    }

    public function testFitShrinksPngAndKeepsTransparency(): void
    {
        $path = $this->image('png', 2560, 1440, transparent: true);

        $this->assertNull((new ImageFitter())->fit($path, 'image/png', [1280, 720]));

        [$width, $height, $kind] = getimagesize($path);
        $this->assertSame([1280, 720, IMAGETYPE_PNG], [$width, $height, $kind]);

        $image = imagecreatefrompng($path);
        $this->assertSame(127, (imagecolorat($image, 5, 5) >> 24) & 0x7F, 'transparent corner stays transparent');
    }

    public function testFitCropsTheToleranceSliverFromJpeg(): void
    {
        $path = $this->image('jpg', 1930, 1080);

        $this->assertNull((new ImageFitter())->fit($path, 'image/jpeg', [1920, 1080]));
        $this->assertSame([1920, 1080, IMAGETYPE_JPEG], array_slice(getimagesize($path), 0, 3));
    }

    public function testOversizedGifIsRefusedAndLeftUntouched(): void
    {
        $path = $this->image('gif', 480, 480);
        $hash = hash_file('sha256', $path);

        $error = (new ImageFitter())->fit($path, 'image/gif', [240, 240]);

        $this->assertNotNull($error);
        $this->assertStringContainsString('PNG', $error);
        $this->assertSame($hash, hash_file('sha256', $path));
    }

    public function testStoreCopyShrinksALargerImageToTheSlotSize(): void
    {
        $key    = 'challenge.item.zz-4-' . bin2hex(random_bytes(3));
        $source = $this->image('png', 480, 480);

        $result = (new MediaStore())->storeCopy($source, $key, ['image'], null, true);
        $this->assertNull($result['error']);

        $row             = model(MediaAssetModel::class)->findByKey($key);
        $this->cleanup[] = FCPATH . $row['storage_path'];

        $this->assertSame(['240', '240'], [(string) $row['width_px'], (string) $row['height_px']]);
        $this->assertSame([240, 240], array_slice(getimagesize(FCPATH . $row['storage_path']), 0, 2));
        $this->assertSame(hash_file('sha256', FCPATH . $row['storage_path']), $row['sha256'], 'hash of the shrunk file');
        $this->assertSame([480, 480], array_slice(getimagesize($source), 0, 2), 'the source file is not changed');
    }

    public function testStoreCopyRefusesSmallerOrOtherRatio(): void
    {
        $store = new MediaStore();

        foreach ([[200, 200], [480, 360]] as [$width, $height]) {
            $key    = 'challenge.item.zz-4-' . bin2hex(random_bytes(3));
            $result = $store->storeCopy($this->image('png', $width, $height), $key, ['image'], null, true);

            $this->assertNull($result['id']);
            $this->assertNotNull($result['error']);
            $this->assertNull(model(MediaAssetModel::class)->findByKey($key));
            $this->assertFileDoesNotExist(FCPATH . MediaStore::UPLOAD_DIR . $key . '.png');
        }
    }

    public function testUploadShrinksALargerImageBeforeSaving(): void
    {
        $key    = 'bg.zz-' . bin2hex(random_bytes(3));
        $result = (new MediaStore())->store($this->upload($this->image('png', 3840, 2160)), $key, ['image'], null, true);
        $this->assertNull($result['error']);

        $row             = model(MediaAssetModel::class)->findByKey($key);
        $this->cleanup[] = FCPATH . $row['storage_path'];

        $this->assertSame(['1920', '1080'], [(string) $row['width_px'], (string) $row['height_px']]);
        $this->assertSame([1920, 1080], array_slice(getimagesize(FCPATH . $row['storage_path']), 0, 2));
    }

    public function testUploadRefusesASmallerImage(): void
    {
        $key    = 'bg.zz-' . bin2hex(random_bytes(3));
        $result = (new MediaStore())->store($this->upload($this->image('png', 1280, 720)), $key, ['image'], null, true);

        $this->assertNull($result['id']);
        $this->assertStringContainsString('lebih kecil', (string) $result['error']);
        $this->assertNull(model(MediaAssetModel::class)->findByKey($key));
    }

    public function testRegisterFileShrinksInPlace(): void
    {
        $key      = 'challenge.item.zz-3-' . bin2hex(random_bytes(3));
        $relative = MediaStore::UPLOAD_DIR . $key . '.jpg';
        $absolute = FCPATH . $relative;
        $this->cleanup[] = $absolute;

        if (! is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0775, true);
        }

        rename($this->image('jpg', 960, 720), $absolute);

        $result = (new MediaStore())->registerFile($relative, $key, ['image'], null, true);
        $this->assertNull($result['error']);

        $row = model(MediaAssetModel::class)->findByKey($key);
        $this->assertSame($relative, $row['storage_path']);
        $this->assertSame(['480', '360'], [(string) $row['width_px'], (string) $row['height_px']]);
        $this->assertSame([480, 360], array_slice(getimagesize($absolute), 0, 2));
    }

    /** An upload whose temp file is not a real PHP upload (move_uploaded_file() cannot run in CLI). */
    private function upload(string $path): UploadedFile
    {
        return new class ($path, basename($path), null, (int) filesize($path), UPLOAD_ERR_OK) extends UploadedFile {
            public function isValid(): bool
            {
                return true;
            }

            public function move(string $targetPath, ?string $name = null, bool $overwrite = false): bool
            {
                return rename($this->getTempName(), rtrim($targetPath, '/') . '/' . $name);
            }
        };
    }

    /** A temporary test image with a coloured centre and an optional transparent corner. */
    private function image(string $format, int $width, int $height, bool $transparent = false): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 40));

        if ($transparent) {
            imagefilledrectangle($image, 0, 0, intdiv($width, 4), intdiv($height, 4), imagecolorallocatealpha($image, 0, 0, 0, 127));
        }

        $path            = sys_get_temp_dir() . '/fit-' . bin2hex(random_bytes(6)) . '.' . $format;
        $this->cleanup[] = $path;

        match ($format) {
            'png' => imagepng($image, $path),
            'jpg' => imagejpeg($image, $path, 90),
            'gif' => imagegif($image, $path),
        };
        unset($image);

        return $path;
    }
}
