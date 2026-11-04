<?php

namespace App\Commands;

use App\Libraries\ResearchLogCleanup;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Counts what migration 2026-01-01-003600_CleanResearchLogs will change,
 * without changing anything (research revision 10, part 3: 29 §5.8). Run it
 * and note the numbers before `php spark migrate`.
 */
class LogCount extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:log:hitung';
    protected $description = 'Counts the event and device rows the log cleanup migration (003600) will change. Read only.';
    protected $usage       = 'gelita:log:hitung';

    public function run(array $params)
    {
        db_sync_timezone();

        $counts = (new ResearchLogCleanup(db_connect()))->count();
        $rows   = [];

        foreach ($counts['events'] as $type => $count) {
            $rows[] = [$type, $type === 'answer_changed' ? 'renamed to answer_submitted {changed: true}' : 'deleted', (string) $count];
        }

        $rows[] = ['dialogue_completed', 'created from dialogue_advanced (last slide)', (string) $counts['dialogue_completed']];
        $rows[] = ['device_logs', 'rows whose dropped columns hold data', (string) $counts['device_logs_with_details']];

        CLI::table($rows, ['Rows', 'Change', 'Count']);
        CLI::write('Nothing was changed. The migration deletes these rows for good; note the numbers first.', 'yellow');

        return EXIT_SUCCESS;
    }
}
