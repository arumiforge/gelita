<?php

namespace App\Libraries;

/**
 * Di mana setiap aset dipakai permainan — kolom "Dipakai di" halaman Media
 * dan Audio.
 *
 * Aset dirujuk dua cara: lewat kunci resmi (`bg.welcome`, `char.jaka.idle.1`)
 * yang dibaca view dengan media_key_src(), dan lewat kolom FK konten
 * (adegan tantangan, gambar butir, opsi, bacaan, dialog, pustaka). Keduanya
 * dikumpulkan di sini agar admin tahu aset mana yang belum terpakai dan
 * slot mana yang masih kosong.
 */
class MediaUsage
{
    /**
     * Slot resmi yang dibaca view lewat asset_key. Pola `*` = satu segmen apa pun.
     *
     * @var array<string, string>
     */
    public const SLOTS = [
        'ui.logo'              => 'Logo di halaman awal dan panel admin',
        'ui.placeholder'       => 'Gambar pengganti saat aset belum ada',
        'bg.welcome'           => 'Latar halaman sambutan / mulai',
        'bg.auth'              => 'Latar halaman masuk & daftar',
        'bg.intro'             => 'Latar cerita pembuka',
        'bg.map'               => 'Latar peta Kedu',
        'bg.reflection'        => 'Latar Balai Refleksi',
        'map.kedu'             => 'Peta Karesidenan Kedu (halaman peta)',
        'map.region.*'         => 'Peta wilayah (pos tantangan)',
        'bg.*.region'          => 'Latar wilayah (dialog, peta wilayah, tantangan)',
        'reward.badge.*'       => 'Lencana wilayah di profil siswa',
        'char.jaka.*'          => 'Frame animasi Jaka',
        'char.kedu.*'          => 'Frame animasi Mbah Kedu',
    ];

    /**
     * @return array<int, list<string>> media_asset_id → daftar tempat pemakaian
     */
    public function forMedia(): array
    {
        $db   = db_connect();
        $uses = [];
        $add  = static function (?int $id, string $label) use (&$uses): void {
            if ($id !== null && $id > 0) {
                $uses[$id][] = $label;
            }
        };

        $levels = [];

        foreach ($db->table('levels')->select('id, code, name_id, map_media_id, background_media_id, badge_media_id')->get()->getResultArray() as $row) {
            $levels[(int) $row['id']] = (string) $row['code'];
            $add($this->int($row['map_media_id']), 'Peta wilayah ' . $row['name_id']);
            $add($this->int($row['background_media_id']), 'Latar wilayah ' . $row['name_id']);
            $add($this->int($row['badge_media_id']), 'Lencana ' . $row['name_id']);
        }

        foreach ($db->table('challenge_nodes')->select('level_id, sequence, background_media_id, scene_media_id')->get()->getResultArray() as $row) {
            $ref = node_ref($levels[(int) $row['level_id']] ?? '?', (int) $row['sequence']);
            $add($this->int($row['scene_media_id']), 'Adegan tantangan ' . $ref);
            $add($this->int($row['background_media_id']), 'Latar tantangan ' . $ref);
        }

        foreach ($db->table('challenge_items')->select('item_key, media_asset_id')->where('media_asset_id IS NOT NULL')->get()->getResultArray() as $row) {
            $add($this->int($row['media_asset_id']), 'Butir ' . $row['item_key']);
        }

        $options = $db->table('challenge_options co')
            ->select('co.option_key, co.media_asset_id, ci.item_key')
            ->join('challenge_items ci', 'ci.id = co.challenge_item_id')
            ->where('co.media_asset_id IS NOT NULL')
            ->get()->getResultArray();

        foreach ($options as $row) {
            $add($this->int($row['media_asset_id']), 'Opsi ' . $row['item_key'] . ' / ' . $row['option_key']);
        }

        foreach ($db->table('reading_passages')->select('passage_key, media_asset_id')->where('media_asset_id IS NOT NULL')->get()->getResultArray() as $row) {
            $add($this->int($row['media_asset_id']), 'Bacaan ' . $row['passage_key']);
        }

        foreach ($db->table('dialogues')->select('level_id, context_code, sequence, background_media_id')->where('background_media_id IS NOT NULL')->get()->getResultArray() as $row) {
            $where = $row['level_id'] === null ? 'umum' : ($levels[(int) $row['level_id']] ?? '?');
            $add($this->int($row['background_media_id']), "Latar dialog {$where} {$row['context_code']} #{$row['sequence']}");
        }

        foreach ($db->table('library_pages')->select('level_id, sequence, image_a_media_id, image_b_media_id, video_media_id, poster_media_id')->get()->getResultArray() as $row) {
            $label = 'Pustaka ' . ($levels[(int) $row['level_id']] ?? '?') . ' hlm ' . $row['sequence'];

            foreach (['image_a_media_id', 'image_b_media_id', 'video_media_id', 'poster_media_id'] as $column) {
                $add($this->int($row[$column]), $label);
            }
        }

        if ($db->tableExists('library_media')) {
            $rows = $db->table('library_media lm')
                ->select('lm.media_asset_id, lm.poster_media_id, lp.level_id, lp.sequence')
                ->join('library_pages lp', 'lp.id = lm.library_page_id')
                ->get()->getResultArray();

            foreach ($rows as $row) {
                $label = 'Pustaka ' . ($levels[(int) $row['level_id']] ?? '?') . ' hlm ' . $row['sequence'];
                $add($this->int($row['media_asset_id']), $label);
                $add($this->int($row['poster_media_id']), $label . ' (poster)');
            }
        }

        return array_map(static fn (array $labels): array => array_values(array_unique($labels)), $uses);
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
            $where = $row['level_id'] === null ? 'umum' : ($levels[$row['level_id']] ?? '?');
            $label = "Dialog {$where} {$row['context_code']} #{$row['sequence']}";

            foreach (['audio_id_asset_id' => 'ID', 'audio_en_asset_id' => 'EN'] as $column => $lang) {
                if (($id = $this->int($row[$column])) !== null) {
                    $uses[$id][] = $label . ' (' . $lang . ')';
                }
            }
        }

        foreach ($db->table('challenge_nodes')->select('level_id, sequence, audio_intro_id, audio_intro_en_id')->get()->getResultArray() as $row) {
            $label = 'Misi ' . node_ref((string) ($levels[$row['level_id']] ?? '?'), (int) $row['sequence']);

            foreach (['audio_intro_id' => 'ID', 'audio_intro_en_id' => 'EN'] as $column => $lang) {
                if (($id = $this->int($row[$column])) !== null) {
                    $uses[$id][] = $label . ' (' . $lang . ')';
                }
            }
        }

        return $uses;
    }

    /** Keterangan slot resmi untuk asset_key, atau null bila bukan slot. */
    public static function slotLabel(string $assetKey): ?string
    {
        foreach (self::SLOTS as $pattern => $label) {
            if ($pattern === $assetKey || fnmatch($pattern, $assetKey)) {
                return $label;
            }
        }

        return null;
    }

    private function int(mixed $value): ?int
    {
        return $value === null || (int) $value <= 0 ? null : (int) $value;
    }
}
