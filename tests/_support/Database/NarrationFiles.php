<?php

namespace Tests\Support\Database;

use App\Libraries\MediaStore;
use CodeIgniter\Database\BaseConnection;

/**
 * Part 6 tests: tiny WAV recordings imported through NarrationImporter, and
 * the cleanup of their upload copies (MediaStore::uploadDir(), the test folder).
 */
trait NarrationFiles
{
    /** @var list<string> */
    private array $tempWavs = [];

    /** WAV PCM 8 kHz 8-bit mono, one second; $seed makes the content differ. */
    private function wav(int $seed): string
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'nar');
        file_put_contents($path, 'RIFF' . pack('V', 36 + 8000) . 'WAVE'
            . 'fmt ' . pack('V', 16) . pack('v', 1) . pack('v', 1) . pack('V', 8000) . pack('V', 8000) . pack('v', 1) . pack('v', 8)
            . 'data' . pack('V', 8000) . str_repeat(chr(128 + $seed % 100), 8000));
        $this->tempWavs[] = $path;

        return $path;
    }

    private function removeNarrationFiles(BaseConnection $db): void
    {
        foreach ($db->table('media_assets')->like('storage_path', MediaStore::uploadDir(), 'after')->get()->getResultArray() as $row) {
            @unlink(FCPATH . $row['storage_path']);
        }

        array_map(static fn (string $file): bool => @unlink($file), $this->tempWavs);
        $this->tempWavs = [];
    }
}
