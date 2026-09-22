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

    /** Konteks filter memang dibagi: chart di halaman ber-filter ikut membawa filternya. */
    public function testComponentSharesFilterContext(): void
    {
        service('renderer')->setData(['filters' => ['study_id' => 5, 'phase_code' => 'pretest', 'locale' => '']]);

        // atribut di-escape konteks attr (`=` → &#x3D;): bandingkan setelah didekode
        $html = html_entity_decode(component('admin-chart', [
            'id'       => 'chart-uji',
            'title'    => 'Uji',
            'type'     => 'bar',
            'endpoint' => 'api/admin/uji',
        ]), ENT_QUOTES | ENT_HTML5);

        $this->assertStringContainsString('study_id=5', $html);
        $this->assertStringContainsString('phase_code=pretest', $html);
        $this->assertStringNotContainsString('locale=', $html);
    }
}
