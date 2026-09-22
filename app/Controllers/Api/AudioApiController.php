<?php

namespace App\Controllers\Api;

use App\Models\ChallengeAttemptModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Telemetry audio: baris `audio_usage_events` + raw event pendampingnya.
 * `challenge_attempt_id` hanya diterima bila attempt itu milik sesi berjalan.
 */
class AudioApiController extends BaseApiController
{
    public function ingest(): ResponseInterface
    {
        if ($limited = $this->rateLimited('events')) {
            return $limited;
        }

        $body   = $this->jsonBody();
        $events = $body['events'] ?? null;

        if (! is_array($events)) {
            return $this->invalidPayload(['events' => lang('Game.errInvalidPayload')]);
        }

        $max = config('Gelita')->maxEventsPerBatch;

        if (count($events) > $max) {
            return $this->fail('TOO_MANY_EVENTS', lang('Game.errTooManyEvents', [$max]), 413);
        }

        $session  = $this->gameSession();
        $service  = service('eventService');
        $accepted = 0;
        $rejected = [];

        foreach (array_values($events) as $index => $event) {
            if (! is_array($event)) {
                $rejected[] = ['index' => $index, 'reason' => 'format_invalid'];

                continue;
            }

            $attemptId = isset($event['challenge_attempt_id']) ? (int) $event['challenge_attempt_id'] : 0;

            if ($attemptId > 0 && ! $this->ownsAttempt($attemptId)) {
                $rejected[] = ['index' => $index, 'reason' => 'attempt_not_in_session'];

                continue;
            }

            try {
                $service->recordAudio($session, $event);
                $accepted++;
            } catch (\InvalidArgumentException) {
                $rejected[] = ['index' => $index, 'reason' => 'audio_event_invalid'];
            }
        }

        return $this->ok([
            'accepted'    => $accepted,
            'rejected'    => $rejected,
            'server_time' => $this->serverTime(),
        ]);
    }

    private function ownsAttempt(int $attemptId): bool
    {
        $attempt = model(ChallengeAttemptModel::class)->find($attemptId);

        return $attempt !== null && $attempt->session_id === $this->gameSession()->id;
    }
}
