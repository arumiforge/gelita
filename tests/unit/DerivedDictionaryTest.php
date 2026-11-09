<?php

use App\Libraries\DerivedSheets;
use App\Libraries\ExportLabels;
use App\Libraries\ResearchDictionary;
use App\Services\RawDataService;
use App\Services\StaffScope;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §8, §10.2; 16.1 DerivedDictionaryTest): every derived column
 * has a ResearchDictionary entry and an ExportLabels entry, marked `usulan`
 * while `kamus-1.1` was a draft and `baru` since it was approved (9 November 2026); `after_withdrawal` sits on the seven raw sheets.
 *
 * @internal
 */
final class DerivedDictionaryTest extends CIUnitTestCase
{
    public function testEveryDerivedColumnHasAnEntryAndALabel(): void
    {
        $raw = new RawDataService();

        foreach (array_keys(DerivedSheets::TABLES) as $sheet) {
            $this->assertContains($sheet, RawDataService::DIMENSIONS);
            $this->assertSame(array_keys(DerivedSheets::COLUMNS[$sheet]), $raw->columns($sheet, StaffScope::research()));

            foreach (array_keys(DerivedSheets::COLUMNS[$sheet]) as $column) {
                $entry = ResearchDictionary::entry($sheet, $column);
                $this->assertNotNull($entry, "{$sheet}.{$column}");
                $this->assertSame(ResearchDictionary::DRAFT ? 'usulan' : 'baru', $entry['status'], "{$sheet}.{$column} (35 K8)");
                $this->assertTrue(ExportLabels::has($sheet, $column), "{$sheet}.{$column}");
                $this->assertNotSame('', ExportLabels::column($sheet, $column)['meaning']);
            }
        }
    }

    public function testVersionIsTheApprovedKamus11(): void
    {
        // The developer approved kamus-1.1 on 9 November 2026 (04).
        $this->assertSame('kamus-1.1', ResearchDictionary::VERSION);
        $this->assertSame(ResearchDictionary::BASE, ResearchDictionary::VERSION);
        $this->assertFalse(ResearchDictionary::DRAFT);
    }

    public function testAfterWithdrawalOnTheRawSheets(): void
    {
        $raw = new RawDataService();

        foreach (['pengerjaan', 'jawaban', 'percobaan', 'navigasi', 'bantuan', 'selesai', 'event'] as $sheet) {
            $this->assertContains('after_withdrawal', $raw->columns($sheet, StaffScope::research()), $sheet);
            $this->assertNotNull(ResearchDictionary::entry($sheet, 'after_withdrawal'), $sheet);
        }

        $this->assertNotContains('after_withdrawal', $raw->columns('peserta', StaffScope::research()));
    }
}
