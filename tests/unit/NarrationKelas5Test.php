<?php

use App\Libraries\NarrationImporter;
use App\Libraries\StorySync;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Rekaman narasi sesudah perombakan kelas 5 (Dok 2 §11.2): 88 baris naskah
 * + 12 petunjuk Cari Objek (tmg-4, mgl-4, wnb-4 masing-masing 4) = 100
 * rekaman per bahasa, dan rekaman bawaan di repositori tepat sebanyak itu,
 * tanpa sisa petunjuk lama `petunjuk-tmg-4-05…08`.
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
        (new StorySync($this->conn))->run();
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

        $this->assertCount(100, $lines);
        $this->assertCount(12, $clues);

        $expected = [];

        foreach (['tmg-4', 'mgl-4', 'wnb-4'] as $node) {
            foreach (range(1, 4) as $n) {
                $expected[] = NarrationImporter::clueCode($node, $n);
            }
        }

        $this->assertSame($expected, array_keys($clues));

        // Teks petunjuk = petunjuk target bank soal §9, sesuai urutan soal
        $this->assertSame('tmg-4-a01', $clues['petunjuk-tmg-4-01']['item_key']);
        $this->assertStringStartsWith('Temukan kuda-kudaan dari anyaman bambu', (string) $clues['petunjuk-tmg-4-01']['text_id']);
        $this->assertStringStartsWith('Temukan daun yang diseduh', (string) $clues['petunjuk-wnb-4-04']['text_id']);
    }

    public function testBundledRecordingsMatchTheLines(): void
    {
        $codes    = array_keys((new NarrationImporter($this->conn))->lines());
        $manifest = json_decode((string) file_get_contents(FCPATH . 'assets/audio/narasi/produksi.json'), true);
        sort($codes);

        foreach (['id', 'en'] as $locale) {
            $files = array_map(static fn (string $path): string => basename($path, '.mp3'), glob(FCPATH . "assets/audio/narasi/{$locale}/*.mp3") ?: []);
            sort($files);

            $this->assertSame($codes, $files, "rekaman {$locale} = 88 naskah + 12 petunjuk");

            $listed = array_keys($manifest['files'][$locale]);
            sort($listed);
            $this->assertSame($codes, $listed, "produksi.json {$locale}");

            foreach ($manifest['files'][$locale] as $code => $entry) {
                $this->assertSame($entry['sha256'], hash_file('sha256', FCPATH . "assets/audio/narasi/{$locale}/{$code}.mp3"), "{$locale}/{$code}");
            }
        }
    }
}
