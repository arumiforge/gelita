<?php
/**
 * 500 in the GELITA style (production): the shared error card (_gelita.php) with its
 * picture `ui/error-500.webp` (document 40, PL-11) once installed. No exception message or
 * trace; only the request_id that matches the line in writable/logs/.
 */
$kind      = '500';
$code      = '500';
$title     = lang('Game.serverErrorTitle');
$text      = lang('Game.serverErrorText');
$requestId = (string) service('gelitaRequestId');

include __DIR__ . DIRECTORY_SEPARATOR . '_gelita.php';
