<?php

namespace App\Database\Seeds;

/**
 * HANYA development: 2 item contoh per node (30 item) + 1 teks bacaan + 1 petunjuk per node,
 * agar alur permainan dapat diuji sebelum bank soal diimpor.
 *
 * item_key memakai nomor 91–92 (mis. tmg-2-91) supaya tidak bertabrakan dengan
 * bank soal production (01, 02, …). Semua item berstatus review 'draft' dan
 * diawali label [Contoh]/[Sample].
 */
class SampleItemSeeder extends GelitaSeeder
{
    private const LEVEL_CODES = ['tmg' => 'temanggung', 'mgl' => 'magelang', 'wnb' => 'wonosobo'];

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
        $this->seedDistractors($levels);

        foreach ($this->items() as $nodeRef => $items) {
            $nodeId = $this->nodeId($levels, $nodeRef);

            foreach ($items as $index => $item) {
                $this->seedItem($nodeId, $nodeRef, $index + 1, $item, $passages);
            }
        }

        foreach ($this->hints() as $nodeRef => [$textId, $textEn]) {
            $this->insertIfMissing('hints', [
                'challenge_node_id' => $this->nodeId($levels, $nodeRef),
                'challenge_item_id' => null,
                'sequence'          => 1,
            ], ['text_id' => $textId, 'text_en' => $textEn, 'is_active' => 1]);
        }
    }

    /** @param array<string,int> $levels */
    private function nodeId(array $levels, string $nodeRef): int
    {
        [$prefix, $sequence] = explode('-', $nodeRef);

        return (int) $this->findId('challenge_nodes', [
            'level_id' => $levels[self::LEVEL_CODES[$prefix]],
            'sequence' => (int) $sequence,
        ]);
    }

    /**
     * @param array<string,int> $levels
     *
     * @return array<string,int> passage_key => id
     */
    private function seedPassages(array $levels): array
    {
        $id = $this->insertIfMissing('reading_passages', ['passage_key' => 'tmg-teks-contoh'], [
            'level_id' => $levels['temanggung'],
            'title_id' => '[Contoh] Tanah Subur Temanggung',
            'title_en' => '[Sample] The Fertile Land of Temanggung',
            'body_id'  => 'Temanggung berada di antara Gunung Sindoro dan Gunung Sumbing. Tanahnya subur. Warga menanam tembakau dan kopi di lereng gunung. Di Temanggung juga ada candi kuno yang perlu dijaga bersama.',
            'body_en'  => 'Temanggung lies between Mount Sindoro and Mount Sumbing. Its soil is fertile. People grow tobacco and coffee on the mountain slopes. Temanggung also has ancient temples that we must look after together.',
        ]);

        return ['tmg-teks-contoh' => $id];
    }

    /** Pengecoh bank kata contoh, hanya bila node belum punya pengecoh */
    private function seedDistractors(array $levels): void
    {
        $sets = [
            'tmg-2' => [['id' => 'salju', 'en' => 'snow'], ['id' => 'laut', 'en' => 'sea'], ['id' => 'pasir', 'en' => 'sand']],
            'wnb-2' => [['id' => 'panas', 'en' => 'hot'], ['id' => 'rendah', 'en' => 'low'], ['id' => 'kering', 'en' => 'dry']],
        ];

        foreach ($sets as $nodeRef => $distractors) {
            $nodeId = $this->nodeId($levels, $nodeRef);
            $row    = $this->db->table('challenge_nodes')->select('config_json')->where('id', $nodeId)->get()->getRowArray();
            $config = json_decode($row['config_json'] ?? '{}', true) ?: [];

            if (! empty($config['distractors'])) {
                continue;
            }

            $config['distractors'] = $distractors;
            $this->db->table('challenge_nodes')->where('id', $nodeId)->update(['config_json' => $this->toJson($config)]);
        }
    }

    private function seedItem(int $nodeId, string $nodeRef, int $sequence, array $item, array $passages): void
    {
        $itemKey = $nodeRef . '-' . $item['no'];

        if ($this->findId('challenge_items', ['challenge_node_id' => $nodeId, 'item_key' => $itemKey]) !== null) {
            return;
        }

        $this->db->table('challenge_items')->insert([
            'challenge_node_id' => $nodeId,
            'item_key'          => $itemKey,
            'sequence'          => $sequence,
            'interaction_type'  => $item['type'],
            'prompt_id'         => '[Contoh] ' . $item['prompt'][0],
            'prompt_en'         => '[Sample] ' . $item['prompt'][1],
            'source_text_id'    => $item['source'][0] ?? null,
            'source_text_en'    => $item['source'][1] ?? null,
            'passage_id'        => isset($item['passage']) ? $passages[$item['passage']] : null,
            'answer_key_json'   => $this->toJson($item['answer']),
            'indicator_id'      => $this->indicators[$item['indicator']],
            'config_json'       => isset($item['config']) ? $this->toJson($item['config']) : null,
            'review_status'     => 'draft',
            'review_note'       => 'Item contoh development — bukan bagian bank soal production.',
            'scorable'          => $item['scorable'] ?? 1,
            'is_active'         => 1,
        ]);

        $itemId = (int) $this->db->insertID();

        foreach ($item['options'] ?? [] as $order => [$suffix, $labelId, $labelEn, $correct, $feedbackId, $feedbackEn]) {
            $this->db->table('challenge_options')->insert([
                'challenge_item_id' => $itemId,
                'option_key'        => $itemKey . '-' . $suffix,
                'label_id'          => $labelId,
                'label_en'          => $labelEn,
                'is_correct'        => $correct ? 1 : 0,
                'feedback_id'       => $feedbackId,
                'feedback_en'       => $feedbackEn,
                'display_order'     => $order + 1,
            ]);
        }
    }

    /** Opsi pilihan ganda: [suffix, label_id, label_en, benar?, feedback_id, feedback_en] */
    private function choice(string $itemKey, array $options, string $correct): array
    {
        return [
            'answer'  => ['option_key' => $itemKey . '-' . $correct],
            'options' => $options,
        ];
    }

    private function items(): array
    {
        $order9 = ['order' => [0, 1, 2, 3, 4, 5, 6, 7, 8]];

        return [
            // ---------------- TEMANGGUNG ----------------
            'tmg-1' => [
                ['no' => '91', 'type' => 'puzzle_arrange', 'indicator' => 'budaya', 'answer' => $order9, 'config' => ['grid' => 3],
                    'prompt' => ['Gambar ini menunjukkan lereng Gunung Sindoro dan Sumbing di Temanggung.', 'This picture shows the slopes of Mount Sindoro and Sumbing in Temanggung.']],
                ['no' => '92', 'type' => 'puzzle_arrange', 'indicator' => 'budaya', 'answer' => $order9, 'config' => ['grid' => 3],
                    'prompt' => ['Gambar ini menunjukkan kebun kopi di lereng gunung Temanggung.', 'This picture shows a coffee garden on a mountain slope in Temanggung.']],
            ],
            'tmg-2' => [
                ['no' => '91', 'type' => 'fill_blank_bank', 'indicator' => 'literasi',
                    'answer' => ['text_id' => 'Sumbing', 'text_en' => 'Sumbing'],
                    'prompt' => ['Temanggung terletak di antara Gunung Sindoro dan Gunung ___.', 'Temanggung lies between Mount Sindoro and Mount ___.']],
                ['no' => '92', 'type' => 'fill_blank_bank', 'indicator' => 'literasi',
                    'answer' => ['text_id' => 'kopi', 'text_en' => 'coffee'],
                    'prompt' => ['Di lereng gunung Temanggung, warga menanam tembakau dan ___.', 'On the mountain slopes of Temanggung, people grow tobacco and ___.']],
            ],
            'tmg-3' => [
                ['no' => '91', 'type' => 'verdict_card', 'indicator' => 'literasi', 'passage' => 'tmg-teks-contoh',
                    'answer' => ['verdict' => 'benar'],
                    'prompt' => ['Warga Temanggung menanam kopi di lereng gunung.', 'People in Temanggung grow coffee on the mountain slopes.']],
                ['no' => '92', 'type' => 'verdict_card', 'indicator' => 'sikap', 'passage' => 'tmg-teks-contoh',
                    'answer' => ['verdict' => 'salah'],
                    'prompt' => ['Candi kuno boleh dicoret-coret karena sudah tua.', 'It is fine to scribble on ancient temples because they are old.']],
            ],
            'tmg-4' => [
                ['no' => '91', 'type' => 'find_object', 'indicator' => 'budaya',
                    'answer' => ['target' => true],
                    'config' => ['x' => 30, 'y' => 55, 'w' => 14, 'decoy' => false],
                    'prompt' => ['Temukan daun tembakau.', 'Find the tobacco leaf.']],
                ['no' => '92', 'type' => 'find_object', 'indicator' => 'budaya', 'scorable' => 0,
                    'answer' => ['target' => false],
                    'config' => ['x' => 70, 'y' => 40, 'w' => 13, 'decoy' => true,
                        'wrong_feedback_id' => 'Angklung berasal dari Jawa Barat, bukan Temanggung.',
                        'wrong_feedback_en' => 'Angklung is from West Java, not Temanggung.'],
                    'prompt' => ['Angklung (objek jebakan).', 'Angklung (decoy object).']],
            ],
            'tmg-5' => [
                ['no' => '91', 'type' => 'single_choice', 'indicator' => 'literasi', 'passage' => 'tmg-teks-contoh',
                    'prompt' => ['Di antara dua gunung apa Temanggung berada?', 'Between which two mountains does Temanggung lie?']]
                    + $this->choice('tmg-5-91', [
                        ['a', 'Sindoro dan Sumbing', 'Sindoro and Sumbing', true, 'Tepat! Teks menyebut Gunung Sindoro dan Gunung Sumbing.', 'Correct! The text mentions Mount Sindoro and Mount Sumbing.'],
                        ['b', 'Merapi dan Merbabu', 'Merapi and Merbabu', false, null, null],
                        ['c', 'Lawu dan Slamet', 'Lawu and Slamet', false, null, null],
                        ['d', 'Ungaran dan Muria', 'Ungaran and Muria', false, null, null],
                    ], 'a'),
                ['no' => '92', 'type' => 'single_choice', 'indicator' => 'literasi', 'passage' => 'tmg-teks-contoh',
                    'prompt' => ['Tanaman apa yang ditanam warga di lereng gunung?', 'What do people grow on the mountain slopes?']]
                    + $this->choice('tmg-5-92', [
                        ['a', 'Padi dan jagung', 'Rice and corn', false, null, null],
                        ['b', 'Tembakau dan kopi', 'Tobacco and coffee', true, 'Tepat! Jawabannya tertulis jelas di teks.', 'Correct! The answer is stated clearly in the text.'],
                        ['c', 'Kelapa dan sawit', 'Coconut and oil palm', false, null, null],
                        ['d', 'Teh dan cokelat', 'Tea and cocoa', false, null, null],
                    ], 'b'),
            ],

            // ---------------- MAGELANG ----------------
            'mgl-1' => [
                ['no' => '91', 'type' => 'puzzle_arrange', 'indicator' => 'budaya', 'answer' => $order9, 'config' => ['grid' => 3],
                    'prompt' => ['Topeng Ireng adalah kesenian tari dari Magelang.', 'Topeng Ireng is a dance art from Magelang.']],
                ['no' => '92', 'type' => 'puzzle_arrange', 'indicator' => 'budaya', 'answer' => $order9, 'config' => ['grid' => 3],
                    'prompt' => ['Candi Borobudur berdiri megah di Magelang.', 'Borobudur Temple stands grandly in Magelang.']],
            ],
            'mgl-2' => [
                ['no' => '91', 'type' => 'fill_blank_free', 'indicator' => 'literasi',
                    'answer' => ['accept_id' => ['Magelang'], 'accept_en' => ['Magelang'], 'case_sensitive' => false],
                    'prompt' => ['Candi Borobudur berada di Kabupaten ___.', 'Borobudur Temple is located in ___ Regency.']],
                ['no' => '92', 'type' => 'fill_blank_free', 'indicator' => 'literasi',
                    'answer' => ['accept_id' => ['stupa'], 'accept_en' => ['stupa'], 'case_sensitive' => false],
                    'prompt' => ['Bangunan berbentuk seperti lonceng di Candi Borobudur disebut ___.', 'The bell-shaped structures at Borobudur Temple are called ___.']],
            ],
            'mgl-3' => [
                ['no' => '91', 'type' => 'verdict_reason', 'indicator' => 'sikap',
                    'answer' => ['verdict' => 'benar',
                        'sample_reason_id' => 'Candi Borobudur memang terletak di Kabupaten Magelang.',
                        'sample_reason_en' => 'Borobudur Temple is indeed located in Magelang Regency.'],
                    'prompt' => ['Candi Borobudur berada di Magelang.', 'Borobudur Temple is in Magelang.']],
                ['no' => '92', 'type' => 'verdict_reason', 'indicator' => 'sikap',
                    'answer' => ['verdict' => 'pendapat',
                        'sample_reason_id' => "Kata 'paling indah' adalah penilaian pribadi, bukan fakta.",
                        'sample_reason_en' => "The words 'most beautiful' are a personal judgement, not a fact."],
                    'prompt' => ['Tari Topeng Ireng adalah tarian paling indah di Indonesia.', 'The Topeng Ireng dance is the most beautiful dance in Indonesia.']],
            ],
            'mgl-4' => [
                ['no' => '91', 'type' => 'single_choice', 'indicator' => 'budaya',
                    'prompt' => ['Kesenian Topeng Ireng berasal dari daerah mana?', 'Where does the Topeng Ireng art come from?']]
                    + $this->choice('mgl-4-91', [
                        ['a', 'Bali', 'Bali', false, null, null],
                        ['b', 'Aceh', 'Aceh', false, null, null],
                        ['c', 'Magelang', 'Magelang', true, 'Tepat! Topeng Ireng adalah kesenian Magelang.', 'Correct! Topeng Ireng is an art from Magelang.'],
                        ['d', 'Papua', 'Papua', false, null, null],
                    ], 'c'),
                ['no' => '92', 'type' => 'single_choice', 'indicator' => 'budaya',
                    'prompt' => ['Apa fungsi relief pada dinding Candi Borobudur?', 'What is the purpose of the reliefs on the walls of Borobudur Temple?']]
                    + $this->choice('mgl-4-92', [
                        ['a', 'Menceritakan kisah dan ajaran', 'To tell stories and teachings', true, 'Tepat! Relief adalah gambar pahatan yang bercerita.', 'Correct! Reliefs are carved pictures that tell stories.'],
                        ['b', 'Menahan banjir', 'To hold back floods', false, null, null],
                        ['c', 'Tempat menyimpan padi', 'To store rice', false, null, null],
                        ['d', 'Penanda batas desa', 'To mark village borders', false, null, null],
                    ], 'a'),
            ],
            'mgl-5' => [
                ['no' => '91', 'type' => 'single_choice', 'indicator' => 'literasi',
                    'source' => ['Gambar: bangunan berbentuk lonceng berjajar di puncak candi. Teks: Di bagian atas Candi Borobudur terdapat banyak stupa.',
                        'Picture: bell-shaped structures in rows at the top of a temple. Text: At the top of Borobudur Temple there are many stupas.'],
                    'prompt' => ['Bagian candi apa yang tampak pada gambar dan disebut dalam teks?', 'Which part of the temple appears in the picture and is mentioned in the text?']]
                    + $this->choice('mgl-5-91', [
                        ['a', 'Gapura', 'Gate', false, null, null],
                        ['b', 'Stupa', 'Stupa', true, 'Tepat! Gambar dan teks sama-sama menunjuk stupa.', 'Correct! Both the picture and the text point to the stupas.'],
                        ['c', 'Kolam', 'Pond', false, null, null],
                        ['d', 'Menara', 'Tower', false, null, null],
                    ], 'b'),
                ['no' => '92', 'type' => 'single_choice', 'indicator' => 'literasi',
                    'source' => ['Gambar: penari dengan hiasan kepala dari bulu berwarna-warni. Teks: Penari Topeng Ireng memakai hiasan kepala dari bulu.',
                        'Picture: a dancer wearing a colourful feather headdress. Text: Topeng Ireng dancers wear feather headdresses.'],
                    'prompt' => ['Apa yang dipakai penari menurut gambar dan teks?', 'What does the dancer wear according to the picture and the text?']]
                    + $this->choice('mgl-5-92', [
                        ['a', 'Topi caping', 'A farmer\'s hat', false, null, null],
                        ['b', 'Mahkota emas', 'A golden crown', false, null, null],
                        ['c', 'Kain batik', 'A batik cloth', false, null, null],
                        ['d', 'Hiasan kepala dari bulu', 'A feather headdress', true, 'Tepat! Gambar dan teks cocok.', 'Correct! The picture and the text match.'],
                    ], 'd'),
            ],

            // ---------------- WONOSOBO ----------------
            'wnb-1' => [
                ['no' => '91', 'type' => 'ordering', 'indicator' => 'literasi',
                    'answer' => ['order' => ['c', 'a', 'd', 'b']],
                    'config' => ['pieces' => [
                        ['key' => 'a', 'text_id' => 'Rebus mie dan kol sebentar.', 'text_en' => 'Boil the noodles and cabbage briefly.'],
                        ['key' => 'b', 'text_id' => 'Sajikan selagi hangat.', 'text_en' => 'Serve while warm.'],
                        ['key' => 'c', 'text_id' => 'Siapkan mie dan kol.', 'text_en' => 'Prepare the noodles and cabbage.'],
                        ['key' => 'd', 'text_id' => 'Tuang ke mangkuk, lalu siram dengan kuah kental.', 'text_en' => 'Pour into a bowl, then add the thick sauce.'],
                    ]],
                    'prompt' => ['Susun langkah menyajikan mie berkuah kental.', 'Put the steps for serving noodles in thick sauce in order.']],
                ['no' => '92', 'type' => 'ordering', 'indicator' => 'literasi',
                    'answer' => ['order' => ['b', 'd', 'a', 'c']],
                    'config' => ['pieces' => [
                        ['key' => 'a', 'text_id' => 'Bandingkan dengan sumber lain.', 'text_en' => 'Compare it with other sources.'],
                        ['key' => 'b', 'text_id' => 'Baca judul dan isi berita sampai habis.', 'text_en' => 'Read the headline and the whole story.'],
                        ['key' => 'c', 'text_id' => 'Putuskan: bagikan atau tidak.', 'text_en' => 'Decide: share it or not.'],
                        ['key' => 'd', 'text_id' => 'Periksa siapa yang menulis berita.', 'text_en' => 'Check who wrote the story.'],
                    ]],
                    'prompt' => ['Susun langkah memeriksa sebuah berita.', 'Put the steps for checking a news story in order.']],
            ],
            'wnb-2' => [
                ['no' => '91', 'type' => 'fill_blank_bank', 'indicator' => 'literasi',
                    'source' => ['Udara di Dataran Tinggi Dieng terasa dingin, terutama pada pagi hari. Wisatawan disarankan membawa jaket tebal.',
                        'The air on the Dieng Plateau feels cold, especially in the morning. Visitors are advised to bring a thick jacket.'],
                    'answer' => ['text_id' => 'dingin', 'text_en' => 'cold'],
                    'prompt' => ['Kesimpulan: wisatawan perlu membawa jaket karena udara Dieng ___.', 'Conclusion: visitors need a jacket because the air in Dieng is ___.']],
                ['no' => '92', 'type' => 'fill_blank_bank', 'indicator' => 'literasi',
                    'source' => ['Dieng sering tertutup kabut dan awan karena letaknya jauh di atas permukaan laut.',
                        'Dieng is often covered in mist and clouds because it lies far above sea level.'],
                    'answer' => ['text_id' => 'tinggi', 'text_en' => 'high'],
                    'prompt' => ['Kesimpulan: Dieng disebut negeri di atas awan karena letaknya ___.', 'Conclusion: Dieng is called the land above the clouds because it is ___.']],
            ],
            'wnb-3' => [
                ['no' => '91', 'type' => 'verdict_card', 'indicator' => 'sikap',
                    'answer' => ['verdict' => 'salah'],
                    'config' => ['sources' => [
                        ['label_id' => 'Sumber A', 'label_en' => 'Source A', 'kind' => 'official',
                            'text_id' => 'Pengumuman pengelola kawasan: pengunjung dilarang memanjat bangunan candi demi menjaga kelestariannya.',
                            'text_en' => 'Site management notice: visitors must not climb the temple buildings, to keep them preserved.'],
                        ['label_id' => 'Sumber B', 'label_en' => 'Source B', 'kind' => 'anonymous',
                            'text_id' => 'Pesan berantai: naik ke atas candi aman kok, banyak yang melakukannya.',
                            'text_en' => 'Chain message: climbing the temple is safe, lots of people do it.'],
                    ]],
                    'prompt' => ['Pengunjung boleh memanjat candi untuk berfoto.', 'Visitors may climb the temple to take photos.']],
                ['no' => '92', 'type' => 'verdict_card', 'indicator' => 'sikap',
                    'answer' => ['verdict' => 'benar'],
                    'config' => ['sources' => [
                        ['label_id' => 'Sumber A', 'label_en' => 'Source A', 'kind' => 'official',
                            'text_id' => 'Papan informasi pengelola: buanglah sampah pada tempat yang disediakan.',
                            'text_en' => 'Management information board: throw rubbish in the bins provided.'],
                        ['label_id' => 'Sumber B', 'label_en' => 'Source B', 'kind' => 'anonymous',
                            'text_id' => 'Komentar akun tanpa nama: sampah kecil tidak apa-apa dibuang sembarangan.',
                            'text_en' => 'Comment from an anonymous account: small rubbish can be thrown anywhere.'],
                    ]],
                    'prompt' => ['Pengunjung sebaiknya membuang sampah pada tempatnya.', 'Visitors should put their rubbish in the bins.']],
            ],
            'wnb-4' => [
                ['no' => '91', 'type' => 'source_trust', 'indicator' => 'sikap',
                    'prompt' => ['Kamu ingin tahu jadwal festival budaya di Wonosobo. Sumber mana yang paling dapat dipercaya?', 'You want to know the schedule of a cultural festival in Wonosobo. Which source is the most trustworthy?']]
                    + $this->choice('wnb-4-91', [
                        ['a', 'Situs resmi pemerintah daerah', 'The official local government website', true, 'Tepat! Sumber resmi jelas siapa pembuatnya.', 'Correct! An official source clearly shows who made it.'],
                        ['b', 'Pesan berantai tanpa nama pengirim', 'A chain message with no sender name', false, null, null],
                        ['c', 'Komentar akun anonim di media sosial', 'A comment from an anonymous social media account', false, null, null],
                        ['d', 'Tebakan teman sekelas', "A classmate's guess", false, null, null],
                    ], 'a'),
                ['no' => '92', 'type' => 'source_trust', 'indicator' => 'sikap',
                    'prompt' => ['Kamu mencari informasi cuaca di Dieng hari ini. Sumber mana yang paling dapat dipercaya?', 'You are looking for today\'s weather in Dieng. Which source is the most trustworthy?']]
                    + $this->choice('wnb-4-92', [
                        ['a', 'Unggahan lama dari tahun lalu', 'An old post from last year', false, null, null],
                        ['b', 'Situs resmi BMKG', 'The official BMKG website', true, 'Tepat! BMKG adalah lembaga resmi cuaca.', 'Correct! BMKG is the official weather agency.'],
                        ['c', 'Pesan berantai', 'A chain message', false, null, null],
                        ['d', 'Gambar tanpa keterangan', 'A picture with no caption', false, null, null],
                    ], 'b'),
            ],
            'wnb-5' => [
                ['no' => '91', 'type' => 'single_choice', 'indicator' => 'sikap', 'config' => ['digital_pillar' => 'digital_ethics'],
                    'prompt' => ['Kamu menerima pesan berantai yang belum jelas kebenarannya. Apa yang sebaiknya kamu lakukan?', 'You receive a chain message that may not be true. What should you do?']]
                    + $this->choice('wnb-5-91', [
                        ['a', 'Langsung menyebarkannya ke semua teman', 'Share it with all your friends right away', false, null, null],
                        ['b', 'Memeriksa kebenarannya dulu di sumber resmi', 'Check whether it is true on an official source first', true, 'Tepat! Periksa dulu sebelum membagikan.', 'Correct! Check before you share.'],
                        ['c', 'Menambah cerita agar lebih seru', 'Add to the story to make it more exciting', false, null, null],
                        ['d', 'Mengirim ulang tanpa nama', 'Forward it without your name', false, null, null],
                    ], 'b'),
                ['no' => '92', 'type' => 'single_choice', 'indicator' => 'sikap', 'config' => ['digital_pillar' => 'digital_safety'],
                    'prompt' => ['Temanmu meminta kata sandi akun gamemu. Apa yang sebaiknya kamu lakukan?', 'A friend asks for the password to your game account. What should you do?']]
                    + $this->choice('wnb-5-92', [
                        ['a', 'Memberikannya karena dia teman dekat', 'Give it because they are a close friend', false, null, null],
                        ['b', 'Menuliskannya di papan kelas', 'Write it on the classroom board', false, null, null],
                        ['c', 'Menolak dengan sopan dan menjaga kata sandi tetap rahasia', 'Politely refuse and keep the password secret', true, 'Tepat! Kata sandi hanya untuk dirimu sendiri.', 'Correct! Your password is only for you.'],
                        ['d', 'Mengganti kata sandi menjadi 12345', 'Change the password to 12345', false, null, null],
                    ], 'c'),
            ],
        ];
    }

    /** @return array<string,array{0:string,1:string}> */
    private function hints(): array
    {
        $puzzle  = ['Mulailah dari kepingan sudut, lalu cocokkan warnanya.', 'Start with the corner pieces, then match the colours.'];
        $rumpang = ['Baca seluruh kalimat dulu, lalu cari kata yang paling cocok.', 'Read the whole sentence first, then find the word that fits best.'];
        $pilihan = ['Temukan kata kunci pertanyaan di dalam teks.', 'Find the key words of the question in the text.'];

        return [
            'tmg-1' => $puzzle,
            'tmg-2' => $rumpang,
            'tmg-3' => ['Cocokkan setiap pernyataan dengan isi teks, bukan dengan tebakan.', 'Check each statement against the text, not against a guess.'],
            'tmg-4' => ['Perhatikan benda yang memang berasal dari Temanggung.', 'Look for objects that really come from Temanggung.'],
            'tmg-5' => $pilihan,
            'mgl-1' => $puzzle,
            'mgl-2' => $rumpang,
            'mgl-3' => ["Pendapat biasanya memakai kata penilaian seperti 'paling indah' atau 'bagus'.", "Opinions usually use judging words such as 'most beautiful' or 'nice'."],
            'mgl-4' => ['Ingat kembali asal dan kegunaan setiap benda budaya.', 'Recall where each cultural object comes from and what it is used for.'],
            'mgl-5' => ['Cari bagian yang sama pada gambar dan pada teks.', 'Look for what appears in both the picture and the text.'],
            'wnb-1' => ['Cari langkah yang harus dilakukan paling awal.', 'Find the step that must be done first.'],
            'wnb-2' => ['Kesimpulan harus sesuai dengan isi paragraf.', 'The conclusion must match what the paragraph says.'],
            'wnb-3' => ['Periksa siapa yang membuat setiap sumber.', 'Check who made each source.'],
            'wnb-4' => ['Sumber terpercaya jelas siapa pembuatnya dan masih baru.', 'A trustworthy source clearly shows who made it and is up to date.'],
            'wnb-5' => ['Pikirkan akibat setiap tindakan bagi dirimu dan orang lain.', 'Think about what each action means for you and for others.'],
        ];
    }
}
