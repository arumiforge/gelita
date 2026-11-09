<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Computes a study's derived data layer now (research revision 10, part 9:
 * 35 §9, §13), waiting for a run another request holds. `--cek` only prints
 * whether the current run is stale and the fingerprint; it writes nothing.
 */
class DerivedCompute extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:turunan:hitung';
    protected $description = 'Computes the derived data layer of one field-mode study (35 §9). --cek only reports, without writing.';
    protected $usage       = 'gelita:turunan:hitung --studi=KODE [--cek]';
    protected $options     = [
        '--studi' => 'Study code (research_studies.code).',
        '--cek'   => 'Only print whether the current run is stale, and the input fingerprint. No writes.',
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

        $derived = service('derivedData');
        $status  = $derived->status((int) $study['id']);

        if ($this->flag('cek', $params)) {
            CLI::write('Field mode: ' . ($status['field_mode'] ? 'on' : 'off'));
            CLI::write('Current run: ' . ($status['current'] === null ? 'none' : '#' . $status['current']['id'] . ' (' . ($status['current']['finished_at'] ?? '') . ', ' . $status['current']['dictionary_version'] . ')'));
            CLI::write('Stale: ' . ($status['stale'] ? 'yes' : 'no'));
            CLI::write('Running: ' . ($status['running'] ? 'yes' : 'no'));
            CLI::write('Fingerprint: ' . $derived->fingerprint((int) $study['id']));

            return EXIT_SUCCESS;
        }

        if (! $status['field_mode']) {
            CLI::error('Field mode is off for this study: nothing is computed (35 K9).');

            return EXIT_ERROR;
        }

        $started = microtime(true);

        try {
            $runId = $derived->ensureFresh((int) $study['id'], null, 'command');

            // Fresh already: compute anyway (the command always makes a new run, like the button)
            if ($runId !== null && ! $status['stale'] && $status['current'] !== null) {
                $result = $derived->compute((int) $study['id'], null, 'command');

                if ($result['status'] !== 'done') {
                    throw new \DomainException($result['status'] === 'busy' ? 'Another run is computing.' : (string) ($result['run']['error'] ?? 'failed'));
                }

                $runId = (int) $result['run']['id'];
            }
        } catch (\DomainException $e) {
            CLI::error($e->getMessage());

            return EXIT_ERROR;
        }

        $run = db_connect()->table('derived_runs')->where('id', $runId)->get()->getRowArray();
        CLI::write(sprintf('Run #%d done: %d students, %s, %.1f s.', $runId, (int) $run['participant_count'], $run['dictionary_version'], microtime(true) - $started), 'green');

        return EXIT_SUCCESS;
    }
}
