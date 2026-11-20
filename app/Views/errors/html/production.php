<?php
/**
 * Every other client error in production (403, 405, 429, …) in the GELITA style (owner,
 * 19 November 2026; was CodeIgniter's "Whoops!" page): the shared error card with the
 * picture of the 400 page (`ui/error-400.webp`, document 40, PL-12).
 *
 * @var int|null $statusCode
 */
$kind  = '400';
$code  = (string) (isset($statusCode) && $statusCode >= 400 && $statusCode < 500 ? $statusCode : 400);
$title = lang('Game.requestErrorTitle');
$text  = lang('Game.requestErrorText');

include __DIR__ . DIRECTORY_SEPARATOR . '_gelita.php';
