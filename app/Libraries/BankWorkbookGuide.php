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

    /** Urutan & kegunaan sheet data. [judul Indonesia, fungsi, kapan diisi] */
    public const SHEETS = [
        'nodes'         => ['Tantangan', 'Judul, instruksi, kartu misi, dan pengaturan 15 tantangan yang SUDAH ADA (tmg-1 … wnb-5). Sheet ini tidak membuat tantangan baru.', 'Satu baris per tantangan. Sel kosong = nilai lama dipertahankan.'],
        'distractors'   => ['Kata pengecoh', 'Kata-kata SALAH yang ikut muncul di bank kata tantangan rumpang (tmg-2, wnb-2).', 'Satu baris per kata. Seluruh pengecoh satu tantangan diganti isi sheet ini.'],
        'passages'      => ['Teks bacaan', 'Bacaan panjang yang tampil di samping soal. Dirujuk butir lewat kolom passage_key.', 'Satu baris per bacaan. Isi Indonesia dan English wajib.'],
        'items'         => ['Butir soal', 'Bank soal: setiap pertanyaan, pernyataan, gambar puzzle, objek yang dicari, atau urutan yang disusun.', 'Satu baris per butir. Siswa mendapat butir acak sebanyak items_per_round.'],
        'options'       => ['Opsi jawaban', 'Pilihan A–D untuk butir single_choice dan source_trust.', 'Satu baris per opsi; tepat SATU opsi benar per butir.'],
        'pieces'        => ['Potongan urutan', 'Kartu-kartu yang disusun siswa pada butir ordering (Wonosobo tantangan 1).', 'Satu baris per kartu.'],
        'sources'       => ['Dua sumber', 'Sumber A dan Sumber B yang dibandingkan pada butir pernyataan Wonosobo tantangan 3.', 'Dua baris per butir.'],
        'hints'         => ['Petunjuk', 'Petunjuk yang boleh dibuka siswa (membukanya mengurangi skor kemandirian).', 'Satu baris per petunjuk: untuk satu tantangan (node_ref) ATAU satu butir (item_key).'],
        'library'       => ['Pustaka Kedu — halaman', 'Halaman buku bacaan tiap wilayah di menu Pustaka.', 'Satu baris per halaman. Halaman bernomor sama ditimpa.'],
        'library_media' => ['Pustaka Kedu — gambar & video', 'Galeri gambar/video tiap halaman Pustaka: berkas terunggah ATAU tautan YouTube/Drive/Vimeo/Wikimedia Commons.', 'Satu baris per media. Halaman yang punya baris di sini diganti seluruh galerinya.'],
    ];

    /**
     * Kamus kolom: [nama Indonesia, status (Wajib|Bersyarat|Opsional), arti & cara mengisi, isian yang boleh, contoh].
     *
     * @var array<string, array<string, array{0: string, 1: string, 2: string, 3: string, 4: string}>>
     */
    public const COLUMNS = [
        'nodes' => [
            'node_ref'             => ['Kode tantangan', 'Wajib', 'Tantangan yang diubah: awalan wilayah + nomor pos di peta.', 'tmg-1 … tmg-5 (Temanggung), mgl-1 … mgl-5 (Magelang), wnb-1 … wnb-5 (Wonosobo)', 'tmg-2'],
            'title_id'             => ['Judul (Indonesia)', 'Opsional', 'Judul tantangan di peta wilayah dan kartu misi. Kosong = judul lama.', 'teks, maks. 150 karakter', 'Lengkapi Kalimat Temanggung'],
            'title_en'             => ['Judul (English)', 'Opsional', 'Terjemahan judul untuk pemain berbahasa Inggris.', 'teks, maks. 150 karakter', 'Complete the Temanggung Sentence'],
            'instruction_id'       => ['Instruksi (Indonesia)', 'Opsional', 'Perintah singkat yang tampil di atas arena permainan.', 'teks', 'Pilih satu kata yang tepat dari bank kata.'],
            'instruction_en'       => ['Instruksi (English)', 'Opsional', 'Terjemahan instruksi.', 'teks', 'Choose the one correct word from the word bank.'],
            'description_id'       => ['Kartu misi (Indonesia)', 'Opsional', 'Cerita/tujuan singkat di kartu misi sebelum tantangan dimulai.', 'teks, 1–3 kalimat', 'Mbah Kedu meminta Jaka melengkapi catatan perjalanan.'],
            'description_en'       => ['Kartu misi (English)', 'Opsional', 'Terjemahan kartu misi.', 'teks', 'Mbah Kedu asks Jaka to complete the travel notes.'],
            'items_per_round'      => ['Butir per ronde', 'Opsional', 'Jumlah butir yang diambil acak dari bank setiap kali siswa bermain. Bank soal sebaiknya 1,5–2× angka ini agar ada cadangan.', 'angka bulat ≥ 1', '4'],
            'verdict_options'      => ['Pilihan penilaian', 'Bersyarat', 'Khusus tantangan jenis boleh (tmg-3, mgl-3, wnb-3): tombol penilaian yang tampil. Tulis dipisah koma tanpa spasi.', 'benar,salah   ATAU   benar,salah,pendapat', 'benar,salah,pendapat'],
            'require_reason'       => ['Wajib alasan', 'Bersyarat', 'Khusus jenis boleh: 1 = siswa wajib menulis alasan (butir verdict_reason).', '0 atau 1', '1'],
            'use_word_bank'        => ['Pakai bank kata', 'Bersyarat', 'Khusus jenis rumpang: 1 = siswa memilih dari bank kata; 0 = siswa mengetik jawaban.', '0 atau 1', '1'],
            'distractor_count'     => ['Jumlah pengecoh', 'Bersyarat', 'Khusus rumpang bank kata: berapa kata pengecoh (dari sheet distractors) yang ikut ditampilkan. Sediakan pengecoh minimal sebanyak ini.', 'angka bulat ≥ 0', '2'],
            'scene_media_key'      => ['Gambar adegan', 'Opsional', 'asset_key gambar adegan tantangan — WAJIB untuk cari objek (tmg-4), tempat objek disembunyikan. Unggah dulu di Panel → Media.', 'asset_key terdaftar', 'challenge.tmg-4.scene'],
            'background_media_key' => ['Latar tantangan', 'Opsional', 'asset_key latar layar tantangan. Kosong = memakai latar wilayah.', 'asset_key terdaftar', 'challenge.tmg-4.bg'],
        ],
        'distractors' => [
            'node_ref' => ['Kode tantangan', 'Wajib', 'Tantangan rumpang yang memakai bank kata.', 'tmg-2, wnb-2 (atau node rumpang lain dengan use_word_bank = 1)', 'tmg-2'],
            'text_id'  => ['Kata pengecoh (Indonesia)', 'Wajib', 'Kata yang TIDAK tepat untuk rumpang mana pun, tetapi masuk akal agar siswa berpikir.', 'satu kata / frasa pendek', 'salju'],
            'text_en'  => ['Kata pengecoh (English)', 'Opsional', 'Terjemahan pengecoh. Kosong = sama dengan kolom Indonesia.', 'satu kata / frasa pendek', 'snow'],
        ],
        'passages' => [
            'passage_key'      => ['Kode bacaan', 'Wajib', 'Kode unik teks bacaan. Dipakai kolom passage_key di sheet items. Jangan diubah setelah dipakai.', 'huruf kecil, angka, tanda minus; unik', 'tmg-teks-a'],
            'level_code'       => ['Wilayah', 'Wajib', 'Wilayah pemilik bacaan. Bacaan hanya boleh dipakai butir di wilayah yang sama.', 'temanggung | magelang | wonosobo', 'temanggung'],
            'title_id'         => ['Judul bacaan (Indonesia)', 'Opsional', 'Judul di atas teks bacaan.', 'teks', 'Tanah Subur di Kaki Dua Gunung'],
            'title_en'         => ['Judul bacaan (English)', 'Opsional', 'Terjemahan judul.', 'teks', 'Fertile Land at the Foot of Two Mountains'],
            'body_id'          => ['Isi bacaan (Indonesia)', 'Wajib', 'Teks yang dibaca siswa. Tulis kalimat pendek dan jelas sesuai jenjang SD/SMP.', 'teks panjang; baris kosong = paragraf baru', 'Temanggung berada di antara …'],
            'body_en'          => ['Isi bacaan (English)', 'Wajib', 'Terjemahan isi bacaan.', 'teks panjang', 'Temanggung lies between …'],
            'media_asset_key'  => ['Gambar bacaan', 'Opsional', 'asset_key gambar pendamping bacaan (unggah dulu di Panel → Media).', 'asset_key terdaftar', 'passage.tmg-teks-a'],
            'reference_source' => ['Sumber rujukan', 'Opsional', 'Dari mana fakta dalam bacaan berasal (buku, situs resmi, jurnal).', 'teks', 'Pemkab Temanggung; BPS Temanggung 2024'],
        ],
        'items' => [
            'item_key'          => ['Kode butir', 'Wajib', 'Kode unik butir: kode tantangan + nomor 2 digit. JANGAN diubah setelah butir dijawab siswa (data penelitian).', '{node_ref}-01, {node_ref}-02, …', 'tmg-2-01'],
            'node_ref'          => ['Kode tantangan', 'Wajib', 'Tantangan pemilik butir.', 'tmg-1 … wnb-5', 'tmg-2'],
            'sequence'          => ['Urutan di bank', 'Opsional', 'Urutan tampil di editor admin. Tidak memengaruhi acakan siswa.', 'angka bulat', '1'],
            'interaction_type'  => ['Jenis interaksi', 'Wajib', 'Bentuk soal; harus cocok dengan jenis tantangan. puzzle: puzzle_arrange/ordering · rumpang: fill_blank_bank/fill_blank_free · boleh: verdict_card/verdict_reason · pilihan: single_choice/source_trust · cari: find_object.', 'puzzle_arrange | ordering | fill_blank_bank | fill_blank_free | verdict_card | verdict_reason | single_choice | source_trust | find_object', 'fill_blank_bank'],
            'indicator'         => ['Indikator', 'Opsional', 'Indikator pembelajaran yang diukur. Kosong = ikut indikator tantangan.', 'literasi | budaya | sikap', 'literasi'],
            'prompt_id'         => ['Soal / pernyataan (Indonesia)', 'Wajib', 'Rumpang: tandai bagian kosong dengan ___ (tiga garis bawah). Cari objek: teks petunjuk benda yang dicari. Puzzle: keterangan gambar yang dibaca setelah puzzle selesai. Boleh: pernyataan yang dinilai.', 'teks', 'Temanggung terletak di antara Gunung Sindoro dan Gunung ___.'],
            'prompt_en'         => ['Soal / pernyataan (English)', 'Opsional', 'Terjemahan soal (rumpang: tetap pakai ___). Kosong = pemain English melihat teks Indonesia.', 'teks', 'Temanggung lies between Mount Sindoro and Mount ___.'],
            'source_text_id'    => ['Teks sumber butir (Indonesia)', 'Opsional', 'Kutipan/informasi pendek khusus butir ini yang tampil di atas soal (bila tidak memakai passage).', 'teks', 'Pengumuman: Festival dimulai pukul 08.00.'],
            'source_text_en'    => ['Teks sumber butir (English)', 'Opsional', 'Terjemahan teks sumber.', 'teks', 'Notice: The festival starts at 08.00.'],
            'passage_key'       => ['Kode bacaan', 'Opsional', 'Bacaan dari sheet passages (atau yang sudah ada di database) yang dipakai butir ini.', 'passage_key', 'tmg-teks-a'],
            'answer_id'         => ['Kunci jawaban (Indonesia)', 'Bersyarat', 'Lihat tabel "Kunci jawaban per jenis interaksi" di sheet PETUNJUK. single_choice/source_trust: KOSONGKAN (kunci diambil dari opsi is_correct = 1).', 'tergantung jenis interaksi', 'Sumbing'],
            'answer_en'         => ['Kunci jawaban (English)', 'Bersyarat', 'Untuk rumpang: jawaban versi English. Kosong = sama dengan kolom Indonesia.', 'tergantung jenis interaksi', 'Sumbing'],
            'sample_reason_id'  => ['Contoh alasan (Indonesia)', 'Bersyarat', 'Khusus verdict_reason: contoh alasan benar untuk rubrik guru. Tidak dinilai otomatis.', 'teks', 'Kata "paling indah" adalah pendapat pribadi.'],
            'sample_reason_en'  => ['Contoh alasan (English)', 'Bersyarat', 'Terjemahan contoh alasan.', 'teks', 'The words "most beautiful" are a personal opinion.'],
            'x'                 => ['Posisi kiri (%)', 'Bersyarat', 'Khusus find_object: jarak objek dari tepi KIRI gambar adegan, dalam persen lebar.', 'angka 0–100', '18'],
            'y'                 => ['Posisi atas (%)', 'Bersyarat', 'Khusus find_object: jarak objek dari tepi ATAS gambar adegan, dalam persen tinggi.', 'angka 0–100', '62'],
            'w'                 => ['Lebar objek (%)', 'Bersyarat', 'Khusus find_object: lebar objek dalam persen lebar adegan. Jaga agar objek tidak saling menumpuk.', 'angka 5–30', '12'],
            'decoy'             => ['Objek jebakan', 'Bersyarat', 'Khusus find_object: 1 = objek jebakan (bukan dari wilayah ini). Jebakan selalu tampil, tidak dinilai (scorable = 0), dan wajib punya wrong_feedback.', '0 atau 1', '0'],
            'wrong_feedback_id' => ['Penjelasan salah klik (Indonesia)', 'Bersyarat', 'Khusus objek jebakan: penjelasan yang tampil saat siswa mengklik jebakan.', 'teks', 'Angklung berasal dari Jawa Barat, bukan Temanggung.'],
            'wrong_feedback_en' => ['Penjelasan salah klik (English)', 'Bersyarat', 'Terjemahan penjelasan salah klik.', 'teks', 'Angklung comes from West Java, not Temanggung.'],
            'digital_pillar'    => ['Pilar literasi digital', 'Bersyarat', 'Khusus Wonosobo tantangan 5: pilar yang diukur butir ini (untuk analitik).', 'digital_skills (cakap) | digital_ethics (etika) | digital_safety (aman) | digital_culture (budaya)', 'digital_safety'],
            'media_asset_key'   => ['Gambar butir', 'Bersyarat', 'WAJIB untuk puzzle_arrange (gambar yang dipotong 3×3, persegi) dan find_object (gambar benda, PNG transparan). Unggah dulu di Panel → Media, atau pasang nanti dari editor tantangan.', 'asset_key terdaftar', 'challenge.item.tmg-1-01'],
            'scorable'          => ['Dinilai', 'Opsional', '1 = dihitung dalam skor (bawaan). 0 = tidak dinilai (wajib untuk objek jebakan).', '0 atau 1', '1'],
            'review_status'     => ['Status tinjauan', 'Opsional', 'draft = draf; needs_verification = fakta perlu dicek (muncul peringatan); verified = sudah diverifikasi ahli/guru.', 'draft | needs_verification | verified', 'verified'],
            'review_note'       => ['Catatan tinjauan', 'Opsional', 'Catatan untuk peninjau: apa yang perlu dicek atau sudah dicek.', 'teks', 'Tinggi gunung dicek dengan data PVMBG.'],
            'reference_source'  => ['Sumber rujukan', 'Opsional', 'Sumber fakta butir (disarankan untuk butir budaya/sejarah).', 'teks', 'UNESCO World Heritage Centre, Borobudur'],
        ],
        'options' => [
            'option_key'      => ['Kode opsi', 'Wajib', 'Kode unik opsi: kode butir + huruf.', '{item_key}-a … {item_key}-d', 'tmg-5-01-a'],
            'item_key'        => ['Kode butir', 'Wajib', 'Butir pemilik opsi (harus ada di sheet items).', 'item_key', 'tmg-5-01'],
            'label_id'        => ['Teks opsi (Indonesia)', 'Wajib', 'Teks pilihan jawaban.', 'teks singkat', 'Sindoro dan Sumbing'],
            'label_en'        => ['Teks opsi (English)', 'Opsional', 'Terjemahan opsi. Kosong = sama dengan kolom Indonesia.', 'teks singkat', 'Sindoro and Sumbing'],
            'is_correct'      => ['Jawaban benar?', 'Wajib', '1 = opsi benar. Setiap butir wajib punya TEPAT SATU opsi bernilai 1.', '0 atau 1', '1'],
            'feedback_id'     => ['Umpan balik (Indonesia)', 'Opsional', 'Penjelasan yang tampil setelah siswa memilih opsi ini. Sangat disarankan untuk opsi benar.', 'teks', 'Tepat! Teks menyebut kedua gunung itu.'],
            'feedback_en'     => ['Umpan balik (English)', 'Opsional', 'Terjemahan umpan balik.', 'teks', 'Correct! The text names both mountains.'],
            'media_asset_key' => ['Gambar opsi', 'Opsional', 'asset_key gambar opsi (400 × 400 px).', 'asset_key terdaftar', 'challenge.option.mgl-4-01-a'],
            'display_order'   => ['Urutan tampil', 'Opsional', 'Urutan opsi A–D bila tidak diacak.', '1–4', '1'],
        ],
        'pieces' => [
            'item_key'  => ['Kode butir', 'Wajib', 'Butir ordering pemilik potongan.', 'item_key', 'wnb-1-01'],
            'piece_key' => ['Kode potongan', 'Wajib', 'Kode kartu. Urutan benar ditulis di kolom answer_id butir, dipisah koma.', 'a, b, c, … atau 1, 2, 3, …', 'a'],
            'text_id'   => ['Teks kartu (Indonesia)', 'Wajib', 'Isi satu langkah/kejadian.', 'teks singkat', 'Siapkan mi dan kol.'],
            'text_en'   => ['Teks kartu (English)', 'Opsional', 'Terjemahan. Kosong = sama dengan Indonesia.', 'teks singkat', 'Prepare the noodles and cabbage.'],
        ],
        'sources' => [
            'item_key' => ['Kode butir', 'Wajib', 'Butir pernyataan (verdict_card) yang membandingkan dua sumber.', 'item_key', 'wnb-3-01'],
            'label_id' => ['Nama sumber (Indonesia)', 'Wajib', 'Label kartu sumber.', 'teks singkat', 'Sumber A'],
            'label_en' => ['Nama sumber (English)', 'Opsional', 'Terjemahan label.', 'teks singkat', 'Source A'],
            'kind'     => ['Jenis sumber', 'Wajib', 'official = resmi (pemerintah/pengelola); anonymous = akun/komentar tanpa nama; chain_message = pesan berantai; blog = blog/tulisan pribadi.', 'official | anonymous | chain_message | blog', 'official'],
            'text_id'  => ['Isi sumber (Indonesia)', 'Wajib', 'Kutipan isi sumber.', 'teks', 'Pengumuman pengelola: pengunjung dilarang memanjat candi.'],
            'text_en'  => ['Isi sumber (English)', 'Opsional', 'Terjemahan isi sumber.', 'teks', 'Management notice: visitors must not climb the temple.'],
        ],
        'hints' => [
            'node_ref' => ['Kode tantangan', 'Bersyarat', 'Isi untuk petunjuk umum satu tantangan. Isi SALAH SATU: node_ref atau item_key.', 'tmg-1 … wnb-5', 'tmg-2'],
            'item_key' => ['Kode butir', 'Bersyarat', 'Isi untuk petunjuk khusus satu butir.', 'item_key', 'tmg-2-01'],
            'sequence' => ['Urutan petunjuk', 'Opsional', '1 = petunjuk yang dibuka pertama, 2 = berikutnya, dst.', 'angka bulat ≥ 1', '1'],
            'text_id'  => ['Petunjuk (Indonesia)', 'Wajib', 'Arahan cara berpikir — jangan langsung memberi jawaban.', 'teks', 'Baca seluruh kalimat dulu.'],
            'text_en'  => ['Petunjuk (English)', 'Opsional', 'Terjemahan petunjuk.', 'teks', 'Read the whole sentence first.'],
        ],
        'library' => [
            'level_code' => ['Wilayah', 'Wajib', 'Wilayah pemilik halaman Pustaka.', 'temanggung | magelang | wonosobo', 'temanggung'],
            'sequence'   => ['Nomor halaman', 'Wajib', 'Urutan halaman di buku. Nomor yang sudah ada = halaman itu DITIMPA.', 'angka 1–999', '1'],
            'title_id'   => ['Judul halaman (Indonesia)', 'Wajib', 'Judul halaman buku.', 'teks, maks. 250 karakter', 'Mengenal Temanggung'],
            'title_en'   => ['Judul halaman (English)', 'Wajib', 'Terjemahan judul.', 'teks, maks. 250 karakter', 'Getting to Know Temanggung'],
            'body_id'    => ['Isi halaman (Indonesia)', 'Wajib', 'Teks halaman dengan format ringan (lihat PETUNJUK): baris kosong = paragraf, "## " = subjudul, "- " = daftar, "**tebal**", "> " = kotak fakta, "Sumber:" = catatan rujukan.', 'teks panjang', '## Tanah yang subur …'],
            'body_en'    => ['Isi halaman (English)', 'Opsional', 'Terjemahan isi (format sama). Kosong = pemain English membaca teks Indonesia.', 'teks panjang', '## Fertile land …'],
            'is_active'  => ['Tampil?', 'Opsional', '1 = tampil di permainan (bawaan); 0 = disembunyikan.', '0 atau 1', '1'],
        ],
        'library_media' => [
            'level_code'       => ['Wilayah', 'Wajib', 'Wilayah halaman tujuan.', 'temanggung | magelang | wonosobo', 'temanggung'],
            'page_sequence'    => ['Nomor halaman', 'Wajib', 'Nomor halaman Pustaka tujuan (sheet library atau yang sudah ada).', 'angka ≥ 1', '1'],
            'sequence'         => ['Urutan media', 'Opsional', 'Urutan di galeri. Media pertama tampil paling besar.', 'angka ≥ 1', '1'],
            'media_kind'       => ['Jenis media', 'Opsional', 'image = gambar; video = video. Tautan YouTube/Vimeo otomatis video.', 'image | video', 'image'],
            'media_asset_key'  => ['Berkas terunggah', 'Bersyarat', 'asset_key berkas yang sudah diunggah di Panel → Media. Isi SALAH SATU: kolom ini atau external_url.', 'asset_key terdaftar', 'library.temanggung.p1.1'],
            'external_url'     => ['Tautan media', 'Bersyarat', 'Tautan YouTube, Google Drive (dibagikan "siapa saja yang memiliki link"), Vimeo, halaman berkas Wikimedia Commons, atau alamat berkas .jpg/.png/.mp4. Video pihak ketiga baru dimuat saat siswa menekan Putar.', 'https://…', 'https://youtu.be/xxxxxxxxxxx'],
            'poster_media_key' => ['Poster video', 'Opsional', 'asset_key gambar sampul untuk berkas video (bukan tautan).', 'asset_key terdaftar', 'library.temanggung.p1.2.poster'],
            'caption_id'       => ['Keterangan (Indonesia)', 'Opsional', 'Keterangan singkat di bawah gambar/video; juga teks alternatif untuk pembaca layar.', 'teks, maks. 500 karakter', 'Gunung Sindoro dilihat dari Kledung'],
            'caption_en'       => ['Keterangan (English)', 'Opsional', 'Terjemahan keterangan.', 'teks, maks. 500 karakter', 'Mount Sindoro seen from Kledung'],
            'credit'           => ['Kredit / lisensi', 'Opsional', 'Pemilik & lisensi media dari pihak lain. Wajib diisi bila memakai foto/video orang lain.', 'teks, maks. 300 karakter', 'Wikimedia Commons, CC BY-SA 4.0'],
        ],
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
            ->setDescription('Workbook impor bank soal GELITA. Sheet PETUNJUK dan KAMUS_KOLOM tidak ikut diimpor.');
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
            $sheet->getStyle('B' . $r)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($r)->setRowHeight(32);
            $r++;
        }

        $r++;
        $r = $this->heading($sheet, $r, 'Mengapa header kolom berbahasa Inggris?');
        $r = $this->paragraph($sheet, $r, 'Baris pertama setiap sheet data adalah KODE KOLOM yang dibaca sistem, jadi tidak boleh diterjemahkan, diubah, atau dipindah. Arti setiap kolom dalam bahasa Indonesia ada di sheet KAMUS_KOLOM, di catatan (segitiga merah) pada setiap sel header, dan di kotak bantuan kecil yang muncul saat Anda memilih sel di bawah header. Warna header: emas tua = WAJIB, krem = BERSYARAT (wajib untuk jenis soal tertentu), abu-biru = OPSIONAL.');
        $r = $this->legend($sheet, $r);

        $r++;
        $r = $this->heading($sheet, $r, 'Langkah mengisi dan mengimpor');
        foreach ([
            'Unggah dulu gambar yang dibutuhkan (adegan cari objek, gambar puzzle, gambar objek, gambar Pustaka) di Panel Admin → Media. Catat asset_key-nya. Daftar gambar yang dibutuhkan workbook ini ada di sheet DAFTAR_MEDIA (bila ada).',
            'Isi sheet data dari kiri ke kanan: nodes → distractors → passages → items → options / pieces / sources → hints → library → library_media. Satu baris = satu data. Jangan menggabungkan sel (merge) dan jangan memakai rumus.',
            'Kolom berakhiran _id berbahasa Indonesia, _en berbahasa Inggris. Bila kolom _en dikosongkan, pemain berbahasa Inggris membaca teks Indonesia — sebaiknya tetap diisi.',
            'Simpan sebagai .xlsx (Excel Workbook), maksimal 20 MB.',
            'Panel Admin → Konten → Impor bank soal → pilih berkas → Pratinjau. Pratinjau TIDAK mengubah apa pun: ia menampilkan galat (merah, wajib diperbaiki) dan peringatan (kuning, boleh lanjut).',
            'Bila galat = 0, tekan Jalankan impor. Seluruh isi masuk dalam satu transaksi: satu baris gagal = tidak ada yang berubah.',
            'Setelah impor: Konten → Verifikasi konten untuk memastikan setiap tantangan punya bank soal cukup, lalu coba mainkan sebagai siswa.',
            'Impor ulang aman: data dicocokkan lewat kode (node_ref, passage_key, item_key, option_key, nomor halaman) lalu diperbarui. Kunci jawaban butir yang SUDAH dijawab siswa tidak dapat diubah — buat butir baru dengan kode baru.',
            'Alternatif lewat server: php spark gelita:bank:import berkas.xlsx --dry-run (pratinjau), lalu tanpa --dry-run.',
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
            $table[] = [$name, 'Lampiran (tidak diimpor)', (string) ($extra['note'] ?? ''), count($extra['rows'] ?? []) . ' baris'];
        }

        $r = $this->table($sheet, $r, ['Sheet', 'Nama', 'Fungsi & cara mengisi', 'Isi saat ini'], $table);

        $r++;
        $r = $this->heading($sheet, $r, 'Jenis tantangan dan jenis butir');
        $r = $this->table($sheet, $r, ['Tantangan', 'Jenis (engine)', 'interaction_type yang sah', 'Catatan'], [
            ['tmg-1, mgl-1', 'puzzle — susun gambar 3×3', 'puzzle_arrange', 'Setiap butir WAJIB punya gambar (media_asset_key), persegi ±900×900 px. prompt = keterangan gambar.'],
            ['wnb-1', 'puzzle — urutkan kartu', 'ordering', 'Kartu di sheet pieces; answer_id = urutan benar kode kartu, mis. c,a,d,b,e.'],
            ['tmg-2, wnb-2', 'rumpang — bank kata', 'fill_blank_bank', 'Kalimat dengan ___ ; jawaban satu kata; pengecoh di sheet distractors.'],
            ['mgl-2', 'rumpang — ketik jawaban', 'fill_blank_free', 'Jawaban alternatif dipisah | (garis tegak), mis. Syailendra|Sailendra. Huruf besar/kecil tidak dibedakan.'],
            ['tmg-3, wnb-3', 'boleh — benar/salah', 'verdict_card', 'answer_id = benar atau salah. wnb-3 memakai dua sumber (sheet sources).'],
            ['mgl-3', 'boleh — benar/salah/pendapat + alasan', 'verdict_reason', 'answer_id = benar, salah, atau pendapat; isi sample_reason untuk rubrik guru.'],
            ['tmg-5, mgl-4, mgl-5, wnb-5', 'pilihan — pilihan ganda', 'single_choice', 'Empat opsi di sheet options, tepat satu is_correct = 1. answer_id dikosongkan.'],
            ['wnb-4', 'pilihan — sumber terpercaya', 'source_trust', 'Seperti pilihan ganda: opsi berupa jenis sumber informasi.'],
            ['tmg-4', 'cari — cari objek di adegan', 'find_object', 'Butuh gambar adegan (scene_media_key di sheet nodes) + gambar tiap objek. Target: answer_id = target, decoy = 0. Jebakan: answer_id = decoy, decoy = 1, scorable = 0, isi wrong_feedback.'],
        ]);

        $r++;
        $r = $this->heading($sheet, $r, 'Kunci jawaban per jenis interaksi (kolom answer_id / answer_en)');
        $r = $this->table($sheet, $r, ['interaction_type', 'Isi answer_id', 'Isi answer_en', 'Contoh'], [
            ['puzzle_arrange', 'kosong (susunan benar selalu 1–9 dari kiri atas)', 'kosong', '—'],
            ['ordering', 'kode kartu berurutan, dipisah koma', 'kosong', 'c,a,d,b'],
            ['fill_blank_bank', 'satu kata/frasa jawaban (masuk bank kata)', 'kata jawaban versi English', 'kopi / coffee'],
            ['fill_blank_free', 'semua jawaban yang diterima, dipisah |', 'jawaban English, dipisah |', 'stupa|stupha / stupa'],
            ['verdict_card', 'benar | salah', 'kosong', 'salah'],
            ['verdict_reason', 'benar | salah | pendapat', 'kosong', 'pendapat'],
            ['single_choice / source_trust', 'KOSONGKAN — kunci dari sheet options', 'kosong', '—'],
            ['find_object', 'target (objek dicari) | decoy (jebakan)', 'kosong', 'target'],
        ]);

        $r++;
        $r = $this->heading($sheet, $r, 'Format teks Pustaka Kedu (kolom body_id / body_en sheet library)');
        $r = $this->table($sheet, $r, ['Ketik', 'Hasil di layar', 'Contoh', ''], [
            ['baris kosong', 'paragraf baru', '(tekan Alt+Enter dua kali di dalam sel Excel)', ''],
            ['## Subjudul', 'subjudul', '## Asal-usul nama', ''],
            ['- teks', 'butir daftar berpoin', '- Tembakau srintil', ''],
            ['**kata**', 'cetak tebal', 'Candi **Borobudur**', ''],
            ['> teks', 'kotak "Tahukah kamu?"', '> Tahukah kamu? Dieng berarti tempat para dewa.', ''],
            ['Sumber: …', 'catatan rujukan kecil di akhir halaman', 'Sumber: UNESCO; Pemkab Magelang', ''],
        ]);

        $r++;
        $r = $this->heading($sheet, $r, 'Hal yang sering keliru');
        foreach ([
            'Menerjemahkan atau mengganti nama header/sheet → kolom dianggap kosong atau sheet dilewati.',
            'Angka 0/1 tertulis "benar"/"salah" → pakai 0 atau 1 untuk kolom is_correct, scorable, decoy, is_active, require_reason, use_word_bank.',
            'Butir pilihan ganda dengan dua opsi benar atau tanpa opsi benar → galat saat pratinjau/verifikasi.',
            'Rumpang tanpa ___ di kalimat soal → siswa tidak melihat tempat kosong.',
            'Kode butir diubah setelah dipakai siswa → dianggap butir baru, data lama tidak tersambung.',
            'Bank soal sama persis dengan items_per_round → tidak ada cadangan; sediakan 1,5–2×.',
            'Profil skoring TIDAK diatur di workbook: setiap tantangan memakai profil skoring aktif studi (bawaan GELITA_V2). Ubah hanya lewat Panel → Studi & rilis bila penelitian memerlukannya.',
        ] as $text) {
            $r = $this->step($sheet, $r, '•', $text);
        }
    }

    private function dictionarySheet(Worksheet $sheet): void
    {
        $sheet->setTitle('KAMUS_KOLOM');
        $headers = ['Sheet', 'Kolom (kode header)', 'Nama Indonesia', 'Status', 'Arti & cara mengisi', 'Isian yang boleh', 'Contoh'];
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
            $comment->getText()->createTextRun($label . ' — ' . strtoupper($status))->getFont()->setBold(true);
            $comment->getText()->createTextRun("\n" . $meaning . "\n\nIsian: " . $allowed . "\nContoh: " . $example);
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
        $rule->setPromptTitle(mb_substr($label, 0, 32));
        $rule->setPrompt(mb_substr($status . '. ' . $meaning, 0, 250));

        if (isset(self::CHOICES[$column])) {
            $rule->setType(DataValidation::TYPE_LIST);
            $rule->setShowDropDown(true);
            $rule->setFormula1('"' . implode(',', self::CHOICES[$column]) . '"');
            $rule->setShowErrorMessage(true);
            $rule->setErrorStyle(DataValidation::STYLE_STOP);
            $rule->setErrorTitle('Isian tidak dikenali');
            $rule->setError(mb_substr('Pilih salah satu: ' . implode(', ', self::CHOICES[$column]), 0, 250));
        } elseif (isset(self::NUMBERS[$column])) {
            [$kind, $min, $max] = self::NUMBERS[$column];
            $rule->setType($kind === 'whole' ? DataValidation::TYPE_WHOLE : DataValidation::TYPE_DECIMAL);
            $rule->setOperator(DataValidation::OPERATOR_BETWEEN);
            $rule->setFormula1((string) $min);
            $rule->setFormula2((string) $max);
            $rule->setShowErrorMessage(true);
            $rule->setErrorStyle(DataValidation::STYLE_STOP);
            $rule->setErrorTitle('Angka di luar batas');
            $rule->setError("Isi angka {$min}–{$max}.");
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

        foreach (['Wajib' => 'WAJIB — harus diisi', 'Bersyarat' => 'BERSYARAT — wajib untuk jenis soal tertentu', 'Opsional' => 'OPSIONAL — boleh kosong'] as $status => $text) {
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
