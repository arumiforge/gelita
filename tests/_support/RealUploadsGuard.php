<?php

namespace Tests\Support;

/**
 * Proves a test left the real upload folder (public/assets/uploads/) alone.
 * Tests store media in their own folder (Config\Gelita::$uploadDir in
 * testing), but under the same official names a real install uses, so the
 * guard places files with those names in the real folder first (only where
 * none exists yet) and checks afterwards that every file there, those and
 * any real uploads of a developer machine, is still unchanged.
 *
 * Call guardRealUploads() in setUp() and assertRealUploadsUntouched() at the
 * end of tearDown(), after the test's own cleanup.
 */
trait RealUploadsGuard
{
    /** Folder of real uploads, relative to FCPATH (production default). */
    private static string $realUploadDir = 'assets/uploads/';

    /** @var array<string, string> file name => inode, size, and modification time */
    private array $realUploadsBefore = [];

    /** @var list<string> files the guard placed and removes again */
    private array $realUploadsPlaced = [];

    /** @param list<string> $names file names the test stores in its own folder */
    protected function guardRealUploads(array $names): void
    {
        $dir = FCPATH . self::$realUploadDir;

        foreach ($names as $name) {
            if (! is_file($dir . $name) && file_put_contents($dir . $name, 'real upload: ' . $name) !== false) {
                $this->realUploadsPlaced[] = $dir . $name;
            }
        }

        $this->realUploadsBefore = $this->realUploads();
    }

    protected function assertRealUploadsUntouched(): void
    {
        $after = $this->realUploads();

        foreach ($this->realUploadsPlaced as $file) {
            @unlink($file);
        }

        $this->realUploadsPlaced = [];

        $this->assertSame($this->realUploadsBefore, $after, 'the test changed files in public/' . self::$realUploadDir);
    }

    /** @return array<string, string> */
    private function realUploads(): array
    {
        $out = [];

        foreach (glob(FCPATH . self::$realUploadDir . '*') ?: [] as $file) {
            clearstatcache(true, $file);
            $out[basename($file)] = is_file($file) ? fileinode($file) . ':' . filesize($file) . '@' . filemtime($file) : 'dir';
        }

        ksort($out);

        return $out;
    }
}
