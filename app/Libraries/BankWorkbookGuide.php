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

    public const NODE_REFS = [
        'tmg-1', 'tmg-2', 'tmg-3', 'tmg-4', 'tmg-5',
        'mgl-1', 'mgl-2', 'mgl-3', 'mgl-4', 'mgl-5',
        'wnb-1', 'wnb-2', 'wnb-3', 'wnb-4', 'wnb-5',
    ];

    /** Urutan & kegunaan sheet data. [judul Indonesia, fungsi, cara mengisi] */
    public const SHEETS = [
        'nodes'         => ['Tantangan', 'Judul, perintah, kartu misi, dan pengaturan 15 tantangan yang SUDAH ADA (tmg-1 … wnb-5). Sheet ini tidak membuat tantangan baru.', 'Satu baris untuk satu tantangan. Sel yang dikosongkan = isi lama tetap dipakai.'],
        'distractors'   => ['Kata pengecoh', 'Kata-kata yang SALAH tetapi ikut muncul di pilihan kata tantangan Isi Rumpang (tmg-2, wnb-2), supaya siswa perlu berpikir.', 'Satu baris untuk satu kata. Semua kata pengecoh satu tantangan diganti dengan isi sheet ini.'],
        'passages'      => ['Teks bacaan', 'Bacaan panjang yang tampil di samping soal. Soal memakainya lewat kolom passage_key di sheet items.', 'Satu baris untuk satu bacaan. Isi bahasa Indonesia dan Inggris wajib diisi.'],
        'items'         => ['Soal', 'Bank soal: setiap pertanyaan, pernyataan, gambar Susun Gambar, benda yang dicari, atau urutan yang disusun.', 'Satu baris untuk satu soal. Setiap kali bermain, siswa mendapat soal acak sebanyak angka items_per_round di sheet nodes.'],
        'options'       => ['Pilihan jawaban', 'Pilihan A–D untuk soal pilihan ganda (single_choice) dan soal sumber tepercaya (source_trust).', 'Satu baris untuk satu pilihan; setiap soal punya tepat SATU pilihan yang benar.'],
        'pieces'        => ['Kartu urutan', 'Kartu-kartu yang disusun siswa pada soal urutkan kartu (ordering, Wonosobo tantangan 1).', 'Satu baris untuk satu kartu.'],
        'sources'       => ['Kartu sumber', 'Sumber A dan Sumber B yang dibandingkan siswa pada soal pernyataan Wonosobo tantangan 3.', 'Dua baris untuk satu soal.'],
        'hints'         => ['Petunjuk', 'Petunjuk yang boleh dibuka siswa saat kesulitan (membuka petunjuk mengurangi nilai kemandirian siswa).', 'Satu baris untuk satu petunjuk: untuk satu tantangan (isi node_ref) ATAU untuk satu soal (isi item_key).'],
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
            'node_ref'             => ['Kode tantangan', 'Wajib', 'Tantangan yang diubah: singkatan wilayah + nomor pos di peta.', 'tmg-1 … tmg-5 (Temanggung), mgl-1 … mgl-5 (Magelang), wnb-1 … wnb-5 (Wonosobo)', 'tmg-2'],
            'title_id'             => ['Judul (Indonesia)', 'Opsional', 'Judul tantangan di peta wilayah dan di kartu misi. Kosong = judul lama tetap dipakai.', 'teks, paling banyak 150 huruf', 'Lengkapi Kalimat Temanggung'],
            'title_en'             => ['Judul (Inggris)', 'Opsional', 'Terjemahan judul untuk siswa yang memilih bahasa Inggris.', 'teks, paling banyak 150 huruf', 'Complete the Temanggung Sentence'],
            'instruction_id'       => ['Perintah (Indonesia)', 'Opsional', 'Perintah singkat yang tampil di atas area permainan.', 'teks', 'Pilih satu kata yang tepat dari bank kata.'],
            'instruction_en'       => ['Perintah (Inggris)', 'Opsional', 'Terjemahan perintah.', 'teks', 'Choose the one correct word from the word bank.'],
            'description_id'       => ['Kartu misi (Indonesia)', 'Opsional', 'Cerita atau tujuan singkat di kartu misi sebelum tantangan dimulai.', 'teks, 1–3 kalimat', 'Mbah Kedu meminta Jaka melengkapi catatan perjalanan.'],
            'description_en'       => ['Kartu misi (Inggris)', 'Opsional', 'Terjemahan kartu misi.', 'teks', 'Mbah Kedu asks Jaka to complete the travel notes.'],
            'items_per_round'      => ['Soal per permainan', 'Opsional', 'Jumlah soal yang diambil acak dari bank soal setiap kali siswa memainkan tantangan ini. Sebaiknya bank soal berisi 1,5–2 kali angka ini supaya ada cadangan.', 'angka bulat 1 ke atas', '4'],
            'verdict_options'      => ['Tombol jawaban', 'Bersyarat', 'Khusus tantangan Benar atau Salah (tmg-3, mgl-3, wnb-3): tombol jawaban yang tampil. Tulis dipisah koma tanpa spasi.', 'benar,salah   ATAU   benar,salah,pendapat', 'benar,salah,pendapat'],
            'require_reason'       => ['Wajib menulis alasan', 'Bersyarat', 'Khusus tantangan Benar atau Salah: 1 = siswa wajib menulis alasan (soal jenis verdict_reason); 0 = tidak.', '0 atau 1', '1'],
            'use_word_bank'        => ['Pakai pilihan kata', 'Bersyarat', 'Khusus tantangan Isi Rumpang: 1 = siswa memilih dari kumpulan kata (bank kata); 0 = siswa mengetik sendiri jawabannya.', '0 atau 1', '1'],
            'distractor_count'     => ['Jumlah kata pengecoh', 'Bersyarat', 'Khusus Isi Rumpang dengan pilihan kata: berapa kata pengecoh (dari sheet distractors) yang ikut ditampilkan. Sediakan kata pengecoh paling sedikit sebanyak angka ini.', 'angka bulat 0 ke atas', '2'],
            'scene_media_key'      => ['Gambar adegan', 'Opsional', 'Kode berkas gambar adegan tantangan — WAJIB untuk Cari Objek (tmg-4), karena di gambar inilah benda-benda disembunyikan. Unggah gambarnya di menu Gambar & suara.', 'kode berkas gambar', 'challenge.tmg-4.scene'],
            'background_media_key' => ['Gambar latar tantangan', 'Opsional', 'Kode berkas gambar latar layar tantangan. Kosong = memakai latar wilayah.', 'kode berkas gambar', 'challenge.tmg-4.bg'],
        ],
        'distractors' => [
            'node_ref' => ['Kode tantangan', 'Wajib', 'Tantangan Isi Rumpang yang memakai pilihan kata.', 'tmg-2, wnb-2 (atau tantangan Isi Rumpang lain dengan use_word_bank = 1)', 'tmg-2'],
            'text_id'  => ['Kata pengecoh (Indonesia)', 'Wajib', 'Kata yang TIDAK tepat untuk tempat kosong mana pun, tetapi masuk akal sehingga siswa perlu berpikir.', 'satu kata / frasa pendek', 'salju'],
            'text_en'  => ['Kata pengecoh (Inggris)', 'Opsional', 'Terjemahan kata pengecoh. Kosong = sama dengan kolom Indonesia.', 'satu kata / frasa pendek', 'snow'],
        ],
        'passages' => [
            'passage_key'      => ['Kode bacaan', 'Wajib', 'Kode unik teks bacaan; ditulis juga di kolom passage_key sheet items untuk menyambungkan soal dengan bacaannya. Jangan diubah setelah dipakai.', 'huruf kecil, angka, dan tanda minus; tidak boleh kembar', 'tmg-teks-a'],
            'level_code'       => ['Wilayah', 'Wajib', 'Wilayah pemilik bacaan. Bacaan hanya boleh dipakai soal di wilayah yang sama.', 'temanggung | magelang | wonosobo', 'temanggung'],
            'title_id'         => ['Judul bacaan (Indonesia)', 'Opsional', 'Judul di atas teks bacaan.', 'teks', 'Tanah Subur di Kaki Dua Gunung'],
            'title_en'         => ['Judul bacaan (Inggris)', 'Opsional', 'Terjemahan judul.', 'teks', 'Fertile Land at the Foot of Two Mountains'],
            'body_id'          => ['Isi bacaan (Indonesia)', 'Wajib', 'Teks yang dibaca siswa. Tulis kalimat pendek dan jelas sesuai jenjang SD/SMP.', 'teks panjang; baris kosong = paragraf baru', 'Temanggung berada di antara …'],
            'body_en'          => ['Isi bacaan (Inggris)', 'Wajib', 'Terjemahan isi bacaan.', 'teks panjang', 'Temanggung lies between …'],
            'media_asset_key'  => ['Gambar bacaan', 'Opsional', 'Kode berkas gambar pendamping bacaan (unggah di menu Gambar & suara).', 'kode berkas gambar', 'passage.tmg-teks-a'],
            'reference_source' => ['Sumber rujukan', 'Opsional', 'Asal fakta dalam bacaan (buku, situs resmi, jurnal).', 'teks', 'Pemkab Temanggung; BPS Temanggung 2024'],
        ],
        'items' => [
            'item_key'          => ['Kode soal', 'Wajib', 'Kode unik soal: kode tantangan + nomor 2 angka. JANGAN diubah setelah soal dijawab siswa, karena jawaban siswa tersambung ke kode ini (data penelitian).', 'kode tantangan diikuti -01, -02, dan seterusnya', 'tmg-2-01'],
            'node_ref'          => ['Kode tantangan', 'Wajib', 'Tantangan tempat soal ini muncul.', 'tmg-1 … wnb-5', 'tmg-2'],
            'sequence'          => ['Urutan di bank soal', 'Opsional', 'Urutan tampil di halaman pengelolaan soal. Tidak memengaruhi soal acak yang diterima siswa.', 'angka bulat', '1'],
            'interaction_type'  => ['Jenis soal', 'Wajib', 'Harus cocok dengan jenis tantangannya. Susun Gambar: puzzle_arrange/ordering · Isi Rumpang: fill_blank_bank/fill_blank_free · Benar/Salah: verdict_card/verdict_reason · Kuis Pilihan: single_choice/source_trust · Cari Objek: find_object.', 'puzzle_arrange | ordering | fill_blank_bank | fill_blank_free | verdict_card | verdict_reason | single_choice | source_trust | find_object', 'fill_blank_bank'],
            'indicator'         => ['Indikator', 'Opsional', 'Indikator pembelajaran yang diukur soal ini. Kosong = mengikuti indikator tantangannya.', 'literasi | budaya | sikap', 'literasi'],
            'prompt_id'         => ['Soal / pernyataan (Indonesia)', 'Wajib', 'Isi Rumpang: tandai tempat kosong dengan ___ (tiga garis bawah). Cari Objek: petunjuk benda yang harus dicari. Susun Gambar: keterangan gambar yang dibaca setelah gambar tersusun. Benar atau Salah: pernyataan yang dinilai.', 'teks', 'Temanggung terletak di antara Gunung Sindoro dan Gunung ___.'],
            'prompt_en'         => ['Soal / pernyataan (Inggris)', 'Opsional', 'Terjemahan soal (Isi Rumpang: tetap pakai ___). Kosong = siswa yang memilih bahasa Inggris melihat teks Indonesia.', 'teks', 'Temanggung lies between Mount Sindoro and Mount ___.'],
            'source_text_id'    => ['Teks sumber soal (Indonesia)', 'Opsional', 'Kutipan atau informasi pendek khusus soal ini, tampil di atas soal (bila soal tidak memakai teks bacaan).', 'teks', 'Pengumuman: Festival dimulai pukul 08.00.'],
            'source_text_en'    => ['Teks sumber soal (Inggris)', 'Opsional', 'Terjemahan teks sumber.', 'teks', 'Notice: The festival starts at 08.00.'],
            'passage_key'       => ['Kode bacaan', 'Opsional', 'Teks bacaan yang dipakai soal ini: kode dari sheet passages, atau kode bacaan yang sudah ada di permainan.', 'kode bacaan', 'tmg-teks-a'],
            'answer_id'         => ['Kunci jawaban (Indonesia)', 'Bersyarat', 'Lihat tabel "Kunci jawaban untuk setiap jenis soal" di sheet PETUNJUK. Untuk single_choice dan source_trust: KOSONGKAN (kunci diambil dari pilihan jawaban yang is_correct = 1).', 'tergantung jenis soal', 'Sumbing'],
            'answer_en'         => ['Kunci jawaban (Inggris)', 'Bersyarat', 'Untuk Isi Rumpang: jawaban dalam bahasa Inggris. Kosong = sama dengan kolom Indonesia.', 'tergantung jenis soal', 'Sumbing'],
            'sample_reason_id'  => ['Contoh alasan (Indonesia)', 'Bersyarat', 'Khusus verdict_reason: contoh alasan yang benar sebagai pegangan guru. Tidak dinilai otomatis.', 'teks', 'Kata "paling indah" adalah pendapat pribadi.'],
            'sample_reason_en'  => ['Contoh alasan (Inggris)', 'Bersyarat', 'Terjemahan contoh alasan.', 'teks', 'The words "most beautiful" are a personal opinion.'],
            'x'                 => ['Posisi dari kiri (%)', 'Bersyarat', 'Khusus find_object: jarak benda dari tepi KIRI gambar adegan, dalam persen lebar gambar.', 'angka 0–100', '18'],
            'y'                 => ['Posisi dari atas (%)', 'Bersyarat', 'Khusus find_object: jarak benda dari tepi ATAS gambar adegan, dalam persen tinggi gambar.', 'angka 0–100', '62'],
            'w'                 => ['Lebar benda (%)', 'Bersyarat', 'Khusus find_object: lebar benda dalam persen lebar gambar adegan. Jaga agar benda-benda tidak saling bertumpuk.', 'angka 5–30', '12'],
            'decoy'             => ['Benda jebakan', 'Bersyarat', 'Khusus find_object: 1 = benda jebakan (bukan dari wilayah ini). Jebakan selalu tampil, tidak dinilai (scorable = 0), dan wajib punya penjelasan jebakan (wrong_feedback_id).', '0 atau 1', '0'],
            'wrong_feedback_id' => ['Penjelasan jebakan (Indonesia)', 'Bersyarat', 'Khusus benda jebakan: penjelasan yang tampil saat siswa mengklik jebakan.', 'teks', 'Angklung berasal dari Jawa Barat, bukan Temanggung.'],
            'wrong_feedback_en' => ['Penjelasan jebakan (Inggris)', 'Bersyarat', 'Terjemahan penjelasan jebakan.', 'teks', 'Angklung comes from West Java, not Temanggung.'],
            'digital_pillar'    => ['Pilar literasi digital', 'Bersyarat', 'Khusus Wonosobo tantangan 5: pilar literasi digital yang diukur soal ini (untuk laporan hasil belajar).', 'digital_skills (cakap digital) | digital_ethics (etika digital) | digital_safety (aman digital) | digital_culture (budaya digital)', 'digital_safety'],
            'media_asset_key'   => ['Gambar soal', 'Bersyarat', 'WAJIB untuk puzzle_arrange (gambar yang dipotong 3×3, berbentuk persegi) dan find_object (gambar benda, PNG berlatar transparan). Unggah di menu Gambar & suara, atau pasang belakangan dari halaman tantangan.', 'kode berkas gambar', 'challenge.item.tmg-1-01'],
            'scorable'          => ['Dinilai?', 'Opsional', '1 = dihitung dalam skor (bawaan). 0 = tidak dinilai (wajib untuk benda jebakan).', '0 atau 1', '1'],
            'review_status'     => ['Status pemeriksaan isi', 'Opsional', 'draft = masih draf; needs_verification = fakta perlu dicek (muncul peringatan saat diimpor); verified = sudah dicek ahli/guru.', 'draft | needs_verification | verified', 'verified'],
            'review_note'       => ['Catatan pemeriksaan', 'Opsional', 'Catatan untuk pemeriksa isi: apa yang perlu dicek atau sudah dicek.', 'teks', 'Tinggi gunung dicek dengan data PVMBG.'],
            'reference_source'  => ['Sumber rujukan', 'Opsional', 'Asal fakta dalam soal (disarankan untuk soal budaya/sejarah).', 'teks', 'UNESCO World Heritage Centre, Borobudur'],
        ],
        'options' => [
            'option_key'      => ['Kode pilihan', 'Wajib', 'Kode unik pilihan jawaban: kode soal + huruf.', 'kode soal diikuti -a, -b, -c, -d', 'tmg-5-01-a'],
            'item_key'        => ['Kode soal', 'Wajib', 'Soal pemilik pilihan jawaban ini (harus ada di sheet items).', 'kode soal', 'tmg-5-01'],
            'label_id'        => ['Teks pilihan (Indonesia)', 'Wajib', 'Teks pilihan jawaban.', 'teks singkat', 'Sindoro dan Sumbing'],
            'label_en'        => ['Teks pilihan (Inggris)', 'Opsional', 'Terjemahan pilihan jawaban. Kosong = sama dengan kolom Indonesia.', 'teks singkat', 'Sindoro and Sumbing'],
            'is_correct'      => ['Jawaban benar?', 'Wajib', '1 = pilihan ini jawaban yang benar; 0 = salah. Setiap soal wajib punya TEPAT SATU pilihan bernilai 1.', '0 atau 1', '1'],
            'feedback_id'     => ['Umpan balik (Indonesia)', 'Opsional', 'Penjelasan yang tampil setelah siswa memilih pilihan ini. Sangat disarankan untuk pilihan yang benar.', 'teks', 'Tepat! Teks menyebut kedua gunung itu.'],
            'feedback_en'     => ['Umpan balik (Inggris)', 'Opsional', 'Terjemahan umpan balik.', 'teks', 'Correct! The text names both mountains.'],
            'media_asset_key' => ['Gambar pilihan', 'Opsional', 'Kode berkas gambar untuk pilihan ini (400 × 400 px).', 'kode berkas gambar', 'challenge.option.mgl-4-01-a'],
            'display_order'   => ['Urutan tampil', 'Opsional', 'Urutan pilihan A–D bila tidak diacak.', '1–4', '1'],
        ],
        'pieces' => [
            'item_key'  => ['Kode soal', 'Wajib', 'Soal urutkan kartu (ordering) pemilik kartu ini.', 'kode soal', 'wnb-1-01'],
            'piece_key' => ['Kode kartu', 'Wajib', 'Kode kartu. Urutan yang benar ditulis di kolom answer_id soal tersebut, dipisah koma.', 'a, b, c, … atau 1, 2, 3, …', 'a'],
            'text_id'   => ['Teks kartu (Indonesia)', 'Wajib', 'Isi satu langkah atau kejadian.', 'teks singkat', 'Siapkan mi dan kol.'],
            'text_en'   => ['Teks kartu (Inggris)', 'Opsional', 'Terjemahan. Kosong = sama dengan kolom Indonesia.', 'teks singkat', 'Prepare the noodles and cabbage.'],
        ],
        'sources' => [
            'item_key' => ['Kode soal', 'Wajib', 'Soal pernyataan (verdict_card) yang membandingkan dua sumber.', 'kode soal', 'wnb-3-01'],
            'label_id' => ['Nama sumber (Indonesia)', 'Wajib', 'Judul kartu sumber.', 'teks singkat', 'Sumber A'],
            'label_en' => ['Nama sumber (Inggris)', 'Opsional', 'Terjemahan judul kartu sumber.', 'teks singkat', 'Source A'],
            'kind'     => ['Jenis sumber', 'Wajib', 'official = resmi (pemerintah/pengelola); anonymous = akun/komentar tanpa nama; chain_message = pesan berantai; blog = blog/tulisan pribadi.', 'official | anonymous | chain_message | blog', 'official'],
            'text_id'  => ['Isi sumber (Indonesia)', 'Wajib', 'Kutipan isi sumber.', 'teks', 'Pengumuman pengelola: pengunjung dilarang memanjat candi.'],
            'text_en'  => ['Isi sumber (Inggris)', 'Opsional', 'Terjemahan isi sumber.', 'teks', 'Management notice: visitors must not climb the temple.'],
        ],
        'hints' => [
            'node_ref' => ['Kode tantangan', 'Bersyarat', 'Isi untuk petunjuk umum satu tantangan. Isi SALAH SATU saja: node_ref atau item_key.', 'tmg-1 … wnb-5', 'tmg-2'],
            'item_key' => ['Kode soal', 'Bersyarat', 'Isi untuk petunjuk khusus satu soal.', 'kode soal', 'tmg-2-01'],
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
        'interaction_type' => ['puzzle_arrange', 'ordering', 'fill_blank_bank', 'fill_blank_free', 'verdict_card', 'verdict_reason', 'single_choice', 'source_trust', 'find_object'],
        'indicator'        => ['literasi', 'budaya', 'sikap'],
        'review_status'    => ['draft', 'needs_verification', 'verified'],
        'kind'             => ['official', 'anonymous', 'chain_message', 'blog'],
        'digital_pillar'   => ['digital_skills', 'digital_ethics', 'digital_safety', 'digital_culture'],
        'media_kind'       => ['image', 'video'],
        'require_reason'   => ['0', '1'],
        'use_word_bank'    => ['0', '1'],
        'decoy'            => ['0', '1'],
        'scorable'         => ['0', '1'],
        'is_correct'       => ['0', '1'],
        'is_active'        => ['0', '1'],
    ];

    /** Kolom angka: [jenis, min, maks]. */
    private const NUMBERS = [
        'items_per_round'  => ['whole', 1, 50],
        'distractor_count' => ['whole', 0, 50],
        'sequence'         => ['whole', 1, 999],
        'page_sequence'    => ['whole', 1, 999],
        'display_order'    => ['whole', 1, 20],
        'x'                => ['decimal', 0, 100],
        'y'                => ['decimal', 0, 100],
        'w'                => ['decimal', 1, 60],
    ];

    /** Kolom teks panjang: lebar kolom besar + teks terbungkus. */
    private const LONG = [
        'body_id', 'body_en', 'prompt_id', 'prompt_en', 'source_text_id', 'source_text_en', 'description_id', 'description_en',
        'instruction_id', 'instruction_en', 'text_id', 'text_en', 'feedback_id', 'feedback_en', 'sample_reason_id', 'sample_reason_en',
        'wrong_feedback_id', 'wrong_feedback_en', 'reference_source', 'review_note', 'caption_id', 'caption_en', 'credit', 'external_url',
        'label_id', 'label_en', 'title_id', 'title_en',
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
     * @param array{title?: string, subtitle?: string}          $meta
     * @param array<string, array{note?: string, headers: list<string>, rows: list<list<string|int|float|null>>}> $extraSheets
     *        sheet tambahan (mis. daftar media) — diabaikan importer
     */
    public function write(array $rows, string $path, array $meta = [], array $extraSheets = []): void
    {
        $book = new Spreadsheet();
        $book->getProperties()
            ->setCreator('GELITA')
            ->setTitle($meta['title'] ?? 'Bank Soal GELITA')
            ->setDescription('Berkas Excel bank soal GELITA. Sheet PETUNJUK dan KAMUS_KOLOM hanya berisi penjelasan dan tidak ikut diimpor.');
        $book->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $this->guideSheet($book->getActiveSheet(), $meta, $rows, $extraSheets);
        $this->dictionarySheet($book->createSheet());

        foreach (self::COLUMNS as $name => $columns) {
            $this->dataSheet($book->createSheet(), $name, array_keys($columns), $rows[$name] ?? []);
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
     * @param array<string, list<list<mixed>>> $rows
     * @param array<string, array<string, mixed>> $extraSheets
     */
    private function guideSheet(Worksheet $sheet, array $meta, array $rows, array $extraSheets): void
    {
        $sheet->setTitle('PETUNJUK');
        $sheet->setShowGridlines(false);
        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(58);
        $sheet->getColumnDimension('D')->setWidth(58);
        $sheet->getColumnDimension('E')->setWidth(30);

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
        foreach ([
            'Siapkan gambar lebih dulu. Unggah gambar yang dibutuhkan (adegan Cari Objek, gambar Susun Gambar, gambar benda, gambar Pustaka) di Panel GELITA → menu Gambar & suara, lalu catat kode berkasnya (mis. challenge.tmg-4.scene). Kode itulah yang ditulis di kolom gambar (kolom yang namanya memuat kata media, mis. media_asset_key atau scene_media_key). Daftar gambar yang dibutuhkan berkas ini ada di sheet DAFTAR_MEDIA (bila ada). Gambar juga boleh diunggah belakangan dengan kode yang sama.',
            'Isi sheet dari kiri ke kanan: nodes → distractors → passages → items → options / pieces / sources → hints → library → library_media. Sheet yang tidak diperlukan boleh dibiarkan kosong. Satu baris = satu data. Jangan menggabungkan sel (merge) dan jangan memakai rumus.',
            'Untuk kolom berkode (jenis soal, wilayah, 0/1, dan sebagainya), klik selnya lalu pilih dari daftar pilihan yang muncul, supaya tidak salah ketik.',
            'Simpan sebagai berkas Excel (.xlsx atau "Excel Workbook"), paling besar 20 MB.',
            'Buka Panel GELITA → Konten permainan → Impor soal dari Excel, pilih berkas, lalu tekan Periksa berkas. Pemeriksaan TIDAK mengubah apa pun. Hasilnya berupa kesalahan (merah, harus diperbaiki) dan peringatan (kuning, boleh dilanjutkan); setiap temuan menyebut nama sheet dan nomor barisnya.',
            'Bila tidak ada kesalahan, tekan Simpan soal ke permainan. Semua isi disimpan sekaligus: bila ada satu baris yang gagal, tidak ada yang berubah.',
            'Setelah disimpan: buka Konten permainan → Periksa kelengkapan untuk memastikan setiap tantangan punya soal yang cukup, lalu coba mainkan sendiri sebagai siswa.',
            'Mengimpor ulang berkas yang sama aman: data dicocokkan lewat kodenya (node_ref, passage_key, item_key, option_key, nomor halaman) lalu diperbarui, bukan digandakan. Kunci jawaban soal yang SUDAH dijawab siswa tidak dapat diubah — buat soal baru dengan kode baru.',
            'Untuk petugas teknis: impor juga dapat dijalankan di server dengan perintah php spark gelita:bank:import berkas.xlsx --dry-run (periksa saja), lalu tanpa --dry-run.',
        ] as $index => $text) {
            $r = $this->step($sheet, $r, (string) ($index + 1), $text);
        }

        $r++;
        $r = $this->heading($sheet, $r, 'Isi setiap sheet');
        $table = [];

        foreach (self::SHEETS as $name => [$title, $purpose, $how]) {
            $table[] = [$name, $title, $purpose . ' ' . $how, isset($rows[$name]) ? count($rows[$name]) . ' baris' : '—'];
        }

        foreach ($extraSheets as $name => $extra) {
            $table[] = [$name, 'Lampiran (tidak ikut diimpor)', (string) ($extra['note'] ?? ''), count($extra['rows'] ?? []) . ' baris'];
        }

        $r = $this->table($sheet, $r, ['Sheet', 'Isinya', 'Kegunaan & cara mengisi', 'Jumlah baris saat ini'], $table);

        $r++;
        $r = $this->heading($sheet, $r, 'Jenis tantangan dan jenis soal');
        $r = $this->table($sheet, $r, ['Tantangan', 'Jenis tantangan', 'Jenis soal (interaction_type)', 'Catatan'], [
            ['tmg-1, mgl-1', 'Susun Gambar — kepingan 3×3', 'puzzle_arrange', 'Setiap soal WAJIB punya gambar (kolom media_asset_key), berbentuk persegi ±900×900 px. Kolom prompt_id berisi keterangan gambar yang dibaca setelah gambar tersusun.'],
            ['wnb-1', 'Susun Urutan — urutkan kartu', 'ordering', 'Kartu diisi di sheet pieces. answer_id = urutan benar kode kartu, dipisah koma, mis. c,a,d,b,e.'],
            ['tmg-2, wnb-2', 'Isi Rumpang — pilih kata', 'fill_blank_bank', 'Kalimat dengan ___ (tiga garis bawah) sebagai tempat kosong; jawabannya satu kata; kata pengecoh diisi di sheet distractors.'],
            ['mgl-2', 'Isi Rumpang — ketik jawaban', 'fill_blank_free', 'Beberapa jawaban yang diterima dipisah | (garis tegak), mis. Syailendra|Sailendra. Huruf besar/kecil tidak dibedakan.'],
            ['tmg-3, wnb-3', 'Benar atau Salah', 'verdict_card', 'answer_id = benar atau salah. wnb-3 memakai dua kartu sumber (sheet sources).'],
            ['mgl-3', 'Benar atau Salah — dengan pendapat & alasan', 'verdict_reason', 'answer_id = benar, salah, atau pendapat. Isi sample_reason_id sebagai contoh alasan untuk guru.'],
            ['tmg-5, mgl-4, mgl-5, wnb-5', 'Kuis Pilihan — pilihan ganda', 'single_choice', 'Empat pilihan jawaban di sheet options, tepat satu yang is_correct = 1. Kolom answer_id dikosongkan.'],
            ['wnb-4', 'Kuis Pilihan — sumber tepercaya', 'source_trust', 'Seperti pilihan ganda, tetapi pilihannya berupa jenis sumber informasi.'],
            ['tmg-4', 'Cari Objek — cari benda di gambar', 'find_object', 'Perlu gambar adegan (kolom scene_media_key di sheet nodes) dan gambar setiap benda. Benda yang dicari: answer_id = target, decoy = 0. Benda jebakan: answer_id = decoy, decoy = 1, scorable = 0, dan isi wrong_feedback_id.'],
        ]);

        $r++;
        $r = $this->heading($sheet, $r, 'Kunci jawaban untuk setiap jenis soal (kolom answer_id / answer_en)');
        $r = $this->table($sheet, $r, ['Jenis soal', 'Isi answer_id', 'Isi answer_en', 'Contoh'], [
            ['puzzle_arrange', 'kosongkan (susunan benar selalu 1–9 dari kiri atas)', 'kosongkan', '—'],
            ['ordering', 'kode kartu sesuai urutan yang benar, dipisah koma', 'kosongkan', 'c,a,d,b'],
            ['fill_blank_bank', 'satu kata/frasa jawaban (otomatis masuk pilihan kata)', 'kata jawaban dalam bahasa Inggris', 'kopi / coffee'],
            ['fill_blank_free', 'semua jawaban yang diterima, dipisah |', 'jawaban bahasa Inggris, dipisah |', 'stupa|stupha / stupa'],
            ['verdict_card', 'benar atau salah', 'kosongkan', 'salah'],
            ['verdict_reason', 'benar, salah, atau pendapat', 'kosongkan', 'pendapat'],
            ['single_choice / source_trust', 'KOSONGKAN — kunci diambil dari sheet options', 'kosongkan', '—'],
            ['find_object', 'target (benda yang dicari) atau decoy (jebakan)', 'kosongkan', 'target'],
        ]);

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

        $r++;
        $r = $this->heading($sheet, $r, 'Hal yang sering keliru');
        foreach ([
            'Menerjemahkan atau mengganti judul kolom / nama sheet → kolom dianggap kosong atau sheet dilewati.',
            'Menulis "benar"/"salah" atau "ya"/"tidak" di kolom 0/1 → untuk kolom is_correct, scorable, decoy, is_active, require_reason, dan use_word_bank pakai 0 (tidak) atau 1 (ya).',
            'Soal pilihan ganda dengan dua jawaban benar atau tanpa jawaban benar → muncul sebagai kesalahan saat diperiksa.',
            'Soal Isi Rumpang tanpa ___ (tiga garis bawah) di kalimatnya → siswa tidak melihat tempat kosong.',
            'Kode soal (item_key) diubah setelah dijawab siswa → dianggap soal baru, dan jawaban lama tidak tersambung lagi.',
            'Jumlah soal sama persis dengan items_per_round → tidak ada soal cadangan; sediakan 1,5–2 kali lipat.',
            'Aturan penilaian (skor & bintang) TIDAK diatur di berkas ini: setiap tantangan memakai aturan penilaian yang aktif (bawaan GELITA_V2). Aturan itu hanya diubah lewat Panel GELITA → Pengaturan penelitian → Aturan penilaian bila penelitian memerlukannya.',
        ] as $text) {
            $r = $this->step($sheet, $r, '•', $text);
        }
    }

    private function dictionarySheet(Worksheet $sheet): void
    {
        $sheet->setTitle('KAMUS_KOLOM');
        $headers = ['Sheet', 'Kode kolom (judul di baris 1)', 'Nama dalam bahasa Indonesia', 'Wajib diisi?', 'Arti & cara mengisi', 'Yang boleh diisi', 'Contoh'];
        $sheet->fromArray($headers, null, 'A1');
        $row = 2;

        foreach (self::COLUMNS as $name => $columns) {
            foreach ($columns as $column => [$label, $status, $meaning, $allowed, $example]) {
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
