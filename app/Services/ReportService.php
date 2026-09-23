<?php

namespace App\Services;

use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Laporan PDF (FITUR 15): ringkasan studi dan laporan satu peserta.
 *
 * Data diambil lewat AnalyticsService yang terikat cakupan sekolah dan mode
 * anonim pemohon, dirender dari view `pdf/*` (setiap nilai lewat esc()), lalu
 * dicetak mPDF. Chart berupa bar horizontal dari tabel + blok berwarna yang
 * dibuat server; tidak ada screenshot browser dan tidak ada raw event.
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
        $data = $context['template'] === 'participant'
            ? $this->participantData($context)
            : $this->studyData($context);

        $view = $context['template'] === 'participant' ? 'pdf/report-participant' : 'pdf/report-study';
        $html = view($view, $data, ['saveData' => false]);

        $this->writePdf($html, $path, $data['title']);

        $sha = hash_file('sha256', $path);

        if ($sha === false) {
            throw new \RuntimeException('SHA-256 laporan tidak dapat dihitung.');
        }

        return ['sha' => $sha, 'rows' => (int) $data['rows']];
    }

    /**
     * Snapshot laporan studi.
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function studyData(array $context): array
    {
        $filters   = $context['filters'];
        $analytics = new AnalyticsService($context['school_scope'], true);
        $summary   = $analytics->summary($filters);

        return [
            'title'      => 'Laporan Studi GELITA',
            'meta'       => $this->meta($context),
            'summary'    => $summary,
            'cohort'     => model(ParticipantModel::class)->cohortSummary($filters),
            'levels'     => $analytics->levelBreakdown($filters),
            'nodes'      => $analytics->nodeDifficulty($filters),
            'indicators' => $analytics->indicatorMastery($filters),
            'security'   => $analytics->digitalSecurityLiteracy($filters),
            'prePost'    => $analytics->prePostComparison($filters),
            'rows'       => $summary['participant_count'],
        ];
    }

    /**
     * Snapshot laporan satu peserta. Guru hanya untuk peserta di sekolahnya;
     * AnalyticsService melempar exception bila di luar cakupan.
     *
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function participantData(array $context): array
    {
        $participantId = (int) ($context['participant_id'] ?? 0);

        if ($participantId <= 0) {
            throw new \DomainException('Laporan peserta memerlukan participant_id.');
        }

        $analytics = new AnalyticsService($context['school_scope'], (bool) $context['anonymized']);

        try {
            $profile = $analytics->participantProfile($participantId);
        } catch (\RuntimeException $e) {
            throw new \DomainException($e->getMessage(), 0, $e);
        }

        $levels = [];

        foreach (service('contentRepository')->levels() as $level) {
            $levels[$level->id] = ['code' => (string) $level->code, 'name' => (string) $level->name_id];
        }

        $node = $profile['hardest_node'] === null
            ? null
            : service('contentRepository')->node((int) $profile['hardest_node']['node_id']);

        return [
            'title'       => 'Laporan Peserta GELITA',
            'meta'        => $this->meta($context),
            'profile'     => $profile,
            'levels'      => $levels,
            'hardestNode' => $node === null ? null : [
                'title'               => (string) $node->title_id,
                'level'               => $levels[$node->level_id]['name'] ?? '',
                'first_pass_accuracy' => $profile['hardest_node']['first_pass_accuracy'],
            ],
            'rows' => 1,
        ];
    }

    /**
     * Keterangan sampul: studi, fase, rentang, pemohon, waktu cetak, cakupan.
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
            'study'        => $study === null ? 'Semua studi' : ($study['name'] . ' (' . $study['code'] . ')'),
            'phase'        => $filters['phase_code'] ?? 'semua fase',
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
