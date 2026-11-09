<?php

namespace App\Services;

use App\Libraries\ExcelWriter;
use App\Libraries\ExportLabels;
use App\Models\AuditLogModel;
use App\Models\ParticipantModel;

/**
 * One child's data copy for the parents (research revision 10, part 4: 30
 * §8, K2, K14). Made on request and sent at once, never kept on the server:
 * one ZIP with a short PDF in plain sentences and an XLSX with the download
 * sheets narrowed to this child. Coded; without answer keys, item texts,
 * internal ids, device details, `ip_hash`, or other children's data.
 */
class ParticipantCopyService
{
    public const PURPOSES = ['view', 'copy'];

    /** Choice for the ang-21–ang-23 answers; `none` by default (K2). */
    public const RISK_CHOICES = ['none', 'child_agreed', 'parent_insisted'];

    /**
     * The download sheets that go into the copy.
     *
     * From the head researcher's sheet list (29 §7.2, RawDataService::DIMENSIONS);
     * `kamus`, `event`, `butir`, `kode_pilihan` (item texts, answer keys) and the
     * field sheets stay out. Part 8 (34 §11.4, 30 §8): the test, questionnaire
     * (ang-01–ang-17), and attitude sheets; never `risiko_gabungan`.
     */
    public const SHEETS = ['peserta', 'sesi', 'progres', 'pengerjaan', 'jawaban', 'percobaan', 'navigasi', 'bantuan', 'selesai', 'masukan',
        // Part 8 (34 §11.4)
        'tes_jawaban', 'tes_bagian', 'angket', 'sikap',
    ];

    /** Columns never in the copy: answer key, item text, device details, internal links. */
    private const DROPPED = [
        'answer_key', 'prompt', 'payload', 'device_type', 'os_name', 'browser_name', 'screen_size', 'is_touch',
        'app_client_version', 'package_code', 'package_mode', 'school_npsn', 'client_hints', 'user_agent',
    ];

    /**
     * Builds the ZIP and returns its bytes and file name; nothing stays on disk.
     *
     * @return array{name: string, body: string}
     */
    public function build(int $participantId, string $purpose, string $riskChoice, int $staffId): array
    {
        $participant = model(ParticipantModel::class)->find($participantId);

        if ($participant === null) {
            throw new \DomainException(lang('Admin.resetCodeNotFound'));
        }

        if (service('studyClosure')->identitiesReplacedFor($participantId)) {
            throw new \DomainException(lang('Admin.closureReplaced'));
        }

        $purpose    = in_array($purpose, self::PURPOSES, true) ? $purpose : 'copy';
        $hasRisk    = service('dataRequests')->hasRiskAnswers($participantId);
        $riskChoice = $hasRisk && in_array($riskChoice, self::RISK_CHOICES, true) ? $riskChoice : 'none';
        $code       = (string) $participant->participant_code;
        $dir        = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'salinan-' . bin2hex(random_bytes(6));
        mkdir($dir, 0700, true);

        try {
            $xlsx = $dir . DIRECTORY_SEPARATOR . 'data.xlsx';
            $pdf  = $dir . DIRECTORY_SEPARATOR . 'ringkasan.pdf';
            $zip  = $dir . DIRECTORY_SEPARATOR . 'salinan.zip';

            $this->writeXlsx($xlsx, $participantId, $staffId, $riskChoice !== 'none');
            service('reportService')->writePdf($this->summaryHtml($participantId, $hasRisk && $riskChoice === 'none'), $pdf, 'Salinan data anak ' . $code);

            $archive = new \ZipArchive();
            $archive->open($zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $archive->addFile($pdf, 'ringkasan-' . $code . '.pdf');
            $archive->addFile($xlsx, 'data-' . $code . '.xlsx');
            $archive->close();

            $body = (string) file_get_contents($zip);
        } finally {
            array_map('unlink', glob($dir . DIRECTORY_SEPARATOR . '*') ?: []);
            rmdir($dir);
        }

        model(AuditLogModel::class)->record('participant_copy', [
            'staff_user_id' => $staffId,
            'target_type'   => 'participant',
            'target_id'     => (string) $participantId,
            'metadata'      => ['participant_code' => $code, 'purpose' => $purpose, 'risk_answers' => $riskChoice],
        ]);

        return ['name' => 'salinan-' . $code . '-' . date('Y-m-d') . '.zip', 'body' => $body];
    }

    /**
     * The PDF's HTML: registration data, challenges done per region, stars,
     * playing time, and a sentence about what was left out.
     */
    public function summaryHtml(int $participantId, bool $riskLeftOut): string
    {
        $db          = db_connect();
        $participant = model(ParticipantModel::class)->find($participantId);
        $school      = $participant->school_id === null ? null : $db->table('schools')->select('research_code')->where('id', $participant->school_id)->get()->getRow('research_code');
        $regions     = $db->table('challenge_attempts ca')
            ->select('l.name_id AS region, COUNT(DISTINCT ca.challenge_node_id) AS done, MAX(ca.stars) AS best', false)
            ->join('game_sessions gs', 'gs.id = ca.session_id')
            ->join('challenge_nodes n', 'n.id = ca.challenge_node_id')
            ->join('levels l', 'l.id = n.level_id')
            ->where('gs.participant_id', $participantId)->where('ca.status', 'completed')
            ->groupBy('l.id, l.name_id, l.sequence')->orderBy('l.sequence', 'ASC')
            ->get()->getResultArray();
        $stars = (int) ($db->table('participant_progress')->selectSum('total_stars', 'stars')->where('participant_id', $participantId)->get()->getRow('stars') ?? 0);
        $ms    = (int) ($db->table('game_sessions')->selectSum('duration_ms', 'ms')->where('participant_id', $participantId)->get()->getRow('ms') ?? 0);

        return view('pdf/participant-copy', [
            'participant' => [
                'participant_code' => $participant->participant_code,
                'age'              => $participant->age,
                'gender'           => $participant->gender,
                'class_level'      => $participant->class_level,
                'school_ref'       => $school,
                'rombel'           => $participant->rombel,
            ],
            'regions'     => $regions,
            'stars'       => $stars,
            'minutes'     => (int) round($ms / 60000),
            'riskLeftOut' => $riskLeftOut,
            // Part 8 (34 §11.4, 30 §8): the parts of the tests and the questionnaire done
            'instruments' => $this->instrumentSummary($participantId),
        ], ['saveData' => false]);
    }

    /**
     * The sheets of the head researcher's download for this child only.
     *
     * @return list<string> sheet names written
     */
    public function writeXlsx(string $path, int $participantId, int $staffId, bool $withRisk): array
    {
        $export  = service('exportService');
        $context = $export->context([
            'id' => 0, 'requested_by' => $staffId, 'format' => 'xlsx',
            'scope_json' => ['participant_id' => $participantId, 'sheets' => self::SHEETS],
        ]);
        $writer  = new ExcelWriter($path);
        $written = [];

        try {
            foreach (array_intersect($context['sheets'], self::SHEETS) as $sheet) {
                $columns = self::columns($export->columns($sheet, true, $context['full_detail']));
                $writer->startSheet(ExportLabels::sheetTitle($sheet), ExportLabels::headers($sheet, $columns, $context['labeled']));

                foreach ($export->sheetRows($sheet, $context) as $row) {
                    $writer->row(array_map(static fn (string $c): mixed => $row[$c] ?? null, $columns));
                }

                $written[] = $sheet;
            }

            if ($withRisk) {
                $writer->startSheet('risiko', ['unit', 'response', 'answered_at']);

                foreach ($this->riskRows($participantId) as $row) {
                    $writer->row([$row['unit'], $row['response'], $row['answered_at']]);
                }

                $written[] = 'risiko';
            }

            $writer->finish();
        } catch (\Throwable $e) {
            $writer->abort();

            throw $e;
        }

        return $written;
    }

    /**
     * Part 8 (30 §8): which parts of the tests, the questionnaire, and the
     * attitude scale the child did, in plain words (never scores or risk items).
     *
     * @return list<string>
     */
    private function instrumentSummary(int $participantId): array
    {
        $db    = db_connect();
        $names = ['mb' => 'Bagian 1 (membaca)', 'ae' => 'Bagian 2 (aman dan etis)', 'bd' => 'Bagian 3 (budaya digital)'];
        $out   = [];

        foreach ($db->table('instrument_sittings')->where('participant_id', $participantId)->where('is_preview', 0)->where('kind !=', 'risk')->orderBy('id', 'ASC')->get()->getResultArray() as $sitting) {
            if ($sitting['kind'] === 'test') {
                $parts = array_column($db->table('instrument_parts')->select('part')->where('sitting_id', (int) $sitting['id'])->whereIn('part', array_keys($names))
                    ->groupStart()->where('submitted_at IS NOT NULL', null, false)->orWhere('closed_at IS NOT NULL', null, false)->groupEnd()->get()->getResultArray(), 'part');
                $done  = array_values(array_intersect_key($names, array_flip($parts)));
                $out[] = ($sitting['occasion'] === 'pre' ? 'Tes awal' : 'Tes akhir') . ': ' . ($done === [] ? 'belum ada bagian yang selesai' : implode(', ', $done) . ' selesai') . '.';
            } elseif ($sitting['kind'] === 'questionnaire') {
                $out[] = 'Angket akses digital: ' . ($sitting['submitted_at'] === null ? 'belum selesai' : 'selesai') . '.';
            } elseif ($sitting['kind'] === 'attitude') {
                $out[] = 'Skala sikap di Balai Refleksi: ' . ($sitting['submitted_at'] === null ? 'belum dikirim' : 'dikirim') . '.';
            }
        }

        return $out;
    }

    /**
     * @param list<string> $columns
     *
     * @return list<string> without internal ids, answer keys, item texts, device details
     */
    public static function columns(array $columns): array
    {
        return array_values(array_filter($columns, static fn (string $c): bool => ! in_array($c, self::DROPPED, true) && ! str_ends_with($c, '_id') && ! str_starts_with($c, 'device_')));
    }

    /**
     * ang-21–ang-23 answers of the child's `risk` sitting (part 8 tables).
     *
     * @return list<array{unit: string, response: ?string, answered_at: ?string}>
     */
    private function riskRows(int $participantId): array
    {
        return db_connect()->table('instrument_responses r')->select('r.unit, r.response, r.answered_at')
            ->join('instrument_sittings s', 's.id = r.sitting_id')
            ->where('s.participant_id', $participantId)->where('s.kind', 'risk')->where('s.is_preview', 0)
            ->orderBy('r.id', 'ASC')->get()->getResultArray();
    }
}
