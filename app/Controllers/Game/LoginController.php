<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Libraries\PasswordPolicy;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use CodeIgniter\HTTP\RedirectResponse;

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
            return redirect()->to(site_url('peta'));
        }

        // tujuan yang disimpan GameSessionFilter harus bertahan sampai POST
        session()->keepFlashdata('redirect_after_login');

        $study = model(ResearchStudyModel::class)->requireActiveStudy();

        return view('game/login', [
            'locale'     => $this->locale,
            'allowPhase' => (int) $study['allow_phase_choice'] === 1,
            'phases'     => config('Gelita')->phases,
            'errors'     => session('errors') ?? [],
        ]);
    }

    public function login(): RedirectResponse
    {
        if (! $this->validate(['username' => 'required', 'password' => 'required'])) {
            return redirect()->to(site_url('masuk'))->with('errors', $this->validator->getErrors());
        }

        $study  = model(ResearchStudyModel::class)->requireActiveStudy();
        $result = service('sessionService')->login(
            model(ParticipantModel::class)->normalizeUsername((string) $this->request->getPost('username')),
            (string) $this->request->getPost('password'),
            (int) $study['allow_phase_choice'] === 1 ? (string) $this->request->getPost('phase') : '',
            (string) (session('locale') ?? $this->locale),
            $this->deviceInfo(),
        );

        return match ($result['status']) {
            'ok'          => $this->afterLogin(),
            'must_change' => redirect()->to(site_url('ganti-sandi'))->with('message', lang('Auth.mustChange')),
            'locked'      => redirect()->to(site_url('masuk'))
                ->with('errors', ['password' => lang('Auth.locked', [$result['lock_minutes'] ?? 5])]),
            default => redirect()->to(site_url('masuk'))
                ->with('errors', ['password' => lang('Auth.loginFailed')]),
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
        $study = model(ResearchStudyModel::class)->requireActiveStudy();

        $login = service('sessionService')->login(
            $participant->username,
            $new,
            (string) $study['active_phase_code'],
            (string) (session('locale') ?? $this->locale),
            $this->deviceInfo(),
        );

        if (! $hadGameSession && ($login['session'] ?? null) !== null) {
            service('eventService')->record($login['session'], 'password_changed', ['via' => 'reset']);
        }

        return redirect()->to(site_url('peta'))->with('message', lang('Auth.passwordChanged'));
    }

    public function logout(): RedirectResponse
    {
        service('sessionService')->logout();

        return redirect()->to(site_url('/'));
    }

    /** Kembali ke halaman yang sempat diminta sebelum login, selain itu peta. */
    private function afterLogin(): RedirectResponse
    {
        $target = session('redirect_after_login');
        session()->remove('redirect_after_login');

        return redirect()->to(safe_internal_url(is_string($target) ? $target : null, 'peta'));
    }
}
