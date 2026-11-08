<?php

namespace App\Controllers\Admin;

use App\Models\ResearchStudyModel;
use App\Services\StudyClosureService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Penutupan studi — `/admin/studi/{id}/penutupan` (research revision 10,
 * part 4: 30 §10.2). Seen by the admin and `ketua` (`studies.view`); the
 * admin runs the replacement (`identities.replace`) after *Hitung* and the
 * word `GANTI`; `ketua` checks the feedback for names (`research.export`).
 */
class StudyClosureController extends BaseAdminController
{
    public function show(int $id): string
    {
        $study = $this->study($id);

        return $this->panel('admin/closure/show', lang('Admin.closureTitle') . ' · ' . $study['code'], [
            'breadcrumb' => [lang('Admin.menuStudy') => 'admin/studi', lang('Admin.closureTitle') => null],
            'study'      => $study,
            'conditions' => service('studyClosure')->conditions($study),
            'preview'    => session('closure_preview'),
            'confirm'    => StudyClosureService::CONFIRM_WORD,
        ]);
    }

    public function preview(int $id): RedirectResponse
    {
        $study = $this->study($id);
        $path  = 'admin/studi/' . $id . '/penutupan';

        if (in_array(false, service('studyClosure')->conditions($study), true)) {
            return $this->back($path, lang('Admin.closureNotReady'));
        }

        return redirect()->to(site_url($path))->with('closure_preview', service('studyClosure')->preview($id));
    }

    public function run(int $id): RedirectResponse
    {
        $this->study($id);
        $path = 'admin/studi/' . $id . '/penutupan';

        if ((string) $this->request->getPost('confirm') !== StudyClosureService::CONFIRM_WORD) {
            return $this->back($path, lang('Admin.closureTypeWord', [StudyClosureService::CONFIRM_WORD]));
        }

        try {
            $counts = service('studyClosure')->replaceIdentities($id, $this->staffId());
        } catch (\DomainException $e) {
            return $this->back($path, $e->getMessage());
        }

        return $this->done($path, lang('Admin.closureDone', [$counts['participants'], $counts['schools']]));
    }

    public function feedbackNames(int $id): string
    {
        $study = $this->study($id);

        return $this->panel('admin/closure/feedback', lang('Admin.closureFeedbackTitle'), [
            'breadcrumb' => [lang('Admin.closureTitle') => 'admin/studi/' . $id . '/penutupan', lang('Admin.closureFeedbackTitle') => null],
            'study'      => $study,
            'rows'       => service('studyClosure')->maskedFeedback($id),
            'fields'     => StudyClosureService::FEEDBACK_FIELDS,
        ]);
    }

    public function saveFeedbackNames(int $id): RedirectResponse
    {
        $study = $this->study($id);

        if ($study['identities_replaced_at'] !== null) {
            return $this->back('admin/studi/' . $id . '/penutupan', lang('Admin.closureReplaced'));
        }

        $texts = $this->request->getPost('texts');
        $rows  = service('studyClosure')->saveFeedbackNames($id, is_array($texts) ? $texts : [], $this->staffId());

        return $this->done('admin/studi/' . $id . '/penutupan', lang('Admin.closureFeedbackSaved', [$rows]));
    }

    /** @return array<string, mixed> */
    private function study(int $id): array
    {
        $study = model(ResearchStudyModel::class)->find($id);

        if ($study === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $study;
    }
}
