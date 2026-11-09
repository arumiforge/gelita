<?php

use App\Libraries\Derived\Clock;
use App\Libraries\Derived\FieldSheetFlags;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §6.1; 11 → Aturan gabung lembar lapangan): the L1–L3 windows.
 *
 * @internal
 */
final class FieldSheetFlagsTest extends CIUnitTestCase
{
    public function testL3WindowIsOneMinuteBeforeToTwoMinutesAfterOrAfterItsEnd(): void
    {
        $flags = $this->flags([$this->row('l3_bantuan', '09:00:00', null, ['help_type' => 'ISI', 'seats' => '1'])]);

        $this->assertSame(0, $flags->at(1, $this->t('08:58:59'))['helped_content']);
        $this->assertSame(1, $flags->at(1, $this->t('08:59:00'))['helped_content']);
        $this->assertSame(1, $flags->at(1, $this->t('09:02:00'))['helped_content']);
        $this->assertSame(0, $flags->at(1, $this->t('09:02:01'))['helped_content']);

        $long = $this->flags([$this->row('l3_bantuan', '09:00:00', '09:10:00', ['help_type' => 'TKN', 'seats' => '1'])]);
        $this->assertSame(1, $long->at(1, $this->t('09:11:30'))['helped_technical'], 'until 2 minutes after the end');
        $this->assertSame(0, $long->at(1, $this->t('09:11:30'))['helped_content']);

        $other = $this->flags([$this->row('l3_bantuan', '09:00:00', null, ['help_type' => 'GG-KLR', 'seats' => '2, 3'])]);
        $this->assertSame(0, $other->at(1, $this->t('09:00:00'))['disturbed'], 'another seat');
    }

    public function testL1FiveMinutesOrItsDurationAndSeatAll(): void
    {
        $flags = $this->flags([
            $this->row('l1_penyimpangan', '09:00:00', null, ['deviation_code' => 'DEV-NET', 'affected_seats' => 'semua']),
            $this->row('l1_penyimpangan', '09:01:00', '09:20:00', ['deviation_code' => 'DEV-LIS', 'affected_seats' => '1;4']),
        ]);

        $this->assertSame('DEV-NET;DEV-LIS', $flags->at(1, $this->t('09:04:00'))['deviation_code']);
        $this->assertSame('DEV-LIS', $flags->at(1, $this->t('09:06:00'))['deviation_code']);
        $this->assertNull($flags->at(1, $this->t('09:21:00'))['deviation_code']);
    }

    public function testL2SharedDeviceUntilTheDeviceSwitch(): void
    {
        $flags = $this->flags([$this->row('l2_kursi', '08:00:00', null, ['shared_device' => 'Ya', 'switch_time' => '09:30'], true)]);

        $this->assertSame(1, $flags->at(1, $this->t('09:29:59'))['shared_device']);
        $this->assertSame(0, $flags->at(1, $this->t('09:30:00'))['shared_device']);
    }

    public function testEmptyWhenTheSheetWasNeverImported(): void
    {
        $flags = new FieldSheetFlags([], [1 => []], [1 => 1], [1 => '2026-11-10']);
        $out   = $flags->at(1, $this->t('09:00:00'));

        $this->assertNull($out['shared_device']);
        $this->assertNull($out['helped_content']);
        $this->assertNull($flags->at(null, $this->t('09:00:00'))['helped_content'], 'no field session');
    }

    private function flags(array $rows): FieldSheetFlags
    {
        return new FieldSheetFlags($rows, [1 => ['l1_penyimpangan', 'l2_kursi', 'l3_bantuan']], [1 => 1], [1 => '2026-11-10']);
    }

    private function row(string $sheet, string $start, ?string $end, array $values, bool $mine = false): array
    {
        return ['id' => 1, 'field_session_id' => 1, 'sheet' => $sheet, 'mine' => $mine, 'seat_no' => null, 'started_at' => $this->t($start), 'ended_at' => $end === null ? null : $this->t($end), 'values' => $values];
    }

    private function t(string $time): int
    {
        return Clock::ms('2026-11-10 ' . $time);
    }
}
