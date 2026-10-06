<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Panduan workbook bank soal untuk guru/admin yang mengisi Excel.
 *
 * Pembacanya guru dan admin sekolah, bukan orang teknis: tulis semua
 * penjelasan dalam bahasa sehari-hari dan sebut menu panel dengan nama yang
 * tampil di layar (mis. "Gambar & suara", "Impor soal (Excel)").
 *
 * Header sheet sengaja berbahasa Inggris (kode kolom yang dibaca importer),
 * jadi setiap workbook yang dibuat lewat kelas ini membawa penjelasannya
 * sendiri:
 *   1. sheet PETUNJUK  — langkah mengisi & mengimpor, arti tiap sheet, kunci
 *                        jawaban per jenis soal, format teks Pustaka;
 *   2. sheet KAMUS_KOLOM — setiap kolom: nama Indonesia, wajib/tidak, arti,
 *                        isian yang boleh, contoh;
 *   3. di sheet data   — header diwarnai (wajib/bersyarat/opsional), catatan
 *                        (comment) di setiap header, pesan bantuan saat sel
 *                        dipilih, dan daftar pilihan untuk kolom berkode.
 *
 * Importer (ContentImportService) hanya membaca sheet berkode dan baris
 * pertamanya, sehingga semua bantuan ini tidak pernah ikut terimpor.
 * Kelas ini tidak bergantung pada framework (hanya PhpSpreadsheet), jadi
 * dapat dipakai skrip penyusun workbook di luar aplikasi.
 */
class BankWorkbookGuide
{
    public const LEVELS = ['temanggung', 'magelang', 'wonosobo'];

    /** 4 tantangan per wilayah (perombakan kelas 5): 1 Susun Gambar, 2 Isi Rumpang, 3 Benar atau Salah, 4 Cari Objek */
    public const NODE_REFS = [
        'tmg-1', 'tmg-2', 'tmg-3', 'tmg-4',
        'mgl-1', 'mgl-2', 'mgl-3', 'mgl-4',
        'wnb-1', 'wnb-2', 'wnb-3', 'wnb-4',
    ];

    /** Variasi soal; setiap pengerjaan memakai satu variasi acak. */
    public const VARIANT_GROUPS = ['a', 'b', 'c'];

    /** Urutan & kegunaan sheet data. [judul Indonesia, fungsi, cara mengisi] */
    public const SHEETS = [
        'nodes'         => ['Tantangan', 'Judul, perintah, kartu misi, dan pengaturan 12 tantangan yang SUDAH ADA (tmg-1 … wnb-4). Sheet ini tidak membuat tantangan baru.', 'Satu baris untuk satu tantangan. Sel yang dikosongkan = isi lama tetap dipakai.'],
        'variants'      => ['Judul variasi', 'Judul tampil setiap variasi soal (a, b, c) satu tantangan. Setiap kali bermain, siswa mendapat SATU variasi yang dipilih acak. Benar atau Salah memakai judul bacaannya.', 'Satu baris untuk satu variasi. Kosong = judul tantangan yang dipakai.'],
        'distractors'   => ['Kata pengecoh', 'Kata-kata yang SALAH tetapi ikut muncul di pilihan kata tantangan Isi Rumpang, per variasi, supaya siswa perlu berpikir.', 'Satu baris untuk satu kata. Semua kata pengecoh satu variasi diganti dengan isi sheet ini.'],
        'passages'      => ['Teks bacaan', 'Bacaan panjang yang tampil di samping soal. Soal memakainya lewat kolom passage_key di sheet items.', 'Satu baris untuk satu bacaan. Isi bahasa Indonesia dan Inggris wajib diisi.'],
        'items'         => ['Soal', 'Bank soal: setiap rumpang, pernyataan, gambar Susun Gambar, atau benda yang dicari — dikelompokkan per variasi.', 'Satu baris untuk satu soal. Setiap kali bermain, siswa mendapat SEMUA soal aktif dari satu variasi yang dipilih acak.'],
        'hints'         => ['Petunjuk', 'Petunjuk yang boleh dibuka siswa saat kesulitan (tercatat sebagai data pemakaian bantuan; tidak mengurangi bintang).', 'Satu baris untuk satu petunjuk: untuk satu tantangan (isi node_ref) ATAU untuk satu soal (isi item_key).'],
        'library'       => ['Pustaka Kedu — halaman', 'Halaman buku bacaan setiap wilayah di menu Pustaka.', 'Satu baris untuk satu halaman. Halaman dengan nomor yang sudah ada akan ditimpa.'],
        'library_media' => ['Pustaka Kedu — gambar & video', 'Galeri gambar/video setiap halaman Pustaka: berkas yang sudah diunggah ATAU tautan YouTube, Google Drive, Vimeo, atau Wikimedia Commons.', 'Satu baris untuk satu gambar/video. Galeri halaman yang punya baris di sini diganti seluruhnya.'],
    ];

    /**
     * Kamus kolom: [nama Indonesia, status (Wajib|Bersyarat|Opsional), arti & cara mengisi, isian yang boleh, contoh].
     * Nama Indonesia dipakai juga di pesan pemeriksaan impor (ContentImportService),
     * jadi usahakan tetap pendek (≤ 32 huruf muat di judul kotak bantuan Excel).
     *
     * @var array<string, array<string, array{0: string, 1: string, 2: string, 3: string, 4: string}>>
     */
    public const COLUMNS = [
        'nodes' => [
            'node_ref'             => ['Kode tantangan', 'Wajib', 'Tantangan yang diubah: singkatan wilayah + nomor pos di peta.', 'tmg-1 … tmg-4 (Temanggung), mgl-1 … mgl-4 (Magelang), wnb-1 … wnb-4 (Wonosobo)', 'tmg-2'],
            'title_id'             => ['Judul (Indonesia)', 'Opsional', 'Judul tantangan di peta wilayah dan di kartu misi. Kosong = judul lama tetap dipakai.', 'teks, paling banyak 150 huruf', 'Lengkapi Kalimat Temanggung'],
            'title_en'             => ['Judul (Inggris)', 'Opsional', 'Terjemahan judul untuk siswa yang memilih bahasa Inggris.', 'teks, paling banyak 150 huruf', 'Complete the Temanggung Sentence'],
            'instruction_id'       => ['Perintah (Indonesia)', 'Opsional', 'Perintah singkat yang tampil di atas area permainan.', 'teks', 'Pilih satu kata yang tepat dari bank kata.'],
            'instruction_en'       => ['Perintah (Inggris)', 'Opsional', 'Terjemahan perintah.', 'teks', 'Choose the one correct word from the word bank.'],
            'description_id'       => ['Kartu misi (Indonesia)', 'Opsional', 'Cerita atau tujuan singkat di kartu misi sebelum tantangan dimulai.', 'teks, 1–3 kalimat', 'Mbah Kedu meminta Jaka melengkapi catatan perjalanan.'],
            'description_en'       => ['Kartu misi (Inggris)', 'Opsional', 'Terjemahan kartu misi.', 'teks', 'Mbah Kedu asks Jaka to complete the travel notes.'],
            'distractor_count'     => ['Jumlah kata pengecoh', 'Bersyarat', 'Khusus Isi Rumpang: berapa kata pengecoh (dari sheet distractors) yang ikut tampil di bank kata. Aturan kelas 5: Temanggung 0, Magelang 2, Wonosobo 2; setiap variasi wajib punya kata pengecoh sebanyak angka ini.', 'angka bulat 0 ke atas', '2'],
            'scene_media_key'      => ['Gambar adegan', 'Bersyarat', 'Khusus Cari Objek (tantangan 4): kode berkas gambar adegan 16:9 (1280 × 720 px) tempat benda-benda diletakkan. Unggah gambarnya di menu Gambar & suara; sebelum itu sistem memasang gambar contoh.', 'kode berkas gambar', 'challenge.tmg-4.scene'],
            'scene_media_label'    => ['Nama gambar adegan', 'Opsional', 'Nama pendek gambar adegan (paling banyak 120 huruf), tampil di halaman Media dan di gambar contoh. Hanya mengisi slot yang namanya masih kosong.', 'teks pendek', 'Pasar Budaya Temanggung'],
            'scene_media_note'     => ['Isi gambar adegan', 'Opsional', 'Isi gambar adegan yang diharapkan (paling banyak 500 huruf), tampil di halaman Media agar admin tahu gambar apa yang harus diunggah.', 'teks', 'halaman pasar budaya, tiga deret rak kosong'],
            'background_media_key' => ['Gambar latar tantangan', 'Opsional', 'Kode berkas gambar latar layar tantangan. Kosong = memakai latar wilayah.', 'kode berkas gambar', 'challenge.tmg-4.bg'],
            'variant_code'         => ['Kode variasi tampilan', 'Opsional', 'Nama variasi tantangan untuk keperluan tampilan: susun_gambar, rumpang_bacaan, benar_salah_bacaan, atau cari_objek. Kosong = tidak diubah. Biarkan seperti di workbook bank soal kelas 5 bila ragu.', 'huruf kecil, angka, dan garis bawah', 'rumpang_bacaan'],
            'indicator'            => ['Indikator tantangan', 'Opsional', 'Indikator pembelajaran yang diukur tantangan ini; dipakai untuk soal yang kolom indicator-nya kosong. Kosong = tidak diubah. Bila indikator tantangan diganti, isi indikator soal lama secara tertulis agar hasil lama tetap terhitung di indikator semula.', 'literasi | budaya | sikap', 'budaya'],
        ],
        'variants' => [
            'node_ref'      => ['Kode tantangan', 'Wajib', 'Tantangan pemilik variasi.', 'tmg-1 … wnb-4', 'tmg-2'],
            'variant_group' => ['Variasi', 'Wajib', 'Kode variasi; sama dengan kolom variant_group di sheet items.', 'a | b | c', 'a'],
            'title_id'      => ['Judul variasi (Indonesia)', 'Opsional', 'Judul yang tampil saat siswa mendapat variasi ini. Kosong = judul tantangan.', 'teks, paling banyak 150 huruf', 'Gunung Kembar'],
            'title_en'      => ['Judul variasi (Inggris)', 'Opsional', 'Terjemahan judul variasi.', 'teks, paling banyak 150 huruf', 'The Twin Mountains'],
        ],
        'distractors' => [
            'node_ref'      => ['Kode tantangan', 'Wajib', 'Tantangan Isi Rumpang.', 'tmg-2, mgl-2, wnb-2', 'tmg-2'],
            'variant_group' => ['Variasi', 'Wajib', 'Variasi pemilik kata pengecoh ini.', 'a | b | c', 'a'],
            'text_id'       => ['Kata pengecoh (Indonesia)', 'Wajib', 'Kata yang TIDAK tepat untuk tempat kosong mana pun, tetapi masuk akal sehingga siswa perlu berpikir.', 'satu kata / frasa pendek', 'salju'],
            'text_en'       => ['Kata pengecoh (Inggris)', 'Opsional', 'Terjemahan kata pengecoh. Kosong = sama dengan kolom Indonesia.', 'satu kata / frasa pendek', 'snow'],
        ],
        'passages' => [
            'passage_key'      => ['Kode bacaan', 'Wajib', 'Kode unik teks bacaan; ditulis juga di kolom passage_key sheet items untuk menyambungkan soal dengan bacaannya. Bacaan Benar atau Salah kelas 5: {tantangan}-{variasi}. Jangan diubah setelah dipakai.', 'huruf kecil, angka, dan tanda minus; tidak boleh kembar', 'tmg-3-a'],
            'level_code'       => ['Wilayah', 'Wajib', 'Wilayah pemilik bacaan. Bacaan hanya boleh dipakai soal di wilayah yang sama.', 'temanggung | magelang | wonosobo', 'temanggung'],
            'title_id'         => ['Judul bacaan (Indonesia)', 'Opsional', 'Judul di atas teks bacaan; juga judul variasi Benar atau Salah.', 'teks', 'Situs Liyangan'],
            'title_en'         => ['Judul bacaan (Inggris)', 'Opsional', 'Terjemahan judul.', 'teks', 'The Liyangan Site'],
            'body_id'          => ['Isi bacaan (Indonesia)', 'Wajib', 'Teks yang dibaca siswa kelas 5 SD/MI: kalimat pendek dan jelas. Baris kosong memisahkan paragraf (Benar atau Salah: Temanggung 1, Magelang 2, Wonosobo 3 paragraf).', 'teks panjang; baris kosong = paragraf baru', 'Situs Liyangan berada di …'],
            'body_en'          => ['Isi bacaan (Inggris)', 'Wajib', 'Terjemahan isi bacaan (jumlah paragrafnya sama).', 'teks panjang', 'The Liyangan Site is in …'],
            'media_asset_key'  => ['Gambar bacaan', 'Opsional', 'Kode berkas gambar pendamping bacaan (unggah di menu Gambar & suara).', 'kode berkas gambar', 'passage.tmg-3-a'],
            'reference_source' => ['Sumber rujukan', 'Opsional', 'Asal fakta dalam bacaan (buku, situs resmi, jurnal).', 'teks', 'Pemkab Temanggung; BPS Temanggung 2024'],
        ],
        'items' => [
            'item_key'          => ['Kode soal', 'Wajib', 'Kode unik soal: kode tantangan + variasi + nomor 2 angka. JANGAN diubah setelah soal dijawab siswa, karena jawaban siswa tersambung ke kode ini (data penelitian).', '{tantangan}-{variasi}{nn}', 'tmg-2-a01'],
            'node_ref'          => ['Kode tantangan', 'Wajib', 'Tantangan tempat soal ini muncul.', 'tmg-1 … wnb-4', 'tmg-2'],
            'variant_group'     => ['Variasi', 'Wajib', 'Kelompok soal yang tampil bersama dalam satu pengerjaan. Kosong = a.', 'a | b | c', 'a'],
            'sequence'          => ['Urutan dalam variasi', 'Opsional', 'Urutan tampil soal di dalam variasinya (Isi Rumpang: urutan potongan bacaan).', 'angka bulat', '1'],
            'interaction_type'  => ['Jenis soal', 'Wajib', 'Harus cocok dengan jenis tantangannya. Susun Gambar: puzzle_arrange · Isi Rumpang: fill_blank_bank · Benar atau Salah: verdict_card · Cari Objek: find_object.', 'puzzle_arrange | fill_blank_bank | verdict_card | find_object', 'fill_blank_bank'],
            'indicator'         => ['Indikator', 'Opsional', 'Indikator pembelajaran yang diukur soal ini. Kosong = mengikuti indikator tantangannya.', 'literasi | budaya | sikap', 'literasi'],
            'bloom_level'       => ['Tingkat berpikir (Bloom)', 'Bersyarat', 'Tingkat berpikir soal menurut Taksonomi Bloom revisi: C1 mengingat, C2 memahami, C3 menerapkan, C4 menganalisis, C5 mengevaluasi, C6 mencipta. WAJIB untuk Isi Rumpang, Benar atau Salah, dan benda yang dicari di Cari Objek (Susun Gambar dan benda pengecoh tanpa Bloom). Rentang yang dianjurkan untuk kelas 5 ada di sheet PETUNJUK.', 'C1 | C2 | C3 | C4 | C5 | C6', 'C2'],
            'skill'             => ['Keterampilan membaca', 'Bersyarat', 'Khusus Benar atau Salah (WAJIB): keterampilan yang diukur pernyataan ini. Temanggung: informasi_tersurat, mengenali_fakta, mengenali_gambar · Magelang: gagasan_pokok, menghubungkan_informasi, mengklasifikasikan_tindakan · Wonosobo: membandingkan_informasi, menilai_pilihan, menentukan_solusi.', 'kode keterampilan', 'informasi_tersurat'],
            'prompt_id'         => ['Soal / pernyataan (Indonesia)', 'Wajib', 'Isi Rumpang: satu potongan bacaan dengan TEPAT SATU tempat kosong ___ (tiga garis bawah) yang tidak boleh berada di awal potongan; potongan satu variasi disambung menjadi satu bacaan, dan baris kosong DI TENGAH sel (setelah ___) memulai paragraf baru. Cari Objek: petunjuk benda yang harus dicari (benda pengecoh: nama bendanya). Susun Gambar: keterangan gambar yang tampil setelah gambar tersusun. Benar atau Salah: pernyataan yang dinilai.', 'teks', 'Temanggung terletak di antara Gunung Sindoro dan Gunung ___.'],
            'prompt_en'         => ['Soal / pernyataan (Inggris)', 'Opsional', 'Terjemahan soal (Isi Rumpang: tetap pakai ___). Kosong = siswa yang memilih bahasa Inggris melihat teks Indonesia.', 'teks', 'Temanggung lies between Mount Sindoro and Mount ___.'],
            'passage_key'       => ['Kode bacaan', 'Bersyarat', 'Khusus Benar atau Salah (WAJIB): bacaan variasi ini, kode dari sheet passages ({tantangan}-{variasi}, mis. tmg-3-a). Semua pernyataan satu variasi memakai bacaan yang sama.', 'kode bacaan', 'tmg-3-a'],
            'answer_id'         => ['Kunci jawaban (Indonesia)', 'Bersyarat', 'Lihat tabel "Kunci jawaban untuk setiap jenis soal" di sheet PETUNJUK.', 'tergantung jenis soal', 'Sumbing'],
            'answer_en'         => ['Kunci jawaban (Inggris)', 'Bersyarat', 'Untuk Isi Rumpang: jawaban dalam bahasa Inggris. Kosong = sama dengan kolom Indonesia.', 'tergantung jenis soal', 'Sumbing'],
            'x'                 => ['Posisi dari kiri (%)', 'Bersyarat', 'Khusus find_object: jarak benda dari tepi KIRI gambar adegan, dalam persen lebar gambar.', 'angka 0–100', '18'],
            'y'                 => ['Posisi dari atas (%)', 'Bersyarat', 'Khusus find_object: jarak benda dari tepi ATAS gambar adegan, dalam persen tinggi gambar.', 'angka 0–100', '62'],
            'w'                 => ['Lebar benda (%)', 'Bersyarat', 'Khusus find_object: lebar benda dalam persen lebar gambar adegan. Jaga agar benda-benda tidak saling bertumpuk.', 'angka 5–30', '12'],
            'decoy'             => ['Benda jebakan', 'Bersyarat', 'Khusus find_object: 1 = benda jebakan (bukan dari wilayah ini). Jebakan selalu tampil, tidak dinilai (scorable = 0), dan wajib punya penjelasan jebakan (wrong_feedback_id).', '0 atau 1', '0'],
            'wrong_feedback_id' => ['Penjelasan jebakan (Indonesia)', 'Bersyarat', 'Khusus benda jebakan: penjelasan yang tampil saat siswa mengklik jebakan.', 'teks', 'Angklung berasal dari Jawa Barat, bukan Temanggung.'],
            'wrong_feedback_en' => ['Penjelasan jebakan (Inggris)', 'Bersyarat', 'Terjemahan penjelasan jebakan.', 'teks', 'Angklung comes from West Java, not Temanggung.'],
            'media_asset_key'   => ['Gambar soal', 'Bersyarat', 'WAJIB untuk puzzle_arrange (satu gambar utuh persegi ≥ 1200 px; sistem memotongnya otomatis sesuai tingkat wilayah: Temanggung 3 × 3 kotak, Magelang 4 × 4 kotak, Wonosobo 4 × 4 jigsaw) dan find_object (gambar benda 240 × 240 px, PNG berlatar transparan). Pernyataan bergambar Benar atau Salah: 480 × 360 px. Sebelum gambar asli diunggah di menu Gambar & suara, sistem memasang gambar contoh.', 'kode berkas gambar', 'challenge.item.tmg-1-01'],
            'media_label'       => ['Nama gambar', 'Opsional', 'Nama pendek gambar/benda (paling banyak 120 huruf), mis. "Kuda kepang bambu". Tampil di halaman Media dan ditulis di gambar contoh. Hanya mengisi slot yang namanya masih kosong.', 'teks pendek', 'Kuda kepang bambu'],
            'media_note'        => ['Isi gambar yang diharapkan', 'Opsional', 'Isi gambar asli yang harus disiapkan admin (paling banyak 500 huruf). Tampil di halaman Media. Hanya mengisi slot yang keterangannya masih kosong.', 'teks', 'kuda kepang anyaman bambu berwarna (tanpa penari)'],
            'scorable'          => ['Dinilai?', 'Opsional', '1 = soal dinilai (bawaan). 0 = tidak dinilai (wajib untuk benda jebakan).', '0 atau 1', '1'],
            'is_active'         => ['Soal dipakai?', 'Opsional', '1 = soal dipakai di permainan; 0 = soal diistirahatkan (tidak muncul lagi, tetapi jawaban siswa yang sudah ada tetap tersimpan). Jangan menghapus baris soal lama: istirahatkan dengan 0. Kosong = soal baru dipakai, soal lama tidak diubah.', '0 atau 1', '1'],
            'review_status'     => ['Status pemeriksaan isi', 'Opsional', 'draft = masih draf; needs_verification = fakta perlu dicek (muncul peringatan saat diimpor); verified = sudah dicek ahli/guru. Bank soal kelas 5 seluruhnya verified (disetujui peneliti).', 'draft | needs_verification | verified', 'verified'],
            'review_note'       => ['Catatan pemeriksaan', 'Opsional', 'Catatan untuk pemeriksa isi: apa yang perlu dicek atau sudah dicek.', 'teks', 'Tinggi gunung dicek dengan data PVMBG.'],
            'reference_source'  => ['Sumber rujukan', 'Opsional', 'Asal fakta dalam soal (disarankan untuk soal budaya/sejarah).', 'teks', 'UNESCO World Heritage Centre, Borobudur'],
        ],
        'hints' => [
            'node_ref' => ['Kode tantangan', 'Bersyarat', 'Isi untuk petunjuk umum satu tantangan. Isi SALAH SATU saja: node_ref atau item_key.', 'tmg-1 … wnb-4', 'tmg-2'],
            'item_key' => ['Kode soal', 'Bersyarat', 'Isi untuk petunjuk khusus satu soal.', 'kode soal', 'tmg-2-a01'],
            'sequence' => ['Urutan petunjuk', 'Opsional', '1 = petunjuk yang terbuka pertama, 2 = berikutnya, dan seterusnya.', 'angka bulat 1 ke atas', '1'],
            'text_id'  => ['Petunjuk (Indonesia)', 'Wajib', 'Arahan cara berpikir — jangan langsung memberi jawaban.', 'teks', 'Baca seluruh kalimat dulu.'],
            'text_en'  => ['Petunjuk (Inggris)', 'Opsional', 'Terjemahan petunjuk.', 'teks', 'Read the whole sentence first.'],
        ],
        'library' => [
            'level_code' => ['Wilayah', 'Wajib', 'Wilayah pemilik halaman Pustaka.', 'temanggung | magelang | wonosobo', 'temanggung'],
            'sequence'   => ['Nomor halaman', 'Wajib', 'Urutan halaman di buku. Nomor yang sudah ada = halaman itu DITIMPA.', 'angka 1–999', '1'],
            'title_id'   => ['Judul halaman (Indonesia)', 'Wajib', 'Judul halaman buku.', 'teks, paling banyak 250 huruf', 'Mengenal Temanggung'],
            'title_en'   => ['Judul halaman (Inggris)', 'Wajib', 'Terjemahan judul.', 'teks, paling banyak 250 huruf', 'Getting to Know Temanggung'],
            'body_id'    => ['Isi halaman (Indonesia)', 'Wajib', 'Teks halaman dengan format sederhana (lihat sheet PETUNJUK): baris kosong = paragraf baru, "## " = subjudul, "- " = daftar berpoin, "**tebal**", "*miring*", "> " = kotak fakta, "Sumber:" = catatan sumber.', 'teks panjang', '## Tanah yang subur …'],
            'body_en'    => ['Isi halaman (Inggris)', 'Opsional', 'Terjemahan isi (formatnya sama). Kosong = siswa yang memilih bahasa Inggris membaca teks Indonesia.', 'teks panjang', '## Fertile land …'],
            'is_active'  => ['Tampil?', 'Opsional', '1 = tampil di permainan (bawaan); 0 = disembunyikan.', '0 atau 1', '1'],
        ],
        'library_media' => [
            'level_code'       => ['Wilayah', 'Wajib', 'Wilayah halaman tujuan.', 'temanggung | magelang | wonosobo', 'temanggung'],
            'page_sequence'    => ['Nomor halaman', 'Wajib', 'Nomor halaman Pustaka tempat gambar/video ini tampil (dari sheet library atau halaman yang sudah ada).', 'angka 1 ke atas', '1'],
            'sequence'         => ['Urutan gambar/video', 'Opsional', 'Urutan di galeri. Yang pertama tampil paling besar.', 'angka 1 ke atas', '1'],
            'media_kind'       => ['Jenis media', 'Opsional', 'image = gambar; video = video. Tautan YouTube/Vimeo otomatis dianggap video.', 'image | video', 'image'],
            'media_asset_key'  => ['Berkas yang sudah diunggah', 'Bersyarat', 'Kode berkas gambar/video yang sudah diunggah di menu Gambar & suara. Isi SALAH SATU saja: kolom ini atau external_url.', 'kode berkas', 'library.temanggung.p1.1'],
            'external_url'     => ['Tautan', 'Bersyarat', 'Tautan YouTube, Google Drive (dibagikan ke "siapa saja yang memiliki link"), Vimeo, halaman berkas Wikimedia Commons, atau alamat langsung berkas .jpg/.png/.mp4. Video baru dimuat saat siswa menekan Putar.', 'https://…', 'https://youtu.be/xxxxxxxxxxx'],
            'poster_media_key' => ['Gambar sampul video', 'Opsional', 'Kode berkas gambar sampul untuk video yang diunggah sendiri (bukan tautan).', 'kode berkas gambar', 'library.temanggung.p1.2.poster'],
            'caption_id'       => ['Keterangan (Indonesia)', 'Opsional', 'Keterangan singkat di bawah gambar/video; juga dibacakan untuk siswa yang memakai pembaca layar.', 'teks, paling banyak 500 huruf', 'Gunung Sindoro dilihat dari Kledung'],
            'caption_en'       => ['Keterangan (Inggris)', 'Opsional', 'Terjemahan keterangan.', 'teks, paling banyak 500 huruf', 'Mount Sindoro seen from Kledung'],
            'credit'           => ['Pemilik & izin pakai', 'Opsional', 'Pemilik dan lisensi gambar/video milik pihak lain. Wajib diisi bila memakai foto/video orang lain.', 'teks, paling banyak 300 huruf', 'Wikimedia Commons, CC BY-SA 4.0'],
        ],
    ];

    /** Arti status kolom dalam kalimat sehari-hari (catatan header & kotak bantuan). */
    private const STATUS_TEXT = [
        'Wajib'     => 'wajib diisi',
        'Bersyarat' => 'wajib untuk keadaan tertentu',
        'Opsional'  => 'boleh dikosongkan',
    ];

    /** Daftar pilihan (dropdown) per kolom. */
    public const CHOICES = [
        'node_ref'         => self::NODE_REFS,
        'level_code'       => self::LEVELS,
        'variant_group'    => self::VARIANT_GROUPS,
        'interaction_type' => ['puzzle_arrange', 'fill_blank_bank', 'verdict_card', 'find_object'],
        'indicator'        => ['literasi', 'budaya', 'sikap'],
        'review_status'    => ['draft', 'needs_verification', 'verified'],
        'media_kind'       => ['image', 'video'],
        'decoy'            => ['0', '1'],
        'scorable'         => ['0', '1'],
        'is_active'        => ['0', '1'],
        'bloom_level'      => ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
        'skill'            => [
            'informasi_tersurat', 'mengenali_fakta', 'mengenali_gambar',
            'gagasan_pokok', 'menghubungkan_informasi', 'mengklasifikasikan_tindakan',
            'membandingkan_informasi', 'menilai_pilihan', 'menentukan_solusi',
        ],
    ];

    /** Kolom angka: [jenis, min, maks]. */
    private const NUMBERS = [
        'distractor_count' => ['whole', 0, 50],
        'sequence'         => ['whole', 1, 999],
        'page_sequence'    => ['whole', 1, 999],
        'x'                => ['decimal', 0, 100],
        'y'                => ['decimal', 0, 100],
        'w'                => ['decimal', 1, 60],
    ];

    /** Kolom teks panjang: lebar kolom besar + teks terbungkus. */
    private const LONG = [
        'body_id', 'body_en', 'prompt_id', 'prompt_en', 'description_id', 'description_en', 'media_note', 'scene_media_note',
        'instruction_id', 'instruction_en', 'text_id', 'text_en',
        'wrong_feedback_id', 'wrong_feedback_en', 'reference_source', 'review_note', 'caption_id', 'caption_en', 'credit', 'external_url',
        'title_id', 'title_en',
    ];

    private const ROWS_WITH_VALIDATION = 1000;

    private const COLORS = [
        'Wajib'     => ['C7A265', '1B1B1B'],
        'Bersyarat' => ['F0DFB8', '1B1B1B'],
        'Opsional'  => ['DDE3EE', '1B1B1B'],
    ];

    /**
     * Tulis workbook berpanduan.
     *
     * @param array<string, list<list<string|int|float|null>>> $rows   baris data per sheet, urut sesuai header
     * @param array{title?: string, subtitle?: string, sheets?: list<string>} $meta
     *        `sheets` = sheet data yang ditulis (bawaan: semua). Workbook bank soal kelas 5
     *        tanpa library/library_media; workbook Pustaka hanya keduanya.
     * @param array<string, array{note?: string, headers: list<string>, rows: list<list<string|int|float|null>>}> $extraSheets
     *        sheet tambahan (mis. daftar media) — diabaikan importer
     */
    public function write(array $rows, string $path, array $meta = [], array $extraSheets = []): void
    {
        $sheets = $meta['sheets'] ?? array_keys(self::COLUMNS);
        $sheets = array_values(array_filter(array_keys(self::COLUMNS), static fn (string $name): bool => in_array($name, $sheets, true)));

        $book = new Spreadsheet();
        $book->getProperties()
            ->setCreator('GELITA')
            ->setTitle($meta['title'] ?? 'Bank Soal GELITA')
            ->setDescription('Berkas Excel bank soal GELITA. Sheet PETUNJUK dan KAMUS_KOLOM hanya berisi penjelasan dan tidak ikut diimpor.');
        $book->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $this->guideSheet($book->getActiveSheet(), $meta, $rows, $extraSheets, $sheets);
        $this->dictionarySheet($book->createSheet(), $sheets);

        foreach ($sheets as $name) {
            $this->dataSheet($book->createSheet(), $name, array_keys(self::COLUMNS[$name]), $rows[$name] ?? []);
        }

        foreach ($extraSheets as $name => $sheet) {
            $this->extraSheet($book->createSheet(), $name, $sheet);
        }

        $book->setActiveSheetIndex(0);

        $writer = new XlsxWriter($book);
        $writer->setPreCalculateFormulas(false);
        $writer->save($path);
        $book->disconnectWorksheets();
    }

    /** @return list<string> header resmi sebuah sheet */
    public static function headers(string $sheet): array
    {
        return array_keys(self::COLUMNS[$sheet] ?? []);
    }

    // ------------------------------------------------------------- PETUNJUK

    /**
     * @param array<string, list<list<mixed>>>    $rows
     * @param array<string, array<string, mixed>> $extraSheets
     * @param list<string>                        $sheets sheet data yang ada di workbook ini
     */
    private function guideSheet(Worksheet $sheet, array $meta, array $rows, array $extraSheets, array $sheets): void
    {
        $sheet->setTitle('PETUNJUK');
        $sheet->setShowGridlines(false);
        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(58);
        $sheet->getColumnDimension('D')->setWidth(58);
        $sheet->getColumnDimension('E')->setWidth(30);

        $library = array_intersect($sheets, ['library', 'library_media']) !== [];
        $bank    = array_diff($sheets, ['library', 'library_media']) !== [];

        $r = 1;
        $sheet->setCellValue('B' . $r, $meta['title'] ?? 'Bank Soal GELITA');
        $sheet->getStyle('B' . $r)->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('1B2740');
        $r++;
        $sheet->setCellValue('B' . $r, 'Game Edukasi Literasi dan Etnopedagogi Kedu — Temanggung · Magelang · Wonosobo');
        $sheet->getStyle('B' . $r)->getFont()->setItalic(true)->getColor()->setRGB('8A6A32');
        $r++;

        if (! empty($meta['subtitle'])) {
            $sheet->setCellValue('B' . $r, $meta['subtitle']);
            $sheet->mergeCells("B{$r}:E{$r}");
            $sheet->getStyle('B' . $r)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getRowDimension($r)->setRowHeight(15 * max(2, (int) ceil(mb_strlen((string) $meta['subtitle']) / 150)) + 2);
            $r++;
        }

        $r++;
        $r = $this->heading($sheet, $r, 'Sebelum mulai: judul kolom jangan diubah');
        $r = $this->paragraph($sheet, $r, 'Baris pertama setiap sheet berisi KODE KOLOM yang dibaca sistem (misalnya node_ref atau prompt_id). Kode ini tidak boleh diterjemahkan, diubah, atau dipindah, begitu juga nama sheet-nya. Arti setiap kolom dalam bahasa Indonesia dapat dilihat dengan tiga cara: (1) buka sheet KAMUS_KOLOM; (2) arahkan tetikus ke judul kolom — sel yang bertanda segitiga merah kecil menyimpan catatan penjelasan; (3) klik sel di bawah judul kolom — kotak bantuan kecil akan muncul. Warna judul kolom menunjukkan wajib-tidaknya kolom itu diisi:');
        $r = $this->legend($sheet, $r);
        $r = $this->paragraph($sheet, $r, 'Kolom yang namanya berakhiran _id diisi dalam bahasa Indonesia, dan yang berakhiran _en diisi dalam bahasa Inggris. Bila kolom _en dikosongkan, siswa yang memilih bahasa Inggris membaca teks Indonesia — sebaiknya tetap diisi.');

        $r++;
        $r = $this->heading($sheet, $r, 'Langkah mengisi dan mengimpor');
        $steps = [
            'Gambar boleh diunggah sebelum atau sesudah impor. Kode berkas gambar (mis. challenge.tmg-4.scene) ditulis di kolom gambar (kolom yang namanya memuat kata media, mis. media_asset_key atau scene_media_key); daftar gambar yang dibutuhkan ada di sheet DAFTAR_MEDIA (bila ada). Selama gambar asli belum diunggah, sistem memasang gambar contoh bertulisan "GAMBAR CONTOH"; unggah gambar asli di Panel GELITA → menu Gambar & suara dengan kode berkas yang sama, maka gambar asli langsung tampil.',
            'Isi sheet dari kiri ke kanan: ' . implode(' → ', $sheets) . '. Sheet yang tidak diperlukan boleh dibiarkan kosong. Satu baris = satu data. Jangan menggabungkan sel (merge) dan jangan memakai rumus.',
            'Untuk kolom berkode (jenis soal, wilayah, keterampilan, 0/1, dan sebagainya), klik selnya lalu pilih dari daftar pilihan yang muncul, supaya tidak salah ketik.',
            'Simpan sebagai berkas Excel (.xlsx atau "Excel Workbook"), paling besar 20 MB.',
            'Buka Panel GELITA → Konten permainan → Impor soal dari Excel, pilih berkas, lalu tekan Periksa berkas. Pemeriksaan TIDAK mengubah apa pun. Hasilnya berupa kesalahan (merah, harus diperbaiki) dan peringatan (kuning, boleh dilanjutkan); setiap temuan menyebut nama sheet dan nomor barisnya.',
            'Bila tidak ada kesalahan, tekan Simpan soal ke permainan. Semua isi disimpan sekaligus: bila ada satu baris yang gagal, tidak ada yang berubah.',
            'Setelah disimpan: buka Konten permainan → Periksa kelengkapan untuk memastikan setiap tantangan lengkap, lalu coba mainkan sendiri sebagai siswa.',
            'Mengimpor ulang berkas yang sama aman: data dicocokkan lewat kodenya (node_ref, passage_key, item_key, nomor halaman) lalu diperbarui, bukan digandakan. Kunci jawaban soal yang SUDAH dijawab siswa tidak dapat diubah — buat soal baru dengan kode baru.',
            'Untuk petugas teknis: impor juga dapat dijalankan di server dengan perintah php spark gelita:bank:import berkas.xlsx --dry-run (periksa saja), lalu tanpa --dry-run.',
        ];

        foreach ($steps as $index => $text) {
            $r = $this->step($sheet, $r, (string) ($index + 1), $text);
        }

        $r++;
        $r = $this->heading($sheet, $r, 'Isi setiap sheet');
        $table = [];

        foreach ($sheets as $name) {
            [$title, $purpose, $how] = self::SHEETS[$name];
            $table[] = [$name, $title, $purpose . ' ' . $how, isset($rows[$name]) ? count($rows[$name]) . ' baris' : '—'];
        }

        foreach ($extraSheets as $name => $extra) {
            $table[] = [$name, 'Lampiran (tidak ikut diimpor)', (string) ($extra['note'] ?? ''), count($extra['rows'] ?? []) . ' baris'];
        }

        $r = $this->table($sheet, $r, ['Sheet', 'Isinya', 'Kegunaan & cara mengisi', 'Jumlah baris saat ini'], $table);

        if ($bank) {
            $r++;
            $r = $this->heading($sheet, $r, 'Jenis tantangan dan jenis soal');
            $r = $this->table($sheet, $r, ['Tantangan', 'Jenis tantangan', 'Jenis soal (interaction_type)', 'Satu variasi berisi'], [
                ['tmg-1, mgl-1, wnb-1', 'Susun Gambar', 'puzzle_arrange', 'Satu gambar (kolom media_asset_key) + keterangannya di kolom prompt_id, yang dibaca setelah gambar tersusun.'],
                ['tmg-2, mgl-2, wnb-2', 'Isi Rumpang — pilih kata', 'fill_blank_bank', 'Satu bacaan rumpang: setiap soal satu potongan bacaan dengan ___ (tiga garis bawah). Kata pengecoh diisi di sheet distractors per variasi.'],
                ['tmg-3, mgl-3, wnb-3', 'Benar atau Salah', 'verdict_card', 'Satu bacaan (sheet passages, kode {tantangan}-{variasi}) + pernyataannya. answer_id = benar atau salah; kolom skill wajib.'],
                ['tmg-4, mgl-4, wnb-4', 'Cari Objek — cari benda di gambar', 'find_object', 'Satu adegan + 4 benda dicari (answer_id = target, decoy = 0) + benda pengecoh (answer_id = decoy, decoy = 1, scorable = 0, wrong_feedback_id wajib).'],
            ]);

            $r++;
            $r = $this->heading($sheet, $r, 'Aturan bank soal kelas 5 per tingkat wilayah');
            $rules = [];

            foreach (['mudah' => 'Temanggung', 'sedang' => 'Magelang', 'sulit' => 'Wonosobo'] as $difficulty => $region) {
                $puzzle  = DifficultyRules::puzzle($difficulty);
                $reading = DifficultyRules::reading($difficulty);
                $tf      = DifficultyRules::trueFalse($difficulty);
                $hunt    = DifficultyRules::hunt($difficulty);

                $rules[] = [
                    $region . ' (' . $difficulty . ')',
                    'Susun Gambar ' . DifficultyRules::layoutLabel($puzzle['grid'], $puzzle['shape'])
                        . ' · Isi Rumpang ' . $reading['blanks'][0] . ' rumpang + ' . $reading['distractors'][0] . ' pengecoh, Bloom ' . DifficultyRules::bloomRangeLabel($difficulty),
                    'Benar atau Salah ' . $tf['paragraphs'] . ' paragraf, ' . $tf['statements'] . ' pernyataan, Bloom ' . DifficultyRules::bloomRangeLabel($difficulty, 'truefalse')
                        . '; keterampilan ' . implode(', ', $tf['skills']),
                    'Cari Objek ' . $hunt['targets'] . ' dicari + ' . $hunt['decoys'] . ' pengecoh, Bloom ' . DifficultyRules::bloomRangeLabel($difficulty, 'hunt'),
                ];
            }

            $r = $this->table($sheet, $r, ['Wilayah', 'Susun Gambar · Isi Rumpang', 'Benar atau Salah', 'Cari Objek'], $rules);
            $r = $this->paragraph($sheet, $r, 'Setiap tantangan Susun Gambar, Isi Rumpang, dan Benar atau Salah wajib punya tepat 3 variasi (a, b, c); Cari Objek cukup satu adegan. Potongan Isi Rumpang tidak boleh diawali ___, jawaban dalam satu variasi harus berbeda, dan kata pengecoh tidak boleh sama dengan jawaban. Benda Cari Objek harus berada di dalam gambar (posisi 0–100 %) dan tidak saling menumpuk lebih dari 20 %. Pemeriksaan impor menolak berkas yang melanggar aturan ini.');

            $r++;
            $r = $this->heading($sheet, $r, 'Kunci jawaban untuk setiap jenis soal (kolom answer_id / answer_en)');
            $r = $this->table($sheet, $r, ['Jenis soal', 'Isi answer_id', 'Isi answer_en', 'Contoh'], [
                ['puzzle_arrange', 'kosongkan (susunan benar dibuat otomatis)', 'kosongkan', '—'],
                ['fill_blank_bank', 'satu kata/frasa jawaban (otomatis masuk bank kata)', 'kata jawaban dalam bahasa Inggris', 'kopi / coffee'],
                ['verdict_card', 'benar atau salah', 'kosongkan', 'salah'],
                ['find_object', 'target (benda yang dicari) atau decoy (pengecoh)', 'kosongkan', 'target'],
            ]);
        }

        if ($library) {
            $r++;
            $r = $this->heading($sheet, $r, 'Format teks Pustaka Kedu (kolom body_id / body_en di sheet library)');
            $r = $this->table($sheet, $r, ['Ketik', 'Hasilnya di layar', 'Contoh', ''], [
                ['baris kosong', 'paragraf baru', '(di dalam sel Excel, tekan Alt+Enter dua kali)', ''],
                ['## Subjudul', 'subjudul', '## Asal-usul nama', ''],
                ['- teks', 'daftar berpoin', '- Tembakau srintil', ''],
                ['**kata**', 'huruf tebal', 'Candi **Borobudur**', ''],
                ['*kata*', 'huruf miring (untuk istilah daerah/asing)', 'dari kata *senduro*', ''],
                ['> teks', 'kotak "Tahukah kamu?"', '> Tahukah kamu? Dieng berarti tempat para dewa.', ''],
                ['Sumber: …', 'catatan sumber kecil di akhir halaman', 'Sumber: UNESCO; Pemkab Magelang', ''],
            ]);
        }

        $r++;
        $r = $this->heading($sheet, $r, 'Hal yang sering keliru');
        foreach (array_filter([
            'Menerjemahkan atau mengganti judul kolom / nama sheet → kolom dianggap kosong atau sheet dilewati.',
            'Menulis "benar"/"salah" atau "ya"/"tidak" di kolom 0/1 → untuk kolom scorable, decoy, dan is_active pakai 0 (tidak) atau 1 (ya).',
            $bank ? 'Soal Isi Rumpang tanpa ___ (tiga garis bawah) di kalimatnya → siswa tidak melihat tempat kosong.' : null,
            $bank ? 'Kode soal (item_key) diubah setelah dijawab siswa → dianggap soal baru, dan jawaban lama tidak tersambung lagi.' : null,
            $bank ? 'Variasi tidak lengkap → setiap tantangan Susun Gambar, Isi Rumpang, dan Benar atau Salah perlu tepat tiga variasi (a, b, c), supaya pengerjaan ulang mendapat soal lain.' : null,
            $bank ? 'Bintang TIDAK diatur di berkas ini: ★★★ semua soal Berhasil pada Percobaan Pertama, ★★ semua benar di akhir, ★ selesai dengan kesempatan habis. Siswa boleh mencoba sampai 3 kali per soal.' : null,
        ]) as $text) {
            $r = $this->step($sheet, $r, '•', $text);
        }
    }

    /** @param list<string> $sheets sheet data yang ada di workbook ini */
    private function dictionarySheet(Worksheet $sheet, array $sheets): void
    {
        $sheet->setTitle('KAMUS_KOLOM');
        $headers = ['Sheet', 'Kode kolom (judul di baris 1)', 'Nama dalam bahasa Indonesia', 'Wajib diisi?', 'Arti & cara mengisi', 'Yang boleh diisi', 'Contoh'];
        $sheet->fromArray($headers, null, 'A1');
        $row = 2;

        foreach ($sheets as $name) {
            foreach (self::COLUMNS[$name] as $column => [$label, $status, $meaning, $allowed, $example]) {
                $sheet->fromArray([$name, $column, $label, $status, $meaning, $allowed, $example], null, 'A' . $row, true);
                [$fill, $ink] = self::COLORS[$status];
                $sheet->getStyle('D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fill);
                $sheet->getStyle('D' . $row)->getFont()->getColor()->setRGB($ink);
                $row++;
            }
        }

        foreach (['A' => 15, 'B' => 22, 'C' => 30, 'D' => 11, 'E' => 70, 'F' => 40, 'G' => 32] as $letter => $width) {
            $sheet->getColumnDimension($letter)->setWidth($width);
        }

        $sheet->getStyle('A2:G' . ($row - 1))->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('B2:B' . ($row - 1))->getFont()->setName('Consolas');
        $this->headerStyle($sheet, 'A1:G1', '1B2740', 'FFFFFF');
        $sheet->freezePane('C2');
        $sheet->setAutoFilter('A1:G' . ($row - 1));
    }

    // ----------------------------------------------------------- sheet data

    /**
     * @param list<string>             $headers
     * @param list<list<mixed>>        $rows
     */
    private function dataSheet(Worksheet $sheet, string $name, array $headers, array $rows): void
    {
        $sheet->setTitle($name);
        $sheet->fromArray($headers, null, 'A1');

        if ($rows !== []) {
            // strictNullComparison: angka 0 tetap ditulis, sel null dibiarkan kosong
            $sheet->fromArray($rows, null, 'A2', true);
        }

        $last   = count($rows) + 1;
        $limit  = max(self::ROWS_WITH_VALIDATION, $last + 200);

        foreach ($headers as $index => $column) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);
            [$label, $status, $meaning, $allowed, $example] = self::COLUMNS[$name][$column];
            [$fill, $ink] = self::COLORS[$status];

            $this->headerStyle($sheet, $letter . '1', $fill, $ink);

            $comment = $sheet->getComment($letter . '1');
            $comment->setAuthor('GELITA');
            $comment->getText()->createTextRun($label . ' — ' . ucfirst(self::STATUS_TEXT[$status]))->getFont()->setBold(true);
            $comment->getText()->createTextRun("\n" . $meaning . "\n\nYang boleh diisi: " . $allowed . "\nContoh: " . $example);
            $comment->setWidth('340pt');
            $comment->setHeight('170pt');

            $isLong = in_array($column, self::LONG, true);
            $sheet->getColumnDimension($letter)->setWidth($isLong ? (str_starts_with($column, 'body_') ? 70 : 42) : max(12, min(24, strlen($column) + 4)));

            $this->validation($sheet, "{$letter}2:{$letter}{$limit}", $column, $label, $status, $meaning);

            if ($isLong && $last > 1) {
                $sheet->getStyle("{$letter}2:{$letter}{$last}")->getAlignment()->setWrapText(true);
            }

            // Kolom teks/kode diformat Teks agar Excel tidak mengubah isian
            // seperti "1,5" (desimal di lokal Indonesia) atau "c,a,d,b".
            // Kolom angka & 0/1 dibiarkan Umum supaya validasi angkanya berlaku.
            if (! isset(self::NUMBERS[$column]) && (self::CHOICES[$column] ?? null) !== ['0', '1']) {
                $sheet->getStyle("{$letter}2:{$letter}{$limit}")->getNumberFormat()->setFormatCode('@');
            }
        }

        $lastLetter = Coordinate::stringFromColumnIndex(count($headers));

        if ($last > 1) {
            $sheet->getStyle("A2:{$lastLetter}{$last}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        }

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->freezePane('B2');
        $sheet->setAutoFilter("A1:{$lastLetter}" . max(2, $last));
    }

    private function validation(Worksheet $sheet, string $range, string $column, string $label, string $status, string $meaning): void
    {
        $rule = new DataValidation();
        $rule->setAllowBlank(true);
        $rule->setShowInputMessage(true);
        $rule->setPromptTitle(self::clip($label, 32));
        $rule->setPrompt(self::clip(ucfirst(self::STATUS_TEXT[$status]) . '. ' . $meaning, 250));

        if (isset(self::CHOICES[$column])) {
            $rule->setType(DataValidation::TYPE_LIST);
            $rule->setShowDropDown(true);
            $rule->setFormula1('"' . implode(',', self::CHOICES[$column]) . '"');
            $rule->setShowErrorMessage(true);
            $rule->setErrorStyle(DataValidation::STYLE_STOP);
            $rule->setErrorTitle('Isian tidak dikenal');
            $rule->setError(self::clip('Pilih salah satu dari daftar pilihan: ' . implode(', ', self::CHOICES[$column]), 250));
        } elseif (isset(self::NUMBERS[$column])) {
            [$kind, $min, $max] = self::NUMBERS[$column];
            $rule->setType($kind === 'whole' ? DataValidation::TYPE_WHOLE : DataValidation::TYPE_DECIMAL);
            $rule->setOperator(DataValidation::OPERATOR_BETWEEN);
            $rule->setFormula1((string) $min);
            $rule->setFormula2((string) $max);
            $rule->setShowErrorMessage(true);
            $rule->setErrorStyle(DataValidation::STYLE_STOP);
            $rule->setErrorTitle('Angka di luar batas');
            $rule->setError($kind === 'whole' ? "Isi dengan angka bulat {$min} sampai {$max}." : "Isi dengan angka {$min} sampai {$max}.");
        } else {
            $rule->setType(DataValidation::TYPE_NONE);
        }

        $sheet->setDataValidation($range, $rule);
    }

    /** @param array{note?: string, headers: list<string>, rows: list<list<mixed>>} $extra */
    private function extraSheet(Worksheet $sheet, string $name, array $extra): void
    {
        $sheet->setTitle(mb_substr($name, 0, 31));
        $sheet->fromArray($extra['headers'], null, 'A1');

        if ($extra['rows'] !== []) {
            $sheet->fromArray($extra['rows'], null, 'A2', true);
        }

        $lastLetter = Coordinate::stringFromColumnIndex(count($extra['headers']));
        $last       = count($extra['rows']) + 1;
        $this->headerStyle($sheet, "A1:{$lastLetter}1", '1B2740', 'FFFFFF');

        foreach ($extra['headers'] as $index => $header) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->getColumnDimension($letter)->setWidth(max(14, min(60, (int) ($extra['widths'][$index] ?? 24))));
        }

        $sheet->getStyle("A2:{$lastLetter}{$last}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastLetter}" . max(2, $last));
    }

    /** Potong teks panjang untuk kotak bantuan Excel (judul ≤ 32, isi ≤ 255 huruf). */
    private static function clip(string $text, int $max): string
    {
        return mb_strlen($text) <= $max ? $text : rtrim(mb_substr($text, 0, $max - 1)) . '…';
    }

    // ------------------------------------------------------------- gaya

    private function headerStyle(Worksheet $sheet, string $range, string $fill, string $ink): void
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setRGB($ink);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fill);
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('4A3218');
    }

    private function heading(Worksheet $sheet, int $row, string $text): int
    {
        $sheet->setCellValue('B' . $row, $text);
        $sheet->mergeCells("B{$row}:E{$row}");
        $style = $sheet->getStyle("B{$row}:E{$row}");
        $style->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1B2740');
        $sheet->getRowDimension($row)->setRowHeight(22);

        return $row + 1;
    }

    private function paragraph(Worksheet $sheet, int $row, string $text): int
    {
        $sheet->setCellValue('B' . $row, $text);
        $sheet->mergeCells("B{$row}:E{$row}");
        $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getRowDimension($row)->setRowHeight(15 * max(2, (int) ceil(mb_strlen($text) / 150)));

        return $row + 1;
    }

    private function step(Worksheet $sheet, int $row, string $marker, string $text): int
    {
        $sheet->setCellValue('A' . $row, $marker);
        $sheet->setCellValue('B' . $row, $text);
        $sheet->mergeCells("B{$row}:E{$row}");
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->getColor()->setRGB('8A6A32');
        $sheet->getStyle('A' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getRowDimension($row)->setRowHeight(15 * max(1, (int) ceil(mb_strlen($text) / 150)) + 3);

        return $row + 1;
    }

    private function legend(Worksheet $sheet, int $row): int
    {
        $col = ['B', 'C', 'D'];

        foreach (['Wajib' => 'WAJIB — harus diisi', 'Bersyarat' => 'BERSYARAT — wajib untuk jenis soal atau keadaan tertentu', 'Opsional' => 'OPSIONAL — boleh dikosongkan'] as $status => $text) {
            $cell = array_shift($col) . $row;
            [$fill, $ink] = self::COLORS[$status];
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB($ink);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fill);
        }

        return $row + 1;
    }

    /**
     * @param list<string>       $headers 4 kolom (B–E)
     * @param list<list<string>> $rows
     */
    private function table(Worksheet $sheet, int $row, array $headers, array $rows): int
    {
        $sheet->fromArray($headers, null, 'B' . $row);
        $this->headerStyle($sheet, "B{$row}:E{$row}", 'E6D3A8', '1B1B1B');
        $row++;

        foreach ($rows as $line) {
            $sheet->fromArray($line, null, 'B' . $row, true);
            $sheet->getStyle("B{$row}:E{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("B{$row}:E{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR)->getColor()->setRGB('C7A265');
            $sheet->getStyle('B' . $row)->getFont()->setName('Consolas');
            $longest = max(array_map(static fn ($value): int => mb_strlen((string) $value), $line));
            $sheet->getRowDimension($row)->setRowHeight(15 * max(1, (int) ceil($longest / 60)) + 2);
            $row++;
        }

        return $row;
    }
}
