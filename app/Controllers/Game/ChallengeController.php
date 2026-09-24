<?php

namespace App\Controllers\Game;

use App\Models\ChallengeAttemptModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Kartu misi, layar tantangan, riwayat hasil, dan layar bintang.
 *
 * `play()` tidak menilai apa pun: ia hanya membuka attempt lewat
 * ChallengeService dan menanamkan payload soal untuk mesin permainan.
 * Seluruh interaksi jawaban berjalan lewat `/api/attempts/*`.
 */
class ChallengeController extends BaseGameController
{
    public function brief(string $code, int $sequence): string|RedirectResponse
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);
        $node    = $this->requireNode($level->id, $sequence);

        if (! $this->levelUnlocked($session, $level->sequence) || ! $this->nodeUnlocked($session, $node)) {
            return redirect()->to(site_url('wilayah/' . $level->code))->with('error', lang('Game.nodeLocked'));
        }

        if ($gate = $this->dialogueGate($session, $level)) {
            return $gate;
        }

        return view('game/mission-brief', $this->hudData() + [
            'level'    => $level,
            'node'     => $node,
            'sequence' => $sequence,
            'best'     => $this->bestAttempt($session, $node->id),
        ]);
    }

    public function play(string $code, int $sequence): string|RedirectResponse
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);
        $node    = $this->requireNode($level->id, $sequence);

        if (! $this->levelUnlocked($session, $level->sequence) || ! $this->nodeUnlocked($session, $node)) {
            return redirect()->to(site_url('wilayah/' . $level->code))->with('error', lang('Game.nodeLocked'));
        }

        if ($gate = $this->dialogueGate($session, $level)) {
            return $gate;
        }

        // Engine diperiksa SEBELUM openNode(): membuka attempt lalu gagal
        // merender akan meninggalkan attempt `in_progress` yang tidak pernah
        // dimainkan dan memindahkan penunjuk progres sesi.
        $engine = (string) $node->engine_type;

        if (! in_array($engine, config('Gelita')->engineTypes, true)) {
            throw PageNotFoundException::forPageNotFound("Engine '{$engine}' tidak dikenali.");
        }

        try {
            $opened = service('challengeService')->openNode($session, $node->id);
        } catch (\RuntimeException $e) {
            log_message('error', 'Gagal membuka node {id}: {msg}', ['id' => $node->id, 'msg' => $e->getMessage()]);

            return redirect()->to(site_url('wilayah/' . $level->code))->with('error', lang('Game.challengeUnavailable'));
        }

        return view('game/challenge/' . $engine, $this->hudData() + [
            'level'    => $level,
            'node'     => $node,
            'sequence' => $sequence,
            'attempt'  => $opened['attempt'],
            'payload'  => $opened['payload'],
        ]);
    }

    public function result(string $code, int $sequence): string
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);
        $node    = $this->requireNode($level->id, $sequence);

        $attempts = model(ChallengeAttemptModel::class)
            ->where('session_id', $session->id)
            ->where('challenge_node_id', $node->id)
            ->where('status', 'completed')
            ->orderBy('attempt_no', 'ASC')
            ->findAll();

        return view('game/challenge-result', $this->hudData() + [
            'level'    => $level,
            'node'     => $node,
            'sequence' => $sequence,
            'attempts' => $attempts,
            'best'     => $this->bestAttempt($session, $node->id),
        ]);
    }

    public function finished(int $attemptId): string
    {
        $session = $this->session();
        $attempt = model(ChallengeAttemptModel::class)->find($attemptId);

        if ($attempt === null || $attempt->session_id !== $session->id || ! $attempt->isCompleted()) {
            throw PageNotFoundException::forPageNotFound("Hasil {$attemptId} tidak ditemukan pada sesi ini.");
        }

        $node  = service('contentRepository')->node($attempt->challenge_node_id);
        $level = $node === null ? null : service('contentRepository')->levelById($node->level_id);

        $levelScore = $level === null
            ? ['score' => 0.0, 'stars' => 0, 'completed_nodes' => 0, 'total_nodes' => 0]
            : service('scoringService')->levelScore($session->id, $level->id);

        // Wilayah sesudahnya (baris levelOverview, beserta `entry`): bila baru
        // terbuka, tombol lanjut langsung menuju dialog pembukanya
        $nextRegion = null;

        if ($level !== null) {
            foreach ($this->levelOverview($session) as $row) {
                if ($row['sequence'] === $level->sequence + 1) {
                    $nextRegion = $row;
                    break;
                }
            }
        }

        $levelCompleted = $levelScore['total_nodes'] > 0
            && $levelScore['completed_nodes'] >= $levelScore['total_nodes'];

        // Momen "Pustaka {wilayah} terbuka!": hanya pada attempt yang menuntaskan
        // wilayah (bukan saat mengulang tantangan di wilayah yang sudah tuntas),
        // dan hanya bila wilayah itu memang punya halaman Pustaka.
        $libraryUnlocked = $levelCompleted
            && service('contentRepository')->libraryPages($level->id) !== []
            && $this->closesLevel($session->id, $level->id, $attempt->id);

        return view('game/challenge-finished', $this->hudData() + [
            'attempt'        => $attempt,
            'node'           => $node,
            'level'          => $level,
            'levelScore'     => $levelScore,
            'levelCompleted'  => $levelCompleted,
            'libraryUnlocked' => $libraryUnlocked,
            'allCompleted'    => $this->allNodesCompleted($session),
            'nextRegion'      => $nextRegion,
        ]);
    }

    /** Attempt ini yang membuat seluruh tantangan wilayah selesai? (closingAttemptId()) */
    private function closesLevel(int $sessionId, int $levelId, int $attemptId): bool
    {
        $nodeIds = array_map(
            static fn ($node): int => (int) $node->id,
            service('contentRepository')->nodesForLevel($levelId),
        );

        if ($nodeIds === []) {
            return false;
        }

        $attempts = model(ChallengeAttemptModel::class)
            ->where('session_id', $sessionId)
            ->where('status', 'completed')
            ->whereIn('challenge_node_id', $nodeIds)
            ->orderBy('completed_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return self::closingAttemptId($attempts, $nodeIds) === $attemptId;
    }
}
