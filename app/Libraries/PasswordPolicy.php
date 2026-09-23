<?php

namespace App\Libraries;

/**
 * Aturan & penilaian kekuatan kata sandi SISWA.
 * Satu-satunya pemutus di server; JavaScript menerima salinan config lewat toClient().
 * Tidak pernah mengembalikan, menyimpan, atau mencatat isi kata sandi.
 */
class PasswordPolicy
{
    private const BCRYPT_MAX_BYTES = 72;

    private array $cfg;

    public function __construct(array $cfg = [])
    {
        $this->cfg = $cfg !== [] ? $cfg : config('Gelita')->passwordPolicy;
    }

    /**
     * @return array{
     *   criteria: array<string, bool>,
     *   met: int,
     *   level: string,
     *   contains_username: bool,
     *   acceptable: bool
     * }
     */
    public function check(string $password, ?string $username = null): array
    {
        $length = mb_strlen($password);

        $criteria = [
            'length' => $length >= $this->cfg['min_length']
                && $length <= $this->cfg['max_length']
                && strlen($password) <= self::BCRYPT_MAX_BYTES,
        ];

        if ($this->cfg['require_upper']) {
            $criteria['upper'] = (bool) preg_match('/[A-Z]/', $password);
        }
        if ($this->cfg['require_lower']) {
            $criteria['lower'] = (bool) preg_match('/[a-z]/', $password);
        }
        if ($this->cfg['require_digit']) {
            $criteria['digit'] = (bool) preg_match('/[0-9]/', $password);
        }
        if ($this->cfg['require_symbol']) {
            $criteria['symbol'] = (bool) preg_match('/[^A-Za-z0-9]/', $password);
        }

        $met   = count(array_filter($criteria));
        $level = $this->levelFor($met);

        $username         = $username === null ? '' : trim($username);
        $containsUsername = $this->cfg['forbid_username']
            && $username !== ''
            && str_contains(mb_strtolower($password), mb_strtolower($username));

        return [
            'criteria'          => $criteria,
            'met'               => $met,
            'level'             => $level,
            'contains_username' => $containsUsername,
            'acceptable'        => $level === $this->cfg['accept_level'] && ! $containsUsername,
        ];
    }

    /**
     * Konfigurasi yang dikirim ke JavaScript (tanpa data rahasia), ditambah
     * batas byte bcrypt agar syarat panjang di layar sama persis dengan check().
     */
    public function toClient(): array
    {
        return $this->cfg + ['max_bytes' => self::BCRYPT_MAX_BYTES];
    }

    private function levelFor(int $met): string
    {
        foreach ($this->cfg['levels'] as $level => [$min, $max]) {
            if ($met >= $min && $met <= $max) {
                return $level;
            }
        }

        return 'weak';
    }
}
