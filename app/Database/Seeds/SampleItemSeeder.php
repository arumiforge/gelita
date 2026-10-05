<?php

namespace App\Database\Seeds;

/**
 * HANYA development: soal contoh untuk 12 tantangan (4 per wilayah) agar alur
 * permainan dapat diuji sebelum workbook bank soal kelas 5 tersedia
 * (perombakan kelas 5, Dok 1 §8). Seeder ini dihapus di Dok 2 §7.1.
 *
 * Setiap tantangan punya variasi `a` berisi 2 soal contoh (kode `-a91`,
 * `-a92`). Pengecualian Susun Gambar: satu variasi hanya menampilkan SATU
 * gambar, jadi gambar contoh kedua ada di variasi `b` (`-b91`). Isi Rumpang
 * dan Benar atau Salah memakai satu teks bacaan contoh per wilayah. Semua
 * soal berstatus review 'draft' dan diawali label [Contoh]/[Sample] (bacaan
 * rumpang: hanya potongan pertama, supaya paragrafnya tetap terbaca).
 */
class SampleItemSeeder extends GelitaSeeder
{
    private const LEVEL_CODES = ['tmg' => 'temanggung', 'mgl' => 'magelang', 'wnb' => 'wonosobo'];

    /** Tantangan Isi Rumpang yang tampil sebagai satu bacaan (ContentSeeder). */
    private const READINGS = ['tmg-2', 'mgl-2', 'wnb-2'];

    /** @var array<string,int> */
    private array $indicators = [];

    public function run(): void
    {
        if (ENVIRONMENT !== 'development') {
            $this->info('SampleItemSeeder dilewati: hanya untuk ENVIRONMENT development.');

            return;
        }

        foreach ($this->db->table('learning_indicators')->select('id, code')->get()->getResultArray() as $row) {
            $this->indicators[$row['code']] = (int) $row['id'];
        }

        $levels   = $this->levelIds();
        $passages = $this->seedPassages($levels);
        $this->seedVariantConfig($levels);

        foreach ($this->items() as $nodeRef => $items) {
            $nodeId = $this->nodeId($levels, $nodeRef);

            if ($nodeId <= 0) {
                continue;
            }

            foreach ($items as $index => $item) {
                $this->seedItem($nodeId, $nodeRef, $index + 1, $item, $passages);
            }
        }

        foreach ($this->hints() as $nodeRef => [$textId, $textEn]) {
            $nodeId = $this->nodeId($levels, $nodeRef);

            if ($nodeId > 0) {
                $this->insertIfMissing('hints', [
                    'challenge_node_id' => $nodeId,
                    'challenge_item_id' => null,
                    'sequence'          => 1,
                ], ['text_id' => $textId, 'text_en' => $textEn, 'is_active' => 1]);
            }
        }
    }

    /** @param array<string,int> $levels */
    private function nodeId(array $levels, string $nodeRef): int
    {
        [$prefix, $sequence] = explode('-', $nodeRef);

        if (! isset($levels[self::LEVEL_CODES[$prefix]])) {
            return 0;
        }

        return (int) $this->findId('challenge_nodes', [
            'level_id' => $levels[self::LEVEL_CODES[$prefix]],
            'sequence' => (int) $sequence,
        ]);
    }

    /**
     * Satu teks bacaan contoh per wilayah (Benar atau Salah).
     *
     * @param array<string,int> $levels
     *
     * @return array<string,int> passage_key => id
     */
    private function seedPassages(array $levels): array
    {
        $texts = [
            'tmg-3-a' => ['temanggung', '[Contoh] Tanah Subur Temanggung', '[Sample] The Fertile Land of Temanggung',
                'Temanggung berada di antara Gunung Sindoro dan Gunung Sumbing. Tanahnya subur. Warga menanam tembakau dan kopi di lereng gunung.',
                'Temanggung lies between Mount Sindoro and Mount Sumbing. Its soil is fertile. People grow tobacco and coffee on the mountain slopes.'],
            'mgl-3-a' => ['magelang', '[Contoh] Candi Borobudur', '[Sample] Borobudur Temple',
                'Candi Borobudur berada di Kabupaten Magelang. Dindingnya dihiasi relief yang menceritakan kisah. Pengunjung dilarang memanjat stupa.',
                'Borobudur Temple is in Magelang Regency. Its walls are decorated with reliefs that tell stories. Visitors must not climb the stupas.'],
            'wnb-3-a' => ['wonosobo', '[Contoh] Udara Dieng', '[Sample] The Air of Dieng',
                'Dieng berada di dataran tinggi Wonosobo. Udaranya sejuk, bahkan dingin pada pagi hari. Carica tumbuh baik di sana.',
                'Dieng is on the Wonosobo highlands. The air is cool, even cold in the morning. Carica grows well there.'],
        ];

        $out = [];

        foreach ($texts as $key => [$levelCode, $titleId, $titleEn, $bodyId, $bodyEn]) {
            if (! isset($levels[$levelCode])) {
                continue;
            }

            $out[$key] = $this->insertIfMissing('reading_passages', ['passage_key' => $key], [
                'level_id'  => $levels[$levelCode],
                'title_id'  => $titleId,
                'title_en'  => $titleEn,
                'body_id'   => $bodyId,
                'body_en'   => $bodyEn,
                'is_active' => 1,
            ]);
        }

        return $out;
    }

    /**
     * Judul variasi `a` dan pengecoh contoh Isi Rumpang, hanya bila node belum
     * punya pengaturan variasi itu.
     *
     * @param array<string,int> $levels
     */
    private function seedVariantConfig(array $levels): void
    {
        $distractors = [
            'mgl-2' => [['id' => 'Wonosobo', 'en' => 'Wonosobo']],
            'wnb-2' => [['id' => 'panas', 'en' => 'hot']],
        ];

        foreach ($this->items() as $nodeRef => $items) {
            $nodeId = $this->nodeId($levels, $nodeRef);

            if ($nodeId <= 0) {
                continue;
            }

            $row    = $this->db->table('challenge_nodes')->select('config_json')->where('id', $nodeId)->get()->getRowArray();
            $config = json_decode($row['config_json'] ?? '{}', true) ?: [];

            if (! empty($config['variants']['a'])) {
                continue;
            }

            $config['variants'] = (array) ($config['variants'] ?? []);
            $config['variants']['a'] = ['title_id' => '[Contoh] Variasi A', 'title_en' => '[Sample] Variant A']
                + (isset($distractors[$nodeRef]) ? ['distractors' => $distractors[$nodeRef]] : []);

            if (isset($distractors[$nodeRef])) {
                $config['distractor_count'] = count($distractors[$nodeRef]);
            }

            $this->db->table('challenge_nodes')->where('id', $nodeId)->update(['config_json' => $this->toJson($config)]);
        }
    }

    /** @param array<string,int> $passages */
    private function seedItem(int $nodeId, string $nodeRef, int $sequence, array $item, array $passages): void
    {
        $group   = $item['group'] ?? 'a';
        $itemKey = $nodeRef . '-' . $group . $item['no'];

        if ($this->findId('challenge_items', ['challenge_node_id' => $nodeId, 'item_key' => $itemKey]) !== null) {
            return;
        }

        // Bacaan: label hanya di potongan pertama agar paragrafnya tetap utuh
        $label = ! in_array($nodeRef, self::READINGS, true) || $sequence === 1;

        $this->db->table('challenge_items')->insert([
            'challenge_node_id' => $nodeId,
            'item_key'          => $itemKey,
            'sequence'          => $item['sequence'] ?? $sequence,
            'variant_group'     => $group,
            'interaction_type'  => $item['type'],
            'prompt_id'         => ($label ? '[Contoh] ' : '') . $item['prompt'][0],
            'prompt_en'         => ($label ? '[Sample] ' : '') . $item['prompt'][1],
            'passage_id'        => isset($item['passage']) ? ($passages[$item['passage']] ?? null) : null,
            'answer_key_json'   => $this->toJson($item['answer']),
            'indicator_id'      => $this->indicators[$item['indicator']] ?? null,
            'bloom_level'       => $item['bloom'] ?? null,
            'config_json'       => isset($item['config']) ? $this->toJson($item['config']) : null,
            'review_status'     => 'draft',
            'review_note'       => 'Soal contoh development — bukan bagian bank soal kelas 5.',
            'scorable'          => $item['scorable'] ?? 1,
            'is_active'         => 1,
        ]);
    }

    /** @return array<string, list<array<string, mixed>>> */
    private function items(): array
    {
        // Ukuran papan dibekukan per soal: mudah 3 × 3 kotak, sedang 4 × 4
        // kotak, sulit 4 × 4 jigsaw (DifficultyRules).
        $board = static fn (int $grid, string $shape): array => [
            'answer' => ['order' => range(0, $grid * $grid - 1)],
            'config' => ['grid' => $grid, 'shape' => $shape],
        ];
        $blank  = static fn (string $id, string $en): array => ['text_id' => $id, 'text_en' => $en];
        $target = static fn (float $x, float $y): array => ['answer' => ['target' => true], 'config' => ['x' => $x, 'y' => $y, 'w' => 14, 'decoy' => false]];

        return [
            // ---------------- TEMANGGUNG ----------------
            'tmg-1' => [
                ['no' => '91', 'type' => 'puzzle_arrange', 'indicator' => 'budaya',
                    'prompt' => ['Gambar ini menunjukkan lereng Gunung Sindoro dan Sumbing di Temanggung.', 'This picture shows the slopes of Mount Sindoro and Sumbing in Temanggung.']] + $board(3, 'square'),
                ['no' => '91', 'group' => 'b', 'sequence' => 1, 'type' => 'puzzle_arrange', 'indicator' => 'budaya',
                    'prompt' => ['Gambar ini menunjukkan kebun kopi di lereng gunung Temanggung.', 'This picture shows a coffee garden on a mountain slope in Temanggung.']] + $board(3, 'square'),
            ],
            'tmg-2' => [
                ['no' => '91', 'type' => 'fill_blank_bank', 'indicator' => 'literasi', 'bloom' => 'C1', 'answer' => $blank('Sumbing', 'Sumbing'),
                    'prompt' => ['Temanggung terletak di antara Gunung Sindoro dan Gunung ___.', 'Temanggung lies between Mount Sindoro and Mount ___.']],
                ['no' => '92', 'type' => 'fill_blank_bank', 'indicator' => 'literasi', 'bloom' => 'C1', 'answer' => $blank('kopi', 'coffee'),
                    'prompt' => ['Di lereng gunung, warga menanam tembakau dan ___.', 'On the mountain slopes, people grow tobacco and ___.']],
            ],
            'tmg-3' => [
                ['no' => '91', 'type' => 'verdict_card', 'indicator' => 'literasi', 'passage' => 'tmg-3-a',
                    'answer' => ['verdict' => 'benar'],
                    'prompt' => ['Warga Temanggung menanam kopi di lereng gunung.', 'People in Temanggung grow coffee on the mountain slopes.']],
                ['no' => '92', 'type' => 'verdict_card', 'indicator' => 'literasi', 'passage' => 'tmg-3-a',
                    'answer' => ['verdict' => 'salah'],
                    'prompt' => ['Tanah Temanggung tandus.', 'The soil of Temanggung is barren.']],
            ],
            'tmg-4' => [
                ['no' => '91', 'type' => 'find_object', 'indicator' => 'budaya',
                    'prompt' => ['Temukan daun tembakau.', 'Find the tobacco leaf.']] + $target(30, 55),
                ['no' => '92', 'type' => 'find_object', 'indicator' => 'budaya',
                    'prompt' => ['Temukan biji kopi.', 'Find the coffee beans.']] + $target(68, 40),
            ],

            // ---------------- MAGELANG ----------------
            'mgl-1' => [
                ['no' => '91', 'type' => 'puzzle_arrange', 'indicator' => 'budaya',
                    'prompt' => ['Candi Borobudur berdiri megah di Magelang.', 'Borobudur Temple stands grandly in Magelang.']] + $board(4, 'square'),
                ['no' => '91', 'group' => 'b', 'sequence' => 1, 'type' => 'puzzle_arrange', 'indicator' => 'budaya',
                    'prompt' => ['Topeng Ireng adalah kesenian tari dari Magelang.', 'Topeng Ireng is a dance art from Magelang.']] + $board(4, 'square'),
            ],
            'mgl-2' => [
                ['no' => '91', 'type' => 'fill_blank_bank', 'indicator' => 'literasi', 'bloom' => 'C2', 'answer' => $blank('Magelang', 'Magelang'),
                    'prompt' => ['Candi Borobudur berdiri di Kabupaten ___.', 'Borobudur Temple stands in ___ Regency.']],
                ['no' => '92', 'type' => 'fill_blank_bank', 'indicator' => 'literasi', 'bloom' => 'C2', 'answer' => $blank('stupa', 'stupas'),
                    'prompt' => ['Bangunan berbentuk lonceng di teras bundar disebut ___.', 'The bell-shaped structures on the round terraces are called ___.']],
            ],
            'mgl-3' => [
                ['no' => '91', 'type' => 'verdict_card', 'indicator' => 'literasi', 'passage' => 'mgl-3-a',
                    'answer' => ['verdict' => 'benar'],
                    'prompt' => ['Candi Borobudur berada di Magelang.', 'Borobudur Temple is in Magelang.']],
                ['no' => '92', 'type' => 'verdict_card', 'indicator' => 'sikap', 'passage' => 'mgl-3-a',
                    'answer' => ['verdict' => 'salah'],
                    'prompt' => ['Pengunjung boleh memanjat stupa.', 'Visitors may climb the stupas.']],
            ],
            'mgl-4' => [
                ['no' => '91', 'type' => 'find_object', 'indicator' => 'budaya',
                    'prompt' => ['Temukan topeng.', 'Find the mask.']] + $target(25, 50),
                ['no' => '92', 'type' => 'find_object', 'indicator' => 'budaya',
                    'prompt' => ['Temukan stupa kecil.', 'Find the small stupa.']] + $target(72, 45),
            ],

            // ---------------- WONOSOBO ----------------
            'wnb-1' => [
                ['no' => '91', 'type' => 'puzzle_arrange', 'indicator' => 'budaya',
                    'prompt' => ['Telaga Warna di Dieng airnya tampak berganti warna.', 'The water of Telaga Warna in Dieng seems to change colour.']] + $board(4, 'jigsaw'),
                ['no' => '91', 'group' => 'b', 'sequence' => 1, 'type' => 'puzzle_arrange', 'indicator' => 'budaya',
                    'prompt' => ['Dari Bukit Sikunir, matahari terbit tampak di atas awan.', 'From Sikunir Hill, the sunrise appears above the clouds.']] + $board(4, 'jigsaw'),
            ],
            'wnb-2' => [
                ['no' => '91', 'type' => 'fill_blank_bank', 'indicator' => 'literasi', 'bloom' => 'C4', 'answer' => $blank('sehingga', 'so'),
                    'prompt' => ['Dieng terletak lebih dari 2.000 meter di atas permukaan laut ___ udaranya dingin.', 'Dieng is more than 2,000 metres above sea level, ___ its air is cold.']],
                ['no' => '92', 'type' => 'fill_blank_bank', 'indicator' => 'literasi', 'bloom' => 'C3', 'answer' => $blank('sejuk', 'cool'),
                    'prompt' => ['Carica membutuhkan udara yang ___.', 'Carica needs ___ air.']],
            ],
            'wnb-3' => [
                ['no' => '91', 'type' => 'verdict_card', 'indicator' => 'literasi', 'passage' => 'wnb-3-a',
                    'answer' => ['verdict' => 'benar'],
                    'prompt' => ['Udara Dieng dingin pada pagi hari.', 'The air in Dieng is cold in the morning.']],
                ['no' => '92', 'type' => 'verdict_card', 'indicator' => 'literasi', 'passage' => 'wnb-3-a',
                    'answer' => ['verdict' => 'salah'],
                    'prompt' => ['Dieng berada di tepi pantai.', 'Dieng is on the seashore.']],
            ],
            'wnb-4' => [
                ['no' => '91', 'type' => 'find_object', 'indicator' => 'budaya',
                    'prompt' => ['Temukan buah carica.', 'Find the carica fruit.']] + $target(35, 60),
                ['no' => '92', 'type' => 'find_object', 'indicator' => 'budaya',
                    'prompt' => ['Temukan topi rajut.', 'Find the knitted hat.']] + $target(65, 35),
            ],
        ];
    }

    /** @return array<string,array{0:string,1:string}> */
    private function hints(): array
    {
        $puzzle  = ['Mulailah dari kepingan sudut, lalu cocokkan warnanya.', 'Start with the corner pieces, then match the colours.'];
        $rumpang = ['Baca seluruh bacaan dulu, lalu cari kata yang paling cocok untuk setiap kotak.', 'Read the whole text first, then find the word that fits each box best.'];
        $boleh   = ['Cocokkan setiap pernyataan dengan isi bacaan, bukan dengan tebakan.', 'Check each statement against the text, not against a guess.'];
        $cari    = ['Perhatikan bentuk dan warna benda di gambar.', 'Look closely at the shape and colour of each object in the picture.'];
        $out     = [];

        foreach (['tmg', 'mgl', 'wnb'] as $prefix) {
            $out[$prefix . '-1'] = $puzzle;
            $out[$prefix . '-2'] = $rumpang;
            $out[$prefix . '-3'] = $boleh;
            $out[$prefix . '-4'] = $cari;
        }

        return $out;
    }
}
