<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Brings the `dialogues` rows of an installed server up to date with the
 * story script (app/Database/Seeds/data/story.php = docs/naskah-cerita.md).
 *
 * ContentSeeder only inserts missing rows, so a revised script never reaches
 * a server that already has content. This class compares every script line
 * with its row (level, context, sequence) and sorts it into:
 *
 * - `same`     the row already holds the current title and text;
 * - `update`   the row holds an earlier script version listed in
 *              data/story-previous.php, so replacing it loses nothing;
 * - `edited`   the row holds other text, most likely edited in the panel;
 *              replaced only when the caller asks to overwrite;
 * - `missing`  no row yet; inserted like ContentSeeder::seedDialogues();
 * - `no_level` the line's region is not installed; skipped.
 *
 * Only title_id, title_en, text_id, and text_en are written. Character, pose,
 * effect, background, active flag, and the linked recordings stay as they
 * are, so a replaced line keeps audio that still speaks the old text.
 */
class StorySync
{
    public const FIELDS = ['title_id', 'title_en', 'text_id', 'text_en'];

    private BaseConnection $db;

    /** @var list<array<string, mixed>> */
    private array $script;

    /** @var array<string, list<array<string, ?string>>> */
    private array $previous;

    /**
     * @param list<array<string, mixed>>|null                   $script   defaults to data/story.php
     * @param array<string, list<array<string, ?string>>>|null $previous defaults to data/story-previous.php
     */
    public function __construct(?BaseConnection $db = null, ?array $script = null, ?array $previous = null)
    {
        $data           = APPPATH . 'Database/Seeds/data/';
        $this->db       = $db ?? db_connect();
        $this->script   = $script ?? require $data . 'story.php';
        $this->previous = $previous ?? require $data . 'story-previous.php';
    }

    /**
     * One entry per script line, in script order.
     *
     * @return list<array{code: string, status: string, id: ?int, has_audio: bool, line: array<string, mixed>, row: ?array<string, mixed>}>
     */
    public function plan(): array
    {
        $levels = [];

        foreach ($this->db->table('levels')->select('id, code')->get()->getResultArray() as $level) {
            $levels[(string) $level['code']] = (int) $level['id'];
        }

        $plan = [];

        foreach ($this->script as $line) {
            $entry = ['code' => (string) $line['code'], 'status' => 'no_level', 'id' => null, 'has_audio' => false, 'line' => $line, 'row' => null];

            if ($line['level'] !== null && ! isset($levels[$line['level']])) {
                $plan[] = $entry;

                continue;
            }

            $row = $this->db->table('dialogues')
                ->where('level_id', $line['level'] === null ? null : $levels[$line['level']])
                ->where('context_code', $line['context'])
                ->where('sequence', $line['sequence'])
                ->orderBy('id', 'ASC')
                ->get(1)
                ->getRowArray();

            $entry['line']['level_id'] = $line['level'] === null ? null : $levels[$line['level']];

            if ($row === null) {
                $entry['status'] = 'missing';
                $plan[]          = $entry;

                continue;
            }

            $entry['id']        = (int) $row['id'];
            $entry['row']       = $row;
            $entry['has_audio'] = ! empty($row['audio_id_asset_id']) || ! empty($row['audio_en_asset_id']);
            $entry['status']    = match (true) {
                self::matches($row, $line)                                => 'same',
                $this->matchesPrevious($row, (string) $line['code']) => 'update',
                default                                                   => 'edited',
            };

            $plan[] = $entry;
        }

        return $plan;
    }

    /**
     * Writes the plan: `update` and `missing` lines always, `edited` lines only
     * when $overwrite is true. Each update is conditional on the row still
     * holding the text read by plan(), so a panel save in between wins and
     * the line is reported as `stale`.
     *
     * @param list<array<string, mixed>> $plan from plan()
     *
     * @return array{updated: list<string>, overwritten: list<string>, inserted: list<string>, stale: list<string>, stale_audio: list<string>}
     */
    public function apply(array $plan, bool $overwrite = false): array
    {
        $result = ['updated' => [], 'overwritten' => [], 'inserted' => [], 'stale' => [], 'stale_audio' => []];

        foreach ($plan as $entry) {
            $line = $entry['line'];
            $code = $entry['code'];

            if ($entry['status'] === 'missing') {
                $this->db->table('dialogues')->insert([
                    'level_id'       => $line['level_id'],
                    'context_code'   => $line['context'],
                    'sequence'       => $line['sequence'],
                    'character_code' => $line['character'],
                    'pose'           => $line['pose'],
                    'effect'         => $line['effect'],
                    'title_id'       => $line['title_id'],
                    'title_en'       => $line['title_en'],
                    'text_id'        => $line['text_id'],
                    'text_en'        => $line['text_en'],
                    'is_active'      => 1,
                ]);
                $result['inserted'][] = $code;

                continue;
            }

            if ($entry['status'] !== 'update' && ! ($overwrite && $entry['status'] === 'edited')) {
                continue;
            }

            $builder = $this->db->table('dialogues')->where('id', $entry['id']);

            foreach (self::FIELDS as $field) {
                $builder->where($field, $entry['row'][$field]);
            }

            $builder->update(array_intersect_key($line, array_flip(self::FIELDS)));

            if ($this->db->affectedRows() !== 1) {
                $result['stale'][] = $code;

                continue;
            }

            $result[$entry['status'] === 'update' ? 'updated' : 'overwritten'][] = $code;

            if ($entry['has_audio']) {
                $result['stale_audio'][] = $code;
            }
        }

        if ($result['updated'] !== [] || $result['overwritten'] !== [] || $result['inserted'] !== []) {
            service('contentRepository')->flush();
        }

        return $result;
    }

    /** @param array<string, mixed> $row */
    private function matchesPrevious(array $row, string $code): bool
    {
        foreach ($this->previous[$code] ?? [] as $version) {
            if (self::matches($row, $version)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Same title and text; NULL and an empty title count as equal, as the
     * dialogue editor stores a blank title as NULL.
     *
     * @param array<string, mixed> $row
     * @param array<string, mixed> $version
     */
    private static function matches(array $row, array $version): bool
    {
        foreach (self::FIELDS as $field) {
            if (trim((string) ($row[$field] ?? '')) !== trim((string) ($version[$field] ?? ''))) {
                return false;
            }
        }

        return true;
    }
}
