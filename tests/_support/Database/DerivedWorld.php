<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use Config\Services;

/**
 * Part 9 (35 §16): the grade-5 world with field mode on, one research
 * school S03, the world's student as S03-A01 (booklet B1, seat 1, consent
 * given), and field session S03-A-P2 (Temanggung) with the world's game
 * session in it. Helpers write raw attempts, tries, and events directly.
 */
trait DerivedWorld
{
    use GradeFiveWorld;

    private function createDerivedWorld(): BaseConnection
    {
        $db = $this->createGradeFiveWorld();

        $db->table('schools')->insert(['research_code' => 'S03', 'name' => 'SD Uji', 'is_verified' => 1]);
        $school = (int) $db->insertID();
        $db->table('research_studies')->update(['field_mode' => 1, 'meeting_plan' => 'lima_pertemuan', 'idle_threshold_ms' => 180000, 'rapid_guess_ms' => 3000, 'test_rapid_guess_ms' => 3000]);
        $db->table('participants')->where('id', 1)->update([
            'participant_code' => 'S03-A01', 'school_id' => $school, 'rombel' => 'A', 'assented_at' => '2026-11-02 08:00:00', 'updated_at' => '2026-11-02 08:00:00',
        ]);
        $db->table('participant_consents')->insert(['participant_id' => 1, 'study_id' => 1, 'parent_consent_on' => '2026-11-01', 'status' => 'active']);
        $db->table('participant_seats')->insert(['participant_id' => 1, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 1, 'booklet' => 'B1', 'pre_form' => 'A', 'post_form' => 'B']);
        $db->table('field_sessions')->insert([
            'study_id' => 1, 'code' => 'S03-A-P2', 'school_id' => $school, 'rombel' => 'A', 'meeting_no' => 2, 'session_date' => '2026-11-10',
            'opens_at' => '2026-11-10 07:30:00', 'closes_at' => '2026-11-10 10:00:00',
        ]);
        $db->table('game_sessions')->where('id', 1)->update(['field_session_id' => (int) $db->insertID(), 'meeting_no' => 2, 'started_at' => '2026-11-10 08:00:00']);
        Services::resetSingle('derivedData');
        Services::resetSingle('fieldSessions');

        return $db;
    }

    /** One attempt with one segment; returns its id. */
    private function addAttempt(BaseConnection $db, string $ref, string $start, ?string $end, string $variant = 'a', int $session = 1, string $status = 'completed'): int
    {
        $db->table('challenge_attempts')->insert([
            'session_id' => $session, 'challenge_node_id' => $this->nodeId($db, $ref), 'attempt_no' => 1 + $db->table('challenge_attempts')->where('session_id', $session)->where('challenge_node_id', $this->nodeId($db, $ref))->countAllResults(),
            'variant_group' => $variant, 'status' => $end === null ? 'in_progress' : $status, 'selected_item_ids_json' => '[]',
            'started_at' => $start, 'completed_at' => $end, 'variant_source' => 'booklet',
        ]);
        $id = (int) $db->insertID();
        $db->table('challenge_attempt_segments')->insert(['challenge_attempt_id' => $id, 'session_id' => $session, 'field_session_id' => 1, 'segment_no' => 1, 'opened_at' => $start, 'left_at' => $end]);

        return $id;
    }

    /** @return list<int> scored item ids of a challenge's variant (and shared x) */
    private function scoredItems(BaseConnection $db, string $ref, string $variant = 'a'): array
    {
        return array_map('intval', array_column($db->table('challenge_items')->select('id')->where('challenge_node_id', $this->nodeId($db, $ref))
            ->whereIn('variant_group', [$variant, 'x'])->where('scorable', 1)->where('is_active', 1)->orderBy('sequence', 'ASC')->get()->getResultArray(), 'id'));
    }

    private function addTry(BaseConnection $db, int $attempt, int $item, int $tryNo, bool $correct, string $at, array $extra = []): int
    {
        $db->table('item_tries')->insert($extra + [
            'challenge_attempt_id' => $attempt, 'challenge_item_id' => $item, 'try_no' => $tryNo, 'answer_json' => json_encode(['value' => $correct ? 'B' : 'S']),
            'is_correct' => $correct ? 1 : 0, 'occurred_at' => $at,
        ]);

        return (int) $db->insertID();
    }

    private function addEvent(BaseConnection $db, string $type, string $at, array $payload = [], ?int $attempt = null, ?int $item = null): void
    {
        static $n = 0;
        $n++;
        $db->table('game_event_logs')->insert([
            'event_uuid' => sprintf('00000000-0000-4000-8000-%012d', $n + random_int(0, 99999999)), 'client_event_id' => 'c' . $n . '-' . bin2hex(random_bytes(3)), 'session_id' => 1, 'participant_id' => 1,
            'challenge_attempt_id' => $attempt, 'challenge_item_id' => $item, 'event_type' => $type, 'occurred_at' => $at, 'clock_offset_ms' => 0,
            'payload_json' => json_encode($payload),
        ]);
    }
}
