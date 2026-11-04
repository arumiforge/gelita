<?php

namespace App\Controllers\Admin;

use App\Models\ParticipantModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * One child's data copy — `/admin/peserta/salinan` (research revision 10,
 * part 4: 30 §8). Head researcher only (`research.export`). The ZIP is made
 * on request and sent at once, never kept on the server.
 */
class ParticipantCopyController extends BaseAdminController
{
    public function form(): string
    {
        $code  = strtoupper(trim((string) ($this->request->getGet('kode') ?? '')));
        $found = $code === '' ? null : model(ParticipantModel::class)->findByCode($code);

        return $this->panel('admin/participants/copy', lang('Admin.copyTitle'), [
            'code'    => $code,
            'found'   => $found === null ? null : ['id' => $found->id, 'participant_code' => $found->participant_code],
            'hasRisk' => $found !== null && service('dataRequests')->hasRiskAnswers($found->id),
        ]);
    }

    public function download(): ResponseInterface|RedirectResponse
    {
        $participant = model(ParticipantModel::class)->findByCode(strtoupper(trim((string) $this->request->getPost('participant_code'))));

        if ($participant === null) {
            return $this->back('admin/peserta/salinan', lang('Admin.resetCodeNotFound'));
        }

        try {
            $zip = service('participantCopy')->build(
                $participant->id,
                (string) $this->request->getPost('purpose'),
                (string) $this->request->getPost('risk_answers'),
                $this->staffId(),
            );
        } catch (\DomainException $e) {
            return $this->back('admin/peserta/salinan', $e->getMessage());
        }

        return $this->response
            ->setHeader('Content-Type', 'application/zip')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $zip['name'] . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($zip['body']);
    }
}
