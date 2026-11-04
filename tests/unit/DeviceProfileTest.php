<?php

use App\Libraries\DeviceProfile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pelacakan perangkat (perombakan kelas 5, Dok 1 §11): User-Agent nyata
 * diurai matomo/device-detector, iPad ber-UA Macintosh dikoreksi, dan isian
 * `device_json` dari formulir tidak pernah dipercaya begitu saja.
 *
 * @internal
 */
final class DeviceProfileTest extends CIUnitTestCase
{
    private const WINDOWS_CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
    private const SAMSUNG_TAB    = 'Mozilla/5.0 (Linux; Android 13; SM-X200) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
    private const IPHONE_SAFARI  = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1';
    private const MAC_SAFARI     = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15';
    private const CHROMEBOOK     = 'Mozilla/5.0 (X11; CrOS x86_64 14541.0.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
    private const GOOGLEBOT      = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
    private const ANDROID_FROZEN = 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36';

    /** @var array<string, mixed> */
    private array $server;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server = service('superglobals')->getServerArray();
    }

    protected function tearDown(): void
    {
        service('superglobals')->setServerArray($this->server);
        parent::tearDown();
    }

    public function testWindowsElevenChromeFromClientHintHeaders(): void
    {
        $row = DeviceProfile::fromParts(self::WINDOWS_CHROME, [
            'Sec-CH-UA'                  => '"Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
            'Sec-CH-UA-Mobile'           => '?0',
            'Sec-CH-UA-Platform'         => '"Windows"',
            'Sec-CH-UA-Platform-Version' => '"15.0.0"',
        ], []);

        $this->assertSame(['desktop', 'Windows', '11', 'Chrome', 'Blink'], [$row['device_type'], $row['os_name'], $row['os_version'], $row['browser_name'], $row['browser_engine']]);
        $this->assertSame(0, $row['is_bot']);
        $this->assertNull($row['corrected']);
        $this->assertArrayNotHasKey('client_hints_json', $row, 'hints are read, not stored (29 §5.5)');

        // tanpa Client Hints, UA Windows 11 tidak dapat dibedakan dari Windows 10
        $this->assertSame('10', DeviceProfile::fromParts(self::WINDOWS_CHROME, [], [])['os_version']);
    }

    public function testSamsungTabletAndIphone(): void
    {
        $tablet = DeviceProfile::fromParts(self::SAMSUNG_TAB, [], ['touch_points' => 5]);

        $this->assertSame(['tablet', 'Android', '13', 'Chrome'], [$tablet['device_type'], $tablet['os_name'], $tablet['os_version'], $tablet['browser_name']]);
        $this->assertArrayNotHasKey('device_brand', $tablet);
        $this->assertArrayNotHasKey('device_model', $tablet);

        $phone = DeviceProfile::fromParts(self::IPHONE_SAFARI, [], ['touch_points' => 5]);

        $this->assertSame(['smartphone', 'iOS', '17.4', 'Mobile Safari'], [$phone['device_type'], $phone['os_name'], $phone['os_version'], $phone['browser_name']]);
        $this->assertNull($phone['corrected']);
    }

    public function testIpadWithDesktopUserAgentIsCorrected(): void
    {
        $ipad = DeviceProfile::fromParts(self::MAC_SAFARI, [], ['touch_points' => 5, 'screen_w' => 1024, 'screen_h' => 1366]);

        $this->assertSame('tablet', $ipad['device_type']);
        $this->assertSame('iPadOS', $ipad['os_name']);
        $this->assertNull($ipad['os_version'], 'versi macOS dari UA bukan versi iPadOS');
        $this->assertSame('ipad_desktop_ua', $ipad['corrected']);
        $this->assertSame('Safari', $ipad['browser_name']);

        // Mac sungguhan (tanpa layar sentuh) tidak ikut dikoreksi
        $mac = DeviceProfile::fromParts(self::MAC_SAFARI, [], ['touch_points' => 0]);

        $this->assertSame(['desktop', 'Mac', null], [$mac['device_type'], $mac['os_name'], $mac['corrected']]);
        $this->assertSame('desktop', DeviceProfile::fromParts(self::MAC_SAFARI, [], [])['device_type'], 'tanpa JavaScript tidak ada sinyal sentuh');
    }

    public function testChromebookAndBot(): void
    {
        $chromebook = DeviceProfile::fromParts(self::CHROMEBOOK, [], []);

        $this->assertSame(['desktop', 'Chrome OS', 'Chrome'], [$chromebook['device_type'], $chromebook['os_name'], $chromebook['browser_name']]);
        $this->assertSame(0, $chromebook['is_bot']);

        $bot = DeviceProfile::fromParts(self::GOOGLEBOT, [], []);

        $this->assertSame(1, $bot['is_bot']);
        $this->assertNull($bot['device_type']);
    }

    public function testUserAgentDataFillsTheFrozenAndroidUserAgent(): void
    {
        $client = ['ua_data' => [
            'platform'        => 'Android',
            'platformVersion' => '13.0.0',
            'model'           => 'SM-A515F',   // not requested any more; ignored
            'mobile'          => true,
            'fullVersionList' => [['brand' => 'Google Chrome', 'version' => '124.0.6367.82']],
        ]];

        $row = DeviceProfile::fromParts(self::ANDROID_FROZEN, [], $client);

        $this->assertSame(['smartphone', 'Android'], [$row['device_type'], $row['os_name']]);
        $this->assertStringStartsWith('13', (string) $row['os_version'], 'platformVersion still fills the frozen UA');
        $this->assertStringNotContainsString('SM-A515F', json_encode($row), 'the model is never kept');
    }

    public function testScreenSignalsAreValidatedAndClipped(): void
    {
        $row = DeviceProfile::fromParts(str_repeat('A', 600), [], [
            'screen_w'      => '1366',
            'screen_h'      => 768.4,
            'viewport_w'    => 'lebar',
            'viewport_h'    => 100_000,
            'pixel_ratio'   => 1.3333,
            'touch_points'  => -2,
            'orientation'   => 'landscape-primary',
            'is_standalone' => 'true',
            'language'      => 'id-ID',
            'timezone'      => 'Asia/Jakarta; DROP TABLE',
        ]);

        $this->assertArrayNotHasKey('user_agent', $row);
        $this->assertSame([1366, 768], [$row['screen_w'], $row['screen_h']]);
        $this->assertNull($row['viewport_w'], 'bukan angka');
        $this->assertNull($row['viewport_h'], 'di luar batas kolom');
        $this->assertNull($row['touch_points']);
        $this->assertSame('landscape-primary', $row['orientation']);
        $this->assertNull($row['is_standalone'], 'hanya boolean');

        foreach (['pixel_ratio', 'language', 'timezone', 'os_platform'] as $dropped) {
            $this->assertArrayNotHasKey($dropped, $row, $dropped . ' is not stored (29 §5.5)');
        }

        $this->assertNull(DeviceProfile::fromParts(self::IPHONE_SAFARI, [], ['orientation' => str_repeat('x', 30)])['orientation']);
    }

    public function testBrokenOrOversizedDeviceJsonIsIgnored(): void
    {
        $this->assertSame([], DeviceProfile::decodeClient(null));
        $this->assertSame([], DeviceProfile::decodeClient(''));
        $this->assertSame([], DeviceProfile::decodeClient('{"screen_w":1366'), 'JSON rusak');
        $this->assertSame([], DeviceProfile::decodeClient('[1366, 768]'), 'bukan objek');
        $this->assertSame([], DeviceProfile::decodeClient('"1366x768"'));
        $this->assertSame([], DeviceProfile::decodeClient(json_encode(['pad' => str_repeat('x', DeviceProfile::CLIENT_JSON_MAX)])), 'lebih dari 4 KB');
        $this->assertSame(['screen_w' => 1366, 'touch_points' => 0], DeviceProfile::decodeClient('{"screen_w":1366,"touch_points":0}'));

        // tanpa device_json: tetap terurai dari User-Agent saja
        $row = DeviceProfile::fromParts(self::IPHONE_SAFARI, [], DeviceProfile::decodeClient('{rusak'));

        $this->assertSame('smartphone', $row['device_type']);
        $this->assertNull($row['screen_w']);
    }

    public function testRawIpIsNeverKept(): void
    {
        $row = DeviceProfile::fromParts(self::WINDOWS_CHROME, [], [], '203.0.113.9');

        $this->assertSame(hash_ip('203.0.113.9'), $row['ip_hash']);
        $this->assertStringNotContainsString('203.0.113.9', json_encode($row));
        $this->assertNull(DeviceProfile::fromParts(self::WINDOWS_CHROME, [], [], null)['ip_hash']);
        $this->assertMatchesRegularExpression('#^matomo/device-detector \d+\.\d+#', $row['detector']);
    }

    /** Revision 10, part 3 (29 §5.5, `DeviceMinimisationTest`): only the kept columns reach device_logs. */
    public function testDeviceLogsKeepOnlyTheMinimisedColumns(): void
    {
        $fields = (new \ReflectionProperty(\App\Models\DeviceLogModel::class, 'allowedFields'))->getValue(model(\App\Models\DeviceLogModel::class, false));

        foreach (['user_agent', 'client_hints_json', 'device_brand', 'device_model', 'pixel_ratio', 'touch_points', 'timezone', 'language', 'os_platform'] as $dropped) {
            $this->assertNotContains($dropped, $fields, $dropped);
        }

        $this->assertSame(['Sec-CH-UA-Platform-Version', 'Sec-CH-UA-Full-Version-List'], config('Gelita')->clientHints, 'Accept-CH without Model, Arch, Bitness');

        $response = (new \App\Filters\ClientHintsFilter())->after(service('request'), service('response'));
        $this->assertSame('Sec-CH-UA-Platform-Version, Sec-CH-UA-Full-Version-List', $response->getHeaderLine('Accept-CH'));

        $device = (string) file_get_contents(FCPATH . 'assets/js/core/device.js');
        foreach (['pixel_ratio', 'timezone', "'model'", "'architecture'", "'bitness'"] as $word) {
            $this->assertStringNotContainsString($word, $device, $word . ' is not sent any more');
        }
    }

    public function testCaptureReadsTheRequestHeaders(): void
    {
        service('superglobals')
            ->setServer('HTTP_USER_AGENT', self::WINDOWS_CHROME)
            ->setServer('HTTP_SEC_CH_UA_PLATFORM', '"Windows"')
            ->setServer('HTTP_SEC_CH_UA_PLATFORM_VERSION', '"15.0.0"')
            ->setServer('REMOTE_ADDR', '198.51.100.7');

        $request = new IncomingRequest(config('App'), new SiteURI(config('App')), null, new UserAgent());
        $row     = DeviceProfile::capture($request, ['screen_w' => 1920, 'screen_h' => 1080, 'touch_points' => 0]);

        $this->assertSame(['desktop', 'Windows', '11'], [$row['device_type'], $row['os_name'], $row['os_version']]);
        $this->assertSame(hash_ip('198.51.100.7'), $row['ip_hash']);
        $this->assertSame(
            ['device_type' => 'desktop', 'os_name' => 'Windows', 'browser_name' => 'Chrome', 'screen_size' => '1920x1080', 'is_touch' => 0],
            DeviceProfile::sessionSummary($row),
        );
        $this->assertNull(DeviceProfile::sessionSummary(DeviceProfile::fromParts(self::WINDOWS_CHROME, [], []))['is_touch'], 'tanpa JavaScript: tidak diketahui');
    }
}
