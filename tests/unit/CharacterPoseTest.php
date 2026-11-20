<?php

use App\Libraries\ImageSlots;
use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * A character pose is one still picture, `char.{jaka|kedu}.{pose}.1`
 * (owner: still pictures only; the frame loop of up to 24 pictures was
 * removed). character_frame_src() picks it, falling back to `idle`; the
 * character component and the dialogue lines carry no frame list, and the
 * frame player core/sprite.js is gone.
 *
 * @internal
 */
final class CharacterPoseTest extends CIUnitTestCase
{
    /** asset_key → active storage path */
    private array $media = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private CharacterPoseTest $test)
            {
            }

            public function mediaKeyMap(): array
            {
                return $this->test->media();
            }

            public function mediaMap(): array
            {
                return [];
            }
        });
        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        parent::tearDown();
    }

    /** @return array<string, string> */
    public function media(): array
    {
        return $this->media;
    }

    public function testPosePictureFallsBackToIdle(): void
    {
        $this->media = [
            'char.kedu.smile.1' => 'assets/char/kedu-smile-1.png',
            'char.kedu.idle.1'  => 'assets/char/kedu-idle-1.png',
            // An old frame of the removed loop is never read
            'char.kedu.worried.2' => 'assets/char/kedu-worried-2.png',
        ];

        $this->assertSame(base_url('assets/char/kedu-smile-1.png'), character_frame_src('mbah_kedu', 'smile'));
        $this->assertSame(base_url('assets/char/kedu-idle-1.png'), character_frame_src('mbah_kedu', 'worried'));
        $this->assertSame(base_url('assets/char/kedu-idle-1.png'), character_frame_src('mbah_kedu', 'happy'), 'a pose the character does not have');
        $this->assertSame(base_url('assets/char/kedu-idle-1.png'), character_frame_src('mbah_kedu'));

        $this->media = [];
        $this->assertNull(character_frame_src('jaka', 'happy'));
        $this->assertNull(character_frame_src('narator'));
    }

    public function testComponentRendersOneStillPicture(): void
    {
        $this->media = [
            'char.jaka.bow.1' => 'assets/char/jaka-bow-1.png',
            'char.jaka.bow.2' => 'assets/char/jaka-bow-2.png',
        ];

        $html = component('character', ['character' => 'jaka', 'pose' => 'bow']);

        $this->assertMatchesRegularExpression('/<figure class="character character-jaka "\s+data-character="jaka" data-pose="bow">/', $html);
        $this->assertStringContainsString('src="' . esc(base_url('assets/char/jaka-bow-1.png'), 'attr') . '"', $html);
        $this->assertStringNotContainsString('jaka-bow-2', $html);
        $this->assertStringNotContainsString('data-frames', $html);

        $this->media = [];
        $this->assertStringContainsString('class="character-fallback"', component('character', ['character' => 'jaka', 'pose' => 'bow']));
    }

    public function testNoFrameLoopIsLeft(): void
    {
        $this->assertFileDoesNotExist(FCPATH . 'assets/js/core/sprite.js');
        $this->assertFalse(property_exists(config('Gelita'), 'characterAnimations'));
        $this->assertFalse(function_exists('character_frames'));
        $this->assertFalse(function_exists('character_frames_attr'));
        $this->assertFalse(function_exists('character_fps'));

        foreach (['js/game.js', 'js/admin.js', 'js/game/dialogue.js'] as $file) {
            $js = (string) file_get_contents(FCPATH . 'assets/' . $file);
            $this->assertStringNotContainsString('sprite.js', $js, $file);
            $this->assertStringNotContainsString('data-frames', $js, $file);
            $this->assertStringNotContainsString('poseFrames', $js, $file);
        }

        $views = (string) file_get_contents(APPPATH . 'Views/partials/dialogue-scene.php')
            . file_get_contents(APPPATH . 'Views/admin/media/show.php');
        $this->assertStringNotContainsString('data-pose-frames', $views);
        $this->assertStringNotContainsString('unggah-gerak', $views);
    }

    public function testOneSlotPerPose(): void
    {
        $slots = ImageSlots::poseSlots();

        $this->assertCount(10, $slots);
        $this->assertSame('char.jaka.idle.1', $slots['jaka.idle']);
        $this->assertSame('char.kedu.weak.1', $slots['kedu.weak']);

        foreach ($slots as $key) {
            $this->assertNotNull(ImageSlots::patternFor($key), $key);
        }

        // Frames 2+ are no longer image slots
        $this->assertNull(ImageSlots::patternFor('char.jaka.idle.2'));
        $this->assertNull(ImageSlots::patternFor('char.kedu.weak.24'));
    }
}
