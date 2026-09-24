<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Tahap 4 — seluruh route Game, API, dan Admin.
 *
 * Routing otomatis dimatikan: tidak ada URL yang muncul tanpa ditulis di sini.
 *
 * @var RouteCollection $routes
 */
$routes->setAutoRoute(false);

$routes->get('/', 'Game\HomeController::index');

// ---------------------------------------------------------------- GAME
$routes->group('', ['namespace' => 'App\Controllers\Game'], static function ($routes): void {
    // Tanpa login
    $routes->get('mulai', 'HomeController::start');
    $routes->get('persetujuan', 'RegisterController::consent');
    $routes->post('persetujuan', 'RegisterController::storeConsent');
    $routes->get('daftar', 'RegisterController::form');
    $routes->post('daftar', 'RegisterController::store');
    $routes->get('masuk', 'LoginController::form');
    $routes->post('masuk', 'LoginController::login');
    $routes->post('bahasa', 'HomeController::setLocale');

    // Sudah login, belum tentu boleh bermain (mis. wajib ganti sandi)
    $routes->group('', ['filter' => 'participantAuth'], static function ($routes): void {
        $routes->get('ganti-sandi', 'LoginController::changePasswordForm');
        $routes->post('ganti-sandi', 'LoginController::changePassword');
        $routes->get('keluar', 'LoginController::logout');
    });

    // Login + sandi sudah sah + sesi permainan aktif
    $routes->group('', ['filter' => 'gameSession'], static function ($routes): void {
        $routes->get('gerbang', 'GateController::index');
        $routes->get('gerbang/peta', 'GateController::map');
        $routes->get('intro', 'GateController::intro');
        $routes->get('intro/selesai', 'GateController::finishIntro');
        $routes->get('peta', 'MapController::kedu');
        $routes->get('wilayah/(:segment)', 'MapController::level/$1');
        $routes->get('dialog/(:segment)', 'DialogueController::show/$1');
        $routes->get('tuntas/(:segment)', 'DialogueController::done/$1');
        $routes->get('penutup', 'DialogueController::ending');
        $routes->get('misi/(:segment)/(:num)', 'ChallengeController::brief/$1/$2');
        $routes->get('tantangan/(:segment)/(:num)', 'ChallengeController::play/$1/$2');
        $routes->get('hasil/(:segment)/(:num)', 'ChallengeController::result/$1/$2');
        $routes->get('selesai/(:num)', 'ChallengeController::finished/$1');
        $routes->get('pustaka', 'LibraryController::index');
        $routes->get('pustaka/(:segment)', 'LibraryController::show/$1');
        $routes->get('profil', 'ProfileController::index');
        $routes->get('refleksi', 'ReflectionController::index');
        $routes->post('refleksi', 'ReflectionController::store');
    });
});

// ---------------------------------------------------------------- API GAME
$routes->group('api', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => 'jsonResponse',
], static function ($routes): void {
    // Tanpa login — dibatasi 20 request/menit per IP di controller
    $routes->get('auth/username-available', 'AuthApiController::usernameAvailable');

    $routes->group('', ['filter' => 'apiSession'], static function ($routes): void {
        $routes->get('session', 'SessionApiController::show');
        $routes->post('session/locale', 'SessionApiController::setLocale');
        $routes->post('session/heartbeat', 'SessionApiController::heartbeat');
        $routes->post('session/complete', 'SessionApiController::complete');

        $routes->get('levels', 'ContentApiController::levels');
        $routes->get('levels/(:num)/nodes', 'ContentApiController::nodes/$1');
        $routes->get('nodes/(:num)', 'ContentApiController::node/$1');
        $routes->get('library/(:num)', 'ContentApiController::library/$1');

        $routes->post('nodes/(:num)/attempts', 'ChallengeApiController::open/$1');
        $routes->post('attempts/(:num)/responses', 'ChallengeApiController::respond/$1');
        $routes->post('attempts/(:num)/check', 'ChallengeApiController::check/$1');
        $routes->post('attempts/(:num)/hints', 'ChallengeApiController::hint/$1');
        $routes->post('attempts/(:num)/complete', 'ChallengeApiController::complete/$1');
        $routes->post('attempts/(:num)/abandon', 'ChallengeApiController::abandon/$1');

        $routes->post('events', 'EventApiController::ingest');
        $routes->post('audio-events', 'AudioApiController::ingest');
        $routes->get('progress', 'ProgressApiController::show');
    });
});

// ---------------------------------------------------------------- ADMIN
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes): void {
    $routes->get('login', 'AuthController::loginForm');
    $routes->post('login', 'AuthController::login');
    $routes->get('logout', 'AuthController::logout');

    $routes->group('', ['filter' => 'staffAuth'], static function ($routes): void {
        $routes->get('/', 'DashboardController::index');
        $routes->get('dashboard', 'DashboardController::index');

        // Akun sendiri — guru & admin
        $routes->get('akun/sandi', 'AccountController::passwordForm');
        $routes->post('akun/sandi', 'AccountController::changePassword');

        // Data penelitian — guru & admin (scope guru dibatasi sekolahnya)
        $routes->get('peserta', 'ParticipantController::index');
        $routes->get('peserta/(:num)', 'ParticipantController::show/$1');
        $routes->post('peserta/(:num)/reset-sandi', 'ParticipantController::resetPassword/$1');
        $routes->get('sesi', 'SessionController::index');
        $routes->get('sesi/(:num)', 'SessionController::show/$1');
        $routes->get('sesi/(:num)/event', 'SessionController::timeline/$1');
        $routes->get('analitik/level', 'AnalyticsController::levels');
        $routes->get('analitik/node', 'AnalyticsController::nodes');
        $routes->get('analitik/node/(:num)', 'AnalyticsController::node/$1');
        $routes->get('analitik/butir', 'AnalyticsController::items');
        $routes->get('analitik/indikator', 'AnalyticsController::indicators');
        $routes->get('analitik/prepost', 'AnalyticsController::prePost');
        $routes->get('masukan', 'FeedbackController::index');

        // Export — guru & admin, tetapi mode non-anonim hanya admin
        $routes->get('ekspor', 'ExportController::index');
        $routes->post('ekspor/xlsx', 'ExportController::xlsx');
        $routes->post('ekspor/pdf', 'ExportController::pdf');
        $routes->get('ekspor/unduh/(:num)', 'ExportController::download/$1');

        // Konten — admin saja
        $routes->group('', ['filter' => 'staffRole:admin'], static function ($routes): void {
            $routes->get('konten', 'ContentController::index');
            $routes->get('konten/level/(:num)', 'ContentController::level/$1');
            $routes->post('konten/level/(:num)', 'ContentController::updateLevel/$1');
            $routes->get('konten/node/(:num)', 'ContentController::node/$1');
            $routes->post('konten/node/(:num)', 'ContentController::updateNode/$1');
            $routes->post('konten/node/(:num)/item', 'ContentController::createItem/$1');
            $routes->post('konten/item/(:num)', 'ContentController::updateItem/$1');
            $routes->post('konten/item/(:num)/hapus', 'ContentController::deleteItem/$1');
            $routes->post('konten/item/(:num)/opsi', 'ContentController::saveOptions/$1');
            $routes->get('konten/impor-bank', 'ContentController::importForm');
            $routes->post('konten/impor-bank/pratinjau', 'ContentController::importPreview');
            $routes->post('konten/impor-bank/jalankan', 'ContentController::importRun');
            $routes->get('konten/impor-bank/templat', 'ContentController::importTemplate');
            $routes->get('konten/bacaan/(:num)', 'ContentController::passages/$1');
            $routes->post('konten/bacaan/(:num)', 'ContentController::savePassages/$1');
            $routes->get('konten/pustaka/(:num)', 'ContentController::library/$1');
            $routes->post('konten/pustaka/(:num)', 'ContentController::saveLibrary/$1');
            $routes->get('konten/dialog/(:num)', 'ContentController::dialogues/$1');
            $routes->post('konten/dialog/(:num)', 'ContentController::saveDialogues/$1');
            $routes->post('konten/verifikasi', 'ContentController::verify');

            $routes->get('media', 'MediaController::index');
            $routes->post('media/unggah', 'MediaController::upload');
            $routes->post('media/(:num)/nonaktif', 'MediaController::deactivate/$1');
            $routes->get('media/audio', 'MediaController::audioIndex');
            $routes->post('media/audio/unggah', 'MediaController::uploadAudio');
            $routes->post('media/audio/(:num)/setujui', 'MediaController::approveAudio/$1');
            $routes->post('media/pindai', 'MediaController::scan');

            $routes->get('studi', 'StudyController::index');
            $routes->post('studi', 'StudyController::store');
            $routes->post('studi/(:num)', 'StudyController::update/$1');
            $routes->get('studi/rilis', 'StudyController::releases');
            $routes->post('studi/rilis', 'StudyController::storeRelease');
            $routes->post('studi/rilis/(:num)/aktifkan', 'StudyController::activateRelease/$1');
            $routes->get('studi/skoring', 'StudyController::scoringProfiles');
            $routes->post('studi/skoring', 'StudyController::storeScoringProfile');

            $routes->get('tata-kelola', 'GovernanceController::index');
            $routes->post('tata-kelola/hapus/pratinjau', 'GovernanceController::deletePreview');
            $routes->post('tata-kelola/hapus/(:num)/jalankan', 'GovernanceController::deleteExecute/$1');
            $routes->post('tata-kelola/hapus/(:num)/batal', 'GovernanceController::deleteCancel/$1');
            $routes->get('tata-kelola/audit', 'GovernanceController::audit');
            $routes->post('tata-kelola/retensi', 'GovernanceController::runRetention');

            $routes->get('staf', 'StaffController::index');
            $routes->post('staf', 'StaffController::store');
            $routes->post('staf/(:num)', 'StaffController::update/$1');
            $routes->post('staf/(:num)/sandi', 'StaffController::resetPassword/$1');
            $routes->post('staf/(:num)/nonaktif', 'StaffController::deactivate/$1');
        });
    });
});

// ---------------------------------------------------------------- API ADMIN
$routes->group('api/admin', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => ['jsonResponse', 'staffAuth'],
], static function ($routes): void {
    $routes->get('summary', 'AdminApiController::summary');
    $routes->get('levels', 'AdminApiController::levels');
    $routes->get('nodes', 'AdminApiController::nodes');
    $routes->get('items', 'AdminApiController::items');
    $routes->get('indicators', 'AdminApiController::indicators');
    $routes->get('prepost', 'AdminApiController::prePost');
    $routes->get('participants', 'AdminApiController::participants');
    $routes->get('sessions/(:num)/timeline', 'AdminApiController::timeline/$1');
    $routes->get('exports/(:num)/status', 'AdminApiController::exportStatus/$1');
});
