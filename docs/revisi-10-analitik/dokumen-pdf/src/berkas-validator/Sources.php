<?php

declare(strict_types=1);

namespace BerkasValidator;

use RuntimeException;

/**
 * Parses the item documents (03, 08, 14–18) into plain arrays.
 *
 * Item:  code, source, part (bank|tes), type, block, text, options, key,
 *        dim, sub, level, bloom, line.
 * Block: key, title, text, source (the stimulus a group of items shares).
 */
final class Sources
{
    /** @var array<string, array> */
    public array $items = [];
    /** @var array<string, array> */
    public array $blocks = [];
    /** @var list<array> rows for B.7: ref, text, code, origin, item */
    public array $errorCells = [];
    /** @var array<string, array> Skenario Digital scripts by code */
    public array $scenarios = [];
    /** @var array<string, array> Periksa Unggahan clues by code */
    public array $clues = [];
    /** @var array<string, array> Temukan Benda (PBK) by code */
    public array $pbk = [];
    /** @var array<string, int> counts from the "Jumlah" table of 08 */
    public array $bankTotals = [];
    /** @var array<string, array{text: string, indicator: string, direction: string}> */
    public array $attitude = [];
    /** @var array<string, string> */
    public array $survey = [];
    /** @var array<string, string> mock-up code => state */
    public array $mockups = [];
    /** @var array<string, string> 03: code => meaning, for every coded table */
    public array $codes03 = [];
    /** @var array<string, string> D1, D2, D3 => definition */
    public array $dimensions = [];
    /** @var array<string, array{a: string, b: string}> test pair titles: M1, G1, ae01 */
    public array $testPairs = [];

    public function __construct(private readonly string $dir)
    {
        $this->parse03();
        $this->parse08();
        $this->parse14();
        $this->parse15();
        $this->parse16();
        $this->parse17();
        $this->parse18();
    }

    /** @return list<string> source files read, for the version hash */
    public function files(): array
    {
        return array_map(fn ($f) => $this->dir . '/' . $f, [
            '03_KODE_DAN_ARTI.md', '08_DRAF_BANK_SOAL.md', '14_TES_BAGIAN_MEMBACA.md',
            '15_TES_BAGIAN_DIGITAL.md', '16_ANGKET_AKSES_DIGITAL.md', '17_SKALA_SIKAP.md',
            '18_MOCKUP_LAYAR_BARU.md',
        ]);
    }

    private function path(string $file): string
    {
        return $this->dir . '/' . $file;
    }

    // ------------------------------------------------------------------ 03

    private function parse03(): void
    {
        foreach (Markdown::blocks($this->path('03_KODE_DAN_ARTI.md')) as $b) {
            if ($b['type'] !== 'table' || ($b['head'][0] ?? '') !== 'Kode') {
                continue;
            }
            foreach ($b['rows'] as $r) {
                $this->codes03[Markdown::plain($r[0])] = $r[1] ?? '';
                if (preg_match('/^D[123]$/', $r[0])) {
                    $this->dimensions[$r[0]] = Markdown::plain($r[1]);
                }
            }
        }
        if (count($this->dimensions) !== 3) {
            throw new RuntimeException('03: dimension table D1–D3 not found');
        }
    }

    /** Expands a 03 range like "mk-sd-01 … 05" or "ang-01 … ang-17". @return list<string> */
    public function range03(string $prefix): array
    {
        $out = [];
        foreach (array_map('strval', array_keys($this->codes03)) as $code) {
            if (! str_starts_with($code, $prefix)) {
                continue;
            }
            if (preg_match('/^(.*?)(\d+)\s*…\s*(?:\1)?(\d+)$/u', $code, $m)) {
                for ($i = (int) $m[2]; $i <= (int) $m[3]; $i++) {
                    $out[] = $m[1] . str_pad((string) $i, strlen($m[2]), '0', STR_PAD_LEFT);
                }
            } elseif (preg_match('/^[\w-]+$/u', $code)) {
                $out[] = $code;
            }
        }

        return $out;
    }

    // ------------------------------------------------------------------ 08

    private const LEVEL_FROM_TIER = [
        // 08 "Tingkat" line → padanan level of the instrument (B.5a: L1 jelas, L2 samar, L3 bertentangan/dua langkah)
        'jelas' => 'L1', 'samar' => 'L2', 'bertentangan' => 'L3', 'dua langkah' => 'L3',
    ];

    private function parse08(): void
    {
        $file   = '08_DRAF_BANK_SOAL.md';
        $all    = Markdown::blocks($this->path($file));
        foreach ($all as $i => $b) {
            if ($b['type'] === 'h' && $b['text'] === 'Jumlah') {
                $t = $all[$i + 1];
                foreach ($t['rows'] as $r) {
                    $this->bankTotals[Markdown::plain($r[0])] = (int) Markdown::plain(end($r));
                }
            }
        }
        $blocks = Markdown::blocks($this->path($file), 'Temanggung', 'Pemeriksaan terhadap kisi-kisi');

        $challenge = null;   // e.g. tmg-2
        $variant   = null;   // block key
        $title     = '';
        $instr     = '';
        $tier      = '';
        $bloomRule = '';
        $babak     = 0;
        $pending   = [];     // quotes and paragraphs of the current variant
        $scenario  = null;
        $parts     = [];

        $flushScenario = function () use (&$scenario) {
            if ($scenario !== null) {
                $this->scenarios[$scenario['code']] = $scenario;
                $scenario = null;
            }
        };

        foreach ($blocks as $b) {
            if ($b['type'] === 'h') {
                if ($b['level'] === 2 && preg_match('/^`([a-z]{3}-\d)` (.*)$/u', $b['text'], $m)) {
                    $flushScenario();
                    $challenge = $m[1];
                    $instr = $tier = $bloomRule = '';
                    $babak = 0;
                    $variant = null;
                    $pending = [];
                } elseif ($challenge !== null && preg_match('/^Variasi ([ab])(?::\s*(.*))?$/u', $b['text'], $m)) {
                    $flushScenario();
                    $variant = $challenge . '-' . $m[1];
                    $title   = $m[2] ?? '';
                    $pending = [];
                    $parts   = [];
                } elseif (str_starts_with($b['text'], 'Kartu jangkar')) {
                    $variant = $challenge . '-x';
                    $title   = $b['text'];
                    $pending = [];
                } elseif (str_starts_with($b['text'], 'Babak 1')) {
                    $babak = 1;
                } elseif (str_starts_with($b['text'], 'Babak 2')) {
                    $babak = 2;
                } elseif ($b['level'] === 1) {
                    $challenge = null;
                }
                continue;
            }
            if ($challenge === null) {
                continue;
            }
            $kind = (int) substr($challenge, 4, 1);

            if ($b['type'] === 'p') {
                if (preg_match('/^\*\*Instruksi:\*\*\s*"?(.*?)"?$/mu', $b['text'], $m)) {
                    $instr = $m[1];
                }
                if (preg_match('/^\*\*Tingkat:\*\*\s*(.*)$/mu', $b['text'], $m)) {
                    $tier = $m[1];
                }
                if (preg_match('/^\*\*Bloom:\*\*\s*(.*)$/mu', $b['text'], $m)) {
                    $bloomRule = $m[1];
                }
                if ($kind === 5 && preg_match('/^\*\*([a-z]{3}-5-[ab]\d\d) · ([^*]+?)\.\*\*\s*(.*)$/su', $b['text'], $m)) {
                    $flushScenario();
                    $scenario = [
                        'code' => $m[1], 'topic' => trim($m[2]), 'situation' => Markdown::plain($m[3]),
                        'actions' => [], 'explain' => '', 'reasons' => [], 'line' => $b['line'],
                        'instr' => $instr, 'tier' => $tier, 'bloomRule' => $bloomRule,
                    ];
                } elseif ($scenario !== null && preg_match('/^\*\*Penjelasan setelah kesempatan habis:\*\*\s*(.*)$/su', $b['text'], $m)) {
                    $scenario['explain'] = Markdown::plain($m[1]);
                } elseif ($variant !== null) {
                    $pending[] = $b;
                }
                continue;
            }
            if ($b['type'] === 'quote') {
                $pending[] = $b;
                continue;
            }
            // tables
            $head = $b['head'];
            if ($kind === 2 && $head[0] === 'Kode' && in_array('Rumpang', $head, true)) {
                $this->addCloze($challenge, $variant, $title, $instr, $pending, $b, $file);
            } elseif ($kind === 3 && $head[0] === 'Kode' && in_array('Pernyataan', $head, true)) {
                $this->addStatements($challenge, $variant, $title, $instr, $pending, $b, $file);
            } elseif ($kind === 4 && $babak === 1 && $head[0] === 'Kode') {
                foreach ($b['rows'] as $r) {
                    $this->pbk[$r[0]] = ['code' => $r[0], 'clue' => Markdown::plain($r[1]), 'key' => Markdown::plain($r[2]), 'bloom' => $r[3]];
                }
            } elseif ($kind === 4 && $babak === 2 && $head[0] === 'No') {
                $parts = [];
                foreach ($b['rows'] as $r) {
                    $parts[$r[0]] = ['label' => Markdown::plain($r[1]), 'content' => Markdown::plain($r[2])];
                }
            } elseif ($kind === 4 && $babak === 2 && $head[0] === 'Kode') {
                $this->addClues($challenge, $variant, $title, $instr, $tier, $pending, $parts, $b, $file);
            } elseif ($kind === 5 && $scenario !== null && $head[0] === 'Tindakan') {
                foreach ($b['rows'] as $r) {
                    $scenario['actions'][] = ['text' => Markdown::plain($r[0]), 'score' => (int) $r[1], 'code' => $r[2], 'feedback' => Markdown::plain($r[3])];
                }
            } elseif ($kind === 5 && $scenario !== null && $head[0] === 'Alasan') {
                foreach ($b['rows'] as $r) {
                    $scenario['reasons'][] = ['text' => Markdown::plain($r[0]), 'code' => $r[1]];
                }
            }
        }
        $flushScenario();

        foreach ($this->scenarios as $s) {
            $this->addScenarioItem($s, $file);
        }
    }

    private static function stimulus(string $instr, array $pending): string
    {
        $parts = [];
        foreach ($pending as $p) {
            if ($p['type'] === 'quote') {
                $parts[] = Markdown::quoteText($p['text']);
            } elseif (str_starts_with($p['text'], '**Adegan.**')) {
                $parts[] = Markdown::plain($p['text']);
            }
        }

        return trim(($instr !== '' ? 'Instruksi: "' . $instr . "\"\n\n" : '') . implode("\n\n", $parts));
    }

    private function addBlock(string $key, string $title, string $text, string $source): void
    {
        $this->blocks[$key] = ['key' => $key, 'title' => $title, 'text' => $text, 'source' => $source];
    }

    private function addItem(array $item): void
    {
        if (isset($this->items[$item['code']])) {
            throw new RuntimeException("Duplicate item code {$item['code']}");
        }
        $this->items[$item['code']] = $item + ['sub' => '—', 'bloom' => '—', 'levelNote' => ''];
    }

    private function addCloze(string $ch, ?string $variant, string $title, string $instr, array $pending, array $table, string $file): void
    {
        $meta = '';
        foreach ($pending as $p) {
            if ($p['type'] === 'p' && str_contains($p['text'], 'Pengecoh bank kata')) {
                $meta = $p['text'];
            }
        }
        preg_match('/Pengecoh bank kata:\s*(.*?)(?:\s·\s|$)/u', $meta, $m);
        preg_match_all('/\*([^*]+)\*/u', $m[1] ?? '', $mm);
        $decoys = $mm[1];
        if (count($decoys) !== 2) {
            throw new RuntimeException("08 {$variant}: expected 2 word-bank decoys, found " . count($decoys));
        }
        $special = [];   // word => [gap => code]
        if (preg_match('/Kode khusus:\s*(.*)$/u', $meta, $k)) {
            foreach (preg_split('/;\s*/u', $k[1]) as $rule) {
                if (preg_match('/\*([^*]+)\*\s+di rumpang\s+(.*?)\s*→\s*([A-Z]+-[A-Z]+)/u', $rule, $r)) {
                    preg_match_all('/\((\d+)\)/u', $r[2], $g);
                    foreach ($g[1] as $gap) {
                        $special[$r[1]][(int) $gap] = $r[3];
                    }
                }
            }
        }
        $h    = $table['head'];
        $rows = $table['rows'];
        $answers = array_map(fn ($r) => Markdown::plain($r[Markdown::col($h, 'Kunci')]), $rows);
        $bank = array_merge($answers, $decoys);
        $sorted = $bank;
        usort($sorted, 'strcasecmp');

        $text = self::stimulus($instr, $pending);
        $text = preg_replace('/\((\d+)\)/u', '($1) ……', $text);
        $this->addBlock($variant, $title, $text, $file);

        foreach ($rows as $r) {
            $code = $r[0];
            $gap  = (int) trim($r[Markdown::col($h, 'Rumpang')], '() ');
            $key  = Markdown::plain($r[Markdown::col($h, 'Kunci')]);
            $this->addItem([
                'code' => $code, 'source' => $file, 'part' => 'bank', 'type' => 'rumpang', 'block' => $variant,
                'text' => "Rumpang ({$gap})", 'options' => 'Bank kata: ' . implode(' · ', $sorted), 'key' => $key,
                'dim' => 'D1', 'sub' => $r[Markdown::col($h, 'Sub')], 'level' => $r[Markdown::col($h, 'AKM')],
                'bloom' => $r[Markdown::col($h, 'Bloom')], 'line' => $table['line'],
            ]);
            // B.7: every wrong word in this gap gets one code (08 "Aturan kode di Isi Rumpang").
            $target = $r[Markdown::col($h, 'Pengecoh sasaran')];
            $targetCodes = [];
            if (preg_match_all('/\*([^*]+)\*\s*→\s*([A-Z]+-[A-Z]+)/u', $target, $tm, PREG_SET_ORDER)) {
                foreach ($tm as $t) {
                    $targetCodes[$t[1]] = $t[2];
                }
            }
            foreach ($bank as $word) {
                if ($word === $key) {
                    continue;
                }
                if (in_array($word, $answers, true)) {
                    [$c, $o] = ['INF-REL', 'aturan 1: jawaban rumpang lain'];
                } elseif (isset($special[$word][$gap])) {
                    [$c, $o] = [$special[$word][$gap], 'kode khusus'];
                } elseif (isset($targetCodes[$word])) {
                    [$c, $o] = [$targetCodes[$word], 'aturan 2: pengecoh sasaran (tabel)'];
                } else {
                    [$c, $o] = ['INF-LIT', 'aturan 3: pengecoh di rumpang lain'];
                }
                $this->errorCells[] = ['ref' => "{$code} · {$word}", 'text' => "Rumpang ({$gap}) diisi \"{$word}\" (kunci: {$key})", 'code' => $c, 'origin' => $o, 'item' => $code];
            }
            foreach (array_keys($targetCodes) as $w) {
                if (! in_array($w, $decoys, true)) {
                    throw new RuntimeException("08 {$code}: target decoy '{$w}' is not in the word bank");
                }
            }
        }
    }

    private function addStatements(string $ch, ?string $variant, string $title, string $instr, array $pending, array $table, string $file): void
    {
        $h = $table['head'];
        $this->addBlock($variant, $title, self::stimulus($instr, $pending), $file);
        $anchor = str_ends_with((string) $variant, '-x');
        foreach ($table['rows'] as $r) {
            $code = $r[0];
            $text = Markdown::plain($r[Markdown::col($h, 'Pernyataan')]);
            $this->addItem([
                'code' => $code, 'source' => $file, 'part' => 'bank', 'type' => $anchor ? 'jangkar' : 'boleh', 'block' => $variant,
                'text' => $text, 'options' => 'BENAR · SALAH', 'key' => Markdown::plain($r[Markdown::col($h, 'Kunci')]),
                'dim' => 'D1', 'sub' => $r[Markdown::col($h, 'Sub')], 'level' => $r[Markdown::col($h, 'AKM')],
                'bloom' => $r[Markdown::col($h, 'Bloom')], 'line' => $table['line'],
            ]);
            $this->errorCells[] = ['ref' => $code, 'text' => $text, 'code' => $r[Markdown::col($h, 'Kode salah')], 'origin' => 'tabel', 'item' => $code];
        }
    }

    private static function tierLevel(string $tier): string
    {
        foreach (array_reverse(self::LEVEL_FROM_TIER, true) as $word => $level) {
            if (str_contains($tier, $word)) {
                return $level;
            }
        }
        throw new RuntimeException("Cannot map 08 'Tingkat' line to a level: {$tier}");
    }

    private const CLUE_TYPE_CODE = [
        'Atribusi' => 'BUD-ATR', 'Representasi' => 'BUD-REP', 'Izin' => 'BUD-IZN', 'Menghargai tradisi' => 'BUD-ETK',
    ];

    private function addClues(string $ch, ?string $variant, string $title, string $instr, string $tier, array $pending, array $parts, array $table, string $file): void
    {
        if ($parts === []) {
            throw new RuntimeException("08 {$variant}: tap-part table missing");
        }
        // Neutral part names: the 08 letters (A, B, C, …) would show the variant.
        $neutral = [];
        $lines   = [];
        $n       = 0;
        foreach ($parts as $id => $p) {
            $neutral[$id] = 'Bagian ' . (++$n);
            $lines[] = $neutral[$id] . ' (' . $p['label'] . '): ' . $p['content'];
        }
        $text = self::stimulus($instr, $pending);
        $this->addBlock($variant, $title, trim($text . "\n\n" . implode("\n", $lines)), $file);

        $h = $table['head'];
        foreach ($table['rows'] as $r) {
            $code  = $r[0];
            $type  = $r[Markdown::col($h, 'Jenis')];
            $keyId = $r[Markdown::col($h, 'Kunci')];
            if (! isset($parts[$keyId])) {
                throw new RuntimeException("08 {$code}: key {$keyId} is not a tap part");
            }
            $decoys = [];
            foreach (preg_split('/;\s*/u', $r[Markdown::col($h, 'Pengecoh utama')]) as $d) {
                if (preg_match('/^(\w+)\s*→\s*([A-Z]+-[A-Z]+)$/u', trim($d), $m)) {
                    $decoys[$m[1]] = $m[2];
                }
            }
            $clue = Markdown::plain($r[Markdown::col($h, 'Petunjuk')]);
            $this->clues[$code] = [
                'code' => $code, 'type' => $type, 'clue' => $clue, 'key' => $keyId,
                'keyText' => $parts[$keyId]['label'] . ': ' . $parts[$keyId]['content'], 'decoys' => $decoys, 'parts' => $parts,
            ];
            $this->addItem([
                'code' => $code, 'source' => $file, 'part' => 'bank', 'type' => 'unggahan', 'block' => $variant,
                'text' => $clue, 'options' => 'Ketuk satu bagian: Bagian 1–' . count($parts), 'key' => $neutral[$keyId],
                'dim' => 'D3', 'level' => self::tierLevel($tier), 'levelNote' => "08 Tingkat: {$tier}",
                'bloom' => $r[Markdown::col($h, 'Bloom')], 'line' => $table['line'],
            ]);
            $typeCode = self::CLUE_TYPE_CODE[$type] ?? throw new RuntimeException("08 {$code}: unknown clue type {$type}");
            foreach ($parts as $id => $p) {
                if ($id === $keyId) {
                    continue;
                }
                [$c, $o] = isset($decoys[$id]) ? [$decoys[$id], 'pengecoh utama (tabel)'] : [$typeCode, 'aturan: kode jenis petunjuk'];
                $this->errorCells[] = ['ref' => "{$code} · {$id}", 'text' => "{$id} {$p['label']}: {$p['content']}", 'code' => $c, 'origin' => $o, 'item' => $code];
            }
        }
    }

    private function addScenarioItem(array $s, string $file): void
    {
        $actions = array_column($s['actions'], 'text');
        sort($actions, SORT_STRING | SORT_FLAG_CASE);
        $reasons = array_column($s['reasons'], 'text');
        sort($reasons, SORT_STRING | SORT_FLAG_CASE);
        $best = array_values(array_filter($s['actions'], fn ($a) => $a['score'] === 2));
        $mid  = array_values(array_filter($s['actions'], fn ($a) => $a['score'] === 1));
        if (count($best) !== 1 || count($mid) !== 1) {
            throw new RuntimeException("08 {$s['code']}: needs exactly one action scored 2 and one scored 1");
        }
        $expected = array_column(array_filter($s['reasons'], fn ($r) => in_array($r['code'], ['R-RISK', 'R-ETIK'], true)), 'text');

        // Bloom: "C3 untuk …" or "C4, kecuali `x` dan `y` C5"
        $bloom = '';
        if (preg_match('/^(C\d)/u', $s['bloomRule'], $m)) {
            $bloom = $m[1];
            if (preg_match('/kecuali (.*) (C\d)/u', $s['bloomRule'], $e) && str_contains($e[1], $s['code'])) {
                $bloom = $e[2];
            }
        }
        $block = $s['code'];
        $this->addBlock($block, '', trim('Instruksi: "' . $s['instr'] . "\"\n\n" . $s['situation']), $file);
        $this->addItem([
            'code' => $s['code'], 'source' => $file, 'part' => 'bank', 'type' => 'skenario', 'block' => $block,
            'text' => "Langkah 1 – pilih tindakan. Langkah 2 – \"Yang paling penting kupikirkan di sini adalah …\"",
            'options' => "Tindakan: " . implode(' · ', $actions) . "\nAlasan: " . implode(' · ', $reasons),
            'key' => 'Tindakan paling tepat: ' . $best[0]['text'] . '. Kurang tepat: ' . $mid[0]['text']
                . '. Alasan yang diharapkan: ' . implode(' / ', $expected) . '.',
            'dim' => 'D2', 'level' => self::tierLevel($s['tier']), 'levelNote' => "08 Tingkat: {$s['tier']}",
            'bloom' => $bloom, 'line' => $s['line'],
        ]);
        foreach ($s['actions'] as $a) {
            $this->errorCells[] = ['ref' => "{$s['code']} · tindakan (skor {$a['score']})", 'text' => $a['text'], 'code' => $a['code'], 'origin' => 'tabel tindakan', 'item' => $s['code']];
        }
        foreach ($s['reasons'] as $r) {
            $this->errorCells[] = ['ref' => "{$s['code']} · alasan", 'text' => $r['text'], 'code' => $r['code'], 'origin' => 'tabel alasan', 'item' => $s['code']];
        }
    }

    // ------------------------------------------------------------------ 14

    private function parse14(): void
    {
        $file   = '14_TES_BAGIAN_MEMBACA.md';
        $blocks = Markdown::blocks($this->path($file), 'M1 ', 'Kesetaraan pasangan');
        $textCode = null;
        $title = '';
        $quotes = [];
        $section = '';
        foreach ($blocks as $b) {
            if ($b['type'] === 'h') {
                if ($b['level'] === 2 && preg_match('/^(M\d)/u', $b['text'], $m)) {
                    $section = $m[1];
                }
                if (preg_match('/^Bentuk ([AB]):\s*(.*)$/u', $b['text'], $m)) {
                    $title = $m[2];
                    $this->testPairs[$section][$m[1]] = $m[2];
                    $textCode = null;
                    $quotes = [];
                }
                continue;
            }
            if ($b['type'] === 'p' && preg_match('/^Kode teks `(tes-mb-[AB]-M\d)`/u', $b['text'], $m)) {
                $textCode = $m[1];
                continue;
            }
            if ($b['type'] === 'quote' && $textCode !== null) {
                $quotes[] = Markdown::quoteText($b['text']);
                continue;
            }
            if ($b['type'] === 'table' && $textCode !== null && $b['head'][0] === 'Kode') {
                $this->addBlock($textCode, $title, implode("\n\n", $quotes), $file);
                $h = $b['head'];
                foreach ($b['rows'] as $r) {
                    $cell = array_map('trim', explode('·', $r[Markdown::col($h, 'Sel')]));
                    $this->addItem([
                        'code' => $r[0], 'source' => $file, 'part' => 'tes', 'type' => 'tes-mb', 'block' => $textCode,
                        'text' => Markdown::plain($r[Markdown::col($h, 'Pokok soal')]), 'options' => '',
                        'key' => Markdown::plain($r[Markdown::col($h, 'Kunci')]),
                        'dim' => 'D1', 'sub' => $cell[0], 'level' => $cell[1], 'format' => $cell[2] ?? '', 'line' => $b['line'],
                    ]);
                }
                $textCode = null;
            }
        }
    }

    // ------------------------------------------------------------------ 15

    private function parse15(): void
    {
        $file   = '15_TES_BAGIAN_DIGITAL.md';
        $blocks = Markdown::blocks($this->path($file), 'Bagian 2 Aman dan etis', 'Kesetaraan pasangan');
        $tierByNo = [];
        $code = null;
        $scene = null;
        $topic = '';
        $buf = [];
        $bdItem = null;
        $sceneLevel = '';
        foreach ($blocks as $b) {
            if ($b['type'] === 'h') {
                if (preg_match('/^Nomor (\d) · (.*?) · (.*)$/u', $b['text'], $m)) {
                    $topic = $m[2];
                    $this->testPairs['ae' . str_pad($m[1], 2, '0', STR_PAD_LEFT)] = ['A' => $m[2], 'B' => $m[2]];
                } elseif (preg_match('/^`(tes-ae-[AB]\d\d)`$/u', $b['text'], $m)) {
                    $code = $m[1];
                    $scene = null;
                    $buf = [];
                } elseif (preg_match('/^`(tes-bd-([AB])-(G\d))` · (.*)$/u', $b['text'], $m)) {
                    $scene = $m[1];
                    $code = null;
                    $buf = [];
                    $this->testPairs[$m[3]][$m[2]] = $m[4];
                    $sceneLevel = $m[3] === 'G1' ? 'L1–L2' : 'L2–L3';
                } elseif (preg_match('/^Adegan G\d/u', $b['text'])) {
                    $code = null;
                }
                continue;
            }
            if ($b['type'] === 'table' && $b['head'][0] === 'No' && in_array('Kerumitan', $b['head'], true)) {
                foreach ($b['rows'] as $r) {
                    $tierByNo[str_pad($r[0], 2, '0', STR_PAD_LEFT)] = $r[Markdown::col($b['head'], 'Kerumitan')];
                }
                continue;
            }
            if ($code !== null) {
                if ($b['type'] === 'p') {
                    $buf[] = $b['text'];
                } elseif ($b['type'] === 'table' && $b['head'][0] === 'Huruf') {
                    $situation = $screen = $question = '';
                    foreach ($buf as $p) {
                        if (preg_match('/^\*\*Situasi[^*]*\*\*\s*(.*)$/su', $p, $m)) {
                            $situation = Markdown::plain($m[1]);
                        } elseif (preg_match('/^\*\*Layar tiruan\.\*\*\s*(.*)$/su', $p, $m)) {
                            $screen = Markdown::plain($m[1]);
                        } elseif (preg_match('/^\*\*Pertanyaan:\*\*\s*(.*)$/su', $p, $m)) {
                            $question = Markdown::plain($m[1]);
                        }
                    }
                    [$options, $key] = self::letteredOptions($b, 'Tindakan');
                    $no = substr($code, -2);
                    $tier = $tierByNo[$no] ?? throw new RuntimeException("15 {$code}: no Kerumitan row");
                    $this->addBlock($code, $topic, $situation . "\n\nGambaran layar (untuk validator): " . $screen, $file);
                    $this->addItem([
                        'code' => $code, 'source' => $file, 'part' => 'tes', 'type' => 'tes-ae', 'block' => $code,
                        'text' => $question, 'options' => $options, 'key' => $key,
                        'dim' => 'D2', 'level' => str_starts_with($tier, 'Jelas') ? 'L1–L2' : 'L2–L3',
                        'levelNote' => "15 Kerumitan: {$tier} (padanan di 13)", 'line' => $b['line'],
                    ]);
                    $code = null;
                }
                continue;
            }
            if ($scene !== null) {
                if ($b['type'] === 'p' && preg_match('/^\*\*Pengantar untuk siswa\*\*[^:]*:\s*(.*)$/su', $b['text'], $m)) {
                    $buf['intro'] = Markdown::plain($m[1]);
                } elseif ($b['type'] === 'table' && $b['head'][0] === 'Unsur') {
                    $lines = [];
                    foreach ($b['rows'] as $r) {
                        $lines[] = Markdown::plain($r[0]) . ': ' . Markdown::plain($r[1]);
                    }
                    $this->addBlock($scene, $this->testPairs[substr($scene, -2)][substr($scene, 7, 1)], trim(($buf['intro'] ?? '') . "\n\n" . implode("\n", $lines)), $file);
                } elseif ($b['type'] === 'p' && preg_match('/^\*\*`(tes-bd-[AB]\d\d)`[^*]*\*\*\s*\n?(.*)$/su', $b['text'], $m)) {
                    $bdItem = ['code' => $m[1], 'question' => Markdown::plain($m[2])];
                } elseif ($b['type'] === 'table' && $b['head'][0] === 'Huruf' && $bdItem !== null) {
                    [$options, $key] = self::letteredOptions($b, 'Pilihan');
                    $this->addItem([
                        'code' => $bdItem['code'], 'source' => $file, 'part' => 'tes', 'type' => 'tes-bd', 'block' => $scene,
                        'text' => $bdItem['question'], 'options' => $options, 'key' => $key,
                        'dim' => 'D3', 'level' => $sceneLevel, 'levelNote' => 'Kerumitan adegan ' . substr($scene, -2) . ' (padanan di 13)', 'line' => $b['line'],
                    ]);
                    $bdItem = null;
                }
            }
        }
    }

    /** Options "A. … · B. …" and the key from a Huruf table (key row is bold). */
    private static function letteredOptions(array $table, string $textCol): array
    {
        $h = $table['head'];
        $opts = [];
        $key = '';
        $partial = '';
        foreach ($table['rows'] as $r) {
            $letter = Markdown::plain($r[0]);
            $text   = Markdown::plain($r[Markdown::col($h, $textCol)]);
            $opts[] = "{$letter}. {$text}";
            if (str_contains($r[0], '**')) {
                $key = $letter;
            }
            $si = array_search('Skor', $h, true);
            if ($si !== false && trim(Markdown::plain($r[$si])) === '1') {
                $partial = $letter;
            }
        }
        if ($key === '') {
            throw new RuntimeException('Lettered option table without a bold key row at line ' . $table['line']);
        }

        return [implode("\n", $opts), $key . ($partial !== '' ? " (skor 1: {$partial})" : '')];
    }

    // ------------------------------------------------------------------ 16–18

    private function parse16(): void
    {
        $code = null;
        foreach (Markdown::blocks($this->path('16_ANGKET_AKSES_DIGITAL.md')) as $b) {
            if ($b['type'] === 'h' && preg_match('/^(ang-\d\d) /u', $b['text'], $m)) {
                $code = $m[1];
            } elseif ($b['type'] === 'quote' && $code !== null) {
                $this->survey[$code] = Markdown::plain($b['text']);
                $code = null;
            } elseif ($b['type'] === 'table' && $b['head'][0] === 'Kode') {
                foreach ($b['rows'] as $r) {
                    if (preg_match('/^ang-2\d$/u', $r[0])) {
                        $this->survey[$r[0]] = Markdown::plain($r[1]);
                    }
                }
            }
        }
    }

    private function parse17(): void
    {
        foreach (Markdown::blocks($this->path('17_SKALA_SIKAP.md'), 'Butir', 'Format jawaban') as $b) {
            if ($b['type'] === 'table' && ($b['head'][1] ?? '') === 'Kode') {
                $h = $b['head'];
                foreach ($b['rows'] as $r) {
                    $this->attitude[$r[1]] = [
                        'text' => Markdown::plain($r[Markdown::col($h, 'Pernyataan')]),
                        'indicator' => $r[Markdown::col($h, 'Indikator')],
                        'direction' => $r[Markdown::col($h, 'Arah')],
                    ];
                }
            }
        }
    }

    private function parse18(): void
    {
        foreach (Markdown::blocks($this->path('18_MOCKUP_LAYAR_BARU.md'), 'Layar per layar', 'Untuk instrumen media') as $b) {
            if ($b['type'] === 'table' && $b['head'][0] === 'Gambar') {
                foreach ($b['rows'] as $r) {
                    if (preg_match('/`(mk-[a-z]{2}-\d\d)`/u', $r[0], $m)) {
                        $this->mockups[$m[1]] = Markdown::plain($r[1]);
                    }
                }
            }
        }
    }

    /** @return list<array> items of one part in document order */
    public function itemsOf(string $part): array
    {
        return array_values(array_filter($this->items, fn ($i) => $i['part'] === $part));
    }
}
