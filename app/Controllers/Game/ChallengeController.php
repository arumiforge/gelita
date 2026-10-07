<?php

namespace App\Controllers\Game;

use App\Models\ChallengeAttemptModel;
use App\Models\ItemResponseModel;
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

        // Pengerjaan lama ternyata sudah tuntas dan baru saja ditutup
        if (isset($opened['redirect'])) {
            return redirect()->to($opened['redirect']);
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

        $levelSummary = $level === null
            ? ['stars' => 0, 'completed_nodes' => 0, 'total_nodes' => 0]
            : service('scoringService')->levelSummary($session->id, $level->id);

        // Wilayah sesudahnya (baris levelOverview, beserta `entry` dan isi tirai
        // wilayah): bila baru terbuka, tombol lanjut langsung menuju dialog pembukanya
        $nextRegion = $level === null ? null : $this->nextRegion($session, $level->sequence);

        $levelCompleted = $levelSummary['total_nodes'] > 0
            && $levelSummary['completed_nodes'] >= $levelSummary['total_nodes'];

        // Attempt yang menuntaskan wilayah (bukan saat mengulang tantangan di
        // wilayah yang sudah tuntas): tombol utama "Lanjut" menuju adegan
        // /tuntas/{code}, dan momen "Pustaka {wilayah} terbuka!" bila wilayah
        // itu memang punya halaman Pustaka.
        $closesRegion    = $levelCompleted && $this->closesLevel($session->id, $level->id, $attempt->id);
        $libraryUnlocked = $closesRegion && service('contentRepository')->libraryPages($level->id) !== [];

        return view('game/challenge-finished', $this->hudData() + [
            'attempt'        => $attempt,
            'node'           => $node,
            'level'          => $level,
            'levelSummary'     => $levelSummary,
            'levelCompleted'  => $levelCompleted,
            'closesRegion'    => $closesRegion,
            'libraryUnlocked' => $libraryUnlocked,
            'allCompleted'    => $this->allNodesCompleted($session),
            'nextRegion'      => $nextRegion,
            // Cari Objek: jumlah ketukan salah (Dok 2 §12.1 "Ketukan salah"); engine batch memakai check_count
            'wrongTaps'       => $node?->engine_type === 'cari'
                ? array_sum(array_map(static fn ($r): int => (int) $r->wrong_click_count, model(ItemResponseModel::class)->forAttempt($attempt->id)))
                : null,
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
