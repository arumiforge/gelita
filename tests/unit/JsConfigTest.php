<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Kontrak server ↔ JavaScript area game.
 *
 * - `sessionTag` (penanda antrean event offline) buram, stabil per sesi, dan
 *   berbeda antarsesi — agar event siswa A tidak pernah masuk ke sesi siswa B.
 * - Setiap teks yang diminta JavaScript lewat t('…') ada di lang('Js.*'),
 *   sehingga tidak ada kunci mentah yang tampil kepada anak.
 *
 * @internal
 */
final class JsConfigTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('gelita');
    }

    public function testSessionTagIsOpaqueStableAndPerSession(): void
    {
        $tag = session_tag(42);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $tag);
        $this->assertSame($tag, session_tag(42));
        $this->assertNotSame($tag, session_tag(43));
        $this->assertNull(session_tag(null));
        $this->assertNull(session_tag(0));
    }

    public function testJsConfigCarriesTextWithoutIdentity(): void
    {
        $config = js_config('en');

        $this->assertSame('en', $config['locale']);
        $this->assertArrayHasKey('csrfHash', $config);
        $this->assertNull($config['sessionTag'], 'tanpa login tidak ada penanda sesi');
        $keys = array_keys(require APPPATH . 'Language/id/Js.php');

        // Both languages, so a language switch needs no request for JS texts (asset package Stage 4)
        $this->assertSame(config('Gelita')->locales, array_keys($config['text']));

        foreach (config('Gelita')->locales as $code) {
            $this->assertSame($keys, array_keys($config['text'][$code]));
            $this->assertSame(lang('Js.sessionExpired', [], $code), $config['text'][$code]['sessionExpired']);
        }
        $this->assertNotSame($config['text']['id']['sessionExpired'], $config['text']['en']['sessionExpired']);
        $this->assertSame($config['text'], js_config('id')['text'], 'texts do not depend on the active locale');

        $encoded = json_encode($config);
        $this->assertStringNotContainsString('participant', (string) $encoded);
        $this->assertStringNotContainsString('session_code', (string) $encoded);
    }

    public function testNextAssetsAttrIsCappedDeduplicatedAndEscaped(): void
    {
        $this->assertSame('', next_assets_attr([]));
        $this->assertSame('', next_assets_attr([null, '']));

        $attr = next_assets_attr(['/a.jpg', '/a.jpg', null, '/b.png?v=1"x', '/c.jpg', '/d.jpg'], 3);
        $this->assertStringStartsWith(' data-next-assets="', $attr);
        $this->assertStringNotContainsString('"x', substr($attr, 19, -1), 'quotes are escaped inside the attribute');

        preg_match('/data-next-assets="([^"]*)"/', $attr, $match);
        $urls = json_decode(html_entity_decode($match[1], ENT_QUOTES), true);
        $this->assertSame(['/a.jpg', '/b.png?v=1"x', '/c.jpg'], $urls);
    }

    public function testEveryJsTextKeyExists(): void
    {
        $keys  = array_keys(require APPPATH . 'Language/id/Js.php');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FCPATH . 'assets/js'));
        $used  = [];

        foreach ($files as $file) {
            // Panel admin satu bahasa (Indonesia) dan tidak memakai t()
            if ($file->getExtension() !== 'js' || str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'admin')) {
                continue;
            }

            preg_match_all("/\\bt\\('([A-Za-z]+)'/", (string) file_get_contents($file->getPathname()), $matches);

            foreach ($matches[1] as $key) {
                $used[$key] = $file->getFilename();
            }
        }

        $this->assertNotSame([], $used, 'pemindaian t() tidak menemukan apa pun — pola berubah?');

        foreach ($used as $key => $file) {
            $this->assertContains($key, $keys, "t('{$key}') di {$file} tidak ada di Language/*/Js.php");
        }
    }
}
