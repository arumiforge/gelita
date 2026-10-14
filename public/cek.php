<?php
header('Content-Type: text/plain');
echo 'HOST  = ' . ($_SERVER['HTTP_HOST'] ?? '-') . "\n";
echo 'HTTPS = ' . ($_SERVER['HTTPS'] ?? '(kosong)') . "\n";
echo 'XFP   = ' . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '(kosong)') . "\n";