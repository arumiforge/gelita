<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
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
        'event_type', 'sequence_no', 'occurred_at', 'server_received_at', 'payload_json',
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
     * Menulis batch event; duplikat (session_id, client_event_id) diabaikan.
     *
     * @param list<array<string, mixed>> $events baris siap tulis
     *
     * @return array{accepted: int, duplicate: int}
     */
    public function appendBatch(array $events): array
    {
        if ($events === []) {
            return ['accepted' => 0, 'duplicate' => 0];
        }

        foreach ($events as $index => $row) {
            if (isset($row['payload_json']) && is_array($row['payload_json'])) {
                $events[$index]['payload_json'] = json_encode($row['payload_json'], JSON_UNESCAPED_UNICODE);
            }
        }

        $this->db->table($this->table)->ignore(true)->insertBatch($events);
        $accepted = $this->db->affectedRows();

        return [
            'accepted'  => $accepted,
            'duplicate' => max(0, count($events) - $accepted),
        ];
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

    /** @return list<array<string, mixed>> */
    public function timeline(int $sessionId, int $limit = 500): array
    {
        return $this->where('session_id', $sessionId)
            ->where('deleted_at', null)
            ->orderBy('sequence_no', 'ASC')
            ->orderBy('occurred_at', 'ASC')
            ->findAll(max(1, $limit));
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
