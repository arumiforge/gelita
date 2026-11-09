<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Every `CONFIG.x` read in the browser code must be defined in the frozen
 * CONFIG of core/config.js; a key the server sends but config.js does not
 * copy is silently undefined (found in the wave F re-check, 9 November 2026:
 * `researchMode`, `minStagePx`, `inputIdleLogMs`, `emptyTapLimit`).
 *
 * @internal
 */
final class JsConfigKeysTest extends CIUnitTestCase
{
    public function testEveryConfigKeyReadInJavaScriptIsDefined(): void
    {
        $config = (string) file_get_contents(FCPATH . 'assets/js/core/config.js');
        $this->assertSame(1, preg_match('/export const CONFIG = Object\.freeze\(\{(.*?)\n\}\);/s', $config, $m));
        preg_match_all('/^\s{2}([a-zA-Z]+):/m', $m[1], $defined);

        $used = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FCPATH . 'assets/js', FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'js' || str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'min' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            preg_match_all('/\bCONFIG\.([a-zA-Z]+)/', (string) file_get_contents($file->getPathname()), $hits);

            foreach ($hits[1] as $key) {
                $used[$key][] = basename($file->getPathname());
            }
        }

        $missing = array_diff_key($used, array_flip($defined[1]));
        $this->assertSame([], array_map(static fn (array $f): string => implode(', ', array_unique($f)), $missing), 'CONFIG keys read in JS but not defined in core/config.js');
    }

    public function testResearchScreenKeysReachTheConfigScript(): void
    {
        helper('gelita');
        $this->assertArrayHasKey('researchMode', research_screen_config(null));
        $this->assertArrayHasKey('minStagePx', research_screen_config(null));
    }
}
