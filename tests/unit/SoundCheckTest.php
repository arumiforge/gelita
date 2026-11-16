<?php

use App\Filters\SoundCheckFilter;
use App\Libraries\EventCatalog;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ResearchModeWorld;

/**
 * Research-mode sound check (owner, 15 November 2026, decision 4 of
 * docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md; built 16 November 2026):
 * the first page of every research-mode game session goes to `/cek-suara`;
 * each answer is the server-only research event `sound_check_answered`;
 * "Ya" goes on to the page the student was opening, "Tidak" shows what to do
 * and offers "Lanjut tanpa suara"; outside research mode nothing changes.
 *
 * @internal
 */
final class SoundCheckTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ResearchModeWorld;

    private int $sessionId;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->sessionId = $this->createResearchModeWorld(2)['session']->id;
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testTheFirstPageOfAResearchSessionGoesToTheSoundCheck(): void
    {
        $this->signInUnchecked();
        $response = $this->send(Method::GET, 'peta');

        $this->assertRedirectsTo('cek-suara', $response);
        $this->assertSame('/peta', $_SESSION[SoundCheckFilter::TARGET] ?? null);

        $page = (string) $this->send(Method::GET, 'cek-suara')->getBody();
        $this->assertStringContainsString('data-screen="sound-check"', $page);
        $this->assertStringContainsString(esc(lang('Game.soundCheckQuestion')), $page);
        $this->assertStringContainsString('name="heard" value="ya"', $page);
        $this->assertStringContainsString('name="heard" value="tidak"', $page);
        $this->assertStringNotContainsString('name="lanjut"', $page, 'no way round before an answer');
    }

    public function testYesIsRecordedAndGoesOnToThePageTheStudentWasOpening(): void
    {
        $this->signInUnchecked();
        $this->send(Method::GET, 'peta');
        $response = $this->send(Method::POST, 'cek-suara', ['heard' => 'ya', 'plays' => '2']);

        $this->assertRedirectsTo('peta', $response);
        $this->assertSame($this->sessionId, (int) ($_SESSION[SoundCheckFilter::DONE] ?? 0));
        $this->assertSame([['heard' => true, 'plays' => 2]], $this->answers());

        // Answered once per game session: the next page is not sent back
        $next = $this->send(Method::GET, 'peta');
        $this->assertFalse($next->isRedirect() && str_contains((string) $next->getRedirectUrl(), 'cek-suara'));
    }

    public function testNoShowsHelpAndLetsTheStudentGoOnWithoutSound(): void
    {
        $this->signInUnchecked();
        $this->send(Method::GET, 'peta');
        $this->assertRedirectsTo('cek-suara', $this->send(Method::POST, 'cek-suara', ['heard' => 'tidak', 'plays' => '1']));
        $this->assertSame([['heard' => false, 'plays' => 1]], $this->answers());

        $page = (string) $this->send(Method::GET, 'cek-suara')->getBody();
        $this->assertStringContainsString(esc(lang('Game.soundCheckHelp')), $page);
        $this->assertStringContainsString('name="lanjut" value="1"', $page);

        $this->assertRedirectsTo('peta', $this->send(Method::POST, 'cek-suara', ['lanjut' => '1']));
        $this->assertCount(1, $this->answers(), 'going on adds no answer: the last answer is the result');
        $this->assertSame($this->sessionId, (int) ($_SESSION[SoundCheckFilter::DONE] ?? 0));
    }

    public function testANewGameSessionAsksAgain(): void
    {
        $this->signInUnchecked();
        $_SESSION[SoundCheckFilter::DONE] = $this->sessionId + 1000;

        $this->assertRedirectsTo('cek-suara', $this->send(Method::GET, 'peta'));
    }

    public function testOutsideResearchModeThereIsNoSoundCheck(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $this->signInUnchecked();

        $response = $this->send(Method::GET, 'peta');
        $this->assertFalse($response->isRedirect() && str_contains((string) $response->getRedirectUrl(), 'cek-suara'));

        $this->assertRedirectsTo('peta', $this->send(Method::GET, 'cek-suara'), 'the page itself sends the student on');
    }

    public function testABadAnswerOrAForeignTargetIsRefused(): void
    {
        $this->signInUnchecked();
        $_SESSION[SoundCheckFilter::TARGET] = '//evil.example/x';

        $this->assertRedirectsTo('cek-suara', $this->send(Method::POST, 'cek-suara', ['heard' => 'mungkin']));
        $this->assertSame([], $this->answers());

        $this->assertRedirectsTo('peta', $this->send(Method::POST, 'cek-suara', ['heard' => 'ya', 'plays' => '500']));
        $this->assertSame([['heard' => true, 'plays' => 0]], $this->answers(), 'plays outside 0–99 are stored as 0');
    }

    public function testTheEventIsResearchDataTheClientCannotSend(): void
    {
        $this->assertTrue(EventCatalog::isResearch('sound_check_answered'));
        $this->assertTrue(EventCatalog::isServerOnly('sound_check_answered'));
        $this->assertSame('Cek suara dijawab', lang('Admin.event.sound_check_answered'));
    }

    /** Signed in with a game session whose sound check is not answered yet. */
    private function signInUnchecked(): void
    {
        $this->signIn(1, $this->sessionId);
        unset($_SESSION[SoundCheckFilter::DONE]);
    }

    /** @return list<array<string, mixed>> payloads of the recorded answers, oldest first */
    private function answers(): array
    {
        $rows = $this->conn->table('game_event_logs')->where('event_type', 'sound_check_answered')->orderBy('id')->get()->getResultArray();

        return array_map(static fn (array $row): array => json_decode((string) $row['payload_json'], true), $rows);
    }
}
