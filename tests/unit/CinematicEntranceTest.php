<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Cinematic entrances of the start screens (owner, 20 October 2026): the
 * welcome page, Mulai, Masuk, Daftar, and Gerbang name a cue sheet with
 * `data-cine` that game/cine.js plays on the Web Animations API; the welcome
 * page and the gate add the WebGL screen effects afterwards. Parts are held
 * hidden only when motion is allowed, with a CSS fallback that shows them.
 *
 * @internal
 */
final class CinematicEntranceTest extends CIUnitTestCase
{
    public function testStartScreensNameTheirCueSheet(): void
    {
        $sheets = [
            'game/welcome.php'         => 'welcome',
            'game/start-choice.php'    => 'gate',
            'game/start.php'           => 'account',
            'game/login.php'           => 'account',
            'game/register/step-1.php' => 'account',
            'game/register/step-2.php' => 'account',
            'game/register/step-3.php' => 'account',
        ];
        $script = (string) file_get_contents(FCPATH . 'assets/js/game/cine.js');

        foreach ($sheets as $view => $sheet) {
            $this->assertStringContainsString('data-cine="' . $sheet . '"', (string) file_get_contents(APPPATH . 'Views/' . $view), $view);
            $this->assertMatchesRegularExpression('/^  ' . $sheet . ': \{$/m', $script, "cue sheet {$sheet}");
        }

        // Sparks, mist, and light rays only on the two picture pages, behind the switch
        foreach (['game/welcome.php', 'game/start-choice.php'] as $view) {
            $this->assertStringContainsString('<?= screen_fx_attr() ?>', (string) file_get_contents(APPPATH . 'Views/' . $view), $view);
        }
    }

    public function testCuesLeaveThePageToItsOwnCss(): void
    {
        $script = (string) file_get_contents(FCPATH . 'assets/js/game/cine.js');

        $this->assertStringContainsString("fill: 'backwards'", $script, 'no animation keeps a hold on a part after its cue');
        $this->assertStringNotContainsString('transform:', $script, 'translate/scale/rotate only, so hover and positioning transforms stay');
        // Smoothness analysis (20 October 2026): an animated blur/brightness re-renders the full-screen picture every frame
        $this->assertDoesNotMatchRegularExpression('/\bfilter: \[/', $script, 'cues animate only compositor properties');
        $this->assertStringContainsString('await picturesReady(stage);', $script, 'the picture never pops in halfway through its push-in');
        $this->assertMatchesRegularExpression('/if \(performance\.now\(\) > LATE_MS\) \{[^}]*is-cine-live/s', $script, 'after the CSS fallback has shown the page, it is never hidden again');
        $this->assertStringContainsString('reducedMotion()', $script);
        $this->assertStringContainsString('sessionStorage', $script, 'the full film once per page and session');
        $this->assertStringContainsString("import('./game/cine.js')", (string) file_get_contents(FCPATH . 'assets/js/game.js'));
    }

    public function testPartsAreHeldOnlyWithMotionAndShowWithoutTheModule(): void
    {
        $boot = (string) file_get_contents(FCPATH . 'assets/js/core/viewport.js');
        $this->assertMatchesRegularExpression("/prefers-reduced-motion: reduce[^\\n]*\\n[^\\n]*classList\\.add\\('cine-ok'\\)/", $boot);

        $css = (string) file_get_contents(FCPATH . 'assets/css/cinematic.css');
        $this->assertMatchesRegularExpression('/\.cine-ok \[data-cine\]:not\(\.is-cine-live\)[^{]*\{\s*animation: cine-hold \.6s ease 4s both;/', $css, 'shown after 4 s if the module never runs');
        // The looping effects (drift, logo float, glow, sheen) sit behind the motion query
        $loops = substr($css, (int) strpos($css, '@media (prefers-reduced-motion: no-preference) {'));
        foreach (['scene-drift 40s', 'logo-float 6s', 'logo-breathe 5s', 'logo-sheen 7s'] as $loop) {
            $this->assertStringContainsString($loop, substr($loops, 0, (int) strpos($loops, '@keyframes scene-drift')), $loop);
        }
    }

    public function testLogoSheenIsMaskedByTheLogoAndMovesOnTheCompositor(): void
    {
        $view = (string) file_get_contents(APPPATH . 'Views/game/welcome.php');
        $css  = (string) file_get_contents(FCPATH . 'assets/css/cinematic.css');

        $this->assertStringContainsString('style="--logo-src: url(&quot;<?= esc($logo, \'attr\') ?>&quot;)"', $view);
        $this->assertStringContainsString('<span class="logo-sheen" aria-hidden="true"></span>', $view);
        $this->assertStringContainsString('mask: var(--logo-src) center / contain no-repeat;', $css);
        $this->assertStringContainsString('@keyframes logo-sheen { 0% { translate: -100% 0; }', $css, 'a translate, not a background-position repaint');
        $this->assertStringContainsString('.has-parallax :is(.scene-img, .welcome-hero, .gate-jaka) { will-change: translate; }', $css);
        $this->assertStringContainsString('[data-cine] > .screen-fx { animation: cine-hold 2.5s ease both; }', $css, 'the WebGL layer fades in');
    }

    /** Owner, 20 October 2026: the welcome page's light comes from the sun of its own picture. */
    public function testWelcomeLightFollowsTheBackgroundPicture(): void
    {
        $script = (string) file_get_contents(FCPATH . 'assets/js/game/cine.js');
        $fx     = (string) file_get_contents(FCPATH . 'assets/js/game/screen-fx.js');

        $this->assertStringContainsString('export function findLight(img)', $script);
        $this->assertMatchesRegularExpression("/welcome: \{[^}]*fx: 'sun',\s*light: true,/s", $script);
        $this->assertStringContainsString('createScreenFx(root, { threeUrl, light, quality: 1 })', $script);
        // The story scenes keep their source above the top edge and the downward cone
        $this->assertStringContainsString('new THREE.Vector2(0.5, 1.18)', $fx);
        $this->assertStringContainsString("uCone: { value: light ? 0 : 1 }", $fx);
        $this->assertStringContainsString("case 'sun':", $fx);
    }
}
