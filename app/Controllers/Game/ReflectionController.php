<?php

namespace App\Controllers\Game;

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
            'errors'   => session('errors') ?? [],
        ]);
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
