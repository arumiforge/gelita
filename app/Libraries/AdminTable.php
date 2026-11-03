<?php

namespace App\Libraries;

use App\Services\RawDataService;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Tabel data panel admin yang diurutkan dan dipaginasi di server (Dok 3 §8.2).
 *
 * Hasil `page()` berbentuk sama dengan `RawDataService::page()` (kontrak
 * sesi data mentah, §8.4), sehingga components/data-table.php merender
 * keduanya:
 *
 *     ['rows' => list<array>, 'total' => int, 'page' => int, 'per_page' => int,
 *      'sort' => ?string, 'columns' => list<array{key, label, type, sortable?}>]
 *
 * `sort` adalah kunci kolom, diawali `-` untuk urutan menurun (`-started_at`).
 *
 * Definisi kolom untuk halaman daftar (Siswa, Sesi, Kritik & saran):
 *
 *     ['key' => 'participant_code', 'label' => 'Kode', 'type' => 'code',
 *      'sort' => 'participants.participant_code',   // ekspresi ORDER BY; null = tidak dapat diurutkan
 *      'identity' => true]                          // hanya untuk admin (nama, nama pengguna)
 *
 * Jenis kolom: text · int · num · ms · datetime · date · code · status · bool
 * · json · stars · clip (teks panjang dipotong, klik untuk lengkap)
 * · label:{grup admin_label}. Lihat components/data-table.php.
 */
final class AdminTable
{
    /** Pilihan baris per halaman. */
    public const PER_PAGE = [25, 50, 100];

    /**
     * Kolom yang boleh dilihat pemanggil: identity columns are dropped for
     * callers who may not see identities (`participants.accounts`).
     *
     * @param list<array<string, mixed>> $columns
     *
     * @return list<array<string, mixed>>
     */
    public static function visibleColumns(array $columns, bool $identity): array
    {
        return array_values(array_filter(
            $columns,
            static fn (array $column): bool => $identity || empty($column['identity']),
        ));
    }

    /**
     * Halaman, baris per halaman, dan urutan dari query string. Urutan yang
     * tidak dikenal diganti `$defaultSort`.
     *
     * @param list<string> $sortable kunci kolom yang dapat diurutkan
     *
     * @return array{page: int, per_page: int, sort: string|null}
     */
    public static function params(IncomingRequest $request, array $sortable, ?string $defaultSort): array
    {
        $perPage = (int) ($request->getGet('per_page') ?? 0);
        $sort    = trim((string) ($request->getGet('sort') ?? ''));

        return [
            'page'     => max(1, (int) ($request->getGet('page') ?? 1)),
            'per_page' => in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0],
            'sort'     => self::validSort($sort, $sortable) ?? $defaultSort,
        ];
    }

    /** `key` atau `-key` bila key termasuk $sortable; selain itu null. */
    public static function validSort(?string $sort, array $sortable): ?string
    {
        if ($sort === null || $sort === '') {
            return null;
        }

        return in_array(ltrim($sort, '-'), $sortable, true) ? $sort : null;
    }

    /**
     * Satu halaman hasil builder. Builder sudah memuat SELECT, JOIN, WHERE
     * (termasuk cakupan sekolah); kelas ini hanya menambah ORDER BY dan LIMIT.
     *
     * @param list<array<string, mixed>> $columns
     *
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int, sort: string|null, columns: list<array<string, mixed>>}
     */
    public static function page(BaseBuilder $builder, array $columns, int $page, int $perPage, ?string $sort, string $tieBreaker): array
    {
        $total = (int) (clone $builder)->countAllResults(false);
        $pages = max(1, (int) ceil($total / max(1, $perPage)));
        $page  = min(max(1, $page), $pages);

        self::order($builder, $columns, $sort, $tieBreaker);

        $rows = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return [
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'sort'     => $sort,
            'columns'  => self::publicColumns($columns),
        ];
    }

    /**
     * Semua baris builder sebagai CSV mentah (UTF-8 dengan BOM, pemisah koma,
     * judul = kunci kolom). Nilai ditulis apa adanya dari database: tanpa
     * format tampilan, durasi tetap milidetik.
     *
     * @param list<array<string, mixed>> $columns
     * @param resource                   $handle
     *
     * @return int jumlah baris data
     */
    public static function streamCsv(BaseBuilder $builder, array $columns, $handle, ?string $sort, string $tieBreaker): int
    {
        $keys  = array_column($columns, 'key');
        $types = array_column($columns, 'type', 'key');

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $keys, ',', '"', '');

        self::order($builder, $columns, $sort, $tieBreaker);

        $count = 0;
        $query = $builder->get();

        // Akhir hasil: MySQLi memberi null, SQLite3 memberi false
        while (is_array($row = $query->getUnbufferedRow('array'))) {
            fputcsv($handle, array_map(static fn (string $key) => self::csvValue(
                ($types[$key] ?? '') === 'school_ref' ? RawDataService::schoolRef($row[$key] ?? null) : ($row[$key] ?? null),
            ), $keys), ',', '"', '');
            $count++;
        }

        return $count;
    }

    /** Nilai sel CSV: null → kosong, bool → 1/0, array → JSON. */
    public static function csvValue(mixed $value): string
    {
        return match (true) {
            $value === null      => '',
            is_bool($value)      => $value ? '1' : '0',
            is_array($value)     => (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default              => (string) $value,
        };
    }

    /**
     * Query string halaman ini dengan perubahan: nilai null membuang kunci.
     * `page` selalu dibuang kecuali diberikan, agar urutan atau filter baru
     * mulai dari halaman 1.
     *
     * @param array<string, mixed>       $query  query string sekarang
     * @param array<string, string|int|null> $change
     */
    public static function url(string $path, array $query, array $change): string
    {
        if (! array_key_exists('page', $change)) {
            unset($query['page']);
        }

        foreach ($change as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }

        $query = array_filter($query, static fn ($value): bool => $value !== '' && $value !== null);

        return $path . ($query === [] ? '' : '?' . http_build_query($query));
    }

    /**
     * HTML satu sel menurut jenis kolom. Sel kosong menjadi "—"; durasi
     * tampil `m:ss` dengan nilai milidetik di `title`; teks panjang dipotong
     * dan dapat dibuka.
     */
    public static function cell(mixed $value, string $type = 'text'): string
    {
        if ($value === null || $value === '' || (is_array($value) && $value === [])) {
            return '<span class="is-empty">—</span>';
        }

        if (str_starts_with($type, 'label:')) {
            return esc(admin_label(substr($type, 6), (string) $value));
        }

        switch ($type) {
            case 'int':
                return esc(fmt_num($value, 0, 'id'));

            case 'num':
                $number = (float) $value;
                $places = floor($number) === $number ? 0 : 2;

                return esc(fmt_num($number, $places, 'id'));

            case 'ms':
                $ms = (int) $value;

                return '<span title="' . esc(fmt_num($ms, 0, 'id') . ' mdtk', 'attr') . '">' . esc(ms_to_human($ms)) . '</span>';

            case 'datetime':
                return '<span title="' . esc((string) $value, 'attr') . '">' . esc(fmt_date($value, true, 'id')) . '</span>';

            case 'date':
                return esc(preg_replace('/ \d{2}:\d{2}$/', '', fmt_date($value, false, 'id')));

            case 'code':
                return esc((string) $value);

            case 'school_ref':
                // A school id shown as its code (`SCH-000123`), never its name (27 K5)
                return esc((string) RawDataService::schoolRef($value));

            case 'status':
                $code = (string) $value;

                return '<span class="badge is-' . esc($code, 'attr') . '">' . esc(admin_label('status', $code)) . '</span>';

            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'ya' : 'tidak';

            case 'stars':
                return stars_html((int) $value, 5, false);

            case 'clip':
                $text = (string) $value;

                if (mb_strlen($text) <= 32) {
                    return esc($text);
                }

                return '<details class="cell-more"><summary title="Klik untuk teks lengkap">' . esc(mb_substr($text, 0, 32)) . '…</summary><p data-keep-space>' . esc($text) . '</p></details>';

            case 'json':
                $raw = is_string($value) ? $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                return '<span class="json-inline" title="' . esc($raw, 'attr') . '">' . esc($raw) . '</span>';

            default:
                $text = is_array($value) ? AnswerText::fromData($value) : (string) $value;

                if (mb_strlen($text) > 80) {
                    return '<details class="cell-more"><summary>' . esc(mb_substr($text, 0, 60)) . '…</summary><p data-keep-space>' . esc($text) . '</p></details>';
                }

                return esc($text);
        }
    }

    /** Kelas `<td>`/`<th>` untuk jenis kolom (angka rata kanan, kode mono). */
    public static function cellClass(string $type): string
    {
        return match ($type) {
            'int', 'num', 'ms' => 'is-num',
            'code', 'school_ref' => 'is-code',
            'datetime'         => 'is-time',
            default            => '',
        };
    }

    /** @param list<array<string, mixed>> $columns */
    private static function order(BaseBuilder $builder, array $columns, ?string $sort, string $tieBreaker): void
    {
        if ($sort !== null && $sort !== '') {
            $key = ltrim($sort, '-');

            foreach ($columns as $column) {
                if ($column['key'] === $key && ! empty($column['sort'])) {
                    $builder->orderBy((string) $column['sort'], str_starts_with($sort, '-') ? 'DESC' : 'ASC', false);
                    break;
                }
            }
        }

        // Urutan stabil antarhalaman
        $builder->orderBy($tieBreaker, 'DESC');
    }

    /**
     * Kolom untuk view: tanpa ekspresi SQL.
     *
     * @param list<array<string, mixed>> $columns
     *
     * @return list<array{key: string, label: string, type: string, sortable: bool}>
     */
    private static function publicColumns(array $columns): array
    {
        return array_map(static fn (array $column): array => [
            'key'      => (string) $column['key'],
            'label'    => (string) $column['label'],
            'type'     => (string) ($column['type'] ?? 'text'),
            'sortable' => ! empty($column['sort']),
        ], $columns);
    }
}
