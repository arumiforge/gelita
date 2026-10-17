<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Telemetry audio: baris `audio_usage_events` + raw event pendampingnya.
 * `challenge_attempt_id` hanya diterima bila attempt itu milik sesi berjalan.
 *
 * Aksi yang diterima: AudioUsageEventModel::ACTIONS — `play` (pemain menekan
 * putar), `autoplay` (layar bernarasi memutar sendiri), `pause`, `replay`,
 * `complete`. Other actions are rejected per entry (`audio_event_invalid`)
 * by EventService::recordAudioBatch().
 *
 * Kept for tabs opened before the combined batch (rencana-log-asinkron
 * §5.1 R2): new clients send audio inside POST /api/events.
 */
class AudioApiController extends BaseApiController
{
    public function ingest(): ResponseInterface
    {
        // Telemetry never writes $_SESSION: free the session lock at once (R5)
        session()->close();

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

        $session = $this->gameSession();
        $service = service('eventService');
        $sentAt  = is_string($body['client_sent_at'] ?? null) ? $body['client_sent_at'] : null;

        // Events the client audio queue had to discard (rencana-paket-aset §7.3)
        $service->recordDropped($session, $body, 'audio');

        $result = $service->recordAudioBatch($session, array_values($events), $sentAt);

        return $this->ok($result + ['server_time' => $this->serverTime()]);
    }
}
