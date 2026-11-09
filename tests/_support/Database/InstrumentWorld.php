<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\TestResponse;

/**
 * ConsentWorld plus the test module (research revision 10, part 8: 34): the
 * instrument content comes with the reset of GradeFiveWorld (the shipped data
 * files), student 1 (`S03-A01`) gets a seat with forms, and helpers bind the
 * student's game session to a field session and answer items by their key.
 *
 * Classes using it also use FeatureTestTrait.
 */
trait InstrumentWorld
{
    use ConsentWorld;

    private function createInstrumentWorld(): BaseConnection
    {
        $db = $this->createConsentWorld();
        $db->table('participant_seats')->insert(['participant_id' => 1, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 1, 'booklet' => 'B1', 'pre_form' => 'A', 'post_form' => 'B']);
        $this->consent($db, 1);
        \Config\Services::resetSingle('instruments');
        \Config\Services::resetSingle('instrumentGate');
        \Config\Services::resetSingle('instrumentRepository');

        return $db;
    }

    private function dropInstrumentWorld(): void
    {
        config('Gelita')->instrumentPreview = false;
        $this->dropConsentWorld();
    }

    /**
     * A field session of meeting $meetingNo (opened 30 minutes ago), with
     * student $participantId's game session $sessionId bound to it.
     *
     * @param array<string, mixed> $overrides
     */
    private function meeting(BaseConnection $db, int $meetingNo, bool $testStarted = true, array $overrides = [], int $participantId = 1, int $sessionId = 1): int
    {
        $token = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ'), 0, 6);
        $id    = $this->fieldSession($db, $meetingNo, $token, $overrides + ['test_started_at' => $testStarted ? date('Y-m-d H:i:s', time() - 60) : null])['id'];
        $db->table('game_sessions')->where('id', $sessionId)->update(['field_session_id' => $id, 'meeting_no' => $meetingNo, 'status' => 'active']);
        $this->signIn($participantId, $sessionId);
        $this->forgetInstruments();

        return $id;
    }

    private function forgetInstruments(): void
    {
        foreach (['instruments', 'instrumentGate', 'fieldSessions'] as $name) {
            \Config\Services::resetSingle($name);
        }
    }

    /** Requests through FieldWorld::send() with the module services fresh. */
    private function go(string $method, string $path, array $params = []): TestResponse
    {
        $this->forgetInstruments();
        $response = $this->send($method, $path, $params);
        $this->forgetInstruments();

        return $response;
    }

    /**
     * POST fields that answer $item exactly by its key (`a[...]`).
     *
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    private function keyAnswer(array $item): array
    {
        $unit   = $item['units'][0];
        $answer = (string) $item['key']['answer'];

        return match ($item['type']) {
            'pgk'   => ['a' => [$unit => ['1' => $answer[0], '2' => $answer[1], '3' => $answer[2]]]],
            'match' => ['a' => [$unit => \App\Services\InstrumentScorer::pairs($answer)]],
            default => ['a' => [$unit => $answer]],
        };
    }

    /** @return list<array<string, mixed>> the active test items of a part and form */
    private function testItems(string $part, ?string $form = 'A'): array
    {
        $repo = service('instrumentRepository');

        return $repo->items((int) $repo->activeVersion('tes')['id'], $part, $form);
    }

    /** Starts and sends one test part of the open sitting, every item answered by the key. */
    private function passPart(string $part, string $form = 'A'): void
    {
        $this->go(Method::POST, 'tes/bagian/' . $part . '/mulai');

        foreach ($this->testItems($part, $form) as $item) {
            $this->go(Method::GET, 'tes/bagian/' . $part . '/' . $item['position']);
            $this->go(Method::POST, 'tes/bagian/' . $part . '/' . $item['position'], $this->keyAnswer($item) + ['tujuan' => 'berikutnya']);
        }

        $this->go(Method::POST, 'tes/bagian/' . $part . '/kirim');
    }

    /** @return array<string, mixed>|null */
    private function sittingRow(BaseConnection $db, string $occasion, string $kind, int $participantId = 1): ?array
    {
        return $db->table('instrument_sittings')->where(['participant_id' => $participantId, 'occasion' => $occasion, 'kind' => $kind])->get()->getRowArray();
    }
}
