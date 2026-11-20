<?php
/**
 * 404 in the GELITA style: the shared error card (_gelita.php) with its picture
 * `ui/error-404.webp` (document 40, PL-10) once installed.
 */
$kind  = '404';
$code  = '404';
$title = lang('Game.notFoundTitle');
$text  = lang('Game.notFoundText');

include __DIR__ . DIRECTORY_SEPARATOR . '_gelita.php';
