<?php

use App\Database\Seeds\MediaAssetSeeder;
use App\Libraries\AssetChecklist;
use App\Libraries\ImageSlots;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The image slot catalogue (App\Libraries\ImageSlots) stays in step with
 * the code that registers and reads slots: every slot the seeder registers
 * is described, every key a view reads is a used slot, and slots marked
 * "not used" are really not read anywhere.
 *
 * @internal
 */
final class ImageSlotsTest extends CIUnitTestCase
{
    public function testEverySeededSlotIsDescribed(): void
    {
        $seeder = new MediaAssetSeeder(config('Database'));
        $slots  = (new ReflectionMethod($seeder, 'slots'))->invoke($seeder);

        $this->assertNotEmpty($slots);

        foreach ($slots as [$key]) {
            $slot = ImageSlots::describe($key);

            $this->assertNotNull($slot, $key);
            $this->assertNotSame('', $slot['label'], $key);
            $this->assertNotSame('', $slot['content'], $key);
            $this->assertNotSame('other', $slot['group'], $key);
            $this->assertArrayHasKey($slot['priority'], ImageSlots::PRIORITIES, $key);

            if ($slot['priority'] !== 'unused') {
                $this->assertNotSame([], $slot['screens'], $key . ' names the screens it appears on');
            }

            $this->assertStringNotContainsString('{', $slot['label'] . $slot['content'] . json_encode($slot['screens']), $key . ': placeholders filled');
        }
    }

    public function testEveryKeyReadByAViewIsAUsedSlot(): void
    {
        $read = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'Views', FilesystemIterator::SKIP_DOTS)) as $file) {
            preg_match_all("/media_key_src(?:_locale)?\\('([a-z0-9.\\-]+)'/", (string) file_get_contents($file->getPathname()), $m);
            array_push($read, ...$m[1]);
        }

        $read = array_unique($read);
        $this->assertContains('bg.welcome', $read);

        foreach ($read as $key) {
            $slot = ImageSlots::describe($key);
            $this->assertNotNull($slot, $key);
            $this->assertNotSame('unused', $slot['priority'], $key . ' is read by a view');
        }

        // Slots marked unused are not read by any view
        foreach (ImageSlots::namedKeys() as $key) {
            if (ImageSlots::describe($key)['priority'] === 'unused') {
                $this->assertNotContains($key, $read, $key);
            }
        }

        foreach (AssetChecklist::UI_SLOTS as $key) {
            $this->assertNotNull(ImageSlots::label($key), $key);
            $this->assertNotSame('unused', ImageSlots::describe($key)['priority'], $key);
        }
    }

    /**
     * A size pattern that matches no real key is silently never enforced
     * (`map.region` once missed every `map.region.{wilayah}`). Each pattern
     * must match a key the seeder registers or a slot pattern of the catalogue.
     */
    public function testEverySizePatternMatchesARealSlot(): void
    {
        $seeder     = new MediaAssetSeeder(config('Database'));
        $candidates = array_column((new ReflectionMethod($seeder, 'slots'))->invoke($seeder), 0);
        $candidates = [...$candidates, ...array_keys((new ReflectionClassConstant(ImageSlots::class, 'SLOTS'))->getValue())];

        $gelita = config('Gelita');

        foreach (['assetSizes' => $gelita->assetSizes, 'assetDisplaySizes' => $gelita->assetDisplaySizes] as $list => $sizes) {
            foreach (array_keys($sizes) as $pattern) {
                $hits = array_filter($candidates, static fn (string $key): bool => $pattern === $key || fnmatch($pattern, $key));

                $this->assertNotEmpty($hits, sprintf('%s pattern "%s" matches no slot', $list, $pattern));
            }
        }

        $this->assertSame([1400, 900], (new App\Libraries\MediaStore())->requiredSize('map.region.magelang'));
        $this->assertSame([320, 320], (new App\Libraries\MediaStore())->requiredSize('reward.badge.wonosobo'));
        $this->assertNull((new App\Libraries\MediaStore())->requiredSize('library.tmg-p1-1'), 'library sizes are only recommended');
    }

    public function testEverySlotHasKnownFileTypes(): void
    {
        foreach (ImageSlots::typeRules() as $pattern => $types) {
            $this->assertNotEmpty($types, $pattern);
            $this->assertSame([], array_diff($types, array_keys(ImageSlots::TYPE_MIMES)), $pattern);

            if (ImageSlots::describe(str_replace('*', 'x', $pattern))['transparent'] ?? false) {
                $this->assertNotContains('jpg', $types, $pattern . ' needs transparency');
            }
        }

        $this->assertSame(['png', 'webp'], ImageSlots::types('char.jaka.idle.1'));
        $this->assertSame(['jpg', 'png', 'webp'], ImageSlots::types('bg.welcome'));
        $this->assertNull(ImageSlots::types('zz.bebas'));
        $this->assertSame('PNG atau WebP', ImageSlots::typesText(['png', 'webp']));
        $this->assertSame('JPG, PNG, atau WebP', ImageSlots::typesText(['jpg', 'png', 'webp']));
        $this->assertSame(array_key_first(ImageSlots::typeRules()), 'ui.logo-hero', 'exact keys come first');
    }

    public function testRegionAndChallengeKeysFillTheirPlaceholders(): void
    {
        $map = ImageSlots::describe('map.region.magelang', ['level_id' => 2]);
        $this->assertSame('region', $map['group']);
        $this->assertSame('Peta wilayah Magelang', $map['label']);
        $this->assertSame('/wilayah/magelang', $map['screens'][0][1]);
        $this->assertNull($map['recommended'], 'the size is required ($assetSizes), not only recommended');
        $this->assertSame('admin/konten/level/2', $map['admin'][1]);

        // Without ids there is no admin link (no half-filled URL)
        $this->assertNull(ImageSlots::describe('map.region.magelang')['admin']);

        $hunt = ImageSlots::describe('challenge.item.wnb-4-a03');
        $this->assertSame('challenge', $hunt['group']);
        $this->assertSame('wonosobo', $hunt['region']);
        $this->assertTrue($hunt['transparent']);
        $this->assertSame('/tantangan/wonosobo/4', $hunt['screens'][0][1]);

        $this->assertSame('/tantangan/temanggung/4', ImageSlots::describe('challenge.tmg-4.scene')['screens'][0][1]);
        $this->assertSame('Gambar Susun Gambar Magelang', ImageSlots::describe('challenge.item.mgl-1-02')['label']);

        $frame = ImageSlots::describe('char.kedu.smile.2');
        $this->assertSame('Mbah Kedu · pose tersenyum · gambar 2', $frame['label']);
        $this->assertSame('character', ImageSlots::groupOf('char.jaka.idle.1'));

        $this->assertSame('other', ImageSlots::groupOf('challenge-lama.topi'));
        $this->assertNull(ImageSlots::describe('challenge-lama.topi'));
    }

    /** Owner decisions of 11 October 2026: 3D red-scarf Jaka, catalogue logo and image buttons, WebP. */
    public function testTextsFollowTheCurrentDesign(): void
    {
        $jaka = ImageSlots::describe('char.jaka.idle.1');
        $kedu = ImageSlots::describe('char.kedu.idle.1');

        $this->assertStringContainsString('syal merah', $jaka['content']);
        $this->assertStringContainsString('tanpa syal merah', $kedu['content']);
        $this->assertStringContainsString('tongkat', $kedu['content']);
        $this->assertStringContainsString('24 gambar', $jaka['tips']);

        foreach (['char.jaka.idle.1', 'char.kedu.idle.1', 'ui.btn-start', 'ui.btn-start.en', 'avatar.1', 'map.kedu', 'map.region.temanggung'] as $key) {
            $text = implode(' ', array_filter([ImageSlots::describe($key)['content'], ImageSlots::describe($key)['tips']]));
            $this->assertDoesNotMatchRegularExpression('/\b(lurik|iket|blangkon|surjan|perkamen)\b/i', $text, $key);
        }

        $this->assertStringContainsString('MULAI', ImageSlots::describe('ui.btn-start')['content']);
        $this->assertStringContainsString('START', ImageSlots::describe('ui.btn-start.en')['content']);
        $this->assertStringContainsString('tanpa wajah', ImageSlots::describe('avatar.3')['content']);
        $this->assertStringContainsString('WebP', ImageSlots::describe('bg.welcome')['format']);
        $this->assertSame(['png'], ImageSlots::types('app.icon-512'), 'app icons stay PNG');
    }
}
