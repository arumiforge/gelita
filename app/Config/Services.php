<?php

namespace Config;

use App\Libraries\HeadAsGetRouteCollection;
use App\Libraries\RequestId;
use App\Services\AnalyticsService;
use App\Services\ChallengeService;
use App\Services\ContentImportService;
use App\Services\ContentRepository;
use App\Services\EventService;
use App\Services\ExportService;
use App\Services\GameContext;
use App\Services\ReportService;
use App\Services\RetentionService;
use App\Services\ScoringService;
use App\Services\SessionService;
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

    /** Koleksi rute yang melayani HEAD dengan rute GET (lihat kelasnya). */
    public static function routes(bool $getShared = true): HeadAsGetRouteCollection
    {
        if ($getShared) {
            return static::getSharedInstance('routes');
        }

        return new HeadAsGetRouteCollection(service('locator'), new Modules(), config(Routing::class));
    }

    /** State sesi + progres aktif untuk request ini. */
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
                ->select('id, username, email, role, display_name, school_id, is_active, must_change_password, last_login_at')
                ->where('id', $staffId)
                ->get()
                ->getRow();
        }

        if ($getShared) {
            static::$instances[$key] = $staff;
        }

        return $staff;
    }

    /** Pembaca konten ber-cache: level, node, item, media. */
    public static function contentRepository(bool $getShared = true): ContentRepository
    {
        if ($getShared) {
            return static::getSharedInstance('contentRepository');
        }

        return new ContentRepository();
    }

    /** Siklus hidup akun siswa dan sesi permainan. */
    public static function sessionService(bool $getShared = true): SessionService
    {
        if ($getShared) {
            return static::getSharedInstance('sessionService');
        }

        return new SessionService();
    }

    /** Inti permainan: buka node, terima jawaban, tutup attempt. */
    public static function challengeService(bool $getShared = true): ChallengeService
    {
        if ($getShared) {
            return static::getSharedInstance('challengeService');
        }

        return new ChallengeService();
    }

    /** Satu-satunya tempat rumus skor ditulis. */
    public static function scoringService(bool $getShared = true): ScoringService
    {
        if ($getShared) {
            return static::getSharedInstance('scoringService');
        }

        return new ScoringService();
    }

    /** Penulis raw event penelitian. */
    public static function eventService(bool $getShared = true): EventService
    {
        if ($getShared) {
            return static::getSharedInstance('eventService');
        }

        return new EventService();
    }

    /**
     * Dataset dasbor & export. $schoolScope NULL = admin (semua sekolah),
     * terisi = guru (dibatasi sekolahnya).
     */
    public static function analyticsService(
        ?int $schoolScope = null,
        bool $anonymous = false,
        bool $getShared = true,
    ): AnalyticsService {
        if ($getShared) {
            return static::getSharedInstance('analyticsService', $schoolScope, $anonymous)
                ->forStaff($schoolScope, $anonymous);
        }

        return new AnalyticsService($schoolScope, $anonymous);
    }

    /** Pemuat workbook bank soal. */
    public static function contentImportService(bool $getShared = true): ContentImportService
    {
        if ($getShared) {
            return static::getSharedInstance('contentImportService');
        }

        return new ContentImportService();
    }

    /** Pembangun berkas export XLSX/PDF; hak pemohon dibaca ulang dari staff_users. */
    public static function exportService(bool $getShared = true): ExportService
    {
        if ($getShared) {
            return static::getSharedInstance('exportService');
        }

        return new ExportService();
    }

    /** Laporan PDF (mPDF) untuk studi dan satu peserta. */
    public static function reportService(bool $getShared = true): ReportService
    {
        if ($getShared) {
            return static::getSharedInstance('reportService');
        }

        return new ReportService();
    }

    /** Penghapusan data dua langkah dan retensi terjadwal. */
    public static function retentionService(bool $getShared = true): RetentionService
    {
        if ($getShared) {
            return static::getSharedInstance('retentionService');
        }

        return new RetentionService();
    }
}
