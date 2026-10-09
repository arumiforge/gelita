<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Layar tantangan dan layar selesai lebar penuh tanpa tokoh Jaka
 * (Dok 3 §5, §12.1). Avatar Mbah Kedu di bilah petunjuk Cari Objek tetap:
 * ia yang membacakan petunjuk.
 *
 * @internal
 */
final class ChallengeFrameTest extends CIUnitTestCase
{
    private function view(string $name): string
    {
        return (string) file_get_contents(APPPATH . 'Views/' . $name . '.php');
    }

    public function testFrameAndFinishedScreenDoNotRenderJaka(): void
    {
        foreach (['game/challenge/_frame', 'game/challenge-finished', 'game/challenge/puzzle', 'game/challenge/rumpang', 'game/challenge/boleh', 'game/challenge/cari'] as $name) {
            $source = $this->view($name);

            $this->assertDoesNotMatchRegularExpression("~'character'\\s*=>\\s*'jaka'~", $source, "{$name} merender Jaka");
            $this->assertStringNotContainsString('challenge-character', $source);
            $this->assertStringNotContainsString('finished-jaka', $source);
            $this->assertStringNotContainsString('finished-layout', $source);
        }
    }

    public function testHuntKeepsMbahKeduAvatar(): void
    {
        $this->assertMatchesRegularExpression("~'character'\\s*=>\\s*'mbah_kedu'~", $this->view('game/challenge/cari'));
    }

    public function testFrameUsesStageCoordinates(): void
    {
        $frame = $this->view('game/challenge/_frame');
        $this->assertStringContainsString('challenge-head stage-box', $frame);
        $this->assertStringContainsString('challenge-aside', $frame);

        $stage = (string) file_get_contents(FCPATH . 'assets/css/stage.css');
        $this->assertMatchesRegularExpression('~\.challenge-head\s*\{\s*--x:\s*32;\s*--y:\s*64;\s*--w:\s*1216;\s*--h:\s*56;~', $stage);
        foreach (['puzzle-board', 'rumpang-reading', 'boleh-passages', 'hunt-scene'] as $panel) {
            $this->assertStringContainsString('.' . $panel, $stage);
        }
    }

    public function testCssNoLongerStylesJaka(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/game.css');
        $this->assertStringNotContainsString('.challenge-character', $css);
        $this->assertStringNotContainsString('.finished-jaka', $css);
    }

    public function testStatementImagesCanBeEnlarged(): void
    {
        $boleh = $this->view('game/challenge/boleh');
        $this->assertStringContainsString('class="card-image" data-zoom=', $boleh);

        $js = (string) file_get_contents(FCPATH . 'assets/js/engines/boleh.js');
        $this->assertStringContainsString('createLightbox', $js);
        $this->assertStringNotContainsString('max-width', $js, 'tanpa cabang matchMedia lebar layar');

        // Dok 3 §11 audit, item 8: the event carries the region code like every other overlay_opened
        $this->assertStringContainsString("overlayOpened('statement_image', { levelId: ctx.levelId ?? null, level: ctx.levelCode ?? null })", $js);
        $this->assertMatchesRegularExpression('~const ctx = \{[^}]*\blevelCode,~', (string) file_get_contents(FCPATH . 'assets/js/game/challenge.js'));
    }

    /** Dok 3 §11 audit, item 7: every literal pose in a view exists for that character. */
    public function testViewsRequestOnlyKnownPoses(): void
    {
        $poses = config('Gelita')->characterPoses;
        $found = 0;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'Views', FilesystemIterator::SKIP_DOTS)) as $file) {
            preg_match_all("~'character'\\s*=>\\s*'(\\w+)',\\s*'pose'\\s*=>\\s*'(\\w+)'~", (string) file_get_contents($file->getPathname()), $matches, PREG_SET_ORDER);

            foreach ($matches as [, $character, $pose]) {
                $found++;
                $this->assertContains($pose, $poses[$character] ?? [], "{$file->getFilename()}: {$character} has no pose {$pose}");
            }
        }

        $this->assertGreaterThan(3, $found);
    }
}
