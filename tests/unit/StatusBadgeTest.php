<?php

use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Owner request of 20 October 2026: the status medallions (navy = open, gold
 * arc = started, green with a check = done) replace the shard dots, the
 * region-map pin icons, and the Peta Kedu "done" badge; a locked challenge
 * keeps its lock. Until the pictures are installed in `assets/ui/` the old
 * marks stay. No database: ContentRepository is replaced by a stub.
 *
 * @internal
 */
final class StatusBadgeTest extends CIUnitTestCase
{
    private const STATES = ['open', 'progress', 'done'];

    /** @var list<string> pictures this test wrote, removed again in tearDown */
    private array $written = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        Services::injectMock('contentRepository', new class () extends ContentRepository {
            public function __construct()
            {
            }

            public function mediaKeyMap(): array
            {
                return [];
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
        foreach ($this->written as $path) {
            @unlink($path);
        }

        Services::resetSingle('contentRepository');
        Services::resetSingle('renderer');
        parent::tearDown();
    }

    /** Puts the three pictures in place for the test, unless the real ones are installed. */
    private function installBadges(): void
    {
        foreach (self::STATES as $state) {
            $path = FCPATH . 'assets/ui/status-' . $state . '.webp';

            if (! is_file($path)) {
                file_put_contents($path, 'RIFF');
                $this->written[] = $path;
            }
        }
    }

    private function lantern(): string
    {
        return component('lantern', [
            'progress' => ['shards' => 1, 'shards_total' => 4, 'completed_levels' => 0],
            'lantern'  => [[
                'code'  => 'temanggung',
                'name'  => 'Temanggung',
                'nodes' => [
                    ['sequence' => 1, 'title' => 'Susun Gambar', 'status' => 'completed', 'started' => false],
                    ['sequence' => 2, 'title' => 'Isi Rumpang', 'status' => 'open', 'started' => true],
                    ['sequence' => 3, 'title' => 'Benar atau Salah', 'status' => 'open', 'started' => false],
                    ['sequence' => 4, 'title' => 'Cari Objek', 'status' => 'locked', 'started' => false],
                ],
            ]],
        ]);
    }

    public function testBadgeFollowsTheStatus(): void
    {
        $this->installBadges();

        $this->assertSame(game_asset_url('ui/status-done.webp'), status_badge_src(['status' => 'completed']));
        $this->assertSame(game_asset_url('ui/status-progress.webp'), status_badge_src(['status' => 'open', 'started' => true]));
        $this->assertSame(game_asset_url('ui/status-open.webp'), status_badge_src(['status' => 'open', 'started' => false]));
        $this->assertNull(status_badge_src(['status' => 'locked']), 'a locked challenge keeps its lock');
    }

    public function testLanternShowsOneMedallionPerNodeButTheLockedOne(): void
    {
        $this->installBadges();
        $html = $this->lantern();
        $dots = substr($html, (int) strpos($html, '<ol class="shard-dots">'));
        $dots = substr($dots, 0, (int) strpos($dots, '</ol>'));

        $this->assertSame(3, substr_count($dots, ' has-badge'));
        $this->assertStringContainsString('<span class="shard is-completed has-badge" aria-hidden="true"><img src="' . esc(game_asset_url('ui/status-done.webp'), 'attr'), $dots);
        $this->assertStringContainsString('<span class="shard is-open is-started has-badge" aria-hidden="true"><img src="' . esc(game_asset_url('ui/status-progress.webp'), 'attr'), $dots);
        $this->assertStringContainsString('<span class="shard is-locked" aria-hidden="true"></span>', $dots);
        $this->assertStringContainsString('2. Isi Rumpang: ' . lang('Game.shardStarted'), $dots);
        $this->assertStringContainsString(lang('Game.shardStarted'), substr($html, (int) strpos($html, 'lantern-legend')));
    }

    public function testWithoutThePicturesTheOldMarksStay(): void
    {
        foreach (self::STATES as $state) {
            if (is_file(FCPATH . 'assets/ui/status-' . $state . '.webp')) {
                $this->markTestSkipped('the real pictures are installed');
            }
        }

        $this->assertNull(status_badge_src(['status' => 'completed']));
        $html = $this->lantern();
        $this->assertStringNotContainsString('has-badge', $html);
        $this->assertStringContainsString('<span class="shard is-open is-started" aria-hidden="true"></span>', $html, 'half dot for a started node');
    }

    public function testMapsUseTheBadgeAndKeepTheLock(): void
    {
        $level = (string) file_get_contents(APPPATH . 'Views/game/map-level.php');
        $this->assertStringContainsString('$badge = status_badge_src($node);', $level);
        $this->assertStringContainsString("<?php elseif (\$badge === null): ?>\n                  <?= icon(\$icons[\$node['status']]) ?>", $level);

        $kedu = (string) file_get_contents(APPPATH . 'Views/game/map-kedu.php');
        $this->assertStringContainsString("status_badge_src(['status' => 'completed'])", $kedu);
        $this->assertStringContainsString("<span class=\"kedu-lock\"><?= icon('lock') ?></span>", $kedu, 'the lock stays');
    }
}
