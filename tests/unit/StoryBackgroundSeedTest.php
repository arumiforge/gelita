<?php

use App\Database\Seeds\ContentSeeder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Tests\Support\Database\NarrationTables;

/**
 * ContentSeeder::seedDialogues() links a slide's own background when its slot
 * (DialogueModel::backgroundKey(), or the line's `bg` in data/story.php)
 * already exists; every other slide keeps the default background (NULL).
 *
 * @internal
 */
final class StoryBackgroundSeedTest extends CIUnitTestCase
{
    use NarrationTables;

    private BaseConnection $sqlite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite = Database::connect('tests');
        $this->createNarrationTables($this->sqlite);
    }

    protected function tearDown(): void
    {
        $this->dropNarrationTables();
        parent::tearDown();
    }

    public function testSeederLinksBackgroundsWhoseSlotExists(): void
    {
        foreach (['bg.story.level_done.temanggung.2', 'bg.story.intro.1'] as $key) {
            $this->sqlite->table('media_assets')->insert([
                'asset_key' => $key, 'asset_type' => 'background', 'storage_path' => 'assets/uploads/' . $key . '.webp', 'mime_type' => 'image/webp',
            ]);
        }

        $media = array_column($this->sqlite->table('media_assets')->get()->getResultArray(), 'id', 'asset_key');

        (new ContentSeeder(config('Database'), $this->sqlite))->seedDialogues();

        $linked = array_filter(array_column(
            $this->sqlite->table('dialogues d')->select('d.id, d.context_code, d.sequence, l.code, d.background_media_id')
                ->join('levels l', 'l.id = d.level_id', 'left')->get()->getResultArray(),
            'background_media_id',
            'id',
        ));

        $this->assertCount(2, $linked, 'only the two slides with an existing slot');

        $slide = fn (?string $code, string $context, int $sequence): ?string => $this->sqlite->table('dialogues d')
            ->select('d.background_media_id')->join('levels l', 'l.id = d.level_id', 'left')
            ->where(['l.code' => $code, 'd.context_code' => $context, 'd.sequence' => $sequence])->get()->getRow('background_media_id');

        $this->assertSame((int) $media['bg.story.level_done.temanggung.2'], (int) $slide('temanggung', 'level_done', 2));
        $this->assertNull($slide('magelang', 'level_done', 2), 'same slide in another region');
        $this->assertSame((int) $media['bg.story.intro.1'], (int) $this->sqlite->table('dialogues')
            ->where(['level_id' => null, 'context_code' => 'intro', 'sequence' => 1])->get()->getRow('background_media_id'));
    }
}
