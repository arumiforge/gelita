<?php

use App\Database\Seeds\ContentSeeder;
use App\Libraries\NarrationImporter;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;
use Tests\Support\Database\NarrationFiles;

/**
 * Instruction narration (32 §8, K10): `instruksi-{node}` is imported into
 * `challenge_nodes.audio_instruction_*` with the context
 * `challenge_instruction`; the ▶ button shows only for an approved
 * recording, and nothing on the challenge screen plays on its own.
 *
 * @internal
 */
final class InstructionNarrationTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;
    use NarrationFiles;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);
        $this->conn = $this->createGradeFiveWorld();
        (new ContentSeeder(config('Database'), $this->conn))->seedDialogues();
        $this->conn->table('participants')->update(['intro_seen_at' => date('Y-m-d H:i:s')]);
        $_SESSION = ['dialogues_shown' => ['1:1' => true]];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->removeNarrationFiles($this->conn);
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testEveryChallengeHasAnInstructionLine(): void
    {
        $lines = array_filter((new NarrationImporter($this->conn))->lines(), static fn (array $l): bool => $l['context_code'] === NarrationImporter::INSTRUCTION_CONTEXT);

        $this->assertCount(15, $lines);
        $this->assertSame('Bacalah ceritanya. Pilih apa yang akan kamu lakukan, lalu pilih alasanmu.', $lines['instruksi-tmg-5']['text_id']);
        $this->assertSame('narator', $lines['instruksi-wnb-1']['character_code']);
    }

    public function testImportLinksTheNodeAndTheButtonShowsOnlyWhenApproved(): void
    {
        $report = (new NarrationImporter($this->conn))->importFiles([['name' => 'instruksi-tmg-1.wav', 'path' => $this->wav(3)]], 'id');
        $this->assertSame(['instruksi-tmg-1'], $report['created'], json_encode($report));

        $node  = $this->conn->table('challenge_nodes')->where('id', $this->nodeId($this->conn, 'tmg-1'))->get()->getRowArray();
        $audio = $this->conn->table('audio_assets')->where('id', (int) $node['audio_instruction_id'])->get()->getRowArray();
        $this->assertSame(['challenge_instruction', 'draft'], [$audio['context_code'], $audio['approval_status']]);
        $this->assertNull($node['audio_instruction_en_id']);

        $draft = $this->challengePage();
        $this->assertStringNotContainsString('instruction-audio', $draft, 'a draft is never heard');

        $this->conn->table('audio_assets')->where('id', (int) $audio['id'])->update(['approval_status' => 'approved']);
        service('contentRepository')->flush();
        service('cache')->clean();

        $page = $this->challengePage();
        $this->assertStringContainsString('class="audio-player instruction-audio" data-audio-id="' . $audio['id'] . '"', $page);
        $this->assertStringContainsString(esc(lang('Game.instructionListen'), 'attr'), $page);
        $this->assertStringNotContainsString('autoplay', $page);
    }

    private function challengePage(): string
    {
        foreach (['security', 'gameContext', 'sessionService', 'eventService', 'challengeService'] as $name) {
            Services::resetSingle($name);
        }

        $response = $this->withSession(['participant_id' => 1, 'game_session_id' => 1] + $_SESSION)->call(Method::GET, 'tantangan/temanggung/1');
        $response->assertOK();

        return (string) $response->response()->getBody();
    }
}
