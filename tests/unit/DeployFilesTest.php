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

    public function testShellScriptsUseLfAndStrictMode(): void
    {
        foreach (['deploy.sh' => 'set -Eeuo pipefail', 'backup.sh' => 'set -euo pipefail'] as $name => $strict) {
            $source = (string) file_get_contents(self::DEPLOY . 'linux/' . $name);

            $this->assertStringStartsWith("#!/usr/bin/env bash\n", $source, $name);
            $this->assertStringNotContainsString("\r", $source, "{$name} tanpa CRLF");
            // set -E: trap ERR juga terpicu oleh galat di dalam fungsi spark()
            $this->assertStringContainsString("\n{$strict}\n", $source, $name);
        }
    }

    /** Konfigurasi Nginx kedua jalur hanya boleh berbeda di baris khusus platform. */
    public function testNginxConfigsMatchAcrossPlatforms(): void
    {
        foreach (['gelita-http.conf', 'gelita-https.conf'] as $name) {
            $this->assertSame(
                $this->portableNginx(self::DEPLOY . 'linux/nginx/' . $name),
                $this->portableNginx(self::DEPLOY . 'windows/nginx/' . $name),
                $name,
            );
        }
    }

    public function testScriptsAvoidCommandsThatBreakProduction(): void
    {
        foreach ([...glob(self::DEPLOY . 'linux/*.sh'), ...glob(self::DEPLOY . 'windows/*.ps1')] as $file) {
            $source = (string) file_get_contents($file);
            $name   = basename($file);

            // tidak ada di CodeIgniter 4.7 / membekukan .env / menyimpangkan composer.lock
            $this->assertDoesNotMatchRegularExpression('/spark\s+(down|up|optimize|session:migration)\b/', $source, $name);
            $this->assertStringNotContainsString('composer update', $source, $name);
            // CodeIgniter tidak membaca DB_USER; migration lewat database_default_DSN
            $this->assertStringNotContainsString('DB_USER', $source, $name);
        }

        foreach (['linux/deploy.sh', 'windows/deploy.ps1'] as $script) {
            $this->assertStringContainsString('database_default_DSN', (string) file_get_contents(self::DEPLOY . $script), $script);
        }
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

        foreach (['/writable/pemeliharaan.flag', '/public/assets/uploads/*', '!/public/assets/uploads/.gitkeep'] as $rule) {
            $this->assertContains($rule, $lines, $rule);
        }
    }

    /** Baris konfigurasi tanpa komentar, dengan baris khusus platform dibuang. */
    private function portableNginx(string $file): array
    {
        $platform = '/^(listen|http2|root|ssl_certificate|ssl_certificate_key|fastcgi_pass|if \(-f)\b/';
        $lines    = [];

        foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
            $line = trim(preg_replace('/\s+#.*$|^\s*#.*$/', '', $line));

            if ($line !== '' && ! preg_match($platform, $line)) {
                $lines[] = $line;
            }
        }

        return $lines;
    }
}
