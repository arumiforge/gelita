<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\GelitaGuideContent;
use App\Libraries\GuideContent;
use App\Libraries\GuideSourceCheck;
use App\Libraries\ValidatorGuideContent;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Checks the hand-written guides against the document sections they were
 * written from (owner, 17 November 2026; GuideSourceCheck): the validator
 * guide (/admin/panduan-validator, sources in docs/revisi-10-analitik/) and
 * the GELITA guide (/admin/panduan-gelita, sources anywhere under docs/).
 *
 *   php spark gelita:panduan:cek                                  # which pages of both guides may be behind
 *   php spark gelita:panduan:cek --panduan=gelita                 # one guide only (validator, gelita)
 *   php spark gelita:panduan:cek --panduan=validator --tandai     # after checking that guide: record its sources
 *
 * --tandai needs --panduan, so checking one guide never marks the other's
 * unread pages as checked (coordinator review, 17 November 2026).
 *
 * Changes no database. --tandai only rewrites the lock files in
 * app/Libraries/GuideSources/, which are committed.
 */
class GuideCheck extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:panduan:cek';
    protected $description = 'Memeriksa apakah halaman panduan validator dan panduan Mengenal GELITA perlu disesuaikan dengan dokumen sumbernya.';
    protected $usage       = 'gelita:panduan:cek [--panduan=validator|gelita] [--tandai]';
    protected $options     = [
        '--panduan' => 'Hanya satu panduan: validator atau gelita (bawaan: keduanya; wajib bersama --tandai).',
        '--tandai'  => 'Setelah teks panduan itu diperiksa: catat sumber yang sekarang sebagai sudah sesuai.',
    ];

    /** Guide key => [content class, its source check, its lock file]. */
    public const GUIDES = [
        'validator' => [ValidatorGuideContent::class, 'forValidatorGuide', GuideSourceCheck::VALIDATOR_LOCK],
        'gelita'    => [GelitaGuideContent::class, 'forGelitaGuide', GuideSourceCheck::GELITA_LOCK],
    ];

    public function run(array $params)
    {
        $only = (string) ($this->option('panduan', $params) ?? '');

        if ($only !== '' && ! isset(self::GUIDES[$only])) {
            CLI::error('Panduan tidak dikenal: ' . $only . '. Pilih validator atau gelita.');

            return EXIT_ERROR;
        }

        $mark = $this->flag('tandai', $params);

        if ($mark && $only === '') {
            CLI::error('--tandai hanya untuk satu panduan yang sudah diperiksa: tambahkan --panduan=validator atau --panduan=gelita.');

            return EXIT_ERROR;
        }

        $exit = EXIT_SUCCESS;

        foreach (self::GUIDES as $key => [$guide, $factory, $lock]) {
            if ($only !== '' && $only !== $key) {
                continue;
            }

            if ($this->checkGuide($key, $guide, GuideSourceCheck::$factory(), $lock, $mark) !== EXIT_SUCCESS) {
                $exit = EXIT_ERROR;
            }
        }

        return $exit;
    }

    /**
     * @param class-string<GuideContent> $guide
     */
    private function checkGuide(string $key, string $guide, GuideSourceCheck $check, string $lock, bool $mark): int
    {
        $refs  = $guide::sources();
        $label = $key === 'gelita' ? 'Panduan Mengenal GELITA' : 'Panduan validator';
        $class = substr((string) strrchr($guide, '\\'), 1);

        if (! $check->docsPresent()) {
            CLI::write($label . ': dokumen sumber tidak ada di instalasi ini; pemeriksaan dilewati.', 'yellow');

            return EXIT_SUCCESS;
        }

        if ($mark) {
            $missing = $check->mark($refs);

            if ($missing !== []) {
                CLI::error($label . ': bagian sumber ini tidak ditemukan; perbaiki rujukan `sources` di ' . $class . ' dulu:');

                foreach ($missing as $ref) {
                    CLI::write('  ' . $check->docsDir() . $ref);
                }

                return EXIT_ERROR;
            }

            CLI::write($label . ': ' . count($refs) . ' sumber dicatat sebagai sesuai di ' . $lock . '. Commit berkas itu bersama perubahan panduan.', 'green');

            return EXIT_SUCCESS;
        }

        $problems = $check->problems($refs);

        if ($problems === []) {
            CLI::write($label . ' sesuai dengan ' . count($refs) . ' bagian sumbernya.', 'green');

            return EXIT_SUCCESS;
        }

        CLI::write($label . ': halaman yang perlu diperiksa:', 'yellow');

        foreach (self::pagesFor($problems, $guide) as $page => $sources) {
            CLI::write('  /' . $guide::BASE . '/' . $page);

            foreach ($sources as $ref => $state) {
                CLI::write('      ' . $state . ': ' . $check->docsDir() . $ref);
            }
        }

        CLI::newLine();
        CLI::write('Sesuaikan teksnya di app/Libraries/' . $class . '.php (atau pastikan memang tidak perlu berubah), lalu jalankan php spark gelita:panduan:cek --panduan=' . $key . ' --tandai.');

        return EXIT_ERROR;
    }

    /**
     * Guide pages per changed source.
     *
     * @param array<string, string>      $problems ref => state
     * @param class-string<GuideContent> $guide
     *
     * @return array<string, array<string, string>> page id => [ref => state]
     */
    public static function pagesFor(array $problems, string $guide = ValidatorGuideContent::class): array
    {
        $out = [];

        foreach ($guide::chapters() as $chapter) {
            foreach ($chapter['sections'] as $section) {
                foreach ($section['sources'] ?? [] as $ref) {
                    if (isset($problems[$ref])) {
                        $out[$section['id']][$ref] = $problems[$ref];
                    }
                }
            }
        }

        return $out;
    }
}
