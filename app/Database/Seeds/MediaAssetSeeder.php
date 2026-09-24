<?php

namespace App\Database\Seeds;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Mendaftarkan aset bawaan di public/assets/{ui,char,bg,map,challenge,library,reward,audio}.
 *
 * 1. Slot wajib (SLOTS + level + frame karakter) selalu didaftarkan dengan asset_key resmi.
 *    Bila berkasnya belum ada → is_active = 0 sehingga media_src() memakai placeholder.
 *    Ekstensi slot bebas (png/jpg/webp/…): berkas dicocokkan berdasarkan path tanpa ekstensi.
 * 2. Berkas lain hasil pindai didaftarkan dengan asset_key dari path:
 *    assets/challenge/tmg-1/topi.png → challenge.tmg-1.topi
 *    assets/bg/bg-dieng.jpg          → bg.dieng   (prefix nama folder dibuang)
 * 3. Dijalankan ulang: metadata berkas diperbarui; berkas yang hilang → is_active = 0.
 * 4. levels.map/background/badge_media_id yang masih NULL ditautkan ke slot level.
 *
 * audio_assets TIDAK dibuat di sini (wajib transcript + persetujuan admin).
 */
class MediaAssetSeeder extends GelitaSeeder
{
    private const FOLDERS = ['ui', 'char', 'bg', 'map', 'challenge', 'library', 'reward', 'audio'];

    private const MIME = [
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'mp3'  => 'audio/mpeg',
        'ogg'  => 'audio/ogg',
        'wav'  => 'audio/wav',
        'm4a'  => 'audio/mp4',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];

    /** [asset_key, path tanpa ekstensi, ekstensi default] */
    private const SLOTS = [
        ['ui.logo', 'assets/ui/logo-gelita', 'png'],
        // Halaman awal: logo landscape dan tombol Mulai bergambar. Tombol
        // memuat tulisan, jadi punya varian bahasa (akhiran -en → locale en).
        ['ui.logo-hero', 'assets/ui/logo-hero', 'png'],
        ['ui.btn-start', 'assets/ui/btn-start', 'png'],
        ['ui.btn-start.en', 'assets/ui/btn-start-en', 'png'],
        ['ui.placeholder', 'assets/ui/placeholder', 'svg'],
        ['map.kedu', 'assets/map/map-kedu', 'png'],
        // Latar layar umum yang dibaca view lewat media_key_src()
        ['bg.welcome', 'assets/bg/bg-welcome', 'jpg'],
        ['bg.auth', 'assets/bg/bg-auth', 'jpg'],
        ['bg.intro', 'assets/bg/bg-intro', 'jpg'],
        ['bg.map', 'assets/bg/bg-map', 'jpg'],
        ['bg.reflection', 'assets/bg/bg-reflection', 'jpg'],
    ];

    /** Dipakai bila Config\Gelita belum ada; isinya sama dengan $characterAnimations */
    private const DEFAULT_ANIMATIONS = [
        'jaka.idle'  => ['frames' => 3],
        'jaka.bow'   => ['frames' => 3],
        'jaka.happy' => ['frames' => 3],
        'kedu.idle'  => ['frames' => 3],
    ];

    /** @var array<string,string> asset_key => storage_path yang sudah dipakai run ini */
    private array $usedKeys = [];

    /** @var array<string,true> storage_path yang sudah diproses run ini */
    private array $seenPaths = [];

    private int $registered = 0;

    private int $missing = 0;

    public function run(): void
    {
        $files = $this->scanFiles();                 // path tanpa ekstensi (lowercase) => path lengkap
        $slots = $this->slots();

        foreach ($slots as [$key, $basePath, $defaultExt]) {
            $found = $files[strtolower($basePath)] ?? null;

            if ($found !== null) {
                unset($files[strtolower($basePath)]);
                $this->register($key, $found, true);
            } else {
                $this->register($key, $basePath . '.' . $defaultExt, false);
            }
        }

        foreach ($files as $path) {
            $this->register($this->keyFromPath($path), $path, true);
        }

        $this->deactivateVanished();
        $this->linkLevels();

        $this->info(sprintf('media_assets: %d terdaftar, %d slot belum ada berkasnya (is_active = 0).', $this->registered, $this->missing));
    }

    /** @return list<array{0:string,1:string,2:string}> */
    private function slots(): array
    {
        $slots = self::SLOTS;

        foreach (['temanggung', 'magelang', 'wonosobo'] as $code) {
            $slots[] = ["map.region.{$code}", "assets/map/map-{$code}", 'png'];
            $slots[] = ["bg.{$code}.region", "assets/bg/bg-{$code}", 'jpg'];
            $slots[] = ["reward.badge.{$code}", "assets/reward/badge-{$code}", 'png'];
        }

        $animations = self::DEFAULT_ANIMATIONS;
        if (class_exists(\Config\Gelita::class)) {
            $animations = config('Gelita')->characterAnimations ?? $animations;
        }

        foreach ($animations as $name => $animation) {
            $slug = str_replace('.', '-', $name);

            for ($i = 1; $i <= (int) $animation['frames']; $i++) {
                $slots[] = ["char.{$name}.{$i}", "assets/char/{$slug}-{$i}", 'png'];
            }
        }

        return $slots;
    }

    /** @return array<string,string> */
    private function scanFiles(): array
    {
        $files = [];

        foreach (self::FOLDERS as $folder) {
            $dir = FCPATH . 'assets' . DIRECTORY_SEPARATOR . $folder;

            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                $ext = strtolower($file->getExtension());

                if (! $file->isFile() || str_starts_with($file->getFilename(), '.') || ! isset(self::MIME[$ext])) {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(FCPATH)));
                $base     = strtolower(substr($relative, 0, -(strlen($ext) + 1)));

                $files[$base] ??= $relative;
            }
        }

        ksort($files);

        return $files;
    }

    private function keyFromPath(string $path): string
    {
        $withoutExt = preg_replace('/\.[^.\/]+$/', '', substr($path, strlen('assets/')));
        $parts      = explode('/', strtolower($withoutExt));
        $folder     = $parts[0];
        $last       = count($parts) - 1;

        if ($last > 0 && str_starts_with($parts[$last], $folder . '-')) {
            $parts[$last] = substr($parts[$last], strlen($folder) + 1);
        }

        $parts = array_map(static fn ($p) => trim(preg_replace('/[^a-z0-9-]+/', '-', $p), '-'), $parts);
        $key   = implode('.', array_filter($parts, static fn ($p) => $p !== ''));

        // hindari tabrakan (mis. bg/bg-x.jpg dan bg/x.png)
        if (isset($this->usedKeys[$key]) && $this->usedKeys[$key] !== $path) {
            $key .= '.' . strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }

        return substr($key, 0, 160);
    }

    private function register(string $key, string $path, bool $exists): void
    {
        $this->usedKeys[$key]   = $path;
        $this->seenPaths[$path] = true;

        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $data = [
            'asset_type'   => $this->assetType($path, $ext),
            'storage_path' => $path,
            'mime_type'    => self::MIME[$ext] ?? 'application/octet-stream',
            'locale'       => $this->localeFromPath($path),
            'is_active'    => $exists ? 1 : 0,
        ];

        if ($exists) {
            $absolute = FCPATH . $path;
            $data['mime_type'] = $this->detectMime($absolute, $data['mime_type']);
            $data['file_size'] = filesize($absolute) ?: null;
            $data['sha256']    = hash_file('sha256', $absolute) ?: null;

            [$data['width_px'], $data['height_px']] = $this->dimensions($absolute, $ext);
            $this->registered++;
        } else {
            $this->missing++;
        }

        $table = $this->db->table('media_assets');
        $id    = $this->findId('media_assets', ['asset_key' => $key]);

        if ($id === null) {
            // storage_path yang sudah dipakai kunci lain (mis. hasil unggah admin) tidak didaftarkan dua kali
            if ($this->findId('media_assets', ['storage_path' => $path]) !== null) {
                return;
            }

            $table->insert(['asset_key' => $key, 'version' => '1'] + $data);

            return;
        }

        $current = $table->select('storage_path')->where('id', $id)->get()->getRowArray();

        // baris sudah menunjuk berkas lain yang masih ada (mis. hasil unggah admin) → biarkan
        if ($current['storage_path'] !== $path && is_file(FCPATH . $current['storage_path'])) {
            return;
        }

        if (! $exists) {
            // berkas belum/tidak ada: metadata lama dipertahankan, cukup dinonaktifkan
            $data = ['is_active' => 0];
        }

        $this->db->table('media_assets')->where('id', $id)->update($data);
    }

    /** Baris aset bawaan yang berkasnya sudah tidak ada → is_active = 0 */
    private function deactivateVanished(): void
    {
        $builder = $this->db->table('media_assets')->select('id, storage_path')->where('is_active', 1)->groupStart();

        foreach (self::FOLDERS as $folder) {
            $builder->orLike('storage_path', 'assets/' . $folder . '/', 'after');
        }

        foreach ($builder->groupEnd()->get()->getResultArray() as $row) {
            if (! isset($this->seenPaths[$row['storage_path']]) && ! is_file(FCPATH . $row['storage_path'])) {
                $this->db->table('media_assets')->where('id', $row['id'])->update(['is_active' => 0]);
            }
        }
    }

    private function linkLevels(): void
    {
        foreach ($this->levelIds() as $code => $levelId) {
            $links = [
                'map_media_id'        => "map.region.{$code}",
                'background_media_id' => "bg.{$code}.region",
                'badge_media_id'      => "reward.badge.{$code}",
            ];

            foreach ($links as $column => $key) {
                $mediaId = $this->findId('media_assets', ['asset_key' => $key]);

                if ($mediaId !== null) {
                    $this->db->table('levels')
                        ->where('id', $levelId)
                        ->where($column, null)
                        ->update([$column => $mediaId]);
                }
            }
        }
    }

    private function assetType(string $path, string $ext): string
    {
        $mime = self::MIME[$ext] ?? '';

        return match (true) {
            str_starts_with($mime, 'audio/')          => 'audio',
            str_starts_with($mime, 'video/')          => 'video',
            str_starts_with($path, 'assets/char/')    => 'sprite_frame',
            default                                   => 'image',
        };
    }

    /** nama berkas berakhiran -en / _en / .en → locale en; -id → id; selain itu NULL */
    private function localeFromPath(string $path): ?string
    {
        $stem = strtolower(pathinfo($path, PATHINFO_FILENAME));

        return preg_match('/[-_.](id|en)$/', $stem, $m) ? $m[1] : null;
    }

    private function detectMime(string $absolute, string $fallback): string
    {
        if (! function_exists('finfo_open')) {
            return $fallback;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = $finfo ? finfo_file($finfo, $absolute) : false;

        // finfo sering mengembalikan text/plain / text/xml untuk SVG
        if ($mime === false || $mime === 'application/octet-stream' || str_starts_with($mime, 'text/')) {
            return $fallback;
        }

        return $mime;
    }

    /** @return array{0:int|null,1:int|null} */
    private function dimensions(string $absolute, string $ext): array
    {
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
            $size = @getimagesize($absolute);

            return $size ? [$size[0], $size[1]] : [null, null];
        }

        if ($ext === 'svg') {
            $svg = @file_get_contents($absolute, false, null, 0, 4096) ?: '';

            if (preg_match('/<svg[^>]*\bwidth="(\d+)(?:px)?"[^>]*\bheight="(\d+)(?:px)?"/i', $svg, $m)) {
                return [(int) $m[1], (int) $m[2]];
            }

            if (preg_match('/viewBox="[\d.\-]+\s+[\d.\-]+\s+([\d.]+)\s+([\d.]+)"/i', $svg, $m)) {
                return [(int) round((float) $m[1]), (int) round((float) $m[2])];
            }
        }

        return [null, null];
    }
}
