<?php

use App\Services\ExportService;
use App\Services\ReportService;
use App\Services\RetentionService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tahap 7: kelima command `gelita:*` aktif (tidak lagi kerangka "belum aktif"),
 * service baru terdaftar, dan templat PDF meng-escape setiap nilai — mPDF
 * memproses HTML, jadi keluaran mentah adalah lubang injeksi.
 *
 * @internal
 */
final class Stage7WiringTest extends CIUnitTestCase
{
    private const COMMANDS = [
        'gelita:bank:import'     => 'App\Commands\BankImport',
        'gelita:content:verify'  => 'App\Commands\ContentVerify',
        'gelita:media:scan'      => 'App\Commands\MediaScan',
        'gelita:retention:run'   => 'App\Commands\RetentionRun',
        'gelita:score:recompute' => 'App\Commands\ScoreRecompute',
    ];

    public function testGelitaCommandsAreDiscoveredAndActive(): void
    {
        $commands = service('commands')->getCommands();

        foreach (self::COMMANDS as $name => $class) {
            $this->assertArrayHasKey($name, $commands, "{$name} terdaftar");
            $this->assertSame($class, $commands[$name]['class']);

            $source = (string) file_get_contents((new ReflectionClass($class))->getFileName());

            $this->assertStringNotContainsString('belum aktif', $source, "{$name} bukan lagi kerangka");
        }
    }

    public function testCommandsThatWriteTimeSyncTimezone(): void
    {
        // pre_system (tempat zona waktu DB disetel) tidak terpicu di CLI
        foreach (['BankImport', 'MediaScan', 'RetentionRun', 'ScoreRecompute'] as $command) {
            $source = (string) file_get_contents(APPPATH . "Commands/{$command}.php");

            $this->assertStringContainsString('db_sync_timezone()', $source, $command);
        }
    }

    public function testServicesAreRegistered(): void
    {
        $this->assertInstanceOf(ExportService::class, service('exportService'));
        $this->assertInstanceOf(ReportService::class, service('reportService'));
        $this->assertInstanceOf(RetentionService::class, service('retentionService'));
    }

    public function testExportControllerUsesServiceSheetList(): void
    {
        $source = (string) file_get_contents(APPPATH . 'Controllers/Admin/ExportController.php');

        $this->assertStringNotContainsString("class_exists('App\\Services\\ExportService')", $source, 'jalur "mesin belum aktif" sudah dicabut');
        $this->assertStringContainsString('ExportService::SHEETS', $source);
    }

    public function testPdfTemplatesEscapeEveryEcho(): void
    {
        foreach (glob(APPPATH . 'Views/pdf/*.php') as $view) {
            $source = (string) file_get_contents($view);

            preg_match_all('/<\?=\s*(.+?)\s*\?>/s', $source, $echoes);

            foreach ($echoes[1] as $expression) {
                $safe = str_starts_with($expression, 'esc(')
                    || str_starts_with($expression, 'view(')
                    || str_starts_with($expression, '(int)')
                    || str_starts_with($expression, 'number_format(')
                    || preg_match('/^\$(fill|alt)\b/', $expression) === 1
                    || preg_match('/^\$\w+\[\'delta\'\] === null \? \'—\' : esc\(/', $expression) === 1
                    || preg_match("/^\\\$\\w+ \\? '[^']*' : '[^']*'$/", $expression) === 1;

                $this->assertTrue($safe, basename($view) . ": keluaran tanpa esc(): <?= {$expression} ?>");
            }
        }
    }
}
