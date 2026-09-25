<?php

namespace App\Controllers\Admin;

use App\Libraries\NarrationCatalog;
use App\Libraries\NarrationImporter;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Narasi naskah cerita di panel: status rekaman 88 baris, unggah banyak
 * rekaman sekaligus, impor dari folder server, persetujuan massal, dan
 * daftar rekaman untuk pengisi suara. Hanya role `admin`.
 *
 * Aturan impor ada di App\Libraries\NarrationImporter (dipakai juga oleh
 * `php spark gelita:narration:import`), status dan persetujuan massal di
 * App\Libraries\NarrationCatalog.
 */
class NarrationController extends BaseAdminController
{
    public function index(): string
    {
        $catalog = new NarrationCatalog();
        $rows    = $catalog->rows();

        return $this->panel('admin/narration/index', 'Narasi', [
            'groups'   => $catalog->groups($rows),
            'progress' => $catalog->progress($rows),
            'drafts'   => array_combine(
                config('Gelita')->locales,
                array_map(static fn (string $locale): int => count($catalog->draftIds($locale)), config('Gelita')->locales),
            ),
            'locales'  => config('Gelita')->locales,
        ]);
    }

    public function uploadForm(): string
    {
        return $this->panel('admin/narration/upload', 'Unggah narasi', [
            'locales' => config('Gelita')->locales,
            'limits'  => self::uploadLimits(),
            'report'  => session('narration_report'),
            'folder'  => (new NarrationImporter())->folderFor('{id|en}'),
        ]);
    }

    public function upload(): RedirectResponse
    {
        $back   = 'admin/konten/narasi/unggah';
        $locale = (string) $this->request->getPost('locale');

        if (! in_array($locale, config('Gelita')->locales, true)) {
            return $this->back($back, 'Pilih bahasa rekaman.');
        }

        $files    = $this->request->getFileMultiple('files') ?? [];
        $incoming = [];

        foreach ($files as $file) {
            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // Hanya berkas unggahan sah (is_uploaded_file) yang diteruskan ke importer
            $incoming[] = $file->isValid()
                ? ['name' => $file->getClientName(), 'path' => $file->getTempName()]
                : ['name' => $file->getClientName(), 'path' => '', 'error' => $file->getErrorString()];
        }

        if ($incoming === []) {
            return $this->back($back, 'Tidak ada berkas yang terkirim. Pilih satu atau beberapa berkas rekaman.');
        }

        $dryRun = (bool) $this->request->getPost('dry_run');
        $report = (new NarrationImporter())->importFiles($incoming, $locale, $dryRun, $this->staffId() ?: null);

        // PHP membuang diam-diam berkas di atas max_file_uploads
        $maxFiles = self::uploadLimits()['max_files'];

        if ($maxFiles > 0 && count($files) >= $maxFiles) {
            $report['truncated'] = $maxFiles;
        }

        $this->audit($report);

        return redirect()->to(site_url($back))
            ->with('narration_report', $report)
            ->with('message', self::summary($report));
    }

    public function importFolder(): RedirectResponse
    {
        $back   = 'admin/konten/narasi/unggah';
        $locale = (string) $this->request->getPost('locale');

        if (! in_array($locale, config('Gelita')->locales, true)) {
            return $this->back($back, 'Pilih bahasa rekaman.');
        }

        $report = (new NarrationImporter())->importFolder($locale, (bool) $this->request->getPost('dry_run'), $this->staffId() ?: null);
        $this->audit($report);

        return redirect()->to(site_url($back))
            ->with('narration_report', $report)
            ->with('message', self::summary($report));
    }

    /** "Setujui semua narasi draft" satu bahasa. */
    public function approveAll(): RedirectResponse
    {
        $locale = (string) $this->request->getPost('locale');
        $back   = (string) $this->request->getPost('back') === 'audio' ? 'admin/media/audio' : 'admin/konten/narasi';

        if (! in_array($locale, config('Gelita')->locales, true)) {
            return $this->back($back, 'Bahasa tidak dikenal.');
        }

        $count = (new NarrationCatalog())->approveDrafts($locale, $this->staffId() ?: null);

        return $this->done($back, $count === 0
            ? 'Tidak ada narasi ' . strtoupper($locale) . ' berstatus draft.'
            : "{$count} narasi " . strtoupper($locale) . ' disetujui dan kini terdengar pemain.');
    }

    /** Daftar rekaman (XLSX) untuk pengisi suara. */
    public function recordingList(): DownloadResponse
    {
        $path = WRITEPATH . 'exports/daftar-rekaman-narasi-' . bin2hex(random_bytes(4)) . '.xlsx';

        (new NarrationCatalog())->writeRecordingList($path);

        model(AuditLogModel::class)->record('narration_list_download', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'dialogues',
            'target_id'     => 'narasi',
        ]);

        // Berkasnya kecil (±88 baris): dikirim dari memori, berkas sementara langsung dihapus
        $bytes = (string) file_get_contents($path);
        @unlink($path);

        return $this->response->download('gelita-daftar-rekaman-' . date('Ymd') . '.xlsx', $bytes);
    }

    /**
     * Batas unggahan yang berlaku: yang terkecil dari Config\Gelita dan PHP.
     *
     * @return array{file_bytes: int, post_bytes: int, max_files: int, gelita_bytes: int, php_file_bytes: int}
     */
    public static function uploadLimits(): array
    {
        $gelita  = config('Gelita')->maxUploadBytes;
        $phpFile = self::iniBytes((string) ini_get('upload_max_filesize'));
        $post    = self::iniBytes((string) ini_get('post_max_size'));

        return [
            'file_bytes'     => $phpFile > 0 ? min($gelita, $phpFile) : $gelita,
            'post_bytes'     => $post,
            'max_files'      => (int) ini_get('max_file_uploads'),
            'gelita_bytes'   => $gelita,
            'php_file_bytes' => $phpFile,
        ];
    }

    /** Nilai ini PHP ("64M", "2G", "512K") → byte; 0 = tanpa batas. */
    public static function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return 0;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g'     => $number * 1024 ** 3,
            'm'     => $number * 1024 ** 2,
            'k'     => $number * 1024,
            default => $number,
        };
    }

    /** @param array<string, mixed> $report */
    public static function summary(array $report): string
    {
        return sprintf(
            '%sNarasi %s: %d baru, %d diganti, %d sama, %d nama tidak dikenal, %d gagal.',
            $report['dry_run'] ? 'Pemeriksaan (tidak disimpan) — ' : '',
            strtoupper((string) $report['locale']),
            count($report['created']),
            count($report['replaced']),
            count($report['unchanged']),
            count($report['unknown']),
            count($report['failed']),
        );
    }

    /** @param array<string, mixed> $report */
    private function audit(array $report): void
    {
        if ($report['dry_run'] || ($report['created'] === [] && $report['replaced'] === [])) {
            return;
        }

        model(AuditLogModel::class)->record('narration_import', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'audio_asset',
            'target_id'     => 'narasi.' . $report['locale'],
            'metadata'      => [
                'via'      => $report['source'],
                'locale'   => $report['locale'],
                'created'  => count($report['created']),
                'replaced' => count($report['replaced']),
                'unknown'  => count($report['unknown']),
                'failed'   => count($report['failed']),
            ],
        ]);
    }
}
