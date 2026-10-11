<?php

use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Character frame animation: character_frames() collects the uploaded frames
 * of a pose with fps/loop from Config\Gelita::$characterAnimations, the
 * character component and dialogue lines carry them as data attributes, and
 * core/sprite.js plays them. Frame 1 stays the server-rendered image.
 *
 * @internal
 */
final class CharacterFramesTest extends CIUnitTestCase
{
    /** asset_key → active storage path */
    private array $media = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private CharacterFramesTest $test)
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

    public function testFramesOfAPoseComeInOrderWithFpsAndLoop(): void
    {
        $this->media = $this->frames('jaka', 'happy', [1, 2, 3]) + $this->frames('jaka', 'idle', [1, 2, 3]);

        $this->assertSame([
            'frames' => [base_url('assets/char/jaka-happy-1.png'), base_url('assets/char/jaka-happy-2.png'), base_url('assets/char/jaka-happy-3.png')],
            'fps'    => 1.5,   // 3 frames in one 2-second loop
            'loop'   => true,
        ], character_frames('jaka', 'happy'));

        $idle = character_frames('jaka');
        $this->assertSame(1.0, $idle['fps']);
        $this->assertTrue($idle['loop']);
        $this->assertSame(character_frame_src('jaka'), $idle['frames'][0]);
    }

    public function testMissingFrameIsSkippedAndMissingFrameOneFallsBackToIdle(): void
    {
        $this->media = $this->frames('kedu', 'smile', [1, 3]) + $this->frames('kedu', 'idle', [1, 2, 3]);

        $smile = character_frames('mbah_kedu', 'smile');
        $this->assertSame([base_url('assets/char/kedu-smile-1.png'), base_url('assets/char/kedu-smile-3.png')], $smile['frames']);

        // Frame 1 of `worried` is missing: the whole idle animation plays instead
        $this->media = $this->frames('kedu', 'worried', [2, 3]) + $this->frames('kedu', 'idle', [1, 2, 3]);
        $worried     = character_frames('mbah_kedu', 'worried');
        $this->assertSame(base_url('assets/char/kedu-idle-1.png'), $worried['frames'][0]);
        $this->assertCount(3, $worried['frames']);
        $this->assertSame(0.86, $worried['fps'], 'idle speed: 3 frames in 3.5 s');
        $this->assertSame(character_frame_src('mbah_kedu', 'worried'), $worried['frames'][0]);

        // A pose another character does not have also falls back to idle
        $this->assertSame(base_url('assets/char/kedu-idle-1.png'), character_frames('mbah_kedu', 'happy')['frames'][0]);

        $this->media = [];
        $this->assertNull(character_frames('jaka', 'happy'));
        $this->assertNull(character_frames('narator'));
    }

    public function testEveryPoseLoopsUpTo24FramesAndSpeedFollowsTheFrameCount(): void
    {
        foreach (config('Gelita')->characterAnimations as $name => $animation) {
            $this->assertSame(24, $animation['frames'], $name);
            $this->assertTrue($animation['loop'], "{$name} repeats (owner decision 11 October 2026)");
        }

        $twoSeconds = ['seconds' => 2.0];
        $this->assertSame(12.0, character_fps(24, $twoSeconds), '24 frames in 2 s');
        $this->assertSame(1.5, character_fps(3, $twoSeconds), 'interim 3 frames play slowly, no flicker');
        $this->assertSame(0.5, character_fps(1, ['seconds' => 3.5]), 'never slower than one frame per 2 s');
        $this->assertSame(24.0, character_fps(24, ['seconds' => 0.1]), 'never faster than 24 per second');

        // All 24 uploaded frames play in order
        $this->media = $this->frames('jaka', 'happy', range(1, 24));
        $happy       = character_frames('jaka', 'happy');
        $this->assertCount(24, $happy['frames']);
        $this->assertSame(base_url('assets/char/jaka-happy-24.png'), $happy['frames'][23]);
        $this->assertSame(12.0, $happy['fps']);
    }

    public function testAttributesOnlyForTwoOrMoreFrames(): void
    {
        $this->assertSame('', character_frames_attr(null));
        $this->assertSame('', character_frames_attr(['frames' => ['/a.png'], 'fps' => 4, 'loop' => true]));

        $attr = character_frames_attr(['frames' => ['/a.png', '/b.png'], 'fps' => 4, 'loop' => true], 'data-pose-');
        $this->assertSame(' data-pose-frames="' . esc('["/a.png","/b.png"]', 'attr') . '" data-pose-fps="4" data-pose-loop="1"', $attr);
    }

    public function testComponentRendersFrameOneAndCarriesTheFrames(): void
    {
        $this->media = $this->frames('jaka', 'bow', [1, 2, 3]);

        $html = component('character', ['character' => 'jaka', 'pose' => 'bow']);

        $this->assertMatchesRegularExpression('/<figure class="character character-jaka "\s+data-character="jaka" data-pose="bow" data-frames="[^"]+" data-fps="1.2" data-loop="1">/', $html);
        $this->assertStringContainsString('src="' . esc(base_url('assets/char/jaka-bow-1.png'), 'attr') . '"', $html);
        $this->assertStringContainsString(esc(json_encode([
            base_url('assets/char/jaka-bow-1.png'), base_url('assets/char/jaka-bow-2.png'), base_url('assets/char/jaka-bow-3.png'),
        ], JSON_UNESCAPED_SLASHES), 'attr'), $html);

        // One frame: a still image without player attributes; none: the monogram
        $this->media = $this->frames('jaka', 'bow', [1]);
        $this->assertStringNotContainsString('data-frames', component('character', ['character' => 'jaka', 'pose' => 'bow']));
        $this->media = [];
        $this->assertStringContainsString('class="character-fallback"', component('character', ['character' => 'jaka', 'pose' => 'bow']));
    }

    public function testSpritePlayerIsLoadedAndUsedByTheDialogue(): void
    {
        $sprite   = (string) file_get_contents(FCPATH . 'assets/js/core/sprite.js');
        $game     = (string) file_get_contents(FCPATH . 'assets/js/game.js');
        $dialogue = (string) file_get_contents(FCPATH . 'assets/js/game/dialogue.js');

        $this->assertStringContainsString("document.querySelector('.character[data-frames]')", $game);
        $this->assertStringContainsString("import('./core/sprite.js')", $game);
        $this->assertStringContainsString("from '../core/sprite.js'", $dialogue);
        $this->assertStringContainsString('line.dataset.poseFrames', $dialogue);
        // Reduced motion keeps frame 1; hidden tabs and off-screen figures pause
        $this->assertStringContainsString('reducedMotion()', $sprite);
        $this->assertStringContainsString('IntersectionObserver', $sprite);
        $this->assertStringContainsString('visibilitychange', $sprite);
    }

    /**
     * @param list<int> $numbers
     *
     * @return array<string, string>
     */
    private function frames(string $slug, string $pose, array $numbers): array
    {
        $out = [];

        foreach ($numbers as $n) {
            $out["char.{$slug}.{$pose}.{$n}"] = "assets/char/{$slug}-{$pose}-{$n}.png";
        }

        return $out;
    }
}
