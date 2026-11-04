<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * The data cleanup of migration 2026-01-01-003600_CleanResearchLogs
 * (research revision 10, part 3: 29 §5.8, K3), and its dry run for
 * `php spark gelita:log:hitung`. One class, so the command counts exactly
 * what the migration changes.
 *
 * Steps, each in chunks of CHUNK ids so no table stays locked for long:
 * 1. one `dialogue_completed` per (game session, context, level) whose
 *    `dialogue_advanced` reached the last slide, from the first such row;
 * 2. `answer_changed` becomes `answer_submitted` with `changed: true`
 *    (the payload is changed in PHP, so MariaDB and SQLite behave the same);
 * 3. `answer_selected`, `dialogue_advanced`, and `audio_pause` rows are deleted.
 * The device columns and the audit row are the migration's own steps.
 */
final class ResearchLogCleanup
{
    public const CHUNK = 2000;

    /** Event rows deleted for good. */
    public const DELETED_TYPES = ['answer_selected', 'dialogue_advanced', 'audio_pause'];

    /** device_logs columns dropped (29 §5.5). */
    public const DROPPED_DEVICE_COLUMNS = [
        'user_agent', 'client_hints_json', 'device_brand', 'device_model', 'pixel_ratio',
        'touch_points', 'timezone', 'language', 'os_platform',
    ];

    public function __construct(private readonly BaseConnection $db)
    {
    }

    /**
     * What the migration will change, without changing anything.
     *
     * @return array{events: array<string, int>, dialogue_completed: int, device_logs_with_details: int}
     */
    public function count(): array
    {
        $events = [];

        foreach ([...self::DELETED_TYPES, 'answer_changed'] as $type) {
            $events[$type] = $this->db->table('game_event_logs')->where('event_type', $type)->countAllResults();
        }

        $devices = 0;
        $present = array_values(array_intersect(self::DROPPED_DEVICE_COLUMNS, $this->db->getFieldNames('device_logs')));

        if ($present !== []) {
            $builder = $this->db->table('device_logs')->groupStart();

            foreach ($present as $column) {
                $builder->orWhere($column . ' IS NOT NULL', null, false);
            }

            $devices = $builder->groupEnd()->countAllResults();
        }

        return [
            'events'                   => $events,
            'dialogue_completed'       => count($this->completedDialogues()),
            'device_logs_with_details' => $devices,
        ];
    }

    /**
     * Steps 1–3.
     *
     * @return array{dialogue_completed: int, answer_changed: int, deleted: array<string, int>}
     */
    public function run(): array
    {
        $created = 0;

        foreach ($this->completedDialogues() as $row) {
            $clientId = sprintf('mig-dc-%d-%s-%d', $row['session_id'], $row['context'], $row['level_id'] ?? 0);

            $exists = $this->db->table('game_event_logs')->where('session_id', $row['session_id'])->where('client_event_id', $clientId)->countAllResults() > 0;

            if ($exists) {
                continue;
            }

            $this->db->table('game_event_logs')->insert([
                'event_uuid'           => self::uuid(),
                'client_event_id'      => $clientId,
                'session_id'           => $row['session_id'],
                'participant_id'       => $row['participant_id'],
                'level_id'             => $row['level_id'],
                'event_type'           => 'dialogue_completed',
                'sequence_no'          => $row['sequence_no'],
                'occurred_at'          => $row['occurred_at'],
                'server_received_at'   => $row['server_received_at'],
                'clock_offset_ms'      => $row['clock_offset_ms'],
                'payload_json'         => json_encode(['context' => $row['context'], 'total' => $row['total'], 'via' => 'migration'], JSON_UNESCAPED_UNICODE),
            ]);
            $created++;
        }

        $changed = 0;
        $lastId  = 0;

        do {
            $rows = $this->db->table('game_event_logs')->select('id, payload_json')
                ->where('event_type', 'answer_changed')->where('id >', $lastId)
                ->orderBy('id')->limit(self::CHUNK)->get()->getResultArray();

            foreach ($rows as $row) {
                $payload            = json_decode((string) ($row['payload_json'] ?? ''), true);
                $payload            = is_array($payload) ? $payload : [];
                $payload['changed'] = true;

                $this->db->table('game_event_logs')->where('id', $row['id'])->update([
                    'event_type'   => 'answer_submitted',
                    'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ]);
                $changed++;
                $lastId = (int) $row['id'];
            }
        } while (count($rows) === self::CHUNK);

        $deleted = [];

        foreach (self::DELETED_TYPES as $type) {
            $deleted[$type] = 0;

            do {
                $ids = array_column($this->db->table('game_event_logs')->select('id')->where('event_type', $type)
                    ->orderBy('id')->limit(self::CHUNK)->get()->getResultArray(), 'id');

                if ($ids !== []) {
                    $this->db->table('game_event_logs')->whereIn('id', $ids)->delete();
                    $deleted[$type] += count($ids);
                }
            } while (count($ids) === self::CHUNK);
        }

        return ['dialogue_completed' => $created, 'answer_changed' => $changed, 'deleted' => $deleted];
    }

    /**
     * The first last-slide `dialogue_advanced` per (session, context, level).
     *
     * @return list<array<string, mixed>>
     */
    private function completedDialogues(): array
    {
        $groups = [];
        $lastId = 0;

        do {
            $rows = $this->db->table('game_event_logs')
                ->select('id, session_id, participant_id, level_id, sequence_no, occurred_at, server_received_at, clock_offset_ms, payload_json')
                ->where('event_type', 'dialogue_advanced')->where('id >', $lastId)
                ->orderBy('id')->limit(self::CHUNK)->get()->getResultArray();

            foreach ($rows as $row) {
                $lastId  = (int) $row['id'];
                $payload = json_decode((string) ($row['payload_json'] ?? ''), true);
                $total   = is_array($payload) ? (int) ($payload['total'] ?? 0) : 0;
                $context = is_array($payload) && is_string($payload['context'] ?? null) ? $payload['context'] : '';

                if ($total <= 0 || (int) ($payload['index'] ?? 0) < $total || $context === '') {
                    continue;
                }

                $key  = $row['session_id'] . '|' . $context . '|' . ($row['level_id'] ?? '');
                $item = [
                    'session_id'         => (int) $row['session_id'],
                    'participant_id'     => (int) $row['participant_id'],
                    'level_id'           => $row['level_id'] === null ? null : (int) $row['level_id'],
                    'sequence_no'        => (int) $row['sequence_no'],
                    'occurred_at'        => (string) $row['occurred_at'],
                    'server_received_at' => $row['server_received_at'],
                    'clock_offset_ms'    => $row['clock_offset_ms'],
                    'context'            => $context,
                    'total'              => $total,
                ];

                // The first last-slide row by time (ids follow arrival, not occurrence)
                if (! isset($groups[$key]) || [$item['occurred_at'], $item['sequence_no']] < [$groups[$key]['occurred_at'], $groups[$key]['sequence_no']]) {
                    $groups[$key] = $item;
                }
            }
        } while (count($rows) === self::CHUNK);

        return array_values($groups);
    }

    private static function uuid(): string
    {
        $bytes    = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
