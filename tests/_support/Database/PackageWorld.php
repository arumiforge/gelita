<?php

namespace Tests\Support\Database;

use App\Services\AssetPackageService;
use CodeIgniter\Database\BaseConnection;
use Config\Services;

/**
 * Small world for asset package tests (SQLite group `tests`): the full schema
 * (ResetTables), three regions, an active release, and a temporary public/
 * folder with real files for a handful of media assets, narration (approved
 * and draft), and game code. The AssetPackageService for that folder is
 * injected as the shared `assetPackages` service, so helpers, the builder,
 * the commands, and the API all use it.
 */
trait PackageWorld
{
    use ResetTables;

    private BaseConnection $conn;

    private string $public;

    private string $data;

    private AssetPackageService $packages;

    /** @var array<string, int> asset_key => media id */
    private array $media = [];

    private function createPackageWorld(): void
    {
        $this->conn     = $this->createResetTables();
        $root         = sys_get_temp_dir() . '/gelita-paket-' . bin2hex(random_bytes(4));
        $this->public = $root . '/public/';
        $this->data   = $root . '/writable/paket/';
        mkdir($this->public, 0775, true);

        foreach (['temanggung', 'magelang', 'wonosobo'] as $i => $code) {
            $this->conn->table('levels')->insert([
                'sequence' => $i + 1, 'code' => $code, 'name_id' => ucfirst($code), 'name_en' => ucfirst($code),
                'difficulty' => 'mudah', 'focus_id' => 'Fokus', 'focus_en' => 'Focus',
            ]);
        }

        $this->conn->table('game_releases')->insert([
            'release_code' => 'GELITA-UJI', 'app_version' => '2.0.0', 'content_version' => 'kelas5-1',
            'asset_version' => '1', 'scoring_version' => 'bintang-1', 'is_active' => 1,
        ]);

        // Game code, plus admin code that must stay out of the package
        $this->writeFile('assets/css/base.css', "@font-face { src: url('../fonts/a.woff2'); }\n");
        $this->writeFile('assets/css/admin.css', "body { color: red; }\n");
        $this->writeFile('assets/js/game.js', "import './core/config.js';\n");
        $this->writeFile('assets/js/core/config.js', "export const x = 1;\n");
        $this->writeFile('assets/js/admin.js', "console.log('admin');\n");
        $this->writeFile('assets/js/admin/tables.js', "console.log('tables');\n");
        $this->writeFile('assets/vendor/howler.min.js', "/* howler */\n");
        $this->writeFile('assets/fonts/a.woff2', 'wOF2-font');
        $this->writeFile('offline.html', "<!doctype html><title>Offline</title>\n");

        $this->addMedia('bg.welcome', 'assets/uploads/bg.welcome.jpg', 'image', 'image/jpeg', 'welcome-image');
        $this->addMedia('ui.btn-start', 'assets/uploads/ui.btn-start.png', 'image', 'image/png', 'start-id');
        $this->addMedia('ui.btn-start.en', 'assets/uploads/ui.btn-start.en.png', 'image', 'image/png', 'start-en', ['locale' => 'en']);
        $this->addMedia('char.jaka.idle.1', 'assets/uploads/char.jaka.idle.1.png', 'sprite_frame', 'image/png', 'jaka');
        $this->addMedia('bg.temanggung.region', 'assets/uploads/bg.temanggung.region.jpg', 'image', 'image/jpeg', 'tmg-bg');
        $this->addMedia('challenge.item.mgl-4-a01', 'assets/uploads/mockup/challenge.item.mgl-4-a01.svg', 'image', 'image/svg+xml', '<svg/>', ['is_placeholder' => 1]);
        $this->addMedia('library.candi', 'assets/uploads/library.candi.jpg', 'image', 'image/jpeg', 'candi');
        // Same bytes as bg.welcome under another key: one file, two keys
        $this->addMedia('bg.auth', 'assets/uploads/bg.auth.jpg', 'image', 'image/jpeg', 'welcome-image');
        $this->addMedia('audio.sfx.click', 'assets/audio/sfx/click.mp3', 'audio', 'audio/mpeg', 'click');
        $this->addMedia('audio.music.challenge', 'assets/audio/music/challenge.mp3', 'audio', 'audio/mpeg', 'music');

        $this->addNarration('id', 'intro-01', 'approved', 'intro id');
        $this->addNarration('en', 'intro-01', 'approved', 'intro en');
        $this->addNarration('id', 'dialog-wonosobo-03', 'approved', 'dialog wnb');
        $this->addNarration('id', 'petunjuk-tmg-4-01', 'approved', 'clue tmg');
        $this->addNarration('id', 'penutup-01', 'approved', 'ending');
        $this->addNarration('id', 'kenal-magelang-01', 'draft', 'draft mgl');

        // Library page of Magelang links library.candi → group wilayah-magelang
        $this->conn->table('library_pages')->insert(['level_id' => 2, 'sequence' => 1, 'title_id' => 'Candi', 'title_en' => 'Temple', 'body_id' => 'Isi.', 'body_en' => 'Body.']);
        $this->conn->table('library_media')->insert(['library_page_id' => 1, 'sequence' => 1, 'media_kind' => 'image', 'media_asset_id' => $this->media['library.candi']]);

        $this->packages = new AssetPackageService($this->public, $this->data);
        Services::injectMock('assetPackages', $this->packages);
        Services::resetSingle('contentRepository');
        service('cache')->clean();
    }

    private function dropPackageWorld(): void
    {
        $this->dropResetTables();
        $this->removeDir(dirname($this->public));
    }

    /** @param array<string, mixed> $extra */
    private function addMedia(string $key, string $path, string $type, string $mime, string $content, array $extra = []): int
    {
        $this->writeFile($path, $content);
        $this->conn->table('media_assets')->insert($extra + [
            'asset_key' => $key, 'asset_type' => $type, 'storage_path' => $path, 'mime_type' => $mime,
            'file_size' => strlen($content), 'sha256' => hash('sha256', $content), 'is_active' => 1,
        ]);

        return $this->media[$key] = (int) $this->conn->insertID();
    }

    private function addNarration(string $locale, string $code, string $status, string $content): int
    {
        $id = $this->addMedia("audio.narasi.{$locale}.{$code}", "assets/audio/narasi/{$locale}/{$code}.mp3", 'audio', 'audio/mpeg', $content, ['locale' => $locale]);
        $this->conn->table('audio_assets')->insert([
            'media_asset_id' => $id, 'locale' => $locale, 'context_code' => $code, 'transcript' => 'Teks.', 'approval_status' => $status,
        ]);

        return (int) $this->conn->insertID();
    }

    /** Replace a media file and its recorded sha256, as MediaStore does on upload. */
    private function replaceMedia(string $key, string $content): void
    {
        $row = $this->conn->table('media_assets')->where('asset_key', $key)->get()->getRowArray();
        $this->writeFile((string) $row['storage_path'], $content);
        $this->conn->table('media_assets')->where('id', $row['id'])->update(['sha256' => hash('sha256', $content), 'file_size' => strlen($content)]);
        service('contentRepository')->flush();
        service('cache')->clean();
    }

    /** Point the active release to a package (or '1' = none), as the admin would. */
    private function useRelease(string $assetVersion): void
    {
        $this->conn->table('game_releases')->where('is_active', 1)->update(['asset_version' => $assetVersion]);
        $this->packages->forget();
    }

    private function writeFile(string $relative, string $content): void
    {
        $path = $this->public . $relative;

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, $content);
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
