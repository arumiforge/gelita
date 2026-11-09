<?php

namespace App\Controllers\Admin;

use App\Models\FieldSessionModel;
use App\Services\FieldTestService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Start-test button and extra minutes on the field-session page (research
 * revision 10, part 8: 34 §5.3, §7.4, §12, K1, K3). Capability
 * `field_sessions.manage`, no new capability (K15).
 */
class FieldTestController extends BaseAdminController
{
    private const BASE = 'admin/sesi-lapangan/';

    /** POST /admin/sesi-lapangan/{id}/mulai-tes */
    public function start(int $id): RedirectResponse
    {
        $fieldSession = $this->find($id);

        try {
            (new FieldTestService())->start($fieldSession, $this->staffId() ?: null);
        } catch (\InvalidArgumentException $e) {
            return $this->back(self::BASE . $id, $e->getMessage());
        }

        return $this->done(self::BASE . $id, lang('Admin.fieldTestStartedNow'));
    }

    /** POST /admin/sesi-lapangan/{id}/tambah-waktu */
    public function extendTime(int $id): RedirectResponse
    {
        $fieldSession = $this->find($id);
        $minutes      = (int) $this->request->getPost('minutes');
        $block        = (string) $this->request->getPost('block');

        try {
            $code = (new FieldTestService())->extendTime($fieldSession, (string) $this->request->getPost('participant_code'), $block, $minutes, (string) $this->request->getPost('reason'), $this->staffId() ?: null);
        } catch (\InvalidArgumentException $e) {
            return $this->back(self::BASE . $id, $e->getMessage());
        }

        return $this->done(self::BASE . $id, lang('Admin.instrumentExtendDone', [$code, $minutes]));
    }

    /** @return array<string, mixed> */
    private function find(int $id): array
    {
        $study = model(\App\Models\ResearchStudyModel::class)->activeStudy();
        $row   = model(FieldSessionModel::class)->find($id);

        if ($row === null || $study === null || (int) $row['study_id'] !== (int) $study['id']) {
            throw PageNotFoundException::forPageNotFound(lang('Admin.fieldSessionNotFound'));
        }

        return $row;
    }
}
