<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\NarrationCatalog;
use App\Libraries\NarrationImporter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Writes the recording list of NarrationCatalog (one row per script line:
 * file code, speaker, text in both languages) as a CSV, for work done outside
 * the panel (ai-studio/elevenlabs-voice/). With --dir, two more columns say
 * whether FOLDER/{id|en}/{file code}.{mp3|m4a|ogg|wav} already exists, so the
 * same file is both the to-do list and the done list.
 */
class NarrationList extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:narration:list';
    protected $description = 'Menulis daftar rekaman narasi (kode berkas, tokoh, teks) sebagai CSV.';
    protected $usage       = 'gelita:narration:list --csv=BERKAS [--dir=FOLDER]';
    protected $options     = [
        '--csv' => 'Berkas CSV yang ditulis',
        '--dir' => 'Folder rekaman (FOLDER/id, FOLDER/en): menambah kolom berkas_id dan berkas_en (ada/belum)',
    ];

    public function run(array $params)
    {
        $csv = $this->option('csv', $params);
        $dir = $this->option('dir', $params);

        if ($csv === null) {
            CLI::error('--csv wajib diisi, mis. --csv=ai-studio/elevenlabs-voice/lines.csv');

            return EXIT_ERROR;
        }

        // spark runs from public/, so a relative path is taken from the repository root.
        $absolute = static fn (string $path): string => preg_match('#^([A-Za-z]:)?[/\\\\]#', $path) === 1 ? $path : ROOTPATH . $path;
        $csv      = $absolute($csv);
        $dir      = $dir === null ? null : $absolute($dir);
        $handle   = @fopen($csv, 'wb');

        if ($handle === false) {
            CLI::error('Tidak dapat menulis ' . $csv . '.');

            return EXIT_ERROR;
        }

        $locales = config('Gelita')->locales;
        $headers = ['kode_berkas', 'konteks', 'wilayah', 'urutan', 'tokoh', 'teks_id', 'teks_en'];
        $found   = array_fill_keys($locales, 0);
        $rows    = (new NarrationCatalog())->recordingList()['rows'];

        fputcsv($handle, $dir === null ? $headers : array_merge($headers, array_map(static fn (string $locale): string => 'berkas_' . $locale, $locales)));

        foreach ($rows as $row) {
            // recordingList(): 0 code, 1 context, 2 region, 3 sequence, 4 speaker, 9 text_id, 10 text_en
            $line = [$row[0], $row[1], $row[2], $row[3], $row[4], $row[9], $row[10]];

            foreach ($dir === null ? [] : $locales as $locale) {
                $exists = false;

                foreach (NarrationImporter::EXTENSIONS as $extension) {
                    $exists = $exists || is_file(rtrim($dir, '/\\') . '/' . $locale . '/' . $row[0] . '.' . $extension);
                }

                $found[$locale] += (int) $exists;
                $line[] = $exists ? 'ada' : 'belum';
            }

            fputcsv($handle, $line);
        }

        fclose($handle);
        CLI::write(count($rows) . ' baris ditulis ke ' . $csv . '.', 'green');

        foreach ($dir === null ? [] : $locales as $locale) {
            CLI::write('  ' . strtoupper($locale) . ': ' . $found[$locale] . ' ada, ' . (count($rows) - $found[$locale]) . ' belum.');
        }

        return EXIT_SUCCESS;
    }
}
