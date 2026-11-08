<?php

namespace App\Controllers\Admin;

use App\Models\FieldSessionModel;
use App\Models\ResearchStudyModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Coded consent per student — `/admin/sesi-lapangan/{id}/izin` (research
 * revision 10, part 4: 30 §5.2). Filled by P1 from L7, codes only, next to
 * the seat page. Capability `field_sessions.manage`; the admin never sees it.
 */
class ConsentController extends BaseAdminController
{
    public function edit(int $id): string
    {
        $fieldSession = $this->find($id);
        $roster       = service('consentService')->roster($fieldSession);

        return $this->panel('admin/consent/edit', lang('Admin.consentTitle') . ' · ' . $fieldSession['code'], [
            'fieldSession' => $fieldSession,
            'roster'       => $roster,
            'missing'      => service('consentService')->missingCount(array_column($roster, 'id')),
        ]);
    }

    public function save(int $id): RedirectResponse
    {
        $fieldSession = $this->find($id);
        $input        = $this->request->getPost('consent');
        $result       = service('consentService')->save($fieldSession, is_array($input) ? $input : [], $this->staffId() ?: null);
        $path         = 'admin/sesi-lapangan/' . $id . '/izin';
        $notes        = array_map(static fn (string $code): string => lang('Admin.consentDeactivated', [$code]), $result['deactivated']);

        if ($result['stale'] !== []) {
            return $this->back($path, lang('Admin.consentStale') . ' (' . implode(', ', $result['stale']) . ')');
        }

        if ($result['errors'] !== []) {
            $lines = array_map(static fn (string $code, string $message): string => $code . ': ' . $message, array_keys($result['errors']), $result['errors']);

            return $this->back($path, implode(' ', array_merge([lang('Admin.consentSaved', [$result['saved']])], $lines, $notes)));
        }

        return $this->done($path, implode(' ', array_merge([lang('Admin.consentSaved', [$result['saved']])], $notes)));
    }

    /** @return array<string, mixed> a field session of the active study, else 404 */
    private function find(int $id): array
    {
        $study = model(ResearchStudyModel::class)->activeStudy();
        $row   = model(FieldSessionModel::class)->find($id);

        if ($row === null || $study === null || (int) $row['study_id'] !== (int) $study['id']) {
            throw PageNotFoundException::forPageNotFound(lang('Admin.fieldSessionNotFound'));
        }

        return $row;
    }
}
