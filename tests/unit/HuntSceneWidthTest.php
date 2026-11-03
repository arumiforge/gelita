<?php

use App\Libraries\HuntGeometry;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * HUNT_SCENE_DP is the width of `.hunt-scene` in the challenge layout
 * (31 §9.1, §22 no. 10). A layout change that moves the scene must update
 * the constant and re-check the scenes, so this test reads the CSS.
 *
 * @internal
 */
final class HuntSceneWidthTest extends CIUnitTestCase
{
    public function testConstantMatchesTheStageLayout(): void
    {
        $css = (string) file_get_contents(FCPATH . 'assets/css/stage.css');

        $this->assertMatchesRegularExpression('/\.challenge-cari \.hunt-scene\s*\{[^}]*--w:\s*(\d+);[^}]*--h:\s*(\d+);/', $css);
        preg_match('/\.challenge-cari \.hunt-scene\s*\{[^}]*--w:\s*(\d+);[^}]*--h:\s*(\d+);/', $css, $m);

        $this->assertSame(HuntGeometry::HUNT_SCENE_DP, (int) $m[1], 'stage.css .hunt-scene width changed: update HuntGeometry::HUNT_SCENE_DP and re-check the scenes');
        $this->assertSame((int) round(HuntGeometry::sceneHeightDp()), (int) $m[2], 'the scene stays 16:9');
    }
}
