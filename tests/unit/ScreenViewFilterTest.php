<?php

use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Filters\ScreenViewFilter;
use App\Services\ContentRepository;
use App\Services\EventService;
use App\Services\GameContext;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Event `screen_viewed` (perombakan kelas 5, Dok 1 §12.4): setiap halaman
 * game yang dirender untuk siswa bersesi tercatat dengan kode layar, layar
 * sebelumnya, dan parameter rute; pengalihan dan respons non-HTML tidak.
 *
 * @internal
 */
final class ScreenViewFilterTest extends CIUnitTestCase
{
    /** @var list<array{type: string, payload: array<string, mixed>, refs: array<string, int>}> */
    public array $events = [];

    public bool $failEvents = false;

    protected function setUp(): void
    {
        parent::setUp();

        $test = $this;

        Services::injectMock('eventService', new class ($test) extends EventService {
            public function __construct(private ScreenViewFilterTest $test)
            {
            }

            public function record(GameSession $session, string $type, array $payload = [], array $refs = []): void
            {
                if ($this->test->failEvents) {
                    throw new RuntimeException('database tidak dapat dihubungi');
                }

                $this->test->events[] = ['type' => $type, 'payload' => $payload, 'refs' => $refs];
            }
        });

        Services::injectMock('contentRepository', new class () extends ContentRepository {
            public function __construct()
            {
            }

            public function levels(): array
            {
                $out = [];

                foreach (['temanggung' => 1, 'magelang' => 2, 'wonosobo' => 3] as $code => $id) {
                    $level = new Level();
                    $level->injectRawData(['id' => $id, 'code' => $code, 'sequence' => $id]);
                    $out[] = $level;
                }

                return $out;
            }

            public function nodesForLevel(int $levelId): array
            {
                $out = [];

                for ($sequence = 1; $sequence <= 4; $sequence++) {
                    $node = new ChallengeNode();
                    $node->injectRawData(['id' => $levelId * 10 + $sequence, 'level_id' => $levelId, 'sequence' => $sequence]);
                    $out[] = $node;
                }

                return $out;
            }
        });

        $session = new GameSession();
        $session->injectRawData(['id' => 9, 'participant_id' => 5, 'locale' => 'id', 'status' => 'active']);

        $context          = new GameContext();
        $context->session = $session;
        Services::injectMock('gameContext', $context);

        session()->remove(['last_screen', 'participant_id', 'game_session_id']);
    }

    protected function tearDown(): void
    {
        foreach (['eventService', 'contentRepository', 'gameContext'] as $name) {
            Services::resetSingle($name);
        }

        session()->remove('last_screen');
        parent::tearDown();
    }

    public function testEveryGameScreenHasItsCode(): void
    {
        $expected = [
            'gerbang'               => ['gate', []],
            'gerbang/peta'          => ['gate', []],
            'intro'                 => ['intro', []],
            'peta'                  => ['map', []],
            'wilayah/magelang'      => ['region', ['level' => 'magelang']],
            'dialog/intro'          => ['dialogue', ['code' => 'intro']],
            'misi/magelang/2'       => ['brief', ['level' => 'magelang', 'seq' => 2]],
            'tantangan/magelang/2'  => ['challenge', ['level' => 'magelang', 'seq' => 2]],
            'selesai/41'            => ['finished', ['attempt' => 41]],
            'hasil/wonosobo/4'      => ['result', ['level' => 'wonosobo', 'seq' => 4]],
            'tuntas/temanggung'     => ['region_done', ['level' => 'temanggung']],
            'penutup'               => ['ending', []],
            'pustaka'               => ['library_index', []],
            'pustaka/magelang'      => ['library', ['level' => 'magelang']],
            'profil'                => ['profile', []],
            'refleksi'              => ['reflection', []],
            'ganti-sandi'           => ['change_password', []],
        ];

        foreach ($expected as $path => $match) {
            $this->assertSame($match, ScreenViewFilter::resolve($path), $path);
        }

        foreach (['', 'mulai', 'masuk', 'daftar/2', 'api/session', 'admin/dashboard', 'tantangan/magelang', 'pustaka/a/b'] as $path) {
            $this->assertNull(ScreenViewFilter::resolve($path), "{$path} bukan layar game");
        }
    }

    public function testRecordsScreenPreviousScreenAndRouteReferences(): void
    {
        $this->view('peta');
        $this->view('tantangan/magelang/2');

        $this->assertCount(2, $this->events);
        $this->assertSame('screen_viewed', $this->events[0]['type']);
        $this->assertSame('map', $this->events[0]['payload']['screen']);
        $this->assertSame('/peta', $this->events[0]['payload']['path']);
        $this->assertNull($this->events[0]['payload']['from']);
        $this->assertSame('{}', json_encode($this->events[0]['payload']['params']), 'params tetap objek JSON');
        $this->assertSame([], $this->events[0]['refs']);

        $this->assertSame(
            ['screen' => 'challenge', 'path' => '/tantangan/magelang/2', 'from' => 'map', 'params' => ['level' => 'magelang', 'seq' => 2]],
            $this->events[1]['payload'],
        );
        $this->assertSame(['level_id' => 2, 'challenge_node_id' => 22], $this->events[1]['refs']);
        $this->assertSame('challenge', session('last_screen'));

        $this->view('pustaka/wonosobo');
        $this->assertSame(['level_id' => 3], $this->events[2]['refs']);
        $this->assertSame('challenge', $this->events[2]['payload']['from']);
    }

    public function testLockedLibraryIsItsOwnScreen(): void
    {
        $this->view('pustaka/magelang', '<section class="screen library-locked" data-screen="library-locked">');

        $this->assertSame('library_locked', $this->events[0]['payload']['screen']);
    }

    public function testRedirectsPostsErrorsAndNonHtmlAreNotRecorded(): void
    {
        $this->view('peta', status: 302);
        $this->view('peta', status: 404);
        $this->view('refleksi', method: 'POST');
        $this->view('peta', contentType: 'application/json');
        $this->view('masuk');

        $this->assertSame([], $this->events);
        $this->assertNull(session('last_screen'));
    }

    public function testWithoutGameSessionNothingIsRecorded(): void
    {
        Services::injectMock('gameContext', new GameContext());

        $this->view('peta');

        $this->assertSame([], $this->events);
    }

    public function testFailedRecordingNeverBreaksThePage(): void
    {
        $this->failEvents = true;

        $this->assertNull($this->view('peta'));
        $this->assertNull(session('last_screen'), 'layar yang gagal dicatat tidak menjadi "from"');
    }

    private function view(string $path, string $body = '<main>GELITA</main>', int $status = 200, string $method = 'GET', string $contentType = 'text/html; charset=UTF-8'): mixed
    {
        $request = (new IncomingRequest(config('App'), new SiteURI(config('App'), $path), null, new UserAgent()))->withMethod($method);

        $response = new Response(config('App'));
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', $contentType);
        $response->setBody($body);

        return (new ScreenViewFilter())->after($request, $response);
    }
}
