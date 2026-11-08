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
// Installable app manifest; icons come from the panel slots (no login, no session)
$routes->get('manifest.json', 'Game\AppController::manifest');

// ---------------------------------------------------------------- GAME
$routes->group('', ['namespace' => 'App\Controllers\Game'], static function ($routes): void {
    // Tanpa login
    $routes->get('mulai', 'HomeController::start');
    // Tentang GELITA: the game in plain words, from the HUD menu (owner, 21 October 2026)
    $routes->get('tentang', 'HomeController::about');
    // Pendaftaran 3 langkah (Dok 1 §10)
    $routes->get('daftar', 'RegisterController::index');
    $routes->get('daftar/(:num)', 'RegisterController::step/$1');
    $routes->post('daftar/(:num)', 'RegisterController::saveStep/$1');
    $routes->get('masuk', 'LoginController::form');
    $routes->post('masuk', 'LoginController::login');
    $routes->post('bahasa', 'HomeController::setLocale');
    // Asset package on this device (rencana-paket-aset Stage 3): download before Mulai, teacher check
    $routes->get('siapkan', 'PackageController::prepare');
    $routes->get('periksa-paket', 'PackageController::inspect');

    // Sudah login, belum tentu boleh bermain (mis. wajib ganti sandi)
    $routes->group('', ['filter' => ['participantAuth', 'screenView']], static function ($routes): void {
        $routes->get('ganti-sandi', 'LoginController::changePasswordForm');
        $routes->post('ganti-sandi', 'LoginController::changePassword');
        $routes->get('keluar', 'LoginController::logout');
        // Field mode (revision 10, part 2: 28 §6.4, §7): token and "see you", no game session needed
        $routes->get('token', 'FieldTokenController::form');
        $routes->post('token', 'FieldTokenController::submit');
        $routes->get('sampai-jumpa', 'FieldTokenController::closed');
    });

    // Login + sandi sudah sah + sesi permainan aktif; setiap layar → screen_viewed
    $routes->group('', ['filter' => ['gameSession', 'screenView']], static function ($routes): void {
        $routes->get('gerbang', 'GateController::index');
        $routes->get('gerbang/peta', 'GateController::map');
        $routes->get('intro', 'GateController::intro');
        $routes->get('intro/selesai', 'GateController::finishIntro');
        $routes->get('peta', 'MapController::kedu');
        $routes->get('wilayah/(:segment)', 'MapController::level/$1');
        $routes->get('dialog/(:segment)', 'DialogueController::show/$1');
        // Part 6 (32 §12): the bridge story before the fifth challenge
        $routes->get('penghubung/(:segment)', 'DialogueController::bridge/$1');
        $routes->get('tuntas/(:segment)', 'DialogueController::done/$1');
        $routes->get('penutup', 'DialogueController::ending');
        $routes->get('misi/(:segment)/(:num)', 'ChallengeController::brief/$1/$2');
        $routes->get('tantangan/(:segment)/(:num)', 'ChallengeController::play/$1/$2');
        $routes->get('hasil/(:segment)/(:num)', 'ChallengeController::result/$1/$2');
        $routes->get('selesai/(:num)', 'ChallengeController::finished/$1');
        $routes->get('pustaka', 'LibraryController::index');
        $routes->get('pustaka/(:segment)', 'LibraryController::show/$1');
        $routes->get('profil', 'ProfileController::index');
        $routes->post('profil/avatar', 'ProfileController::avatar');
        $routes->get('refleksi', 'ReflectionController::index');
        $routes->post('refleksi', 'ReflectionController::store');
    });
});

// ---------------------------------------------------------------- API GAME
$routes->group('api', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => 'jsonResponse',
], static function ($routes): void {
    // Tanpa login — dibatasi per IP di controller (20 dan 60 request/menit)
    $routes->get('auth/username-available', 'AuthApiController::usernameAvailable');
    $routes->get('schools/lookup', 'SchoolApiController::lookup');
    // Active asset package (docs/rencana-paket-aset.md §5.3); checked before Mulai, so no login
    $routes->get('paket/aktif', 'PackageApiController::active');

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
        // Rights per capability (revision 10, part 1: App\Libraries\StaffAccess, 27 §4, §7).
        // Fixed paths are registered before patterned ones, so the order reads clearly.
        $routes->get('/', 'DashboardController::index', ['filter' => 'staffCan:dashboard.view']);
        $routes->get('dashboard', 'DashboardController::index', ['filter' => 'staffCan:dashboard.view']);

        // Own account (every staff role)
        $routes->get('akun/sandi', 'AccountController::passwordForm');
        $routes->post('akun/sandi', 'AccountController::changePassword');

        // Students: the admin sees accounts, researchers see codes (one route, two views)
        $routes->group('', ['filter' => 'staffCan:participants.reset_by_code'], static function ($routes): void {
            $routes->get('peserta/reset-sandi', 'ParticipantController::resetByCodeForm');
            $routes->post('peserta/reset-sandi', 'ParticipantController::resetByCode');
        });
        $routes->group('', ['filter' => 'staffCan:participants.accounts,participants.research'], static function ($routes): void {
            $routes->get('peserta', 'ParticipantController::index');
            $routes->get('peserta/(:num)', 'ParticipantController::show/$1');
        });
        $routes->post('peserta/(:num)/reset-sandi', 'ParticipantController::resetPassword/$1', ['filter' => 'staffCan:participants.reset_by_id']);

        // Field sessions and L2 seats (revision 10, part 2: 28 §15); `baru` before the patterned paths
        $routes->group('', ['filter' => 'staffCan:field_sessions.manage'], static function ($routes): void {
            $routes->get('sesi-lapangan', 'FieldSessionController::index');
            $routes->get('sesi-lapangan/baru', 'FieldSessionController::create');
            $routes->post('sesi-lapangan/baru', 'FieldSessionController::store');
            $routes->get('sesi-lapangan/(:num)', 'FieldSessionController::show/$1');
            $routes->post('sesi-lapangan/(:num)/jam', 'FieldSessionController::updateHours/$1');
            $routes->post('sesi-lapangan/(:num)/token-baru', 'FieldSessionController::regenerateToken/$1');
            $routes->post('sesi-lapangan/(:num)/tutup', 'FieldSessionController::close/$1');
            $routes->get('sesi-lapangan/(:num)/kursi', 'FieldSessionController::seats/$1');
            $routes->post('sesi-lapangan/(:num)/kursi', 'FieldSessionController::saveSeats/$1');
            $routes->get('sesi-lapangan/(:num)/susulan', 'FieldSessionController::makeupForm/$1');
            $routes->post('sesi-lapangan/(:num)/susulan', 'FieldSessionController::storeMakeup/$1');
            // Part 3 (29 §13): field sheet template, upload, second typing
            $routes->get('sesi-lapangan/(:num)/lembar', 'FieldSheetController::template/$1');
            $routes->get('sesi-lapangan/(:num)/lembar/unggah', 'FieldSheetController::uploadForm/$1');
            $routes->post('sesi-lapangan/(:num)/lembar/unggah', 'FieldSheetController::preview/$1');
            $routes->post('sesi-lapangan/(:num)/lembar/simpan', 'FieldSheetController::save/$1');
        });

        // Part 3 (29 §5.6, K15): the admin's tech log
        $routes->group('', ['filter' => 'staffCan:tech_logs.view'], static function ($routes): void {
            $routes->get('log-teknis', 'TechLogController::index');
            $routes->get('log-teknis/(perangkat|event|sesi)', 'TechLogController::table/$1');
            $routes->get('log-teknis/(perangkat|event|sesi)/csv', 'TechLogController::csv/$1');
        });
        $routes->get('pemantauan', 'MonitoringController::index', ['filter' => 'staffCan:monitoring.view']);

        // Research data of all students: head researcher only
        $routes->group('', ['filter' => 'staffCan:research.browse'], static function ($routes): void {
            $routes->get('sesi', 'SessionController::index');
            $routes->get('sesi/(:num)', 'SessionController::show/$1');
            $routes->get('sesi/(:num)/event', 'SessionController::timeline/$1');
            $routes->get('masukan', 'FeedbackController::index');
            $routes->get('data', 'RawDataController::index');
            $routes->get('data/(:segment)', 'RawDataController::show/$1');
        });

        // Downloads: head researcher only, always coded (27 K14)
        $routes->group('', ['filter' => 'staffCan:research.export'], static function ($routes): void {
            $routes->get('ekspor', 'ExportController::index');
            $routes->post('ekspor/xlsx', 'ExportController::xlsx');
            $routes->post('ekspor/csv', 'ExportController::csv');
            $routes->post('ekspor/pdf', 'ExportController::pdf');
            $routes->get('ekspor/unduh/(:num)', 'ExportController::download/$1');
        });

        // Content, narration, media: read for content.view, writes and the bank import for content.edit (27 K7)
        $routes->group('', ['filter' => 'staffCan:content.edit'], static function ($routes): void {
            $routes->get('konten/impor-bank', 'ContentController::importForm');
            $routes->post('konten/impor-bank/pratinjau', 'ContentController::importPreview');
            $routes->post('konten/impor-bank/jalankan', 'ContentController::importRun');
            $routes->get('konten/impor-bank/templat', 'ContentController::importTemplate');
            $routes->post('konten/level/(:num)', 'ContentController::updateLevel/$1');
            $routes->post('konten/node/(:num)', 'ContentController::updateNode/$1');
            $routes->post('konten/node/(:num)/item', 'ContentController::createItem/$1');
            $routes->post('konten/item/(:num)', 'ContentController::updateItem/$1');
            $routes->post('konten/item/(:num)/hapus', 'ContentController::deleteItem/$1');
            $routes->post('konten/bacaan/(:num)', 'ContentController::savePassages/$1');
            $routes->post('konten/pustaka/(:num)', 'ContentController::saveLibrary/$1');
            $routes->post('konten/dialog/(:num)', 'ContentController::saveDialogues/$1');
            $routes->get('konten/narasi/unggah', 'NarrationController::uploadForm');
            $routes->post('konten/narasi/unggah', 'NarrationController::upload');
            $routes->post('konten/narasi/impor-folder', 'NarrationController::importFolder');
            $routes->post('konten/narasi/setujui', 'NarrationController::approveAll');
            $routes->post('konten/narasi/batalkan', 'NarrationController::cancelAll');
            $routes->post('konten/verifikasi', 'ContentController::verify');
            $routes->post('media/unggah', 'MediaController::upload');
            $routes->post('media/(:num)/nonaktif', 'MediaController::deactivate/$1');
            $routes->post('media/(:num)/unggah-gerak', 'MediaController::uploadFrames/$1');
            $routes->post('media/audio/unggah', 'MediaController::uploadAudio');
            $routes->post('media/audio/(:num)/setujui', 'MediaController::approveAudio/$1');
            $routes->post('media/pindai', 'MediaController::scan');
        });
        $routes->group('', ['filter' => 'staffCan:content.view'], static function ($routes): void {
            $routes->get('konten', 'ContentController::index');
            $routes->get('konten/level/(:num)', 'ContentController::level/$1');
            $routes->get('konten/node/(:num)', 'ContentController::node/$1');
            $routes->get('konten/bacaan/(:num)', 'ContentController::passages/$1');
            $routes->get('konten/pustaka/(:num)', 'ContentController::library/$1');
            $routes->get('konten/dialog/(:num)', 'ContentController::dialogues/$1');
            $routes->get('konten/narasi', 'NarrationController::index');
            $routes->get('konten/narasi/daftar-rekaman', 'NarrationController::recordingList');
            $routes->get('konten/narasi/naskah-pdf', 'NarrationController::scriptPdf');
            $routes->get('media', 'MediaController::index');
            $routes->get('media/kelengkapan', 'MediaController::checklist');
            $routes->get('media/audio', 'MediaController::audioIndex');
            $routes->get('media/(:num)', 'MediaController::show/$1');
        });

        // Studies: the head researcher changes them, the admin only looks (27 K12)
        $routes->get('studi', 'StudyController::index', ['filter' => 'staffCan:studies.view']);
        $routes->group('', ['filter' => 'staffCan:studies.manage'], static function ($routes): void {
            $routes->post('studi', 'StudyController::store');
            $routes->post('studi/(:num)', 'StudyController::update/$1');
        });

        // Releases and asset packages stay with the admin, under /admin/studi/rilis
        $routes->group('', ['filter' => 'staffCan:releases.manage'], static function ($routes): void {
            $routes->get('studi/rilis', 'StudyController::releases');
            $routes->post('studi/rilis', 'StudyController::storeRelease');
            $routes->get('studi/rilis/(:num)/aktifkan', 'StudyController::confirmActivation/$1');
            $routes->post('studi/rilis/(:num)/aktifkan', 'StudyController::activateRelease/$1');

            // Rilis → Paket aset: package mode switch and batch build (docs/rencana-paket-aset.md §6.3)
            $routes->get('studi/rilis/paket', 'PackageController::index');
            $routes->post('studi/rilis/paket/bangun', 'PackageController::startBuild');
            $routes->post('studi/rilis/paket/bangun/(:segment)/lanjut', 'PackageController::stepBuild/$1');
            $routes->post('studi/rilis/paket/(:segment)/terbitkan', 'PackageController::publish/$1');
            $routes->post('studi/rilis/paket/matikan', 'PackageController::switchOff');
            $routes->post('studi/rilis/paket/izin-daring', 'PackageController::saveOnlineLab');
            $routes->post('studi/rilis/paket/periksa', 'PackageController::verify');
        });

        // Governance: deletion, audit log, retention (admin)
        $routes->group('', ['filter' => 'staffCan:deletion.execute'], static function ($routes): void {
            $routes->get('tata-kelola', 'GovernanceController::index');
            $routes->post('tata-kelola/hapus/pratinjau', 'GovernanceController::deletePreview');
            $routes->post('tata-kelola/hapus/(:num)/jalankan', 'GovernanceController::deleteExecute/$1');
            $routes->post('tata-kelola/hapus/(:num)/batal', 'GovernanceController::deleteCancel/$1');
        });
        $routes->get('tata-kelola/audit', 'GovernanceController::audit', ['filter' => 'staffCan:audit.view']);
        $routes->post('tata-kelola/retensi', 'GovernanceController::runRetention', ['filter' => 'staffCan:retention.run']);

        // Part 4 (30 §16): consent per student, parents' data requests, risk follow-up, one child's copy,
        // registration correction, consent register, study closure. Fixed paths before patterned ones.
        $routes->get('sesi-lapangan/(:num)/izin', 'ConsentController::edit/$1', ['filter' => 'staffCan:field_sessions.manage']);
        $routes->post('sesi-lapangan/(:num)/izin', 'ConsentController::save/$1', ['filter' => 'staffCan:field_sessions.manage']);
        $routes->get('permintaan-data', 'DataRequestController::index', ['filter' => 'staffCan:deletion.request,deletion.confirm,deletion.execute']);
        $routes->get('permintaan-data/baru', 'DataRequestController::create', ['filter' => 'staffCan:deletion.request']);
        $routes->post('permintaan-data/baru', 'DataRequestController::store', ['filter' => 'staffCan:deletion.request']);
        $routes->get('permintaan-data/(:num)', 'DataRequestController::show/$1', ['filter' => 'staffCan:deletion.request,deletion.confirm,deletion.execute']);
        $routes->post('permintaan-data/(:num)/konfirmasi', 'DataRequestController::confirm/$1', ['filter' => 'staffCan:deletion.confirm']);
        $routes->post('permintaan-data/(:num)/tolak', 'DataRequestController::reject/$1', ['filter' => 'staffCan:deletion.confirm']);
        $routes->post('permintaan-data/(:num)/batal', 'DataRequestController::cancel/$1', ['filter' => 'staffCan:deletion.request']);
        $routes->post('permintaan-data/(:num)/centang', 'DataRequestController::tick/$1', ['filter' => 'staffCan:deletion.request,deletion.confirm,deletion.execute']);
        $routes->get('permintaan-data/(:num)/berita-acara', 'DataRequestController::minutes/$1', ['filter' => 'staffCan:deletion.confirm,deletion.execute']);
        $routes->get('tindak-lanjut', 'RiskFollowUpController::index', ['filter' => 'staffCan:risk_flags.view']);
        $routes->get('peserta/salinan', 'ParticipantCopyController::form', ['filter' => 'staffCan:research.export']);
        $routes->post('peserta/salinan', 'ParticipantCopyController::download', ['filter' => 'staffCan:research.export']);
        $routes->post('peserta/(:num)/betulkan', 'ParticipantController::correct/$1', ['filter' => 'staffCan:participants.accounts']);
        $routes->post('peserta/(:num)/aktifkan', 'ParticipantController::reactivate/$1', ['filter' => 'staffCan:participants.accounts']);
        $routes->get('ekspor/izin', 'ExportController::consentRegister', ['filter' => 'staffCan:research.export']);
        $routes->get('studi/(:num)/penutupan', 'StudyClosureController::show/$1', ['filter' => 'staffCan:studies.view']);
        $routes->post('studi/(:num)/penutupan/hitung', 'StudyClosureController::preview/$1', ['filter' => 'staffCan:identities.replace']);
        $routes->post('studi/(:num)/penutupan/jalankan', 'StudyClosureController::run/$1', ['filter' => 'staffCan:identities.replace']);
        $routes->get('studi/(:num)/penutupan/nama', 'StudyClosureController::feedbackNames/$1', ['filter' => 'staffCan:research.export']);
        $routes->post('studi/(:num)/penutupan/nama', 'StudyClosureController::saveFeedbackNames/$1', ['filter' => 'staffCan:research.export']);

        $routes->group('', ['filter' => 'staffCan:schools.manage'], static function ($routes): void {
            $routes->get('sekolah', 'SchoolController::index');
            $routes->post('sekolah/gabung-otomatis', 'SchoolController::autoMerge');
            $routes->post('sekolah/(:num)/gabung', 'SchoolController::merge/$1');
            $routes->post('sekolah/(:num)/sahkan', 'SchoolController::verify/$1');
            $routes->post('sekolah/kode-penelitian', 'SchoolController::setResearchCode');
        });

        $routes->group('', ['filter' => 'staffCan:staff.manage'], static function ($routes): void {
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
    $routes->get('summary', 'AdminApiController::summary', ['filter' => 'staffCan:dashboard.view']);
    $routes->get('sessions/(:num)/timeline', 'AdminApiController::timeline/$1', ['filter' => 'staffCan:research.browse']);
    $routes->get('exports/(:num)/status', 'AdminApiController::exportStatus/$1', ['filter' => 'staffCan:research.export']);
    $routes->get('monitoring', 'AdminApiController::monitoring', ['filter' => 'staffCan:monitoring.view']);
});
