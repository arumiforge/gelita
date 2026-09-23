<?php

use App\Entities\Level;
use App\Libraries\ChartData;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Bentuk data chart admin (admin/charts.js) dibuat satu tempat untuk API dan
 * view. Test ini mengunci kontraknya: bentuk yang dipahami adapter, kondisi
 * kosong yang eksplisit, dan tidak ada sel yang pindah wilayah.
 *
 * @internal
 */
final class ChartDataTest extends CIUnitTestCase
{
    public function testLevelsWithoutCompletedAttemptsIsEmpty(): void
    {
        $chart = ChartData::levels([
            ['name' => 'Temanggung', 'avg_score' => 0, 'avg_first_pass' => 0, 'completed_attempts' => 0],
        ]);

        $this->assertSame([], $chart['series'], 'tanpa percobaan selesai → kondisi kosong, bukan batang bernilai 0');
    }

    public function testLevelsBuildsTwoSeries(): void
    {
        $chart = ChartData::levels([
            1 => ['name' => 'Temanggung', 'avg_score' => 82.14, 'avg_first_pass' => 70, 'completed_attempts' => 3],
            2 => ['name' => 'Magelang', 'avg_score' => 0, 'avg_first_pass' => 0, 'completed_attempts' => 0],
        ]);

        $this->assertSame(['Temanggung', 'Magelang'], $chart['labels']);
        $this->assertCount(2, $chart['series']);
        $this->assertSame([82.1, 0.0], $chart['series'][0]['values']);
        $this->assertSame(100, $chart['max']);
    }

    public function testNodeHeatmapPlacesCellsByLevelAndSequence(): void
    {
        $levels = [$this->level(1, 'Temanggung'), $this->level(2, 'Magelang')];
        $rows   = [
            10 => ['node_id' => 10, 'level_id' => 1, 'sequence' => 1, 'title' => 'Puzzle', 'attempts' => 4, 'difficulty_index' => 61.26],
            11 => ['node_id' => 11, 'level_id' => 2, 'sequence' => 2, 'title' => 'Rumpang', 'attempts' => 0, 'difficulty_index' => 0],
        ];

        $chart = ChartData::nodeHeatmap($rows, $levels);

        $this->assertSame(['Node 1', 'Node 2'], $chart['x']);
        $this->assertSame(['Temanggung', 'Magelang'], $chart['y']);
        $this->assertSame([[0, 0, 61.3], [1, 1, null]], $chart['values'], 'node tanpa percobaan bernilai null, bukan 0 (= "mudah")');
        $this->assertStringEndsWith('admin/analitik/node/10', $chart['links']->{'0,0'});
    }

    public function testNodeHeatmapWithoutAttemptsIsEmpty(): void
    {
        $chart = ChartData::nodeHeatmap([
            ['node_id' => 1, 'level_id' => 1, 'sequence' => 1, 'title' => 'x', 'attempts' => 0, 'difficulty_index' => 0],
        ], [$this->level(1, 'Temanggung')]);

        $this->assertSame([], $chart['values']);
    }

    public function testIndicatorMatrixAddsOverallColumnAndEvidenceLabels(): void
    {
        $levels  = [$this->level(1, 'Temanggung')];
        $overall = ['LIT-1' => ['evidence_count' => 5, 'correct_count' => 4, 'mastery_ratio' => 0.8]];
        $perLevel = [1 => ['LIT-1' => ['evidence_count' => 5, 'correct_count' => 4, 'mastery_ratio' => 0.8]]];

        $chart = ChartData::indicatorMatrix($overall, $perLevel, $levels);

        $this->assertSame(['Temanggung', 'Keseluruhan'], $chart['x']);
        $this->assertSame(['LIT-1'], $chart['y']);
        $this->assertSame([[0, 0, 0.8], [1, 0, 0.8]], $chart['values']);
        $this->assertSame('80% · 4/5 bukti', $chart['labels'][0][2], 'jumlah bukti ikut tertulis, bukan label biner');
        $this->assertTrue($chart['ratio']);
    }

    public function testDistributionIsIntegerAxis(): void
    {
        $chart = ChartData::distribution([10 => 3, 11 => 5], 'Peserta', ' th');

        $this->assertSame(['10 th', '11 th'], $chart['labels']);
        $this->assertSame([3, 5], $chart['series'][0]['values']);
        $this->assertTrue($chart['integer']);
        $this->assertSame([], ChartData::distribution([], 'Peserta')['series']);
    }

    public function testPrePostWithoutPairsIsEmpty(): void
    {
        $empty = ChartData::prePost(['pretest' => 0.0, 'posttest' => 0.0, 'delta' => null, 'pairs' => 0, 'incompatible_pairs' => 2]);
        $this->assertSame([], $empty['series']);

        $chart = ChartData::prePost(['pretest' => 60.5, 'posttest' => 72.0, 'delta' => 11.5, 'pairs' => 4, 'incompatible_pairs' => 0]);
        $this->assertSame(['Pretest', 'Posttest'], $chart['labels']);
        $this->assertSame([60.5, 72.0], $chart['series'][0]['values']);
    }

    private function level(int $id, string $name): Level
    {
        $level = new Level();
        $level->injectRawData(['id' => $id, 'code' => strtolower($name), 'sequence' => $id, 'name_id' => $name, 'name_en' => $name]);

        return $level;
    }
}
