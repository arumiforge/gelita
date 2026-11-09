<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Imports the instrument data files (research revision 10, part 8: 34 §6.2,
 * K2): `app/Database/Seeds/data/instrumen/{tes,angket,sikap}.php` →
 * `instrument_versions`, `instrument_stimuli`, `instrument_items`.
 *
 * Every check runs before anything is written; one failed check refuses that
 * file whole. A file whose content hash equals the active version writes
 * nothing; changed content under the same `version` is refused ("Isi berubah,
 * naikkan versinya."); a new version becomes active and the old one stays,
 * since sittings point at their own version. Items are never changed in place.
 *
 * Used by `gelita:instrumen:impor` and `gelita:reset` (after the bank).
 */
class InstrumentImporter
{
    public const INSTRUMENTS = ['tes', 'angket', 'sikap'];

    /** Test parts (13 → Kode butir), their time block (K9), and items per form. */
    public const TEST_PARTS = ['mb' => ['block' => 'p1', 'count' => 16], 'ae' => ['block' => 'p23', 'count' => 8], 'bd' => ['block' => 'p23', 'count' => 8]];

    public const TEST_FORMS = ['A', 'B'];

    /** Questionnaire sections, one per screen (16). */
    public const QUESTIONNAIRE_SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    /** The 41 data columns of ang-01–ang-17, in this order (16 → Pemasukan data; K7). */
    public const QUESTIONNAIRE_UNITS = [
        'phone_access', 'home_phone', 'home_tablet', 'home_computer', 'home_console', 'home_device_none', 'home_internet',
        'first_use_grade', 'screen_time_school', 'screen_time_holiday', 'game_time_school', 'game_time_holiday',
        'act_video', 'act_game', 'act_chat', 'act_schoolwork', 'act_search', 'act_read', 'act_post',
        'acct_whatsapp', 'acct_youtube', 'acct_tiktok', 'acct_instagram', 'acct_facebook', 'acct_game',
        'parent_couse', 'parent_talk', 'parent_rule_time', 'parent_rule_content',
        'safety_info_parent', 'safety_info_teacher', 'safety_info_family', 'safety_info_friend', 'safety_info_media', 'safety_info_none', 'safety_info_dk',
        'school_device_use', 'digital_quiz_exp', 'read_medium_pref', 'home_indonesian', 'books_home',
    ];

    /** Attitude items written as negatives (17 → Butir: no. 3, 6, 8, 10). */
    public const ATTITUDE_NEGATIVES = [3, 6, 8, 10];

    /** Answer codes of the attitude scale (17 → Empat pilihan). */
    public const ATTITUDE_CODES = ['1', '2', '3', '4'];

    private BaseConnection $db;

    private string $dir;

    public function __construct(?BaseConnection $db = null, ?string $dir = null)
    {
        $this->db  = $db ?? db_connect();
        $this->dir = rtrim($dir ?? self::defaultDir(), '/\\') . DIRECTORY_SEPARATOR;
    }

    public static function defaultDir(): string
    {
        return APPPATH . 'Database' . DIRECTORY_SEPARATOR . 'Seeds' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'instrumen';
    }

    /**
     * Imports the files (all, or $only).
     *
     * @param list<string>|null $only
     *
     * @return array{status: string, files: array<string, array{status: string, version: ?string, items: int}>, errors: list<string>}
     *               file status: `imported`, `unchanged`, `checked` (dry run), `refused`, `missing`
     */
    public function import(?array $only = null, bool $dryRun = false): array
    {
        $files  = [];
        $errors = [];

        foreach ($only ?? self::INSTRUMENTS as $instrument) {
            if (! in_array($instrument, self::INSTRUMENTS, true)) {
                $errors[] = $instrument . ': berkas instrumen tidak dikenal.';

                continue;
            }

            $path = $this->dir . $instrument . '.php';

            if (! is_file($path)) {
                $files[$instrument] = ['status' => 'missing', 'version' => null, 'items' => 0];
                $errors[]           = $instrument . ': berkas ' . $path . ' tidak ditemukan.';

                continue;
            }

            $data  = (static fn (string $file) => require $file)($path);
            $found = is_array($data) ? $this->check($instrument, $data) : [$instrument . '.php tidak mengembalikan array.'];

            if ($found !== []) {
                $files[$instrument] = ['status' => 'refused', 'version' => is_array($data) ? (string) ($data['version'] ?? '') : null, 'items' => 0];
                array_push($errors, ...array_map(static fn (string $e): string => $instrument . ': ' . $e, $found));

                continue;
            }

            $rows = $this->normalize($instrument, $data);
            $hash = hash('sha256', (string) json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $same = $this->db->table('instrument_versions')->where('instrument', $instrument)->where('version', (string) $data['version'])->get()->getRowArray();

            if ($same !== null && $same['content_hash'] !== $hash) {
                $files[$instrument] = ['status' => 'refused', 'version' => (string) $data['version'], 'items' => 0];
                $errors[]           = $instrument . ': Isi berubah, naikkan versinya.';

                continue;
            }

            if ($same !== null) {
                // Same content: nothing is written, but an older file may make it active again
                if (! $dryRun && (int) $same['is_active'] !== 1) {
                    $this->activate($instrument, (int) $same['id']);
                }

                $files[$instrument] = ['status' => 'unchanged', 'version' => (string) $data['version'], 'items' => count($rows['items'])];

                continue;
            }

            if (! $dryRun) {
                $this->write($instrument, (string) $data['version'], (string) $data['status'], $hash, $rows);
            }

            $files[$instrument] = ['status' => $dryRun ? 'checked' : 'imported', 'version' => (string) $data['version'], 'items' => count($rows['items'])];
        }

        if (! $dryRun) {
            service('instrumentRepository')->flush();
        }

        return ['status' => $errors === [] ? 'ok' : 'failed', 'files' => $files, 'errors' => $errors];
    }

    // ------------------------------------------------------------ checks

    /**
     * Every check of 34 §6.2 for one file.
     *
     * @param array<string, mixed> $data
     *
     * @return list<string> problems (empty = fine)
     */
    public function check(string $instrument, array $data): array
    {
        $errors = [];

        if (preg_match('/^' . $instrument . '-\d+\.\d+$/', (string) ($data['version'] ?? '')) !== 1) {
            $errors[] = "Versi harus berbentuk {$instrument}-0.1.";
        }

        if (! in_array($data['status'] ?? null, ['needs_verification', 'frozen'], true)) {
            $errors[] = 'Status harus needs_verification atau frozen.';
        }

        if (! is_array($data['items'] ?? null) || $data['items'] === []) {
            return [...$errors, 'Tidak ada butir.'];
        }

        return [...$errors, ...match ($instrument) {
            'tes'    => $this->checkTest($data),
            'angket' => $this->checkQuestionnaire($data),
            default  => $this->checkAttitude($data),
        }];
    }

    /** @return list<string> */
    private function checkTest(array $data): array
    {
        $errors  = [];
        $stimuli = (array) ($data['stimuli'] ?? []);
        $byCell  = [];
        $counts  = [];

        foreach ((array) $data['items'] as $code => $item) {
            $code = (string) $code;

            if (preg_match('/^tes-(?:(mb|ae|bd)-([AB])(\d{2})|ct-(\d{2}))$/', $code, $m) !== 1) {
                $errors[] = "{$code}: kode butir tidak sesuai pola tes-{bagian}-{bentuk}{nn} atau tes-ct-{nn}.";

                continue;
            }

            $example = ($m[4] ?? '') !== '';
            $part    = $example ? 'ct' : $m[1];

            if (($item['part'] ?? null) !== $part || (int) ($item['no'] ?? 0) !== (int) ($example ? $m[4] : $m[3]) || ($item['form'] ?? null) !== ($example ? null : $m[2])) {
                $errors[] = "{$code}: bagian, bentuk, atau nomor tidak cocok dengan kodenya.";
            }

            if (! isset($stimuli[(string) ($item['stimulus'] ?? '')])) {
                $errors[] = "{$code}: bacaan atau adegan " . ($item['stimulus'] ?? '—') . ' tidak ada.';
            }

            array_push($errors, ...array_map(static fn (string $e): string => "{$code}: {$e}", $this->checkKey($item, $example)));

            if (! $example) {
                $counts[$part][$m[2]] = ($counts[$part][$m[2]] ?? 0) + 1;
                $byCell[$part][(int) $m[3]][$m[2]] = [(string) $item['type'], json_encode($this->equivalenceCell($part, (array) ($item['cell'] ?? [])))];
            }
        }

        foreach (self::TEST_PARTS as $part => $rule) {
            foreach (self::TEST_FORMS as $form) {
                if (($counts[$part][$form] ?? 0) !== $rule['count']) {
                    $errors[] = "Bagian {$part} bentuk {$form} harus berisi {$rule['count']} butir (sekarang " . ($counts[$part][$form] ?? 0) . ').';
                }
            }

            // Forms A and B: same numbers, part, item type, and cell (13 → Aturan kesetaraan)
            foreach ($byCell[$part] ?? [] as $no => $forms) {
                if (count($forms) !== 2 || $forms['A'] !== $forms['B']) {
                    $errors[] = sprintf('Butir %s nomor %02d: bentuk A dan B harus punya jenis butir dan sel yang sama.', $part, $no);
                }
            }
        }

        foreach ((array) ($data['parts'] ?? []) as $part => $meta) {
            foreach ((array) ($meta['examples'] ?? []) as $example) {
                if (! isset($data['items'][$example])) {
                    $errors[] = "Contoh {$example} untuk bagian {$part} tidak ada.";
                }
            }
        }

        if (array_keys((array) ($data['parts'] ?? [])) !== array_keys(self::TEST_PARTS)) {
            $errors[] = 'Daftar bagian harus mb, ae, bd.';
        }

        return $errors;
    }

    /** The part of a cell that forms A and B share (13 → Aturan kesetaraan). */
    private function equivalenceCell(string $part, array $cell): array
    {
        return match ($part) {
            'mb'    => [$cell['sub'] ?? null, $cell['level'] ?? null],
            'ae'    => [$cell['topic'] ?? null, $cell['complexity'] ?? null],
            default => [$cell['kind'] ?? null],
        };
    }

    /**
     * Key of one test item against its type (34 §6.1): every wrong PG/D2/D3
     * choice has an error code, the right one none (15).
     *
     * @return list<string>
     */
    private function checkKey(array $item, bool $example): array
    {
        $show   = (array) ($item['show'] ?? []);
        $key    = (array) ($item['key'] ?? []);
        $answer = (string) ($key['answer'] ?? '');
        $errors = (array) ($key['errors'] ?? []);
        $out    = [];

        switch ($item['type'] ?? null) {
            case 'pg':
            case 'd2':
                $options = array_map('strval', array_keys((array) ($show['options'] ?? [])));

                if ($options !== ['A', 'B', 'C', 'D'] || ! in_array($answer, $options, true)) {
                    return ['pilihan harus A–D dan kuncinya salah satu darinya.'];
                }

                if ($item['type'] === 'd2' && ! $example) {
                    $scores = array_map('intval', (array) ($key['scores'] ?? []));
                    ksort($scores);

                    if (array_keys($scores) !== $options || (array_count_values($scores)[2] ?? 0) !== 1 || ($scores[$answer] ?? null) !== 2
                        || array_diff(array_values($scores), [0, 1, 2]) !== []) {
                        $out[] = 'skor D2 harus 2, 1, atau 0 untuk setiap pilihan, tepat satu bernilai 2 (kuncinya).';
                    }
                }

                if (! $example) {
                    foreach ($options as $letter) {
                        $right = $letter === $answer;

                        if ($right && isset($errors[$letter])) {
                            $out[] = "pilihan tepat {$letter} tidak boleh punya kode kesalahan.";
                        }

                        if (! $right && trim((string) ($errors[$letter] ?? '')) === '') {
                            $out[] = "pilihan {$letter} belum punya kode kesalahan.";
                        }
                    }
                }

                return $out;

            case 'pgk':
                $scale = ($show['scale'] ?? 'bs') === 'fp' ? 'FP' : 'BS';

                if (array_map('strval', array_keys((array) ($show['statements'] ?? []))) !== ['1', '2', '3'] || preg_match('/^[' . $scale . ']{3}$/', $answer) !== 1) {
                    return ['PGK butuh tiga pernyataan dan kunci tiga huruf ' . $scale[0] . '/' . $scale[1] . '.'];
                }

                if (! $example && array_map('strval', array_keys($errors)) !== ['1', '2', '3']) {
                    $out[] = 'setiap pernyataan PGK butuh satu kode kesalahan.';
                }

                return $out;

            case 'match':
                $right = array_map('strval', array_keys((array) ($show['right'] ?? [])));

                if (array_map('strval', array_keys((array) ($show['left'] ?? []))) !== ['1', '2', '3'] || $right !== ['A', 'B', 'C', 'D']
                    || preg_match('/^1([A-D]) 2([A-D]) 3([A-D])$/', $answer, $m) !== 1 || count(array_unique([$m[1], $m[2], $m[3]])) !== 3) {
                    return ['menjodohkan butuh pernyataan 1–3, pilihan A–D, dan kunci seperti 1C 2A 3D.'];
                }

                if (! $example) {
                    $unused = array_values(array_diff($right, [$m[1], $m[2], $m[3]]));

                    if (trim((string) ($errors[$unused[0]] ?? '')) === '' || trim((string) ($errors['other_pair'] ?? '')) === '') {
                        $out[] = 'menjodohkan butuh kode untuk pilihan pengecoh dan untuk pilihan milik pernyataan lain (other_pair).';
                    }
                }

                return $out;
        }

        return ['jenis butir tidak dikenal.'];
    }

    /** @return list<string> */
    private function checkQuestionnaire(array $data): array
    {
        $errors = [];
        $main   = [];
        $risk   = 0;
        $units  = [];

        foreach ((array) $data['items'] as $code => $item) {
            $code = (string) $code;

            if (preg_match('/^ang-\d{2}$/', $code) !== 1) {
                $errors[] = "{$code}: kode butir tidak sesuai pola ang-{nn}.";

                continue;
            }

            $part = (string) ($item['part'] ?? '');

            if ($part === 'risk') {
                $risk++;
            } elseif (in_array($part, self::QUESTIONNAIRE_SECTIONS, true)) {
                $main[] = (int) ($item['no'] ?? 0);
                array_push($units, ...(array) ($item['units'] ?? []));
            } else {
                $errors[] = "{$code}: bagian {$part} tidak dikenal.";
            }

            array_push($errors, ...array_map(static fn (string $e): string => "{$code}: {$e}", $this->checkQuestion($item)));
        }

        if ($main !== range(1, 17)) {
            $errors[] = 'Angket utama harus berisi 17 butir ang-01 sampai ang-17, urut.';
        }

        if ($units !== self::QUESTIONNAIRE_UNITS) {
            $errors[] = 'Satuan ang-01–ang-17 harus 41 kolom 16, urut (sekarang ' . count($units) . ').';
        }

        if ($risk < 2 || $risk > 3) {
            $errors[] = 'Butir risiko daring harus dua atau tiga.';
        }

        if (array_keys((array) ($data['sections'] ?? [])) !== self::QUESTIONNAIRE_SECTIONS) {
            $errors[] = 'Bagian angket harus A sampai H.';
        }

        return $errors;
    }

    /** @return list<string> */
    private function checkQuestion(array $item): array
    {
        $show  = (array) ($item['show'] ?? []);
        $units = array_values((array) ($item['units'] ?? []));

        if ($units === [] || count(array_unique($units)) !== count($units) || trim((string) ($show['prompt'] ?? '')) === '') {
            return ['butuh pertanyaan dan satuan yang unik.'];
        }

        if (($item['type'] ?? null) === 'multi') {
            return array_keys((array) ($show['boxes'] ?? [])) === $units ? [] : ['kotak centang harus sama dengan satuannya.'];
        }

        if (($item['type'] ?? null) !== 'single' || (array) ($show['options'] ?? []) === []) {
            return ['jenis butir harus single atau multi, dengan pilihan.'];
        }

        $groups = $show['rows'] ?? $show['columns'] ?? null;

        if ($groups === null ? count($units) !== 1 : array_keys((array) $groups) !== $units) {
            return ['baris atau kolom harus sama dengan satuannya.'];
        }

        return [];
    }

    /** @return list<string> */
    private function checkAttitude(array $data): array
    {
        $errors   = [];
        $numbers  = [];
        $negative = [];
        $example  = false;

        foreach ((array) $data['items'] as $code => $item) {
            $code = (string) $code;

            if (preg_match('/^skp-(ct-)?\d{2}$/', $code, $m) !== 1 || trim((string) ($item['text'] ?? '')) === '') {
                $errors[] = "{$code}: kode harus skp-{nn} atau skp-ct-{nn}, dengan kalimatnya.";

                continue;
            }

            if (($m[1] ?? '') !== '') {
                $example = true;

                continue;
            }

            $numbers[] = (int) ($item['no'] ?? 0);

            if (($item['direction'] ?? '+') === '-') {
                $negative[] = (int) $item['no'];
            }
        }

        if ($numbers !== range(1, 12)) {
            $errors[] = 'Skala sikap harus berisi 12 pernyataan skp-01 sampai skp-12, urut.';
        }

        if ($negative !== self::ATTITUDE_NEGATIVES) {
            $errors[] = 'Butir negatif harus nomor 3, 6, 8, dan 10.';
        }

        if (! $example) {
            $errors[] = 'Contoh skp-ct-01 belum ada.';
        }

        if (array_map('strval', array_keys((array) ($data['choices'] ?? []))) !== self::ATTITUDE_CODES) {
            $errors[] = 'Pilihan jawaban harus berkode 1 sampai 4.';
        }

        return $errors;
    }

    // ------------------------------------------------------------ writing

    /**
     * Rows as stored, the input of the content hash.
     *
     * @return array{meta: array<string, mixed>, stimuli: list<array<string, mixed>>, items: list<array<string, mixed>>}
     */
    private function normalize(string $instrument, array $data): array
    {
        $meta    = array_diff_key($data, array_flip(['version', 'status', 'source', 'items', 'stimuli']));
        $stimuli = [];
        $items   = [];

        foreach ((array) ($data['stimuli'] ?? []) as $code => $stimulus) {
            $stimuli[] = ['code' => (string) $code, 'show' => array_diff_key((array) $stimulus, ['image' => 1]), 'image_slot' => $stimulus['image'] ?? null];
        }

        foreach ((array) $data['items'] as $code => $item) {
            $code = (string) $code;

            $items[] = match ($instrument) {
                'tes' => [
                    'code' => $code, 'part' => (string) $item['part'], 'form' => $item['form'] ?? null, 'position' => (int) $item['no'],
                    'type' => (string) $item['type'], 'stimulus' => (string) $item['stimulus'], 'show' => (array) $item['show'],
                    'key' => (array) $item['key'], 'units' => [$code], 'cell' => $item['cell'] ?? null,
                ],
                'angket' => [
                    'code' => $code, 'part' => (string) $item['part'], 'form' => null, 'position' => (int) $item['no'],
                    'type' => (string) $item['type'], 'stimulus' => null, 'show' => (array) $item['show'],
                    'key' => null, 'units' => array_values((array) $item['units']), 'cell' => null,
                ],
                default => [
                    'code' => $code, 'part' => 'sikap', 'form' => null, 'position' => (int) $item['no'],
                    'type' => 'likert4', 'stimulus' => null, 'show' => ['text' => (string) $item['text']],
                    'key' => null, 'units' => [str_replace('-', '_', $code)],
                    'cell' => array_filter(['indicator' => $item['indicator'] ?? null, 'direction' => $item['direction'] ?? null, 'example' => ! empty($item['example']) ?: null], static fn ($v): bool => $v !== null),
                ],
            };
        }

        return ['meta' => $meta, 'stimuli' => $stimuli, 'items' => $items];
    }

    /** @param array{meta: array<string, mixed>, stimuli: list<array<string, mixed>>, items: list<array<string, mixed>>} $rows */
    private function write(string $instrument, string $version, string $status, string $hash, array $rows): void
    {
        $json = static fn ($value): ?string => $value === null ? null : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->db->transStart();

        $this->db->table('instrument_versions')->insert([
            'instrument' => $instrument, 'version' => $version, 'content_hash' => $hash, 'status' => $status,
            'is_active' => 0, 'meta_json' => $json($rows['meta']), 'imported_at' => date('Y-m-d H:i:s'),
        ]);
        $versionId = (int) $this->db->insertID();
        $stimulusIds = [];

        foreach ($rows['stimuli'] as $stimulus) {
            $this->db->table('instrument_stimuli')->insert([
                'version_id' => $versionId, 'code' => $stimulus['code'], 'show_json' => $json($stimulus['show']), 'image_slot' => $stimulus['image_slot'],
            ]);
            $stimulusIds[$stimulus['code']] = (int) $this->db->insertID();
        }

        foreach ($rows['items'] as $item) {
            $this->db->table('instrument_items')->insert([
                'version_id' => $versionId, 'code' => $item['code'], 'part' => $item['part'], 'form' => $item['form'],
                'position' => $item['position'], 'type' => $item['type'],
                'stimulus_id' => $item['stimulus'] === null ? null : $stimulusIds[$item['stimulus']],
                'show_json' => $json($item['show']), 'key_json' => $json($item['key']),
                'units_json' => $json($item['units']), 'cell_json' => $json($item['cell'] === [] ? null : $item['cell']),
            ]);
        }

        $this->activate($instrument, $versionId);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Impor instrumen ' . $instrument . ' gagal ditulis.');
        }
    }

    private function activate(string $instrument, int $versionId): void
    {
        $this->db->table('instrument_versions')->where('instrument', $instrument)->update(['is_active' => 0]);
        $this->db->table('instrument_versions')->where('id', $versionId)->update(['is_active' => 1]);
    }
}
