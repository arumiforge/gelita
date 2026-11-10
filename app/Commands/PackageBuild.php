<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Services\AssetPackageBuilder;
use App\Services\ReleaseActivation;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * `php spark gelita:paket:bangun` — build a new asset package from everything
 * that is active now (docs/rencana-paket-aset.md §6.1 step 2, §6.3).
 *
 * Runs the same batch steps as the admin panel in one go and prints the
 * summary: files and size per group, files new compared with the release's
 * current package, and warnings. Building changes nothing for students; a
 * release whose asset_version is the new package code does.
 *
 * `--deploy` (deploy.sh / deploy.ps1; plan §6.3, owner 9 November 2026):
 * does nothing while package mode is off (the active release names no
 * package). While it is on, builds a package and activates it through
 * ReleaseActivation (audit `release_activate`, `via: deploy`), unless a
 * running study would need a confirmation and a reason: then the package
 * is only built and an admin publishes it on Rilis → Paket aset. A failed
 * build never fails the deploy; the deploy scripts only print the warning.
 */
class PackageBuild extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:paket:bangun';
    protected $description = 'Membangun paket aset baru (gambar, suara, dan kode game) dari semua aset yang aktif.';
    protected $usage       = 'gelita:paket:bangun [--catatan "teks"] [--versi-aplikasi 1ba4502] [--tanpa-pustaka] [--deploy]';
    protected $options     = [
        '--catatan'        => 'Catatan untuk paket ini',
        '--versi-aplikasi' => 'Versi kode untuk folder assets/k/ (bawaan: app_version rilis aktif)',
        '--tanpa-pustaka'  => 'Jangan menyalin gambar Pustaka dari Wikimedia Commons',
        '--deploy'         => 'Untuk skrip deploy: hanya bila mode paket menyala; paket baru langsung diterbitkan, kecuali ada penelitian berjalan',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $deploy = $this->flag('deploy', $params);

        if ($deploy && service('assetPackages')->releasePackageCode() === null) {
            CLI::write('Mode paket mati: paket tidak dibangun.');

            return EXIT_SUCCESS;
        }

        $options = [
            'notes'       => $this->option('catatan', $params) ?? ($deploy ? 'Dibangun otomatis saat deploy' : null),
            'app_version' => $this->option('versi-aplikasi', $params),
        ];

        if ($this->flag('tanpa-pustaka', $params)) {
            $options['copy_library'] = false;
        }

        try {
            $summary = (new AssetPackageBuilder())->build($options, static function (array $progress): void {
                CLI::showProgress($progress['done'], max(1, $progress['total']));
            });
        } catch (\Throwable $e) {
            CLI::showProgress(false);
            CLI::error('Paket gagal dibangun: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        CLI::showProgress(false);
        self::printSummary($summary);

        return $deploy ? $this->publish((string) $summary['package_code']) : EXIT_SUCCESS;
    }

    /** Activate a release with the new package, unless a running study needs a reason. */
    private function publish(string $code): int
    {
        $activation = new ReleaseActivation();
        $lock       = $activation->lockFor(['asset_version' => $code]);

        if ($lock !== null) {
            CLI::write(sprintf(
                'Penelitian "%s" sedang berjalan: paket %s belum diterbitkan. Terbitkan di Rilis → Paket aset dengan alasan, atau biarkan siswa tetap memakai paket %s.',
                $lock['study']['name'],
                $code,
                $lock['package_from'] ?? '(tanpa paket)',
            ), 'yellow');

            return EXIT_SUCCESS;
        }

        try {
            $release = $activation->createForPackage($code);
            $result  = $activation->activate((int) $release['id'], ['via' => 'deploy']);
        } catch (\Throwable $e) {
            CLI::error('Paket ' . $code . ' gagal diterbitkan: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        if ($result['status'] !== ReleaseActivation::ACTIVATED) {
            CLI::error('Paket ' . $code . ' belum diterbitkan.');

            return EXIT_ERROR;
        }

        CLI::write('Paket ' . $code . ' diterbitkan (rilis ' . $release['release_code'] . '). Perangkat mengunduh berkas yang berubah sebelum Mulai berikutnya.', 'green');

        return EXIT_SUCCESS;
    }

    /** @param array<string, mixed> $summary AssetPackageBuilder::finish() */
    public static function printSummary(array $summary): void
    {
        CLI::write(sprintf('Paket %s selesai: %d berkas, %s.', $summary['package_code'], $summary['file_count'], self::size((int) $summary['total_bytes'])), 'green');
        CLI::write('Manifest: public/' . $summary['manifest_url'] . ' (sha256 ' . substr((string) $summary['manifest_sha256'], 0, 16) . '…)');

        $rows = [];

        foreach ($summary['groups'] as $group => $bytes) {
            $rows[] = [$group, self::size((int) $bytes)];
        }

        CLI::table($rows, ['Bagian', 'Ukuran']);

        CLI::write($summary['compared_with'] === null
            ? 'Belum ada paket di rilis aktif; perangkat mengunduh seluruh paket.'
            : sprintf('Dibanding paket %s: %d berkas baru/berubah (%s untuk perangkat yang sudah punya paket lama).', $summary['compared_with'], $summary['new_files'], self::size((int) $summary['new_bytes'])));

        if ($summary['warnings'] !== []) {
            $counts = array_count_values(array_column($summary['warnings'], 'code'));
            CLI::write(count($summary['warnings']) . ' peringatan:', 'yellow');

            foreach ($counts as $code => $n) {
                CLI::write(sprintf('  - %s: %d', self::warningLabel((string) $code), $n), 'yellow');
            }

            foreach ($summary['warnings'] as $warning) {
                if (! in_array($warning['code'], ['placeholder', 'narration_not_approved'], true)) {
                    CLI::write('    ' . $warning['key'] . ($warning['detail'] ? ' — ' . $warning['detail'] : ''), 'yellow');
                }
            }
        }

        CLI::write('Siswa belum melihat paket ini. Pakai kode ' . $summary['package_code'] . ' sebagai "Versi gambar & suara" di rilis baru, lalu aktifkan rilisnya.');
    }

    public static function warningLabel(string $code): string
    {
        return [
            'placeholder'            => 'gambar contoh yang belum diganti',
            'narration_not_approved' => 'narasi belum disetujui (tidak ikut paket)',
            'over_budget'            => 'berkas melebihi anggaran ukuran',
            'file_missing'           => 'berkas tidak ditemukan (tidak ikut paket)',
            'hash_mismatch'          => 'isi berkas berbeda dengan catatan media',
            'library_copy_failed'    => 'gambar Pustaka gagal disalin dari Commons (tetap tautan luar)',
            'minify_failed'          => 'kode ringkas gagal dibuat (paket memakai kode asli)',
        ][$code] ?? $code;
    }

    public static function size(int $bytes): string
    {
        return $bytes >= 1048576
            ? str_replace('.', ',', sprintf('%.1f MB', $bytes / 1048576))
            : str_replace('.', ',', sprintf('%.1f KB', $bytes / 1024));
    }
}
