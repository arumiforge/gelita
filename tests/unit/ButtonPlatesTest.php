<?php

use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * The owner's button pictures of 21 October 2026: the carved plates that stretch
 * to their label (back, next, enter), the lettered Balai Refleksi plate, and the
 * Balai Refleksi page redesign that uses Mbah Kedu's avatar.
 *
 * @internal
 */
final class ButtonPlatesTest extends CIUnitTestCase
{
    /** @var array<string, string> asset_key → storage_path */
    private array $media = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('contentRepository', new class ($test) extends ContentRepository {
            public function __construct(private ButtonPlatesTest $test)
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
        service('request')->setLocale('id');
        Services::resetSingle('renderer');
        parent::tearDown();
    }

    /** @return array<string, string> */
    public function media(): array
    {
        return $this->media;
    }

    public function testPlatesAreShippedAndStretchThroughABorderImage(): void
    {
        foreach (['back', 'next', 'enter'] as $kind) {
            $this->assertFileExists(FCPATH . 'assets/ui/plate-' . $kind . '.webp');
            $this->assertSame(
                'class="btn-plate is-' . $kind . ' is-final" style="--plate: url(&quot;' . esc(asset_url_versioned('ui/plate-' . $kind . '.webp'), 'attr') . '&quot;)"',
                plate_attrs($kind, 'is-final'),
            );
        }

        $this->assertStringStartsWith('class="btn-plate is-next"', plate_attrs('sideways'), 'an unknown kind falls back to next');

        // The carved ends keep their shape; only the plain middle grows with the label
        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringContainsString('border-image-slice: 0 var(--slice-r) 0 var(--slice-l) fill;', $css);
        $this->assertStringContainsString('min-width: calc(var(--plate-h) * (var(--cap-l) + var(--cap-r)));', $css);
        $this->assertMatchesRegularExpression('/\.btn-plate\.is-back\s+\{ --slice-l: 262; --slice-r: 189;/', $css);
    }

    public function testReflectionPlateFollowsTheLanguage(): void
    {
        $this->assertFileExists(FCPATH . 'assets/ui/btn-reflection.webp');
        $this->assertFileExists(FCPATH . 'assets/ui/btn-reflection-en.webp');

        $id = picture_link('reflection', base_url('refleksi'), 'Balai Refleksi', 'kedu-reflection');
        $this->assertStringStartsWith('<a class="btn-art kedu-reflection" href="' . esc(base_url('refleksi'), 'attr') . '"><img src="' . esc(asset_url_versioned('ui/btn-reflection.webp'), 'attr') . '"', $id);
        $this->assertStringContainsString('alt="' . esc('Balai Refleksi', 'attr') . '" width="900" height="183"', $id);

        service('request')->setLocale('en');
        $this->assertStringContainsString(esc(asset_url_versioned('ui/btn-reflection-en.webp'), 'attr'), picture_link('reflection', base_url('refleksi'), 'Reflection Hall'));

        // Without a picture: a plain gold link that still says where it goes
        $this->assertSame('<a class="btn btn-primary btn-lg" href="' . esc(base_url('x'), 'attr') . '">Label</a>', picture_link('missing', base_url('x'), 'Label'));
    }

    public function testNavBarPutsArrowButtonsOnThePlates(): void
    {
        $html = view('components/nav-bar', ['nav' => [
            ['label' => 'Peta Kedu', 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'left'],
            ['label' => 'Lewati', 'href' => base_url('refleksi'), 'style' => 'quiet', 'arrow' => 'right'],
            ['label' => 'Balai Refleksi', 'href' => base_url('refleksi'), 'picture' => 'reflection'],
            ['label' => 'Profil', 'href' => base_url('profil'), 'style' => 'quiet', 'icon' => 'user'],
        ]]);

        $this->assertStringContainsString('<a ' . plate_attrs('back') . ' href="' . esc(base_url('peta'), 'attr') . '"><span>Peta Kedu</span></a>', $html);
        $this->assertStringContainsString('<a ' . plate_attrs('next') . ' href="' . esc(base_url('refleksi'), 'attr') . '"><span>Lewati</span></a>', $html);
        $this->assertStringContainsString('<a class="btn-art nav-picture" href="' . esc(base_url('refleksi'), 'attr') . '"><img src="', $html);
        $this->assertStringContainsString('<a class="btn btn-quiet" href="' . esc(base_url('profil'), 'attr') . '"><svg', $html, 'a button without an arrow keeps its look');
    }

    public function testCinematicFinalButtonsUseTheMountainPlate(): void
    {
        $this->assertStringContainsString("'finalPlate' => 'enter'", (string) file_get_contents(APPPATH . 'Views/game/intro.php'));
        $this->assertStringContainsString("'plate' => 'enter'", (string) file_get_contents(APPPATH . 'Views/game/dialogue.php'));
        $this->assertStringContainsString("'finalPlate' => 'reflection'", (string) file_get_contents(APPPATH . 'Views/game/ending.php'));
        $this->assertSame(3, substr_count((string) file_get_contents(APPPATH . 'Views/game/region-done.php'), "'plate' => 'next'"));
    }

    public function testReflectionPageUsesTheAvatarAndThePlateHeading(): void
    {
        $this->media = ['ui.kedu-avatar' => 'assets/uploads/ui.kedu-avatar.webp?v=k1'];

        $page = view('game/reflection', [
            'locale'      => 'id',
            'participant' => ['id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka', 'avatar_no' => null],
            'progress'    => ['completed_nodes' => 12, 'completed_levels' => 3, 'unlocked_level_sequence' => 4,
                'total_stars' => 24, 'shards' => 12, 'shards_total' => 12, 'current_level_id' => null, 'current_node_id' => null],
            'lantern'  => [],
            'existing' => null,
            'errors'   => [],
            'journey'  => [
                'first_pass' => 30, 'scorable' => 44, 'completed' => 12, 'duration_ms' => 1_980_000, 'correct_items' => 37,
                'regions'    => [['name' => 'Temanggung', 'stars' => 9, 'stars_max' => 12]],
                'engines'    => [['engine' => 'puzzle', 'accuracy' => 33.3]],
                'most_missed' => null,
            ],
        ]);

        $hero = substr($page, (int) strpos($page, '<header class="reflection-hero">'));
        $hero = substr($hero, 0, (int) strpos($hero, '</header>'));

        $this->assertMatchesRegularExpression('/<h1 class="reflection-plate">\s*<img src="[^"]*btn-reflection\.webp[^"]*" alt="Balai&#x20;Refleksi"/', $hero);
        $this->assertStringContainsString('<img class="kedu-avatar" src="' . esc(base_url('assets/uploads/ui.kedu-avatar.webp?v=k1'), 'attr') . '"', $hero);
        $this->assertStringContainsString('<p class="reflection-words">' . esc(lang('Game.reflectionLead')) . '</p>', $hero);
        // No pose frame and no name line: it is clearly Mbah Kedu speaking
        $this->assertStringNotContainsString('character-img', $hero);
        $this->assertStringNotContainsString('<cite', $hero);
        $this->assertStringNotContainsString(lang('Game.char_mbah_kedu') . '</', $hero);
        // The bars run on the bronze track; Peta Kedu goes back on the back plate
        $this->assertStringContainsString('data-screen="reflection" style="--loading-track: url(&quot;', $page);
        $this->assertStringContainsString('<a ' . plate_attrs('back') . ' href="' . esc(base_url('peta'), 'attr') . '"><span>' . esc(lang('Game.mapKedu')) . '</span></a>', $page);
    }
}
