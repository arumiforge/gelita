<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\SchoolDirectory;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Cek NPSN saat siswa mengetik di `/daftar`: nama resmi sekolah muncul
 * sebelum formulir dikirim, jadi salah ketik satu angka langsung terlihat.
 *
 * Terbuka tanpa login dan hanya mengembalikan data direktori publik (nama,
 * jenjang, kecamatan, kab/kota). Dibatasi 60 request per menit per hash IP —
 * satu lab sekolah biasanya keluar lewat satu IP publik yang sama.
 */
class SchoolApiController extends BaseController
{
    private const RATE_PER_MINUTE = 60;

    public function lookup(): ResponseInterface
    {
        $throttler = service('throttler');
        $key       = 'gelita.school.' . (hash_ip($this->request->getIPAddress()) ?? 'unknown');

        if (! $throttler->check($key, self::RATE_PER_MINUTE, MINUTE)) {
            return $this->fail('RATE_LIMITED', lang('Game.errRateLimited'), 429, [
                'retry_after' => $throttler->getTokenTime(),
            ]);
        }

        $npsn = trim((string) $this->request->getGet('npsn'));

        if (preg_match(SchoolDirectory::NPSN_PATTERN, $npsn) !== 1) {
            return $this->ok(['found' => false, 'reason' => 'format']);
        }

        $directory = service('schoolDirectory');
        $school    = $directory->findByNpsn($npsn);

        if ($school === null) {
            return $this->ok(['found' => false, 'reason' => 'unknown']);
        }

        return $this->ok(['found' => true, 'school' => $directory->describe($school)]);
    }
}
