<?php

namespace Config;

use App\Libraries\AssetMinifier;
use App\Libraries\HeadAsGetRouteCollection;
use App\Libraries\RequestId;
use App\Libraries\VideoThumbnail;
use App\Services\AnalyticsService;
use App\Services\AssetPackageService;
use App\Services\ChallengeService;
use App\Services\ContentImportService;
use App\Services\ContentRepository;
use App\Services\EventService;
use App\Services\ExportService;
use App\Services\GameContext;
use App\Services\RawDataService;
use App\Services\ReportService;
use App\Services\RetentionService;
use App\Services\ScoringService;
use App\Services\SchoolDirectory;
use App\Services\SessionService;
use App\Services\StaffScope;
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

    /** Minified JS/CSS mirror in public/assets/min/ (gelita:aset:minify). */
    public static function assetMinifier(bool $getShared = true): AssetMinifier
    {
        if ($getShared) {
            return static::getSharedInstance('assetMinifier');
        }

        return new AssetMinifier();
    }

    /** Active asset package and its URL map (docs/rencana-paket-aset.md §5.2). */
    public static function assetPackages(bool $getShared = true): AssetPackageService
    {
        if ($getShared) {
            return static::getSharedInstance('assetPackages');
        }

        return new AssetPackageService();
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

    /** Direktori sekolah: NPSN resmi, pencocokan nama ketikan, penggabungan. */
    public static function schoolDirectory(bool $getShared = true): SchoolDirectory
    {
        if ($getShared) {
            return static::getSharedInstance('schoolDirectory');
        }

        return new SchoolDirectory();
    }

    /** Inti permainan: buka node, terima jawaban, tutup attempt. */
    public static function challengeService(bool $getShared = true): ChallengeService
    {
        if ($getShared) {
            return static::getSharedInstance('challengeService');
        }

        return new ChallengeService();
    }

    /** Satu-satunya tempat aturan bintang (`bintang-1`) ditulis. */
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

    /**
     * Data mentah per dimensi (Dok 3 §8.4). Dengan $scope → salinan yang
     * terikat cakupan staf itu (dipakai page() dan streamCsv()).
     */
    public static function rawDataService(?StaffScope $scope = null, bool $getShared = true): RawDataService
    {
        $service = $getShared ? static::getSharedInstance('rawDataService', null) : new RawDataService();

        return $scope === null ? $service : $service->withScope($scope);
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

    /** Laporan PDF (mPDF) data mentah satu peserta. */
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

    /**
     * Pengunduh thumbnail video Pustaka (YouTube/Vimeo/Drive) di sisi server.
     * Uji mengganti instans ini dengan HTTP tiruan lewat injectMock().
     */
    public static function videoThumbnail(bool $getShared = true): VideoThumbnail
    {
        if ($getShared) {
            return static::getSharedInstance('videoThumbnail');
        }

        return new VideoThumbnail();
    }
}
