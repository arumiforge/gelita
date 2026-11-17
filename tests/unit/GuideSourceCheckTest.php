<?php

use App\Libraries\GuideSourceCheck;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * GuideSourceCheck (17 November 2026): GitHub-style anchors, a section's
 * text up to the next heading of its level, and the states a source can be
 * in against the lock file.
 *
 * @internal
 */
final class GuideSourceCheckTest extends CIUnitTestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = sys_get_temp_dir() . '/guide-src-' . bin2hex(random_bytes(4)) . '/';
        mkdir($this->root . GuideSourceCheck::DOCS, 0777, true);
        $this->write(<<<'MD'
            # Dokumen

            ## Akses untuk validator

            Satu.

            ### Usulan akun uji

            Dua.

            ```
            ## bukan judul
            ```

            ## Beban dua penilai B.5a di minggu 3–5

            Tiga.

            ## Akses untuk validator

            Kembar.
            MD);
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->root));
        parent::tearDown();
    }

    public function testAnchorsFollowGithub(): void
    {
        $this->assertSame('beban-dua-penilai-b5a-di-minggu-35', GuideSourceCheck::anchor('Beban dua penilai B.5a di minggu 3–5'));
        $this->assertSame('prasyarat-no-8-instalasi-uji-dan-akun-uji', GuideSourceCheck::anchor('Prasyarat no. 8: instalasi uji dan akun uji'));
        $this->assertSame('panel-ahli-materi-im', GuideSourceCheck::anchor('Panel ahli materi (IM)'));
    }

    public function testSectionRunsToTheNextHeadingOfItsLevel(): void
    {
        $check = $this->check();

        $section = (string) $check->sectionText('20.md#akses-untuk-validator');
        $this->assertStringContainsString('Satu.', $section);
        $this->assertStringContainsString('Dua.', $section, 'sub-sections belong to it');
        $this->assertStringContainsString('## bukan judul', $section, 'a heading inside a code fence is text');
        $this->assertStringNotContainsString('Tiga.', $section);

        $this->assertSame("### Usulan akun uji\n\nDua.\n\n```\n## bukan judul\n```\n", $check->sectionText('20.md#usulan-akun-uji'));
        $this->assertStringContainsString('Kembar.', (string) $check->sectionText('20.md#akses-untuk-validator-1'), 'duplicate headings get -1');
        $this->assertNull($check->sectionText('20.md#tidak-ada'));
        $this->assertNull($check->sectionText('99.md#akses-untuk-validator'));
    }

    public function testStatesAgainstTheLock(): void
    {
        $refs  = ['20.md#akses-untuk-validator', '20.md#beban-dua-penilai-b5a-di-minggu-35'];
        $check = $this->check();

        $this->assertSame(array_fill_keys($refs, GuideSourceCheck::NEW), $check->states($refs));
        $this->assertSame([], $check->mark($refs));
        $this->assertSame([], $this->check()->problems($refs), 'read back from the lock file');

        // Whitespace alone is no change; words are
        $this->write(str_replace('Satu.', "Satu.\n\n", (string) file_get_contents($this->root . GuideSourceCheck::DOCS . '20.md')));
        $this->assertSame([], $this->check()->problems($refs));

        $this->write(str_replace('Tiga.', 'Tiga, diubah.', (string) file_get_contents($this->root . GuideSourceCheck::DOCS . '20.md')));
        $this->assertSame(['20.md#beban-dua-penilai-b5a-di-minggu-35' => GuideSourceCheck::CHANGED], $this->check()->problems($refs));

        $this->assertSame(['20.md#hilang'], $this->check()->mark([...$refs, '20.md#hilang']), 'refuses while a heading is missing');
        $this->assertSame(['20.md#hilang' => GuideSourceCheck::GONE], $this->check()->problems(['20.md#hilang']));

        exec('rm -rf ' . escapeshellarg($this->root . 'docs'));
        $this->assertSame([], $this->check()->problems($refs), 'an install without the documents reports nothing');
        $this->assertSame(GuideSourceCheck::NO_DOCS, $this->check()->states($refs)[$refs[0]]);
    }

    private function check(): GuideSourceCheck
    {
        return new GuideSourceCheck($this->root . 'lock.json', $this->root);
    }

    private function write(string $text): void
    {
        file_put_contents($this->root . GuideSourceCheck::DOCS . '20.md', $text);
    }
}
