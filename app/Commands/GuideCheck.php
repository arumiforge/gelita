<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\GuideSourceCheck;
use App\Libraries\ValidatorGuideContent;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Checks the validator guide against the research-document sections it was
 * written from (owner, 17 November 2026; GuideSourceCheck).
 *
 *   php spark gelita:panduan:cek            # which guide pages may be behind
 *   php spark gelita:panduan:cek --tandai   # after checking them: record the sources as current
 *
 * Changes no database. --tandai only rewrites the lock file
 * app/Libraries/GuideSources/validator.json, which is committed.
 */
class GuideCheck extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:panduan:cek';
    protected $description = 'Memeriksa apakah halaman panduan validator perlu disesuaikan dengan dokumen sumbernya.';
    protected $usage       = 'gelita:panduan:cek [--tandai]';
    protected $options     = [
        '--tandai' => 'Setelah teks panduan diperiksa: catat sumber yang sekarang sebagai sudah sesuai.',
    ];

    public function run(array $params)
    {
        $check = GuideSourceCheck::forValidatorGuide();
        $refs  = ValidatorGuideContent::sources();

        if (! $check->docsPresent()) {
            CLI::write('Dokumen penelitian tidak ada di instalasi ini; pemeriksaan dilewati.', 'yellow');

            return EXIT_SUCCESS;
        }

        if ($this->flag('tandai', $params)) {
            $missing = $check->mark($refs);

            if ($missing !== []) {
                CLI::error('Bagian sumber ini tidak ditemukan; perbaiki rujukan `sources` di ValidatorGuideContent dulu:');

                foreach ($missing as $ref) {
                    CLI::write('  ' . $ref);
                }

                return EXIT_ERROR;
            }

            CLI::write(count($refs) . ' sumber dicatat sebagai sesuai di ' . GuideSourceCheck::VALIDATOR_LOCK . '. Commit berkas itu bersama perubahan panduan.', 'green');

            return EXIT_SUCCESS;
        }

        $problems = $check->problems($refs);

        if ($problems === []) {
            CLI::write('Panduan validator sesuai dengan ' . count($refs) . ' bagian sumbernya.', 'green');

            return EXIT_SUCCESS;
        }

        CLI::write('Halaman panduan validator yang perlu diperiksa:', 'yellow');

        foreach (self::pagesFor($problems) as $page => $sources) {
            CLI::write('  /' . ValidatorGuideContent::BASE . '/' . $page);

            foreach ($sources as $ref => $state) {
                CLI::write('      ' . $state . ': ' . GuideSourceCheck::DOCS . $ref);
            }
        }

        CLI::newLine();
        CLI::write('Sesuaikan teksnya di app/Libraries/ValidatorGuideContent.php (atau pastikan memang tidak perlu berubah), lalu jalankan php spark gelita:panduan:cek --tandai.');

        return EXIT_ERROR;
    }

    /**
     * Guide pages per changed source.
     *
     * @param array<string, string> $problems ref => state
     *
     * @return array<string, array<string, string>> page id => [ref => state]
     */
    public static function pagesFor(array $problems): array
    {
        $out = [];

        foreach (ValidatorGuideContent::chapters() as $chapter) {
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
