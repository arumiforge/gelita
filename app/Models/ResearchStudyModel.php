<?php

namespace App\Models;

use CodeIgniter\Model;

class ResearchStudyModel extends Model
{
    protected $table         = 'research_studies';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'code', 'name', 'description', 'year_label', 'status', 'retention_days',
        'default_locale', 'unlock_mode', 'field_mode', 'meeting_plan', 'package_code',
        // Part 4 (30 §15)
        'closed_at', 'consent_register_exported_at', 'feedback_names_checked_at', 'identities_replaced_at',
        // Part 7 (33 §6; test_rapid_guess_ms: owner, 4 November 2026)
        'idle_threshold_ms', 'rapid_guess_ms', 'min_stage_px', 'test_rapid_guess_ms',
        // Part 8 (34 §7.4): hidden time limits of the test module (JSON; null = Config\Gelita::$instrumentDefaults)
        'instrument_settings',
        // Part 9 (35 §5, §9, §12): the G4 threshold lock, the current derived run, the running claim
        'thresholds_locked_at', 'derived_run_id', 'derived_running_since',
    ];
    protected $validationRules = [
        'code'                => ['label' => 'Kode singkat studi', 'rules' => 'required|max_length[50]|is_unique[research_studies.code,id,{id}]'],
        'name'                => ['label' => 'Nama studi', 'rules' => 'required|max_length[200]'],
        'status'              => 'required|in_list[draft,active,closed]',
        'unlock_mode'         => 'required|in_list[sequential,free]',
        'field_mode'          => 'permit_empty|in_list[0,1]',
        'meeting_plan'        => 'permit_empty|in_list[lima_pertemuan,satu_sesi]',
    ];

    /**
     * The active study when its field mode is on (research revision 10,
     * part 2: 28 K1, §5.1), else null. Field mode only counts while the study
     * is `active`; a closed study opens the game as usual.
     *
     * @return array<string, mixed>|null
     */
    public function fieldModeStudy(): ?array
    {
        $study = $this->activeStudy();

        return $study !== null && (int) ($study['field_mode'] ?? 0) === 1 ? $study : null;
    }

    /** Studi yang sedang berjalan; NULL bila belum ada. */
    public function activeStudy(): ?array
    {
        return $this->where('status', 'active')->orderBy('id', 'DESC')->first();
    }

    /** Studi aktif atau pengecualian — dipakai service yang tidak dapat berjalan tanpanya. */
    public function requireActiveStudy(): array
    {
        $study = $this->activeStudy();

        if ($study === null) {
            throw new \RuntimeException('Tidak ada research_studies dengan status active.');
        }

        return $study;
    }
}
