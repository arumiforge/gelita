<?php

namespace App\Database\Seeds;

/**
 * Struktur konten: 15 challenge_nodes (metadata saja), cerita (data/story.php), dan pustaka.
 * Isi bank soal production dimuat lewat impor workbook (gelita:bank:import).
 * Baris yang sudah ada tidak ditimpa, sehingga hasil impor/suntingan admin aman.
 */
class ContentSeeder extends GelitaSeeder
{
    /** Posisi 5 pos di peta wilayah (persen), dapat diubah admin */
    private const NODE_POSITIONS = [
        1 => ['18.00', '72.00'],
        2 => ['34.00', '48.00'],
        3 => ['50.00', '68.00'],
        4 => ['66.00', '44.00'],
        5 => ['82.00', '62.00'],
    ];

    public function run(): void
    {
        $levels     = $this->levelIds();
        $indicators = [];

        foreach ($this->db->table('learning_indicators')->select('id, code')->get()->getResultArray() as $row) {
            $indicators[$row['code']] = (int) $row['id'];
        }

        foreach ($this->nodes() as $levelCode => $nodes) {
            foreach ($nodes as $sequence => $node) {
                [$x, $y] = self::NODE_POSITIONS[$sequence];

                $this->insertIfMissing('challenge_nodes', [
                    'level_id' => $levels[$levelCode],
                    'sequence' => $sequence,
                ], [
                    'engine_type'        => $node['engine'],
                    'variant_code'       => $node['variant'],
                    'title_id'           => $node['title_id'],
                    'title_en'           => $node['title_en'],
                    'instruction_id'     => $node['instruction_id'],
                    'instruction_en'     => $node['instruction_en'],
                    'indicator_id'       => $indicators[$node['indicator']],
                    'scoring_profile_id' => null,
                    'config_json'        => $this->toJson($node['config']),
                    'map_x'              => $x,
                    'map_y'              => $y,
                    'content_version'    => '1',
                    'is_active'          => 1,
                ]);
            }
        }

        $this->seedDialogues($levels);
        $this->seedLibrary($levels);
    }

    private function nodes(): array
    {
        $puzzle   = static fn (array $extra = []) => $extra + ['items_per_round' => 1, 'grid' => 3, 'allow_retry' => true];
        $rumpang  = static fn (bool $bank) => [
            'items_per_round'  => 4,
            'use_word_bank'    => $bank,
            'distractor_count' => $bank ? 2 : 0,
            'allow_retry'      => true,
            'distractors'      => [],
        ];
        $boleh    = static fn (int $n, array $verdicts, bool $reason) => [
            'items_per_round' => $n,
            'allow_retry'     => true,
            'verdict_options' => $verdicts,
            'require_reason'  => $reason,
        ];
        $pilihan  = static fn (int $n) => ['items_per_round' => $n, 'allow_retry' => false, 'shuffle_options' => true];

        return [
            'temanggung' => [
                1 => [
                    'engine' => 'puzzle', 'variant' => 'puzzle_gambar', 'indicator' => 'budaya',
                    'config' => $puzzle(),
                    'title_id' => 'Susun Gambar Budaya Temanggung',
                    'title_en' => 'Arrange the Temanggung Culture Picture',
                    'instruction_id' => 'Susun kepingan gambar (kisi 3×3) hingga utuh, lalu baca keterangannya.',
                    'instruction_en' => 'Arrange the picture pieces (3×3 grid) until the picture is complete, then read the caption.',
                ],
                2 => [
                    'engine' => 'rumpang', 'variant' => 'rumpang_bank_kata', 'indicator' => 'literasi',
                    'config' => $rumpang(true),
                    'title_id' => 'Lengkapi Kalimat Temanggung',
                    'title_en' => 'Complete the Temanggung Sentence',
                    'instruction_id' => 'Pilih satu kata yang tepat dari bank kata untuk mengisi ___.',
                    'instruction_en' => 'Choose the one correct word from the word bank to fill in the ___.',
                ],
                3 => [
                    'engine' => 'boleh', 'variant' => 'benar_salah_teks', 'indicator' => 'literasi',
                    'config' => $boleh(8, ['benar', 'salah'], false),
                    'title_id' => 'Benar atau Salah?',
                    'title_en' => 'True or False?',
                    'instruction_id' => 'Baca teks, lalu tentukan pernyataan BENAR atau SALAH.',
                    'instruction_en' => 'Read the text, then decide whether each statement is TRUE or FALSE.',
                ],
                4 => [
                    'engine' => 'cari', 'variant' => 'cocok_gambar_nama', 'indicator' => 'budaya',
                    'config' => ['items_per_round' => 4, 'allow_retry' => true, 'show_decoys' => true],
                    'title_id' => 'Temukan Budaya Temanggung',
                    'title_en' => 'Find the Temanggung Culture',
                    'instruction_id' => 'Klik gambar sesuai petunjuk. Hati-hati, ada yang bukan dari Temanggung!',
                    'instruction_en' => 'Click the picture that matches the clue. Careful, some are not from Temanggung!',
                ],
                5 => [
                    'engine' => 'pilihan', 'variant' => 'informasi_tersurat', 'indicator' => 'literasi',
                    'config' => $pilihan(3),
                    'title_id' => 'Temukan Jawabannya di Teks',
                    'title_en' => 'Find the Answer in the Text',
                    'instruction_id' => 'Baca teks, lalu pilih satu jawaban yang tepat.',
                    'instruction_en' => 'Read the text, then choose the one correct answer.',
                ],
            ],
            'magelang' => [
                1 => [
                    'engine' => 'puzzle', 'variant' => 'puzzle_topeng_ireng', 'indicator' => 'budaya',
                    'config' => $puzzle(),
                    'title_id' => 'Susun Topeng Ireng',
                    'title_en' => 'Arrange the Topeng Ireng',
                    'instruction_id' => 'Susun kepingan (3×3), lalu baca kisahnya.',
                    'instruction_en' => 'Arrange the pieces (3×3), then read the story.',
                ],
                2 => [
                    'engine' => 'rumpang', 'variant' => 'rumpang_tanpa_bank', 'indicator' => 'literasi',
                    'config' => $rumpang(false),
                    'title_id' => 'Ketik Jawabanmu',
                    'title_en' => 'Type Your Answer',
                    'instruction_id' => 'Ketik satu kata untuk mengisi ___.',
                    'instruction_en' => 'Type one word to fill in the ___.',
                ],
                3 => [
                    'engine' => 'boleh', 'variant' => 'benar_salah_beralasan', 'indicator' => 'sikap',
                    'config' => $boleh(6, ['benar', 'salah', 'pendapat'], true),
                    'title_id' => 'Benar, Salah, atau Pendapat?',
                    'title_en' => 'True, False, or Opinion?',
                    'instruction_id' => 'Tentukan benar, salah, atau pendapat, lalu tulis alasannya.',
                    'instruction_en' => 'Decide whether it is true, false, or an opinion, then write your reason.',
                ],
                4 => [
                    'engine' => 'pilihan', 'variant' => 'cocok_fungsi_asal', 'indicator' => 'budaya',
                    'config' => $pilihan(4),
                    'title_id' => 'Cocokkan Fungsi & Asal',
                    'title_en' => 'Match Function & Origin',
                    'instruction_id' => 'Pilih jawaban yang tepat.',
                    'instruction_en' => 'Choose the correct answer.',
                ],
                5 => [
                    'engine' => 'pilihan', 'variant' => 'banding_gambar_teks', 'indicator' => 'literasi',
                    'config' => $pilihan(3),
                    'title_id' => 'Bandingkan Gambar dan Teks',
                    'title_en' => 'Compare Picture and Text',
                    'instruction_id' => 'Hubungkan gambar dengan teks, lalu pilih jawaban.',
                    'instruction_en' => 'Connect the picture with the text, then choose the answer.',
                ],
            ],
            'wonosobo' => [
                1 => [
                    'engine' => 'puzzle', 'variant' => 'urutkan_informasi', 'indicator' => 'literasi',
                    'config' => ['items_per_round' => 1, 'allow_retry' => true],
                    'title_id' => 'Susun Urutan yang Benar',
                    'title_en' => 'Put in the Right Order',
                    'instruction_id' => 'Baca teks, lalu susun tahapan sesuai urutan.',
                    'instruction_en' => 'Read the text, then put the steps in the right order.',
                ],
                2 => [
                    'engine' => 'rumpang', 'variant' => 'rumpang_kesimpulan', 'indicator' => 'literasi',
                    'config' => $rumpang(true),
                    'title_id' => 'Lengkapi Kesimpulan',
                    'title_en' => 'Complete the Conclusion',
                    'instruction_id' => 'Baca paragraf, lalu pilih kata untuk kesimpulan yang tepat.',
                    'instruction_en' => 'Read the paragraph, then choose the word that makes the correct conclusion.',
                ],
                3 => [
                    'engine' => 'boleh', 'variant' => 'nilai_dua_sumber', 'indicator' => 'sikap',
                    'config' => $boleh(6, ['benar', 'salah'], false),
                    'title_id' => 'Sumber Mana yang Benar?',
                    'title_en' => 'Which Source Is Right?',
                    'instruction_id' => 'Bandingkan dua sumber, lalu nilai pernyataannya.',
                    'instruction_en' => 'Compare the two sources, then judge the statement.',
                ],
                4 => [
                    'engine' => 'pilihan', 'variant' => 'sumber_terpercaya', 'indicator' => 'sikap',
                    'config' => $pilihan(4),
                    'title_id' => 'Pilih Sumber Terpercaya',
                    'title_en' => 'Choose the Trusted Source',
                    'instruction_id' => 'Pilih sumber yang paling dapat dipercaya.',
                    'instruction_en' => 'Choose the most trustworthy source.',
                ],
                5 => [
                    'engine' => 'pilihan', 'variant' => 'tindakan_bertanggung_jawab', 'indicator' => 'sikap',
                    'config' => $pilihan(3),
                    'title_id' => 'Apa yang Sebaiknya Kamu Lakukan?',
                    'title_en' => 'What Should You Do?',
                    'instruction_id' => 'Pilih tindakan yang paling tepat dan bertanggung jawab.',
                    'instruction_en' => 'Choose the most appropriate and responsible action.',
                ],
            ],
        ];
    }

    /**
     * Seluruh narasi dan dialog dari data/story.php (salinan persis
     * docs/naskah-cerita.md). Baris yang sudah ada tidak ditimpa: server yang
     * sudah berjalan memperbarui ceritanya lewat `php spark gelita:story:update`.
     *
     * @param array<string,int> $levels
     */
    private function seedDialogues(array $levels): void
    {
        foreach (require __DIR__ . '/data/story.php' as $line) {
            if ($line['level'] !== null && ! isset($levels[$line['level']])) {
                continue;
            }

            // level_id NULL tidak dijaga UNIQUE oleh MySQL → cek manual lewat insertIfMissing
            $this->insertIfMissing('dialogues', [
                'level_id'     => $line['level'] === null ? null : $levels[$line['level']],
                'context_code' => $line['context'],
                'sequence'     => $line['sequence'],
            ], [
                'character_code' => $line['character'],
                'pose'           => $line['pose'],
                'effect'         => $line['effect'],
                'title_id'       => $line['title_id'],
                'title_en'       => $line['title_en'],
                'text_id'        => $line['text_id'],
                'text_en'        => $line['text_en'],
                'is_active'      => 1,
            ]);
        }
    }

    /** @param array<string,int> $levels */
    private function seedLibrary(array $levels): void
    {
        $pages = [
            'temanggung' => [
                'Mengenal Temanggung', 'Getting to Know Temanggung',
                "Temanggung berada di antara Gunung Sindoro dan Gunung Sumbing. Tanahnya subur, sehingga banyak warga bertani di lereng gunung.\n\nTemanggung dikenal dengan kebun tembakau dan kopi lereng gunung. Di wilayah ini juga terdapat candi kuno yang menjadi warisan budaya dan perlu dijaga bersama.",
                "Temanggung lies between Mount Sindoro and Mount Sumbing. Its soil is fertile, so many people farm on the mountain slopes.\n\nTemanggung is known for its tobacco gardens and mountain-slope coffee. The region also has ancient temples, a cultural heritage that we must look after together.",
            ],
            'magelang' => [
                'Mengenal Magelang', 'Getting to Know Magelang',
                "Magelang adalah tempat berdirinya Candi Borobudur, candi Buddha yang terkenal di seluruh dunia. Dinding candinya dihiasi banyak relief yang menceritakan kisah.\n\nDi Magelang juga ada Gunung Tidar. Selain itu, Magelang memiliki kesenian Topeng Ireng yang ditarikan dengan gerak yang bersemangat.",
                "Magelang is home to Borobudur Temple, a Buddhist temple famous throughout the world. Its walls are decorated with many reliefs that tell stories.\n\nMagelang also has Mount Tidar. In addition, Magelang has the Topeng Ireng art, performed with lively dance movements.",
            ],
            'wonosobo' => [
                'Mengenal Wonosobo', 'Getting to Know Wonosobo',
                "Wonosobo berada di daerah pegunungan dan menjadi pintu menuju Dataran Tinggi Dieng. Dieng sering disebut negeri di atas awan karena letaknya tinggi dan sering diselimuti kabut.\n\nUdaranya sejuk, bahkan dingin pada pagi hari. Sebelum berkunjung, bacalah informasi dari sumber resmi agar perjalananmu aman.",
                "Wonosobo lies in the mountains and is the gateway to the Dieng Plateau. Dieng is often called the land above the clouds because it is high up and often covered in mist.\n\nThe air is cool, even cold in the morning. Before visiting, read information from official sources so your trip is safe.",
            ],
        ];

        foreach ($pages as $levelCode => [$titleId, $titleEn, $bodyId, $bodyEn]) {
            $this->insertIfMissing('library_pages', [
                'level_id' => $levels[$levelCode],
                'sequence' => 1,
            ], [
                'title_id'  => $titleId,
                'title_en'  => $titleEn,
                'body_id'   => $bodyId,
                'body_en'   => $bodyEn,
                'is_active' => 1,
            ]);
        }
    }
}
