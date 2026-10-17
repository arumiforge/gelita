<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Penerima batch raw event dari mesin permainan.
 * Idempotent terhadap `client_event_id`: kiriman ulang dibalas `duplicate`
 * tanpa menambah baris. The batch may carry `client_sent_at` (clock offset)
 * and a `dropped` report; see EventService::ingest().
 *
 * One request carries both client channels (rencana-log-asinkron §5.1 R2):
 * an optional `audio` array (the entries /api/audio-events takes) with its
 * own `audio_dropped` + `audio_dropped_id` report. `events` may then be
 * empty. The answer has an `audio` key whenever the request had an `audio`
 * array. A successful batch also counts as the session heartbeat (R3).
 */
class EventApiController extends BaseApiController
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
        $audio  = $body['audio'] ?? null;

        if (! is_array($events)) {
            return $this->invalidPayload(['events' => lang('Game.errInvalidPayload')]);
        }

        if (array_key_exists('audio', $body) && ! is_array($audio)) {
            return $this->invalidPayload(['audio' => lang('Game.errInvalidPayload')]);
        }

        $max = config('Gelita')->maxEventsPerBatch;

        if (count($events) > $max || count($audio ?? []) > $max) {
            return $this->fail('TOO_MANY_EVENTS', lang('Game.errTooManyEvents', [$max]), 413);
        }

        $session = $this->gameSession();
        $service = service('eventService');

        // Batch fields next to `events`: client_sent_at, dropped, dropped_id (§7.3)
        $envelope = array_intersect_key($body, array_flip(['client_sent_at', 'dropped', 'dropped_id']));
        $result   = $service->ingest($session, array_values($events), $envelope);

        // Audio channel: the same report fields under their audio_ names
        $service->recordDropped($session, [
            'dropped'    => $body['audio_dropped'] ?? null,
            'dropped_id' => $body['audio_dropped_id'] ?? null,
        ], 'audio');

        if (is_array($audio)) {
            $sentAt          = is_string($body['client_sent_at'] ?? null) ? $body['client_sent_at'] : null;
            $result['audio'] = $service->recordAudioBatch($session, array_values($audio), $sentAt);
        }

        service('sessionService')->heartbeatIfDue($session->id);

        return $this->ok($result + ['server_time' => $this->serverTime()]);
    }
}
