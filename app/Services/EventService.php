<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Models\AudioUsageEventModel;
use App\Models\GameEventLogModel;

/**
 * Penulis raw event penelitian. Idempotent terhadap `client_event_id` per sesi.
 * `record()` sengaja tidak membuka transaction sendiri agar dapat dipanggil
 * di dalam transaction milik service pemanggil.
 */
class EventService
{
    /**
     * Menerima batch event dari klien.
     *
     * @param list<array<string, mixed>> $events
     *
     * @return array{accepted: int, duplicate: int, rejected: list<array{index: int, reason: string}>}
     */
    public function ingest(GameSession $session, array $events): array
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
                $model->nextSequenceNo($session->id),
                $now,
                $now,
            ),
        ]);
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
     * @return bool true bila ditulis, false bila duplikat
     */
    public function recordAudio(GameSession $session, array $audioEvent): bool
    {
        $assetId = (int) ($audioEvent['audio_asset_id'] ?? 0);
        $action  = (string) ($audioEvent['action'] ?? '');

        if ($assetId <= 0 || ! in_array($action, ['play', 'pause', 'replay', 'complete'], true)) {
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

        $eventType = match ($action) {
            'play'     => 'audio_play',
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
                'play_index'           => $usage->nextPlayIndex($session->id, $assetId),
                'listened_ms'          => isset($audioEvent['listened_ms']) ? (int) $audioEvent['listened_ms'] : null,
                'completed'            => ! empty($audioEvent['completed']) ? 1 : 0,
                'occurred_at'          => $occurredAt,
                'server_received_at'   => $this->now(),
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
                    $events->nextSequenceNo($session->id),
                    $occurredAt,
                    $this->now(),
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

    private function now(): string
    {
        return (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s.u');
    }

    private function db(): \CodeIgniter\Database\BaseConnection
    {
        return db_connect();
    }
}
