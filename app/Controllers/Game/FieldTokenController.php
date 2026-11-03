<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * The session token screen and the "see you" screen (research revision 10,
 * part 2: 28 §6.4, §7). Both only need a signed-in student (filter
 * `participantAuth`), never a game session, and work without JavaScript.
 */
class FieldTokenController extends BaseController
{
    /** Token refusal reason => Game language key (28 §16). */
    private const MESSAGES = [
        'token_unknown' => 'Game.tokenUnknown',
        'not_open_yet'  => 'Game.tokenNotOpenYet',
        'closed'        => 'Game.tokenClosed',
        'other_class'   => 'Game.tokenOtherClass',
        'not_member'    => 'Game.tokenNotMember',
        'other_study'   => 'Game.tokenOtherStudy',
        'too_many'      => 'Game.tokenTooMany',
    ];

    /** GET /token */
    public function form(): string|RedirectResponse
    {
        if (! service('fieldSessions')->fieldModeOn()) {
            return redirect()->to(site_url('gerbang'));
        }

        // Already inside an open field session: no second token (K7)
        $session = $this->currentSession();

        if ($session !== null && service('fieldSessions')->sessionIsOpen($session)) {
            return redirect()->to(site_url('gerbang'));
        }

        return view('game/token', [
            // No HUD account rows on these screens (the shared renderer may still hold earlier view data)
            'participant'  => null,
            'progress'     => null,
            'locale'       => $this->locale,
            'researchCode' => $this->participant()?->participant_code,
            'errors'       => session('errors') ?? [],
        ]);
    }

    /** POST /token */
    public function submit(): RedirectResponse
    {
        $fields      = service('fieldSessions');
        $participant = $this->participant();

        if (! $fields->fieldModeOn() || $participant === null) {
            return redirect()->to(site_url('gerbang'));
        }

        $key = 'p' . $participant->id;

        if ($fields->throttled($key)) {
            return $this->refuse('too_many');
        }

        $check = $fields->checkToken((string) $this->request->getPost('token'), $participant);

        if ($check['status'] !== 'ok') {
            return $this->refuse($fields->registerFailure($key) ? 'too_many' : $check['status']);
        }

        $fields->clearFailures($key);

        $fieldSession = $check['field_session'];
        service('sessionService')->enterFieldSession(
            $participant->id,
            $fieldSession,
            (string) (session('locale') ?? $this->locale),
            $this->deviceInfo(),
        );

        $entry = $fields->planEntry($fieldSession);

        // Meeting 1 starts with the opening story; later meetings with the map (28 §6.4)
        return redirect()->to(site_url($entry['intro'] ? 'intro' : 'peta'));
    }

    /** GET /sampai-jumpa */
    public function closed(): string
    {
        return view('game/session-closed', [
            'participant' => null,
            'progress'    => null,
            'locale'    => $this->locale,
            'fieldMode' => service('fieldSessions')->fieldModeOn(),
        ]);
    }

    private function refuse(string $reason): RedirectResponse
    {
        return redirect()->to(site_url('token'))->with('errors', [
            'token' => lang(self::MESSAGES[$reason] ?? 'Game.tokenUnknown'),
        ]);
    }

    private function participant(): ?\App\Entities\Participant
    {
        return model(ParticipantModel::class)->find((int) session('participant_id'));
    }

    private function currentSession(): ?\App\Entities\GameSession
    {
        $id = (int) session('game_session_id');

        return $id > 0 ? model(GameSessionModel::class)->where('participant_id', (int) session('participant_id'))->find($id) : null;
    }
}
