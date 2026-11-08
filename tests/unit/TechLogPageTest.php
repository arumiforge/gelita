<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * `/admin/log-teknis` (research revision 10, part 3: 29 §5.6, K15): the
 * admin sees three tabs and their CSV (audited), never a research event or an
 * answer; peneliti and ketua get 404.
 *
 * @internal
 */
final class TechLogPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    private const STAFF = ['admin' => 1, 'ketua' => 2, 'peneliti' => 3];

    protected function setUp(): void
    {
        parent::setUp();
        $db = $this->createRawDataWorld();
        $db->table('game_event_logs')->insert([
            'event_uuid' => '00000000-0000-4000-8000-000000000555', 'client_event_id' => 'drop-1', 'session_id' => 1, 'participant_id' => 1,
            'event_type' => 'events_dropped', 'occurred_at' => date('Y-m-d H:i:s'), 'sequence_no' => 7, 'payload_json' => '{"dropped":3,"channel":"events"}',
        ]);
        $db->table('game_sessions')->where('id', 1)->update(['os_name' => 'Android', 'browser_name' => 'Chrome', 'package_mode' => 'daring']);
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testAdminSeesThreeTabsAndTheirCsv(): void
    {
        $devices = $this->get('admin', 'admin/log-teknis/perangkat');
        $this->assertStringContainsString(lang('Admin.techLogIntro'), $devices);
        $this->assertStringContainsString('Android', $devices);
        $this->assertStringContainsString('GLT-000001', $devices);
        $this->assertStringNotContainsString('Ani Lestari', $devices, 'codes, no names');

        $events = $this->get('admin', 'admin/log-teknis/event');
        $this->assertStringContainsString('events_dropped', $events);
        foreach (['screen_viewed', 'hint_opened', 'challenge_completed'] as $research) {
            $this->assertStringNotContainsString($research, $events, $research);
        }

        $sessions = $this->get('admin', 'admin/log-teknis/sesi');
        $this->assertStringContainsString('daring', $sessions);
        $this->assertStringNotContainsString('verdict', $sessions, 'no answers');

        $csv = $this->get('admin', 'admin/log-teknis/event/csv');
        $this->assertStringContainsString('session_code,participant_code,event_type', $csv);
        $this->assertStringContainsString('events_dropped', $csv);
        $this->assertStringNotContainsString('hint_opened', $csv);

        $audit = db_connect()->table('audit_logs')->where('action', 'tech_log_csv')->get()->getRowArray();
        $this->assertSame(['event', 1], [$audit['target_id'], json_decode((string) $audit['metadata_json'], true)['row_count']]);

        $this->assertStringContainsString('admin/log-teknis', $this->get('admin', 'admin'), 'menu item');
        $this->assertSame(3, service('techLogs')->summary()['dropped_7d']);
    }

    public function testResearchersGetNotFound(): void
    {
        $routes = service('routes');
        $routes->loadRoutes();
        $routes->setHTTPVerb('GET');

        foreach (['admin/log-teknis', 'admin/log-teknis/(perangkat|event|sesi)', 'admin/log-teknis/(perangkat|event|sesi)/csv'] as $path) {
            $this->assertSame(['staffAuth', 'staffCan:tech_logs.view'], $routes->getFiltersForRoute($path), $path);
        }

        foreach (['ketua', 'peneliti'] as $role) {
            $this->assertFalse(App\Libraries\StaffAccess::allows($role, 'tech_logs.view'), $role);
        }

        // The filter answers 404 for a role without the capability
        $_SESSION['staff_role'] = 'ketua';

        try {
            $this->expectException(PageNotFoundException::class);
            (new App\Filters\StaffCanFilter())->before(service('request'), ['tech_logs.view']);
        } finally {
            unset($_SESSION['staff_role']);
        }
    }

    private function get(string $role, string $path): string
    {
        return (string) $this->visit($role, $path)->getBody();
    }

    private function visit(string $role, string $path): \CodeIgniter\Test\TestResponse
    {
        foreach (['security', 'techLogs', 'renderer'] as $name) {
            Services::resetSingle($name);
        }

        return $this->withSession(['staff_id' => self::STAFF[$role], 'staff_role' => $role, 'staff_name' => ucfirst($role)])->call(Method::GET, $path);
    }
}
