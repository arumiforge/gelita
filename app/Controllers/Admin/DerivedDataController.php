<?php

namespace App\Controllers\Admin;

use App\Models\ResearchStudyModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * The derived data layer in the panel (research revision 10, part 9: 35 §9,
 * §13): *Hitung ulang* and the run status for polling. Capability
 * `research.export` (ketua), no new capability (K14).
 */
class DerivedDataController extends BaseAdminController
{
    /**
     * POST /admin/studi/{id}/hitung-ulang: a new run even when not stale
     * (e.g. after a code fix). Synchronous, like `gelita:turunan:hitung`
     * (35 §18 no. 4); a run another request holds answers "sedang dihitung".
     */
    public function recompute(int $studyId): RedirectResponse
    {
        $back = (string) ($this->request->getPost('back') ?: 'admin/studi');
        $back = str_starts_with($back, 'admin/') ? $back : 'admin/studi';

        if (model(ResearchStudyModel::class)->find($studyId) === null) {
            return $this->back($back, lang('Admin.derivedNotFound'));
        }

        $result = service('derivedData')->compute($studyId, $this->staffId(), 'button');

        return match ($result['status']) {
            'done'  => $this->done($back, lang('Admin.derivedDone')),
            'busy'  => $this->back($back, lang('Admin.derivedRunning')),
            'off'   => $this->back($back, lang('Admin.derivedOffForStudy')),
            default => $this->back($back, lang('Admin.derivedFailed', [(string) ($result['run']['error'] ?? '')])),
        };
    }

    /** GET /admin/studi/{id}/hitung-ulang/status: JSON for the panel (no ids beyond the run). */
    public function status(int $studyId): ResponseInterface
    {
        if (model(ResearchStudyModel::class)->find($studyId) === null) {
            return $this->response->setStatusCode(404)->setJSON(['message' => lang('Admin.derivedNotFound')]);
        }

        $status = service('derivedData')->status($studyId);
        $run    = $status['current'];
        $latest = $status['latest'];

        return $this->response->setJSON([
            'field_mode' => $status['field_mode'],
            'running'    => $status['running'],
            'stale'      => $status['stale'],
            'draft'      => $status['draft'],
            'current'    => $run === null ? null : [
                'run_id' => (int) $run['id'], 'finished_at' => $run['finished_at'], 'dictionary_version' => $run['dictionary_version'],
                'idle_threshold_ms' => (int) $run['idle_threshold_ms'], 'rapid_guess_ms' => (int) $run['rapid_guess_ms'],
            ],
            'failed'     => $latest !== null && $latest['status'] === 'failed' ? (string) $latest['error'] : null,
        ]);
    }
}
