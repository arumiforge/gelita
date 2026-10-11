<?php

namespace App\Controllers\Game;

use App\Entities\ChallengeAttempt;
use App\Models\ChallengeAttemptModel;
use App\Models\ParticipantModel;
use CodeIgniter\HTTP\RedirectResponse;

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
            'firstPass'     => array_sum(array_map(static fn (ChallengeAttempt $a): int => (int) $a->first_pass_correct, $completed)),
            'scorable'      => array_sum(array_map(static fn (ChallengeAttempt $a): int => (int) $a->scorable_items, $completed)),
            'canReflect'    => $this->allNodesCompleted($session),
        ]);
    }

    /**
     * POST /profil/avatar — one tap on a tile in "Ganti avatar" saves it
     * (display plan Phase B). '' = back to the initial. Not research data,
     * so no event is logged.
     */
    public function avatar(): RedirectResponse
    {
        $value = trim((string) $this->request->getPost('avatar_no'));

        if ($value !== '' && ! array_key_exists((int) $value, avatar_choices())) {
            return redirect()->to(site_url('profil'))->with('error', lang('Game.avatarInvalid'));
        }

        model(ParticipantModel::class)->update($this->participant()->id, ['avatar_no' => $value === '' ? null : (int) $value]);

        return redirect()->to(site_url('profil'))->with('message', lang('Game.avatarSaved'));
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
