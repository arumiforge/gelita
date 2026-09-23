<?php

namespace App\Libraries;

use App\Models\MediaAssetModel;

/**
 * Pemeriksaan integritas media_assets ↔ berkas di public/.
 *
 * Dipakai tombol "Pindai" di `/admin/media` dan `php spark gelita:media:scan`.
 * Hanya melapor; tidak mengubah database.
 */
class MediaIntegrity
{
    /**
     * Aset aktif yang berkasnya hilang atau berubah, plus berkas di
     * `public/assets/uploads/` yang tidak dirujuk baris mana pun.
     *
     * @return list<array{asset_key: string, issue: string}>
     */
    public function check(): array
    {
        $findings   = [];
        $registered = [];

        foreach (model(MediaAssetModel::class)->findAll() as $asset) {
            $relative = ltrim((string) $asset['storage_path'], '/');
            $path     = FCPATH . $relative;

            $registered[$relative] = true;

            if (! (int) $asset['is_active']) {
                continue;   // slot yang memang belum diunggah; media_src() memakai pengganti
            }

            if (! is_file($path)) {
                $findings[] = ['asset_key' => (string) $asset['asset_key'], 'issue' => 'berkas tidak ada'];

                continue;
            }

            if ($asset['sha256'] !== null && hash_file('sha256', $path) !== $asset['sha256']) {
                $findings[] = ['asset_key' => (string) $asset['asset_key'], 'issue' => 'sha256 tidak cocok'];

                continue;
            }

            if ($asset['file_size'] !== null && (int) filesize($path) !== (int) $asset['file_size']) {
                $findings[] = ['asset_key' => (string) $asset['asset_key'], 'issue' => 'ukuran berkas berubah'];
            }
        }

        foreach ($this->uploadedFiles() as $relative) {
            if (! isset($registered[$relative])) {
                $findings[] = ['asset_key' => $relative, 'issue' => 'berkas unggahan tanpa baris media_assets'];
            }
        }

        return $findings;
    }

    /** @return list<string> path relatif terhadap public/ */
    private function uploadedFiles(): array
    {
        $dir = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads';

        if (! is_dir($dir)) {
            return [];
        }

        $out = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $name = $file->getFilename();

            if (! $file->isFile() || str_starts_with($name, '.') || in_array($name, ['index.html', '.gitkeep'], true)) {
                continue;
            }

            $out[] = str_replace('\\', '/', substr($file->getPathname(), strlen(FCPATH)));
        }

        sort($out);

        return $out;
    }
}
