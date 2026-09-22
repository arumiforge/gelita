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
        return in_array((string) $str, config('Gelita')->eventTypes, true);
    }

    /** Locale yang didukung */
    public function valid_locale(?string $str): bool
    {
        return in_array((string) $str, config('Gelita')->locales, true);
    }

    /** Item harus milik attempt yang disebut (ChallengeAttemptModel dibuat tahap 3) */
    public function item_in_attempt(?string $itemId, string $params, array $data): bool
    {
        $attemptId = (int) ($data['attempt_id'] ?? 0);
        $attempt   = model(ChallengeAttemptModel::class)->find($attemptId);

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

    public function not_reserved_username(?string $str): bool
    {
        return ! in_array(strtolower(trim((string) $str)), config('Gelita')->reservedUsernames, true);
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
}
