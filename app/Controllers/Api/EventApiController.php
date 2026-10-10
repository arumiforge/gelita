<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Penerima batch raw event dari mesin permainan.
 * Idempotent terhadap `client_event_id`: kiriman ulang dibalas `duplicate`
 * tanpa menambah baris. The batch may carry `client_sent_at` (clock offset)
 * and a `dropped` report; see EventService::ingest().
 */
class EventApiController extends BaseApiController
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

        // Batch fields next to `events`: client_sent_at, dropped, dropped_id (§7.3)
        $envelope = array_intersect_key($body, array_flip(['client_sent_at', 'dropped', 'dropped_id']));
        $result   = service('eventService')->ingest($this->gameSession(), array_values($events), $envelope);

        return $this->ok($result + ['server_time' => $this->serverTime()]);
    }
}
