<?php

namespace App\Controllers\Api;

use App\Models\ResearchPhaseModel;
use App\Models\ResearchStudyModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Bootstrap payload dan siklus hidup sesi dari sisi mesin permainan.
 */
class SessionApiController extends BaseApiController
{
    public function show(): ResponseInterface
    {
        $session     = $this->gameSession();
        $participant = service('gameContext')->participant;
        $study       = model(ResearchStudyModel::class)->find($session->study_id);
        $phase       = model(ResearchPhaseModel::class)->find($session->phase_id);

        return $this->ok([
            'session' => [
                'code'   => (string) $session->session_code,
                'locale' => $session->resolvedLocale(),
                'status' => (string) $session->status,
                'phase'  => (string) ($phase['code'] ?? ''),
                'study'  => (string) ($study['code'] ?? ''),
            ],
            'participant' => [
                'code'         => (string) $participant?->participant_code,
                'username'     => (string) $participant?->username,
                'display_name' => (string) $participant?->display_name,
            ],
            'progress'    => $this->progressSummary($session),
            'levels'      => $this->levelOverview($session),
            'server_time' => $this->serverTime(),
        ]);
    }

    public function setLocale(): ResponseInterface
    {
        $body   = $this->jsonBody() ?: (array) $this->request->getPost();
        $locale = (string) ($body['locale'] ?? '');

        if (! in_array($locale, config('Gelita')->locales, true)) {
            return $this->invalidPayload(['locale' => lang('Validation.valid_locale')]);
        }

        $session = $this->gameSession();

        // Mengganti bahasa tidak membuat sesi baru dan tidak mereset progres.
        service('sessionService')->setLocale($session->id, $locale);
        session()->set('locale', $locale);
        service('gameContext')->refreshSession();

        return $this->ok(['locale' => $locale, 'server_time' => $this->serverTime()]);
    }

    public function heartbeat(): ResponseInterface
    {
        $session = $this->gameSession();

        service('sessionService')->heartbeat($session->id);

        return $this->ok(['server_time' => $this->serverTime()]);
    }

    public function complete(): ResponseInterface
    {
        $session   = $this->gameSession();
        $completed = service('sessionService')->completeIfFinished($session->id);

        service('gameContext')->refreshSession();

        return $this->ok([
            'completed' => $completed,
            'status'    => (string) (service('gameContext')->session?->status ?? $session->status),
            'progress'  => $this->progressSummary($session),
        ]);
    }
}
