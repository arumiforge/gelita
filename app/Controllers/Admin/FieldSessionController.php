<?php

namespace App\Controllers\Admin;

use App\Models\FieldSessionModel;
use App\Models\FieldSessionTokenModel;
use App\Models\SchoolModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Field sessions in the panel (research revision 10, part 2: 28 §6, §11,
 * §15): create, change hours, new token, close, make-ups, and the L2 seats.
 * Capability `field_sessions.manage` (researchers and the head researcher).
 * Researchers see school research codes and participant codes only.
 */
class FieldSessionController extends BaseAdminController
{
    private const BASE = 'admin/sesi-lapangan';

    public function index(): string
    {
        $fields = service('fieldSessions');
        $study  = model(\App\Models\ResearchStudyModel::class)->activeStudy();
        $rows   = $study === null ? [] : model(FieldSessionModel::class)->forStudy((int) $study['id']);

        foreach ($rows as &$row) {
            $row['state'] = $fields->state($row);
            $row['token'] = $fields->currentToken((int) $row['id']);
        }

        unset($row);

        return $this->panel('admin/field-sessions/index', lang('Admin.menuFieldSessions'), [
            'study'     => $study,
            'fieldMode' => $fields->fieldModeOn(),
            'sessions'  => $rows,
        ]);
    }

    public function create(): string
    {
        $study = model(\App\Models\ResearchStudyModel::class)->activeStudy();
        $plan  = (string) ($study['meeting_plan'] ?? 'lima_pertemuan');

        return $this->panel('admin/field-sessions/create', lang('Admin.fieldSessionNew'), [
            'fieldMode'    => service('fieldSessions')->fieldModeOn(),
            'schools'      => model(SchoolModel::class)->select('id, research_code')->where('research_code IS NOT NULL', null, false)->orderBy('research_code', 'ASC')->findAll(),
            'meetings'     => service('fieldSessions')->planMeetings($plan),
            'chooseRegion' => service('fieldSessions')->planChoosesRegion($plan),
            'regions'      => service('contentRepository')->levels(),
        ]);
    }

    public function store(): RedirectResponse
    {
        try {
            $created = service('fieldSessions')->create($this->request->getPost() ?? [], $this->staffId() ?: null);
        } catch (\InvalidArgumentException $e) {
            return redirect()->to(site_url(self::BASE . '/baru'))->withInput()->with('error', $e->getMessage());
        }

        return $this->done(self::BASE . '/' . $created['field_session']['id'], lang('Admin.fieldSessionCreated', [$created['field_session']['code'], $created['token']]));
    }

    public function show(int $id): string
    {
        $fields       = service('fieldSessions');
        $fieldSession = $this->find($id);
        $roster       = $fields->roster($fieldSession);
        $entered      = $fields->enteredIds($id);

        return $this->panel('admin/field-sessions/show', (string) $fieldSession['code'], [
            'fieldSession' => $fieldSession,
            'state'        => $fields->state($fieldSession),
            'token'        => $fields->currentToken($id),
            'tokens'       => model(FieldSessionTokenModel::class)->history($id),
            'plan'         => $fields->planEntry($fieldSession),
            'rosterCount'  => count($roster),
            'enteredCount' => count(array_intersect(array_column($roster, 'id'), $entered)),
            'codesUsed'    => service('participantCodes')->used((int) $fieldSession['school_id'], (string) $fieldSession['rombel']),
            'codesMax'     => config('Gelita')->participantCodeMax,
            'makeups'      => model(FieldSessionModel::class)->where('main_session_id', $id)->orderBy('makeup_no', 'ASC')->findAll(),
        ]);
    }

    public function updateHours(int $id): RedirectResponse
    {
        $this->find($id);

        try {
            service('fieldSessions')->updateHours($id, [
                'open_now' => (string) $this->request->getPost('open_now') === '1',
                'closes'   => (string) $this->request->getPost('closes'),
            ], $this->staffId() ?: null);
        } catch (\InvalidArgumentException $e) {
            return $this->back(self::BASE . '/' . $id, $e->getMessage());
        }

        return $this->done(self::BASE . '/' . $id, lang('Admin.fieldSessionHoursSaved'));
    }

    public function regenerateToken(int $id): RedirectResponse
    {
        $this->find($id);

        try {
            $token = service('fieldSessions')->regenerateToken($id, $this->staffId() ?: null);
        } catch (\InvalidArgumentException $e) {
            return $this->back(self::BASE . '/' . $id, $e->getMessage());
        }

        return $this->done(self::BASE . '/' . $id, lang('Admin.fieldSessionTokenCreated', [$token]));
    }

    public function close(int $id): RedirectResponse
    {
        $this->find($id);
        service('fieldSessions')->close($id, $this->staffId() ?: null);

        return $this->done(self::BASE . '/' . $id, lang('Admin.fieldSessionClosedNow'));
    }

    public function seats(int $id): string
    {
        $fieldSession = $this->find($id);
        $seats        = new \App\Services\SeatService();
        $roster       = $seats->roster($fieldSession);

        return $this->panel('admin/field-sessions/seats', lang('Admin.fieldSessionSeats') . ' · ' . $fieldSession['code'], [
            'fieldSession' => $fieldSession,
            'roster'       => $roster,
            'summary'      => $seats->summary($roster),
        ]);
    }

    public function saveSeats(int $id): RedirectResponse
    {
        $fieldSession = $this->find($id);
        $input        = $this->request->getPost('seats');
        $result       = (new \App\Services\SeatService())->save($fieldSession, is_array($input) ? $input : [], $this->staffId() ?: null);
        $path         = self::BASE . '/' . $id . '/kursi';

        if ($result['invalid'] !== [] || $result['locked'] !== []) {
            $codes = array_merge($result['invalid'], $result['locked']);

            return $this->back($path, lang('Admin.seatsSaved', [$result['saved'], $result['changed']]) . ' '
                . lang('Admin.seatInvalid', [\App\Libraries\SeatRules::MAX_NO, implode(', ', $codes)]));
        }

        return $this->done($path, lang('Admin.seatsSaved', [$result['saved'], $result['changed']]));
    }

    public function makeupForm(int $id): string
    {
        $fields = service('fieldSessions');
        $main   = $this->find($id);

        return $this->panel('admin/field-sessions/makeup', lang('Admin.fieldSessionMakeup') . ' · ' . $main['code'], [
            'main'     => $main,
            'state'    => $fields->state($main),
            'roster'   => $fields->roster($main),
            'checked'  => $fields->defaultMakeupMembers($main),
        ]);
    }

    public function storeMakeup(int $id): RedirectResponse
    {
        $this->find($id);
        $members = $this->request->getPost('members');

        try {
            $created = service('fieldSessions')->createMakeup($id, $this->request->getPost() ?? [], is_array($members) ? array_map('intval', $members) : [], $this->staffId() ?: null);
        } catch (\InvalidArgumentException $e) {
            return $this->back(self::BASE . '/' . $id . '/susulan', $e->getMessage());
        }

        return $this->done(self::BASE . '/' . $created['field_session']['id'], lang('Admin.fieldSessionCreated', [$created['field_session']['code'], $created['token']]));
    }

    /** @return array<string, mixed> a field session of the active study, else 404 */
    private function find(int $id): array
    {
        $study = model(\App\Models\ResearchStudyModel::class)->activeStudy();
        $row   = model(FieldSessionModel::class)->select('field_sessions.*, schools.research_code')
            ->join('schools', 'schools.id = field_sessions.school_id', 'left')
            ->where('field_sessions.id', $id)
            ->first();

        if ($row === null || $study === null || (int) $row['study_id'] !== (int) $study['id']) {
            throw PageNotFoundException::forPageNotFound(lang('Admin.fieldSessionNotFound'));
        }

        return $row;
    }
}
