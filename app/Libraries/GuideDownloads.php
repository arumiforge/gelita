<?php

namespace App\Libraries;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Files the public guides offer for download (owner, 20 November 2026): the
 * validation instruments and the B.5a booklet on the validator guide
 * (/admin/panduan-validator/unduh/{key}), the print versions of the research
 * documents for the field and the blank field sheets on the staff guide
 * (/admin/panduan-staf/unduh/{key}). Public like the guides; only the
 * research documents are offered, never the guide pages themselves.
 *
 * This list is the whole whitelist. Only its keys reach a URL; a path never
 * does, and a key of one guide is not found on the other. The files live in
 * the server's checkout (deploy pulls the whole repository, as
 * DocumentStatusPage reads docs/), which the web server does not serve, so
 * the guide controllers stream them.
 *
 * Never offered, whatever happens (owner, 20 November 2026; GuideDownloadsTest
 * refuses them): the B.5a answer key for researchers (berkas-validator/
 * khusus-peneliti/), the developer's handover file serah-terima-peneliti.pdf,
 * anything in dokumen-pdf/arsip/, the validators' xlsx worksheets, and any
 * print version not listed here. Documents 14 and 15 hold the whole pre/post
 * test with its answer keys; the owner chose to publish them anyway.
 *
 * Adding a file: one more entry below with
 *   guide   self::VALIDATOR or self::STAFF
 *   panels  validator guide only: ValidatorGuideContent::PANELS keys it is for
 *   path    relative to ROOTPATH
 *   name    the file name the reader's browser saves
 *   type    content type (self::PDF, self::XLSX, self::ZIP, or another)
 *   label   short name in the reader's words (no document numbers or paths)
 *   text    one sentence on what is in it
 *   group   staff guide only, optional: the topic page that lists it, by the
 *           section's `downloads` value (default `cetak`, the print page;
 *           `contoh` is the simulated sample download for the head researcher)
 * The label and text follow the guide's own rules (ValidatorGuideTest,
 * StaffGuideTest): no item or label codes, keys, names, paths, or server talk.
 */
final class GuideDownloads
{
    public const VALIDATOR = 'validator';

    public const STAFF = 'staf';

    public const PDF = 'application/pdf';

    public const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public const ZIP = 'application/zip';

    /** Folders of the research PDFs, relative to ROOTPATH. */
    private const PDF_DIR = 'docs/revisi-10-analitik/dokumen-pdf/';

    private const PRINT_DIR = self::PDF_DIR . 'cetak/';

    /** @var array<string, array{guide: string, panels?: list<string>, path: string, name: string, type: string, label: string, text: string}> */
    public const FILES = [
        // ---------------------------------------------------------- validator guide, one per panel
        'instrumen-ahli-materi' => [
            'guide'  => self::VALIDATOR,
            'panels' => ['materi'],
            'path'   => self::PDF_DIR . 'instrumen-validasi-ahli-materi-v2.1.pdf',
            'name'   => 'GELITA-instrumen-validasi-ahli-materi-v2.1.pdf',
            'type'   => self::PDF,
            'label'  => 'Instrumen Validasi Ahli Materi (versi 2.1)',
            'text'   => 'Petunjuk penilaian dan semua lembar untuk ahli materi, siap dicetak bila Anda lebih nyaman mengisi di kertas.',
        ],
        'buklet-b5a' => [
            'guide'  => self::VALIDATOR,
            'panels' => ['b5a'],
            'path'   => self::PDF_DIR . 'berkas-validator/materi-b5a-buklet-buta.pdf',
            'name'   => 'GELITA-buklet-pelabelan-buta-B5a.pdf',
            'type'   => self::PDF,
            'label'  => 'Buklet pelabelan buta B.5a',
            'text'   => 'Daftar butir yang sama dengan lembar kerja B.5a, untuk diisi di kertas. Kerjakan sebelum membuka bahan lain.',
        ],
        'instrumen-pengamat-budaya' => [
            'guide'  => self::VALIDATOR,
            'panels' => ['budaya'],
            'path'   => self::PDF_DIR . 'instrumen-validasi-pengamat-budaya-v2.1.pdf',
            'name'   => 'GELITA-instrumen-validasi-pengamat-budaya-v2.1.pdf',
            'type'   => self::PDF,
            'label'  => 'Instrumen Validasi Pengamat Budaya (versi 2.1)',
            'text'   => 'Petunjuk penilaian, lembar penilaian, matriks objek budaya, dan konfirmasi cek fakta untuk pengamat budaya.',
        ],
        'instrumen-ahli-media' => [
            'guide'  => self::VALIDATOR,
            'panels' => ['media'],
            'path'   => self::PDF_DIR . 'instrumen-validasi-ahli-media-v1.1.pdf',
            'name'   => 'GELITA-instrumen-validasi-ahli-media-v1.1.pdf',
            'type'   => self::PDF,
            'label'  => 'Instrumen Validasi Ahli Media, langkah 1 (versi 1.1)',
            'text'   => 'Petunjuk penilaian, lembar mutu media, uji perangkat, catatan temuan, dan telaah gambar rancangan layar.',
        ],

        // ---------------------------------------------------------- staff guide (field work)
        'lembar-kosong' => [
            'guide' => self::STAFF,
            'path'  => self::PDF_DIR . 'lembar-lapangan-kosong.pdf',
            'name'  => 'GELITA-lembar-lapangan-L0-L8-kosong.pdf',
            'type'  => self::PDF,
            'label' => 'Lembar lapangan L0–L8, kosong',
            'text'  => 'Semua lembar siap cetak di kertas A4, satu halaman per lembar, dengan kepala halaman dan baris kosong untuk diisi tangan.',
        ],
        'lembar-lapangan' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '02_LEMBAR_LAPANGAN.pdf',
            'name'  => 'GELITA-lembar-lapangan-petunjuk.pdf',
            'type'  => self::PDF,
            'label' => 'Lembar lapangan L0–L8, petunjuk lengkap',
            'text'  => 'Cara mengisi setiap lembar, daftar kodenya, contoh baris, dan lembar yang dipakai di setiap pertemuan.',
        ],
        'kode-dan-arti' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '03_KODE_DAN_ARTI.pdf',
            'name'  => 'GELITA-kode-dan-artinya.pdf',
            'type'  => self::PDF,
            'label' => 'Kode dan artinya',
            'text'  => 'Arti semua kode yang dipakai di lembar lapangan, panel, dan data penelitian.',
        ],
        'kamus-data' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '11_KAMUS_DATA.pdf',
            'name'  => 'GELITA-kamus-data.pdf',
            'type'  => self::PDF,
            'label' => 'Kamus data penelitian',
            'text'  => 'Arti setiap kolom data penelitian, aturan menggabungkan lembar lapangan dengan catatan permainan, dan penanda untuk analisis.',
        ],
        'rancangan-tes' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '13_TES_AWAL_AKHIR.pdf',
            'name'  => 'GELITA-tes-awal-akhir-rancangan.pdf',
            'type'  => self::PDF,
            'label' => 'Tes awal–akhir, rancangan',
            'text'  => 'Susunan tes, bentuk A dan B, pembagian bentuk dari tempat duduk, waktu di P1 dan P5, dan cara menilainya.',
        ],
        'tes-membaca' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '14_TES_BAGIAN_MEMBACA.pdf',
            'name'  => 'GELITA-tes-bagian-membaca.pdf',
            'type'  => self::PDF,
            'label' => 'Tes awal–akhir, bagian Membaca (bentuk A dan B)',
            'text'  => 'Semua teks dan soal bagian Membaca beserta kunci jawabannya. Jangan diperlihatkan kepada siswa.',
        ],
        'tes-digital' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '15_TES_BAGIAN_DIGITAL.pdf',
            'name'  => 'GELITA-tes-bagian-digital.pdf',
            'type'  => self::PDF,
            'label' => 'Tes awal–akhir, bagian Aman dan etis serta Budaya digital',
            'text'  => 'Semua skenario dan soal bagian digital beserta kunci jawabannya. Jangan diperlihatkan kepada siswa.',
        ],
        'angket-akses-digital' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '16_ANGKET_AKSES_DIGITAL.pdf',
            'name'  => 'GELITA-angket-akses-digital.pdf',
            'type'  => self::PDF,
            'label' => 'Angket akses digital',
            'text'  => 'Butir angket yang diisi siswa setelah tes awal, pertanyaan tambahan di P5, dan cara pelaksanaannya.',
        ],
        'skala-sikap' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '17_SKALA_SIKAP.pdf',
            'name'  => 'GELITA-skala-sikap.pdf',
            'type'  => self::PDF,
            'label' => 'Skala sikap menjaga warisan',
            'text'  => 'Dua belas pernyataan yang diisi siswa di Balai Refleksi pada P5, dengan cara pelaksanaannya.',
        ],
        'surat-izin' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '23_SURAT_INFORMASI_DAN_PERSETUJUAN.pdf',
            'name'  => 'GELITA-surat-informasi-dan-persetujuan.pdf',
            'type'  => self::PDF,
            'label' => 'Surat informasi dan persetujuan',
            'text'  => 'Surat untuk orang tua, formulir izin, naskah persetujuan siswa, dan formulir pengunduran diri, siap dicetak.',
        ],
        'uji-keterbacaan' => [
            'guide' => self::STAFF,
            'path'  => self::PRINT_DIR . '24_UJI_KETERBACAAN.pdf',
            'name'  => 'GELITA-uji-keterbacaan.pdf',
            'type'  => self::PDF,
            'label' => 'Uji keterbacaan dengan anak',
            'text'  => 'Langkah per anak, bahan, peran, lembar catatan, dan aturan keputusan uji keterbacaan.',
        ],

        // ---------------------------------------------------------- staff guide, page contoh-data (owner, 20 November 2026)
        'contoh-data-excel' => [
            'guide' => self::STAFF,
            'group' => 'contoh',
            'path'  => 'docs/contoh-data/GELITA-contoh-data-simulasi-300-siswa.xlsx',
            'name'  => 'GELITA-contoh-data-simulasi-300-siswa.xlsx',
            'type'  => self::XLSX,
            'label' => 'Contoh unduhan Excel, 300 siswa simulasi',
            'text'  => 'Semua lembar bawaan Unduh data dengan nilai berlabel, tanpa Event Mentah. Bukan data penelitian.',
        ],
        'contoh-data-csv' => [
            'guide' => self::STAFF,
            'group' => 'contoh',
            'path'  => 'docs/contoh-data/GELITA-contoh-data-simulasi-300-siswa-csv.zip',
            'name'  => 'GELITA-contoh-data-simulasi-300-siswa-csv.zip',
            'type'  => self::ZIP,
            'label' => 'Contoh unduhan CSV, 300 siswa simulasi',
            'text'  => 'Satu CSV per lembar dengan nilai berkode, termasuk Event Mentah. Bukan data penelitian.',
        ],
    ];

    /**
     * Entries of one guide, keyed by their URL key.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function forGuide(string $guide): array
    {
        return array_filter(self::FILES, static fn (array $file): bool => $file['guide'] === $guide);
    }

    /** Group of an entry without one: the staff guide's print page. */
    public const DEFAULT_GROUP = 'cetak';

    /**
     * Entries of one guide in one group (a staff-guide topic page lists one group).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function forGroup(string $guide, string $group): array
    {
        return array_filter(self::forGuide($guide), static fn (array $file): bool => ($file['group'] ?? self::DEFAULT_GROUP) === $group);
    }

    /**
     * Validator-guide entries for the given panels, in list order; an empty
     * list means every panel.
     *
     * @param list<string> $panels
     *
     * @return array<string, array<string, mixed>>
     */
    public static function forPanels(array $panels): array
    {
        return array_filter(
            self::forGuide(self::VALIDATOR),
            static fn (array $file): bool => $panels === [] || array_intersect($panels, $file['panels'] ?? []) !== [],
        );
    }

    /** The entry of $key when it belongs to $guide, else null. */
    public static function find(string $guide, string $key): ?array
    {
        $file = self::FILES[$key] ?? null;

        return $file !== null && $file['guide'] === $guide ? $file : null;
    }

    /** Where the file is on this machine. */
    public static function fullPath(array $file, string $root = ROOTPATH): string
    {
        return $root . $file['path'];
    }

    /** Size in bytes, or null when the file is not in this checkout. */
    public static function size(array $file, string $root = ROOTPATH): ?int
    {
        $path = self::fullPath($file, $root);

        return is_file($path) ? (int) filesize($path) : null;
    }

    /** Address of a download: BASE/unduh/{key} of the guide's site. */
    public static function url(string $guide, string $key): string
    {
        $base = $guide === self::VALIDATOR ? ValidatorGuideContent::BASE : StaffGuideContent::BASE;

        return base_url($base . '/unduh/' . $key);
    }

    /** "PDF · 687 KB", "XLSX · 1,2 MB" (sizes in the panel's Indonesian format). */
    public static function meta(array $file, ?int $bytes): string
    {
        $kind = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($bytes === null) {
            return $kind;
        }

        $size = $bytes >= 1_048_576
            ? fmt_num($bytes / 1_048_576, 1, 'id') . ' MB'
            : fmt_num(max(1, (int) round($bytes / 1024)), 0, 'id') . ' KB';

        return $kind . ' · ' . $size;
    }

    /**
     * The download of $key on $guide: the file as an attachment under its
     * readable name, never indexed, with an ETag from its content (a repeated
     * request with that tag gets 304). An unknown key, a key of the other
     * guide, or a file missing from this checkout is "page not found".
     */
    public static function respond(string $guide, string $key, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $file = self::find($guide, $key);
        $path = $file === null ? '' : self::fullPath($file);

        if ($path === '' || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $etag     = '"' . substr((string) hash_file('sha256', $path), 0, 32) . '"';
        $modified = gmdate('D, d M Y H:i:s', (int) filemtime($path)) . ' GMT';

        if (in_array($etag, array_map('trim', explode(',', $request->getHeaderLine('If-None-Match'))), true)) {
            return $response->setStatusCode(304)
                ->setHeader('ETag', $etag)
                ->setHeader('Last-Modified', $modified)
                ->setHeader('X-Robots-Tag', 'noindex');
        }

        return $response->download($path, null)
            ->setFileName($file['name'])
            ->setContentType($file['type'], '')
            ->setHeader('ETag', $etag)
            ->setHeader('Last-Modified', $modified)
            ->setHeader('Cache-Control', 'no-cache')
            ->setHeader('X-Robots-Tag', 'noindex');
    }

    /**
     * Rows for the guide views: entries whose file is here, with url, meta,
     * and the panel names (validator guide).
     *
     * @param array<string, array<string, mixed>> $files
     *
     * @return list<array{key: string, label: string, text: string, url: string, meta: string, panels: string}>
     */
    public static function rows(array $files): array
    {
        $rows = [];

        foreach ($files as $key => $file) {
            $bytes = self::size($file);

            if ($bytes === null) {
                continue;
            }

            $rows[] = [
                'key'    => $key,
                'label'  => $file['label'],
                'text'   => $file['text'],
                'url'    => self::url($file['guide'], $key),
                'meta'   => self::meta($file, $bytes),
                'panels' => implode(', ', array_map(static fn (string $p): string => ValidatorGuideContent::PANELS[$p] ?? $p, $file['panels'] ?? [])),
            ];
        }

        return $rows;
    }
}
