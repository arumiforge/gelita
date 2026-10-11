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
        // Display plan A5/A7: bg.auth and ui.placeholder are read by the game now (optional slots)
        $this->assertStringContainsString('Latar masuk, daftar, dan ganti kata sandi', $html);
        $this->assertStringContainsString('<code>/masuk</code>', $html);
        $this->assertStringNotContainsString('belum dipakai permainan', $html);

        // Challenge image: description from the question bank, page of the challenge
        $this->assertStringContainsString('lanskap Gunung Sindoro', $html);
        $this->assertStringContainsString('<code>/tantangan/temanggung/1</code>', $html);
        $this->assertMatchesRegularExpression('#href="[^"]*admin/media/\d+"[^>]*>.*Detail#', $html);
    }

    public function testListFiltersByGroupRegionChallengeAndText(): void
    {
        // Only the slot table: the upload box lists every key for its datalist
        $characters = $this->listed($this->page('admin/media?kelompok=character'));
        $this->assertStringContainsString('char.kedu.weak.1', $characters);
        $this->assertStringNotContainsString('char.kedu.weak.2', $characters, 'frames 2–24 get a row only when uploaded');
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

        // Four challenge buttons drawn on the map, in percent and pixels
        $this->assertSame(4, substr_count($html, 'class="media-pin"'));
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
        $this->assertContains('/refleksi', array_column($uses['char.kedu.smile.1'], 'path'));

        $html = $this->page('admin/media/' . $this->mediaId('char.kedu.weak.1'));
        $this->assertStringContainsString('Mbah Kedu · pose lemah · gambar 1', $html);
        $this->assertStringContainsString('baris cerita', $html);
        $this->assertStringContainsString('Sebelumnya', $html);
        $this->assertStringContainsString('Berikutnya', $html);
    }

    /**
     * Display plan Phase A4: the pose's frames side by side, then the
     * animation once 2+ frames exist; up to 24 frames per pose, always
     * looping, and a form that uploads all frames of the pose at once.
     */
    public function testCharacterDetailShowsThePoseFramesAndAnimation(): void
    {
        $page = 'admin/media/' . $this->mediaId('char.kedu.weak.1');

        $html = $this->page($page);
        $this->assertStringContainsString('Semua gambar pose ini (0 dari 24)', $html);
        $this->assertSame(24, substr_count($html, '(belum ada)'), 'twenty-four empty frames');
        $this->assertStringContainsString('paling sedikit dua gambar', $html);
        $this->assertStringNotContainsString('data-frames=', $html);
        $this->assertStringContainsString('action="' . site_url('admin/media/' . $this->mediaId('char.kedu.weak.1') . '/unggah-gerak') . '"', $html);
        $this->assertStringContainsString('name="frames[]" multiple', $html);

        $this->sqlite->table('media_assets')->where('asset_key', 'char.kedu.weak.1')
            ->update(['is_active' => 1, 'storage_path' => 'assets/uploads/char.kedu.weak.1.png', 'sha256' => str_repeat('1', 64)]);
        $this->sqlite->table('media_assets')->insert([
            'asset_key' => 'char.kedu.weak.2', 'asset_type' => 'sprite_frame', 'storage_path' => 'assets/uploads/char.kedu.weak.2.png',
            'mime_type' => 'image/png', 'sha256' => str_repeat('2', 64), 'is_active' => 1,
        ]);
        service('contentRepository')->flush();
        service('cache')->clean();

        $html = $this->page($page);
        $this->assertSame(22, substr_count($html, '(belum ada)'));
        $this->assertMatchesRegularExpression('/class="character" data-frames=.\["[^\]]*char\.kedu\.weak\.1\.png[^\]]*char\.kedu\.weak\.2\.png[^\]]*\]/', html_entity_decode($html));
        $this->assertStringContainsString('data-fps="0.57" data-loop="1"', $html, '2 frames in the 3.5 s loop of kedu.weak, repeating');
        $this->assertStringContainsString('berulang terus', $html);
        $this->assertStringContainsString('href="' . site_url('admin/media/' . $this->mediaId('char.kedu.weak.2')) . '">Gambar 2</a>', $html, 'other frames open their own page');
        $this->assertStringContainsString('data-sprite-replay', $html);
        $this->assertStringContainsString('data-zoom="', $html, 'frames open in the lightbox');
    }

    public function testListThumbnailsOpenTheLightbox(): void
    {
        $this->sqlite->table('media_assets')->where('asset_key', 'char.jaka.idle.1')
            ->update(['is_active' => 1, 'storage_path' => 'assets/uploads/char.jaka.idle.1.png', 'sha256' => str_repeat('a', 64)]);

        $html = $this->page('admin/media?kelompok=character');

        $this->assertMatchesRegularExpression('/<a class="media-thumb" href="[^"]*char\.jaka\.idle\.1\.png\?v=aaaa[^"]*" target="_blank" rel="noopener" data-zoom=/', $html, 'sprite frames get a thumbnail too');
    }

    /** Display plan A6: intro and ending slides get a background field; region dialogues do not. */
    public function testStorySlidesTakeABackgroundFromTheDialogueEditor(): void
    {
        $html = $this->page('admin/konten/dialog/0?konteks=intro');
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('Latar slide (tidak wajib)', $html);
        $this->assertStringContainsString('<code>bg.story.intro.1</code>', $html, 'default key per slide');

        $regional = $this->page('admin/konten/dialog/1?konteks=level_open');
        $this->assertStringNotContainsString('Latar slide', $regional);

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
