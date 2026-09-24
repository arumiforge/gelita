<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * rich_text(): format ringan isi Pustaka Kedu. Teks di-escape lebih dulu,
 * jadi hanya penanda yang dikenali yang menjadi tag.
 *
 * @internal
 */
final class RichTextTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('content');
    }

    public function testParagraphsAndLineBreaks(): void
    {
        $this->assertSame(
            '<p>Baris satu<br>baris dua</p><p>Paragraf baru</p>',
            rich_text("Baris satu\nbaris dua\n\n\nParagraf baru"),
        );
    }

    public function testHeadingListFactAndSource(): void
    {
        $html = rich_text(implode("\n", [
            '## Kopi Temanggung',
            'Tumbuh di lereng gunung.',
            '- arabika',
            '- robusta',
            '> Tahukah kamu? Kopi dipetik saat merah.',
            'Sumber: Pemkab Temanggung',
        ]));

        $this->assertSame(
            '<h3>Kopi Temanggung</h3>'
            . '<p>Tumbuh di lereng gunung.</p>'
            . '<ul><li>arabika</li><li>robusta</li></ul>'
            . '<aside class="book-fact">Tahukah kamu? Kopi dipetik saat merah.</aside>'
            . '<p class="book-source">Sumber: Pemkab Temanggung</p>',
            $html,
        );
    }

    public function testBoldAndItalic(): void
    {
        $this->assertSame(
            '<p>Tari <strong>Lengger</strong> dari kata <em>senduro</em>.</p>',
            rich_text('Tari **Lengger** dari kata *senduro*.'),
        );
    }

    public function testLoneAsterisksStayLiteral(): void
    {
        $this->assertSame('<p>5 * 3 = 15 dan a*b*c</p>', rich_text('5 * 3 = 15 dan a*b*c'));
    }

    public function testHtmlIsAlwaysEscaped(): void
    {
        $html = rich_text("<script>alert(1)</script>\n## <img src=x onerror=alert(1)>\n- **<b>x</b>**");

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('<strong>&lt;b&gt;x&lt;/b&gt;</strong>', $html);
    }

    public function testEmptyTextRendersNothing(): void
    {
        $this->assertSame('', rich_text("  \n\n "));
    }
}
