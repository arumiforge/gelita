<?php

namespace App\Libraries;

use App\Models\GameReleaseModel;
use App\Models\ResearchStudyModel;
use Config\Gelita;
use Throwable;

/**
 * Facts of this install shown in the validator guide (/admin/panduan-validator),
 * read when the page opens so the guide follows the game without anyone
 * editing it (owner, 17 November 2026): regions and their challenges, the
 * challenge kinds, tries, how regions open, research mode, the version, and
 * the sign-up rules. A section lists the keys it shows (`facts`); a fact that
 * cannot be read (no database yet, no research documents on a server) is
 * left out instead of guessed.
 *
 * Nothing here reveals developer labels, item codes or answer keys: only
 * what a validator sees while playing (ValidatorGuideTest).
 */
final class ValidatorGuideFacts
{
    /** Validation instruments, file prefix in docs/revisi-10-analitik/dokumen-pdf/ => panel name. */
    private const INSTRUMENTS = [
        'instrumen-validasi-ahli-materi'     => 'Ahli materi',
        'instrumen-validasi-pengamat-budaya' => 'Pengamat budaya',
        'instrumen-validasi-ahli-media'      => 'Ahli media',
    ];

    /** @var array<string, array{0: string, 1: string}|null> */
    private array $cache = [];

    public function __construct(private readonly string $root = ROOTPATH)
    {
    }

    /** Every key a section may list. */
    public const KEYS = [
        'versi', 'instrumen', 'wilayah', 'jenis-tantangan', 'kesempatan', 'wilayah-terbuka',
        'mode-penelitian', 'pustaka', 'layar', 'nama-pengguna', 'kata-sandi', 'umur', 'jeda-lama',
    ];

    /**
     * Rows [label, value] for the given keys, unreadable ones left out.
     * Values may carry guide markup (**bold**, `code`).
     *
     * @param list<string> $keys
     *
     * @return list<array{0: string, 1: string}>
     */
    public function rows(array $keys): array
    {
        return array_values(array_filter(array_map(fn (string $key): ?array => $this->fact($key), $keys)));
    }

    /** @return array{0: string, 1: string}|null */
    public function fact(string $key): ?array
    {
        if (! array_key_exists($key, $this->cache)) {
            try {
                $this->cache[$key] = $this->read($key);
            } catch (Throwable) {
                $this->cache[$key] = null;
            }
        }

        return $this->cache[$key];
    }

    /** @return array{0: string, 1: string}|null */
    private function read(string $key): ?array
    {
        $config = config(Gelita::class);

        return match ($key) {
            'versi'           => $this->version(),
            'instrumen'       => $this->instruments(),
            'wilayah'         => $this->regions(),
            'jenis-tantangan' => $this->engines(),
            'kesempatan'      => ['Kesempatan', $config->maxTries . ' kesempatan per tantangan. Di Skenario Digital, ' . $config->maxTries . ' kesempatan per skenario.'],
            'wilayah-terbuka' => $this->unlock(),
            'mode-penelitian' => $this->fieldMode(),
            'pustaka'         => $this->library(),
            'layar'           => $this->screen(),
            'nama-pengguna'   => ['Nama pengguna', '3–30 karakter: huruf kecil, angka, titik, atau garis bawah, tanpa spasi. Contoh: `uji.media1`.'],
            'kata-sandi'      => $this->password($config),
            'umur'            => ['Umur saat mendaftar', $config->registrationAge[0] . '–' . $config->registrationAge[1] . ' tahun (isi `11`).'],
            'jeda-lama'       => ['Bila lama tidak bermain', 'Tantangan yang dibiarkan lebih dari ' . $config->attemptAbandonHours . ' jam ditutup sebagai ditinggalkan; permainan yang tidak disentuh ' . $config->sessionAbandonDays . ' hari dimulai lagi dari awal saat masuk berikutnya.'],
            default           => null,
        };
    }

    /** @return array{0: string, 1: string}|null */
    private function version(): ?array
    {
        $parts   = [];
        $release = model(GameReleaseModel::class)->activeOrNull();

        if ($release !== null) {
            $parts[] = '**' . $release['release_code'] . '**' . (! empty($release['app_version']) ? ' (aplikasi ' . $release['app_version'] . ')' : '');
        }

        $commit = $this->commit();

        if ($commit !== null) {
            $parts[] = 'kode `' . $commit . '`';
        }

        return $parts === [] ? null : ['Versi yang terpasang', implode(', ', $parts) . '. Tulis versi ini di surat pernyataan (D.1) bila diminta.'];
    }

    /**
     * Short commit of the code on this install, read from .git without
     * running git; null when the install is not a git checkout.
     */
    public function commit(): ?string
    {
        $git  = $this->root . '.git';
        $head = is_file($git . '/HEAD') ? trim((string) file_get_contents($git . '/HEAD')) : '';

        if ($head === '') {
            return null;
        }

        if (! str_starts_with($head, 'ref: ')) {
            return preg_match('/^[0-9a-f]{40}$/', $head) ? substr($head, 0, 7) : null;
        }

        $ref = substr($head, 5);

        if (is_file($git . '/' . $ref)) {
            return substr(trim((string) file_get_contents($git . '/' . $ref)), 0, 7);
        }

        $packed = is_file($git . '/packed-refs') ? (string) file_get_contents($git . '/packed-refs') : '';

        return preg_match('/^([0-9a-f]{40}) ' . preg_quote($ref, '/') . '$/m', $packed, $m) ? substr($m[1], 0, 7) : null;
    }

    /** @return array{0: string, 1: string}|null */
    private function instruments(): ?array
    {
        $found = [];

        foreach (self::INSTRUMENTS as $prefix => $panel) {
            $files = glob($this->root . GuideSourceCheck::DOCS . 'dokumen-pdf/' . $prefix . '-v*.pdf') ?: [];
            $versions = array_map(static fn (string $f): string => (string) preg_replace('/^.*-v([0-9.]+)\.pdf$/', '$1', $f), $files);
            usort($versions, 'version_compare');

            if ($versions !== []) {
                $found[] = $panel . ' v' . end($versions);
            }
        }

        return $found === [] ? null : ['Versi instrumen', implode(' · ', $found) . '. Bila versi di berkas Anda berbeda, tanyakan ke tim peneliti.'];
    }

    /** @return array{0: string, 1: string}|null */
    private function regions(): ?array
    {
        $repo  = service('contentRepository');
        $parts = [];

        foreach ($repo->levels() as $level) {
            if ((int) $level->is_active !== 1) {
                continue;
            }
            $parts[] = $level->name_id . ' (' . count($repo->nodesForLevel((int) $level->id)) . ' tantangan)';
        }

        return $parts === [] ? null : ['Wilayah', implode(', ', $parts) . '.'];
    }

    /** @return array{0: string, 1: string}|null */
    private function engines(): ?array
    {
        $repo  = service('contentRepository');
        $types = [];

        foreach ($repo->levels() as $level) {
            foreach ($repo->nodesForLevel((int) $level->id) as $node) {
                $types[(string) $node->engine_type] = true;
            }
        }

        $names = array_map(static fn (string $t): string => lang('Game.engine_' . $t, [], 'id'), array_keys($types));

        return $names === [] ? null : ['Jenis tantangan', implode(', ', $names) . '.'];
    }

    /** @return array{0: string, 1: string}|null */
    private function unlock(): ?array
    {
        $study = model(ResearchStudyModel::class)->activeStudy();

        if ($study === null) {
            return null;
        }

        return ['Wilayah terbuka', ($study['unlock_mode'] ?? '') === 'free'
            ? '**Semua wilayah langsung terbuka.** Anda boleh mulai dari wilayah mana saja.'
            : '**Berurutan.** Wilayah berikutnya terbuka setelah semua tantangan wilayah sebelumnya selesai.'];
    }

    /** @return array{0: string, 1: string}|null */
    private function fieldMode(): ?array
    {
        $study = model(ResearchStudyModel::class)->activeStudy();

        if ($study === null) {
            return null;
        }

        return ['Mode penelitian', (int) ($study['field_mode'] ?? 0) === 1
            ? '**Menyala.** Setelah masuk, ketik token sesi dari tim peneliti. Jam tantangan tidak ditampilkan, seperti saat dipakai siswa.'
            : '**Mati.** Masuk cukup dengan nama pengguna dan kata sandi, tanpa token.'];
    }

    /** @return array{0: string, 1: string}|null */
    private function library(): ?array
    {
        $repo   = service('contentRepository');
        $counts = [];

        foreach ($repo->levels() as $level) {
            $counts[] = count($repo->nodesForLevel((int) $level->id));
        }

        if ($counts === []) {
            return null;
        }

        $count = count(array_unique($counts)) === 1 ? (string) $counts[0] : 'semua';

        return ['Pustaka', 'Pustaka satu wilayah terbuka setelah ' . $count . ' tantangan wilayah itu selesai, juga bila semua wilayah sudah terbuka.'];
    }

    /** @return array{0: string, 1: string}|null */
    private function screen(): ?array
    {
        $study = model(ResearchStudyModel::class)->activeStudy();
        $min   = (int) ($study['min_stage_px'] ?? 0);
        $text  = 'Laptop, Chromebook, atau tablet mendatar. Ponsel tidak diuji.';

        if ($study !== null && (int) ($study['field_mode'] ?? 0) === 1 && $min > 0) {
            $text .= ' Dalam mode penelitian, layar yang area permainannya kurang dari ' . $min . ' piksel ditolak.';
        }

        return ['Perangkat', $text];
    }

    /** @return array{0: string, 1: string} */
    private function password(Gelita $config): array
    {
        $p     = $config->passwordPolicy;
        $needs = array_filter([
            ! empty($p['require_upper']) ? 'huruf besar' : null,
            ! empty($p['require_lower']) ? 'huruf kecil' : null,
            ! empty($p['require_digit']) ? 'angka' : null,
            ! empty($p['require_symbol']) ? 'simbol' : null,
        ]);
        $text = $p['min_length'] . '–' . $p['max_length'] . ' karakter';
        $text .= $needs === [] ? '' : ', dengan ' . implode(', ', $needs);
        $text .= ! empty($p['forbid_username']) ? ', dan tidak memuat nama pengguna.' : '.';

        return ['Kata sandi', $text];
    }
}
