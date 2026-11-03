<?php
// Dumps the data files of one directory as JSON (input for import_08.py).
$dir = $argv[1] ?? __DIR__ . '/../data';
$out = [];
foreach (['temanggung', 'magelang', 'wonosobo'] as $region) {
    $out[$region] = (static fn (string $p): array => require $p)("{$dir}/{$region}.php");
}
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
