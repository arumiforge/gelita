<?php

use App\Controllers\Admin\ValidatorGuideController;
use App\Libraries\GuideSourceCheck;
use App\Libraries\ValidatorGuideContent as Guide;
use App\Libraries\ValidatorGuideFacts;
use App\Validation\GelitaRules;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * Validator guide /admin/panduan-validator (owner, 17 November 2026):
 * public, per panel, nothing a validator must not see, links and terms
 * resolve, live facts read from the install, and every hand-written page
 * still matches the research-document sections it was written from
 * (GuideSourceCheck; the fix when it fails is printed in the message).
 *
 * @internal
 */
final class ValidatorGuideTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    protected function tearDown(): void
    {
        if (db_connect()->tableExists('participants')) {
            $this->dropResetTables();
        }
        parent::tearDown();
    }

    public function testGuideMatchesItsSourceDocuments(): void
    {
        $check = GuideSourceCheck::forValidatorGuide();
        $this->assertTrue($check->docsPresent());

        $problems = $check->problems(Guide::sources());

        $this->assertSame([], $problems, "The research documents behind these validator-guide pages changed:\n"
            . implode("\n", array_map(static fn (string $ref, string $state): string => "  {$state}: {$ref}", array_keys($problems), $problems))
            . "\nCheck the pages listed by `php spark gelita:panduan:cek` against the changed sections, update "
            . "app/Libraries/ValidatorGuideContent.php where needed, then run `php spark gelita:panduan:cek --tandai` and commit the lock file.");

        $this->assertSame([], array_diff(array_keys($check->locked()), Guide::sources()), 'no stale lock entries');
    }

    public function testStructureLinksTermsAndFacts(): void
    {
        $ids   = [];
        $text  = '';
        $terms = Guide::terms();

        foreach (Guide::chapters() as $chapter) {
            $ids[] = $chapter['id'];
            $this->assertNotSame([], $chapter['panels']);
            $this->assertSame([], array_diff($chapter['panels'], array_keys(Guide::PANELS)), $chapter['id']);

            foreach ($chapter['sections'] as $section) {
                $ids[] = $section['id'];
                $text .= json_encode($section, JSON_UNESCAPED_UNICODE);
                $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $section['id']);
                $this->assertSame([], array_diff($section['facts'] ?? [], ValidatorGuideFacts::KEYS), $section['id'] . ' facts');

                if (empty($section['glossary'])) {
                    $this->assertNotEmpty($section['sources'] ?? [], $section['id'] . ' names its sources');
                }
            }
        }

        $this->assertSame(count($ids), count(array_unique($ids)), 'unique ids');

        preg_match_all('/\]\(#([a-z0-9-]+)\)/', $text, $links);
        $this->assertSame([], array_diff($links[1], $ids), 'guide links resolve');

        // Every [[…]] is a known term (a bad key would show as plain text silently)
        preg_match_all('/\[\[([^\]]*)\]\]/u', $text, $raw);
        foreach ($raw[1] as $mark) {
            $key = str_contains($mark, '|') ? substr($mark, strpos($mark, '|') + 1) : $mark;
            $this->assertArrayHasKey($key, $terms, "[[{$mark}]]");
        }

        foreach ($terms as $key => $term) {
            $this->assertTrue($term['more'] === null || in_array($term['more'], $ids, true), $key);
        }

        $this->assertSame([], array_diff(Guide::POPULAR, $ids));
        $this->assertDoesNotMatchRegularExpression('/(?<!\*)\*[^*\s][^*]*\*(?!\*)/u', $text, 'single-star italics do not render');
    }

    public function testNothingValidatorsMustNotSee(): void
    {
        // What readers see: everything but the source references (team-only, never rendered)
        $chapters = array_map(static function (array $chapter): array {
            $chapter['sections'] = array_map(static fn (array $s): array => array_diff_key($s, ['sources' => true]), $chapter['sections']);

            return $chapter;
        }, Guide::chapters());
        $text = json_encode([$chapters, Guide::terms()], JSON_UNESCAPED_UNICODE);

        // Blinding (01 → Peran pengembang, peneliti, dan ahli; 21 → Cara menjaga kebutaan B.5a):
        // no item codes, error or reason codes, label codes, or the researchers' key file
        $this->assertDoesNotMatchRegularExpression('/\b[a-z]{3}-\d-[abx]\d{2}\b|\btes-(mb|ae|bd)\b/', $text, 'item codes');
        $this->assertDoesNotMatchRegularExpression('/\b(INF|DIG|BUD)(-[A-Z]{3})?\b|\bR-[A-Z]{3,}\b/', $text, 'error and reason codes');
        $this->assertDoesNotMatchRegularExpression('/\b(D[123][ab]?|L[123]|C[1-6])\b/', $text, 'label codes');
        $this->assertDoesNotMatchRegularExpression('/kunci-peneliti|khusus-peneliti/i', $text);
        // No secrets or real people: passwords, hosts, addresses, amounts
        $this->assertDoesNotMatchRegularExpression('/password|https?:|@[a-z]|\b\d{1,3}(\.\d{1,3}){3}\b|\bRp\b/i', $text);
        // Written for validators: no server, code, repository or tool talk (synthetic voice is "suara sintetis")
        $this->assertDoesNotMatchRegularExpression('/deploy|migrasi|composer|spark|gelita:|php|\.md\b|docs\/|app\/|ElevenLabs|ChatGPT|Claude/i', $text);
    }

    public function testPanelOrder(): void
    {
        $ids = static fn (string $panel): array => array_column(ValidatorGuideController::order(Guide::chapters(), $panel), 'id');

        $this->assertSame(['mulai', 'media', 'bermain', 'b5a', 'materi', 'budaya', 'kembali', 'rambu'], $ids('media'));
        $this->assertSame(['mulai', 'b5a', 'materi', 'budaya', 'media', 'bermain', 'kembali', 'rambu'], $ids('b5a'));

        $open = array_column(ValidatorGuideController::order(Guide::chapters(), 'b5a'), 'open', 'id');
        $this->assertFalse($open['bermain'], 'the B.5a raters do not get the playing chapter open');
        $this->assertTrue($open['b5a']);
    }

    public function testPublicPagesPerPanelWithLiveFacts(): void
    {
        $this->createRawDataWorld();

        $routes = service('routes');
        $routes->loadRoutes();
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-validator'), 'public (owner, 17 Nov 2026)');
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-validator/masuk'));

        $home = $this->guest('admin/panduan-validator?panel=media');
        $this->assertStringContainsString(lang('Admin.vguideLeadPicked', ['Ahli media']), $home);
        $this->assertStringContainsString('<details class="gd-group" data-chapter="media" open>', $home);
        $this->assertStringContainsString('<details class="gd-group" data-chapter="materi">', $home, 'other panels folded');
        $this->assertStringContainsString('aria-current="page">Ahli media', $home, 'panel picker');
        $this->assertStringNotContainsString(base_url('admin/login'), $home, 'no staff sign-in for validators');

        $blind = $this->guest('admin/panduan-validator?panel=b5a');
        $this->assertStringContainsString(lang('Admin.vguideBlindFirst'), $blind);

        // Live facts come from this install, not from the text
        $facts = $this->guest('admin/panduan-validator/instalasi-ini?panel=materi');
        $this->assertStringContainsString(lang('Admin.vguideFactsCaption'), $facts);
        $this->assertStringContainsString('Temanggung (', $facts);
        $this->assertStringContainsString(config('Gelita')->maxTries . ' kesempatan per tantangan', $facts);
        $this->assertStringContainsString('href="' . base_url('admin/panduan-validator/masalah-akun') . '?panel=materi" rel="next"', $facts, 'the pick travels with the links');
        $this->assertStringNotContainsString(lang('Admin.vguideStale', ['']), $facts);

        db_connect()->table('research_studies')->update(['unlock_mode' => 'free']);
        $free = $this->guest('admin/panduan-validator/instalasi-ini');
        $this->assertStringContainsString('Semua wilayah langsung terbuka', $free);

        $term = $this->guest('admin/panduan-validator/masuk');
        $this->assertStringContainsString('<div class="term-pop" id="istilah-pop-akun-uji" popover role="dialog"', $term);

        $glossary = $this->guest('admin/panduan-validator/istilah');
        $this->assertSame(count(Guide::terms()), substr_count($glossary, 'class="gd-term"'));

        foreach (['renderer', 'staffContext'] as $name) {
            Services::resetSingle($name);
        }
        $staff = $this->withSession(['staff_id' => 2, 'staff_role' => 'ketua', 'staff_name' => 'Ketua'])
            ->call(Method::GET, 'admin/panduan-validator/masuk');
        $staff->assertOK();
        $this->assertStringContainsString(lang('Admin.guideBackToPanel'), (string) $staff->getBody());
    }

    public function testUnknownPageIsNotFound(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->call(Method::GET, 'admin/panduan-validator/tidak-ada');
    }

    public function testLoginPageOffersBothGuides(): void
    {
        $this->createRawDataWorld();
        $_SESSION = [];
        Services::resetSingle('renderer');

        $page = (string) $this->call(Method::GET, 'admin/login')->getBody();
        $this->assertMatchesRegularExpression('/auth-guide-title.*auth-vguide-title/s', $page, 'staff guide first, validator guide after it');
        $this->assertStringContainsString(base_url('admin/panduan-validator') . '?panel=budaya', $page);
    }

    public function testFactsFollowTheRules(): void
    {
        $facts = new ValidatorGuideFacts();
        $rules = new GelitaRules();

        // The username fact states the rule valid_username checks
        $this->assertStringContainsString('3–30 karakter', $facts->fact('nama-pengguna')[1]);
        $this->assertTrue($rules->valid_username('uji.media1'));
        $this->assertTrue($rules->valid_username('a_b'));
        $this->assertFalse($rules->valid_username('ab'));
        $this->assertFalse($rules->valid_username(str_repeat('a', 31)));
        $this->assertFalse($rules->valid_username('uji-media'));

        $policy = config('Gelita')->passwordPolicy;
        $this->assertStringContainsString($policy['min_length'] . '–' . $policy['max_length'] . ' karakter', $facts->fact('kata-sandi')[1]);
        $this->assertNull($facts->fact('tidak-ada'));
    }

    public function testCommitAndInstrumentVersionsAreReadFromFiles(): void
    {
        $root = sys_get_temp_dir() . '/vguide-' . bin2hex(random_bytes(4)) . '/';
        mkdir($root . '.git/refs/heads', 0777, true);
        mkdir($root . GuideSourceCheck::DOCS . 'dokumen-pdf', 0777, true);

        try {
            file_put_contents($root . '.git/HEAD', "ref: refs/heads/main\n");
            file_put_contents($root . '.git/refs/heads/main', str_repeat('a', 7) . str_repeat('0', 33) . "\n");
            $this->assertSame('aaaaaaa', (new ValidatorGuideFacts($root))->commit());

            unlink($root . '.git/refs/heads/main');
            file_put_contents($root . '.git/packed-refs', "# pack-refs\n" . str_repeat('b', 40) . " refs/heads/main\n");
            $this->assertSame('bbbbbbb', (new ValidatorGuideFacts($root))->commit());

            file_put_contents($root . '.git/HEAD', str_repeat('c', 40) . "\n");
            $this->assertSame('ccccccc', (new ValidatorGuideFacts($root))->commit(), 'detached HEAD');

            foreach (['ahli-materi-v2.1', 'ahli-materi-v2.10', 'ahli-media-v1.1'] as $name) {
                touch($root . GuideSourceCheck::DOCS . 'dokumen-pdf/instrumen-validasi-' . $name . '.pdf');
            }
            $this->assertSame('Ahli materi v2.10 · Ahli media v1.1. Bila versi di berkas Anda berbeda, tanyakan ke tim peneliti.', (new ValidatorGuideFacts($root))->fact('instrumen')[1]);
        } finally {
            exec('rm -rf ' . escapeshellarg($root));
        }

        $this->assertNull((new ValidatorGuideFacts('/nonexistent/'))->commit());
    }

    private function guest(string $path): string
    {
        foreach (['renderer', 'staffContext', 'contentRepository'] as $name) {
            Services::resetSingle($name);
        }
        service('cache')->clean();
        $_SESSION = [];

        $response = $this->call(Method::GET, $path);
        $response->assertOK();

        return (string) $response->getBody();
    }
}
