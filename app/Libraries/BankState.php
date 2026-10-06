<?php

namespace App\Libraries;

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\LevelModel;
use App\Models\ReadingPassageModel;

/**
 * Bank soal yang tersimpan di database dalam bentuk node BankRules.
 *
 * Dipakai ContentVerifier (seluruh bank aktif) dan ContentImportService
 * (keadaan sesudah impor = isi database ditimpa baris workbook), sehingga
 * aturan §7.5 Dok 2 diperiksa dengan satu cara di mana pun. Dibaca langsung
 * dari tabel, bukan dari cache ContentRepository, agar importer melihat isi
 * terbaru.
 */
final class BankState
{
    /** @var array<int, string> passage id → passage_key */
    private array $passageKeys = [];

    /** @var array<string, array{level: string, body_id: string, body_en: string}> */
    private array $passages = [];

    /** @var array<int, array{code: string, difficulty: string, sequence: int, name: string}> */
    private array $levels = [];

    public function __construct()
    {
        helper('content');

        foreach (model(LevelModel::class)->ordered() as $level) {
            $this->levels[(int) $level->id] = [
                'code'       => (string) $level->code,
                'difficulty' => (string) $level->difficulty,
                'sequence'   => (int) $level->sequence,
                'name'       => $level->text('name', 'id'),
            ];
        }

        foreach (model(ReadingPassageModel::class)->where('is_active', 1)->findAll() as $passage) {
            $this->passageKeys[(int) $passage->id] = (string) $passage->passage_key;
            $this->passages[(string) $passage->passage_key] = [
                'level'   => $this->levels[(int) $passage->level_id]['code'] ?? '',
                'body_id' => (string) ($passage->body_id ?? ''),
                'body_en' => (string) ($passage->body_en ?? ''),
            ];
        }
    }

    /** @return array<int, array{code: string, difficulty: string, sequence: int, name: string}> */
    public function levels(): array
    {
        return $this->levels;
    }

    /** @return array<string, array{level: string, body_id: string, body_en: string}> */
    public function passages(): array
    {
        return $this->passages;
    }

    /**
     * Seluruh node aktif, keyed by node_ref (tmg-1 … wnb-4).
     *
     * @return array<string, array<string, mixed>>
     */
    public function nodes(): array
    {
        $out = [];

        foreach (model(ChallengeNodeModel::class)->allActive() as $node) {
            $normal = $this->node($node);

            if ($normal !== null) {
                $out[$normal['ref']] = $normal;
            }
        }

        return $out;
    }

    /**
     * Susunan tantangan aktif per wilayah untuk BankRules::region().
     *
     * @param array<string, array<string, mixed>> $nodes hasil nodes()
     *
     * @return array<string, list<array{sequence: int, engine: string}>> kode wilayah → tantangan
     */
    public function regions(array $nodes): array
    {
        $out = [];

        foreach ($this->levels as $level) {
            $out[$level['code']] = [];
        }

        foreach ($nodes as $node) {
            $out[$node['level']][] = ['sequence' => (int) $node['sequence'], 'engine' => (string) $node['engine']];
        }

        return $out;
    }

    /**
     * Satu node dengan soal aktifnya.
     *
     * @return array<string, mixed>|null null bila wilayahnya tidak dikenal
     */
    public function node(ChallengeNode $node): ?array
    {
        $level = $this->levels[(int) $node->level_id] ?? null;

        if ($level === null) {
            return null;
        }

        $normal = [
            'id'               => (int) $node->id,
            'ref'              => node_ref($level['code'], (int) $node->sequence),
            'level'            => $level['code'],
            'sequence'         => (int) $node->sequence,
            'engine'           => (string) $node->engine_type,
            'difficulty'       => $level['difficulty'],
            'distractor_count' => (int) $node->config('distractor_count', 0),
            'variants'         => [],
        ];

        foreach ($node->variants() as $group => $entry) {
            $normal['variants'][$group] = ['distractors' => self::distractors((array) ($entry['distractors'] ?? [])), 'items' => []];
        }

        foreach (model(ChallengeItemModel::class)->bankForNode((int) $node->id) as $item) {
            $group = trim((string) $item->variant_group) ?: 'a';
            $normal['variants'][$group] ??= ['distractors' => [], 'items' => []];
            $normal['variants'][$group]['items'][] = $this->item($item);
        }

        ksort($normal['variants'], SORT_STRING);

        return $normal;
    }

    /** Soal tersimpan → bentuk BankRules. */
    public function item(ChallengeItem $item): array
    {
        $type = (string) $item->interaction_type;

        return [
            'key'       => (string) $item->item_key,
            'type'      => $type,
            'prompt_id' => (string) ($item->prompt_id ?? ''),
            'prompt_en' => (string) ($item->prompt_en ?? ''),
            'answer_id' => match ($type) {
                'fill_blank_bank' => (string) ($item->answerKey('text_id') ?? ''),
                'verdict_card'    => (string) ($item->verdict() ?? ''),
                'find_object'     => $item->isDecoy() ? 'decoy' : 'target',
                default           => '',
            },
            'answer_en' => $type === 'fill_blank_bank' ? (string) ($item->answerKey('text_en') ?? '') : '',
            'bloom'     => (string) ($item->bloom_level ?? ''),
            'skill'     => (string) $item->conf('skill', ''),
            'passage'   => $item->passage_id === null ? null : ($this->passageKeys[(int) $item->passage_id] ?? '#' . $item->passage_id),
            'x'         => $item->conf('x'),
            'y'         => $item->conf('y'),
            'w'         => $item->conf('w'),
            'decoy'     => $item->isDecoy(),
            'wrong_id'  => (string) $item->conf('wrong_feedback_id', ''),
            'media'     => $item->media_asset_id !== null && (int) $item->media_asset_id > 0,
        ];
    }

    /**
     * Pengecoh config_json (string, {id, en}, atau {text_id, text_en}) → [{id, en}].
     *
     * @param list<mixed> $entries
     *
     * @return list<array{id: string, en: string}>
     */
    public static function distractors(array $entries): array
    {
        $out = [];

        foreach ($entries as $entry) {
            if (is_string($entry)) {
                $out[] = ['id' => $entry, 'en' => ''];
            } elseif (is_array($entry)) {
                $out[] = [
                    'id' => (string) ($entry['id'] ?? $entry['text_id'] ?? ''),
                    'en' => (string) ($entry['en'] ?? $entry['text_en'] ?? ''),
                ];
            }
        }

        return $out;
    }
}
