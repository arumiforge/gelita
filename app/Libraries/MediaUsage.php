<?php

namespace App\Libraries;

/**
 * Where each asset is used by the game: the "Dipakai di" column of the Media
 * and Audio pages, and the slot detail page.
 *
 * Assets are referenced two ways: by official key (`bg.welcome`,
 * `char.jaka.idle.1`, read by views with media_key_src(); described in
 * ImageSlots) and by content FK columns (challenge scene, item image,
 * passage, dialogue slide, library). Both are collected here so the admin
 * knows which screen to open to check an upload, and which assets are not
 * used at all.
 */
class MediaUsage
{
    /** Dialogue context → public path; `{code}` is the region code. */
    private const CONTEXT_PATHS = [
        'intro'        => '/intro',
        'map_intro'    => '/peta',
        'region_intro' => '/peta',
        'level_open'   => '/dialog/{code}',
        'level_done'   => '/tuntas/{code}',
        'ending'       => '/penutup',
        'map_done'     => '/peta',
    ];

    /** Character screens outside the story slides: [character slug, pose, screen, path]. */
    private const CHARACTER_SCREENS = [
        ['jaka', 'bow', 'Daftar langkah 3 (kotak tips kata sandi)', '/daftar/3'],
        ['kedu', 'smile', 'Layar tantangan selesai', '/selesai/1'],
    ];

    /**
     * @return array<int, list<array{label: string, path: string|null, edit: string|null}>> media_asset_id → uses
     */
    public function forMedia(): array
    {
        $db   = db_connect();
        $uses = [];
        $add  = static function (?int $id, string $label, ?string $path = null, ?string $edit = null) use (&$uses): void {
            if ($id !== null && $id > 0) {
                $uses[$id][] = ['label' => $label, 'path' => $path, 'edit' => $edit];
            }
        };

        $levels = [];

        foreach ($db->table('levels')->select('id, code, name_id, map_media_id, background_media_id, badge_media_id')->get()->getResultArray() as $row) {
            $levels[(int) $row['id']] = (string) $row['code'];
            $edit = 'admin/konten/level/' . (int) $row['id'];
            $add($this->int($row['map_media_id']), 'Peta wilayah ' . $row['name_id'], '/wilayah/' . $row['code'], $edit);
            $add($this->int($row['background_media_id']), 'Latar wilayah ' . $row['name_id'], '/wilayah/' . $row['code'], $edit);
            $add($this->int($row['badge_media_id']), 'Lencana ' . $row['name_id'] . ' di Profil', '/profil', $edit);
        }

        foreach ($db->table('challenge_nodes')->select('id, level_id, sequence, title_id, background_media_id, scene_media_id')->get()->getResultArray() as $row) {
            $code = $levels[(int) $row['level_id']] ?? '?';
            $ref  = node_ref($code, (int) $row['sequence']);
            $path = '/tantangan/' . $code . '/' . (int) $row['sequence'];
            $edit = 'admin/konten/node/' . (int) $row['id'];
            $add($this->int($row['scene_media_id']), 'Adegan tantangan ' . $ref . ' · ' . $row['title_id'], $path, $edit);
            $add($this->int($row['background_media_id']), 'Latar tantangan ' . $ref . ' · ' . $row['title_id'], '/misi/' . $code . '/' . (int) $row['sequence'], $edit);
        }

        $items = $db->table('challenge_items ci')
            ->select('ci.item_key, ci.media_asset_id, ci.interaction_type, ci.prompt_id, ci.scorable, ci.variant_group, cn.id AS node_id, cn.level_id, cn.sequence, cn.title_id')
            ->join('challenge_nodes cn', 'cn.id = ci.challenge_node_id')
            ->where('ci.media_asset_id IS NOT NULL')
            ->get()->getResultArray();

        foreach ($items as $row) {
            $code  = $levels[(int) $row['level_id']] ?? '?';
            $label = 'Soal ' . $row['item_key'] . ' · ' . $row['title_id'];

            if ($row['interaction_type'] === 'find_object') {
                $label .= (int) $row['scorable'] === 1
                    ? ' · benda yang dicari, petunjuk: "' . mb_strimwidth(strip_tags((string) $row['prompt_id']), 0, 120, '…') . '"'
                    : ' · benda pengecoh (bukan jawaban)';
            } elseif ($row['variant_group'] !== null && $row['variant_group'] !== '') {
                $label .= ' · variasi ' . $row['variant_group'];
            }

            $add($this->int($row['media_asset_id']), $label, '/tantangan/' . $code . '/' . (int) $row['sequence'], 'admin/konten/node/' . (int) $row['node_id']);
        }

        foreach ($db->table('reading_passages')->select('level_id, passage_key, media_asset_id')->where('media_asset_id IS NOT NULL')->get()->getResultArray() as $row) {
            $add($this->int($row['media_asset_id']), 'Bacaan ' . $row['passage_key'], null, 'admin/konten/bacaan/' . (int) $row['level_id']);
        }

        foreach ($db->table('dialogues')->select('level_id, context_code, sequence, background_media_id')->where('background_media_id IS NOT NULL')->get()->getResultArray() as $row) {
            $code = $row['level_id'] === null ? null : ($levels[(int) $row['level_id']] ?? '?');
            $add($this->int($row['background_media_id']), 'Latar ' . self::slideLabel($row['context_code'], $code, (int) $row['sequence']), self::contextPath((string) $row['context_code'], $code));
        }

        $rows = $db->table('library_media lm')
            ->select('lm.media_asset_id, lm.poster_media_id, lp.level_id, lp.sequence')
            ->join('library_pages lp', 'lp.id = lm.library_page_id')
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $code  = $levels[(int) $row['level_id']] ?? '?';
            $label = 'Pustaka ' . $code . ' halaman ' . $row['sequence'];
            $edit  = 'admin/konten/pustaka/' . (int) $row['level_id'];
            $add($this->int($row['media_asset_id']), $label, '/pustaka/' . $code, $edit);
            $add($this->int($row['poster_media_id']), $label . ' (gambar sampul)', '/pustaka/' . $code, $edit);
        }

        foreach ($this->characterUses($db, $levels) as $key => $list) {
            $id = $this->int($db->table('media_assets')->select('id')->where('asset_key', $key)->get()->getRow('id'));

            foreach ($list as $use) {
                $add($id, $use['label'], $use['path']);
            }
        }

        // Same place listed twice (e.g. one image in two variants of one node) → once
        return array_map(static function (array $list): array {
            $seen = [];

            return array_values(array_filter($list, static function (array $use) use (&$seen): bool {
                $id = $use['label'] . '|' . $use['path'];

                return ! isset($seen[$id]) && ($seen[$id] = true);
            }));
        }, $uses);
    }

    /**
     * Story lines and screens that show a character pose. Every frame of the
     * pose is listed, since core/sprite.js plays them all where frame 1 shows.
     *
     * @param array<int, string> $levels level id → code
     *
     * @return array<string, list<array{label: string, path: string|null}>> asset key → uses
     */
    public function characterUses(?\CodeIgniter\Database\BaseConnection $db = null, ?array $levels = null): array
    {
        $db ??= db_connect();
        $levels ??= array_column($db->table('levels')->select('id, code')->get()->getResultArray(), 'code', 'id');
        $count = [];

        $rows = $db->table('dialogues')->select('level_id, context_code, character_code, pose')
            ->where('is_active', 1)->whereIn('character_code', ['jaka', 'mbah_kedu'])
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $slug = $row['character_code'] === 'mbah_kedu' ? 'kedu' : 'jaka';
            $pose = (string) ($row['pose'] ?? '') ?: 'idle';
            $code = $row['level_id'] === null ? null : ($levels[(int) $row['level_id']] ?? '?');
            $key  = "char.{$slug}.{$pose}.1";
            $id   = $row['context_code'] . '|' . $code;

            $count[$key][$id] ??= ['context' => (string) $row['context_code'], 'code' => $code, 'lines' => 0];
            $count[$key][$id]['lines']++;
        }

        $out = [];

        foreach ($count as $key => $places) {
            foreach ($places as $place) {
                $out[$key][] = [
                    'label' => (NarrationCatalog::CONTEXT_LABELS[$place['context']] ?? $place['context'])
                        . ($place['code'] === null ? '' : ' · ' . $place['code'])
                        . ' (' . $place['lines'] . ' baris cerita)',
                    'path'  => self::contextPath($place['context'], $place['code']),
                ];
            }
        }

        foreach (self::CHARACTER_SCREENS as [$slug, $pose, $label, $path]) {
            $out["char.{$slug}.{$pose}.1"][] = ['label' => $label, 'path' => $path];
        }

        $animations = config('Gelita')->characterAnimations;

        foreach ($out as $key => $uses) {
            [, $slug, $pose] = explode('.', $key);

            for ($i = 2; $i <= (int) ($animations["{$slug}.{$pose}"]['frames'] ?? 1); $i++) {
                $out["char.{$slug}.{$pose}.{$i}"] = $uses;
            }
        }

        return $out;
    }

    /**
     * @return array<int, list<string>> audio_assets.id → daftar tempat pemakaian
     */
    public function forAudio(): array
    {
        $db     = db_connect();
        $uses   = [];
        $levels = array_column($db->table('levels')->select('id, code')->get()->getResultArray(), 'code', 'id');

        foreach ($db->table('dialogues')->select('level_id, context_code, sequence, audio_id_asset_id, audio_en_asset_id')->get()->getResultArray() as $row) {
            $label = self::slideLabel($row['context_code'], $row['level_id'] === null ? null : ($levels[$row['level_id']] ?? '?'), (int) $row['sequence']);

            foreach (['audio_id_asset_id' => 'Indonesia', 'audio_en_asset_id' => 'Inggris'] as $column => $lang) {
                if (($id = $this->int($row[$column])) !== null) {
                    $uses[$id][] = $label . ' (' . $lang . ')';
                }
            }
        }

        foreach ($db->table('challenge_nodes')->select('level_id, sequence, audio_intro_id, audio_intro_en_id')->get()->getResultArray() as $row) {
            $label = 'Kartu misi ' . node_ref((string) ($levels[$row['level_id']] ?? '?'), (int) $row['sequence']);

            foreach (['audio_intro_id' => 'Indonesia', 'audio_intro_en_id' => 'Inggris'] as $column => $lang) {
                if (($id = $this->int($row[$column])) !== null) {
                    $uses[$id][] = $label . ' (' . $lang . ')';
                }
            }
        }

        $items = $db->table('challenge_items')
            ->select('item_key, audio_prompt_id, audio_prompt_en_id')
            ->groupStart()->where('audio_prompt_id IS NOT NULL')->orWhere('audio_prompt_en_id IS NOT NULL')->groupEnd()
            ->get()->getResultArray();

        foreach ($items as $row) {
            foreach (['audio_prompt_id' => 'Indonesia', 'audio_prompt_en_id' => 'Inggris'] as $column => $lang) {
                if (($id = $this->int($row[$column])) !== null) {
                    $uses[$id][] = 'Petunjuk soal ' . $row['item_key'] . ' (' . $lang . ')';
                }
            }
        }

        return $uses;
    }

    /** Short label of an official slot for asset_key, or null when it is not a slot. */
    public static function slotLabel(string $assetKey): ?string
    {
        return ImageSlots::label($assetKey);
    }

    /** Public path of a dialogue context (`/dialog/temanggung`), or null. */
    public static function contextPath(string $context, ?string $levelCode): ?string
    {
        $path = self::CONTEXT_PATHS[$context] ?? null;

        return $path === null ? null : str_replace('{code}', $levelCode ?? '', $path);
    }

    /** "Cerita pembuka slide 3" / "Dialog masuk wilayah · temanggung slide 2". */
    private static function slideLabel(string $context, ?string $levelCode, int $sequence): string
    {
        return (NarrationCatalog::CONTEXT_LABELS[$context] ?? $context)
            . ($levelCode === null ? '' : ' · ' . $levelCode)
            . ' slide ' . $sequence;
    }

    private function int(mixed $value): ?int
    {
        return $value === null || (int) $value <= 0 ? null : (int) $value;
    }
}
