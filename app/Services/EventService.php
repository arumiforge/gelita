<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Models\AudioUsageEventModel;
use App\Models\GameEventLogModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Penulis raw event penelitian. Idempotent terhadap `client_event_id` per sesi.
 * `record()` sengaja tidak membuka transaction sendiri agar dapat dipanggil
 * di dalam transaction milik service pemanggil.
 */
class EventService
{
    /**
     * Event yang hanya boleh ditulis server (Dok 1 §12.3). Kiriman klien
     * dengan tipe ini ditolak agar data mentah navigasi dan batas percobaan
     * tidak dapat dipalsukan.
     */
    public const SERVER_ONLY = ['screen_viewed', 'challenge_tries_exhausted', 'clue_failed', 'events_dropped'];

    /**
     * Cookie `{sessionTag}.{n}`: the last client `sequence_no` of this browser
     * (core/storage.js). Read as a floor for server-written events, so the
     * next page's `screen_viewed` follows events still on their way in a beacon.
     */
    public const SEQUENCE_COOKIE = 'gelita_seq';

    /** Largest `dropped` count accepted from one batch envelope. */
    public const MAX_DROPPED_REPORT = 1_000_000;

    /**
     * Menerima batch event dari klien.
     *
     * `$envelope` holds the batch fields next to `events` (core/events.js,
     * rencana-paket-aset §7.3):
     *  - `client_sent_at`: client clock when the batch was sent; every row
     *    gets `clock_offset_ms = server_received_at − client_sent_at`;
     *  - `dropped` + `dropped_id`: events the client queue had to discard,
     *    written once as a server-side `events_dropped` event (idempotent per
     *    `dropped_id`).
     *
     * @param list<array<string, mixed>> $events
     * @param array<string, mixed>       $envelope
     *
     * @return array{accepted: int, duplicate: int, rejected: list<array{index: int, reason: string}>}
     */
    public function ingest(GameSession $session, array $events, array $envelope = []): array
    {
        $config = config('Gelita');
        $model  = model(GameEventLogModel::class);

        $rejected = [];

        if (count($events) > $config->maxEventsPerBatch) {
            $events = array_slice($events, 0, $config->maxEventsPerBatch);
        }

        $rows     = [];
        $seen     = [];
        $sequence = $model->nextSequenceNo($session->id);
        $now      = $this->now();
        $offset   = $this->clockOffsetMs($envelope['client_sent_at'] ?? null, $now);

        $this->recordDropped($session, $envelope, 'events');

        foreach (array_values($events) as $index => $event) {
            if (! is_array($event)) {
                $rejected[] = ['index' => $index, 'reason' => 'format_invalid'];

                continue;
            }

            $type = (string) ($event['event_type'] ?? '');

            if (! in_array($type, $config->eventTypes, true)) {
                $rejected[] = ['index' => $index, 'reason' => 'event_type_unknown'];

                continue;
            }

            if (in_array($type, self::SERVER_ONLY, true)) {
                $rejected[] = ['index' => $index, 'reason' => 'event_type_server_only'];

                continue;
            }

            $clientEventId = trim((string) ($event['client_event_id'] ?? ''));

            if ($clientEventId === '' || mb_strlen($clientEventId) > 120) {
                $rejected[] = ['index' => $index, 'reason' => 'client_event_id_invalid'];

                continue;
            }

            if (isset($seen[$clientEventId])) {
                $rejected[] = ['index' => $index, 'reason' => 'duplicate_in_batch'];

                continue;
            }

            $seen[$clientEventId] = true;

            // Kontrak klien (04/06): node_id, attempt_id, item_id. Nama kolom
            // lengkap (challenge_*_id) tetap diterima.
            $rows[] = $this->buildRow(
                $session,
                $type,
                (array) ($event['payload'] ?? $event['payload_json'] ?? []),
                [
                    'level_id'             => $event['level_id'] ?? null,
                    'challenge_node_id'    => $event['challenge_node_id'] ?? $event['node_id'] ?? null,
                    'challenge_attempt_id' => $event['challenge_attempt_id'] ?? $event['attempt_id'] ?? null,
                    'challenge_item_id'    => $event['challenge_item_id'] ?? $event['item_id'] ?? null,
                ],
                $clientEventId,
                isset($event['sequence_no']) ? (int) $event['sequence_no'] : $sequence++,
                $this->clientTime($event['occurred_at'] ?? null),
                $now,
                $offset,
            );
        }

        $result = $model->appendBatch($rows);

        return [
            'accepted'  => $result['accepted'],
            'duplicate' => $result['duplicate'],
            'rejected'  => $rejected,
        ];
    }

    /**
     * Menulis satu event dari sisi server.
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $refs    level_id, challenge_node_id, challenge_attempt_id, challenge_item_id
     */
    public function record(GameSession $session, string $type, array $payload = [], array $refs = []): void
    {
        if (! in_array($type, config('Gelita')->eventTypes, true)) {
            throw new \InvalidArgumentException("event_type '{$type}' tidak dikenali.");
        }

        $model = model(GameEventLogModel::class);
        $now   = $this->now();

        $model->appendBatch([
            $this->buildRow(
                $session,
                $type,
                $payload,
                $refs,
                'srv-' . uuid4(),
                $this->nextServerSequence($session->id),
                $now,
                $now,
                0,
            ),
        ]);
    }

    /**
     * `sequence_no` for a server-written event, and the lowest number the
     * client may use on a page being rendered (`js_config()` `sequenceFloor`):
     * the session's next number, but never at or below the last client
     * number this browser reported in the SEQUENCE_COOKIE.
     */
    public function nextServerSequence(int $sessionId): int
    {
        return max(model(GameEventLogModel::class)->nextSequenceNo($sessionId), $this->clientSequenceHint($sessionId) + 1);
    }

    /** Last client `sequence_no` from the SEQUENCE_COOKIE, when it belongs to this session. */
    private function clientSequenceHint(int $sessionId): int
    {
        $request = service('request');

        if (! $request instanceof IncomingRequest) {
            return 0;
        }

        $raw = $request->getCookie(self::SEQUENCE_COOKIE);

        if (! is_string($raw) || preg_match('/^([0-9a-f]{16})\.(\d{1,9})$/', $raw, $match) !== 1) {
            return 0;
        }

        return hash_equals((string) session_tag($sessionId), $match[1]) ? (int) $match[2] : 0;
    }

    /**
     * Writes the client's `dropped` report (events its offline queue could
     * not keep) as one server-side `events_dropped` event. `dropped_id` makes
     * a re-sent report a duplicate instead of a second count.
     *
     * @param array<string, mixed> $envelope batch fields: dropped, dropped_id
     */
    public function recordDropped(GameSession $session, array $envelope, string $channel): bool
    {
        $dropped  = filter_var($envelope['dropped'] ?? 0, FILTER_VALIDATE_INT);
        $reportId = is_scalar($envelope['dropped_id'] ?? null) ? trim((string) $envelope['dropped_id']) : '';

        if ($dropped === false || $dropped <= 0 || $reportId === '' || mb_strlen($reportId) > 100) {
            return false;
        }

        $model = model(GameEventLogModel::class);
        $now   = $this->now();

        $result = $model->appendBatch([
            $this->buildRow(
                $session,
                'events_dropped',
                ['dropped' => min($dropped, self::MAX_DROPPED_REPORT), 'channel' => $channel],
                [],
                'drop-' . $reportId,
                $this->nextServerSequence($session->id),
                $now,
                $now,
                0,
            ),
        ]);

        return $result['accepted'] > 0;
    }

    /**
     * Telemetry audio terstruktur: baris audio_usage_events + raw event, dalam
     * satu transaction.
     *
     * Idempotent terhadap `client_event_id` seperti ingest(): kiriman ulang
     * dengan id yang sama tidak menulis baris apa pun dan mengembalikan false.
     *
     * @param array{audio_asset_id: int, action: string, listened_ms?: ?int, completed?: bool,
     *              attempt_id?: ?int, challenge_attempt_id?: ?int, occurred_at?: ?string,
     *              client_event_id?: ?string} $audioEvent
     *
     * @param ?string $clientSentAt `client_sent_at` of the batch (clock offset, §7.3)
     *
     * @return bool true bila ditulis, false bila duplikat
     */
    public function recordAudio(GameSession $session, array $audioEvent, ?string $clientSentAt = null): bool
    {
        $assetId = (int) ($audioEvent['audio_asset_id'] ?? 0);
        $action  = (string) ($audioEvent['action'] ?? '');

        if ($assetId <= 0 || ! in_array($action, AudioUsageEventModel::ACTIONS, true)) {
            throw new \InvalidArgumentException('Event audio tidak lengkap atau aksinya tidak dikenali.');
        }

        $clientEventId = trim((string) ($audioEvent['client_event_id'] ?? ''));

        if (mb_strlen($clientEventId) > 120) {
            throw new \InvalidArgumentException('client_event_id terlalu panjang.');
        }

        $events = model(GameEventLogModel::class);

        if ($clientEventId !== '' && $events->existsClientEvent($session->id, $clientEventId)) {
            return false;
        }

        $usage      = model(AudioUsageEventModel::class);
        $attemptRef = $audioEvent['challenge_attempt_id'] ?? $audioEvent['attempt_id'] ?? null;
        $attemptId  = $attemptRef === null ? null : ((int) $attemptRef ?: null);
        $occurredAt = $this->clientTime($audioEvent['occurred_at'] ?? null);
        $receivedAt = $this->now();

        $eventType = match ($action) {
            'play'     => 'audio_play',
            'autoplay' => 'audio_autoplay',
            'pause'    => 'audio_pause',
            'replay'   => 'audio_replay',
            'complete' => 'audio_completed',
        };

        $db = $this->db();
        $db->transBegin();

        try {
            $usage->insert([
                'session_id'           => $session->id,
                'challenge_attempt_id' => $attemptId,
                'audio_asset_id'       => $assetId,
                'action'               => $action,
                'play_index'           => $usage->nextPlayIndex($session->id, $assetId, $action),
                'listened_ms'          => isset($audioEvent['listened_ms']) ? (int) $audioEvent['listened_ms'] : null,
                'completed'            => ! empty($audioEvent['completed']) ? 1 : 0,
                'occurred_at'          => $occurredAt,
                'server_received_at'   => $receivedAt,
            ], false);

            $events->appendBatch([
                $this->buildRow(
                    $session,
                    $eventType,
                    [
                        'audio_asset_id' => $assetId,
                        'listened_ms'    => $audioEvent['listened_ms'] ?? null,
                        'completed'      => ! empty($audioEvent['completed']),
                    ],
                    ['challenge_attempt_id' => $attemptId],
                    $clientEventId !== '' ? $clientEventId : 'srv-' . uuid4(),
                    $this->nextServerSequence($session->id),
                    $occurredAt,
                    $receivedAt,
                    $this->clockOffsetMs($clientSentAt, $receivedAt),
                ),
            ]);

            if ($attemptId) {
                $db->table('challenge_attempts')
                    ->where('id', $attemptId)
                    ->set('audio_use_count', 'audio_use_count + 1', false)
                    ->update();
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $refs
     *
     * @return array<string, mixed>
     */
    private function buildRow(
        GameSession $session,
        string $type,
        array $payload,
        array $refs,
        string $clientEventId,
        int $sequenceNo,
        string $occurredAt,
        string $receivedAt,
        ?int $clockOffsetMs = null,
    ): array {
        return [
            'event_uuid'           => uuid4(),
            'client_event_id'      => $clientEventId,
            'session_id'           => $session->id,
            'participant_id'       => $session->participant_id,
            'level_id'             => $this->refOrNull($refs['level_id'] ?? null),
            'challenge_node_id'    => $this->refOrNull($refs['challenge_node_id'] ?? null),
            'challenge_attempt_id' => $this->refOrNull($refs['challenge_attempt_id'] ?? null),
            'challenge_item_id'    => $this->refOrNull($refs['challenge_item_id'] ?? null),
            'event_type'           => $type,
            'sequence_no'          => $sequenceNo,
            'occurred_at'          => $occurredAt,
            'server_received_at'   => $receivedAt,
            'clock_offset_ms'      => $clockOffsetMs,
            'payload_json'         => $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_UNICODE),
        ];
    }

    private function refOrNull($value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    /**
     * Waktu klien hanya diterima bila dapat diurai; selain itu memakai waktu server.
     *
     * Pecahan detik dipertahankan (kolom DATETIME(6)) dan zona waktunya
     * dikonversi ke zona aplikasi, mis. `2026-09-21T02:14:22.481Z` →
     * `2026-09-21 09:14:22.481000` (WIB). Urutan event penelitian bergantung
     * pada presisi ini.
     */
    private function clientTime($value): string
    {
        if (is_string($value) && trim($value) !== '') {
            try {
                return (new \DateTimeImmutable(trim($value)))
                    ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                    ->format('Y-m-d H:i:s.u');
            } catch (\Exception) {
                // tidak dapat diurai → waktu server
            }
        }

        return $this->now();
    }

    /**
     * `server_received_at − client_sent_at` in milliseconds (rencana-paket-aset
     * §7.3). Computed when the batch arrives, so events that waited long in
     * the offline queue still get the offset of the clock that stamped them.
     * Null when the client sent no parseable time.
     */
    private function clockOffsetMs(mixed $clientSentAt, string $receivedAt): ?int
    {
        // ISO 8601 only: relative words such as "tomorrow" would parse too
        if (! is_string($clientSentAt) || preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', trim($clientSentAt)) !== 1) {
            return null;
        }

        try {
            $sent     = new \DateTimeImmutable(trim($clientSentAt));
            $received = new \DateTimeImmutable($receivedAt);
        } catch (\Exception) {
            return null;
        }

        $ms = ((float) $received->format('U.u') - (float) $sent->format('U.u')) * 1000;

        // BIGINT holds any real clock error; refuse nonsense (± ~3 centuries)
        return abs($ms) < 1e13 ? (int) round($ms) : null;
    }

    private function now(): string
    {
        return (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s.u');
    }

    private function db(): \CodeIgniter\Database\BaseConnection
    {
        return db_connect();
    }
}
