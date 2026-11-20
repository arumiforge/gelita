<?php

namespace App\Models;

use App\Libraries\DeadlockRetry;
use App\Models\Traits\EncodesJson;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class GameEventLogModel extends Model
{
    use EncodesJson;

    protected $table = 'game_event_logs';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    /** Waktu dikelola manual: occurred_at dari klien, server_received_at dari server. */
    protected $useTimestamps = false;

    /** deleted_at diisi manual oleh layanan penghapusan data, bukan oleh Model. */
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'event_uuid', 'client_event_id', 'session_id', 'participant_id',
        'level_id', 'challenge_node_id', 'challenge_attempt_id', 'challenge_item_id',
        'event_type', 'sequence_no', 'occurred_at', 'server_received_at', 'clock_offset_ms', 'payload_json',
        'deleted_at', 'deleted_by', 'delete_reason',
    ];
    protected $validationRules = [
        'event_uuid'      => 'required|max_length[36]',
        'client_event_id' => 'required|max_length[120]',
        'session_id'      => 'required|is_natural_no_zero',
        'participant_id'  => 'required|is_natural_no_zero',
        'event_type'      => 'required|valid_event_type',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['payload_json'];

    /**
     * Writes a batch of events; a `(session_id, client_event_id)` that is
     * already stored is skipped and counted as a duplicate.
     *
     * Stored rows are looked up first and left out of the INSERT. The client
     * re-sends on purpose (the pagehide copy rides the next page's first
     * batch), and on MySQL 8 a multi-row INSERT IGNORE that meets a duplicate
     * keeps a gap lock at the end of the table until it finishes: two such
     * batches of any two students deadlock (1213; production load test,
     * 21 November 2026). INSERT IGNORE stays for the copy that is still on
     * its way when its twin arrives; a deadlock there is run again by
     * DeadlockRetry, with a fresh lookup.
     *
     * @param list<array<string, mixed>> $events    rows ready to write
     * @param bool                       $mayRepeat false when no row can be stored yet (ids made
     *                                              by the server, or already checked): no lookup
     *
     * @return array{accepted: int, duplicate: int}
     */
    public function appendBatch(array $events, bool $mayRepeat = true): array
    {
        if ($events === []) {
            return ['accepted' => 0, 'duplicate' => 0];
        }

        foreach ($events as $index => $row) {
            if (isset($row['payload_json']) && is_array($row['payload_json'])) {
                $events[$index]['payload_json'] = json_encode($row['payload_json'], JSON_UNESCAPED_UNICODE);
            }
        }

        $accepted = DeadlockRetry::run($this->db, function () use ($events, $mayRepeat): int {
            $rows = $mayRepeat ? $this->withoutStored($events) : $events;

            return $rows === [] ? 0 : $this->insertIgnore($rows);
        }, 'game_event_logs');

        return [
            'accepted'  => $accepted,
            'duplicate' => max(0, count($events) - $accepted),
        ];
    }

    /**
     * One INSERT IGNORE for the rows; the number of rows it wrote.
     *
     * Inside a caller's transaction the framework answers a failed statement
     * with `false` instead of an exception. It is thrown here: reported as
     * "nothing written, all duplicates" the events would be dropped by the
     * client, and after a deadlock the caller would go on writing although
     * its transaction is gone. The caller's `catch` rolls back.
     *
     * @param list<array<string, mixed>> $rows
     *
     * @throws DatabaseException when the statement failed
     */
    protected function insertIgnore(array $rows): int
    {
        // insertBatch() runs the statement itself and drops its result, so a failure cannot
        // be told from "nothing new". The builder only writes the SQL here (test mode).
        $statements = $this->db->table($this->table)->testMode()->ignore(true)->insertBatch($rows, null, max(100, count($rows)));
        $written    = 0;

        foreach ((array) $statements as $sql) {
            if ($this->db->query($sql, null, false) === false) {
                $error = $this->db->error();
                $code  = (int) ($error['code'] ?? 0);

                throw new DatabaseException($code === 0 ? 'INSERT into ' . $this->table . ' failed.' : (string) $error['message'], $code);
            }

            $written += max(0, $this->db->affectedRows());
        }

        return $written;
    }

    /**
     * The rows whose `(session_id, client_event_id)` is not stored yet.
     *
     * @param list<array<string, mixed>> $events
     *
     * @return list<array<string, mixed>>
     */
    private function withoutStored(array $events): array
    {
        $idsBySession = [];

        foreach ($events as $row) {
            $idsBySession[(int) $row['session_id']][] = (string) $row['client_event_id'];
        }

        $stored = [];

        foreach ($idsBySession as $sessionId => $ids) {
            foreach (array_keys($this->storedClientEventIds($sessionId, $ids)) as $id) {
                // The unique key compares without case (utf8mb4_unicode_ci)
                $stored[$sessionId . '|' . mb_strtolower((string) $id)] = true;
            }
        }

        if ($stored === []) {
            return $events;
        }

        return array_values(array_filter(
            $events,
            static fn (array $row): bool => ! isset($stored[(int) $row['session_id'] . '|' . mb_strtolower((string) $row['client_event_id'])]),
        ));
    }

    /**
     * The given client event ids already stored for the session.
     *
     * @param array<string> $clientEventIds
     *
     * @return array<string, true>
     */
    public function storedClientEventIds(int $sessionId, array $clientEventIds): array
    {
        $clientEventIds = array_values(array_unique($clientEventIds));

        if ($clientEventIds === []) {
            return [];
        }

        $rows = $this->db->table($this->table)
            ->select('client_event_id')
            ->where('session_id', $sessionId)
            ->whereIn('client_event_id', $clientEventIds)
            ->get()
            ->getResultArray();

        return array_fill_keys(array_map('strval', array_column($rows, 'client_event_id')), true);
    }

    public function nextSequenceNo(int $sessionId): int
    {
        $row = $this->db->table($this->table)
            ->selectMax('sequence_no', 'max_no')
            ->where('session_id', $sessionId)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_no'] ?? 0)) + 1;
    }

    /**
     * Linimasa satu sesi, diurutkan `occurred_at` lalu `sequence_no`
     * (01_DATABASE.md, aturan 8). Before asset package Stage 5 the client
     * `sequence_no` restarted on every page load, so it cannot be the primary
     * sort key for older sessions.
     * Didukung index (session_id, occurred_at) tabel game_event_logs.
     *
     * @return list<array<string, mixed>>
     */
    public function timeline(int $sessionId, int $limit = 500): array
    {
        return $this->where('session_id', $sessionId)
            ->where('deleted_at', null)
            ->orderBy('occurred_at', 'ASC')
            ->orderBy('sequence_no', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll(max(1, $limit));
    }

    /**
     * Regions whose "Kenali wilayah" narration this participant heard to the
     * last slide, in any session: event `dialogue_completed` with payload
     * `context = region_intro` (29 K5; it is only sent at the last slide).
     * Uses the index (participant_id, event_type, occurred_at); one
     * participant has only a few dialogue events, so it is cheap on every map.
     *
     * The LIKE is only a cheap prefilter on the bare context word: MySQL 8
     * returns a JSON column re-serialised with spaces (`"context": "…"`); the
     * decoded context is checked exactly afterwards.
     *
     * @return list<int> level_id
     */
    public function heardRegionIntros(int $participantId): array
    {
        $rows = $this->db->table($this->table)
            ->select('level_id, payload_json')
            ->where('participant_id', $participantId)
            ->where('event_type', 'dialogue_completed')
            ->where('level_id IS NOT NULL', null, false)
            ->where('deleted_at', null)
            ->like('payload_json', 'region_intro')
            ->get()
            ->getResultArray();

        $levels = [];

        foreach ($rows as $row) {
            $payload = json_decode((string) ($row['payload_json'] ?? ''), true);

            if (is_array($payload) && ($payload['context'] ?? null) === 'region_intro' && (int) $row['level_id'] > 0) {
                $levels[(int) $row['level_id']] = (int) $row['level_id'];
            }
        }

        sort($levels);

        return array_values($levels);
    }

    public function existsClientEvent(int $sessionId, string $clientEventId): bool
    {
        return $this->db->table($this->table)
            ->where('session_id', $sessionId)
            ->where('client_event_id', $clientEventId)
            ->countAllResults() > 0;
    }

    /**
     * Soft delete event pada cakupan tertentu; raw event tidak pernah dihapus keras
     * agar jejak penelitian tetap dapat diaudit.
     *
     * @param array{session_id?: int, participant_id?: int, study_id?: int} $scope
     *
     * @return int jumlah baris yang ditandai
     */
    public function softDeleteScope(array $scope, int $staffId, string $reason): int
    {
        $builder = $this->db->table($this->table)->where('deleted_at', null);

        if (! empty($scope['session_id'])) {
            $builder->where('session_id', (int) $scope['session_id']);
        }
        if (! empty($scope['participant_id'])) {
            $builder->where('participant_id', (int) $scope['participant_id']);
        }
        if (! empty($scope['study_id'])) {
            $builder->whereIn(
                'session_id',
                static fn ($sub) => $sub->select('id')->from('game_sessions')->where('study_id', (int) $scope['study_id']),
            );
        }

        if (empty($scope['session_id']) && empty($scope['participant_id']) && empty($scope['study_id'])) {
            throw new \InvalidArgumentException('Cakupan penghapusan event tidak boleh kosong.');
        }

        $builder->update([
            'deleted_at'    => date('Y-m-d H:i:s'),
            'deleted_by'    => $staffId,
            'delete_reason' => mb_substr($reason, 0, 255),
        ]);

        return $this->db->affectedRows();
    }
}
