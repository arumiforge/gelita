<?php

use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Owner requests of 20 October 2026: the status medallions replace the shard
 * dots, the region-map pin icons, the challenge list numbers, and the Peta
 * Kedu "done" badge. Three states only, no "Sedang dikerjakan": green with a
 * check = done, gold arc = open, navy with a lock = locked. The pictures ship
 * in `assets/ui/status-*.webp`; without them status_badge_src() returns null
 * and the old marks show. No database: ContentRepository is replaced by a
 * stub.
 *
 * @internal
 */
final class StatusBadgeTest extends CIUnitTestCase
{
    private const STATES = ['open', 'progress', 'done'];

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
        Services::resetSingle('contentRepository');
        Services::resetSingle('renderer');
        parent::tearDown();
    }

    private function lantern(): string
    {
        return component('lantern', [
            'progress' => ['shards' => 1, 'shards_total' => 4, 'completed_levels' => 0],
            'lantern'  => [[
                'code'  => 'temanggung',
                'name'  => 'Temanggung',
                'nodes' => [
                    ['sequence' => 1, 'title' => 'Susun Gambar', 'status' => 'completed'],
                    ['sequence' => 2, 'title' => 'Isi Rumpang', 'status' => 'open'],
                    ['sequence' => 3, 'title' => 'Benar atau Salah', 'status' => 'locked'],
                    ['sequence' => 4, 'title' => 'Cari Objek', 'status' => 'locked'],
                ],
            ]],
        ]);
    }

    public function testBadgeFollowsTheStatus(): void
    {
        $this->assertSame(asset_url_versioned('ui/status-done.webp'), status_badge_src(['status' => 'completed']));
        $this->assertSame(asset_url_versioned('ui/status-progress.webp'), status_badge_src(['status' => 'open']), 'open wears the gold arc');
        $this->assertSame(asset_url_versioned('ui/status-open.webp'), status_badge_src(['status' => 'locked']), 'locked wears the navy medallion');
        $this->assertNull(status_badge_src(['status' => 'unknown']));
    }

    public function testLanternShowsThreeStatesWithALockOnTheLockedMedallion(): void
    {
        $html = $this->lantern();
        $dots = substr($html, (int) strpos($html, '<ol class="shard-dots">'));
        $dots = substr($dots, 0, (int) strpos($dots, '</ol>'));

        $this->assertSame(4, substr_count($dots, ' has-badge'));
        $this->assertStringContainsString('<span class="shard is-completed has-badge" aria-hidden="true"><img src="' . esc(asset_url_versioned('ui/status-done.webp'), 'attr'), $dots);
        $this->assertStringContainsString('<span class="shard is-open has-badge" aria-hidden="true"><img src="' . esc(asset_url_versioned('ui/status-progress.webp'), 'attr'), $dots);
        $this->assertStringContainsString('<span class="shard is-locked has-badge" aria-hidden="true"><img src="' . esc(asset_url_versioned('ui/status-open.webp'), 'attr'), $dots);
        $this->assertSame(2, substr_count($dots, 'shard-lock'), 'only the locked medallions carry a lock');
        $this->assertStringContainsString('2. Isi Rumpang: ' . lang('Game.shardOpen'), $dots);

        $legend = substr($html, (int) strpos($html, 'lantern-legend'));
        $this->assertSame(3, substr_count($legend, 'class="shard '), 'done, open, locked');
        $this->assertStringNotContainsString('Sedang dikerjakan', $html);
        $this->assertStringNotContainsString('is-started', $html);
    }

    /** The owner's three pictures ship with the game: square, transparent WebP. */
    public function testThePicturesShipWithTheGame(): void
    {
        foreach (self::STATES as $state) {
            $path = FCPATH . 'assets/ui/status-' . $state . '.webp';
            $this->assertFileExists($path, $state);

            $info = getimagesize($path);
            $this->assertSame(IMAGETYPE_WEBP, $info[2], $state);
            $this->assertSame($info[0], $info[1], "{$state} is square");
            $this->assertStringContainsString('ALPH', (string) file_get_contents($path), "{$state} keeps its transparency");
        }
    }

    public function testMapsUseTheBadgeWithNumberOrLock(): void
    {
        $level = (string) file_get_contents(APPPATH . 'Views/game/map-level.php');
        $this->assertStringContainsString("<?= \$medal(\$node, 'pin-badge') ?>", $level, 'the pin');
        $this->assertStringContainsString("<?= \$medal(\$node, 'node-badge') ?>", $level, 'the challenge list number');
        // Owner, 21 October 2026: no number on the open medallion
        $this->assertStringContainsString(". (\$n['status'] === 'locked' ? icon('lock', 'badge-lock') : '');", $level);
        $this->assertStringNotContainsString("'open'   => '<b class=\"num\">'", $level);
        $this->assertStringNotContainsString('started', $level);

        $kedu = (string) file_get_contents(APPPATH . 'Views/game/map-kedu.php');
        $this->assertStringContainsString("status_badge_src(['status' => 'completed'])", $kedu);
        $this->assertStringContainsString("<span class=\"kedu-lock\"><?= icon('lock') ?></span>", $kedu, 'the region lock stays');
    }
}
