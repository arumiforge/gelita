<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\FieldSessionModel;
use App\Services\FieldSheetService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Field sheets of one field session (research revision 10, part 3: 29 §10,
 * §13), `/admin/sesi-lapangan/{id}/lembar…`, for `peneliti` and `ketua`
 * (`field_sessions.manage`): the filled template, the upload with its
 * preview, and the save. The preview lives in the staff session (the file is
 * not kept) for 30 minutes; save refuses a preview that is gone or belongs to
 * another field session.
 */
class FieldSheetController extends BaseAdminController
{
    private const BASE = 'admin/sesi-lapangan';

    public function template(int $id): ResponseInterface
    {
        $fieldSession = $this->find($id);
        $path         = tempnam(sys_get_temp_dir(), 'lembar-');

        try {
            $this->sheets()->writeTemplate($fieldSession, $path);
            $body = (string) file_get_contents($path);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }

        model(AuditLogModel::class)->record('field_sheet_template', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'field_session',
            'target_id'     => (string) $id,
            'metadata'      => ['field_session_code' => $fieldSession['code'], 'sheets' => $this->sheets()->expectedSheets($fieldSession)],
        ]);

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="lembar-' . $fieldSession['code'] . '.xlsx"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($body);
    }

    public function uploadForm(int $id): string
    {
        $fieldSession = $this->find($id);

        return $this->panel('admin/field-sessions/sheets', lang('Admin.fieldSheetUpload'), [
            'fieldSession' => $fieldSession,
            'status'       => $this->sheets()->status($fieldSession),
            'preview'      => null,
        ]);
    }

    public function preview(int $id): string|RedirectResponse
    {
        $fieldSession = $this->find($id);
        $file         = $this->request->getFile('sheet_file');
        $back         = self::BASE . '/' . $id . '/lembar/unggah';

        if ($file === null || ! $file->isValid() || ! in_array(strtolower($file->getClientExtension()), ['xlsx', 'csv'], true)) {
            return $this->back($back, lang('Admin.fieldSheetFileInvalid'));
        }

        try {
            $preview = $this->sheets()->preview($fieldSession, $file->getTempName(), $file->getClientName(), (string) $this->request->getPost('purpose'));
        } catch (\DomainException $e) {
            return $this->back($back, $e->getMessage());
        }

        session()->set(FieldSheetService::SESSION_KEY, ['field_session_id' => $id, 'at' => time(), 'preview' => $preview]);

        return $this->panel('admin/field-sessions/sheets', lang('Admin.fieldSheetPreviewTitle'), [
            'fieldSession' => $fieldSession,
            'status'       => $this->sheets()->status($fieldSession),
            'preview'      => $preview,
        ]);
    }

    public function save(int $id): RedirectResponse
    {
        $fieldSession = $this->find($id);
        $stored       = session(FieldSheetService::SESSION_KEY);
        $back         = self::BASE . '/' . $id . '/lembar/unggah';

        if (! is_array($stored) || (int) ($stored['field_session_id'] ?? 0) !== $id || time() - (int) ($stored['at'] ?? 0) > FieldSheetService::PREVIEW_TTL) {
            return $this->back($back, lang('Admin.fieldSheetPreviewGone'));
        }

        try {
            $saved = $this->sheets()->save($fieldSession, $stored['preview'], $this->staffId() ?: null, (string) $this->request->getPost('names_confirmed') === '1');
        } catch (\DomainException $e) {
            return $this->back($back, $e->getMessage());
        }

        session()->remove(FieldSheetService::SESSION_KEY);

        $messages = array_map(static fn (array $s): string => $s['replaced']
            ? lang('Admin.fieldSheetReplaced', [$s['sheet']])
            : lang('Admin.fieldSheetSaved', [$s['sheet'], $s['rows']]), $saved);

        return $this->done($back, implode(' ', $messages));
    }

    /** A field session of the active study, or 404 (as FieldSessionController). */
    private function find(int $id): array
    {
        $study = model(\App\Models\ResearchStudyModel::class)->activeStudy();
        $row   = model(FieldSessionModel::class)->find($id);

        if ($row === null || $study === null || (int) $row['study_id'] !== (int) $study['id']) {
            throw PageNotFoundException::forPageNotFound(lang('Admin.fieldSessionNotFound'));
        }

        return $row;
    }

    private function sheets(): FieldSheetService
    {
        return service('fieldSheets');
    }
}
