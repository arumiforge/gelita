<?php

namespace App\Controllers\Game;

use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use App\Models\ParticipantFeedbackModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Balai Refleksi: kritik & saran setelah seluruh tantangan tuntas.
 */
class ReflectionController extends BaseGameController
{
    /** Empat pertanyaan terbuka; minimal dua di antaranya wajib diisi. */
    private const OPEN_FIELDS = ['liked_most', 'hardest_part', 'new_learning', 'suggestion'];

    public function index(): string|RedirectResponse
    {
        $session = $this->session();

        if (! $this->allNodesCompleted($session)) {
            return redirect()->to(site_url('peta'))->with('error', lang('Game.reflectionLocked'));
        }

        $existing = model(ParticipantFeedbackModel::class)
            ->where('session_id', $session->id)
            ->orderBy('submitted_at', 'DESC')
            ->first();

        return view('game/reflection', $this->hudData() + [
            'session'  => $session,
            'existing' => $existing,
            'journey'  => $this->journey($session),
            'errors'   => session('errors') ?? [],
        ]);
    }

    /**
     * Ringkasan perjalanan untuk Balai Refleksi, dari attempt terbaik tiap node
     * yang sudah dinilai ScoringService — rata-rata tampilan, bukan angka
     * penelitian baru.
     *
     * @return array<string, mixed>
     */
    private function journey(GameSession $session): array
    {
        $best    = model(ChallengeAttemptModel::class)->completedForSession($session->id);
        $content = service('contentRepository');
        $locale  = $session->resolvedLocale();

        $regions = [];
        $engines = [];
        $missed  = null;
        $sum     = 0.0;
        $correct = 0;

        foreach ($content->levels() as $level) {
            $values = [];

            foreach ($content->nodesForLevel($level->id) as $node) {
                $attempt = $best[$node->id] ?? null;

                if ($attempt === null) {
                    continue;
                }

                $accuracy  = (float) $attempt->first_pass_accuracy;
                $values[]  = $accuracy;
                $sum      += $accuracy;
                $correct  += (int) $attempt->final_correct;

                $engines[(string) $node->engine_type][] = $accuracy;

                if ($missed === null || $accuracy < $missed['accuracy']) {
                    $missed = [
                        'title'    => $level->text('name', $locale) . ' · ' . $node->text('title', $locale),
                        'accuracy' => $accuracy,
                    ];
                }
            }

            $regions[] = [
                'name'     => $level->text('name', $locale),
                'accuracy' => $values === [] ? 0.0 : round(array_sum($values) / count($values), 1),
            ];
        }

        $engineRows = [];

        foreach ($engines as $engine => $values) {
            $engineRows[] = ['engine' => $engine, 'accuracy' => round(array_sum($values) / count($values), 1)];
        }

        return [
            'first_pass'    => $best === [] ? 0.0 : round($sum / count($best), 1),
            'completed'     => count($best),
            'duration_ms'   => (int) $session->duration_ms,
            'correct_items' => $correct,
            'regions'       => $regions,
            'engines'       => $engineRows,
            'most_missed'   => $missed,
        ];
    }

    public function store(): RedirectResponse
    {
        $session = $this->session();

        if (! $this->allNodesCompleted($session)) {
            return redirect()->to(site_url('peta'))->with('error', lang('Game.reflectionLocked'));
        }

        $rules = [
            'rating'       => 'required|integer|greater_than[0]|less_than[6]',
            'liked_most'   => 'permit_empty|max_length[2000]',
            'hardest_part' => 'permit_empty|max_length[2000]',
            'new_learning' => 'permit_empty|max_length[2000]',
            'suggestion'   => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('refleksi'))
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $answers = [];
        $filled  = 0;

        foreach (self::OPEN_FIELDS as $field) {
            $value           = trim((string) $this->request->getPost($field));
            $answers[$field] = $value === '' ? null : $value;

            if ($value !== '') {
                $filled++;
            }
        }

        if ($filled < 2) {
            return redirect()->to(site_url('refleksi'))
                ->withInput()
                ->with('errors', ['liked_most' => lang('Game.reflectionNeedTwo')]);
        }

        $feedback = model(ParticipantFeedbackModel::class);

        $saved = $feedback->insert([
            'participant_id' => $session->participant_id,
            'session_id'     => $session->id,
            'rating'         => (int) $this->request->getPost('rating'),
            'submitted_at'   => date('Y-m-d H:i:s'),
        ] + $answers, false);

        if ($saved === false) {
            return redirect()->to(site_url('refleksi'))
                ->withInput()
                ->with('errors', $feedback->errors());
        }

        service('eventService')->record($session, 'feedback_submitted', [
            'rating'       => (int) $this->request->getPost('rating'),
            'open_answers' => $filled,
        ]);

        return redirect()->to(site_url('refleksi'))->with('message', lang('Game.reflectionThanks'));
    }
}
