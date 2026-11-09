<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Unlocks a study's idle and rapid-guess thresholds after the G4 lock
 * (research revision 10, part 9: 35 §5, §13). There is no unlock button:
 * an unlock is a deviation, so it needs a reason and writes the audit
 * `study_thresholds_unlocked`.
 */
class DerivedThresholds extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:turunan:ambang';
    protected $description = 'Unlocks the locked thresholds of one study (35 §5); needs --buka and --alasan, writes an audit row.';
    protected $usage       = 'gelita:turunan:ambang --studi=KODE --buka --alasan="..."';
    protected $options     = [
        '--studi'  => 'Study code (research_studies.code).',
        '--buka'   => 'Unlock the thresholds.',
        '--alasan' => 'Reason, recorded as a deviation (required with --buka).',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $code  = (string) $this->option('studi', $params);
        $study = $code === '' ? null : db_connect()->table('research_studies')->where('code', $code)->get()->getRowArray();

        if ($study === null) {
            CLI::error('Study not found. Use --studi=KODE.');

            return EXIT_ERROR;
        }

        if (! $this->flag('buka', $params)) {
            CLI::write('Thresholds locked: ' . (empty($study['thresholds_locked_at']) ? 'no' : (string) $study['thresholds_locked_at']));

            return EXIT_SUCCESS;
        }

        $reason = trim((string) $this->option('alasan', $params));

        if ($reason === '' || $reason === '1') {
            CLI::error('A reason is required: --alasan="...".');

            return EXIT_ERROR;
        }

        if (! service('derivedData')->unlockThresholds((int) $study['id'], $reason)) {
            CLI::error('The thresholds of this study are not locked.');

            return EXIT_ERROR;
        }

        CLI::write('Thresholds unlocked; audit study_thresholds_unlocked written.', 'green');

        return EXIT_SUCCESS;
    }
}
