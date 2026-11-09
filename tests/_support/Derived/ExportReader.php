<?php

namespace Tests\Support\Derived;

use App\Models\DataExportModel;
use App\Services\ExportService;

/**
 * Part 9 tests: build a download for study 1 and read its CSV-ZIP sheets.
 */
trait ExportReader
{
    /** @var list<string> */
    private array $exportFiles = [];

    private function buildExport(int $staffId, array $scope = [], string $format = 'csv'): array
    {
        $scope += ['filters' => ['study_id' => 1], 'sheets' => []];
        $id     = model(DataExportModel::class)->insert(['requested_by' => $staffId, 'format' => $format, 'scope_json' => $scope, 'anonymized' => 1, 'status' => 'running'], true);
        $export = (new ExportService())->build((int) $id);

        if (! empty($export['file_path'])) {
            $this->exportFiles[] = (string) $export['file_path'];
        }

        return $export;
    }

    private function removeExports(): void
    {
        foreach ($this->exportFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /** @return array<string, list<array<string, string>>> sheet => rows */
    private function readSheets(string $path): array
    {
        $zip = new \ZipArchive();
        $zip->open($path);
        $out = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $handle = fopen('php://temp', 'w+b');
            fwrite($handle, substr((string) $zip->getFromIndex($i), 3));
            rewind($handle);
            $header = fgetcsv($handle, null, ',', '"', '');
            $rows   = [];

            while (($line = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $rows[] = array_combine($header, $line);
            }

            fclose($handle);
            $out[basename((string) $zip->getNameIndex($i), '.csv')] = $rows;
        }

        $zip->close();

        return $out;
    }
}
