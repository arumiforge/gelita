<?php

/**
 * Test students for tools/qa/load-test.mjs (docs/rencana-log-asinkron.md §7).
 *
 *   cd /var/www/gelita && sudo -u www-data php tools/qa/load-students.php 90 beban
 *
 * Registers COUNT students "{PREFIX}001"… with password Load#Gelita2026
 * through SessionService::registerAndStart(), the service behind the sign-up
 * page, so each has a participant row, an active game session, and a
 * `session_started` event. Existing usernames are skipped. Run it from the
 * app root against a test database only; never on production.
 */

$_SERVER['CI_ENVIRONMENT'] ??= 'production';

require getcwd() . '/vendor/codeigniter4/framework/system/util_bootstrap.php';

$count  = (int) ($argv[1] ?? 30);
$prefix = (string) ($argv[2] ?? 'beban');
$made   = 0;

for ($n = 1; $n <= $count; $n++) {
    $username = sprintf('%s%03d', $prefix, $n);

    if (db_connect()->table('participants')->where('username', $username)->countAllResults() > 0) {
        continue;
    }

    try {
        service('sessionService')->registerAndStart([
            'username'             => $username,
            'password'             => 'Load#Gelita2026',
            'display_name'         => 'Uji Beban ' . $n,
            'age'                  => '11',
            'gender'               => $n % 2 === 0 ? 'laki-laki' : 'perempuan',
            'class_level'          => config('Gelita')->registrationClass,
            'school_npsn'          => null,
            'school_name'          => 'SD Uji Beban',
            'country_code'         => 'ID',
            'country_name'         => 'Indonesia',
            'province_code'        => '33',
            'province_name'        => 'Jawa Tengah',
            'district_code'        => '3323',
            'district_name'        => 'Kabupaten Temanggung',
            'locale'               => 'id',
            'assent'               => true,
            'registration_metrics' => ['first_submit_criteria' => 5, 'weak_submit_count' => 0],
            'device'               => ['user_agent' => 'gelita-load-test'],
        ]);
    } catch (ErrorException $e) {
        // registerAndStart() ends by regenerating the web session id, which a
        // CLI run does not have; everything before it is already written.
        if (! str_contains($e->getMessage(), 'session_regenerate_id')) {
            throw $e;
        }
    }

    $made++;
}

echo "Students created: {$made}\n";
