<?php
/**
 * Tombol navigasi di bawah layar game.
 *
 * Halaman mengirim `$nav` sebagai daftar tombol:
 *   ['label' => lang('Game.back'), 'href' => base_url('wilayah/temanggung'), 'style' => 'quiet', 'arrow' => 'left'],
 *   ['label' => lang('Game.next'), 'href' => base_url('misi/temanggung/2'), 'style' => 'primary', 'arrow' => 'right'],
 *
 * Tombol berpanah kiri ditempatkan di sisi kiri, sisanya di kanan.
 * Layar tantangan mengirim `$nav = []` dan memakai tombol keluar berkonfirmasi.
 * `attrs` (opsional) menambah atribut `aria-*`/`data-*` pada tautan, mis.
 * tombol Pustaka wilayah yang masih terkunci (map-level.php).
 *
 * @var list<array{label: string, href: string, style?: string, arrow?: string, icon?: string, attrs?: array<string, string>}> $nav
 */
$nav   = $nav ?? [];
$start = array_values(array_filter($nav, static fn (array $b): bool => ($b['arrow'] ?? '') === 'left'));
$end   = array_values(array_filter($nav, static fn (array $b): bool => ($b['arrow'] ?? '') !== 'left'));

$button = static function (array $b): string {
    $style = in_array($b['style'] ?? 'quiet', ['primary', 'ghost', 'quiet'], true) ? $b['style'] ?? 'quiet' : 'quiet';
    $arrow = $b['arrow'] ?? '';
    $icon  = $b['icon'] ?? ($arrow === 'left' ? 'left' : ($arrow === 'right' ? 'right' : ''));
    $extra = '';

    foreach ($b['attrs'] ?? [] as $name => $value) {
        if (preg_match('/^(aria|data)-[a-z0-9-]+$/', (string) $name)) {
            $extra .= ' ' . $name . '="' . esc((string) $value, 'attr') . '"';
        }
    }

    $html = '<a class="btn btn-' . $style . ($style === 'primary' ? ' btn-lg' : '') . '" href="' . esc($b['href'], 'attr') . '"' . $extra . '>';

    if ($icon !== '' && $arrow !== 'right') {
        $html .= icon($icon);
    }

    $html .= '<span>' . esc($b['label']) . '</span>';

    if ($icon !== '' && $arrow === 'right') {
        $html .= icon($icon);
    }

    return $html . '</a>';
};
?>
<?php if ($nav !== []): ?>
  <nav class="nav-bar" aria-label="<?= esc(lang('Game.continue'), 'attr') ?>">
    <div class="nav-bar-start"><?php foreach ($start as $b): ?><?= $button($b) ?><?php endforeach ?></div>
    <div class="nav-bar-end"><?php foreach ($end as $b): ?><?= $button($b) ?><?php endforeach ?></div>
  </nav>
<?php endif ?>
