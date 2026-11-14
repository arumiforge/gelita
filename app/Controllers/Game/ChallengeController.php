<?php

namespace App\Controllers\Game;

use App\Exceptions\ChallengeRuleException;
use App\Libraries\VariantPicker;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
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

        // Part 6 (32 §7.2): the bridge story before the first Skenario Digital attempt
        if ($gate = $this->scenarioGate($session, $level, $node)) {
            return $gate;
        }

        // Research mode without a booklet: the scored challenge stays closed (31 K10)
        $served = model(ChallengeAttemptModel::class)->variantsServed($session->participant_id, $node->id);

        return view('game/mission-brief', $this->hudData() + [
            'level'          => $level,
            'node'           => $node,
            'sequence'       => $sequence,
            'best'           => $this->bestAttempt($session, $node->id),
            'bookletMissing' => (new VariantPicker(\CodeIgniter\Config\Factories::libraries('BookletSource')))->bookletMissing($session, $node, $served),
            // Part 7 (33 §7.4): a completed challenge that may not be played again yet
            'retakeLocked'   => $this->retakeLocked($session, $node),
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

        // Part 6 (32 §7.2): the bridge story before the first Skenario Digital attempt
        if ($gate = $this->scenarioGate($session, $level, $node)) {
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
        } catch (ChallengeRuleException $e) {
            // Part 7 (33 §7.4): a retake that is not open yet goes back to the region map with Game.retakeLater
            if ($e->errorCode === 'RETAKE_LOCKED') {
                return redirect()->to(site_url('wilayah/' . $level->code))->with('error', lang('Game.retakeLater'));
            }

            // BOOKLET_MISSING (31 K10): back to the mission brief, which explains it
            return redirect()->to(site_url('misi/' . $level->code . '/' . $sequence))->with('error', lang('Game.bookletMissing'));
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
            // Part 7 (33 §7.4)
            'retakeLocked' => $this->retakeLocked($session, $node),
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
            // Skenario Digital has tries per scenario and no check (check_count 0): the tries used on all scenarios
            'scenarioTries'   => $node?->engine_type === 'skenario'
                ? array_sum(array_map(static fn ($r): int => (int) $r->try_count, model(ItemResponseModel::class)->forAttempt($attempt->id)))
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

        // Every game session of the student in this study (progress follows the student, 28 §8)
        $session  = model(GameSessionModel::class)->find($sessionId);
        $sessions = $session === null ? [$sessionId] : array_map('intval', array_column(
            db_connect()->table('game_sessions')->select('id')
                ->where('participant_id', $session->participant_id)->where('study_id', $session->study_id)
                ->get()->getResultArray(),
            'id',
        ));

        $attempts = model(ChallengeAttemptModel::class)
            ->whereIn('session_id', $sessions)
            ->where('status', 'completed')
            ->whereIn('challenge_node_id', $nodeIds)
            ->orderBy('completed_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return self::closingAttemptId($attempts, $nodeIds) === $attemptId;
    }
}
