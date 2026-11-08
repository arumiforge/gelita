<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;
use Composer\InstalledVersions;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;

/**
 * Profil perangkat siswa untuk `device_logs` (perombakan kelas 5, Dok 1 §11).
 *
 * Sumber, berurutan:
 *  1. User-Agent + Client Hints header (`Sec-CH-UA-*`, diminta lewat
 *     App\Filters\ClientHintsFilter), diurai matomo/device-detector;
 *  2. `navigator.userAgentData.getHighEntropyValues()` dari formulir
 *     (`device_json.ua_data`) untuk melengkapi hint yang tidak ada di header;
 *  3. sinyal browser dari formulir (`device_json`: ukuran layar, sentuh, …).
 *
 * Koreksi iPad: iPadOS 13+ Safari mengaku "Macintosh"; bila OS Mac tetapi
 * layar sentuh multi-titik, perangkat dicatat sebagai tablet iPad/iPadOS.
 *
 * IP mentah tidak pernah disimpan (hanya `ip_hash`); tidak ada sidik jari
 * peramban.
 *
 * Revision 10, part 3 (29 §5.5): the User-Agent and Client Hints are read
 * to work out the OS and browser, then dropped. Brand, model, platform
 * (architecture), pixel ratio, touch points, time zone, and browser language
 * are not stored. `touch_points` stays in the returned profile only for the
 * iPad correction and `game_sessions.is_touch`; it is not a device_logs column.
 */
final class DeviceProfile
{
    /** Batas ukuran `device_json` dari formulir */
    public const CLIENT_JSON_MAX = 4096;

    /** Stored `game_sessions.package_mode` values (rencana-paket-aset §8.7). */
    public const PACKAGE_MODES = ['lengkap', 'daring', 'nonaktif'];

    /** Panjang maksimum kolom teks device_logs */
    private const LENGTHS = [
        'device_type'     => 30,
        'os_name'         => 60,
        'os_version'      => 40,
        'browser_name'    => 60,
        'browser_version' => 40,
        'browser_engine'  => 40,
        'orientation'     => 20,
        'corrected'       => 40,
        'detector'        => 40,
    ];

    /** Header Client Hints yang dibaca (dasar + entropi tinggi dari Config\Gelita) */
    private const BASE_HINTS = ['Sec-CH-UA', 'Sec-CH-UA-Mobile', 'Sec-CH-UA-Platform'];

    /** Kunci navigator.userAgentData yang diteruskan ke ClientHints::factory() */
    private const UA_DATA_KEYS = ['platform', 'platformVersion', 'fullVersionList', 'mobile', 'brands'];

    /**
     * @param array<string, mixed> $client hasil decode `device_json` dari formulir (boleh kosong)
     *
     * @return array<string, mixed> baris siap ditulis ke device_logs (tanpa session_id/participant_id/via)
     */
    public static function capture(IncomingRequest $request, array $client): array
    {
        $headers = [];

        foreach (array_unique(array_merge(self::BASE_HINTS, config('Gelita')->clientHints)) as $name) {
            $value = trim($request->getHeaderLine($name));

            if ($value !== '') {
                $headers[$name] = $value;
            }
        }

        return self::fromParts(
            (string) $request->getUserAgent()->getAgentString(),
            $headers,
            $client,
            $request->getIPAddress(),
        );
    }

    /**
     * Inti capture() tanpa objek request — dipakai juga oleh uji.
     *
     * @param array<string, string> $headers Client Hints header mentah
     * @param array<string, mixed>  $client  device_json yang sudah di-decode
     *
     * @return array<string, mixed>
     */
    public static function fromParts(string $userAgent, array $headers, array $client, ?string $ip = null): array
    {
        $uaData = isset($client['ua_data']) && is_array($client['ua_data']) ? $client['ua_data'] : [];

        // Header lebih dulu; userAgentData hanya mengisi yang kosong
        $hintInput = [];

        foreach (self::UA_DATA_KEYS as $key) {
            if (array_key_exists($key, $uaData) && (is_scalar($uaData[$key]) || is_array($uaData[$key]))) {
                $hintInput[$key] = $uaData[$key];
            }
        }

        $hintInput = array_merge($hintInput, $headers);

        $detector = new DeviceDetector($userAgent, ClientHints::factory($hintInput));
        $detector->parse();

        $os     = $detector->getOs();
        $browser = $detector->getClient();
        $os     = is_array($os) ? $os : [];
        $browser = is_array($browser) ? $browser : [];

        $row = [
            'device_type'     => $detector->getDeviceName(),
            'os_name'         => $os['name'] ?? null,
            'os_version'      => $os['version'] ?? null,
            'browser_name'    => $browser['name'] ?? null,
            'browser_version' => $browser['version'] ?? null,
            'browser_engine'  => $browser['engine'] ?? null,
            'is_bot'          => $detector->isBot() ? 1 : 0,
        ];

        $row += self::clientSignals($client);
        $row += self::packageSignals($client);

        // Koreksi iPad (iPadOS 13+ Safari ber-UA Macintosh)
        $row['corrected'] = null;

        if (in_array($row['os_name'], ['Mac', 'macOS', 'Mac OS'], true) && (int) ($row['touch_points'] ?? 0) > 1) {
            $row['device_type']  = 'tablet';
            $row['os_name']      = 'iPadOS';
            $row['os_version']   = null;   // versi macOS palsu dari UA, bukan versi iPadOS
            $row['corrected']    = 'ipad_desktop_ua';
        }

        $row['detector'] = self::detectorName();
        $row['ip_hash']  = function_exists('hash_ip') ? hash_ip($ip) : ($ip ? hash('sha256', $ip . config('Gelita')->ipSalt) : null);

        foreach (self::LENGTHS as $column => $max) {
            $row[$column] = self::text($row[$column] ?? null, $max);
        }

        return $row;
    }

    /**
     * Isi `device_json` formulir. Rusak, bukan objek, atau lebih dari 4 KB →
     * diabaikan diam-diam (server memakai User-Agent saja).
     *
     * @return array<string, mixed>
     */
    public static function decodeClient(?string $json): array
    {
        $json = trim((string) $json);

        if ($json === '' || strlen($json) > self::CLIENT_JSON_MAX) {
            return [];
        }

        try {
            $data = json_decode($json, true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($data) && ! array_is_list($data) ? $data : [];
    }

    /**
     * Ringkasan perangkat untuk kolom game_sessions (sesi baru).
     *
     * @param array<string, mixed> $profile hasil capture()
     *
     * @return array<string, mixed>
     */
    public static function sessionSummary(array $profile): array
    {
        $w = $profile['screen_w'] ?? null;
        $h = $profile['screen_h'] ?? null;

        return [
            'device_type'  => $profile['device_type'] ?? null,
            'os_name'      => $profile['os_name'] ?? null,
            'browser_name' => $profile['browser_name'] ?? null,
            'screen_size'  => $w !== null && $h !== null ? $w . 'x' . $h : null,
            'is_touch'     => isset($profile['touch_points']) ? ((int) $profile['touch_points'] > 0 ? 1 : 0) : null,
        ];
    }

    /**
     * Asset package the device runs (rencana-paket-aset §7.1, §8.7):
     * `package_code` (null when the device has no package) and
     * `package_mode` (`lengkap` | `daring` | `nonaktif`). Missing or invalid
     * values give `package_code = null` and `package_mode = null`;
     * SessionService then falls back to its default mode. These keys are not
     * device_logs columns, so DeviceLogModel::record() drops them.
     *
     * @param array<string, mixed> $client
     *
     * @return array{package_code: ?string, package_mode: ?string}
     */
    public static function packageSignals(array $client): array
    {
        $mode = $client['package_mode'] ?? null;

        return [
            'package_code' => self::word($client['package_code'] ?? null, '/^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$/'),
            'package_mode' => is_string($mode) && in_array($mode, self::PACKAGE_MODES, true) ? $mode : null,
        ];
    }

    /**
     * Sinyal layar dari browser; nilai yang bukan angka/teks wajar dibuang.
     *
     * @param array<string, mixed> $client
     *
     * @return array<string, mixed>
     */
    private static function clientSignals(array $client): array
    {
        return [
            'screen_w'      => self::number($client['screen_w'] ?? null, 1, 65535),
            'screen_h'      => self::number($client['screen_h'] ?? null, 1, 65535),
            'viewport_w'    => self::number($client['viewport_w'] ?? null, 1, 65535),
            'viewport_h'    => self::number($client['viewport_h'] ?? null, 1, 65535),
            'touch_points'  => self::number($client['touch_points'] ?? null, 0, 255),
            'orientation'   => self::word($client['orientation'] ?? null, '/^[a-z-]{3,20}$/'),
            'is_standalone' => isset($client['is_standalone']) && is_bool($client['is_standalone']) ? (int) $client['is_standalone'] : null,
        ];
    }

    private static function number(mixed $value, float $min, float $max, bool $float = false): int|float|null
    {
        if (! is_int($value) && ! is_float($value) && ! (is_string($value) && is_numeric($value))) {
            return null;
        }

        $number = (float) $value;

        if (! is_finite($number) || $number < $min || $number > $max) {
            return null;
        }

        return $float ? $number : (int) round($number);
    }

    private static function word(mixed $value, string $pattern): ?string
    {
        return is_string($value) && preg_match($pattern, $value) === 1 ? $value : null;
    }

    private static function text(mixed $value, int $max): ?string
    {
        if ($value === null || ! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        // Library memakai "UNK" untuk nilai yang tidak dikenal
        if ($value === '' || $value === 'UNK') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private static function detectorName(): string
    {
        $version = '';

        if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('matomo/device-detector')) {
            $version = (string) InstalledVersions::getPrettyVersion('matomo/device-detector');
        }

        return trim('matomo/device-detector ' . $version);
    }
}
