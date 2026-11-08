<?php

namespace App\Validation;

use App\Libraries\PasswordPolicy;
use App\Models\ChallengeAttemptModel;

class GelitaRules
{
    /** Engine type yang dikenali */
    public function valid_engine_type(?string $str): bool
    {
        return in_array((string) $str, config('Gelita')->engineTypes, true);
    }

    /** Event type yang boleh ditulis */
    public function valid_event_type(?string $str): bool
    {
        return in_array((string) $str, \App\Libraries\EventCatalog::accepted(), true);
    }

    /** Locale yang didukung */
    public function valid_locale(?string $str): bool
    {
        return in_array((string) $str, config('Gelita')->locales, true);
    }

    /**
     * Item harus milik attempt yang disebut (ChallengeAttemptModel dibuat tahap 3).
     * Parameter = nama field yang memuat id attempt, mis. item_in_attempt[attempt_id].
     *
     * `$itemId` menerima int: body JSON API mengirim angka apa adanya, dan
     * Validation CodeIgniter memanggil aturan dengan strict_types — tipe
     * `?string` saja membuat setiap jawaban dari JavaScript berakhir 500.
     */
    public function item_in_attempt(int|string|null $itemId, string $params, array $data): bool
    {
        $field     = trim($params) === '' ? 'attempt_id' : trim($params);
        $attemptId = (int) ($data[$field] ?? 0);

        if ($attemptId <= 0) {
            return false;
        }

        $attempt = model(ChallengeAttemptModel::class)->find($attemptId);

        return $attempt && in_array((int) $itemId, $attempt->selectedItemIds(), true);
    }

    /** Payload JSON tidak melebihi batas byte */
    public function json_max_bytes(?string $str, string $max): bool
    {
        return strlen((string) $str) <= (int) $max;
    }

    /** Nama pengguna siswa: huruf kecil, angka, titik, garis bawah; 3–30 karakter */
    public function valid_username(?string $str): bool
    {
        return (bool) preg_match('/^[a-z0-9._]{3,30}$/', strtolower(trim((string) $str)));
    }

    /**
     * A student's full name holds no period (owner, 22 October 2026): a
     * period means a shortened name ("M. RIZKI"), and the class list needs
     * the full one. Checked before valid_person_name for its own message.
     */
    public function no_name_period(?string $str): bool
    {
        return ! str_contains((string) $str, '.');
    }

    /**
     * A student's full name: letters (any script, with their accents),
     * spaces, and the apostrophe of names such as MA'RUF; at least one
     * letter. Digits and every other symbol are refused.
     */
    public function valid_person_name(?string $str): bool
    {
        $str = (string) $str;

        return preg_match("/^[\\p{L}\\p{M}' ]+$/u", $str) === 1 && preg_match('/\p{L}/u', $str) === 1;
    }

    public function not_reserved_username(?string $str): bool
    {
        return ! in_array(strtolower(trim((string) $str)), config('Gelita')->reservedUsernames, true);
    }

    /** Prefixes kept for test accounts and the simulator, `uji.` and `sim_` (28 K18). */
    public function not_reserved_prefix(?string $str): bool
    {
        $name = strtolower(trim((string) $str));

        foreach (config('Gelita')->reservedUsernamePrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return false;
            }
        }

        return true;
    }

    /** NPSN 8 angka milik sekolah resmi yang aktif (App\Services\SchoolDirectory) */
    public function known_npsn(?string $str): bool
    {
        return service('schoolDirectory')->findByNpsn((string) $str) !== null;
    }

    /**
     * Sekolah ber-NPSN ini jenjangnya sesuai sasaran (kelas 5 SD/MI; SLB
     * boleh). NPSN yang tidak dikenal dilaporkan aturan known_npsn, jadi di
     * sini dianggap lolos.
     */
    public function allowed_school_stage(?string $str): bool
    {
        $directory = service('schoolDirectory');
        $school    = $directory->findByNpsn((string) $str);

        return $school === null || $directory->stageAllowed($school);
    }

    /**
     * Kata sandi siswa kuat. Parameter = nama field username, mis. strong_password[username].
     * Memakai PasswordPolicy — aturan yang sama dengan yang dikirim ke JavaScript.
     */
    public function strong_password(?string $str, string $usernameField, array $data): bool
    {
        $result = (new PasswordPolicy())->check((string) $str, $data[$usernameField] ?? null);

        return $result['acceptable'];
    }

    /**
     * Pose tokoh sah (Config\Gelita::$characterPoses). Parameter = nama field
     * tokoh, mis. valid_dialogue_pose[character_code]. Bila tokohnya tidak ikut
     * dikirim (update sebagian), pose cukup sah untuk salah satu tokoh.
     */
    public function valid_dialogue_pose(?string $str, string $characterField, array $data): bool
    {
        $poses     = config('Gelita')->characterPoses;
        $character = $data[trim($characterField) ?: 'character_code'] ?? null;

        if ($character !== null) {
            return in_array((string) $str, $poses[(string) $character] ?? [], true);
        }

        return in_array((string) $str, array_merge(...array_values($poses)), true);
    }

    /** Efek layar dialog sah (Config\Gelita::$dialogueEffects) */
    public function valid_dialogue_effect(?string $str): bool
    {
        return in_array((string) $str, config('Gelita')->dialogueEffects, true);
    }
}
