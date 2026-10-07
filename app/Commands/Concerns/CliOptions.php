<?php

namespace App\Commands\Concerns;

use CodeIgniter\CLI\CLI;

/**
 * Pembacaan opsi command GELITA.
 *
 * Parser CLI CodeIgniter hanya mengenal `--opsi nilai`. Bentuk `--opsi=nilai`
 * terbaca sebagai opsi bernama `opsi=nilai` tanpa nilai, sehingga nilainya
 * diam-diam hilang (mis. `gelita:reset --bank=x.xlsx` memakai workbook
 * bawaan). option() menerima kedua bentuk.
 */
trait CliOptions
{
    /** Opsi tanpa nilai: `--dry-run` terbaca sebagai `$params['dry-run'] = null`. */
    protected function flag(string $name, array $params): bool
    {
        return array_key_exists($name, $params) || CLI::getOption($name) !== null;
    }

    /** Nilai opsi `--nama nilai` atau `--nama=nilai`; null bila tidak ada atau kosong. */
    protected function option(string $name, array $params): ?string
    {
        $value = $params[$name] ?? CLI::getOption($name);

        if (! is_string($value) || trim($value) === '') {
            $value = null;

            foreach (array_merge(array_keys($params), array_keys(CLI::getOptions())) as $key) {
                if (is_string($key) && str_starts_with($key, $name . '=')) {
                    $value = substr($key, strlen($name) + 1);
                }
            }
        }

        return $value !== null && trim($value) !== '' ? trim($value) : null;
    }
}
