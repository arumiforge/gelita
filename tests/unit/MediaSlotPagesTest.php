<?php

use App\Database\Seeds\ContentSeeder;
use App\Database\Seeds\MediaAssetSeeder;
use App\Libraries\ImageSlots;
use App\Libraries\MediaStore;
use App\Libraries\MediaUsage;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Gambar & mockup for the admin who installs images: every slot says what
 * the picture should show and which game page shows it, the list filters by
 * group and region, and the slot detail page (`/admin/media/{id}`) gives
 * sizes, screens, map pins, and an upload form that comes back to it.
 *
 * @internal
 */
final class MediaSlotPagesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';
    private const CSRF      = ['gelita_csrf' => self::CSRF_HASH . '00000000000000000000000000000000'];

    private BaseConnection $sqlite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite = $this->createGradeFiveWorld();

        // Official slots (logo, backgrounds, maps, characters) as on a fresh install
        if ($this->sqlite->table('media_assets')->where('asset_key', 'bg.welcome')->countAllResults() === 0) {
            (new MediaAssetSeeder(config('Database'), $this->sqlite))->run();
        }

        if ($this->sqlite->table('dialogues')->countAllResults() === 0) {
            (new ContentSeeder(config('Database'), $this->sqlite))->seedDialogues();
        }

        $this->injectStaff('admin');
        Config\Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        $this->dropResetTables();

        parent::tearDown();
    }

    public function testListDescribesEverySlotAndTheScreensItAppearsOn(): void
    {
        $html = $this->page('admin/media');

        // Official slot without a label in the database: text from ImageSlots
        $this->assertStringContainsString('Latar halaman awal & akun', $html);
        $this->assertStringContainsString('<code>/mulai</code>', $html);
        // Unused: the city lanterns (Peta Kedu redesign: Kenali uses a book icon) and bg.auth
        // (account screens use the owner's shipped pictures; both 18 October 2026)
        $this->assertStringContainsString('Latar masuk, daftar, dan ganti kata sandi', $html);
        $this->assertStringNotContainsString('<code>/masuk</code>', $html);
        $this->assertSame(4, substr_count($html, 'belum dipakai permainan'));
        $this->assertSame(3, substr_count($html, '>Lentera Kenali '));

        // Challenge image: description from the question bank, page of the challenge
        $this->assertStringContainsString('Candi Pringapus tampak depan', $html);
        $this->assertStringContainsString('<code>/tantangan/temanggung/1</code>', $html);
        $this->assertMatchesRegularExpression('#href="[^"]*admin/media/\d+"[^>]*>.*Detail#', $html);
    }

    public function testListFiltersByGroupRegionChallengeAndText(): void
    {
        // Only the slot table: the upload box lists every key for its datalist
        $characters = $this->listed($this->page('admin/media?kelompok=character'));
        $this->assertStringContainsString('char.kedu.weak.1', $characters);
        $this->assertStringNotContainsString('char.kedu.weak.2', $characters, 'one still picture per pose, no frames 2–24');
        $this->assertStringNotContainsString('bg.welcome', $characters);
        $this->assertStringContainsString('Kelompok: <b>Tokoh</b>', $characters);

        $node = $this->listed($this->page('admin/media?kelompok=challenge&wilayah=wonosobo&tantangan=wnb-4'));
        $this->assertStringContainsString('challenge.wnb-4.scene', $node);
        $this->assertStringContainsString('challenge.item.wnb-4-a12', $node);
        $this->assertStringNotContainsString('challenge.item.wnb-1-05', $node);
        $this->assertStringNotContainsString('challenge.item.tmg-4-02', $node);

        $search = $this->listed($this->page('admin/media?cari=telaga'));
        $this->assertStringContainsString('challenge.item.wnb-1-05', $search);
        $this->assertStringNotContainsString('challenge.item.mgl-1-01', $search);

        // Unknown filter values are ignored
        $this->assertStringContainsString('bg.welcome', $this->listed($this->page('admin/media?kelompok=apa&wilayah=bali&tantangan=x')));
    }

    public function testRegionMapDetailShowsPinsSizesAndEditor(): void
    {
        $id   = $this->mediaId('map.region.temanggung');
        $html = $this->page('admin/media/' . $id);

        $this->assertStringContainsString('Peta wilayah Temanggung', $html);
        $this->assertStringContainsString('1400 × 900 px (gambar lebih besar dengan bentuk sama diperkecil otomatis)', $html);
        $this->assertStringContainsString('<code>/wilayah/temanggung</code>', $html);
        $this->assertStringContainsString('admin/konten/level/1', $html);

        // Five challenge buttons drawn on the map (Skenario Digital since part 6), in percent and pixels
        $this->assertSame(5, substr_count($html, 'class="media-pin"'));
        $this->assertStringContainsString('1. Susun Gambar', $html);
        $this->assertStringContainsString('252, 630', $html, '18 %, 70 % of 1400 × 900');

        // Upload form returns here
        $this->assertStringContainsString('name="back" value="admin/media/' . $id . '"', $html);
        $this->assertStringContainsString('<input type="hidden" name="asset_key" value="map.region.temanggung">', $html);
    }

    public function testBadgeDetailShowsFileAndDisplaySize(): void
    {
        $html = $this->page('admin/media/' . $this->mediaId('reward.badge.temanggung'));

        $this->assertStringContainsString('320 × 320 px (gambar lebih besar dengan bentuk sama diperkecil otomatis)', $html);
        $this->assertStringContainsString('72 × 72 dp (layar 1280 × 720)', $html, 'shown at 4.5rem on the profile badge shelf');

        $method = new ReflectionMethod(App\Controllers\Admin\MediaController::class, 'displaySize');
        $this->assertSame([72, 72], $method->invoke(new App\Controllers\Admin\MediaController(), 'reward.badge.temanggung'));
        $this->assertSame([880, 566], $method->invoke(new App\Controllers\Admin\MediaController(), 'map.region.temanggung'));
    }

    public function testChallengeImageDetailNamesTheChallengeAndTheObjectRole(): void
    {
        $html = $this->page('admin/media/' . $this->mediaId('challenge.item.tmg-4-09'));

        $this->assertStringContainsString('240 × 240 px (gambar lebih besar dengan bentuk sama diperkecil otomatis)', $html);
        $this->assertStringContainsString('Ya, wajib transparan', $html);
        $this->assertStringContainsString('<code>/tantangan/temanggung/4</code>', $html);
        $this->assertStringContainsString('benda pengecoh (bukan jawaban)', $html);
        $this->assertStringContainsString('angklung', mb_strtolower($html));

        $target = $this->page('admin/media/' . $this->mediaId('challenge.item.tmg-4-02'));
        $this->assertStringContainsString('benda yang dicari, petunjuk:', $target);
    }

    public function testCharacterDetailListsTheStoryLinesAndScreens(): void
    {
        $uses = (new MediaUsage())->characterUses();
        $this->assertContains('/daftar/3', array_column($uses['char.jaka.bow.1'], 'path'));
        // Balai Refleksi shows Mbah Kedu's avatar since 21 October 2026, not his pose picture
        $this->assertNotContains('/refleksi', array_column($uses['char.kedu.smile.1'] ?? [], 'path'));
        $this->assertContains('/selesai/1', array_column($uses['char.kedu.smile.1'], 'path'));

        $html = $this->page('admin/media/' . $this->mediaId('char.kedu.weak.1'));
        $this->assertStringContainsString('Mbah Kedu · pose lemah', $html);
        $this->assertStringNotContainsString('gambar 1', $html);
        $this->assertStringContainsString('baris cerita', $html);
        $this->assertStringContainsString('Sebelumnya', $html);
        $this->assertStringContainsString('Berikutnya', $html);
    }

    /**
     * Owner: a character pose is one still picture. Its detail page has the
     * single upload form of every slot: no frame strip, no animation, no
     * upload of all frames at once (the route `unggah-gerak` is gone).
     */
    public function testCharacterDetailIsOneStillPicture(): void
    {
        $html = $this->page('admin/media/' . $this->mediaId('char.kedu.weak.1'));

        $this->assertStringContainsString('action="' . site_url('admin/media/unggah') . '"', $html);
        $this->assertStringContainsString('Satu gambar diam per pose', $html);
        $this->assertStringNotContainsString('unggah-gerak', $html);
        $this->assertStringNotContainsString('name="frames[]"', $html);
        $this->assertStringNotContainsString('data-frames=', $html);
        $this->assertStringNotContainsString('Semua gambar pose ini', $html);
        $this->assertStringNotContainsString('gambar gerak', mb_strtolower($html));
    }

    public function testListThumbnailsOpenTheLightbox(): void
    {
        $this->sqlite->table('media_assets')->where('asset_key', 'char.jaka.idle.1')
            ->update(['is_active' => 1, 'storage_path' => 'assets/uploads/char.jaka.idle.1.png', 'sha256' => str_repeat('a', 64)]);

        $html = $this->page('admin/media?kelompok=character');

        $this->assertMatchesRegularExpression('/<a class="media-thumb" href="[^"]*char\.jaka\.idle\.1\.png\?v=aaaa[^"]*" target="_blank" rel="noopener" data-zoom=/', $html, 'pose pictures (stored as sprite_frame) get a thumbnail too');
    }

    /** Display plan A6, widened by the owner on 19 October 2026: story slides and the three region contexts get a background field. */
    public function testStorySlidesTakeABackgroundFromTheDialogueEditor(): void
    {
        $html = $this->page('admin/konten/dialog/0?konteks=intro');
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('Latar slide (tidak wajib)', $html);
        $this->assertStringContainsString('<code>bg.story.intro.1</code>', $html, 'default key per slide');
        $this->assertStringContainsString('<code>bg.story.intro.{urutan}</code>', $html, 'new slide');

        // Region slides name their slot after the region and fall back to its background
        foreach (['region_intro', 'level_open', 'level_done'] as $context) {
            $regional = $this->page('admin/konten/dialog/1?konteks=' . $context);
            $this->assertStringContainsString('enctype="multipart/form-data"', $regional, $context);
            $this->assertStringContainsString('<code>bg.story.' . $context . '.temanggung.1</code>', $regional, $context);
            $this->assertStringContainsString('<code>bg.story.' . $context . '.temanggung.{urutan}</code>', $regional, $context);
            $this->assertStringContainsString('slide memakai latar wilayah Temanggung', $regional, $context);
        }

        // The Peta Kedu story bar has no backdrop of its own
        $this->assertStringNotContainsString('Latar slide', $this->page('admin/konten/dialog/0?konteks=map_intro'));

        $line = $this->sqlite->table('dialogues')->where(['level_id' => 1, 'context_code' => 'level_done', 'sequence' => 2])->get()->getRowArray();
        $this->asAdmin()->call(Method::POST, 'admin/konten/dialog/1', ['context' => 'level_done', 'dialogues' => [[
            'id' => $line['id'], 'sequence' => 2, 'character_code' => $line['character_code'],
            'text_id' => $line['text_id'], 'text_en' => $line['text_en'], 'is_active' => '1', 'background_key' => 'bg.welcome',
        ]]] + self::CSRF)->assertRedirectTo(site_url('admin/konten/dialog/1?konteks=level_done'));
        $this->assertSame($this->mediaId('bg.welcome'), (int) $this->sqlite->table('dialogues')->where('id', $line['id'])->get()->getRow('background_media_id'));

        $slide = $this->sqlite->table('dialogues')->where(['context_code' => 'intro', 'sequence' => 1])->get()->getRowArray();
        $post  = ['context' => 'intro', 'dialogues' => [[
            'id' => $slide['id'], 'sequence' => 1, 'character_code' => $slide['character_code'],
            'text_id' => $slide['text_id'], 'text_en' => $slide['text_en'], 'is_active' => '1', 'background_key' => 'bg.welcome',
        ]]] + self::CSRF;

        $this->asAdmin()->call(Method::POST, 'admin/konten/dialog/0', $post)
            ->assertRedirectTo(site_url('admin/konten/dialog/0?konteks=intro'));
        $this->assertSame($this->mediaId('bg.welcome'), (int) $this->sqlite->table('dialogues')->where('id', $slide['id'])->get()->getRow('background_media_id'));

        // Emptying the code box removes the background again
        $post['dialogues'][0]['background_key'] = '';
        $this->asAdmin()->call(Method::POST, 'admin/konten/dialog/0', $post);
        $this->assertNull($this->sqlite->table('dialogues')->where('id', $slide['id'])->get()->getRow('background_media_id'));

        $this->assertSame('background', ImageSlots::groupOf('bg.story.ending.4'));
        $this->assertSame([1920, 1080], (new MediaStore())->requiredSize('bg.story.ending.4'));
    }

    /** Display plan Phase C: the level editor uploads the city pin, lantern, and curtain picture by fixed key. */
    public function testLevelEditorOffersTheCityArtSlots(): void
    {
        $html = $this->page('admin/konten/level/1');

        foreach (['map.pin.temanggung', 'map.lantern.temanggung', 'bg.loading.temanggung'] as $key) {
            $this->assertStringContainsString('<code>' . $key . '</code>', $html);
        }
        $this->assertStringContainsString('name="slot_pin_file"', $html);
        $this->assertStringContainsString('accept="image/png,image/webp"', $html, 'transparent pin: PNG or WebP');
        $this->assertStringContainsString('Latar tirai "Menuju Temanggung…"', $html);
    }

    public function testUploadFromTheDetailPageComesBack(): void
    {
        $id = $this->mediaId('bg.welcome');

        $this->asAdmin()->call(Method::POST, 'admin/media/unggah', ['asset_key' => 'bg.welcome', 'back' => 'admin/media/' . $id] + self::CSRF)
            ->assertRedirectTo(site_url('admin/media/' . $id));

        // Anything else goes back to the list
        $this->asAdmin()->call(Method::POST, 'admin/media/unggah', ['asset_key' => 'bg.welcome', 'back' => 'https://contoh.test/'] + self::CSRF)
            ->assertRedirectTo(site_url('admin/media'));
    }

    public function testUnknownOrAudioIdGoesBackToTheList(): void
    {
        $this->asAdmin()->call(Method::GET, 'admin/media/99999')->assertRedirectTo(site_url('admin/media'));
    }

    public function testChecklistListsChallengeImagesPerChallenge(): void
    {
        $html = $this->page('admin/media/kelengkapan');

        $this->assertStringContainsString('Gambar tantangan', $html);
        $this->assertStringContainsString('Tantangan wnb-4', $html);
        $this->assertStringContainsString('tantangan=wnb-4', $html);
        $this->assertStringNotContainsString('<code>bg.auth</code>', $html);
        $this->assertMatchesRegularExpression('#admin/media/\d+"[^>]*>.*Lihat & unggah#', $html);
    }

    private function page(string $path): string
    {
        $result = $this->asAdmin()->call(Method::GET, $path);
        $result->assertOK();

        return html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5);
    }

    private function listed(string $html): string
    {
        return strstr($html, 'id="unggah"', true) ?: $html;
    }

    private function mediaId(string $key): int
    {
        $id = $this->sqlite->table('media_assets')->select('id')->where('asset_key', $key)->get()->getRow('id');
        $this->assertNotNull($id, $key);

        return (int) $id;
    }

    private function asAdmin(): self
    {
        return $this->withSession(['staff_id' => 1, 'staff_role' => 'admin', 'gelita_csrf' => self::CSRF_HASH]);
    }

    private function injectStaff(?string $role): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($role === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 1, 'username' => 'admin_uji', 'email' => null, 'role' => $role, 'display_name' => 'Admin Uji',
                'school_id' => null, 'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
