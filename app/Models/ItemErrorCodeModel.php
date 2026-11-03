<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Error code of every wrong choice of one item (31 §15): cloze blank × word
 * (`word`), round-1 clue × object (`object`), round-2 clue × post part
 * (`part`). Bank content imported from the workbook sheets `cloze_matrix`,
 * `object_codes`, and `part_codes`; never sent to the browser.
 */
class ItemErrorCodeModel extends Model
{
    public const CHOICE_TYPES = ['word', 'object', 'part'];

    protected $table         = 'item_error_codes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['challenge_item_id', 'choice_type', 'choice_ref', 'error_code', 'is_main'];

    /**
     * Codes of one item, keyed by choice type then choice ref.
     *
     * @return array<string, array<string, array{code: string, main: bool}>>
     */
    public function forItem(int $itemId): array
    {
        $out = [];

        foreach ($this->where('challenge_item_id', $itemId)->orderBy('id', 'ASC')->findAll() as $row) {
            $out[(string) $row['choice_type']][(string) $row['choice_ref']] = [
                'code' => (string) $row['error_code'],
                'main' => (int) $row['is_main'] === 1,
            ];
        }

        return $out;
    }

    /**
     * Replaces every code of one item (import: revised codes apply at once).
     *
     * @param list<array{choice_type: string, choice_ref: string, error_code: string, is_main?: int}> $rows
     *
     * @return array{written: int, changed: int} changed = codes that differ from the stored ones
     */
    public function replaceForItem(int $itemId, array $rows): array
    {
        $before  = $this->forItem($itemId);
        $changed = 0;

        $this->where('challenge_item_id', $itemId)->delete();

        foreach ($rows as $row) {
            $type = (string) $row['choice_type'];
            $ref  = (string) $row['choice_ref'];

            if (isset($before[$type][$ref]) && $before[$type][$ref]['code'] !== (string) $row['error_code']) {
                $changed++;
            }

            $this->insert([
                'challenge_item_id' => $itemId,
                'choice_type'       => $type,
                'choice_ref'        => $ref,
                'error_code'        => (string) $row['error_code'],
                'is_main'           => (int) ($row['is_main'] ?? 0),
            ]);
        }

        return ['written' => count($rows), 'changed' => $changed];
    }
}
