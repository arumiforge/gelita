<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;
use App\Validation\GelitaRules;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Umpan balik ketersediaan nama pengguna saat siswa mengetik di `/daftar`.
 *
 * Endpoint ini terbuka tanpa login, jadi dibatasi 20 request per menit per
 * hash IP — cukup untuk mengetik, tidak cukup untuk memanen daftar akun.
 */
class AuthApiController extends BaseController
{
    private const RATE_PER_MINUTE = 20;

    public function usernameAvailable(): ResponseInterface
    {
        $throttler = service('throttler');
        $key       = 'gelita.username.' . (hash_ip($this->request->getIPAddress()) ?? 'unknown');

        if (! $throttler->check($key, self::RATE_PER_MINUTE, MINUTE)) {
            return $this->fail('RATE_LIMITED', lang('Game.errRateLimited'), 429, [
                'retry_after' => $throttler->getTokenTime(),
            ]);
        }

        $participants = model(ParticipantModel::class);
        $username     = $participants->normalizeUsername((string) $this->request->getGet('u'));
        $rules        = new GelitaRules();

        if (! $rules->valid_username($username)) {
            return $this->ok(['available' => false, 'reason' => 'format']);
        }

        if (! $rules->not_reserved_username($username)) {
            return $this->ok(['available' => false, 'reason' => 'reserved']);
        }

        if (! $participants->usernameAvailable($username)) {
            return $this->ok(['available' => false, 'reason' => 'taken']);
        }

        return $this->ok(['available' => true]);
    }
}
