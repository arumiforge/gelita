<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tahap 8: berkas operasional di `deploy/`. Aturan di sini mengunci cacat yang
 * terbukti saat skrip dijalankan (docs/08_DEPLOYMENT.md → Catatan Implementasi
 * Tahap 8), bukan gaya penulisan.
 *
 * @internal
 */
final class DeployFilesTest extends CIUnitTestCase
{
    private const DEPLOY = ROOTPATH . 'deploy/';

    /**
     * Windows PowerShell 5.1 membaca skrip tanpa BOM sebagai ANSI (cp1252). Byte
     * UTF-8 tanda pisah (—) berakhir 0x94 = kutip ganda tipografis, sehingga
     * "… — …" terputus dan deploy.ps1 gagal di-parse sebelum baris pertama jalan.
     */
    public function testWindowsScriptsAreAsciiOnly(): void
    {
        $files = [...glob(self::DEPLOY . 'windows/*.ps1'), ...glob(self::DEPLOY . 'windows/*.psd1')];

        $this->assertCount(4, $files);

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression('/[^\x00-\x7F]/', (string) file_get_contents($file), basename($file));
        }
    }

    /**
     * The one Nginx site file of the server (Windows behind a Cloudflare Tunnel).
     * Each rule here was once lost or wrong on a real server: the tunnel site
     * written by hand had no cache rules and no HSTS header (production check
     * of 21 November 2026), and a file that listens on every address lets other
     * machines claim HTTPS and any visitor address.
     */
    public function testSiteFileKeepsItsRules(): void
    {
        $lines = $this->nginxLines(self::DEPLOY . 'windows/nginx/gelita.id.conf');

        foreach ([
            'listen 127.0.0.1:80;',
            'server_name gelita.id;',
            'set_real_ip_from 127.0.0.1;',
            'real_ip_header CF-Connecting-IP;',
            'fastcgi_param HTTPS on;',
            'root "C:/laragon/www/gelita/public";',
            'add_header X-Content-Type-Options "nosniff" always;',
            'add_header X-Frame-Options "SAMEORIGIN" always;',
            'add_header Referrer-Policy "same-origin" always;',
            'add_header Strict-Transport-Security "max-age=31536000" always;',
            'add_header Cache-Control $gelita_cache;',
            'map $uri $gelita_is_asset {',
            'map "$gelita_is_asset:$arg_v" $gelita_cache {',
            '~^1:.   "public, max-age=31536000, immutable";',
            '~^2:    "public, max-age=31536000, immutable";',
            '"1:"    "no-cache";',
            'if (-f "C:/laragon/www/gelita/writable/pemeliharaan.flag") {',
            'location = /index.php {',
            'fastcgi_pass php_upstream;',
            'fastcgi_read_timeout 300;',
            'client_max_body_size 72M;',
        ] as $line) {
            $this->assertContains($line, $lines, $line);
        }

        // add_header inside a location drops every header of the server block
        $this->assertCount(5, array_filter($lines, static fn (string $line): bool => str_starts_with($line, 'add_header ')));
        // Only the front controller runs as PHP; hidden files are never served
        $source = implode("\n", $lines);
        $this->assertStringContainsString("location ~ \\.php\$ {\nreturn 404;\n}", $source);
        $this->assertStringContainsString("location ~ /\\. {\nreturn 404;\n}", $source);
        // Cloudflare ends HTTPS: no certificate and no public port on this machine
        $this->assertSame([], array_values(array_filter($lines, static fn (string $line): bool => preg_match('/^(ssl_|http2|listen (?!127\.0\.0\.1:80;))/', $line) === 1)));
    }

    /**
     * One domain only (owner, 21 November 2026): the files are written for the
     * GELITA server itself, not as a template with a placeholder to replace.
     */
    public function testOneServerOneSiteFile(): void
    {
        // The Linux path and the two certificate files were removed on 21 November 2026
        $this->assertDirectoryDoesNotExist(self::DEPLOY . 'linux');
        $this->assertSame(['gelita.id.conf'], array_map('basename', glob(self::DEPLOY . 'windows/nginx/*')));

        $source = (string) file_get_contents(self::DEPLOY . 'windows/nginx/gelita.id.conf');

        $this->assertSame(1, preg_match_all('/^\s*server_name\s+(\S+);/m', $source, $names));
        $this->assertSame('gelita.id', $names[1][0]);
    }

    public function testScriptsAvoidCommandsThatBreakProduction(): void
    {
        $scripts = glob(self::DEPLOY . 'windows/*.ps1');

        $this->assertCount(3, $scripts);

        foreach ($scripts as $file) {
            $source = (string) file_get_contents($file);
            $name   = basename($file);

            // tidak ada di CodeIgniter 4.7 / membekukan .env / menyimpangkan composer.lock
            $this->assertDoesNotMatchRegularExpression('/spark\s+(down|up|optimize|session:migration)\b/', $source, $name);
            $this->assertStringNotContainsString('composer update', $source, $name);
            // CodeIgniter tidak membaca DB_USER; migration lewat database_default_DSN
            $this->assertStringNotContainsString('DB_USER', $source, $name);
        }

        $this->assertStringContainsString('database_default_DSN', (string) file_get_contents(self::DEPLOY . 'windows/deploy.ps1'));
    }

    /**
     * Copy-Item mewarisi stempel waktu .env. Tanpa penyegaran, salinan .env yang
     * tidak berubah lebih dari 30 hari langsung terhapus retensi backup yang sama.
     */
    public function testWindowsBackupRefreshesEnvCopyTimestamp(): void
    {
        $source = (string) file_get_contents(self::DEPLOY . 'windows/backup.ps1');

        $this->assertMatchesRegularExpression('/Copy-Item[^\n]+\.env[^\n]*\$salinanEnv\s*\n\(Get-Item \$salinanEnv\)\.LastWriteTime = Get-Date/', $source);
    }

    public function testGitignoreCoversDeployRuntimeFiles(): void
    {
        $lines = file(ROOTPATH . '.gitignore', FILE_IGNORE_NEW_LINES);

        foreach (['/writable/pemeliharaan.flag', '/writable/backup-status.json', '/public/assets/uploads/*', '!/public/assets/uploads/.gitkeep'] as $rule) {
            $this->assertContains($rule, $lines, $rule);
        }
    }

    /**
     * The file's lines without comments and indentation.
     *
     * @return list<string>
     */
    private function nginxLines(string $file): array
    {
        $lines = [];

        foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
            $line = trim((string) preg_replace('/\s+#.*$|^\s*#.*$/', '', $line));

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines;
    }
}
