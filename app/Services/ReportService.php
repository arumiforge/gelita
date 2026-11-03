<?php

namespace App\Services;

use App\Models\ResearchStudyModel;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Laporan PDF satu siswa: tabel formal data mentah (Dok 3 §10). Laporan olahan
 * (skor per wilayah, indikator, pretest–posttest) dihapus pada perombakan
 * kelas 5 (Dok 1 §14); peneliti mengolah data dari ekspor Excel.
 *
 * Data diambil lewat AnalyticsService yang terikat cakupan sekolah dan mode
 * anonim pemohon, dirender dari view `pdf/*` (setiap nilai lewat esc()), lalu
 * dicetak mPDF.
 */
class ReportService
{
    /**
     * Menulis PDF ke $path.
     *
     * @param array<string, mixed> $context hasil ExportService::context()
     *
     * @return array{sha: string, rows: int} rows = jumlah peserta tercakup
     */
    public function render(string $path, array $context): array
    {
        if (($context['template'] ?? null) !== 'participant') {
            throw new \DomainException('Ringkasan PDF penelitian sudah tidak tersedia. Unduh data mentah sebagai Excel, atau buat laporan satu siswa dari halaman profil siswa.');
        }

        $data = $this->participantData($context);
        $html = view('pdf/report-participant', $data, ['saveData' => false]);

        $this->writePdf($html, $path, $data['title']);

        $sha = hash_file('sha256', $path);

        if ($sha === false) {
            throw new \RuntimeException('SHA-256 laporan tidak dapat dihitung.');
        }

        return ['sha' => $sha, 'rows' => (int) $data['rows']];
    }

    /**
     * Snapshot laporan satu peserta (coded for `ketua`, the only role that
     * downloads).
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function participantData(array $context): array
    {
        $participantId = (int) ($context['participant_id'] ?? 0);

        if ($participantId <= 0) {
            throw new \DomainException('Laporan siswa memerlukan kode siswa. Buat laporan dari halaman profil siswa.');
        }

        $analytics = new AnalyticsService(null, (bool) $context['anonymized']);

        try {
            $profile = $analytics->participantProfile($participantId);
        } catch (\RuntimeException $e) {
            throw new \DomainException($e->getMessage(), 0, $e);
        }

        return [
            'title'   => 'Laporan Siswa GELITA',
            'meta'    => $this->meta($context),
            'profile' => $profile,
            'rows'    => 1,
        ];
    }

    /**
     * Keterangan sampul: studi, rentang, pemohon, waktu cetak, cakupan.
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function meta(array $context): array
    {
        $filters = $context['filters'];
        $study   = isset($filters['study_id'])
            ? model(ResearchStudyModel::class)->find((int) $filters['study_id'])
            : model(ResearchStudyModel::class)->activeStudy();

        $school = null;

        if (isset($filters['school_id'])) {
            $row    = db_connect()->table('schools')->select('name')->where('id', (int) $filters['school_id'])->get()->getRowArray();
            $school = $row['name'] ?? null;
        }

        $level = null;

        if (isset($filters['level_id'])) {
            $level = service('contentRepository')->levelById((int) $filters['level_id'])?->name_id;
        }

        return [
            'export_id'    => $context['export_id'],
            'study'        => $study === null ? 'Semua penelitian' : ($study['name'] . ' (' . $study['code'] . ')'),
            'date_from'    => $filters['date_from'] ?? null,
            'date_to'      => $filters['date_to'] ?? null,
            'school'       => $school,
            'level'        => $level,
            'class_level'  => $filters['class_level'] ?? null,
            'locale'       => $filters['locale'] ?? null,
            'requester'    => $context['requester'],
            'anonymized'   => (bool) $context['anonymized'],
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /** mPDF: UTF-8, A4, margin 20 mm, header/footer bernomor halaman. */
    private function writePdf(string $html, string $path, string $title): void
    {
        $tempDir = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'mpdf';

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 20,
            'margin_right'  => 20,
            'margin_top'    => 24,
            'margin_bottom' => 20,
            'margin_header' => 10,
            'margin_footer' => 10,
            'tempDir'       => $tempDir,
            'default_font'  => 'dejavusans',
        ]);

        // HTML sudah di-escape di view; mPDF tidak boleh mengambil sumber luar.
        $mpdf->showImageErrors = false;
        $mpdf->SetTitle($title);
        $mpdf->SetAuthor('GELITA');
        $mpdf->SetCreator('GELITA');
        $mpdf->SetHTMLHeader('<div class="pdf-header">GELITA · ' . esc($title) . '</div>');
        $mpdf->SetHTMLFooter(
            '<table class="pdf-footer"><tr><td>Dicetak ' . esc(date('d-m-Y H:i')) . '</td>'
            . '<td class="right">Halaman {PAGENO} dari {nbpg}</td></tr></table>',
        );
        $mpdf->WriteHTML($html);
        $mpdf->Output($path, Destination::FILE);
    }
}
