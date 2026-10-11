<?php

use CodeIgniter\HTTP\ContentSecurityPolicy;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\ContentSecurityPolicy as CspConfig;

/**
 * The CSP that production sends (`app.CSPEnabled = true` in .env) has to let
 * the game run: no inline scripts in views, audio unlock through a data: WAV,
 * inline style attributes, library images/videos from other hosts, and the
 * video players MediaLink embeds (docs/02 §7.6).
 *
 * @internal
 */
final class ContentSecurityPolicyTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Factories::reset('config');
        parent::tearDown();
    }

    /** @return array<string, string> directive => source list */
    private function directives(): array
    {
        // As in production (.env); the repo default is off
        $app             = new App();
        $app->CSPEnabled = true;
        Factories::injectMock('config', App::class, $app);

        $response = new Response($app);
        $response->setBody('<html></html>');
        (new ContentSecurityPolicy(new CspConfig()))->finalize($response);

        $header = $response->getHeaderLine('Content-Security-Policy');
        $this->assertNotSame('', $header);

        $directives = [];

        foreach (array_filter(array_map('trim', explode(';', $header))) as $part) {
            [$name, $sources]  = array_pad(explode(' ', $part, 2), 2, '');
            $directives[$name] = $sources;
        }

        return $directives;
    }

    public function testScriptsOnlyFromOwnOrigin(): void
    {
        $d = $this->directives();

        $this->assertSame("'self'", $d['default-src']);
        $this->assertSame("'self'", $d['script-src']);
        $this->assertSame("'self'", $d['script-src-elem']);
        $this->assertSame("'none'", $d['object-src']);
        $this->assertSame("'self'", $d['frame-ancestors']);
    }

    public function testGameAssetsAreAllowed(): void
    {
        $d = $this->directives();

        // core/audio.js and Howler unlock audio with a silent data: WAV
        $this->assertStringContainsString('data:', $d['media-src']);
        $this->assertStringContainsString('https:', $d['media-src']);
        $this->assertStringContainsString('data:', $d['img-src']);
        $this->assertStringContainsString('https:', $d['img-src']);
        // Several views position elements through style attributes
        $this->assertSame("'unsafe-inline'", $d['style-src-attr']);
        $this->assertSame("'self'", $d['style-src-elem']);
        $this->assertSame("'self'", $d['connect-src']);
        $this->assertSame("'self'", $d['worker-src']);
        $this->assertSame("'self'", $d['manifest-src']);
    }

    public function testLibraryVideoPlayersAreAllowed(): void
    {
        $frames = $this->directives()['frame-src'];

        foreach (['https://www.youtube-nocookie.com', 'https://player.vimeo.com', 'https://drive.google.com'] as $host) {
            $this->assertStringContainsString($host, $frames);
        }
    }

    public function testViewsHaveNoInlineScripts(): void
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'Views', FilesystemIterator::SKIP_DOTS));
        $found = [];

        foreach ($files as $file) {
            // Framework error pages only show in development
            if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            preg_match_all('/<script\b([^>]*)>/i', (string) file_get_contents($file->getPathname()), $tags);

            foreach ($tags[1] as $attributes) {
                if (! str_contains($attributes, 'src=') && ! str_contains($attributes, 'application/json')) {
                    $found[] = substr($file->getPathname(), strlen(APPPATH)) . ': <script' . $attributes . '>';
                }
            }
        }

        $this->assertSame([], $found, 'Inline scripts are blocked by the production CSP; load them from a file.');
    }

    public function testViewportScriptLoadsBeforeStylesheets(): void
    {
        $layout = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');

        $this->assertFileExists(FCPATH . 'assets/js/core/viewport.js');
        $this->assertLessThan(strpos($layout, 'rel="stylesheet"'), strpos($layout, "js/core/viewport.js"));
        // A classic blocking script, so --app-w/--app-h exist before the first paint
        $this->assertMatchesRegularExpression('#<script src="[^"]+js/core/viewport\.js[^"]*"></script>#', $layout);
    }
}
