<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Libraries\PasswordPolicy;
use App\Models\ParticipantModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Masuk, keluar, dan ganti kata sandi siswa.
 *
 * Pesan kegagalan login sengaja identik untuk "nama pengguna tidak ada" dan
 * "kata sandi salah", agar akun tidak dapat ditebak keberadaannya.
 */
class LoginController extends BaseController
{
    public function form(): string|RedirectResponse
    {
        if ((int) session('participant_id') > 0 && (int) session('game_session_id') > 0) {
            return redirect()->to(site_url('mulai'));
        }

        // tujuan yang disimpan GameSessionFilter harus bertahan sampai POST
        session()->keepFlashdata('redirect_after_login');

        return view('game/login', [
            'locale' => $this->locale,
            'errors' => session('errors') ?? [],
        ]);
    }

    /**
     * A plain POST answers with a redirect, as without JavaScript. game/login.js
     * sends the form with `X-Requested-With: XMLHttpRequest` and gets JSON
     * instead, so a wrong password shows a modal without reloading the page:
     * `{ ok: true, redirect }` or `{ ok: false, field, message }`.
     */
    public function login(): ResponseInterface
    {
        if (! $this->validate(['username' => 'required', 'password' => 'required'])) {
            $errors = $this->validator->getErrors();

            return $this->failLogin((string) array_key_first($errors), $errors);
        }

        $result = service('sessionService')->login(
            model(ParticipantModel::class)->normalizeUsername((string) $this->request->getPost('username')),
            (string) $this->request->getPost('password'),
            (string) (session('locale') ?? $this->locale),
            $this->deviceInfo(),
        );

        return match ($result['status']) {
            'ok'          => $this->afterLogin(),
            // Field mode: no open field session yet, the token comes first (28 §6.4)
            'token'       => $this->goTo(site_url('token')),
            // No flash: /ganti-sandi already explains it in its alert (Auth.mustChange)
            'must_change' => $this->goTo(site_url('ganti-sandi')),
            // Part 4 (30 §5.3)
            'inactive'    => $this->failLogin('password', ['password' => lang('Game.loginInactive')]),
            'locked'      => $this->failLogin('password', ['password' => lang('Auth.locked', [$result['lock_minutes'] ?? 5])]),
            default       => $this->failLogin('password', ['password' => lang('Auth.loginFailed')]),
        };
    }

    public function changePasswordForm(): string
    {
        $participant = model(ParticipantModel::class)->find((int) session('participant_id'));

        return view('game/change-password', [
            'locale'         => $this->locale,
            'mustChange'     => $participant !== null && $participant->mustChangePassword(),
            // hanya nama pengguna: dipakai pemeriksaan "sandi memuat nama pengguna"
            'username'       => $participant?->username,
            'passwordPolicy' => (new PasswordPolicy())->toClient(),
            'errors'         => session('errors') ?? [],
        ]);
    }

    public function changePassword(): RedirectResponse
    {
        $participants = model(ParticipantModel::class);
        $participant  = $participants->find((int) session('participant_id'));

        if ($participant === null) {
            return redirect()->to(site_url('masuk'));
        }

        $new   = (string) $this->request->getPost('password');
        $check = (new PasswordPolicy())->check($new, $participant->username);

        $data = [
            'username'         => $participant->username,
            'current_password' => (string) $this->request->getPost('current_password'),
            'password'         => $new,
            'password_confirm' => (string) $this->request->getPost('password_confirm'),
        ];

        $rules = [
            'current_password' => 'required',
            'password'         => 'required|strong_password[username]|differs[current_password]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'password' => [
                'strong_password' => $check['contains_username']
                    ? lang('Auth.containsUsername')
                    : lang('Auth.notStrongSubmit'),
                'differs' => lang('Auth.sameAsOld'),
            ],
            'password_confirm' => ['matches' => lang('Auth.notMatch')],
        ];

        if (! $this->validateData($data, $rules, $messages)) {
            return redirect()->to(site_url('ganti-sandi'))->with('errors', $this->validator->getErrors());
        }

        // Setelah reset guru belum ada sesi permainan: event `password_changed`
        // dicatat pada sesi yang dibuka login di bawah (SessionService hanya
        // dapat mencatatnya bila sesi sudah ada).
        $hadGameSession = (int) session('game_session_id') > 0;

        $result = service('sessionService')->changePassword(
            $participant->id,
            $data['current_password'],
            $new,
        );

        if ($result['status'] !== 'ok') {
            return redirect()->to(site_url('ganti-sandi'))->with('errors', [
                'current_password' => $result['status'] === 'invalid_current'
                    ? lang('Auth.wrongCurrent')
                    : lang('Auth.notStrongSubmit'),
            ]);
        }

        // Sandi baru langsung dipakai untuk melanjutkan/membuat sesi permainan:
        // setelah reset guru, session('game_session_id') memang sengaja kosong.
        $login = service('sessionService')->login(
            $participant->username,
            $new,
            (string) (session('locale') ?? $this->locale),
            $this->deviceInfo(),
        );

        if (! $hadGameSession && ($login['session'] ?? null) !== null) {
            service('eventService')->record($login['session'], 'password_changed', ['via' => 'reset']);
        }

        // Straight to `/gerbang`: going through `/mulai` (one more redirect for
        // a logged-in student) used up the flash before any page showed it.
        $target = (int) session('game_session_id') > 0 ? 'gerbang' : (($login['status'] ?? '') === 'token' ? 'token' : 'mulai');

        return redirect()->to(site_url($target))->with('message', lang('Auth.passwordChanged'));
    }

    public function logout(): RedirectResponse
    {
        service('sessionService')->logout();

        return redirect()->to(site_url('/'));
    }

    /**
     * Kembali ke halaman yang sempat diminta sebelum login; selain itu `/mulai`,
     * yang meneruskan ke cerita pembuka (belum pernah menonton) atau pilihan
     * pemain lama.
     */
    private function afterLogin(): ResponseInterface
    {
        $target = session('redirect_after_login');
        session()->remove('redirect_after_login');

        return $this->goTo(safe_internal_url(is_string($target) ? $target : null, 'mulai'));
    }

    /** Redirect, or for game/login.js the same target as JSON. */
    private function goTo(string $url): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return redirect()->to($url);
        }

        return $this->response->setJSON(['ok' => true, 'redirect' => $url]);
    }

    /**
     * Back to the form with the message, or for game/login.js the message as
     * JSON (the page stays; the CSRF token is sent along in case it changed).
     *
     * @param array<string, string> $errors
     */
    private function failLogin(string $field, array $errors): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return redirect()->to(site_url('masuk'))->with('errors', $errors);
        }

        // The page that would have kept it (form()) is not loaded again
        session()->keepFlashdata('redirect_after_login');

        return $this->response->setJSON([
            'ok'       => false,
            'field'    => $field,
            'message'  => (string) ($errors[$field] ?? reset($errors)),
            'csrfName' => csrf_token(),
            'csrf'     => csrf_hash(),
        ]);
    }
}
