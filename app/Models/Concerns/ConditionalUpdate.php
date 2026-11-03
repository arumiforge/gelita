<?php

namespace App\Models\Concerns;

use App\Exceptions\ChallengeRuleException;
use CodeIgniter\Model;

/**
 * Conditional UPDATEs, the repo's pattern for writes that can race
 * (CLAUDE.md, Concurrency): the row is changed only while it still holds the
 * values the caller read, never with `SELECT … FOR UPDATE`.
 *
 * Moved out of ChallengeService (research revision 10, part 2: 28 §10.2) so
 * field sessions, participant code counters, and the challenge core share it.
 */
trait ConditionalUpdate
{
    /**
     * Changes row $id only while its columns still hold $expected; true when
     * exactly one row changed. On MySQL the UPDATE waits for another
     * transaction's row lock and then reads the newest values; on SQLite
     * (tests) writers are serial anyway. $data always changes one of the
     * $expected columns, so a matching row always counts as changed.
     *
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $data
     */
    protected function updateIf(Model $model, int $id, array $expected, array $data): bool
    {
        $model->where($expected)->update($id, $data);

        return db_connect()->affectedRows() === 1;
    }

    /**
     * updateIf() inside a transaction: losing the race → ATTEMPT_CHANGED,
     * which the caller's rollback turns into the error for the newest state.
     *
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $data
     */
    protected function updateOrStale(Model $model, int $id, array $expected, array $data): void
    {
        if (! $this->updateIf($model, $id, $expected, $data)) {
            throw ChallengeRuleException::attemptChanged();
        }
    }
}
