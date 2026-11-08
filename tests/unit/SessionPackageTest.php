<?php

use App\Libraries\DeviceProfile;
use App\Libraries\ExportLabels;
use App\Models\DataExportModel;
use App\Services\ExportService;
use App\Services\SessionService;
use App\Services\StaffScope;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Every game session knows the asset package it used (rencana-paket-aset
 * §7.1, §11.2): sign-up and login send `package_code` and `package_mode`
 * with the device signals; the server writes
 * `app_client_version = {app_version}+{package_code}` and `package_mode`;
 * a resumed session keeps its package and logs `package_changed`; the Sesi
 * export and its data dictionary carry the columns.
 *
 * @internal
 */
final class SessionPackageTest extends CIUnitTestCase
{
    use ResetTables;

    private const STRONG = 'Kopi-Kedu7!';
    private const UA     = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0 Safari/537.36';

    private BaseConnection $conn;

    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = $this->createResetTables();
        $this->conn->table('research_studies')->insert(['code' => 'STUDI-UJI', 'name' => 'Studi uji', 'status' => 'active']);
        $this->conn->table('game_releases')->insert([
            'release_code' => 'GELITA-UJI', 'app_version' => '2.0.0', 'content_version' => 'kelas5-1',
            'asset_version' => '1', 'scoring_version' => 'bintang-1', 'is_active' => 1,
        ]);
        $this->conn->table('staff_users')->insert([
            // The only role that downloads (27 K14)
            'username' => 'ketua_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1,
        ]);

        foreach (['security', 'schoolDirectory', 'sessionService', 'eventService', 'gameContext', 'rawDataService'] as $name) {
            Services::resetSingle($name);
        }

        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $_SESSION = [];
        Factories::reset('models');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testDeviceSignalsCarryAValidatedPackage(): void
    {
        $this->assertSame(
            ['package_code' => 'pkt-2026-10-20-01', 'package_mode' => 'lengkap'],
            DeviceProfile::packageSignals(['package_code' => 'pkt-2026-10-20-01', 'package_mode' => 'lengkap']),
        );
        $this->assertSame(
            ['package_code' => null, 'package_mode' => null],
            DeviceProfile::packageSignals(['package_code' => '../etc/passwd', 'package_mode' => 'offline']),
        );

        $profile = $this->device('pkt-2026-10-20-01', 'daring');
        $this->assertSame('daring', $profile['package_mode']);
        $this->assertSame('pkt-2026-10-20-01', $profile['package_code']);
    }

    public function testSignUpWritesAppClientVersionAndPackageMode(): void
    {
        $session = $this->register('sekar_ayu', $this->device('pkt-2026-10-20-01', 'lengkap'));

        $this->assertSame('2.0.0+pkt-2026-10-20-01', $session['app_client_version']);
        $this->assertSame('pkt-2026-10-20-01', $session['package_code']);
        $this->assertSame('lengkap', $session['package_mode']);
        $this->assertSame(1, $this->conn->table('device_logs')->where('session_id', $session['id'])->countAllResults(), 'device_logs still written');
    }

    public function testWithoutPackageTheSessionIsOffMode(): void
    {
        // The current client without the Stage 2 hook: package_code null, mode nonaktif
        $hookless = $this->register('bima', $this->device(null, 'nonaktif'));
        $this->assertSame(['2.0.0', null, 'nonaktif'], [$hookless['app_client_version'], $hookless['package_code'], $hookless['package_mode']]);

        // No JavaScript at all (empty device_json): the server default
        $noScript = $this->register('citra', DeviceProfile::fromParts(self::UA, [], []));
        $this->assertSame(['2.0.0', SessionService::DEFAULT_PACKAGE_MODE], [$noScript['app_client_version'], $noScript['package_mode']]);
    }

    public function testResumedSessionKeepsItsPackageAndLogsTheChange(): void
    {
        $first = $this->register('dewi', $this->device('pkt-2026-10-20-01', 'lengkap'));
        service('sessionService')->logout();
        $_SESSION = [];

        $result = service('sessionService')->login('dewi', self::STRONG, 'id', $this->device('pkt-2026-10-27-01', 'lengkap'));

        $this->assertSame('ok', $result['status']);
        $this->assertSame((int) $first['id'], $result['session']->id, 'same session resumed');

        $row = $this->sessionRow((int) $first['id']);
        $this->assertSame(['2.0.0+pkt-2026-10-20-01', 'lengkap'], [$row['app_client_version'], $row['package_mode']], 'S8: no switch mid-session');

        $change = $this->conn->table('game_event_logs')->where('event_type', 'package_changed')->get()->getRowArray();
        $this->assertNotNull($change);
        $this->assertSame([
            'from' => 'pkt-2026-10-20-01', 'to' => 'pkt-2026-10-27-01', 'reason' => 'resume', 'from_mode' => 'lengkap', 'to_mode' => 'lengkap',
        ], json_decode((string) $change['payload_json'], true));
    }

    public function testSessionFromBeforeStageFiveIsFilledOnResume(): void
    {
        $first = $this->register('eka', $this->device(null, 'nonaktif'));
        $this->conn->table('game_sessions')->update(['app_client_version' => null, 'package_code' => null, 'package_mode' => null]);
        service('sessionService')->logout();
        $_SESSION = [];

        service('sessionService')->login('eka', self::STRONG, 'id', $this->device('pkt-2026-10-20-01', 'daring'));

        $row = $this->sessionRow((int) $first['id']);
        $this->assertSame(['2.0.0+pkt-2026-10-20-01', 'pkt-2026-10-20-01', 'daring'], [$row['app_client_version'], $row['package_code'], $row['package_mode']]);
        $this->assertSame(0, $this->conn->table('game_event_logs')->where('event_type', 'package_changed')->countAllResults());
    }

    /** The package columns are admin data since revision 10, part 3 (29 §5.5): the tech log, not the research sheet. */
    public function testPackageColumnsLiveInTheTechLog(): void
    {
        $this->register('fajar', $this->device('pkt-2026-10-20-01', 'lengkap'));
        $this->register('gita', $this->device(null, 'nonaktif'));

        $tech = service('techLogs')->page('sesi', [], 1, 25, null);
        $rows = array_column($tech['rows'], null, 'package_mode');

        $this->assertSame('2.0.0+pkt-2026-10-20-01', $rows['lengkap']['app_client_version']);
        $this->assertSame('pkt-2026-10-20-01', $rows['lengkap']['package_code']);
        $this->assertSame('2.0.0', $rows['nonaktif']['app_client_version']);
        $this->assertNull($rows['nonaktif']['package_code']);

        foreach (['app_client_version', 'package_code', 'package_mode'] as $column) {
            $this->assertNotContains($column, (new ExportService())->columns('sesi', true, true), "sesi.{$column}");
        }
    }

    /** @return array<string, mixed> DeviceProfile::capture() with the package the client reports */
    private function device(?string $code, string $mode): array
    {
        return DeviceProfile::fromParts(self::UA, [], [
            'screen_w' => 1280, 'screen_h' => 800, 'touch_points' => 5, 'package_code' => $code, 'package_mode' => $mode,
        ]);
    }

    /** @return array<string, mixed> the new game_sessions row */
    private function register(string $username, array $device): array
    {
        $_SESSION = [];
        $result   = service('sessionService')->registerAndStart([
            'username' => $username, 'password' => self::STRONG, 'assent' => true, 'school_name' => 'SD Uji',
            'country_code' => 'ID', 'province_code' => '34', 'device' => $device,
        ]);

        return $this->sessionRow($result['session']->id);
    }

    /** @return array<string, mixed> */
    private function sessionRow(int $id): array
    {
        return $this->conn->table('game_sessions')->where('id', $id)->get()->getRowArray();
    }

    /**
     * @param list<string> $sheets
     *
     * @return array<string, mixed>
     */
    private function buildCsv(array $sheets): array
    {
        $id = model(DataExportModel::class)->insert([
            'requested_by' => 1, 'format' => 'csv', 'scope_json' => ['filters' => [], 'sheets' => $sheets, 'labels' => 'code'],
            'anonymized' => 1, 'status' => 'running',
        ], true);

        $export = (new ExportService())->build((int) $id);
        $this->files[] = (string) $export['file_path'];
        $this->assertSame('done', $export['status'], (string) ($export['error_message'] ?? ''));

        return $export;
    }

    /** @return list<array<string, string>> */
    private function csv(string $raw): array
    {
        $handle = fopen('php://temp', 'w+b');
        fwrite($handle, substr($raw, 3));
        rewind($handle);

        $header = fgetcsv($handle, null, ',', '"', '');
        $rows   = [];

        while (($line = fgetcsv($handle, null, ',', '"', '')) !== false) {
            $rows[] = array_combine($header, $line);
        }

        fclose($handle);

        return $rows;
    }
}
