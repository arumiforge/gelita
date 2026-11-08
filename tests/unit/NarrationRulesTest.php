<?php

use App\Database\Seeds\ContentSeeder;
use App\Libraries\NarrationImporter;
use App\Services\ChallengeService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\GradeFiveWorld;
use Tests\Support\Database\NarrationFiles;

/**
 * Narration rules (32 §9, K11, K12): Isi Rumpang and Benar atau Salah never
 * carry audio, even with a filled column; the importer refuses recordings for
 * them; `skenario-*` and `unggahan-*` land on the item of their code;
 * `petunjuk-*` never lands on a *Periksa Unggahan* item.
 *
 * @internal
 */
final class NarrationRulesTest extends CIUnitTestCase
{
    use GradeFiveWorld;
    use NarrationFiles;

    private \CodeIgniter\Database\BaseConnection $conn;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->originalKey = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-narasi';
        $this->conn = $this->createGradeFiveWorld();
        (new ContentSeeder(config('Database'), $this->conn))->seedDialogues();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->removeNarrationFiles($this->conn);
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testReadingAndStatementPayloadsCarryNoAudio(): void
    {
        $importer = new NarrationImporter($this->conn);
        $importer->importFiles([['name' => 'skenario-tmg-5-a01.wav', 'path' => $this->wav(1)]], 'id');
        $audioId = (int) $this->conn->table('audio_assets')->select('id')->get()->getRow('id');
        $this->conn->table('audio_assets')->update(['approval_status' => 'approved']);

        // Even with a filled column (set by hand), statements and blanks are not read out
        $this->conn->table('challenge_items')->whereIn('interaction_type', ['fill_blank_bank', 'verdict_card'])->update(['audio_prompt_id' => $audioId]);
        service('contentRepository')->flush();
        service('cache')->clean();

        foreach (['tmg-2', 'tmg-3'] as $ref) {
            $payload = (new ChallengeService())->openNode(model(App\Models\GameSessionModel::class)->find(1), $this->nodeId($this->conn, $ref))['payload'];
            $json    = json_encode($payload);

            $this->assertStringNotContainsString('"audio"', $json, $ref);
            $this->assertStringNotContainsString('audio.narasi', $json, $ref);
        }
    }

    public function testImporterRefusesStatementsAndMapsItemCodes(): void
    {
        $importer = new NarrationImporter($this->conn);

        $this->assertSame(lang('Admin.narration_d1_refused'), $importer->match('skenario-tmg-3-a01.mp3')['error']);
        $this->assertSame(lang('Admin.narration_d1_refused'), $importer->match('unggahan-mgl-2-b03.mp3')['error']);

        $report = $importer->importFiles([
            ['name' => 'skenario-tmg-5-b02.wav', 'path' => $this->wav(2)],
            ['name' => 'unggahan-tmg-4-a01.wav', 'path' => $this->wav(3)],
            ['name' => 'petunjuk-tmg-4-01.wav', 'path' => $this->wav(4)],
        ], 'id');
        $this->assertCount(3, $report['created'], json_encode($report));

        $item = fn (string $key): array => $this->conn->table('challenge_items')->where('item_key', $key)->get()->getRowArray();
        $ctx  = fn (array $row): string => (string) $this->conn->table('audio_assets')->where('id', (int) $row['audio_prompt_id'])->get()->getRow('context_code');

        $this->assertSame('scenario_situation', $ctx($item('tmg-5-b02')));
        $this->assertNull($item('tmg-5-a02')['audio_prompt_id'], 'variants a and b never swap');
        $this->assertSame('post_clue', $ctx($item('tmg-4-a01')));
        $this->assertSame('hunt_clue', $ctx($item('tmg-4-x01')), 'petunjuk-tmg-4-01 = Temukan Benda x01');

        foreach ($this->conn->table('challenge_items')->where('interaction_type', 'find_part')->get()->getResultArray() as $part) {
            if ($part['audio_prompt_id'] !== null) {
                $this->assertSame('post_clue', $ctx($part), 'no Mbah Kedu round-1 clue on a round-2 item: ' . $part['item_key']);
            }
        }
    }
}
