<?php

namespace App\Services;

/**
 * Instrument content (research revision 10, part 8: 34 §6.2), like
 * ContentRepository for the game: the active version per instrument and the
 * items and stimuli of one version, decoded. A version is never changed in
 * place, so its content is cached under its id and content hash; `flush()`
 * after an import forgets the active versions read in this request.
 *
 * Item arrays carry `key` (answer, scores, error codes): only the server reads
 * it, views receive `show` only (InstrumentKeyLeakTest).
 */
class InstrumentRepository
{
    private const PREFIX = 'gelita_instr';

    private const TTL = 86400;

    /** @var array<string, array<string, mixed>|null> instrument → active version row */
    private array $active = [];

    /** @var array<int, array<string, mixed>> version id → content */
    private array $content = [];

    /**
     * Active version of `tes`, `angket`, or `sikap`, with `meta` decoded; null before any import.
     *
     * @return array<string, mixed>|null
     */
    public function activeVersion(string $instrument): ?array
    {
        if (! array_key_exists($instrument, $this->active)) {
            $row = db_connect()->table('instrument_versions')->where('instrument', $instrument)->where('is_active', 1)->orderBy('id', 'DESC')->get(1)->getRowArray();

            $this->active[$instrument] = $row === null ? null : $this->decodeVersion($row);
        }

        return $this->active[$instrument];
    }

    /**
     * One version's content.
     *
     * @return array{version: array<string, mixed>, items: array<string, array<string, mixed>>, byId: array<int, array<string, mixed>>, stimuli: array<int, array<string, mixed>>}
     */
    public function content(int $versionId): array
    {
        if (isset($this->content[$versionId])) {
            return $this->content[$versionId];
        }

        $db  = db_connect();
        $row = $db->table('instrument_versions')->where('id', $versionId)->get()->getRowArray();

        if ($row === null) {
            throw new \RuntimeException('Versi instrumen ' . $versionId . ' tidak ada.');
        }

        $cacheKey = self::PREFIX . '.v' . $versionId . '.' . substr((string) $row['content_hash'], 0, 16);
        $cache    = service('cache');
        $value    = $cache->get($cacheKey);

        if (! is_array($value)) {
            $items = [];
            $byId  = [];

            foreach ($db->table('instrument_items')->where('version_id', $versionId)->orderBy('id', 'ASC')->get()->getResultArray() as $item) {
                $decoded = [
                    'id'          => (int) $item['id'],
                    'code'        => (string) $item['code'],
                    'part'        => (string) $item['part'],
                    'form'        => $item['form'] === null ? null : (string) $item['form'],
                    'position'    => (int) $item['position'],
                    'type'        => (string) $item['type'],
                    'stimulus_id' => $item['stimulus_id'] === null ? null : (int) $item['stimulus_id'],
                    'show'        => (array) json_decode((string) $item['show_json'], true),
                    'key'         => $item['key_json'] === null ? null : (array) json_decode((string) $item['key_json'], true),
                    'units'       => array_map('strval', (array) json_decode((string) $item['units_json'], true)),
                    'cell'        => $item['cell_json'] === null ? [] : (array) json_decode((string) $item['cell_json'], true),
                ];
                $items[$decoded['code']] = $decoded;
                $byId[$decoded['id']]    = $decoded;
            }

            $stimuli = [];

            foreach ($db->table('instrument_stimuli')->where('version_id', $versionId)->get()->getResultArray() as $stimulus) {
                $stimuli[(int) $stimulus['id']] = [
                    'id'         => (int) $stimulus['id'],
                    'code'       => (string) $stimulus['code'],
                    'show'       => (array) json_decode((string) $stimulus['show_json'], true),
                    'image_slot' => $stimulus['image_slot'] === null ? null : (string) $stimulus['image_slot'],
                ];
            }

            $value = ['version' => $this->decodeVersion($row), 'items' => $items, 'byId' => $byId, 'stimuli' => $stimuli];
            $cache->save($cacheKey, $value, self::TTL);
        }

        return $this->content[$versionId] = $value;
    }

    /**
     * Items of one part (and form) of a version, by position.
     *
     * @return list<array<string, mixed>>
     */
    public function items(int $versionId, string $part, ?string $form = null): array
    {
        $items = array_values(array_filter(
            $this->content($versionId)['items'],
            static fn (array $item): bool => $item['part'] === $part && ($form === null || $item['form'] === null || $item['form'] === $form),
        ));
        usort($items, static fn (array $a, array $b): int => [$a['position'], $a['code']] <=> [$b['position'], $b['code']]);

        return $items;
    }

    /** @return array<string, mixed>|null */
    public function item(int $versionId, string $code): ?array
    {
        return $this->content($versionId)['items'][$code] ?? null;
    }

    /** Forgets what this request read (after an import). */
    public function flush(): void
    {
        $this->active  = [];
        $this->content = [];
    }

    /** @param array<string, mixed> $row */
    private function decodeVersion(array $row): array
    {
        $row['id']   = (int) $row['id'];
        $row['meta'] = $row['meta_json'] === null ? [] : (array) json_decode((string) $row['meta_json'], true);
        unset($row['meta_json']);

        return $row;
    }
}
