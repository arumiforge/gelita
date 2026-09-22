<?php

namespace Config;

use App\Libraries\RequestId;
use App\Services\ContentRepository;
use App\Services\GameContext;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    /**
     * Satu id acak per request, dipakai di semua respons JSON dan log.
     * Berupa objek (service() wajib mengembalikan objek) yang di-serialisasi
     * menjadi string di JSON dan dapat di-cast (string).
     */
    public static function gelitaRequestId(bool $getShared = true): RequestId
    {
        if ($getShared) {
            return static::getSharedInstance('gelitaRequestId');
        }

        return new RequestId();
    }

    /** State sesi + progres aktif untuk request ini (kelas dibuat tahap 3). */
    public static function gameContext(bool $getShared = true): GameContext
    {
        if ($getShared) {
            return static::getSharedInstance('gameContext');
        }

        return new GameContext();
    }

    /**
     * Baris staff_users yang sedang login, atau null.
     * Dimuat sekali per request; hasil null juga di-cache.
     */
    public static function staffContext(bool $getShared = true): ?object
    {
        $key = 'staffcontext';

        if ($getShared && array_key_exists($key, static::$instances)) {
            return static::$instances[$key];
        }

        $staff   = null;
        $staffId = (int) session('staff_id');

        if ($staffId > 0) {
            $staff = db_connect()->table('staff_users')
                ->select('id, username, email, role, display_name, school_id, is_active, last_login_at')
                ->where('id', $staffId)
                ->get()
                ->getRow();
        }

        if ($getShared) {
            static::$instances[$key] = $staff;
        }

        return $staff;
    }

    /** Pembaca konten ber-cache: level, node, item, media (kelas dibuat tahap 3). */
    public static function contentRepository(bool $getShared = true): ContentRepository
    {
        if ($getShared) {
            return static::getSharedInstance('contentRepository');
        }

        return new ContentRepository();
    }
}
