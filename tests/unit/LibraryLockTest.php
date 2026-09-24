<?php

use App\Controllers\Game\LibraryController;
use App\Entities\ChallengeAttempt;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Entities\LibraryPage;
use App\Entities\Participant;
use App\Services\ContentRepository;
use App\Services\EventService;
use App\Services\GameContext;
use App\Services\ScoringService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Pustaka per wilayah: isi Pustaka sebuah wilayah baru terbuka setelah SEMUA
 * tantangan wilayah itu selesai pada sesi ini, apa pun unlock mode studinya.
 *
 * - `/pustaka/{code}` untuk wilayah yang belum tuntas merender halaman
 *   terkunci (bukan redirect) dan TIDAK mencatat `library_opened`;
 * - wilayah yang belum terbuka tetap ditolak ke peta;
 * - momen "Pustaka terbuka" hanya milik attempt yang menuntaskan wilayah.
 *
 * Tanpa database: GameContext, ScoringService, ContentRepository, dan
 * EventService diganti tiruan; status buka wilayah dan data HUD dari probe.
 *
 * @internal
 */
final class LibraryLockTest extends CIUnitTestCase
{
    /** level_id → [completed_nodes, total_nodes] */
    private array $scores = [];

    /** Event yang dicatat EventService tiruan: [type, context] */
    private array $events = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $test = $this;

        Services::injectMock('scoringService', new class ($test) extends ScoringService {
            public function __construct(private LibraryLockTest $test)
            {
            }

            public function levelScore(int $sessionId, int $levelId): array
            {
                return $this->test->score($levelId);
            }
        });

        Services::injectMock('contentRepository', new class () extends ContentRepository {
            public function __construct()
            {
            }

            public function level(string $code): ?Level
            {
                $ids = ['temanggung' => 1, 'magelang' => 2, 'wonosobo' => 3];

                return isset($ids[$code]) ? LibraryLockTest::level($ids[$code], $code) : null;
            }

            public function libraryPages(int $levelId): array
            {
                $page = new LibraryPage();
                $page->injectRawData([
                    'id' => 40 + $levelId, 'level_id' => $levelId, 'sequence' => 1,
                    'title_id' => 'Candi Borobudur', 'title_en' => 'Borobudur Temple',
                    'body_id' => 'Isi halaman pustaka.', 'body_en' => 'Library page body.',
                ]);

                return [$page];
            }

            public function mediaKeyMap(): array
            {
                return [];
            }
        });

        Services::injectMock('eventService', new class ($test) extends EventService {
            public function __construct(private LibraryLockTest $test)
            {
            }

            public function record(GameSession $session, string $type, array $payload = [], array $refs = []): void
            {
                $this->test->recorded($type, $refs);
            }
        });

        $session = new GameSession();
        $session->injectRawData(['id' => 9, 'participant_id' => 5, 'locale' => 'id', 'status' => 'active']);

        $participant = new Participant();
        $participant->injectRawData(['id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka', 'intro_seen_at' => '2026-09-01 08:00:00']);

        $context              = new GameContext();
        $context->session     = $session;
        $context->participant = $participant;
        Services::injectMock('gameContext', $context);

        Services::resetSingle('renderer');
    }

    protected function tearDown(): void
    {
        foreach (['scoringService', 'contentRepository', 'eventService', 'gameContext', 'renderer'] as $name) {
            Services::resetSingle($name);
        }

        parent::tearDown();
    }

    // ------------------------------------------------------------ status

    public function testLibraryStatusFollowsTheRegionStatus(): void
    {
        $probe = $this->probe('sequential', 2);

        $this->assertSame('open', $probe->status('completed'));
        $this->assertSame('locked', $probe->status('in_progress'));
        $this->assertSame('locked', $probe->status('open'), 'wilayah baru terbuka: Pustaka belum');
        $this->assertSame('unavailable', $probe->status('locked'));
    }

    public function testAccessIsPerRegion(): void
    {
        $this->scores = [1 => [5, 5], 2 => [3, 5]];
        $probe        = $this->probe('sequential', 2);

        $this->assertSame('open', $probe->access(self::level(1, 'temanggung'))['status']);

        $magelang = $probe->access(self::level(2, 'magelang'));
        $this->assertSame('locked', $magelang['status']);
        $this->assertSame([3, 5], [$magelang['completed_nodes'], $magelang['total_nodes']]);
        $this->assertSame('wilayah/magelang', $magelang['entry'], 'sedang dijelajahi → langsung ke peta wilayah');

        $this->assertSame('unavailable', $probe->access(self::level(3, 'wonosobo'))['status']);
    }

    public function testContinueLinkOfNewRegionGoesThroughItsDialogue(): void
    {
        $this->scores = [2 => [0, 5]];

        $this->assertSame('dialog/magelang', $this->probe('sequential', 2)->access(self::level(2, 'magelang'))['entry']);
    }

    public function testFreeUnlockModeStillLocksTheLibraryUntilTheRegionIsDone(): void
    {
        $this->scores = [3 => [4, 5]];
        $probe        = $this->probe('free');

        $access = $probe->access(self::level(3, 'wonosobo'));
        $this->assertSame('in_progress', $access['level_status'], 'mode free: wilayah terbuka');
        $this->assertSame('locked', $access['status'], 'mode free: Pustaka tetap terkunci');

        $this->scores = [3 => [5, 5]];
        $this->assertSame('open', $probe->access(self::level(3, 'wonosobo'))['status']);
    }

    // ------------------------------------------------------------ /pustaka/{code}

    public function testLockedLibraryRendersExplanationWithoutRecordingAnEvent(): void
    {
        $this->scores = [2 => [3, 5]];

        $page = $this->probe('free')->show('magelang');

        $this->assertIsString($page, 'halaman terkunci dirender, bukan redirect');
        $this->assertStringContainsString('data-screen="library-locked"', $page);
        $this->assertStringContainsString(esc(lang('Game.libraryLockedTitle', ['Magelang'])), $page);
        $this->assertStringContainsString('Selesaikan kelima tantangan di Magelang untuk membuka Pustaka Magelang.', $page);
        $this->assertStringContainsString('3/5', $page);
        $this->assertStringContainsString('href="' . esc(base_url('wilayah/magelang'), 'attr') . '"', $page, 'Lanjutkan tantangan');
        $this->assertStringNotContainsString('Isi halaman pustaka.', $page, 'isi buku tidak ikut dikirim');
        $this->assertSame([], $this->events, 'library_opened tidak dicatat');
    }

    public function testCompletedRegionShowsTheBookAndRecordsLibraryOpened(): void
    {
        $this->scores = [2 => [5, 5]];

        $page = $this->probe('free')->show('magelang');

        $this->assertIsString($page);
        $this->assertStringContainsString('data-screen="library"', $page);
        $this->assertStringContainsString('Isi halaman pustaka.', $page);
        $this->assertStringContainsString('<title>Pustaka Magelang · GELITA</title>', $page);
        $this->assertStringContainsString('href="' . esc(base_url('pustaka'), 'attr') . '"', $page, 'tautan ke Pustaka Kedu');
        $this->assertSame([['library_opened', ['level_id' => 2]]], $this->events);
    }

    public function testRegionThatIsNotOpenYetIsStillRejected(): void
    {
        $this->scores = [3 => [0, 5]];

        $page = $this->probe('sequential', 2)->show('wonosobo');

        $this->assertInstanceOf(RedirectResponse::class, $page);
        $this->assertStringEndsWith('/peta', $page->getHeaderLine('Location'));
        $this->assertSame([], $this->events);
    }

    // ------------------------------------------------------------ /pustaka

    public function testShelfShowsOneCardPerRegionWithItsStatus(): void
    {
        $this->scores = [1 => [5, 5], 2 => [2, 5], 3 => [0, 5]];

        $page = $this->probe('sequential', 2)->index();

        $this->assertStringContainsString('data-screen="library-index"', $page);
        $this->assertSame(3, substr_count($page, 'class="shelf-card '));

        // Temanggung tuntas → Baca
        $this->assertStringContainsString('shelf-card is-open', $page);
        $this->assertStringContainsString('href="' . base_url('pustaka/temanggung') . '"', $page);

        // Magelang terbuka, belum tuntas → progres, penjelasan, lanjut ke entry wilayah
        $this->assertStringContainsString('shelf-card is-locked', $page);
        $this->assertStringContainsString('2/5', $page);
        $this->assertStringContainsString('Selesaikan kelima tantangan di Magelang untuk membuka Pustaka Magelang.', $page);
        $this->assertStringContainsString('href="' . esc(base_url('wilayah/magelang'), 'attr') . '"', $page);
        $this->assertStringNotContainsString('href="' . base_url('pustaka/magelang') . '"', $page);

        // Wonosobo belum terbuka → keterangan tanpa tombol
        $this->assertStringContainsString('shelf-card is-unavailable', $page);
        $this->assertStringContainsString(esc(lang('Game.libraryUnavailable', ['Wonosobo'])), $page);
        $this->assertStringNotContainsString('wilayah/wonosobo', $page);
        $this->assertStringNotContainsString('dialog/wonosobo', $page);
    }

    public function testRegionMapMarksTheLockedLibraryButton(): void
    {
        $level = self::level(2, 'magelang');
        $data  = self::hud() + [
            'level'      => $level,
            'levelScore' => ['score' => 0.0, 'stars' => 0, 'completed_nodes' => 3, 'total_nodes' => 5],
            'nodes'      => [],
            'hasLibrary' => true,
        ];

        $locked = view('game/map-level', $data + ['library' => ['status' => 'locked', 'level_status' => 'in_progress', 'entry' => 'wilayah/magelang', 'completed_nodes' => 3, 'total_nodes' => 5]]);
        $this->assertStringContainsString('<span>Pustaka Magelang</span>', $locked);
        $this->assertStringContainsString('aria-disabled="true" data-library-locked="magelang"', $locked);
        $this->assertStringContainsString('data-locked-message="' . esc(lang('Game.libraryLockedText', ['Magelang', 5]), 'attr') . '"', $locked);
        $this->assertStringContainsString('href="' . esc(base_url('pustaka/magelang'), 'attr') . '"', $locked, 'tanpa JS → halaman terkunci');

        Services::resetSingle('renderer');
        $open = view('game/map-level', $data + ['library' => ['status' => 'open', 'level_status' => 'completed', 'entry' => 'wilayah/magelang', 'completed_nodes' => 5, 'total_nodes' => 5]]);
        $this->assertStringContainsString('<span>Pustaka Magelang</span>', $open);
        $this->assertStringNotContainsString('data-library-locked', $open);
    }

    // ------------------------------------------------------------ momen terbuka

    public function testOnlyTheAttemptThatFinishesTheRegionOpensTheLibrary(): void
    {
        $nodes = [11, 12, 13];

        // urut completed_at: node 11, 12, 11 (ulang), 13 → penuntas = 104
        $attempts = [$this->attempt(101, 11), $this->attempt(102, 12), $this->attempt(103, 11), $this->attempt(104, 13), $this->attempt(105, 12)];

        $this->assertSame(104, LibraryClosingProbe::closing($attempts, $nodes));
        $this->assertNull(LibraryClosingProbe::closing(array_slice($attempts, 0, 3), $nodes), 'node 13 belum pernah selesai');
        $this->assertNull(LibraryClosingProbe::closing($attempts, []));
        $this->assertSame(102, LibraryClosingProbe::closing($attempts, [11, 12]), 'attempt node lain diabaikan');
    }

    // ------------------------------------------------------------ bantuan

    /** @return array{score: float, stars: int, completed_nodes: int, total_nodes: int} */
    public function score(int $levelId): array
    {
        [$done, $total] = $this->scores[$levelId] ?? [0, 5];

        return ['score' => 0.0, 'stars' => 0, 'completed_nodes' => $done, 'total_nodes' => $total];
    }

    /** Data HUD tetap (hudData() asli membaca database). */
    public static function hud(): array
    {
        return [
            'locale'      => 'id',
            'participant' => ['id' => 5, 'username' => 'jaka', 'display_name' => 'Jaka', 'avatar_code' => null],
            'progress'    => ['completed_nodes' => 0, 'completed_levels' => 0, 'unlocked_level_sequence' => 1, 'total_score' => 0.0,
                'total_stars' => 0, 'shards' => 0, 'shards_total' => 15, 'current_level_id' => null, 'current_node_id' => null],
            'lantern' => [],
        ];
    }

    public function recorded(string $type, array $context): void
    {
        $this->events[] = [$type, $context];
    }

    public static function level(int $id, string $code): Level
    {
        $level = new Level();
        $level->injectRawData([
            'id' => $id, 'code' => $code, 'sequence' => $id,
            'name_id' => ucfirst($code), 'name_en' => ucfirst($code),
            'difficulty' => 'easy', 'background_media_id' => null,
        ]);

        return $level;
    }

    private function attempt(int $id, int $nodeId): ChallengeAttempt
    {
        $attempt = new ChallengeAttempt();
        $attempt->injectRawData(['id' => $id, 'challenge_node_id' => $nodeId, 'status' => 'completed']);

        return $attempt;
    }

    /**
     * LibraryController dengan unlock mode dan urutan wilayah terbuka tiruan
     * (keduanya dibaca dari database di aplikasi nyata) dan HUD tetap.
     * Mode `free` memakai levelUnlocked() asli GameProgress.
     */
    private function probe(string $mode, ?int $unlockedSequence = null): object
    {
        return new class ($mode, $unlockedSequence) extends LibraryController {
            public function __construct(private string $mode, private ?int $unlockedSequence)
            {
            }

            public function status(string $levelStatus): string
            {
                return $this->libraryStatus($levelStatus);
            }

            public function access(Level $level): array
            {
                return $this->libraryAccess($this->session(), $level);
            }

            protected function unlockMode(GameSession $session): string
            {
                return $this->mode;
            }

            protected function levelUnlocked(GameSession $session, int $sequence): bool
            {
                return $this->mode === 'free' ? parent::levelUnlocked($session, $sequence) : $sequence <= (int) $this->unlockedSequence;
            }

            protected function hudData(): array
            {
                return LibraryLockTest::hud();
            }

            /** levelOverview() asli membaca session_progress; statusnya disusun di sini dengan aturan yang sama. */
            protected function levelOverview(GameSession $session): array
            {
                $rows = [];

                foreach (['temanggung' => 1, 'magelang' => 2, 'wonosobo' => 3] as $code => $id) {
                    $access = $this->libraryAccess($session, LibraryLockTest::level($id, $code));
                    $rows[] = [
                        'id' => $id, 'code' => $code, 'sequence' => $id, 'name' => ucfirst($code), 'difficulty' => 'easy',
                        'status' => $access['level_status'], 'entry' => $access['entry'], 'score' => 0.0, 'stars' => 0,
                        'completed_nodes' => $access['completed_nodes'], 'total_nodes' => $access['total_nodes'],
                        'map_x' => 0, 'map_y' => 0, 'map_media' => null, 'background' => null,
                    ];
                }

                return $rows;
            }
        };
    }
}

/** Membuka GameProgress::closingAttemptId() yang protected static. */
final class LibraryClosingProbe extends LibraryController
{
    public static function closing(array $attempts, array $nodeIds): ?int
    {
        return self::closingAttemptId($attempts, $nodeIds);
    }
}
