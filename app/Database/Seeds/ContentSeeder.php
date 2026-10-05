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
        // Ukuran papan Susun Gambar mengikuti tingkat wilayah (DifficultyRules)
        // dan dibekukan per soal saat soal dibuat, jadi tidak disimpan di sini.
        $puzzle   = static fn (array $extra = []) => $extra + ['items_per_round' => 1, 'allow_retry' => true];
        // Isi Rumpang kelas 5 = satu bacaan: semua soal aktif tampil, bank kata
        // berisi semua jawaban + sejumlah kata pengecoh.
        $bacaan   = static fn (int $blanks, int $distractors) => [
            'items_per_round'  => $blanks,
            'as_reading'       => true,
            'use_word_bank'    => true,
            'distractor_count' => $distractors,
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
                    'instruction_id' => 'Tukar dua kepingan gambar (3 × 3) sampai gambarnya utuh, lalu baca keterangannya.',
                    'instruction_en' => 'Swap two picture pieces at a time (3 × 3) until the picture is complete, then read the caption.',
                ],
                2 => [
                    'engine' => 'rumpang', 'variant' => 'rumpang_bacaan', 'indicator' => 'literasi',
                    'config' => $bacaan(3, 0),
                    'title_id' => 'Lengkapi Bacaan: Gunung Kembar',
                    'title_en' => 'Complete the Reading: The Twin Mountains',
                    'instruction_id' => 'Baca paragrafnya. Ketuk kotak kosong, lalu pilih kata yang tepat dari bank kata. Setiap kata dipakai satu kali.',
                    'instruction_en' => 'Read the paragraph. Tap an empty box, then choose the right word from the word bank. Each word is used once.',
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
                    'engine' => 'puzzle', 'variant' => 'puzzle_gambar', 'indicator' => 'budaya',
                    'config' => $puzzle(),
                    'title_id' => 'Susun Gambar Magelang',
                    'title_en' => 'Arrange the Magelang Picture',
                    'instruction_id' => 'Tukar dua kepingan gambar (4 × 4) sampai gambarnya utuh, lalu baca kisahnya.',
                    'instruction_en' => 'Swap two picture pieces at a time (4 × 4) until the picture is complete, then read its story.',
                ],
                2 => [
                    'engine' => 'rumpang', 'variant' => 'rumpang_bacaan', 'indicator' => 'literasi',
                    'config' => $bacaan(5, 2),
                    'title_id' => 'Lengkapi Bacaan: Candi Borobudur',
                    'title_en' => 'Complete the Reading: Borobudur Temple',
                    'instruction_id' => 'Baca bacaannya sampai habis. Pilih kata yang tepat untuk setiap kotak kosong; ada dua kata yang tidak terpakai.',
                    'instruction_en' => 'Read the whole text. Choose the right word for each empty box; two words will not be used.',
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
                    'engine' => 'puzzle', 'variant' => 'puzzle_jigsaw', 'indicator' => 'budaya',
                    'config' => $puzzle(),
                    'title_id' => 'Susun Gambar Wonosobo',
                    'title_en' => 'Arrange the Wonosobo Picture',
                    'instruction_id' => 'Kepingannya berbentuk puzzle (4 × 4). Tukar dua keping sampai gambarnya utuh, lalu baca keterangannya.',
                    'instruction_en' => 'The pieces are jigsaw-shaped (4 × 4). Swap two pieces at a time until the picture is complete, then read the caption.',
                ],
                2 => [
                    'engine' => 'rumpang', 'variant' => 'rumpang_bacaan', 'indicator' => 'literasi',
                    'config' => $bacaan(7, 2),
                    'title_id' => 'Lengkapi Bacaan: Negeri di Atas Awan',
                    'title_en' => 'Complete the Reading: The Land Above the Clouds',
                    'instruction_id' => 'Baca bacaannya sampai habis. Pilih kata yang tepat untuk setiap kotak kosong; ada dua kata yang tidak terpakai.',
                    'instruction_en' => 'Read the whole text. Choose the right word for each empty box; two words will not be used.',
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
