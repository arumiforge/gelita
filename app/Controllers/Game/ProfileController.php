<?php

namespace App\Controllers\Game;

use App\Entities\ChallengeAttempt;
use App\Models\ChallengeAttemptModel;

/**
 * Profil peserta: capaian pada sesi yang sedang berjalan.
 */
class ProfileController extends BaseGameController
{
    public function index(): string
    {
        $session = $this->session();

        $attempts = model(ChallengeAttemptModel::class)
            ->where('session_id', $session->id)
            ->orderBy('started_at', 'DESC')
            ->findAll();

        $completed = array_values(array_filter(
            $attempts,
            static fn (ChallengeAttempt $attempt): bool => $attempt->isCompleted(),
        ));

        return view('game/profile', $this->hudData() + [
            'session'       => $session,
            'levels'        => $this->levelOverview($session),
            'attempts'      => $attempts,
            'nodes'         => $this->nodeTitles(),
            'durationMs'    => (int) $session->duration_ms,
            'meanFirstPass' => $this->mean($completed, 'first_pass_accuracy'),
            'meanFinal'     => $this->mean($completed, 'final_accuracy'),
            'canReflect'    => $this->allNodesCompleted($session),
        ]);
    }

    /**
     * Rata-rata sederhana atas metrik yang sudah dihitung ScoringService saat
     * attempt ditutup — untuk ditampilkan, bukan angka penelitian baru.
     *
     * @param list<ChallengeAttempt> $attempts
     */
    private function mean(array $attempts, string $field): float
    {
        if ($attempts === []) {
            return 0.0;
        }

        $sum = 0.0;

        foreach ($attempts as $attempt) {
            $sum += (float) $attempt->{$field};
        }

        return round($sum / count($attempts), 2);
    }

    /** @return array<int, string> id node → judul, untuk label daftar attempt */
    private function nodeTitles(): array
    {
        $locale  = $this->session()->resolvedLocale();
        $content = service('contentRepository');
        $titles  = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $titles[$node->id] = $level->text('name', $locale) . ' · ' . $node->text('title', $locale);
            }
        }

        return $titles;
    }
}
