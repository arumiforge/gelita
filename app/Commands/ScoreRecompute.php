<?php

namespace App\Commands;

use App\Models\AuditLogModel;
use App\Models\ScoringProfileModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Menghitung ulang skor attempt `completed` dengan scoring profile tertentu.
 *
 * Yang dihitung ulang hanya turunan (akurasi, kemandirian, skor, bintang,
 * scoring_version); jawaban dan event tidak disentuh. Attempt `abandoned`
 * tetap 0. Total sesi (session_progress) diperbarui per sesi dalam satu
 * transaction bersama attempt-nya.
 */
class ScoreRecompute extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:score:recompute';
    protected $description = 'Menghitung ulang skor attempt dengan scoring profile tertentu.';
    protected $usage       = 'gelita:score:recompute --profile <code> [--version <v>] [--study <id>] [--node <id>] [--dry-run]';
    protected $options     = [
        '--profile' => 'Kode scoring profile, mis. GELITA_V2 (wajib).',
        '--version' => 'Versi profil; bawaan versi terbaru kode itu.',
        '--study'   => 'Batasi ke satu research_studies.id.',
        '--node'    => 'Batasi ke satu challenge_nodes.id.',
        '--dry-run' => 'Hanya menghitung jumlah attempt yang akan disentuh.',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $code = trim((string) ($params['profile'] ?? CLI::getOption('profile') ?? ''));

        if ($code === '' || $code === '1') {
            CLI::error('Wajib --profile <code>. Pemakaian: php spark ' . $this->usage);

            return EXIT_ERROR;
        }

        $profiles = model(ScoringProfileModel::class);
        $version  = trim((string) ($params['version'] ?? CLI::getOption('version') ?? ''));
        $profile  = $version !== ''
            ? $profiles->findVersion($code, $version)
            : $profiles->where('code', $code)->orderBy('id', 'DESC')->first();

        if ($profile === null) {
            CLI::error("Scoring profile {$code}" . ($version !== '' ? " versi {$version}" : '') . ' tidak ditemukan.');

            return EXIT_ERROR;
        }

        $studyId = (int) ($params['study'] ?? CLI::getOption('study') ?? 0);
        $nodeId  = (int) ($params['node'] ?? CLI::getOption('node') ?? 0);
        $dryRun  = array_key_exists('dry-run', $params) || CLI::getOption('dry-run') !== null;

        $builder = db_connect()->table('challenge_attempts ca')
            ->select('ca.id, ca.session_id')
            ->join('game_sessions gs', 'gs.id = ca.session_id')
            ->where('ca.status', 'completed')
            ->orderBy('ca.session_id', 'ASC')
            ->orderBy('ca.id', 'ASC');

        if ($studyId > 0) {
            $builder->where('gs.study_id', $studyId);
        }

        if ($nodeId > 0) {
            $builder->where('ca.challenge_node_id', $nodeId);
        }

        $bySession = [];

        foreach ($builder->get()->getResultArray() as $row) {
            $bySession[(int) $row['session_id']][] = (int) $row['id'];
        }

        $total = array_sum(array_map('count', $bySession));

        CLI::write(sprintf('Profil %s versi %s · %d attempt di %d sesi.', $profile['code'], $profile['version'], $total, count($bySession)));

        if ($dryRun || $total === 0) {
            CLI::write($dryRun ? 'Dry-run: tidak ada yang diubah.' : 'Tidak ada attempt completed dalam cakupan ini.', 'yellow');

            return EXIT_SUCCESS;
        }

        $scoring  = service('scoringService');
        $done     = 0;
        $failed   = 0;

        foreach ($bySession as $sessionId => $attemptIds) {
            $db = db_connect();
            $db->transBegin();

            try {
                foreach ($attemptIds as $attemptId) {
                    $scoring->recompute($attemptId, (string) $profile['code'], (string) $profile['version']);
                }

                $db->table('session_progress')->where('session_id', $sessionId)->update([
                    'total_score' => $scoring->totalScore($sessionId),
                    'total_stars' => $scoring->totalStars($sessionId),
                ]);

                $db->transCommit();
                $done += count($attemptIds);
            } catch (\Throwable $e) {
                $db->transRollback();
                $failed += count($attemptIds);
                CLI::error("Sesi {$sessionId} dilewati: " . $e->getMessage());
            }

            CLI::showProgress($done + $failed, $total);
        }

        CLI::showProgress(false);

        model(AuditLogModel::class)->record('score_recompute', [
            'target_type' => 'scoring_profile',
            'target_id'   => (string) $profile['id'],
            'metadata'    => [
                'code'     => $profile['code'],
                'version'  => $profile['version'],
                'study_id' => $studyId ?: null,
                'node_id'  => $nodeId ?: null,
                'attempts' => $done,
                'failed'   => $failed,
                'via'      => 'cli',
            ],
        ]);

        CLI::write("{$done} attempt dihitung ulang" . ($failed > 0 ? ", {$failed} gagal" : '') . '.', $failed > 0 ? 'yellow' : 'green');

        return $failed > 0 ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
