<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * deploy/linux/backup.sh with an unplugged second-location drive (30 §7.5
 * step 4; manual check 19.3 no. 3, 14 November 2026).
 *
 * An unplugged drive leaves its mount point as an empty folder. rsync then
 * created `$KEDUA` on the server's own disk and reported success, so
 * `--ganti` deleted every older backup at the first location while the drive
 * still held them, and the admin ticked "Cadangan diganti". The script must
 * stop with code 2 and delete nothing when a local `$KEDUA` does not exist.
 *
 * Runs a copy of the real script whose only change is the ops folder, with a
 * stub `mysqldump` on PATH; skipped where bash, rsync, gzip, or tar is missing.
 *
 * @internal
 */
final class BackupSecondLocationTest extends CIUnitTestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['bash', 'rsync', 'gzip', 'tar'] as $tool) {
            if (DIRECTORY_SEPARATOR === '\\' || trim((string) shell_exec('command -v ' . $tool . ' 2>/dev/null')) === '') {
                $this->markTestSkipped("{$tool} is not available");
            }
        }

        $this->root = sys_get_temp_dir() . '/gelita-backup-test-' . bin2hex(random_bytes(4));

        foreach (['ops', 'bin', 'app/public/assets/uploads', 'app/writable', 'first', 'mnt'] as $dir) {
            mkdir($this->root . '/' . $dir, 0700, true);
        }

        file_put_contents($this->root . '/app/.env', "CI_ENVIRONMENT = production\n");
        file_put_contents($this->root . '/app/public/assets/uploads/a.txt', 'upload');
        file_put_contents($this->root . '/bin/mysqldump', "#!/bin/sh\necho '-- stub dump'\n");
        chmod($this->root . '/bin/mysqldump', 0700);
        file_put_contents($this->root . '/ops/ops.conf', implode("\n", [
            'APP=' . $this->root . '/app',
            'DIR=' . $this->root . '/first',
            // the drive's mount point exists, the drive (and its gelita folder) does not
            'KEDUA=' . $this->root . '/mnt/gelita',
            'KEDUA_HARI=30',
        ]) . "\n");

        $source = (string) file_get_contents(ROOTPATH . 'deploy/linux/backup.sh');
        $script = preg_replace('/^OPS=\S+/m', 'OPS=' . $this->root . '/ops', $source, 1, $count);
        $this->assertSame(1, $count, 'backup.sh sets OPS on one line');
        file_put_contents($this->root . '/backup.sh', $script);

        // An older backup at the first location, which --ganti would replace
        file_put_contents($this->root . '/first/gelita-db-20200101-0100.sql.gz', 'old');
    }

    protected function tearDown(): void
    {
        if (isset($this->root) && is_dir($this->root)) {
            $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($items as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }

            rmdir($this->root);
        }

        parent::tearDown();
    }

    public function testUnpluggedDriveStopsWithCodeTwoAndDeletesNothing(): void
    {
        [$code, $output] = $this->runBackup('--ganti');

        $this->assertSame(2, $code, $output);
        $this->assertStringContainsString('GAGAL', $output);
        $this->assertFileExists($this->root . '/first/gelita-db-20200101-0100.sql.gz', 'nothing deleted at the first location');
        $this->assertDirectoryDoesNotExist($this->root . '/mnt/gelita', 'no folder made on the empty mount point');
    }

    public function testPluggedDriveReplacesOlderBackupsAtBothLocations(): void
    {
        mkdir($this->root . '/mnt/gelita', 0700);
        file_put_contents($this->root . '/mnt/gelita/gelita-db-20200101-0100.sql.gz', 'old');

        [$code, $output] = $this->runBackup('--ganti');

        $this->assertSame(0, $code, $output);
        $this->assertFileDoesNotExist($this->root . '/first/gelita-db-20200101-0100.sql.gz');
        $this->assertFileDoesNotExist($this->root . '/mnt/gelita/gelita-db-20200101-0100.sql.gz');
        $this->assertNotEmpty(glob($this->root . '/mnt/gelita/gelita-db-*.sql.gz'), 'the fresh backup reached the second location');
        $this->assertSame('30', json_decode((string) file_get_contents($this->root . '/app/writable/backup-status.json'), true)['kedua_hari']);
    }

    /** @return array{int, string} exit code and combined output */
    private function runBackup(string $args): array
    {
        $command = 'PATH=' . escapeshellarg($this->root . '/bin') . ':"$PATH" bash ' . escapeshellarg($this->root . '/backup.sh') . ' ' . $args . ' 2>&1';
        exec($command, $lines, $code);

        return [$code, implode("\n", $lines)];
    }
}
