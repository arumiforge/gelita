<?php

use App\Filters\HtmlMinifyFilter;
use App\Libraries\HtmlMinifier;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters;
use Config\Services;

/**
 * HTML minification and the "Mr. F" source watermark (HtmlMinifier,
 * HtmlMinifyFilter): whitespace that can render stays, quoted attribute
 * values and whitespace-sensitive elements stay byte for byte, and only
 * full HTML pages are touched.
 *
 * @internal
 */
final class HtmlMinifierTest extends CIUnitTestCase
{
    public function testCollapsesWhitespaceAndDropsComments(): void
    {
        $html = "<!DOCTYPE html>\n<html lang=\"id\">\n  <head>\n    <meta charset=\"UTF-8\">\n"
            . "    <title>  GELITA  </title>\n  </head>\n  <body>\n    <!-- note -->\n"
            . "    <p>Halo,\n      <b>Jaka</b>   <i>dan</i>\n    Mbah Kedu</p>\n  </body>\n</html>\n";

        $this->assertSame(
            '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>GELITA</title></head>'
            . '<body><p>Halo, <b>Jaka</b> <i>dan</i> Mbah Kedu</p></body></html>',
            HtmlMinifier::minify($html),
        );
    }

    public function testKeepsWhitespaceSensitiveBlocks(): void
    {
        $blocks = [
            "<pre class=\"code-block\">{\n  \"a\": 1\n}</pre>",
            "<textarea name=\"t\">baris 1\n\n  baris 2</textarea>",
            "<script type=\"module\">\n  // keep <b>  this</b>\n  const a = '<!-- x -->';\n</script>",
            "<style>\n  .a   { color: red; }\n</style>",
            "<code>a  b</code>",
            "<p data-keep-space>teks\n  panjang <code>x  y</code></p>",
        ];

        $out = HtmlMinifier::minify("<html><body>\n  " . implode("\n  ", $blocks) . "\n</body></html>");

        foreach ($blocks as $block) {
            $this->assertStringContainsString($block, $out);
        }
    }

    public function testKeepsBlocksAtEitherEndAndDebugViewMarkers(): void
    {
        $html = "<!-- DEBUG-VIEW START 1 APPPATH/Views/a.php -->\n<!DOCTYPE html>\n<html><body>\n<p>x</p>\n</body></html>\n"
            . "<!-- DEBUG-VIEW ENDED 1 APPPATH/Views/a.php -->\n";

        $this->assertSame(
            '<!-- DEBUG-VIEW START 1 APPPATH/Views/a.php --><!DOCTYPE html><html><body><p>x</p></body></html>'
            . '<!-- DEBUG-VIEW ENDED 1 APPPATH/Views/a.php -->',
            HtmlMinifier::minify($html),
        );
        $this->assertSame('<pre> a </pre>', HtmlMinifier::minify("\n<pre> a </pre>\n"));
    }

    public function testKeepsQuotedAttributeValues(): void
    {
        $html = "<html><body><button\n   type=\"button\"\n   title=\"a  b\n c\"   data-x='1 > 0'  >Ok</button></body></html>";

        $this->assertSame(
            "<html><body><button type=\"button\" title=\"a  b\n c\" data-x='1 > 0'>Ok</button></body></html>",
            HtmlMinifier::minify($html),
        );
    }

    public function testKeepsConditionalComments(): void
    {
        $out = HtmlMinifier::minify('<html><head><!--[if IE]><p>x</p><![endif]--></head></html>');

        $this->assertStringContainsString('<!--[if IE]><p>x</p><![endif]-->', $out);
    }

    public function testWatermarkIsAnsiShadowBeforeDoctype(): void
    {
        $out   = HtmlMinifier::watermark('<!DOCTYPE html><html></html>');
        $lines = explode("\n", HtmlMinifier::WATERMARK);

        $this->assertStringStartsWith("<!--\n███╗   ███╗██████╗        ███████╗\n", $out);
        $this->assertStringEndsWith("-->\n<!DOCTYPE html><html></html>", $out);
        $this->assertCount(6, $lines);
        $this->assertStringNotContainsString('--', HtmlMinifier::WATERMARK);
    }

    public function testFilterMinifiesFullPagesOnly(): void
    {
        $filter = new HtmlMinifyFilter();
        $page   = "<!DOCTYPE html>\n<html>\n<body>\n  <p>Hai</p>\n</body>\n</html>";

        $html = $this->response($page, 'text/html; charset=UTF-8');
        $filter->after(Services::request(), $html);
        $this->assertSame(
            HtmlMinifier::watermark('<!DOCTYPE html><html><body><p>Hai</p></body></html>'),
            $html->getBody(),
        );

        $json = $this->response("{\n  \"ok\": true\n}", 'application/json');
        $filter->after(Services::request(), $json);
        $this->assertSame("{\n  \"ok\": true\n}", $json->getBody());

        $fragment = $this->response("<div>\n  <p>Hai</p>\n</div>", 'text/html; charset=UTF-8');
        $filter->after(Services::request(), $fragment);
        $this->assertSame("<div>\n  <p>Hai</p>\n</div>", $fragment->getBody());
    }

    public function testFilterCanBeSwitchedOff(): void
    {
        config('Gelita')->minifyHtml = false;

        try {
            $page = "<!DOCTYPE html>\n<html></html>";
            $html = $this->response($page, 'text/html; charset=UTF-8');
            (new HtmlMinifyFilter())->after(Services::request(), $html);
            $this->assertSame($page, $html->getBody());
        } finally {
            config('Gelita')->minifyHtml = true;
        }
    }

    public function testFilterRunsOnEveryRequest(): void
    {
        $this->assertContains('minifyHtml', (new Filters())->globals['after']);
    }

    private function response(string $body, string $type): Response
    {
        return (new Response(config('App')))->setHeader('Content-Type', $type)->setBody($body);
    }
}
