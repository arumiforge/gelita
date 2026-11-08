<?php

use App\Database\Seeds\ContentSeeder;
use App\Libraries\NarrationImporter;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Rekaman narasi sesudah perombakan kelas 5 (Dok 2 §11.2): 88 baris naskah
 * + 12 petunjuk Cari Objek (tmg-4, mgl-4, wnb-4 masing-masing 4) = 100; since
 * 21 October 2026 three more lines (`map_done`, Peta Kedu once all is done) = 103
 * rekaman per bahasa, tanpa sisa petunjuk lama `petunjuk-tmg-4-05…08`.
 * Since 17 October 2026 no recording ships with the repository (voice-over).
 *
 * @internal
 */
final class NarrationKelas5Test extends CIUnitTestCase
{
    use GradeFiveWorld;

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = $this->createGradeFiveWorld();
        (new ContentSeeder(config('Database'), $this->conn))->seedDialogues();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testOneHundredRecordingsPerLocale(): void
    {
        $lines = (new NarrationImporter($this->conn))->lines();
        $clues = array_filter($lines, static fn (array $line): bool => $line['context_code'] === NarrationImporter::CLUE_CONTEXT);

        // 112 script lines, 12 round-1 clues, and part 6 (32 §8.3, §9.3): 24 post clues, 24 situations, 15 instructions
        $this->assertCount(187, $lines);
        $this->assertCount(12, $clues);
        $this->assertSame(['post_clue' => 24, 'scenario_situation' => 24, 'challenge_instruction' => 15], array_intersect_key(
            array_count_values(array_column($lines, 'context_code')),
            ['post_clue' => 0, 'scenario_situation' => 0, 'challenge_instruction' => 0],
        ));
        $this->assertSame(['scenario_choice', 'narator'], [$this->conn->table('challenge_items')->where('id', $lines['skenario-tmg-5-a01']['id'])->get()->getRow('interaction_type'), $lines['skenario-tmg-5-a01']['character_code']]);
        $this->assertSame(['find_part', 'mbah_kedu'], [$this->conn->table('challenge_items')->where('id', $lines['unggahan-tmg-4-a01']['id'])->get()->getRow('interaction_type'), $lines['unggahan-tmg-4-a01']['character_code']]);
        $this->assertSame('challenge_nodes', $lines['instruksi-wnb-5']['table']);

        $expected = [];

        foreach (['tmg-4', 'mgl-4', 'wnb-4'] as $node) {
            foreach (range(1, 4) as $n) {
                $expected[] = NarrationImporter::clueCode($node, $n);
            }
        }

        $this->assertSame($expected, array_keys($clues));

        // Teks petunjuk = petunjuk target bank soal §9, sesuai urutan soal
        $this->assertSame('tmg-4-x01', $clues['petunjuk-tmg-4-01']['item_key']);
        $this->assertStringStartsWith('Temukan kuda-kudaan dari anyaman bambu', (string) $clues['petunjuk-tmg-4-01']['text_id']);
        $this->assertStringStartsWith('Temukan daun yang diseduh', (string) $clues['petunjuk-wnb-4-04']['text_id']);
    }

    /**
     * Narration is voice-over by real people (owner, 17 October 2026): no
     * recording or TTS manifest ships with the repository, only the empty
     * drop-in folders for `gelita:narration:import`.
     */
    public function testNoBundledNarrationShipsWithTheRepository(): void
    {
        $this->assertFileDoesNotExist(FCPATH . 'assets/audio/narasi/produksi.json');

        foreach (['id', 'en'] as $locale) {
            $dir = FCPATH . NarrationImporter::FOLDER . $locale . '/';

            $this->assertDirectoryExists($dir);
            $this->assertSame([], glob($dir . '*.{mp3,m4a,ogg,wav}', GLOB_BRACE) ?: [], "no bundled {$locale} recordings");
        }
    }
}
