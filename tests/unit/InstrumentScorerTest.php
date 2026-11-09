<?php

use App\Libraries\InstrumentImporter;
use App\Services\InstrumentScorer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Raw scoring from the key, for every item of the data file (research
 * revision 10, part 8: 34 §7.6, §15.1 `InstrumentScorerTest`; 13 →
 * Penskoran: stands in for the 10% second typing).
 *
 * @internal
 */
final class InstrumentScorerTest extends CIUnitTestCase
{
    /** @return list<array<string, mixed>> scored items as InstrumentRepository decodes them */
    private function items(): array
    {
        $data = require InstrumentImporter::defaultDir() . '/tes.php';
        $out  = [];

        foreach ($data['items'] as $code => $item) {
            if ($item['part'] !== 'ct') {
                $out[] = ['code' => $code] + $item;
            }
        }

        return $out;
    }

    public function testEveryItemScoresItsKeyAndEveryWrongChoice(): void
    {
        $this->assertCount(64, $this->items());

        foreach ($this->items() as $item) {
            $key = $item['key'];

            switch ($item['type']) {
                case 'pg':
                case 'd2':
                    foreach (array_keys($item['show']['options']) as $letter) {
                        $result = InstrumentScorer::score($item, (string) $letter);
                        $right  = $letter === $key['answer'];
                        $score  = $item['type'] === 'd2' ? $key['scores'][$letter] : ($right ? 1 : 0);
                        $this->assertSame(['score' => $score, 'error_codes' => $key['errors'][$letter] ?? null], $result, $item['code'] . ' ' . $letter);
                    }

                    $this->assertSame($item['type'] === 'd2' ? 2 : 1, InstrumentScorer::score($item, $key['answer'])['score'], $item['code']);
                    break;

                case 'pgk':
                    $this->assertSame(['score' => 1, 'error_codes' => null], InstrumentScorer::score($item, $key['answer']), $item['code']);
                    $letters = $item['show']['scale'] === 'fp' ? ['F', 'P'] : ['B', 'S'];
                    $flip    = static fn (string $c): string => $c === $letters[0] ? $letters[1] : $letters[0];

                    // One wrong statement at a time: its own code
                    for ($i = 0; $i < 3; $i++) {
                        $response     = $key['answer'];
                        $response[$i] = $flip($response[$i]);
                        $this->assertSame(['score' => 0, 'error_codes' => $key['errors'][(string) ($i + 1)]], InstrumentScorer::score($item, $response), $item['code'] . ' ' . $response);
                    }

                    // All three wrong: three codes; a statement left out gives none
                    $all = implode('', array_map($flip, str_split($key['answer'])));
                    $this->assertSame(implode(';', $key['errors']), InstrumentScorer::score($item, $all)['error_codes']);
                    $this->assertSame(['score' => 0, 'error_codes' => null], InstrumentScorer::score($item, $key['answer'][0] . '--'));
                    break;

                case 'match':
                    $this->assertSame(['score' => 1, 'error_codes' => null], InstrumentScorer::score($item, $key['answer']), $item['code']);
                    $pairs  = InstrumentScorer::pairs($key['answer']);
                    $decoy  = array_values(array_diff(['A', 'B', 'C', 'D'], $pairs))[0];

                    // The decoy on statement 1: the decoy's code; statements 1 and 2 swapped: INF-REL twice
                    $this->assertSame(['score' => 0, 'error_codes' => $key['errors'][$decoy]], InstrumentScorer::score($item, "1{$decoy} 2{$pairs['2']} 3{$pairs['3']}"), $item['code']);
                    $this->assertSame(['score' => 0, 'error_codes' => $key['errors']['other_pair'] . ';' . $key['errors']['other_pair']], InstrumentScorer::score($item, "1{$pairs['2']} 2{$pairs['1']} 3{$pairs['3']}"));
                    $this->assertSame(['score' => 0, 'error_codes' => null], InstrumentScorer::score($item, "1{$pairs['1']} 2- 3{$pairs['3']}"), 'a pair left out');
                    break;

                default:
                    $this->fail($item['code'] . ': unknown type');
            }

            $this->assertSame(['score' => 0, 'error_codes' => null], InstrumentScorer::score($item, null), $item['code'] . ' skipped');
        }
    }

    public function testRawScoreRangesPerPart(): void
    {
        $max = [];

        foreach ($this->items() as $item) {
            $best = $item['type'] === 'd2' ? 2 : 1;
            $max[$item['part'] . $item['form']] = ($max[$item['part'] . $item['form']] ?? 0) + $best;
        }

        $this->assertSame(['mbA' => 16, 'mbB' => 16, 'aeA' => 16, 'aeB' => 16, 'bdA' => 8, 'bdB' => 8], $max, '13: D1 0–16, D2 0–16, D3 0–8');
    }

    public function testExamplesAreNeverScored(): void
    {
        $data = require InstrumentImporter::defaultDir() . '/tes.php';
        $item = ['code' => 'tes-ct-01'] + $data['items']['tes-ct-01'];

        $this->assertSame(['score' => null, 'error_codes' => null], InstrumentScorer::score($item, 'D'));
    }
}
