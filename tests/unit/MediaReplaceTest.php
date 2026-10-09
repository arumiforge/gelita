<?php

use App\Libraries\MediaStore;
use App\Models\MediaAssetModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Replacing a real image with another file of the same extension keeps
 * storage_path (`assets/uploads/{asset_key}.png`), so the media URL must
 * carry a content version or browsers keep showing the old cached image.
 *
 * @internal
 */
final class MediaReplaceTest extends CIUnitTestCase
{
    use ResetTables;

    private string $key;

    protected function setUp(): void
    {
        parent::setUp();

        helper(['content', 'gelita']);
        $this->createResetTables();
        $this->key = 'test.replace-' . bin2hex(random_bytes(4));
        Services::resetSingle('contentRepository');
    }

    protected function tearDown(): void
    {
        $row = model(MediaAssetModel::class)->findByKey($this->key);

        if ($row !== null && is_file(FCPATH . $row['storage_path'])) {
            unlink(FCPATH . $row['storage_path']);
        }

        $this->dropResetTables();
        parent::tearDown();
    }

    public function testReplacingWithSameExtensionChangesTheMediaUrl(): void
    {
        $first = $this->replace([200, 120, 40]);
        $path  = model(MediaAssetModel::class)->findByKey($this->key)['storage_path'];

        $second = $this->replace([20, 90, 200]);
        $row    = model(MediaAssetModel::class)->findByKey($this->key);

        $this->assertSame($path, $row['storage_path'], 'same extension keeps the official file name');
        $this->assertSame(hash_file('sha256', FCPATH . $row['storage_path']), $row['sha256'], 'the new file is on disk');
        $this->assertNotSame($first['key_src'], $second['key_src']);
        $this->assertNotSame($first['src'], $second['src']);
        $this->assertSame(base_url($row['storage_path']) . '?v=' . substr($row['sha256'], 0, 12), $second['src']);
        $this->assertSame($second['src'], $second['key_src']);
    }

    public function testFileWithoutHashKeepsThePlainUrl(): void
    {
        $this->assertSame('assets/uploads/x.png', MediaStore::versionedPath('assets/uploads/x.png', null));
        $this->assertSame('assets/uploads/x.png', MediaStore::versionedPath('assets/uploads/x.png', ''));
        $this->assertSame('assets/uploads/x.png?v=0123456789ab', MediaStore::versionedPath('assets/uploads/x.png', '0123456789abcdef'));
    }

    /**
     * Store a 16 × 16 PNG of one colour under the test key; return the URLs
     * the game would render right after.
     *
     * @param array{0: int, 1: int, 2: int} $rgb
     *
     * @return array{src: string, key_src: string|null}
     */
    private function replace(array $rgb): array
    {
        $image = imagecreatetruecolor(16, 16);
        imagefill($image, 0, 0, imagecolorallocate($image, ...$rgb));
        $file = tempnam(sys_get_temp_dir(), 'ganti') . '.png';
        imagepng($image, $file);
        imagedestroy($image);

        $result = (new MediaStore())->storeCopy($file, $this->key, ['image']);
        unlink($file);
        $this->assertNull($result['error']);

        service('cache')->clean();

        return ['src' => media_src($result['id']), 'key_src' => media_key_src($this->key)];
    }
}
