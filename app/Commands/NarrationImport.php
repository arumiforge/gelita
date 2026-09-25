<?php

namespace App\Commands;

use App\Libraries\NarrationImporter;
use App\Models\AuditLogModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Mengimpor rekaman narasi dari public/assets/audio/narasi/{id|en}/ ke
 * permainan: nama berkas = kode berkas baris naskah (docs/naskah-cerita.md),
 * mis. `intro-01.mp3`. Jalankan setelah `gelita:story:update`, karena
 * berkas dipetakan ke baris `dialogues` yang sudah ada.
 *
 * Rekaman baru atau yang berubah berstatus draft: dengarkan dan setujui di
 * panel (Konten → Narasi). Berkas yang sama persis dengan rekaman terpasang
 * dilewati, jadi perintah ini aman dijalankan berulang tanpa menghapus
 * persetujuan. Aturan lengkap: App\Libraries\NarrationImporter.
 */
class NarrationImport extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:narration:import';
    protected $description = 'Mengimpor rekaman narasi naskah (public/assets/audio/narasi/{id|en}/) sebagai audio draft.';
    protected $usage       = 'gelita:narration:import [--locale=id|en] [--dry-run]';
    protected $options     = [
        '--locale'  => 'Hanya satu bahasa: id atau en (bawaan: keduanya)',
        '--dry-run' => 'Tampilkan yang akan diimpor tanpa menulis apa pun',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $locales = config('Gelita')->locales;
        $locale  = $this->option('locale', $params);
        $dryRun  = array_key_exists('dry-run', $params) || CLI::getOption('dry-run') !== null;

        if ($locale !== null && ! in_array($locale, $locales, true)) {
            CLI::error('--locale harus salah satu dari: ' . implode(', ', $locales) . '.');

            return EXIT_ERROR;
        }

        if ($dryRun) {
            CLI::write('Uji coba (--dry-run): tidak ada yang ditulis.', 'yellow');
        }

        $importer = new NarrationImporter();
        $failed   = false;

        foreach ($locale === null ? $locales : [$locale] as $code) {
            try {
                $report = $importer->importFolder($code, $dryRun);
            } catch (\Throwable $e) {
                CLI::error("Impor narasi {$code} gagal: " . $e->getMessage());

                return EXIT_ERROR;
            }

            $this->print($report, $importer->folderFor($code));
            $failed = $failed || $report['failed'] !== [];

            if (! $dryRun && ($report['created'] !== [] || $report['replaced'] !== [])) {
                model(AuditLogModel::class)->record('narration_import', [
                    'target_type' => 'audio_asset',
                    'target_id'   => 'narasi.' . $code,
                    'metadata'    => [
                        'via'      => 'cli',
                        'command'  => $this->name,
                        'locale'   => $code,
                        'created'  => count($report['created']),
                        'replaced' => count($report['replaced']),
                        'unknown'  => count($report['unknown']),
                        'failed'   => count($report['failed']),
                    ],
                ]);
            }
        }

        return $failed ? EXIT_ERROR : EXIT_SUCCESS;
    }

    /**
     * Nilai opsi dalam dua bentuk: `--locale en` dan `--locale=en`. Parser
     * CLI CodeIgniter hanya mengenal bentuk pertama; bentuk kedua terbaca
     * sebagai opsi bernama `locale=en` tanpa nilai.
     */
    private function option(string $name, array $params): ?string
    {
        $value = $params[$name] ?? CLI::getOption($name);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        foreach (array_merge(array_keys($params), array_keys(CLI::getOptions())) as $key) {
            if (is_string($key) && str_starts_with($key, $name . '=')) {
                return substr($key, strlen($name) + 1);
            }
        }

        return null;
    }

    /** @param array<string, mixed> $report */
    private function print(array $report, string $folder): void
    {
        $locale = strtoupper((string) $report['locale']);

        CLI::write("Narasi {$locale} dari public/{$folder}", 'light_cyan');

        if ($report['files'] === 0) {
            CLI::write('  Folder kosong atau belum ada.');
        }

        foreach ($report['unknown'] as $row) {
            CLI::write('  Nama tidak dikenal: ' . $row['file'] . ' (' . $row['reason'] . ')'
                . ($row['suggestion'] !== null ? ' → maksudnya ' . $row['suggestion'] . '?' : ''), 'yellow');
        }

        foreach ($report['failed'] as $row) {
            CLI::write('  Gagal: ' . $row['file'] . ' — ' . $row['error'], 'red');
        }

        if ($report['kept'] !== []) {
            CLI::write('  Dilewati, rekaman dari panel lebih baru: ' . implode(', ', $report['kept']), 'yellow');
        }

        CLI::write(sprintf(
            '  %d baru, %d diganti (kembali draft), %d sama, %d nama tidak dikenal, %d gagal; %d baris naskah belum punya rekaman %s.',
            count($report['created']),
            count($report['replaced']),
            count($report['unchanged']),
            count($report['unknown']),
            count($report['failed']),
            count($report['missing']),
            $locale,
        ), $report['failed'] === [] ? 'green' : 'yellow');

        if ($report['missing'] !== [] && count($report['missing']) <= 20) {
            CLI::write('  Belum ada: ' . implode(', ', $report['missing']));
        }

        if (! $report['dry_run'] && ($report['created'] !== [] || $report['replaced'] !== [])) {
            CLI::write('  Rekaman baru berstatus draft: setujui di panel, Konten → Narasi.');
        }
    }
}
