<?php

use App\Libraries\AssetChecklist;
use App\Libraries\MediaStore;
use App\Models\MediaAssetModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * The asset checklist (`/admin/media/kelengkapan`) lists the photo of every
 * Cari Objek round-2 post (Periksa Unggahan, 31 §14 no. 14), so a post that
 * would be drawn with the general placeholder is reported as missing.
 *
 * @internal
 */
final class AssetChecklistPostPhotoTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private const GROUP = 'Foto unggahan Cari Objek babak 2';

    private BaseConnection $sqlite;

    /** @var list<string> files written under the test upload folder */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->sqlite = $this->createGradeFiveWorld();
    }

    protected function tearDown(): void
    {
        array_map(static fn (string $file) => is_file($file) && unlink($file), $this->files);

        parent::tearDown();
    }

    public function testEveryPostOfAnActiveChallengeHasARow(): void
    {
        $posts = $this->sqlite->table('post_scenes ps')->join('challenge_nodes cn', 'cn.id = ps.challenge_node_id')
            ->where('cn.is_active', 1)->countAllResults();

        $this->assertGreaterThan(0, $posts, 'the grade 5 bank has round-2 posts');
        $this->assertCount($posts, $this->group()['items']);
    }

    public function testPostWithoutAPhotoIsReportedMissing(): void
    {
        $scene = $this->scene('tmg-4', 'b');
        $this->sqlite->table('post_scenes')->where('id', $scene['id'])->update(['media_asset_id' => null]);

        $item = $this->row('tmg-4', 'b');

        $this->assertSame('missing', $item['status']);
        $this->assertSame('challenge.post.tmg-4-b', $item['key']);
        $this->assertStringContainsString('belum dipasang', $item['note']);
        // The bank import registers the slot, so the link opens its detail page
        $slot = $this->sqlite->table('media_assets')->where('asset_key', 'challenge.post.tmg-4-b')->get()->getRowArray();
        $this->assertNotNull($slot);
        $this->assertStringEndsWith('admin/media/' . $slot['id'], $item['link']);

        // Without a slot row, the upload box with the key filled in
        $this->sqlite->table('media_assets')->where('id', $slot['id'])->delete();
        $this->assertStringContainsString('asset_key=challenge.post.tmg-4-b', $this->row('tmg-4', 'b')['link']);
    }

    public function testPhotoWithoutItsFileIsReportedMissing(): void
    {
        $id = $this->photo('challenge.post.tmg-4-b', 1280, 720, false);
        $this->sqlite->table('post_scenes')->where('id', $this->scene('tmg-4', 'b')['id'])->update(['media_asset_id' => $id]);

        $item = $this->row('tmg-4', 'b');

        $this->assertSame('missing', $item['status']);
        $this->assertStringContainsString('berkasnya belum diunggah', $item['note']);
        $this->assertStringEndsWith('admin/media/' . $id, $item['link']);
    }

    public function testUploadedPhotoIsOkAndAWrongSizeIsFlagged(): void
    {
        $sceneId = $this->scene('tmg-4', 'b')['id'];

        $good = $this->photo('challenge.post.tmg-4-b', 1280, 720, true);
        $this->sqlite->table('post_scenes')->where('id', $sceneId)->update(['media_asset_id' => $good]);
        $item = $this->row('tmg-4', 'b');
        $this->assertSame('ok', $item['status']);
        $this->assertSame('1280 × 720 px', $item['size']);
        $this->assertSame('', $item['note']);

        $this->sqlite->table('media_assets')->where('id', $good)->update(['width_px' => 1024, 'height_px' => 768]);
        $item = $this->row('tmg-4', 'b');
        $this->assertSame('wrong', $item['status']);
        $this->assertStringContainsString('1024×768', $item['note']);
    }

    public function testPostReusingAnotherPictureNamesIt(): void
    {
        $id = $this->photo('challenge.item.tmg-1-04', 1280, 720, true);
        $this->sqlite->table('post_scenes')->where('id', $this->scene('tmg-4', 'a')['id'])->update(['media_asset_id' => $id]);

        $item = $this->row('tmg-4', 'a');

        $this->assertSame('challenge.item.tmg-1-04', $item['key']);
        $this->assertNotSame('missing', $item['status']);
        $this->assertStringContainsString('memakai gambar tantangan lain', $item['note']);
    }

    // ------------------------------------------------------------------ helpers

    /** @return array<string, mixed> */
    private function group(): array
    {
        foreach ((new AssetChecklist($this->sqlite))->groups() as $group) {
            if ($group['title'] === self::GROUP) {
                return $group;
            }
        }

        $this->fail('no post-photo group');
    }

    /** @return array<string, string> */
    private function row(string $ref, string $variant): array
    {
        foreach ($this->group()['items'] as $item) {
            if (str_starts_with($item['label'], 'Cari Objek ' . $ref . ' variasi ' . $variant)) {
                return $item;
            }
        }

        $this->fail('no row for ' . $ref . ' ' . $variant);
    }

    /** @return array<string, mixed> */
    private function scene(string $ref, string $variant): array
    {
        [$prefix, $sequence] = explode('-', $ref);
        $code                = config('Gelita')->levelPrefixes[$prefix];

        $scene = $this->sqlite->table('post_scenes ps')->select('ps.*')
            ->join('challenge_nodes cn', 'cn.id = ps.challenge_node_id')
            ->join('levels l', 'l.id = cn.level_id')
            ->where('l.code', $code)->where('cn.sequence', (int) $sequence)->where('ps.variant_group', $variant)
            ->get()->getRowArray();

        $this->assertNotNull($scene, $ref . ' ' . $variant);

        return $scene;
    }

    /** A media row for $key (replacing any), with a file under the test upload folder when $withFile. */
    private function photo(string $key, int $width, int $height, bool $withFile): int
    {
        $path = MediaStore::uploadDir() . 'cek-' . $key . '.webp';

        if ($withFile) {
            is_dir(FCPATH . MediaStore::uploadDir()) || mkdir(FCPATH . MediaStore::uploadDir(), 0777, true);
            file_put_contents(FCPATH . $path, 'webp');
            $this->files[] = FCPATH . $path;
        }

        $this->sqlite->table('media_assets')->where('asset_key', $key)->delete();

        return (int) model(MediaAssetModel::class)->insert([
            'asset_key' => $key, 'asset_type' => 'image', 'storage_path' => $path, 'mime_type' => 'image/webp',
            'file_size' => 4, 'width_px' => $width, 'height_px' => $height, 'is_active' => 1, 'is_placeholder' => 0,
        ], true);
    }
}
