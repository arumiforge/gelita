<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Helper tampilan tahap 5: ikon, format angka/tanggal, dan component().
 *
 * @internal
 */
final class ViewHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content', 'ui']);
    }

    protected function tearDown(): void
    {
        // renderer bersama dipakai ulang antar-test: bersihkan data halaman tiruan
        service('renderer')->resetData();
        parent::tearDown();
    }

    public function testIconIsDecorativeInlineSvg(): void
    {
        $svg = icon('check', 'is-big');

        $this->assertStringStartsWith('<svg class="icon is-big"', $svg);
        $this->assertStringContainsString('aria-hidden="true"', $svg);
        $this->assertStringContainsString('focusable="false"', $svg);
    }

    public function testUnknownIconFallsBackToInfo(): void
    {
        $this->assertSame(icon('info'), icon('tidak-ada'));
    }

    public function testNumberFormatsFollowLocale(): void
    {
        $this->assertSame('1.234,5', fmt_num(1234.5, 1, 'id'));
        $this->assertSame('1,234.5', fmt_num(1234.5, 1, 'en'));
        $this->assertSame('—', fmt_num(null));
    }

    public function testPercentAcceptsRatio(): void
    {
        $this->assertSame(fmt_num(73.5, 1) . '%', fmt_pct(0.735, true));
        $this->assertSame(fmt_num(40, 0) . '%', fmt_pct(40, false, 0));
        $this->assertSame('—', fmt_pct(null));
    }

    public function testDateFormatUsesIndonesianMonths(): void
    {
        $this->assertSame('3 Agu 2026 07:05', fmt_date('2026-08-03 07:05:09', false, 'id'));
        $this->assertSame('3 Aug 2026 07:05:09', fmt_date('2026-08-03 07:05:09', true, 'en'));
        $this->assertSame('—', fmt_date(null));
    }

    public function testClassLabelNamesGradeAndSchoolLevel(): void
    {
        $this->assertSame('Kelas 5 SD/MI', class_label('5'));
        $this->assertSame('Kelas 8 SMP/MTs', class_label('8'));
        $this->assertSame('Lainnya', class_label('lainnya'));
        $this->assertSame('Grade 5 (primary)', class_label('5', 'en'));
        $this->assertSame('—', class_label(null));
        $this->assertSame('—', class_label(''));
        $this->assertSame('B', class_label('B'), 'nilai lama yang tidak dikenal tampil apa adanya');
    }

    public function testComponentRendersPassedData(): void
    {
        $html = component('partials/admin-head', ['title' => 'Judul <uji>', 'lead' => 'Penjelasan']);

        $this->assertStringContainsString('Judul &lt;uji&gt;', $html);
        $this->assertStringContainsString('Penjelasan', $html);
    }

    /**
     * Data halaman bernama sama tidak boleh bocor ke komponen: halaman audit
     * punya `$actions` berupa daftar aksi, sedangkan admin-head memakai
     * `$actions` sebagai HTML tombol.
     */
    public function testComponentDoesNotInheritPageData(): void
    {
        service('renderer')->setData(['actions' => ['export', 'delete_preview'], 'lead' => 'bocor']);

        $html = component('partials/admin-head', ['title' => 'Audit log']);

        $this->assertStringNotContainsString('page-actions', $html);
        $this->assertStringNotContainsString('bocor', $html);
    }

    /** Konteks filter memang dibagi: kontrol filter di halaman ber-filter ikut membawa filternya. */
    public function testComponentSharesFilterContext(): void
    {
        service('renderer')->setData([
            'filters'       => ['study_id' => 5, 'locale' => ''],
            'filterOptions' => ['studies' => [
                ['id' => 4, 'code' => 'STUDI-LAMA', 'name' => 'Studi lama', 'status' => 'closed'],
                ['id' => 5, 'code' => 'STUDI-KEDU-KELAS5-2026', 'name' => 'Penelitian kelas 5', 'status' => 'active'],
            ]],
        ]);

        $html = component('admin-filter-bar', ['only' => ['study_id']]);

        $this->assertMatchesRegularExpression('/<option value="5"\s+selected>/', $html, 'filter halaman dibaca komponen');
        $this->assertDoesNotMatchRegularExpression('/<option value="4"\s+selected>/', $html);
        $this->assertStringContainsString('Penelitian kelas 5 (sedang berjalan)', $html);
        $this->assertStringNotContainsString('phase', $html, 'fase penelitian sudah dihapus');
    }
}
