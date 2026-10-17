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

    /** Raw event type per audio action. */
    private const AUDIO_EVENT_TYPES = [
        'play'     => 'audio_play',
        'autoplay' => 'audio_autoplay',
        'pause'    => 'audio_pause',
        'replay'   => 'audio_replay',
        'complete' => 'audio_completed',
    ];

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
     * One structured audio event (audio_usage_events row + raw event).
     * Kept for single writers such as the simulator; the API endpoints use
     * recordAudioBatch().
     *
     * @param array<string, mixed> $audioEvent see recordAudioBatch()
     * @param ?string              $clientSentAt `client_sent_at` of the batch (clock offset, §7.3)
     *
     * @return bool true when written, false when it is a duplicate
     *
     * @throws \InvalidArgumentException when the event is incomplete or not of this session
     */
    public function recordAudio(GameSession $session, array $audioEvent, ?string $clientSentAt = null): bool
    {
        $result = $this->recordAudioBatch($session, [$audioEvent], $clientSentAt);

        if ($result['rejected'] !== []) {
            throw new \InvalidArgumentException('Audio event rejected: ' . $result['rejected'][0]['reason']);
        }

        return $result['accepted'] > 0;
    }

    /**
     * Writes a batch of client audio telemetry (rencana-log-asinkron §5.2 R6):
     * one audio_usage_events row and one raw event per accepted entry, and
     * `challenge_attempts.audio_use_count`, all in one transaction.
     *
     * Each entry: `audio_asset_id`, `action` (AudioUsageEventModel::ACTIONS),
     * optional `listened_ms`, `completed`, `attempt_id` (or
     * `challenge_attempt_id`), `occurred_at`, `client_event_id`.
     * Per-entry rejections, in this order:
     *  - `format_invalid`: not an object;
     *  - `attempt_not_in_session`: the attempt is not one of this session;
     *  - `audio_event_invalid`: unknown asset or action, or a
     *    `client_event_id` over 120 characters.
     * An entry whose `client_event_id` is already stored for the session, or
     * repeated earlier in the same batch, counts as `duplicate` and writes
     * nothing. Entries without a `client_event_id` are never duplicates.
     *
     * Statements: attempt and asset lookups, one existence check, one
     * `MAX(sequence_no)`, then in the transaction one INSERT IGNORE of the
     * raw events, one grouped `MAX(play_index)`, one insertBatch into
     * audio_usage_events, and one counter update per attempt.
     *
     * Two tabs may send the same stored entry at once. The raw event's unique
     * `(session_id, client_event_id)` decides the winner, like updateIf() in
     * ChallengeService: only rows whose `event_uuid` this request actually
     * inserted get an audio_usage_events row and count on the attempt.
     *
     * @param list<mixed> $entries
     * @param ?string     $clientSentAt `client_sent_at` of the batch (clock offset, §7.3)
     *
     * @return array{accepted: int, duplicate: int, rejected: list<array{index: int, reason: string}>}
     */
    public function recordAudioBatch(GameSession $session, array $entries, ?string $clientSentAt = null): array
    {
        $entries    = array_values($entries);
        $receivedAt = $this->now();
        $offset     = $this->clockOffsetMs($clientSentAt, $receivedAt);
        $rejected   = [];
        $duplicate  = 0;

        $attemptIds = [];
        $assetIds   = [];

        foreach ($entries as $entry) {
            if (is_array($entry)) {
                $attemptIds[] = $this->refOrNull($entry['challenge_attempt_id'] ?? $entry['attempt_id'] ?? null);
                $assetIds[]   = $this->refOrNull($entry['audio_asset_id'] ?? null);
            }
        }

        $ownAttempts = $this->existingIds('challenge_attempts', array_filter($attemptIds), $session->id);
        $knownAssets = $this->existingIds('audio_assets', array_filter($assetIds));

        // 1. Validate in batch order
        $valid = [];

        foreach ($entries as $index => $entry) {
            if (! is_array($entry)) {
                $rejected[] = ['index' => $index, 'reason' => 'format_invalid'];

                continue;
            }

            $attemptId = $this->refOrNull($entry['challenge_attempt_id'] ?? $entry['attempt_id'] ?? null);

            if ($attemptId !== null && ! isset($ownAttempts[$attemptId])) {
                $rejected[] = ['index' => $index, 'reason' => 'attempt_not_in_session'];

                continue;
            }

            $assetId       = (int) ($entry['audio_asset_id'] ?? 0);
            $action        = (string) ($entry['action'] ?? '');
            $clientEventId = trim((string) ($entry['client_event_id'] ?? ''));

            if (! isset($knownAssets[$assetId]) || ! in_array($action, AudioUsageEventModel::ACTIONS, true) || mb_strlen($clientEventId) > 120) {
                $rejected[] = ['index' => $index, 'reason' => 'audio_event_invalid'];

                continue;
            }

            $valid[] = [
                'entry'           => $entry,
                'attempt_id'      => $attemptId,
                'asset_id'        => $assetId,
                'action'          => $action,
                'client_event_id' => $clientEventId,
            ];
        }

        // 2. Duplicates: already stored, or repeated within this batch
        $stored = $this->storedClientEventIds($session->id, array_filter(array_column($valid, 'client_event_id')));
        $seen   = [];
        $fresh  = [];

        foreach ($valid as $item) {
            $id = $item['client_event_id'];

            if ($id !== '' && (isset($stored[$id]) || isset($seen[$id]))) {
                $duplicate++;

                continue;
            }

            if ($id !== '') {
                $seen[$id] = true;
            }

            $fresh[] = $item;
        }

        if ($fresh === []) {
            return ['accepted' => 0, 'duplicate' => $duplicate, 'rejected' => $rejected];
        }

        // 3. Raw events: server sequence numbers in batch order
        $sequence = $this->nextServerSequence($session->id);
        $rows     = [];

        foreach ($fresh as $i => $item) {
            $entry      = $item['entry'];
            $occurredAt = $this->clientTime($entry['occurred_at'] ?? null);

            $fresh[$i]['occurred_at'] = $occurredAt;
            $fresh[$i]['row']         = $this->buildRow(
                $session,
                self::AUDIO_EVENT_TYPES[$item['action']],
                [
                    'audio_asset_id' => $item['asset_id'],
                    'listened_ms'    => $entry['listened_ms'] ?? null,
                    'completed'      => ! empty($entry['completed']),
                ],
                ['challenge_attempt_id' => $item['attempt_id']],
                $item['client_event_id'] !== '' ? $item['client_event_id'] : 'srv-' . uuid4(),
                $sequence++,
                $occurredAt,
                $receivedAt,
                $offset,
            );
            $rows[] = $fresh[$i]['row'];
        }

        $db = $this->db();
        $db->transBegin();

        try {
            $written = model(GameEventLogModel::class)->appendBatch($rows);

            // Lost a race with another tab for some entries: keep only ours
            if ($written['accepted'] < count($rows)) {
                $ours = array_flip(array_column(
                    $db->table('game_event_logs')->select('event_uuid')
                        ->where('session_id', $session->id)
                        ->whereIn('event_uuid', array_column($rows, 'event_uuid'))
                        ->get()->getResultArray(),
                    'event_uuid',
                ));
                $won        = array_values(array_filter($fresh, static fn (array $item): bool => isset($ours[$item['row']['event_uuid']])));
                $duplicate += count($fresh) - count($won);
                $fresh      = $won;
            }

            $this->insertAudioUsage($session->id, $fresh, $receivedAt);

            $perAttempt = array_count_values(array_filter(array_column($fresh, 'attempt_id')));

            foreach ($perAttempt as $attemptId => $count) {
                $db->table('challenge_attempts')
                    ->where('id', $attemptId)
                    ->where('session_id', $session->id)
                    ->set('audio_use_count', 'audio_use_count + ' . (int) $count, false)
                    ->update();
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return ['accepted' => count($fresh), 'duplicate' => $duplicate, 'rejected' => $rejected];
    }

    /**
     * audio_usage_events rows for the accepted entries, with `play_index`
     * assigned in batch order from one grouped MAX per asset. Same rule as
     * AudioUsageEventModel::nextPlayIndex(): `play`, `autoplay`, and
     * `replay` open the next playback; `pause` and `complete` belong to the
     * current one (at least 1).
     *
     * @param list<array<string, mixed>> $items
     */
    private function insertAudioUsage(int $sessionId, array $items, string $receivedAt): void
    {
        if ($items === []) {
            return;
        }

        $db      = $this->db();
        $current = [];

        $maxRows = $db->table('audio_usage_events')
            ->select('audio_asset_id')
            ->selectMax('play_index', 'max_index')
            ->where('session_id', $sessionId)
            ->whereIn('audio_asset_id', array_values(array_unique(array_column($items, 'asset_id'))))
            ->groupBy('audio_asset_id')
            ->get()
            ->getResultArray();

        foreach ($maxRows as $row) {
            $current[(int) $row['audio_asset_id']] = (int) $row['max_index'];
        }

        $rows = [];

        foreach ($items as $item) {
            $assetId = $item['asset_id'];
            $last    = $current[$assetId] ?? 0;
            $index   = in_array($item['action'], AudioUsageEventModel::OPENING_ACTIONS, true) ? $last + 1 : max(1, $last);

            $current[$assetId] = max($last, $index);

            $entry  = $item['entry'];
            $rows[] = [
                'session_id'           => $sessionId,
                'challenge_attempt_id' => $item['attempt_id'],
                'audio_asset_id'       => $assetId,
                'action'               => $item['action'],
                'play_index'           => $index,
                'listened_ms'          => isset($entry['listened_ms']) ? max(0, (int) $entry['listened_ms']) : null,
                'completed'            => ! empty($entry['completed']) ? 1 : 0,
                'occurred_at'          => $item['occurred_at'],
                'server_received_at'   => $receivedAt,
            ];
        }

        $db->table('audio_usage_events')->insertBatch($rows);
    }

    /**
     * The given ids that exist in `$table` (and belong to `$sessionId` when given).
     *
     * @param array<int> $ids
     *
     * @return array<int, true>
     */
    private function existingIds(string $table, array $ids, ?int $sessionId = null): array
    {
        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return [];
        }

        $builder = $this->db()->table($table)->select('id')->whereIn('id', $ids);

        if ($sessionId !== null) {
            $builder->where('session_id', $sessionId);
        }

        return array_fill_keys(array_map('intval', array_column($builder->get()->getResultArray(), 'id')), true);
    }

    /**
     * The given client event ids already stored for the session.
     *
     * @param array<string> $clientEventIds
     *
     * @return array<string, true>
     */
    private function storedClientEventIds(int $sessionId, array $clientEventIds): array
    {
        $clientEventIds = array_values(array_unique($clientEventIds));

        if ($clientEventIds === []) {
            return [];
        }

        $rows = $this->db()->table('game_event_logs')
            ->select('client_event_id')
            ->where('session_id', $sessionId)
            ->whereIn('client_event_id', $clientEventIds)
            ->get()
            ->getResultArray();

        return array_fill_keys(array_map('strval', array_column($rows, 'client_event_id')), true);
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
