<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Background music waits for the first tap instead of being dropped
 * (display plan Phase 0 item 1). Screens call Sfx.music() at load, before
 * audio is unlocked: core/audio.js must remember that track and start it
 * from unlock() or setEnabled(true). Source check only; the behaviour runs
 * in Chromium with `node tools/qa/music-check.mjs`.
 *
 * @internal
 */
final class MusicUnlockTest extends CIUnitTestCase
{
    private string $js;

    protected function setUp(): void
    {
        parent::setUp();

        $this->js = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
    }

    public function testMusicKeepsTheWantedTrackWhileLocked(): void
    {
        $this->assertStringContainsString('let wantedMusic = null;', $this->js);

        // music() only records the track; the unlock/enabled guard lives in startWantedMusic()
        $music = $this->body('music(name)');
        $this->assertStringContainsString('wantedMusic = name;', $music);
        $this->assertStringContainsString('startWantedMusic();', $music);
        $this->assertStringNotContainsString('return', $music, 'music() must not drop the track while locked');

        $start = $this->body('function startWantedMusic()');
        $this->assertMatchesRegularExpression('~if \(!unlocked \|\| !enabled \|\| !wantedMusic\) return;~', $start);
        $this->assertStringContainsString('if (next === music) return;', $start, 'a second unlock does not restart the track');
    }

    public function testUnlockAndSoundOnStartTheWantedTrack(): void
    {
        $this->assertStringContainsString('startWantedMusic();', $this->body('unlock()'));

        $enabled = $this->body('setEnabled(on)');
        $this->assertMatchesRegularExpression('~\} else \{\s*startWantedMusic\(\);~', $enabled);
        $this->assertStringNotContainsString('this.stopMusic()', $enabled, 'sound off keeps the request');

        $this->assertStringContainsString('wantedMusic = null;', $this->body('stopMusic()'));
    }

    /** Body of the first method or function whose header ends with $signature. */
    private function body(string $signature): string
    {
        $start = strpos($this->js, $signature . ' {');
        $this->assertNotFalse($start, $signature . ' exists in core/audio.js');

        $depth = 0;

        for ($i = strpos($this->js, '{', $start), $n = strlen($this->js); $i < $n; $i++) {
            $depth += match ($this->js[$i]) { '{' => 1, '}' => -1, default => 0 };

            if ($depth === 0) {
                return substr($this->js, $start, $i - $start + 1);
            }
        }

        $this->fail($signature . ' has no closing brace');
    }
}
