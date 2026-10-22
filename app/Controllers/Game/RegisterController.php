<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Libraries\PasswordPolicy;
use App\Libraries\RegionDirectory;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Services\SchoolDirectory;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Pendaftaran siswa tiga langkah, seluruhnya di server (perombakan kelas 5,
 * Dok 1 §10) — tetap berjalan tanpa JavaScript:
 *
 *   /daftar/1 Tentang kamu · /daftar/2 Sekolah dan daerah · /daftar/3 Akun rahasiamu
 *
 * Isian langkah 1–2 disimpan di session PHP `reg_wizard`; tidak ada data
 * peserta yang ditulis ke database sebelum langkah 3 berhasil. Kata sandi
 * tidak pernah disimpan di session, flash, `old()`, log, maupun event.
 * Langkah 3 memvalidasi ulang isian langkah 1–2 sebelum membuat akun.
 *
 * Membuat kata sandi kuat adalah bagian materi literasi keamanan digital:
 * halaman menunjukkan syarat yang belum terpenuhi, dan dua metrik proses
 * (syarat terpenuhi pada kiriman pertama langkah 3, jumlah penolakan sandi
 * lemah) ikut disimpan.
 *
 * Sekolah: siswa di provinsi berdirektori (Jawa Tengah) wajib mengisi NPSN
 * sehingga semua siswa satu sekolah tertaut ke satu baris `schools` resmi;
 * bila NPSN-nya tidak ada di daftar, siswa mencentang "Sekolahku tidak ada di
 * daftar" lalu menulis nama sekolah. Provinsi lain menulis nama sekolah
 * sendiri. Pencocokan nama: App\Services\SchoolDirectory.
 */
class RegisterController extends BaseController
{
    /** Kunci session PHP isian langkah 1–2 */
    public const WIZARD = 'reg_wizard';

    public const STEPS = [1, 2, 3];

    /** Isian yang disimpan per langkah (langkah 3 tidak pernah disimpan) */
    private const STEP_FIELDS = [
        1 => ['display_name', 'age', 'gender', 'avatar_no'],
        2 => ['country_code', 'country_other', 'province_code', 'district_code', 'school_npsn', 'school_manual', 'school_name'],
    ];

    /** GET /daftar → langkah pertama yang belum lengkap */
    public function index(): RedirectResponse
    {
        return redirect()->to(site_url('daftar/' . $this->firstIncomplete()));
    }

    /** GET /daftar/{1|2|3} */
    public function step(int $n): string|RedirectResponse
    {
        if (! in_array($n, self::STEPS, true)) {
            return $this->index();
        }

        $first = $this->firstIncomplete();

        if ($n > $first) {
            return redirect()->to(site_url('daftar/' . $first));
        }

        $wizard = $this->wizard();
        $saved  = $wizard[(string) $n] ?? [];
        $data   = [
            'locale'   => $this->locale,
            'step'     => $n,
            'done'     => array_map('intval', array_keys($wizard)),
            'values'   => $saved,
            'errors'   => session('errors') ?? [],
        ];

        if ($n === 2) {
            // Kembali dari galat atau dari langkah 3: kartu sekolah dari NPSN,
            // atau centang manual bila NPSN itu tidak ada di daftar.
            $directory = service('schoolDirectory');
            $npsn      = trim((string) (old('school_npsn') ?? $saved['school_npsn'] ?? ''));
            $school    = $npsn === '' ? null : $directory->findByNpsn($npsn);

            $data += [
                'provinces'       => RegionDirectory::provinces(),
                'schoolProvinces' => $directory->directoryProvinces(),
                'selectedSchool'  => $school === null ? null : $directory->describe($school),
                'npsnNotFound'    => $school === null && preg_match(SchoolDirectory::NPSN_PATTERN, $npsn) === 1,
            ];
        }

        if ($n === 3) {
            $data['passwordPolicy'] = (new PasswordPolicy())->toClient();
        }

        return view('game/register/step-' . $n, $data);
    }

    /** POST /daftar/{1|2|3} */
    public function saveStep(int $n): RedirectResponse
    {
        if (! in_array($n, self::STEPS, true)) {
            return $this->index();
        }

        $first = $this->firstIncomplete();

        if ($n > $first) {
            return redirect()->to(site_url('daftar/' . $first));
        }

        $post  = $this->request->getPost();
        $input = is_array($post) ? $post : [];

        if ($n === 3) {
            return $this->register($input);
        }

        $input = $this->normalize($n, $input);

        if (! $this->validateData($input, $this->rulesFor($n, $input), $this->messages())) {
            return $this->backToStep($n, $input, $this->validator->getErrors());
        }

        $wizard               = $this->wizard();
        $wizard[(string) $n]  = array_intersect_key($input, array_flip(self::STEP_FIELDS[$n]));
        session()->set(self::WIZARD, $wizard);

        return redirect()->to(site_url('daftar/' . ($n + 1)));
    }

    /**
     * Langkah 3: metrik sandi lebih dulu, validasi ulang langkah 1–2 dari
     * session, validasi akun, lalu SessionService::registerAndStart().
     *
     * @param array<string, mixed> $input
     */
    private function register(array $input): RedirectResponse
    {
        model(ResearchStudyModel::class)->requireActiveStudy();

        $username = model(ParticipantModel::class)->normalizeUsername((string) ($input['username'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        $input['username'] = $username;

        // Metrik literasi keamanan digital, dihitung sebelum validasi lain
        $check = (new PasswordPolicy())->check($password, $username);

        if (session('reg_first_criteria') === null) {
            session()->set('reg_first_criteria', $check['met']);
        }

        if (! $check['acceptable']) {
            session()->set('reg_weak_count', (int) session('reg_weak_count') + 1);
        }

        // Pertahanan bila session diubah: langkah 1–2 divalidasi ulang
        $wizard = $this->wizard();

        foreach ([1, 2] as $step) {
            $saved = $this->normalize($step, (array) ($wizard[(string) $step] ?? []));

            if (! $this->validateData($saved, $this->rulesFor($step, $saved), $this->messages())) {
                $errors = $this->validator->getErrors();
                unset($wizard[(string) $step]);
                session()->set(self::WIZARD, $wizard);

                return $this->backToStep($step, $saved, $errors);
            }

            $wizard[(string) $step] = $saved;
        }

        if (! $this->validateData($input, $this->rulesFor(3, $input), $this->messages($check))) {
            return $this->backToStep(3, $input, $this->validator->getErrors());
        }

        $about  = $wizard['1'];
        $school = $wizard['2'];
        $region = $this->verifiedRegion($school);
        $byNpsn = service('schoolDirectory')->requiresNpsn($region['country_code'], (string) $region['province_code']);
        $manual = $byNpsn && ! empty($school['school_manual']);

        $result = service('sessionService')->registerAndStart([
            'username'      => $username,
            'password'      => $password,
            'display_name'  => $about['display_name'] ?? null,
            'age'           => $about['age'] ?? null,
            'gender'        => $about['gender'] ?? null,
            'avatar_no'     => $about['avatar_no'] ?? null,
            // Sasaran penelitian: kelas 5 SD/MI saja; tidak ada isian kelas.
            'class_level'   => config('Gelita')->registrationClass,
            // NPSN hanya berlaku di provinsi berdirektori; nama hanya bila bukan jalur NPSN
            'school_npsn'   => $byNpsn ? ($school['school_npsn'] ?? null) : null,
            'school_name'   => $byNpsn && ! $manual ? null : ($school['school_name'] ?? null),
            'country_code'  => $region['country_code'],
            'country_name'  => $region['country_name'],
            'province_code' => $region['province_code'],
            'province_name' => $region['province_name'],
            'district_code' => $region['district_code'],
            'district_name' => $region['district_name'],
            'locale'        => $input['locale'] ?? $this->locale,
            'assent'        => ! empty($input['assent']),
            'registration_metrics' => [
                'first_submit_criteria' => session('reg_first_criteria'),
                'weak_submit_count'     => (int) (session('reg_weak_count') ?? 0),
            ],
            'device' => $this->deviceInfo(),
        ]);

        // Data wizard & metrik selesai dipakai; sesi PHP sudah diperbarui service
        session()->remove([self::WIZARD, 'reg_first_criteria', 'reg_weak_count']);

        // Sambutan; nama pengguna ditampilkan, kata sandi tidak pernah.
        return redirect()->to(site_url('intro'))->with('welcome', lang('Game.registerWelcome', [
            $result['participant']->username,
        ]));
    }

    /**
     * Aturan validasi per langkah.
     *
     * Each field carries its on-screen label, so a generic message reads
     * "Nama lengkap wajib diisi." instead of "display_name wajib diisi.".
     *
     * @param array<string, mixed> $input
     *
     * @return array<string, array{label: string, rules: string}>
     */
    public function rulesFor(int $n, array $input = []): array
    {
        $config = config('Gelita');
        [$minAge, $maxAge] = $config->registrationAge;

        if ($n === 1) {
            return $this->withLabels([
                'display_name' => 'required|min_length[2]|max_length[150]|no_name_period|valid_person_name',
                'age'          => 'required|integer|greater_than_equal_to[' . $minAge . ']|less_than_equal_to[' . $maxAge . ']',
                'gender'       => 'required|in_list[' . implode(',', $config->genders) . ']',
                // Optional; only an uploaded avatar can be chosen (display plan Phase B)
                'avatar_no'    => 'permit_empty|in_list[' . implode(',', array_keys(avatar_choices())) . ']',
            ]);
        }

        if ($n === 2) {
            $byNpsn = service('schoolDirectory')->requiresNpsn((string) ($input['country_code'] ?? ''), (string) ($input['province_code'] ?? ''))
                && empty($input['school_manual']);

            return $this->withLabels([
                'country_code'  => 'required|max_length[5]',
                'province_code' => 'required_without[country_other]|permit_empty|max_length[10]',
                'district_code' => 'required_without[country_other]|permit_empty|max_length[10]',
                'country_other' => 'permit_empty|max_length[100]',
                'school_npsn'   => $byNpsn ? 'required|is_natural|exact_length[8]|known_npsn|allowed_school_stage' : 'permit_empty|max_length[20]',
                'school_manual' => 'permit_empty|in_list[1]',
                'school_name'   => $byNpsn ? 'permit_empty|max_length[200]' : 'required|min_length[3]|max_length[200]',
            ]);
        }

        return $this->withLabels([
            'username'         => 'required|valid_username|not_reserved_username|is_unique[participants.username]',
            'password'         => 'required|strong_password[username]',
            'password_confirm' => 'required|matches[password]',
            'assent'           => 'required|in_list[1]',
            'locale'           => 'permit_empty|valid_locale',
        ]);
    }

    /**
     * Attach the on-screen label to every rule string.
     *
     * @param array<string, string> $rules
     *
     * @return array<string, array{label: string, rules: string}>
     */
    private function withLabels(array $rules): array
    {
        $labels = [
            'display_name'     => 'Game.displayName',
            'age'              => 'Game.age',
            'gender'           => 'Game.gender',
            'avatar_no'        => 'Game.avatar',
            'country_code'     => 'Game.country',
            'province_code'    => 'Game.province',
            'district_code'    => 'Game.district',
            'country_other'    => 'Game.countryOtherName',
            'school_npsn'      => 'Game.schoolNpsn',
            'school_manual'    => 'Game.schoolManual',
            'school_name'      => 'Game.school',
            'username'         => 'Auth.username',
            'password'         => 'Auth.password',
            'password_confirm' => 'Auth.passwordRepeat',
            'assent'           => 'Game.assentLabel',
            'locale'           => 'Game.language',
        ];

        $out = [];

        foreach ($rules as $field => $rule) {
            $out[$field] = ['label' => lang($labels[$field] ?? $field), 'rules' => $rule];
        }

        return $out;
    }

    /**
     * @param array{contains_username?: bool} $check
     *
     * @return array<string, array<string, string>>
     */
    private function messages(array $check = []): array
    {
        return [
            'age' => [
                'greater_than_equal_to' => lang('Game.ageRange', config('Gelita')->registrationAge),
                'less_than_equal_to'    => lang('Game.ageRange', config('Gelita')->registrationAge),
            ],
            'display_name' => [
                'no_name_period'    => lang('Game.displayNamePeriod'),
                'valid_person_name' => lang('Game.displayNameChars'),
            ],
            'gender' => [
                'in_list' => lang('Game.genderRequired'),
            ],
            'avatar_no' => [
                'in_list' => lang('Game.avatarInvalid'),
            ],
            'province_code' => [
                'required_without' => lang('Validation.required'),
            ],
            'district_code' => [
                'required_without' => lang('Validation.required'),
            ],
            'school_npsn' => [
                'required'             => lang('Game.schoolNpsnRequired'),
                'is_natural'           => lang('Game.schoolNpsnFormat'),
                'exact_length'         => lang('Game.schoolNpsnFormat'),
                'known_npsn'           => lang('Game.schoolNpsnUnknown'),
                'allowed_school_stage' => lang('Game.schoolStageNotAllowed'),
            ],
            'school_name' => [
                'required'   => lang('Game.schoolNameRequired'),
                'min_length' => lang('Game.schoolNameRequired'),
            ],
            'username' => [
                'is_unique' => lang('Game.usernameTaken'),
            ],
            'password' => [
                'strong_password' => ! empty($check['contains_username'])
                    ? lang('Auth.containsUsername')
                    : lang('Auth.notStrongSubmit'),
            ],
            'password_confirm' => [
                'matches' => lang('Auth.notMatch'),
            ],
            'assent' => [
                'required' => lang('Game.assentRequired'),
                'in_list'  => lang('Game.assentRequired'),
            ],
        ];
    }

    /**
     * Langkah pertama yang belum lengkap (1, 2, atau 3).
     */
    public function firstIncomplete(): int
    {
        $wizard = $this->wizard();

        foreach ([1, 2] as $step) {
            if (! isset($wizard[(string) $step]) || ! is_array($wizard[(string) $step])) {
                return $step;
            }
        }

        return 3;
    }

    /**
     * Isian langkah 1–2 dari session PHP.
     *
     * @return array<string, array<string, mixed>>
     */
    public function wizard(): array
    {
        $wizard = session(self::WIZARD);

        return is_array($wizard) ? array_intersect_key($wizard, ['1' => true, '2' => true]) : [];
    }

    /**
     * Rapikan isian satu langkah: hanya kolom milik langkah itu, teks
     * dipangkas, NPSN tanpa spasi.
     *
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    private function normalize(int $n, array $input): array
    {
        $out = [];

        foreach (self::STEP_FIELDS[$n] ?? [] as $field) {
            $value       = $input[$field] ?? '';
            $out[$field] = is_scalar($value) ? trim((string) $value) : '';
        }

        if ($n === 1) {
            $out['display_name'] = self::normalizeName($out['display_name']);
        }

        if ($n === 2) {
            $out['school_npsn']  = (string) preg_replace('/\s+/', '', $out['school_npsn']);
            $out['country_code'] = $out['country_code'] !== '' ? $out['country_code'] : 'ID';
        }

        return $out;
    }

    /**
     * A full name as stored (owner, 22 October 2026): capital letters only,
     * one space between words, and the curly apostrophes a phone keyboard
     * types (’ ‘ `) turned into the plain one, so MA’RUF passes the rule.
     */
    public static function normalizeName(string $name): string
    {
        $name = str_replace(["\u{2019}", "\u{2018}", "\u{02BC}", '`'], "'", $name);
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));

        return mb_strtoupper($name, 'UTF-8');
    }

    /**
     * Kembali ke langkah $n dengan galat per isian. Kata sandi sengaja tidak
     * ikut `old()`: siswa mengetik ulang, dan sandi tidak pernah masuk session.
     *
     * @param array<string, mixed>  $input
     * @param array<string, string> $errors
     */
    private function backToStep(int $n, array $input, array $errors): RedirectResponse
    {
        unset($input['password'], $input['password_confirm'], $input['device_json']);

        return redirect()->to(site_url('daftar/' . $n))
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
        $directory   = RegionDirectory::all();
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
}
