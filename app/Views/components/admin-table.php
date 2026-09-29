<?php
/**
 * Tabel data panel + kondisi kosong yang menjelaskan.
 *
 * `$columns` berisi kunci kolom → label, atau kunci → definisi:
 *   ['label' => 'Skor', 'format' => 'num', 'decimals' => 2]
 * Format: text (bawaan) | num | pct (0–100) | ratio (0–1 → %) | date | datetime
 *         | ms (durasi) | bool | badge | code | json
 * `badge` menampilkan kode status sebagai label ramah (admin_label('status'))
 * dengan kelas warna dari kodenya; `label` menerjemahkan kode lewat kelompok
 * `group` (mis. ['format' => 'label', 'group' => 'phase']); `json` meringkas
 * data menjadi teks biasa ("jawaban: kopi") dan menyimpan bentuk aslinya di
 * tooltip.
 * Kolom `render` menerima closure fn(array $row): string yang mengembalikan
 * HTML yang SUDAH di-escape pemanggil (dipakai untuk tautan & tombol aksi).
 *
 * admin/tables.js menambah pengurutan client untuk tabel ≤ 500 baris dan
 * kotak saring baris untuk tabel ≥ 10 baris.
 *
 * @var array<string, string|array<string, mixed>> $columns
 * @var iterable<array<string, mixed>|object>      $rows
 * @var string|null                                $emptyMessage
 * @var string|null                                $caption      judul tabel untuk pembaca layar
 * @var Closure|null                               $rowClass     fn(array $row): string
 * @var string|null                                $id
 */
$toArray = static function ($row): array {
    if (is_array($row)) {
        return $row;
    }

    if (is_object($row) && method_exists($row, 'toArray')) {
        return $row->toArray();
    }

    return (array) $row;
};

/**
 * Data JSON → teks biasa: {"text":"kopi"} → "kopi", {"order":["c","a"]} →
 * "c, a", {"verdict":"salah","reason_text":"…"} → "salah · alasan: …".
 * Isi jawaban tampil tanpa nama kuncinya; kunci lain diberi nama Indonesia
 * bila dikenal, selain itu tampil apa adanya.
 */
$plain = static function ($data) use (&$plain): string {
    static $bare = ['text', 'verdict', 'option_key', 'order', 'answer', 'value'];
    static $names = [
        'reason_text' => 'alasan', 'item_id' => 'benda nomor', 'hint_id' => 'petunjuk nomor',
        'level_id' => 'wilayah nomor', 'node_id' => 'tantangan nomor', 'duration_ms' => 'lama (milidetik)',
        'x' => 'kiri %', 'y' => 'atas %', 'w' => 'lebar %', 'from' => 'dari', 'to' => 'ke', 'first' => 'pertama kali',
        // Isi catatan aktivitas (game_event_logs.payload_json)
        'attempt_no' => 'percobaan ke', 'item_count' => 'jumlah soal', 'check_count' => 'pemeriksaan ke',
        'correct_count' => 'benar', 'total' => 'dari', 'correct' => 'benar', 'first_pass' => 'sejak awal',
        'change_count' => 'jawaban diubah', 'score' => 'skor', 'stars' => 'bintang',
        'first_pass_accuracy' => 'tepat sejak awal (%)', 'level_score' => 'skor wilayah', 'total_score' => 'skor total',
        'total_stars' => 'jumlah bintang', 'level_code' => 'wilayah', 'code' => 'kode', 'asset_key' => 'kode berkas',
        'via' => 'lewat', 'clicked_item_id' => 'benda yang diklik nomor', 'decoy' => 'jebakan',
        'rating' => 'nilai bintang', 'open_answers' => 'isian yang diisi', 'resumed' => 'dilanjutkan',
        'attempt_open' => 'tantangan masih terbuka', 'index' => 'slide ke', 'context' => 'bagian cerita',
        'character' => 'tokoh', 'page' => 'halaman ke', 'page_id' => 'halaman nomor', 'reason' => 'alasan',
        'idle_days' => 'hari tidak aktif', 'last_active_at' => 'terakhir aktif',
    ];
    static $values = [
        'via' => [
            'register' => 'pendaftaran', 'login' => 'masuk', 'logout' => 'keluar', 'new_session' => 'sesi baru',
            'self' => 'oleh siswa sendiri', 'reset' => 'setelah sandi direset', 'user_exit' => 'tombol Keluar',
            'retention' => 'pembersihan otomatis', 'pagehide' => 'halaman ditutup', 'cli' => 'petugas teknis',
        ],
    ];

    if (is_bool($data)) {
        return $data ? 'ya' : 'tidak';
    }

    if (! is_array($data)) {
        return (string) $data;
    }

    if (array_is_list($data)) {
        return implode(', ', array_map($plain, $data));
    }

    $parts = [];

    foreach ($data as $key => $item) {
        $text    = is_scalar($item) && isset($values[$key][(string) $item]) ? $values[$key][(string) $item] : $plain($item);
        $parts[] = in_array($key, $bare, true) ? $text : ($names[$key] ?? $key) . ': ' . $text;
    }

    return implode(' · ', $parts);
};

$format = static function ($value, array $def) use ($plain): string {
    if ($value === null || $value === '') {
        return '—';
    }

    if (($def['format'] ?? 'text') === 'json') {
        $raw     = is_string($value) ? $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $text    = $decoded === null && $raw !== 'null' ? $raw : $plain($decoded);

        return '<span class="json-inline" title="' . esc($raw, 'attr') . '">' . esc($text === '' ? '—' : $text) . '</span>';
    }

    return match ($def['format'] ?? 'text') {
        'num'      => fmt_num($value, (int) ($def['decimals'] ?? 0), 'id'),
        'pct'      => fmt_pct($value, false, (int) ($def['decimals'] ?? 1)),
        'ratio'    => fmt_pct($value, true, (int) ($def['decimals'] ?? 1)),
        'date'     => fmt_date($value, false, 'id'),
        'datetime' => fmt_date($value, true, 'id'),
        'ms'       => ms_to_human((int) $value),
        'bool'     => $value ? 'ya' : 'tidak',
        'badge'    => '<span class="badge is-' . esc((string) $value, 'attr') . '">' . esc(admin_label('status', (string) $value)) . '</span>',
        'label'    => esc(admin_label((string) ($def['group'] ?? 'status'), (string) $value)),
        'code'     => '<code>' . esc((string) $value) . '</code>',
        default    => esc(is_array($value) ? $plain($value) : (string) $value),
    };
};

$defs = [];
foreach ($columns as $key => $def) {
    $defs[$key] = is_array($def) ? $def : ['label' => $def];
}

$prepared = [];
foreach ($rows ?? [] as $row) {
    $prepared[] = $toArray($row);
}
?>
<?php if ($prepared === []): ?>
  <div class="empty-state">
    <?= icon('info') ?>
    <p><?= esc($emptyMessage ?? lang('Admin.emptyDefault')) ?></p>
  </div>
<?php else: ?>
  <div class="table-wrap"<?= isset($id) ? ' id="' . esc($id, 'attr') . '"' : '' ?>>
    <table class="data-table" data-sortable="<?= count($prepared) <= 500 ? 'client' : 'server' ?>">
      <?php if (! empty($caption)): ?>
        <caption class="visually-hidden"><?= esc($caption) ?></caption>
      <?php endif ?>
      <thead>
        <tr>
          <?php foreach ($defs as $def): ?>
            <th scope="col" class="<?= in_array($def['format'] ?? '', ['num', 'pct', 'ratio', 'ms'], true) ? 'is-num' : '' ?><?= isset($def['render']) && ($def['label'] ?? '') === '' ? ' is-actions' : '' ?>">
              <?= esc($def['label'] ?? '') ?>
            </th>
          <?php endforeach ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($prepared as $row): ?>
          <tr class="<?= isset($rowClass) && $rowClass instanceof Closure ? esc($rowClass($row), 'attr') : '' ?>">
            <?php foreach ($defs as $key => $def): ?>
              <?php $numeric = in_array($def['format'] ?? '', ['num', 'pct', 'ratio', 'ms'], true); ?>
              <td class="<?= $numeric ? 'is-num' : '' ?><?= isset($def['render']) && ($def['label'] ?? '') === '' ? ' is-actions' : '' ?>">
                <?= isset($def['render']) && $def['render'] instanceof Closure ? $def['render']($row) : $format($row[$key] ?? null, $def) ?>
              </td>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
<?php endif ?>
