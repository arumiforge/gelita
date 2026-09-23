<?php

use App\Services\EventService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * `game_event_logs.occurred_at` bertipe DATETIME(6) karena urutan event
 * penelitian butuh presisi di bawah satu detik. Waktu dari klien
 * (`new Date().toISOString()`, UTC) wajib tetap memuat milidetiknya dan
 * dikonversi ke zona aplikasi.
 *
 * @internal
 */
final class EventTimeTest extends CIUnitTestCase
{
    private string $originalZone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Jakarta');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalZone);
        parent::tearDown();
    }

    public function testClientIsoTimeKeepsMillisecondsInAppTimezone(): void
    {
        $this->assertSame('2026-09-21 09:14:22.481000', $this->clientTime('2026-09-21T02:14:22.481Z'));
        $this->assertSame('2026-09-21 09:14:22.481000', $this->clientTime('2026-09-21T09:14:22.481+07:00'));
    }

    public function testUnparseableClientTimeFallsBackToServerTime(): void
    {
        foreach (['bukan-waktu', '', null, 12345] as $value) {
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/',
                $this->clientTime($value),
            );
        }
    }

    private function clientTime(mixed $value): string
    {
        $method = new ReflectionMethod(EventService::class, 'clientTime');

        return $method->invoke(new EventService(), $value);
    }
}
