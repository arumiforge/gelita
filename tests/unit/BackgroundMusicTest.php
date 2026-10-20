<?php

use App\Libraries\AssetChecklist;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Background music (owner, 19 October 2026): synthesized gamelan for the
 * home and account pages, the opening and ending stories, and per region the
 * Kenali overlay, the region dialogue, and the region-done scene. Every track
 * a page names has its file and a row in the asset checklist; the music drops
 * under a narration voice in core/audio.js (browser check: tools/qa/music-check.mjs).
 *
 * @internal
 */
final class BackgroundMusicTest extends CIUnitTestCase
{
    private const REGIONS = ['temanggung', 'magelang', 'wonosobo'];

    /** @return array<string, string> view → its `music` section */
    private static function viewTracks(): array
    {
        $tracks = [];

        foreach (glob(APPPATH . 'Views/game/{*,*/*}.php', GLOB_BRACE) ?: [] as $file) {
            if (preg_match("/section\\('music'\\) \\?>(.*?)<\\?= \\\$this->endSection\\(\\)/", (string) file_get_contents($file), $m) === 1) {
                $tracks[substr($file, strlen(APPPATH . 'Views/'))] = $m[1];
            }
        }

        return $tracks;
    }

    public function testHomeAndAccountPagesPlayTheWelcomeTrack(): void
    {
        $tracks = self::viewTracks();

        foreach (['game/welcome.php', 'game/start.php', 'game/start-choice.php', 'game/login.php', 'game/change-password.php',
            'game/register/step-1.php', 'game/register/step-2.php', 'game/register/step-3.php'] as $view) {
            $this->assertSame('welcome', $tracks[$view] ?? null, $view);
        }

        $this->assertSame('opening', $tracks['game/intro.php'] ?? null);
        $this->assertSame('ending', $tracks['game/ending.php'] ?? null);
        $this->assertSame('challenge', $tracks['game/mission-brief.php'] ?? null);
        $this->assertStringContainsString("'dialog-' . \$level->code", $tracks['game/dialogue.php'] ?? '');
        $this->assertStringContainsString("'tuntas-' . \$level->code", $tracks['game/region-done.php'] ?? '');
        $this->assertArrayNotHasKey('game/map-kedu.php', $tracks, 'Peta Kedu keeps its own track and switches for Kenali in map.js');
    }

    public function testEveryTrackHasItsFileAndAChecklistRow(): void
    {
        $names = ['welcome', 'opening', 'ending', 'challenge', 'map', 'region'];

        foreach (self::REGIONS as $code) {
            array_push($names, 'kenal-' . $code, 'dialog-' . $code, 'tuntas-' . $code);
        }

        foreach ($names as $name) {
            $file = FCPATH . 'assets/audio/music/' . $name . '.mp3';
            $this->assertFileExists($file, $name);
            $this->assertGreaterThan(100_000, filesize($file), $name . ' is a real track');
            $this->assertArrayHasKey('music/' . $name, AssetChecklist::SOUNDS, $name);
        }

        $this->assertSame(array_values(config('Gelita')->levelPrefixes), self::REGIONS, 'one track set per region');
    }

    public function testLayoutPassesTheTrackAndTheScriptsStartAndDuckIt(): void
    {
        $layout = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');
        $this->assertStringContainsString("\$music      = trim(\$this->renderSection('music'));", $layout);
        $this->assertStringContainsString("' data-music=\"' . esc(\$music, 'attr') . '\"'", $layout);

        $game = (string) file_get_contents(FCPATH . 'assets/js/game.js');
        $this->assertStringContainsString('if (document.body.dataset.music) Sfx.music(document.body.dataset.music);', $game);

        $map = (string) file_get_contents(FCPATH . 'assets/js/game/map.js');
        $this->assertStringContainsString("Sfx.music(entry.root.dataset.music || 'map');", $map, 'Kenali switches to its region track');

        $audio = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
        $this->assertStringContainsString('const DUCKED = 0.2;', $audio, 'owner: about 20 % under the voice');
        $this->assertStringContainsString('narrationChanged(playing ||', $audio);
    }

    public function testTheTracksComeFromTheGeneratorInTools(): void
    {
        $scenes = (string) file_get_contents(ROOTPATH . 'tools/audio/music_scenes.py');

        foreach (['welcome', 'opening', 'ending'] as $name) {
            $this->assertStringContainsString("'" . $name . "': lambda: piece_" . $name . '(kendang)', $scenes);
        }

        foreach (['kenal', 'dialog', 'tuntas'] as $context) {
            $this->assertStringContainsString("out[f'" . $context . "-{code}'] = lambda c=code: piece_" . $context . '(c, kendang)', $scenes);
        }

        foreach (self::REGIONS as $code) {
            $this->assertStringContainsString("'" . $code . "': dict(", $scenes, 'one set of pieces per region');
        }

        $this->assertStringContainsString('**music_scenes.pieces(kendang_patterns)', (string) file_get_contents(ROOTPATH . 'tools/audio/music.py'));
    }
}
