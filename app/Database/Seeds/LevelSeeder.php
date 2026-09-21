<?php

namespace App\Database\Seeds;

/**
 * Urutan wajib: Temanggung → Magelang → Wonosobo.
 * tp_id/tp_en dibiarkan NULL — diisi admin dari dokumen kurikulum.
 * Media peta/latar/lencana ditautkan oleh MediaAssetSeeder.
 */
class LevelSeeder extends GelitaSeeder
{
    public function run(): void
    {
        $levels = [
            [
                'sequence'   => 1,
                'code'       => 'temanggung',
                'name_id'    => 'Temanggung',
                'name_en'    => 'Temanggung',
                'difficulty' => 'mudah',
                'focus_id'   => 'Mengenali dan menemukan informasi langsung dari gambar, teks pendek, atau audio.',
                'focus_en'   => 'Recognising and finding information stated directly in pictures, short texts, or audio.',
                'cp_id'      => 'Peserta didik mampu memahami informasi sederhana dari teks, gambar, dan audio.',
                'cp_en'      => 'Learners are able to understand simple information from texts, pictures, and audio.',
                'intro_id'   => 'Jaka tiba di Temanggung, tanah subur di antara Gunung Sindoro dan Sumbing. Di sini ada kebun tembakau, kopi lereng gunung, dan candi kuno.',
                'intro_en'   => 'Jaka arrives in Temanggung, fertile land between Mount Sindoro and Sumbing. Here are tobacco gardens, mountain-slope coffee, and ancient temples.',
                'map_x'      => '65.00',
                'map_y'      => '41.00',
            ],
            [
                'sequence'   => 2,
                'code'       => 'magelang',
                'name_id'    => 'Magelang',
                'name_en'    => 'Magelang',
                'difficulty' => 'sedang',
                'focus_id'   => 'Menghubungkan dua atau lebih informasi.',
                'focus_en'   => 'Connecting two or more pieces of information.',
                'cp_id'      => 'Peserta didik mampu menemukan ide pokok, menghubungkan informasi, serta membedakan fakta dan pendapat.',
                'cp_en'      => 'Learners are able to find the main idea, connect information, and tell facts from opinions.',
                'intro_id'   => 'Jaka melangkah ke Magelang, tanah Candi Borobudur dan Gunung Tidar.',
                'intro_en'   => 'Jaka steps into Magelang, home of Borobudur Temple and Mount Tidar.',
                'map_x'      => '56.00',
                'map_y'      => '71.00',
            ],
            [
                'sequence'   => 3,
                'code'       => 'wonosobo',
                'name_id'    => 'Wonosobo',
                'name_en'    => 'Wonosobo',
                'difficulty' => 'sulit',
                'focus_id'   => 'Menilai informasi dan mengambil keputusan secara bertanggung jawab.',
                'focus_en'   => 'Evaluating information and making responsible decisions.',
                'cp_id'      => 'Peserta didik mampu menilai informasi, menyampaikan alasan, dan menggunakan informasi digital secara bertanggung jawab.',
                'cp_en'      => 'Learners are able to evaluate information, give reasons, and use digital information responsibly.',
                'intro_id'   => 'Serpihan cahaya terakhir ada di Wonosobo, negeri di atas awan Dataran Tinggi Dieng. Di sini Jaka belajar memilih informasi yang benar dan bersikap bijak di dunia digital.',
                'intro_en'   => 'The last Shards of Light are in Wonosobo, the cloud-topped Dieng Plateau. Here Jaka learns to choose true information and act wisely online.',
                'map_x'      => '28.00',
                'map_y'      => '55.00',
            ],
        ];

        foreach ($levels as $level) {
            $code = $level['code'];
            unset($level['code']);
            $this->insertIfMissing('levels', ['code' => $code], $level + [
                'tp_id'     => null,
                'tp_en'     => null,
                'is_active' => 1,
            ]);
        }
    }
}
