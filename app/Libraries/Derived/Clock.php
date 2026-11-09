<?php

namespace App\Libraries\Derived;

/**
 * Times of the derived layer (35 §6.7): stored WIB times as milliseconds
 * since the epoch, and back. Pure: the zone is fixed (Asia/Jakarta, the app
 * and database zone), never read from configuration.
 */
final class Clock
{
    public const ZONE = 'Asia/Jakarta';

    /** "2026-11-10 08:00:00.250" (WIB) or ISO 8601 → ms; null for an empty value. */
    public static function ms(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        try {
            $time = new \DateTimeImmutable((string) $value, new \DateTimeZone(self::ZONE));
        } catch (\Exception) {
            return null;
        }

        return (int) $time->format('U') * 1000 + (int) $time->format('v');
    }

    /** ms → "Y-m-d H:i:s.v" in WIB (the stored form); null stays null. */
    public static function stored(?int $ms): ?string
    {
        if ($ms === null) {
            return null;
        }

        $seconds = intdiv($ms, 1000);
        $milli   = $ms - $seconds * 1000;

        return (new \DateTimeImmutable('@' . $seconds))->setTimezone(new \DateTimeZone(self::ZONE))->format('Y-m-d H:i:s') . sprintf('.%03d', $milli);
    }

    /** End of a WIB day "Y-m-d" (23:59:59.999) in ms. */
    public static function endOfDay(string $date): ?int
    {
        $start = self::ms(substr($date, 0, 10) . ' 00:00:00');

        return $start === null ? null : $start + 86_400_000 - 1;
    }
}
