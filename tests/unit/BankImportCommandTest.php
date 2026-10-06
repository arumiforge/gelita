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
        $this->assertStringContainsString('Workbook: 12 node, 30 variasi, 9 bacaan, 126 butir, 36 petunjuk, 12 pengecoh', $io->getOutput());
        $this->assertMatchesRegularExpression('/\|\s*wnb-4\s*\|\s*1\s*\|\s*12\s*\|/', $io->getOutput(), 'wnb-4: 1 variasi, 4 target + 8 pengecoh');
    }
}
