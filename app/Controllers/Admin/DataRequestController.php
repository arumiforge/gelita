<?php

namespace App\Controllers\Admin;

use App\Services\DataRequestService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Parents' data requests — `/admin/permintaan-data` (research revision 10,
 * part 4: 30 §7, §14). `peneliti` file and see their own, `ketua` confirms
 * or rejects and sees all, the admin sees all and runs them in Tata kelola.
 * Codes only; names never go into a request.
 */
class DataRequestController extends BaseAdminController
{
    private const BASE = 'admin/permintaan-data';

    public function index(): string
    {
        $service = service('dataRequests');

        return $this->panel('admin/data-requests/index', lang('Admin.menuDataRequests'), [
            'requests'  => array_map(fn (array $row): array => $row + ['deadline' => $service->deadlines($row)], $service->visibleTo($this->role(), $this->staffId())),
        ]);
    }

    public function create(): string
    {
        $code = trim((string) ($this->request->getGet('kode') ?? ''));

        return $this->panel('admin/data-requests/create', lang('Admin.requestNew'), [
            'breadcrumb' => [lang('Admin.menuDataRequests') => self::BASE, lang('Admin.requestNew') => null],
            'code'       => $code,
            'found'      => $code === '' ? null : service('dataRequests')->lookup($code),
        ]);
    }

    public function store(): RedirectResponse
    {
        $result = service('dataRequests')->create((array) $this->request->getPost(), $this->staffId(), $this->role());

        return match ($result['status']) {
            'created' => $this->done(self::BASE . '/' . $result['id'], lang('Admin.requestCreated')),
            'nothing' => $this->done(self::BASE, lang('Admin.requestNothingToDelete')),
            default   => redirect()->to(site_url(self::BASE . '/baru'))->withInput()->with('error', $result['message']),
        };
    }

    public function show(int $id): string
    {
        $service = service('dataRequests');
        $row     = $this->visible($id);

        return $this->panel('admin/data-requests/show', lang('Admin.menuDataRequests') . ' · ' . $id, [
            'breadcrumb' => [lang('Admin.menuDataRequests') => self::BASE, '#' . $id => null],
            'request'    => $row,
            'deadline'   => $service->deadlines($row),
            'checklist'  => $service->checklist($row),
            'items'      => DataRequestService::itemsFor((string) $row['kind']),
            'copies'     => $this->can('deletion.confirm') ? $service->researcherCopies($row) : [],
            'counts'     => (array) (service('retentionService')->decode($row)['counts'] ?? []),
            'role'       => $this->role(),
            'mine'       => (int) $row['requested_by'] === $this->staffId(),
        ]);
    }

    public function confirm(int $id): RedirectResponse
    {
        return $this->act($id, fn () => service('dataRequests')->confirm($id, $this->staffId()), lang('Admin.requestConfirmed'));
    }

    public function reject(int $id): RedirectResponse
    {
        return $this->act($id, fn () => service('dataRequests')->reject($id, $this->staffId(), (string) $this->request->getPost('reason')), lang('Admin.requestRejected'));
    }

    public function cancel(int $id): RedirectResponse
    {
        return $this->act($id, fn () => service('dataRequests')->cancel($id, $this->staffId()), lang('Admin.requestCancelled'));
    }

    public function tick(int $id): RedirectResponse
    {
        return $this->act($id, fn () => service('dataRequests')->tick($id, (string) $this->request->getPost('item'), $this->staffId(), $this->role()), lang('Admin.requestTicked'));
    }

    public function minutes(int $id): ResponseInterface
    {
        $row  = $this->visible($id);
        $path = service('dataRequests')->minutesPdf($row);
        $body = (string) file_get_contents($path);
        unlink($path);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="berita-acara-' . $id . '.pdf"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($body);
    }

    private function act(int $id, \Closure $action, string $message): RedirectResponse
    {
        $this->visible($id);

        try {
            $action();
        } catch (\DomainException $e) {
            return $this->back(self::BASE . '/' . $id, $e->getMessage());
        }

        return $this->done(self::BASE . '/' . $id, $message);
    }

    /** @return array<string, mixed> the request when the caller may see it, else 404 */
    private function visible(int $id): array
    {
        try {
            $row = service('dataRequests')->find($id);
        } catch (\DomainException) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! service('dataRequests')->canSee($row, $this->role(), $this->staffId())) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $row;
    }

    private function role(): string
    {
        return (string) session('staff_role');
    }
}
