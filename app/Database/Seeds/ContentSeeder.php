<?php

namespace App\Database\Seeds;

/**
 * Struktur konten: 12 challenge_nodes (4 per wilayah, metadata saja), cerita
 * (data/story.php), dan pustaka. Judul, instruksi, deskripsi, dan isi soal
 * datang dari workbook bank soal kelas 5 (Dok 2); seeder hanya membuat
 * struktur dengan judul sementara. Baris yang sudah ada tidak ditimpa,
 * sehingga hasil impor/suntingan admin aman — kecuali reset total
 * (`gelita:reset`) yang mengosongkan tabel node lebih dulu.
 *
 * Urutan node sama di ketiga wilayah (perombakan kelas 5, K1):
 *   1 Susun Gambar (puzzle) · 2 Isi Rumpang (rumpang) ·
 *   3 Benar atau Salah (boleh) · 4 Cari Objek (cari)
 */
class ContentSeeder extends GelitaSeeder
{
    /** [engine, variant_code, indikator, map_x %, map_y %] per urutan node */
    public const NODES = [
        1 => ['puzzle', 'susun_gambar', 'budaya', '18.00', '70.00'],
        2 => ['rumpang', 'rumpang_bacaan', 'literasi', '40.00', '46.00'],
        3 => ['boleh', 'benar_salah_bacaan', 'literasi', '62.00', '68.00'],
        4 => ['cari', 'cari_objek', 'budaya', '82.00', '44.00'],
    ];

    /** Judul & instruksi sementara (diganti workbook bank soal) */
    private const TEXTS = [
        'puzzle' => [
            'Susun Gambar', 'Arrange the Picture',
            'Tukar dua kepingan gambar sampai gambarnya utuh, lalu baca keterangannya.',
            'Swap two picture pieces at a time until the picture is complete, then read the caption.',
        ],
        'rumpang' => [
            'Isi Rumpang', 'Fill in the Blanks',
            'Baca bacaannya. Ketuk kotak kosong, lalu pilih kata yang tepat dari bank kata.',
            'Read the text. Tap an empty box, then choose the right word from the word bank.',
        ],
        'boleh' => [
            'Benar atau Salah', 'True or False',
            'Baca bacaannya, lalu tentukan setiap pernyataan BENAR atau SALAH.',
            'Read the text, then decide whether each statement is TRUE or FALSE.',
        ],
        'cari' => [
            'Cari Objek', 'Find the Objects',
            'Temukan benda sesuai petunjuk di gambar. Hati-hati, ada benda pengecoh!',
            'Find the objects that match the clues in the picture. Careful, some are decoys!',
        ],
    ];

    public function run(): void
    {
        $this->seedNodes();

        $levels = $this->levelIds();
        $this->seedDialogues($levels);
        $this->seedLibrary($levels);
    }

    /**
     * Hanya struktur 12 tantangan — dipakai juga `gelita:reset`, yang tidak
     * boleh menyentuh dialog maupun Pustaka.
     */
    public function seedNodes(): void
    {
        $levels     = $this->levelIds();
        $indicators = [];
        $difficulty = [];

        foreach ($this->db->table('learning_indicators')->select('id, code')->get()->getResultArray() as $row) {
            $indicators[$row['code']] = (int) $row['id'];
        }

        foreach ($this->db->table('levels')->select('code, difficulty')->get()->getResultArray() as $row) {
            $difficulty[$row['code']] = (string) $row['difficulty'];
        }

        foreach (['temanggung', 'magelang', 'wonosobo'] as $levelCode) {
            if (! isset($levels[$levelCode])) {
                continue;
            }

            foreach (self::NODES as $sequence => [$engine, $variant, $indicator, $x, $y]) {
                [$titleId, $titleEn, $instructionId, $instructionEn] = self::TEXTS[$engine];

                $this->insertIfMissing('challenge_nodes', [
                    'level_id' => $levels[$levelCode],
                    'sequence' => $sequence,
                ], [
                    'engine_type'    => $engine,
                    'variant_code'   => $variant,
                    'title_id'       => $titleId,
                    'title_en'       => $titleEn,
                    'instruction_id' => $instructionId,
                    'instruction_en' => $instructionEn,
                    'indicator_id'   => $indicators[$indicator] ?? null,
                    'config_json'    => $this->toJson(self::config($engine, $difficulty[$levelCode] ?? null)),
                    'map_x'          => $x,
                    'map_y'          => $y,
                    'content_version' => '1',
                    'is_active'      => 1,
                ]);
            }
        }
    }

    /**
     * config_json bawaan per engine. Satu variasi acak per pengerjaan
     * (`selection = variant`); judul & pengecoh per variasi diisi workbook.
     * Ukuran papan Susun Gambar mengikuti tingkat wilayah (DifficultyRules)
     * dan dibekukan per soal saat soal dibuat, jadi tidak disimpan di sini.
     *
     * @return array<string, mixed>
     */
    public static function config(string $engine, ?string $difficulty = null): array
    {
        $base = ['selection' => 'variant', 'variants' => (object) []];

        return match ($engine) {
            'rumpang' => $base + [
                'as_reading'       => true,
                'use_word_bank'    => true,
                'distractor_count' => \App\Libraries\DifficultyRules::reading($difficulty)['default_distractors'] ?? 0,
            ],
            'boleh' => $base + ['verdict_options' => ['benar', 'salah']],
            default => $base,
        };
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
