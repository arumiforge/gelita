<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Libraries\PasswordPolicy;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Persetujuan penelitian dan pendaftaran akun siswa.
 *
 * Membuat kata sandi kuat adalah bagian materi literasi keamanan digital:
 * halaman menunjukkan syarat yang belum terpenuhi, dan dua metrik proses
 * (syarat terpenuhi pada percobaan pertama, jumlah penolakan sandi lemah)
 * ikut disimpan. Isi kata sandi sendiri tidak pernah ditulis ke flash,
 * `old()`, log, maupun event.
 */
class RegisterController extends BaseController
{
    /** Versi teks persetujuan yang sedang ditayangkan; ikut disimpan per peserta. */
    private const CONSENT_VERSION = '1';

    public function consent(): string
    {
        $study = model(ResearchStudyModel::class)->requireActiveStudy();

        return view('game/consent', [
            'locale'         => $this->locale,
            'study'          => $study,
            'requireConsent' => (int) $study['require_consent'] === 1,
            'consentVersion' => self::CONSENT_VERSION,
            'consentText'    => $this->consentText($study),
            'errors'         => session('errors') ?? [],
        ]);
    }

    /**
     * Centang persetujuan disimpan di session permainan, bukan database:
     * baris `participant_consents` baru ditulis bersama peserta di store().
     * (Flashdata tidak dipakai karena data ini harus bertahan dari
     * `/persetujuan` → `GET /daftar` → `POST /daftar`.)
     */
    public function storeConsent(): RedirectResponse
    {
        $study    = model(ResearchStudyModel::class)->requireActiveStudy();
        $required = (int) $study['require_consent'] === 1;

        $rules = [
            'participant_agree' => 'required',
            'guardian_agree'    => $required ? 'required' : 'permit_empty',
            'guardian_name'     => 'permit_empty|max_length[150]',
        ];

        if (! $this->validate($rules, [
            'participant_agree' => ['required' => lang('Game.consentNeedParticipant')],
            'guardian_agree'    => ['required' => lang('Game.consentNeedGuardian')],
        ])) {
            return redirect()->to(site_url('persetujuan'))
                ->with('errors', $this->validator->getErrors());
        }

        session()->set('reg_consent', [
            'participant_consented'     => 1,
            'parent_guardian_consented' => $this->request->getPost('guardian_agree') ? 1 : 0,
            'guardian_name'             => trim((string) $this->request->getPost('guardian_name')),
            'consent_version'           => self::CONSENT_VERSION,
            'consent_text'              => $this->consentText($study),
            'consented_at'              => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('daftar'));
    }

    public function form(): string|RedirectResponse
    {
        $study = model(ResearchStudyModel::class)->requireActiveStudy();

        if ((int) $study['require_consent'] === 1 && ! is_array(session('reg_consent'))) {
            return redirect()->to(site_url('persetujuan'));
        }

        return view('game/register', [
            'locale'         => $this->locale,
            'study'          => $study,
            'allowPhase'     => (int) $study['allow_phase_choice'] === 1,
            'phases'         => config('Gelita')->phases,
            'provinces'      => $this->regionDirectory()['provinces'] ?? [],
            'schools'        => array_column(model(SchoolModel::class)->activeList(), 'name'),
            'passwordPolicy' => (new PasswordPolicy())->toClient(),
            'errors'         => session('errors') ?? [],
        ]);
    }

    /**
     * Alur pendaftaran (04_CONTROLLER_ROUTE.md):
     * metrik sandi dihitung lebih dulu, lalu validasi penuh, lalu
     * SessionService::registerAndStart() yang membuat akun + sesi pertama.
     */
    public function store(): RedirectResponse
    {
        $study    = model(ResearchStudyModel::class)->requireActiveStudy();
        $post     = $this->request->getPost();
        $username = model(ParticipantModel::class)->normalizeUsername((string) ($post['username'] ?? ''));
        $password = (string) ($post['password'] ?? '');

        $input             = is_array($post) ? $post : [];
        $input['username'] = $username;

        // 1–3. metrik literasi keamanan digital, dihitung sebelum validasi lain
        $check = (new PasswordPolicy())->check($password, $username);

        if (session('reg_first_criteria') === null) {
            session()->set('reg_first_criteria', $check['met']);
        }

        if (! $check['acceptable']) {
            session()->set('reg_weak_count', (int) session('reg_weak_count') + 1);
        }

        // 4. validasi penuh
        if (! $this->validateData($input, $this->rules(), $this->messages($check))) {
            return $this->backToForm($input, $this->validator->getErrors());
        }

        if ((int) $study['require_consent'] === 1 && ! is_array(session('reg_consent'))) {
            return redirect()->to(site_url('persetujuan'));
        }

        // 5. buat akun + peserta + sesi pertama
        $consent = (array) (session('reg_consent') ?? []);
        $region  = $this->verifiedRegion($input);

        $result = service('sessionService')->registerAndStart([
            'username'                  => $username,
            'password'                  => $password,
            'display_name'              => $input['display_name'] ?? null,
            'age'                       => $input['age'] ?? null,
            'gender'                    => $input['gender'] ?? null,
            'class_level'               => $input['class_level'] ?? null,
            'school_name'               => $input['school_name'] ?? null,
            'country_code'              => $region['country_code'],
            'country_name'              => $region['country_name'],
            'province_code'             => $region['province_code'],
            'province_name'             => $region['province_name'],
            'district_code'             => $region['district_code'],
            'district_name'             => $region['district_name'],
            'phase_code'                => $input['phase'] ?? null,
            'locale'                    => $input['locale'] ?? $this->locale,
            'participant_consented'     => $consent['participant_consented'] ?? 1,
            'parent_guardian_consented' => $consent['parent_guardian_consented'] ?? 0,
            'guardian_name'             => $consent['guardian_name'] ?? null,
            'consent_version'           => $consent['consent_version'] ?? self::CONSENT_VERSION,
            'consent_text'              => $consent['consent_text'] ?? $this->consentText($study),
            'registration_metrics'      => [
                'first_submit_criteria' => session('reg_first_criteria'),
                'weak_submit_count'     => (int) (session('reg_weak_count') ?? 0),
            ],
            'device' => $this->deviceInfo(),
        ]);

        // 6–7. metrik pendaftaran selesai dipakai; sesi PHP sudah diperbarui service
        session()->remove(['reg_first_criteria', 'reg_weak_count', 'reg_consent']);

        // 8. sambutan; nama pengguna ditampilkan, kata sandi tidak pernah.
        // Kunci flash tersendiri: intro menampilkannya sebagai kartu sambutan,
        // bukan toast yang memudar.
        return redirect()->to(site_url('intro'))->with('welcome', lang('Game.registerWelcome', [
            $result['participant']->username,
        ]));
    }

    /** @return array<string, string> */
    private function rules(): array
    {
        return [
            'display_name'     => 'required|min_length[2]|max_length[150]',
            'age'              => 'required|integer|greater_than[4]|less_than[81]',
            'gender'           => 'required|in_list[laki-laki,perempuan,lainnya]',
            'class_level'      => 'required|max_length[20]',
            'school_name'      => 'required|min_length[3]|max_length[200]',
            'country_code'     => 'required|max_length[5]',
            'province_code'    => 'required_without[country_other]|permit_empty|max_length[10]',
            'district_code'    => 'required_without[country_other]|permit_empty|max_length[10]',
            'country_other'    => 'permit_empty|max_length[100]',
            'phase'            => 'permit_empty|in_list[umum,pretest,posttest]',
            'locale'           => 'permit_empty|valid_locale',
            'username'         => 'required|valid_username|not_reserved_username|is_unique[participants.username]',
            'password'         => 'required|strong_password[username]',
            'password_confirm' => 'required|matches[password]',
        ];
    }

    /**
     * @param array{contains_username: bool} $check
     *
     * @return array<string, array<string, string>>
     */
    private function messages(array $check): array
    {
        return [
            'username' => [
                'is_unique' => lang('Game.usernameTaken'),
            ],
            'password' => [
                'strong_password' => $check['contains_username']
                    ? lang('Auth.containsUsername')
                    : lang('Auth.notStrongSubmit'),
            ],
            'password_confirm' => [
                'matches' => lang('Auth.notMatch'),
            ],
        ];
    }

    /**
     * Kembali ke formulir dengan galat per field. Kata sandi sengaja tidak
     * ikut `old()`: siswa mengetik ulang, dan sandi tidak pernah masuk session.
     *
     * @param array<string, mixed>  $input
     * @param array<string, string> $errors
     */
    private function backToForm(array $input, array $errors): RedirectResponse
    {
        unset($input['password'], $input['password_confirm']);

        return redirect()->to(site_url('daftar'))
            ->with('_ci_old_input', ['get' => [], 'post' => $input])
            ->with('errors', $errors);
    }

    /**
     * Nama wilayah diverifikasi terhadap daftar resmi di server.
     * Kode yang tidak dikenal membuat nama snapshot dikosongkan.
     *
     * @param array<string, mixed> $input
     *
     * @return array<string, ?string>
     */
    private function verifiedRegion(array $input): array
    {
        $directory   = $this->regionDirectory();
        $countryCode = trim((string) ($input['country_code'] ?? 'ID')) ?: 'ID';
        $other       = trim((string) ($input['country_other'] ?? ''));

        if ($countryCode !== ($directory['country']['code'] ?? 'ID')) {
            return [
                'country_code'  => $countryCode,
                'country_name'  => $other !== '' ? $other : null,
                'province_code' => null,
                'province_name' => null,
                'district_code' => null,
                'district_name' => null,
            ];
        }

        $provinceCode = trim((string) ($input['province_code'] ?? ''));
        $districtCode = trim((string) ($input['district_code'] ?? ''));
        $province     = null;
        $district     = null;

        foreach ($directory['provinces'] ?? [] as $row) {
            if (($row['code'] ?? null) === $provinceCode) {
                $province = $row;
                break;
            }
        }

        foreach ($province['districts'] ?? [] as $row) {
            if (($row['code'] ?? null) === $districtCode) {
                $district = $row;
                break;
            }
        }

        return [
            'country_code'  => $countryCode,
            'country_name'  => $directory['country']['name'] ?? null,
            'province_code' => $province === null ? null : $provinceCode,
            'province_name' => $province['name'] ?? null,
            'district_code' => $district === null ? null : $districtCode,
            'district_name' => $district['name'] ?? null,
        ];
    }

    /**
     * Daftar wilayah resmi (`public/assets/data/wilayah-id.json`), dibaca sekali
     * per request. Nama dari client tidak pernah dipercaya begitu saja.
     *
     * @return array<string, mixed>
     */
    private function regionDirectory(): array
    {
        static $directory = null;

        if ($directory !== null) {
            return $directory;
        }

        $path = FCPATH . 'assets/data/wilayah-id.json';
        $raw  = is_file($path) ? file_get_contents($path) : false;
        $data = $raw === false ? null : json_decode($raw, true);

        return $directory = is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $study */
    private function consentText(array $study): string
    {
        return lang('Game.consentBody', [$study['name'] ?? 'GELITA']);
    }
}
