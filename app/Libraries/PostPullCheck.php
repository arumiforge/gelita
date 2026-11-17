<?php

namespace App\Libraries;

use CodeIgniter\Database\MigrationRunner;
use Throwable;

/**
 * Which commands a developer install needs after `git pull` (owner,
 * 17 November 2026), for `php spark gelita:setelah-pull`.
 *
 * Two kinds of step:
 * - State steps compare the install itself with the code: Composer packages
 *   against composer.lock, migration files against the migrations table,
 *   the minified JS/CSS mirror against its sources, the validator guide
 *   against its source documents.
 * - Import steps (LEDGER_STEPS) compare a fingerprint of their input files
 *   with the one DevStepLedger recorded when the step last ran here. A step
 *   never recorded is `unknown`: it may be current, so it is reported but
 *   not run; `--tandai` records the current inputs as done.
 *
 * Order matters and is the order of steps(): packages, database structure,
 * content imports (narration install before narration import), the asset
 * mirror, then cache:clear and gelita:content:verify whenever a content
 * step is due. Nothing here changes anything; the command runs the steps.
 */
final class PostPullCheck
{
    public const DUE     = 'perlu';
    public const OK      = 'sesuai';
    public const UNKNOWN = 'belum-tercatat';
    public const SKIPPED = 'dilewati';
    public const MANUAL  = 'periksa-sendiri';

    /** Default question bank workbook (also gelita:reset's default). */
    public const BANK = 'docs/bank-soal/gelita-bank-soal-kelas5.xlsx';

    /**
     * Import steps tracked by the ledger: inputs (paths relative to the
     * application root, extensions to keep, sub-folders to leave out), the
     * command to run, and steps that must run again after this one.
     *
     * @var array<string, array{title: string, inputs: array{paths: list<string>, ext?: list<string>, skip?: list<string>}, command: string, then?: list<string>}>
     */
    public const LEDGER_STEPS = [
        'sekolah' => [
            'title'   => 'Daftar sekolah resmi (NPSN)',
            'inputs'  => ['paths' => ['app/Database/Seeds/data/sekolah-jateng.csv']],
            'command' => 'gelita:schools:import',
        ],
        'instrumen' => [
            'title'   => 'Isi tes, angket, dan skala sikap',
            'inputs'  => ['paths' => ['app/Database/Seeds/data/instrumen'], 'ext' => ['php']],
            'command' => 'gelita:instrumen:impor',
        ],
        'bank' => [
            'title'   => 'Bank soal kelas 5',
            'inputs'  => ['paths' => [self::BANK]],
            'command' => 'gelita:bank:import ' . self::BANK,
        ],
        'cerita' => [
            'title'   => 'Naskah cerita dan dialog',
            'inputs'  => ['paths' => ['app/Database/Seeds/data/story.php']],
            'command' => 'gelita:story:sync',
        ],
        'katalog' => [
            'title'   => 'Gambar Katalog Aset',
            'inputs'  => ['paths' => ['ai-studio/game-images'], 'ext' => ['webp', 'png', 'jpg', 'jpeg', 'svg', 'json'], 'skip' => ['review']],
            'command' => 'gelita:katalog:impor',
        ],
        'narasi-pasang' => [
            'title'   => 'Rekaman narasi cadangan (suara sintetis)',
            'inputs'  => ['paths' => ['ai-studio/elevenlabs-voice/out'], 'ext' => ['mp3']],
            'command' => 'gelita:narration:install',
            'then'    => ['narasi-impor'],
        ],
        'narasi-impor' => [
            'title'   => 'Rekaman narasi ke database',
            'inputs'  => ['paths' => ['public/assets/audio/narasi'], 'ext' => ['mp3', 'json']],
            'command' => 'gelita:narration:import',
        ],
        'suara' => [
            'title'   => 'Musik dan efek suara',
            'inputs'  => ['paths' => ['ai-studio/elevenlabs-sound/out'], 'ext' => ['mp3']],
            'command' => 'gelita:sound:install',
        ],
    ];

    public function __construct(
        private readonly DevStepLedger $ledger,
        private readonly string $root = ROOTPATH,
    ) {
    }

    public static function forInstall(): self
    {
        return new self(DevStepLedger::forInstall());
    }

    /**
     * Every step with its state, in run order.
     *
     * @return list<array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool}>
     */
    public function steps(): array
    {
        $steps   = [];
        $steps[] = $this->composer();
        $steps[] = $this->migrations();

        $forced = [];

        foreach (self::LEDGER_STEPS as $id => $step) {
            $row = $this->ledgerStep($id, $step);

            if (in_array($id, $forced, true) && $row['state'] !== self::DUE) {
                $row['state']  = self::DUE;
                $row['reason'] = 'Langkah sebelumnya mengubah berkasnya.';
            }

            if ($row['state'] === self::DUE) {
                array_push($forced, ...($step['then'] ?? []));
            }

            $steps[] = $row;
        }

        $steps[] = $this->minified();

        $contentDue = array_filter($steps, static fn (array $s): bool => $s['state'] === self::DUE && in_array($s['id'], ['migrasi', ...array_keys(self::LEDGER_STEPS)], true));

        foreach ([['cache', 'Bersihkan cache', 'cache:clear'], ['verifikasi', 'Periksa konten', 'gelita:content:verify']] as [$id, $title, $command]) {
            $steps[] = [
                'id'       => $id,
                'title'    => $title,
                'state'    => $contentDue !== [] ? self::DUE : self::OK,
                'reason'   => $contentDue !== [] ? 'Setelah konten atau struktur database berubah.' : 'Tidak ada langkah konten yang perlu.',
                'command'  => $command,
                'runnable' => true,
            ];
        }

        $steps[] = $this->guide();

        return $steps;
    }

    /** Records the current inputs of every import step as done, without running anything. */
    public function markAll(): void
    {
        foreach (array_keys(self::LEDGER_STEPS) as $id) {
            $this->ledger->record($id);
        }
    }

    /** @return array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool} */
    private function composer(): array
    {
        $row = ['id' => 'composer', 'title' => 'Paket Composer', 'command' => 'composer install', 'runnable' => false];
        $lock = $this->root . 'composer.lock';

        if (! is_file($lock)) {
            return $row + ['state' => self::SKIPPED, 'reason' => 'Tidak ada composer.lock.'];
        }

        $installed = $this->root . 'vendor/composer/installed.json';

        if (! is_file($installed)) {
            return $row + ['state' => self::DUE, 'reason' => 'Folder vendor/ belum terpasang.'];
        }

        $wanted = self::packages(json_decode((string) file_get_contents($lock), true) ?? [], ['packages', 'packages-dev']);
        $have   = self::packages(json_decode((string) file_get_contents($installed), true) ?? [], ['packages']);
        $diff   = [];

        foreach ($wanted as $name => $version) {
            if (($have[$name] ?? null) !== $version) {
                $diff[] = $name;
            }
        }

        return $row + ($diff === []
            ? ['state' => self::OK, 'reason' => 'vendor/ sama dengan composer.lock.']
            : ['state' => self::DUE, 'reason' => count($diff) . ' paket berbeda dari composer.lock (' . implode(', ', array_slice($diff, 0, 3)) . (count($diff) > 3 ? ', …' : '') . ').']);
    }

    /**
     * @param array<string, mixed> $json
     * @param list<string>         $keys
     *
     * @return array<string, string> name => version
     */
    private static function packages(array $json, array $keys): array
    {
        $out = [];

        foreach ($keys as $key) {
            foreach ($json[$key] ?? [] as $package) {
                if (isset($package['name'], $package['version'])) {
                    $out[(string) $package['name']] = (string) $package['version'];
                }
            }
        }

        return $out;
    }

    /** @return array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool} */
    private function migrations(): array
    {
        $row = ['id' => 'migrasi', 'title' => 'Struktur database (migration)', 'command' => 'migrate', 'runnable' => true];

        try {
            $db = db_connect();

            /** @var MigrationRunner $runner */
            $runner = service('migrations', null, $db, false);
            $runner->setNamespace('App');
            $files  = array_map(static fn (object $m): string => $runner->getObjectUid($m), $runner->findMigrations());
            $done   = $db->tableExists('migrations')
                ? array_map(static fn (object $h): string => $runner->getObjectUid($h), $runner->getHistory())
                : [];
        } catch (Throwable $e) {
            return $row + ['state' => self::SKIPPED, 'reason' => 'Database tidak dapat dihubungi (' . trim((string) preg_replace('/\s+/', ' ', $e->getMessage())) . ').'];
        }

        $pending = array_values(array_diff($files, $done));

        return $row + ($pending === []
            ? ['state' => self::OK, 'reason' => 'Semua ' . count($files) . ' migration sudah dijalankan.']
            : ['state' => self::DUE, 'reason' => count($pending) . ' migration baru (' . preg_replace('/^\d+/', '', $pending[0]) . (count($pending) > 1 ? ', …' : '') . ').']);
    }

    /**
     * @param array{title: string, inputs: array{paths: list<string>, ext?: list<string>, skip?: list<string>}, command: string} $step
     *
     * @return array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool}
     */
    private function ledgerStep(string $id, array $step): array
    {
        $row = ['id' => $id, 'title' => $step['title'], 'command' => $step['command'], 'runnable' => true];

        if (! $this->inputsExist($step['inputs']['paths'])) {
            return $row + ['state' => self::SKIPPED, 'reason' => 'Sumbernya tidak ada di instalasi ini.'];
        }

        $last = $this->ledger->get($id);
        $now  = $this->ledger->fingerprint($step['inputs']);

        return $row + match (true) {
            $last === null                  => ['state' => self::UNKNOWN, 'reason' => 'Belum pernah tercatat di instalasi ini.'],
            $last['fingerprint'] !== $now   => ['state' => self::DUE, 'reason' => 'Sumbernya berubah sejak ' . $last['at'] . '.'],
            default                         => ['state' => self::OK, 'reason' => 'Sama dengan saat dijalankan ' . $last['at'] . '.'],
        };
    }

    /** @param list<string> $paths */
    private function inputsExist(array $paths): bool
    {
        foreach ($paths as $path) {
            if (file_exists($this->root . $path)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool} */
    private function minified(): array
    {
        $row = ['id' => 'aset-ringkas', 'title' => 'Salinan ringkas JS/CSS', 'command' => 'gelita:aset:minify', 'runnable' => true];

        try {
            $minifier = service('assetMinifier');

            if (! $minifier->enabled()) {
                return $row + ['state' => self::SKIPPED, 'reason' => 'Peringkasan aset dimatikan.'];
            }

            return $row + ($minifier->isFresh()
                ? ['state' => self::OK, 'reason' => 'Sesuai dengan sumbernya.']
                : ['state' => self::DUE, 'reason' => 'Belum ada atau lebih lama dari sumbernya.']);
        } catch (Throwable $e) {
            return $row + ['state' => self::SKIPPED, 'reason' => $e->getMessage()];
        }
    }

    /** @return array{id: string, title: string, state: string, reason: string, command: string|null, runnable: bool} */
    private function guide(): array
    {
        $row   = ['id' => 'panduan', 'title' => 'Panduan validator', 'command' => 'gelita:panduan:cek', 'runnable' => false];
        $check = GuideSourceCheck::forValidatorGuide();

        if (! $check->docsPresent()) {
            return $row + ['state' => self::SKIPPED, 'reason' => 'Dokumen penelitian tidak ada di instalasi ini.'];
        }

        $problems = $check->problems(ValidatorGuideContent::sources());

        return $row + ($problems === []
            ? ['state' => self::OK, 'reason' => 'Sesuai dengan dokumen sumbernya.']
            : ['state' => self::MANUAL, 'reason' => count($problems) . ' bagian sumber berubah; periksa teks panduan, lalu --tandai.']);
    }
}
