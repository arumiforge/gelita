<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Stretches of one attempt on the challenge screen (research revision 10,
 * part 7: 33 §5.4, K3). The source of "every segment counts on its own" in
 * 11 → Analitik 5; part 9 sums them into `total_ms` / `active_ms`.
 *
 * Only the server writes segments: openNode() opens one, abandonAttempt(),
 * closeAttempt(), a moved attempt, and a paused game session close it. Every
 * close is a conditional UPDATE `WHERE id = ? AND left_at IS NULL`, so two
 * requests at once never overwrite each other. A segment left open (device
 * switched off) is closed by part 9 from the attempt's last event.
 */
class ChallengeAttemptSegmentModel extends Model
{
    /** `left_via` values written by the server. */
    public const VIA = ['closed', 'user_exit', 'pagehide', 'content_changed', 'retention', 'session_ended', 'session_paused', 'migration'];

    protected $table         = 'challenge_attempt_segments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'challenge_attempt_id', 'session_id', 'field_session_id', 'segment_no', 'opened_at', 'left_at', 'left_via',
    ];

    /** @return array<string, mixed>|null the attempt's newest segment */
    public function latestFor(int $attemptId): ?array
    {
        return $this->where('challenge_attempt_id', $attemptId)->orderBy('segment_no', 'DESC')->first();
    }

    /** @return list<array<string, mixed>> the attempt's segments, in order */
    public function forAttempt(int $attemptId): array
    {
        return $this->where('challenge_attempt_id', $attemptId)->orderBy('segment_no', 'ASC')->findAll();
    }

    /**
     * Opens the next segment unless the newest one is still open (a reload).
     * Two requests at once both reading a closed newest segment insert the
     * same `segment_no`; the unique key keeps one (INSERT IGNORE).
     *
     * @return bool true when this call wrote a segment
     */
    public function openNext(int $attemptId, int $sessionId, ?int $fieldSessionId, string $openedAt): bool
    {
        $latest = $this->latestFor($attemptId);

        if ($latest !== null && $latest['left_at'] === null) {
            return false;
        }

        $this->db->table($this->table)->ignore(true)->insert([
            'challenge_attempt_id' => $attemptId,
            'session_id'           => $sessionId,
            'field_session_id'     => $fieldSessionId,
            'segment_no'           => $latest === null ? 1 : (int) $latest['segment_no'] + 1,
            'opened_at'            => $openedAt,
        ]);

        return $this->db->affectedRows() === 1;
    }

    /**
     * Closes the attempt's open segment (if any); false when another request
     * closed it first or none was open.
     */
    public function closeOpen(int $attemptId, string $leftAt, string $via): bool
    {
        $latest = $this->latestFor($attemptId);

        if ($latest === null || $latest['left_at'] !== null) {
            return false;
        }

        $this->db->table($this->table)
            ->where('id', (int) $latest['id'])
            ->where('left_at', null)
            ->update(['left_at' => $leftAt, 'left_via' => $via]);

        return $this->db->affectedRows() === 1;
    }

    /** Closes every open segment written in a game session (session paused, 33 §5.4); rows closed. */
    public function closeForSession(int $sessionId, string $leftAt, string $via = 'session_paused'): int
    {
        // Callers outside the challenge core (logout, idle cleanup) also run on partial test schemas
        if (! $this->db->tableExists($this->table)) {
            return 0;
        }

        $this->db->table($this->table)
            ->where('session_id', $sessionId)
            ->where('left_at', null)
            ->update(['left_at' => $leftAt, 'left_via' => $via]);

        return $this->db->affectedRows();
    }
}
