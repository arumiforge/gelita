<?php

use App\Libraries\MediaStore;
use App\Models\MediaAssetModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Gelita;
use Tests\Support\Database\ResetTables;
use Tests\Support\RealUploadsGuard;

/**
 * The test suite stores media in its own folder, so a test run never
 * overwrites or deletes the real uploads of a developer machine (seen
 * 18 October 2026: CatalogImportTest removed installed catalogue images
 * whose rows stayed active). Production keeps public/assets/uploads/.
 *
 * @internal
 */
final class UploadFolderIsolationTest extends CIUnitTestCase
{
    use ResetTables;
    use RealUploadsGuard;

    private const KEY = 'test.upload-isolation';

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardRealUploads([self::KEY . '.png', self::KEY . '.jpg', self::KEY . '.gif']);
        $this->createResetTables();
    }

    protected function tearDown(): void
    {
        foreach (['png', 'jpg'] as $extension) {
            @unlink(FCPATH . MediaStore::uploadDir() . self::KEY . '.' . $extension);
        }

        $this->dropResetTables();
        parent::tearDown();
        $this->assertRealUploadsUntouched();
    }

    public function testTestsUseTheirOwnFolderAndProductionKeepsUploads(): void
    {
        $this->assertSame('assets/uploads-test/', MediaStore::uploadDir());
        $this->assertSame('assets/uploads/', (new ReflectionProperty(Gelita::class, 'uploadDir'))->getDefaultValue());
    }

    public function testStoringAndReplacingLeaveTheRealFolderAlone(): void
    {
        // A row pointing into the real folder, like an old placeholder row:
        // replacing it must not delete the real file of that name
        model(MediaAssetModel::class)->insert([
            'asset_key' => self::KEY, 'asset_type' => 'image', 'storage_path' => 'assets/uploads/' . self::KEY . '.gif',
            'mime_type' => 'image/gif', 'is_active' => 0,
        ]);

        $png = $this->store('png');
        $this->assertSame(MediaStore::uploadDir() . self::KEY . '.png', $png['storage_path']);
        $this->assertFileExists(FCPATH . $png['storage_path']);

        // Another extension removes the previous copy, inside the test folder only
        $jpg = $this->store('jpg');
        $this->assertSame(MediaStore::uploadDir() . self::KEY . '.jpg', $jpg['storage_path']);
        $this->assertFileDoesNotExist(FCPATH . $png['storage_path']);

        $this->assertFileExists(FCPATH . 'assets/uploads/' . self::KEY . '.png', 'the real file of the same name stays');
        $this->assertFileExists(FCPATH . 'assets/uploads/' . self::KEY . '.gif');
    }

    /** @return array<string, mixed> the media row after storing */
    private function store(string $extension): array
    {
        $image = imagecreatetruecolor(16, 16);
        $base  = tempnam(sys_get_temp_dir(), 'isolation');
        $file  = $base . '.' . $extension;
        $extension === 'png' ? imagepng($image, $file) : imagejpeg($image, $file);
        imagedestroy($image);

        $result = (new MediaStore())->storeCopy($file, self::KEY, ['image'], null, true);
        unlink($file);
        unlink($base);
        $this->assertNull($result['error']);

        return model(MediaAssetModel::class)->findByKey(self::KEY);
    }
}
