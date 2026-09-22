<?php

use App\Controllers\Concerns\GameProgress;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Wilayah yang baru terbuka selalu lewat dialog pembuka lebih dulu.
 *
 * @internal
 */
final class RegionEntryTest extends CIUnitTestCase
{
    /** @return array<string, array{string, string}> */
    public static function statuses(): array
    {
        return [
            'baru terbuka → dialog'            => ['open', 'dialog/magelang'],
            'sedang dijelajahi → peta wilayah' => ['in_progress', 'wilayah/magelang'],
            'tuntas → peta wilayah'            => ['completed', 'wilayah/magelang'],
            'terkunci → ditolak peta wilayah'  => ['locked', 'wilayah/magelang'],
        ];
    }

    /**
     * @dataProvider statuses
     */
    public function testEntryPathFollowsRegionStatus(string $status, string $expected): void
    {
        $probe = new class () {
            use GameProgress;

            public function entry(string $code, string $status): string
            {
                return $this->regionEntryPath($code, $status);
            }
        };

        $this->assertSame($expected, $probe->entry('magelang', $status));
    }
}
