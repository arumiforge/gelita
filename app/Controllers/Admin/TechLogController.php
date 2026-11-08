<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Services\TechLogService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * `/admin/log-teknis` (research revision 10, part 3: 29 §5.6, K15): the
 * admin's tech log, three tabs from TechLogService, each with its CSV
 * (audit `tech_log_csv`). No research data: not research data either, so the
 * rule "the admin does not download research data" holds. Route filter
 * `staffCan:tech_logs.view`.
 */
class TechLogController extends BaseAdminController
{
    /** Tab code → Admin.php key of its title. */
    public const TABS = [
        'perangkat' => 'Admin.techLogTabDevices',
        'event'     => 'Admin.techLogTabEvents',
        'sesi'      => 'Admin.techLogTabSessions',
    ];

    public function index(): RedirectResponse
    {
        return redirect()->to(site_url('admin/log-teknis/perangkat'));
    }

    public function table(string $tab): string
    {
        $filters = $this->techFilters();

        return $this->panel('admin/tech-log/index', lang('Admin.menuTechLog'), [
            'tab'     => $tab,
            'tabs'    => array_map(static fn (string $key): string => lang($key), self::TABS),
            'table'   => $this->rawPage($this->service(), $tab, $filters),
            'filters' => $filters,
            'summary' => $this->service()->summary(),
        ]);
    }

    public function csv(string $tab): ResponseInterface
    {
        $filters  = $this->techFilters();
        $rows     = 0;
        $service  = $this->service();
        $response = $this->csvResponse('gelita-log-teknis-' . $tab, static function ($handle) use ($service, $tab, $filters, &$rows): void {
            $rows = $service->streamCsv($tab, $filters, $handle);
        });

        model(AuditLogModel::class)->record('tech_log_csv', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'tech_log',
            'target_id'     => $tab,
            'metadata'      => ['tab' => $tab, 'filters' => $filters, 'row_count' => $rows],
        ]);

        return $response;
    }

    /** @return array{participant_code?: string} */
    private function techFilters(): array
    {
        $code = trim((string) $this->request->getGet('participant_code'));

        return $code === '' ? [] : ['participant_code' => mb_substr($code, 0, 20)];
    }

    private function service(): TechLogService
    {
        return service('techLogs');
    }
}
