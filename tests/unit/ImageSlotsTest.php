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

    public function testRegionAndChallengeKeysFillTheirPlaceholders(): void
    {
        $map = ImageSlots::describe('map.region.magelang', ['level_id' => 2]);
        $this->assertSame('region', $map['group']);
        $this->assertSame('Peta wilayah Magelang', $map['label']);
        $this->assertSame('/wilayah/magelang', $map['screens'][0][1]);
        $this->assertSame([1400, 900], $map['recommended']);
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
}
