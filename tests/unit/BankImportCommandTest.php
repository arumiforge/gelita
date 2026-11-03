<?php

use App\Commands\BankImport;
use App\Services\SystemResetService;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * `php spark gelita:bank:import <berkas> --dry-run`: ringkasan workbook kelas 5
 * (variasi, butir, petunjuk, pengecoh) tanpa menulis apa pun.
 *
 * @internal
 */
final class BankImportCommandTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createGradeFiveWorld();
    }

    protected function tearDown(): void
    {
        CLI::resetInputOutput();
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testDryRunSummarisesTheGradeFiveWorkbook(): void
    {
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        $code = (new BankImport(Services::logger(), Services::commands()))->run([ROOTPATH . SystemResetService::DEFAULT_BANK, 'dry-run' => null]);

        $this->assertSame(EXIT_SUCCESS, $code, $io->getOutput());
        // Scenario rows of tmg-5 … wnb-5 are checked but set aside until part 6 installs the challenge
        $this->assertStringContainsString('Workbook: 12 node, 52 variasi, 9 bacaan, 153 butir, 36 petunjuk, 12 pengecoh', $io->getOutput());
        $this->assertStringContainsString('Bank baru: 508 kode kesalahan pilihan, 6 unggahan babak 2 dengan 60 bagian ketuk, 0 skenario dengan 0 pilihan, 5 indikator.', $io->getOutput());
        $this->assertStringContainsString('Tantangan tmg-5 (Skenario Digital) belum ada di permainan', $io->getOutput());
        $this->assertMatchesRegularExpression('/\|\s*wnb-4\s*\|\s*3\s*\|\s*20\s*\|/', $io->getOutput(), 'wnb-4: round 1 (x: 4 targets + 8 decoys) and round 2 a, b (4 clues each)');
    }
}
