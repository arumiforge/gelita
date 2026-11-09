<?php

use App\Libraries\DeviceProfile;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * M-11 (research revision 10, part 7: 33 §8.8): keys of `device_json`
 * beyond what the server keeps are ignored, and the client sends only the
 * userAgentData fields DeviceProfile keeps.
 *
 * @internal
 */
final class DeviceJsonKeysTest extends CIUnitTestCase
{
    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    public function testUnknownKeysAreIgnored(): void
    {
        $client = DeviceProfile::decodeClient(json_encode([
            'screen_w' => 1366, 'screen_h' => 768, 'viewport_w' => 1366, 'viewport_h' => 650,
            'battery' => 0.4, 'fonts' => ['Arial'], 'canvas_hash' => 'abc', 'stage_w' => 1155,
            'ua_data' => ['platform' => 'Windows', 'architecture' => 'x86', 'model' => 'Pixel'],
        ]));

        $row  = DeviceProfile::fromParts(self::UA, [], $client, '203.0.113.7');
        $text = json_encode($row);

        foreach (['battery', 'fonts', 'canvas_hash', 'architecture', 'Pixel', 'abc'] as $kept) {
            $this->assertStringNotContainsString($kept, $text, $kept);
        }

        $this->assertSame(1366, $row['screen_w']);
    }

    public function testClientFiltersUserAgentDataToTheServerList(): void
    {
        $source = (string) file_get_contents(FCPATH . 'assets/js/core/device.js');
        $server = (new ReflectionClassConstant(DeviceProfile::class, 'UA_DATA_KEYS'))->getValue();

        $this->assertStringContainsString("const UA_DATA_KEYS = ['" . implode("', '", $server) . "'];", $source);
    }
}
