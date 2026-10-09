<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Daftar siap penelitian (Dok 3 §8.3, halaman Penelitian dan peringatan
 * Ringkasan): tiga syarat sebelum pengambilan data dimulai.
 *
 * 1. 0 gambar contoh (mockup) aktif — `media_assets.is_placeholder = 1`.
 * 2. Semua rekaman narasi disetujui di setiap bahasa.
 * 3. `gelita:content:verify --siap-penelitian` lolos (0 galat).
 */
final class ResearchReadiness
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * @return array{ready: bool, items: list<array{key: string, label: string, ok: bool, detail: string, link: string}>,
     *               mockups: int, narration_pending: int, errors: int, warnings: int}
     */
    public function check(): array
    {
        $mockups = $this->db->table('media_assets')
            ->where('is_placeholder', 1)
            ->where('is_active', 1)
            ->countAllResults();

        $catalog  = new NarrationCatalog($this->db);
        $progress = $catalog->progress($catalog->rows());
        $pending  = 0;
        $parts    = [];

        foreach ($progress as $locale => $count) {
            $pending += $count['total'] - $count['approved'];
            $parts[] = admin_label('locale', $locale) . ' ' . $count['approved'] . '/' . $count['total'];
        }

        $findings = (new ContentVerifier())->run(true);
        $errors   = count(array_filter($findings, static fn (array $f): bool => $f['level'] === 'error'));
        $warnings = count($findings) - $errors;

        $items = [
            [
                'key'    => 'mockup',
                'label'  => 'Tidak ada gambar contoh',
                'ok'     => $mockups === 0,
                'detail' => $mockups === 0 ? 'Semua slot memakai gambar asli.' : $mockups . ' gambar contoh masih aktif.',
                'link'   => 'admin/media?status=mockup',
            ],
            [
                'key'    => 'narration',
                'label'  => 'Rekaman narasi disetujui',
                'ok'     => $pending === 0,
                'detail' => 'Disetujui: ' . implode(' · ', $parts) . '.',
                'link'   => 'admin/konten/narasi',
            ],
            [
                'key'    => 'verify',
                'label'  => 'Verifikasi konten siap penelitian lolos',
                'ok'     => $errors === 0,
                'detail' => $errors === 0
                    ? 'Tidak ada galat' . ($warnings > 0 ? ' (' . $warnings . ' peringatan).' : '.')
                    : $errors . ' galat, ' . $warnings . ' peringatan.',
                'link'   => 'admin/konten',
            ],
        ];

        return [
            'ready'             => $mockups === 0 && $pending === 0 && $errors === 0,
            'items'             => $items,
            'mockups'           => $mockups,
            'narration_pending' => $pending,
            'errors'            => $errors,
            'warnings'          => $warnings,
        ];
    }
}
