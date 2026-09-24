<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * GELITA sebagai aplikasi terpasang (layar utama).
 *
 * - Manifest mengunci posisi mendatar dan layar penuh; URL-nya relatif agar
 *   pemasangan di subdirektori tetap benar; ikon wajib ada dengan ukuran yang
 *   dijanjikan (Chrome menolak memasang bila ikon 192/512 tidak cocok).
 * - Service worker hanya boleh menangani navigasi GET dan hanya menyimpan
 *   halaman offline: respons API, event, atau formulir tidak boleh pernah
 *   tersimpan di cache perangkat kelas yang dipakai bergantian.
 *
 * @internal
 */
final class InstallableAppTest extends CIUnitTestCase
{
    /** @return array<string, mixed> */
    private function manifest(): array
    {
        $manifest = json_decode((string) file_get_contents(FCPATH . 'manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($manifest);

        return $manifest;
    }

    public function testManifestLocksLandscapeFullscreen(): void
    {
        $manifest = $this->manifest();

        $this->assertSame('landscape', $manifest['orientation']);
        $this->assertSame('fullscreen', $manifest['display']);
        $this->assertSame(['fullscreen', 'standalone'], $manifest['display_override']);
        $this->assertSame('GELITA', $manifest['short_name']);

        foreach (['id', 'start_url', 'scope'] as $key) {
            $this->assertSame('./', $manifest[$key], "{$key} relatif terhadap manifest");
        }
    }

    public function testManifestIconsExistWithDeclaredSizes(): void
    {
        $purposes = [];

        foreach ($this->manifest()['icons'] as $icon) {
            $this->assertStringStartsNotWith('/', $icon['src'], 'ikon relatif terhadap manifest');
            $path = FCPATH . $icon['src'];
            $this->assertFileExists($path);

            [$width, $height, $type] = getimagesize($path);
            $this->assertSame($icon['sizes'], "{$width}x{$height}", $icon['src']);
            $this->assertSame(IMAGETYPE_PNG, $type, $icon['src']);

            $purposes[$icon['purpose']][] = $icon['sizes'];
        }

        $this->assertContains('192x192', $purposes['any']);
        $this->assertContains('512x512', $purposes['any']);
        $this->assertContains('512x512', $purposes['maskable']);

        [$width, $height] = getimagesize(FCPATH . 'assets/app/apple-touch-icon.png');
        $this->assertSame([180, 180], [$width, $height]);
    }

    public function testOnlyGameLayoutIsInstallable(): void
    {
        $game = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');
        $this->assertStringContainsString("<link rel=\"manifest\" href=\"<?= base_url('manifest.json') ?>\">", $game);
        $this->assertStringContainsString("base_url('assets/app/apple-touch-icon.png')", $game);

        foreach (['admin', 'auth'] as $layout) {
            $this->assertStringNotContainsString('rel="manifest"', (string) file_get_contents(APPPATH . "Views/layouts/{$layout}.php"));
        }
    }

    public function testServiceWorkerOnlyCachesTheOfflinePage(): void
    {
        $sw = (string) file_get_contents(FCPATH . 'sw.js');

        $this->assertStringContainsString("if (request.mode !== 'navigate' || request.method !== 'GET') return;", $sw);
        $this->assertSame(1, substr_count($sw, 'cache.add('), 'hanya offline.html yang disimpan');
        $this->assertStringContainsString("new URL('offline.html', self.registration.scope)", $sw);
        $this->assertStringNotContainsString('cache.put(', $sw);
        $this->assertStringNotContainsString('cache.addAll(', $sw);
        $this->assertFileExists(FCPATH . 'offline.html');
    }

    public function testOfflinePageIsSelfContained(): void
    {
        $html = (string) file_get_contents(FCPATH . 'offline.html');

        // Saat offline tidak ada berkas lain yang dapat diambil
        $this->assertDoesNotMatchRegularExpression('/\s(?:src|href)="(?!#)/', $html);
        $this->assertStringContainsString('lang="en"', $html, 'teks dwibahasa');
    }
}
