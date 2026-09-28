<?php

use App\Libraries\SchoolName;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Kunci pencocokan nama sekolah: varian penulisan satu sekolah harus sama,
 * dua sekolah berbeda harus tetap berbeda.
 *
 * @internal
 */
final class SchoolNameTest extends CIUnitTestCase
{
    /** @return iterable<string, array{string, list<string>}> */
    public static function sameSchool(): iterable
    {
        yield 'SD 1 Cendono (contoh peneliti)' => ['SD 1 CENDONO', [
            'SD 1 CENDONO', 'SD NEGERI 1 CENDONO', 'SD 1 Cendono', 'sdn 01 Cendono', 'SD N. 1 Cendono',
            'S.D.N. 1 Cendono', 'SD N I Cendono', 'Sekolah Dasar Negeri 1 Cendono', 'SD Negeri Cendono 1',
            'SDN1 Cendono', 'SD Negeri No. 1 Cendono', '  sd   negeri  1   cendono ',
        ]];

        yield 'SMP negeri' => ['SMP 1 DAWE', ['SMP NEGERI 1 DAWE', 'SMPN 1 Dawe', 'SMP N 01 Dawe', 'Sekolah Menengah Pertama Negeri 1 Dawe']];

        yield 'MTs swasta' => ['MTS FALAH MIFTAHUL NU', ['MTS NU MIFTAHUL FALAH', 'MTs NU Miftahul Falah', 'Madrasah Tsanawiyah NU Miftahul Falah', 'MTsS NU Miftahul Falah']];

        yield 'MI swasta' => ['MI FALAH MIFTAHUL NU', ['MIS NU MIFTAHUL FALAH', 'MI NU Miftahul Falah', 'Madrasah Ibtidaiyah NU Miftahul Falah']];

        yield 'SD Islam Terpadu' => ['SD FAIDLURRAHMAN IT KUDUS', ['SDIT FAIDLURRAHMAN KUDUS', 'SD IT Faidlurrahman Kudus', 'SD Islam Terpadu Faidlurrahman Kudus']];

        yield 'SLB negeri' => ['SLB CENDONO KUDUS', ['SLB NEGERI CENDONO KUDUS', 'SLBN Cendono Kudus']];

        yield 'Muhammadiyah' => ['SD 1 KUDUS MUHAMMADIYAH', ['SD MUHAMMADIYAH 1 KUDUS', 'SD Muh 1 Kudus', 'SD Muhammadiyyah 01 Kudus']];

        yield 'apostrof' => ['MI 1 MAARIF', ["MI MA'ARIF 01", 'MI Maarif 1', "MI Ma\u{2019}arif I"]];

        yield 'kecamatan di nama resmi' => ['SD 3 PEKUNCEN TUMIYANG', ['SEKOLAH DASAR NEGERI 3 TUMIYANG KECAMATAN PEKUNCEN', 'SDN 3 Tumiyang Kec. Pekuncen']];
    }

    /** @param list<string> $variants */
    #[\PHPUnit\Framework\Attributes\DataProvider('sameSchool')]
    public function testVariantsShareOneKey(string $key, array $variants): void
    {
        foreach ($variants as $variant) {
            $this->assertSame($key, SchoolName::key($variant), $variant);
        }
    }

    public function testDifferentSchoolsKeepDifferentKeys(): void
    {
        $keys = array_map(SchoolName::key(...), [
            'SD 1 CENDONO', 'SD 2 CENDONO', 'SD 11 CENDONO', 'MI 1 CENDONO', 'SMP 1 CENDONO',
            'SLB CENDONO', 'SD CENDONO', 'SD 1 CENDONO KIDUL',
        ]);

        $this->assertSame($keys, array_values(array_unique($keys)));
    }

    public function testNamesWithoutMeaningfulWordsStillGetAKey(): void
    {
        $this->assertSame('NEGERI', SchoolName::key('negeri'));
        $this->assertSame('---', SchoolName::key(' --- '));
        $this->assertSame('', SchoolName::key('   '));
    }

    public function testPlacesCanBeIgnoredOnBothSides(): void
    {
        $places   = SchoolName::placeTokens('PEKUNCEN', 'Kabupaten Banyumas');
        $official = SchoolName::withoutPlaces(SchoolName::tokens('SEKOLAH DASAR NEGERI 3 TUMIYANG KECAMATAN PEKUNCEN'), $places);
        $typed    = SchoolName::withoutPlaces(SchoolName::tokens('SD N 3 Tumiyang Kab. Banyumas'), $places);

        $this->assertSame('SD 3 TUMIYANG', SchoolName::keyFromTokens($official));
        $this->assertSame('SD 3 TUMIYANG', SchoolName::keyFromTokens($typed));
        $this->assertTrue(SchoolName::hasIdentity($typed));
        $this->assertFalse(SchoolName::hasIdentity(SchoolName::withoutPlaces(SchoolName::tokens('SDN Pekuncen'), $places)));
    }
}
