<?php

namespace App\Libraries;

/**
 * Text of the staff guide (/admin/panduan-staf), the pages that explain the
 * panel to every staff role without the planning documents.
 *
 * Why a data class and not Admin.php keys: the guide is long running text
 * (about 400 lines of steps and notes) that reads best kept together in
 * chapter order, and each step carries the panel page it uses. Admin.php is
 * Indonesian only, like this file, so no English counterpart is needed
 * (decision log, 16 November 2026). Short screen labels (page title,
 * buttons, menu heading) stay in Admin.php.
 *
 * Audience (owner, 16 November 2026): what staff see and do in the panel.
 * No server, deployment or code explanations, and no references to the
 * repository's documents; StaffGuideTest refuses them.
 *
 * Every section is one page, /admin/panduan-staf/{section id}; a chapter is
 * a group in the guide's menu. When a panel page, button label or right
 * changes, change the matching step here (StaffGuideTest checks every link
 * is a real panel route).
 *
 * Markup inside strings (everything else is escaped):
 *   **bold**, `code`, [label](#section-id) (a link to another guide page),
 *   [[term key]] or [[shown text|term key]] (a glossary term, see terms()).
 *
 * Chapter shape:
 *   id, title, roles (who it is written for), lead, sections[]
 * Section shape (every key but id/title optional):
 *   id, title, lead, steps (list of string | [text, link]), list (bullets),
 *   table ([head[], rows[][]]), note (info), warn (warning), glossary (true:
 *   the glossary entries go here)
 * A link is [href, label, capability]; it shows as a link only when the
 * reader holds the capability.
 */
final class StaffGuideContent extends GuideContent
{
    /** URL path of the guide; a section page is BASE/{id}. */
    public const BASE = 'admin/panduan-staf';

    /** Sections offered as cards on the guide's first page. */
    public const POPULAR = ['ringkas', 'token', 'lupa-sandi', 'unggah-lembar', 'unduh', 'larangan-daftar'];

    /**
     * Glossary: key => term, def (one or two sentences), example, more (the
     * guide page that explains it in context; null = the glossary page).
     * Popovers show def, example and a link to `more`; the glossary page
     * lists every entry. Meanings follow the research data dictionary
     * (kamus-1.1) in plain words.
     *
     * @return array<string, array{term: string, def: string, example: string, more: string|null}>
     */
    public static function terms(): array
    {
        return [
            'kode-peserta' => [
                'term'    => 'Kode peserta',
                'def'     => 'Kode siswa dalam penelitian. Dipakai di semua lembar dan halaman panel, menggantikan nama.',
                'example' => '`S03-A07`: sekolah S03, rombel A, siswa nomor 07.',
                'more'    => null,
            ],
            'kode-sekolah' => [
                'term'    => 'Kode sekolah penelitian',
                'def'     => 'Kode sekolah: S untuk sampel utama, U untuk uji coba, V untuk instalasi uji validator, diikuti dua angka.',
                'example' => '`S03` adalah sekolah sampel utama nomor 3.',
                'more'    => 'sekolah',
            ],
            'rombel' => [
                'term'    => 'Rombel',
                'def'     => 'Kelas di sekolah itu, ditulis satu huruf kapital.',
                'example' => 'Huruf `A` pada kode peserta `S03-A07`.',
                'more'    => null,
            ],
            'pertemuan' => [
                'term'    => 'Pertemuan P1–P5',
                'def'     => 'Lima pertemuan dengan satu rombel. Setiap pertemuan membuka wilayahnya sendiri. Jangan tertukar dengan peran peneliti.',
                'example' => 'P1 daftar dan tes awal; P2 Temanggung; P3 Magelang; P4 Wonosobo; P5 semua wilayah, tes akhir, dan penutup.',
                'more'    => 'mode-penelitian',
            ],
            'sesi-lapangan' => [
                'term'    => 'Sesi lapangan',
                'def'     => 'Satu kunjungan ke satu rombel untuk satu pertemuan. Membawa token, wilayah yang dibuka, serta jam buka dan jam tutup.',
                'example' => '`S03-A-P2` adalah pertemuan ke-2 rombel A di S03. Sesi susulannya `S03-A-P2-S`.',
                'more'    => 'sebelum',
            ],
            'token' => [
                'term'    => 'Token',
                'def'     => 'Enam huruf atau angka yang diketik siswa untuk masuk ke sesi lapangan. Hanya token terbaru yang berlaku, dan hanya selama sesi buka.',
                'example' => 'Token ditulis di papan tulis atau ditampilkan besar di proyektor.',
                'more'    => 'token',
            ],
            'buklet' => [
                'term'    => 'Buklet B1–B4',
                'def'     => 'Variasi soal yang didapat siswa pada pengerjaan pertamanya. Dihitung otomatis dari kursi siswa (L2), bersama bentuk tes A atau B.',
                'example' => 'Setelah baris dan kursi disimpan, kolom Buklet, Tes awal, dan Tes akhir terisi sendiri.',
                'more'    => 'kursi',
            ],
            'mode-penelitian' => [
                'term'    => 'Mode penelitian',
                'def'     => 'Sakelar di halaman Penelitian. Selama menyala, permainan hanya terbuka di sesi lapangan, jam disembunyikan, dan aturan penelitian berlaku.',
                'example' => 'Di luar sesi lapangan yang buka, siswa melihat layar "Sampai jumpa".',
                'more'    => 'mode-penelitian',
            ],
            'lembar' => [
                'term'    => 'Lembar L0–L8',
                'def'     => 'Lembar lapangan kertas yang diisi tim selama kunjungan. L7 satu-satunya lembar yang memuat nama.',
                'example' => 'L2 berisi peta kursi, perangkat, dan kode peserta.',
                'more'    => 'lembar',
            ],
            'pengerjaan-pertama' => [
                'term'    => 'Pengerjaan pertama',
                'def'     => 'Pengerjaan sebuah tantangan dengan variasi buklet siswa, sampai tantangan itu tertutup untuk pertama kali. Satu-satunya pengerjaan yang dinilai.',
                'example' => 'Siswa yang mengulang tantangan tetap dinilai dari pengerjaan pertamanya; ulangannya disimpan tanpa dinilai.',
                'more'    => null,
            ],
            'kesempatan' => [
                'term'    => 'Kesempatan',
                'def'     => 'Batas tiga percobaan. Dihitung per tantangan (Isi Rumpang, Benar atau Salah), per petunjuk (Cari Objek), atau per skenario (Skenario Digital).',
                'example' => 'Di Isi Rumpang, siswa boleh menekan Cek tiga kali.',
                'more'    => null,
            ],
            'jeda' => [
                'term'    => 'Batas jeda tanpa sentuhan',
                'def'     => 'Jeda tanpa sentuhan yang lebih lama dari batas ini tidak dihitung sebagai waktu aktif; hanya bagian yang melebihi batas yang dibuang.',
                'example' => 'Dengan batas 3 menit, jeda 5 menit menyumbang 3 menit waktu aktif.',
                'more'    => 'pengaturan-data',
            ],
            'tebakan-cepat' => [
                'term'    => 'Tebakan cepat',
                'def'     => 'Jawaban yang diberikan lebih cepat dari batas detik di pengaturan, sehingga ditandai sebagai tebakan. Jawabannya tetap disimpan.',
                'example' => 'Dengan batas 3 detik, jawaban 1 detik setelah soal tampil ditandai tebakan cepat.',
                'more'    => 'pengaturan-data',
            ],
            'data-mentah' => [
                'term'    => 'Data mentah',
                'def'     => 'Data apa adanya dari permainan dan modul tes: jawaban, percobaan, pengerjaan, progres, navigasi, bantuan, dan event.',
                'example' => 'Setiap ketukan Cek tercatat sebagai satu baris di riwayat percobaan.',
                'more'    => 'data-mentah',
            ],
            'data-turunan' => [
                'term'    => 'Data turunan',
                'def'     => 'Nilai untuk analisis yang dihitung panel dari data mentah, misalnya waktu aktif, tebakan cepat, dan skor. Dihitung ulang bila data mentah berubah.',
                'example' => 'Lembar berawalan `turunan_` di berkas unduhan.',
                'more'    => 'turunan',
            ],
            'kunci-ambang' => [
                'term'    => 'Kunci ambang',
                'def'     => 'Mengunci batas jeda dan batas tebakan cepat sebuah penelitian supaya tidak berubah lagi selama analisis.',
                'example' => 'Ditekan ketua saat versi kamus data dibekukan (gerbang G4).',
                'more'    => 'turunan',
            ],
            'kamus-data' => [
                'term'    => 'Kamus Data',
                'def'     => 'Lembar yang menjelaskan setiap kolom unduhan: arti, satuan, dan versinya. Selalu ikut di setiap unduhan.',
                'example' => 'Versi yang berlaku sekarang `kamus-1.1`.',
                'more'    => 'unduh',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function chapters(): array
    {
        return [
            self::start(),
            self::fieldWork(),
            self::headResearcher(),
            self::admin(),
            self::never(),
        ];
    }

    /** @return array<string, mixed> */
    private static function start(): array
    {
        return [
            'id'    => 'mulai',
            'title' => 'Mulai di sini',
            'roles' => ['admin', 'peneliti', 'ketua'],
            'lead'  => 'Panel GELITA dipakai tiga peran. Setiap peran hanya melihat menu dan halaman yang menjadi tugasnya. Bab untuk peran Anda tampil terbuka; bab peran lain bisa dibuka bila perlu.',
            'sections' => [
                [
                    'id'    => 'cara-pakai',
                    'title' => 'Cara memakai panduan ini',
                    'list'  => [
                        'Menu di kiri memuat semua topik, dikelompokkan per bab. Bab untuk peran Anda ada paling atas.',
                        'Kotak **Cari** di atas menu menyaring topik menurut kata yang diketik.',
                        'Setiap langkah menyebut halaman panel yang dipakai. Bila Anda boleh membukanya, namanya berupa tautan.',
                        'Kotak kuning berisi hal yang tidak boleh terlewat. Baca sebelum menekan tombol.',
                        'Nama tombol ditulis **tebal**, persis seperti di layar.',
                        'Kata bergaris titik, misalnya [[token|token]], bisa diklik untuk melihat arti singkat dan contohnya.',
                    ],
                ],
                [
                    'id'    => 'peran',
                    'title' => 'Tiga peran dan haknya',
                    'lead'  => 'Hak setiap peran sengaja dipisah. Nama anak hanya ada di sisi akun (admin); data penelitian hanya dibaca lewat kode (peneliti dan ketua). Dengan begitu tidak ada satu orang pun yang memegang nama sekaligus jawaban anak.',
                    'table' => [
                        ['Peran', 'Boleh', 'Tidak boleh'],
                        [
                            [
                                '**Peneliti**',
                                'Sesi lapangan, token, izin per siswa (L7), kursi dan buklet (L2), modul tes, tambah waktu, pemantauan, unggah lembar lapangan, atur ulang kata sandi siswa lewat kode, mengajukan permintaan data orang tua, melihat siswa lewat kode, melihat konten.',
                                'Melihat nama atau nama pengguna siswa, membuka Data mentah, Sesi bermain, Kritik & saran, Unduh data, Penelitian, atau Tindak lanjut, mengonfirmasi permintaan hapus.',
                            ],
                            [
                                '**Ketua peneliti**',
                                'Semua hak peneliti, ditambah: data semua siswa (Data mentah, Sesi bermain, Kritik & saran), Unduh data, membuat dan mengubah Penelitian, rilis dan paket aset, mengunci ambang, mengonfirmasi atau menolak permintaan data, Tindak lanjut, salinan data satu anak, daftar izin berkode.',
                                'Melihat nama siswa, menjalankan penghapusan, mengurus sekolah atau akun staf.',
                            ],
                            [
                                '**Admin**',
                                'Akun staf, sekolah dan kode penelitian, kartu akun siswa (dengan nama), atur ulang kata sandi lewat akun, membetulkan data pendaftaran, konten, narasi, gambar, rilis dan paket aset, tata kelola (menjalankan penghapusan, log audit, perawatan data), log teknis, pemantauan (angka saja), mengganti nama dengan kode saat penutupan studi.',
                                'Melihat jawaban, sesi, data mentah, atau kritik & saran, mengunduh data penelitian, mengisi izin, mengajukan atau mengonfirmasi permintaan hapus, mengubah penelitian.',
                            ],
                        ],
                    ],
                    'note' => 'Halaman yang tidak menjadi hak Anda tidak muncul di menu. Bila dibuka lewat alamat, panel menjawab "halaman tidak ditemukan". Itu bukan kerusakan.',
                ],
                [
                    'id'       => 'istilah',
                    'title'    => 'Kata yang sering dipakai',
                    'lead'     => 'Arti kata yang sering muncul di panel, lembar lapangan, dan unduhan data. Di halaman lain panduan ini, kata bergaris titik bisa diklik untuk melihat artinya.',
                    'glossary' => true,
                ],
                [
                    'id'    => 'akun',
                    'title' => 'Akun staf Anda',
                    'steps' => [
                        ['Ganti kata sandi awal saat pertama masuk. Kata sandi minimal 12 karakter dan tidak memuat nama pengguna.', ['admin/akun/sandi', 'Ganti kata sandi', 'dashboard.view']],
                        'Panel keluar sendiri setelah 2 jam tidak dipakai. Masuk lagi seperti biasa.',
                        'Lima kali salah kata sandi mengunci akun 15 menit. Tunggu, lalu coba lagi, atau minta admin membuat kata sandi sementara.',
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function fieldWork(): array
    {
        return [
            'id'    => 'lapangan',
            'title' => 'Kerja lapangan (peneliti dan ketua)',
            'roles' => ['peneliti', 'ketua'],
            'lead'  => 'Urutan kerja satu kunjungan sekolah, dari sehari sebelumnya sampai lembar diunggah.',
            'sections' => [
                [
                    'id'    => 'ringkas',
                    'title' => 'Hari kunjungan dalam 10 langkah',
                    'lead'  => 'Ringkasan untuk dibaca cepat di sekolah. Rincian tiap langkah ada di bawah.',
                    'steps' => [
                        ['Sehari sebelumnya: buat [[sesi lapangan|sesi-lapangan]], unduh templat lembar, cetak lembar, isi L0.', ['admin/sesi-lapangan', 'Sesi lapangan', 'field_sessions.manage']],
                        'Sebelum siswa masuk: cocokkan izin dengan L7 dan isi **Izin per siswa**. Siswa tanpa izin tertulis tidak mendapat akun.',
                        'Buka sesi, lalu tulis [[token|token]] di papan atau tekan **Tampilkan token besar**.',
                        'Siswa masuk: P1 lewat **Daftar**, P2–P5 lewat **Masuk**, lalu mengetik token.',
                        'Isi **Kursi dan buklet (L2)**: baris dan kursi tiap [[kode peserta|kode-peserta]].',
                        'P1 dan P5: tekan **Mulai tes awal** atau **Mulai tes akhir** pada waktunya.',
                        ['Selama sesi: buka Pemantauan. Tangani setiap angka merah.', ['admin/pemantauan', 'Pemantauan', 'monitoring.view']],
                        ['Siswa lupa kata sandi: atur ulang lewat kode peserta.', ['admin/peserta/reset-sandi', 'Atur ulang kata sandi siswa', 'participants.reset_by_code']],
                        'Selesai: tekan **Tutup sesi sekarang** dan tulis jam tutup di L1.',
                        'Dalam 24 jam: ketik lembar ke templat dan unggah. Buat sesi susulan bila ada siswa yang tertinggal.',
                    ],
                ],
                [
                    'id'    => 'sebelum',
                    'title' => 'Sebelum kunjungan',
                    'lead'  => 'Syarat: ketua sudah menyalakan [[Mode penelitian|mode-penelitian]] di halaman Penelitian, dan admin sudah memberi sekolah [[kode penelitian|kode-sekolah]]. Tanpa itu, halaman Sesi lapangan memberi tahu apa yang kurang.',
                    'steps' => [
                        ['Buka Sesi lapangan dan tekan **Buat sesi lapangan**.', ['admin/sesi-lapangan', 'Sesi lapangan', 'field_sessions.manage']],
                        'Isi **Kode sekolah**, **Rombel** (satu huruf), **Pertemuan** ([[P1–P5|pertemuan]]), **Tanggal**, **Jam buka**, dan **Jam tutup**. Satu sesi paling lama 4 jam. Pada rencana satu sesi, pilih juga **Wilayah**.',
                        'Simpan. Panel menampilkan kode sesi dan tokennya. Tulis keduanya di L1.',
                        'Di halaman sesi, tekan **Unduh templat lembar**. Berkas Excel itu sudah berisi kode sesi, kode peserta rombel, dan bukletnya, tanpa nama.',
                        'Cetak [[lembar|lembar]] yang dibutuhkan pertemuan itu (lihat [Lembar L0–L8](#lembar)) di kertas A4, tulis dengan tinta hitam.',
                        'Isi L0 (daftar periksa persiapan) sehari sebelumnya dan sekali lagi sebelum siswa masuk.',
                    ],
                    'list' => [
                        'Perangkat siswa: laptop, Chromebook, atau tablet minimal 10 inci. Layar permainan harus selebar 960 piksel atau lebih; **ponsel ditolak**.',
                        'P1: siswa belum punya akun dan mendaftar di sesi. P2–P5: siswa membawa nama pengguna dan kata sandinya.',
                        'Satu rombel hanya punya satu sesi per pertemuan. Bila sudah ada, buka sesi itu atau buat sesi susulan.',
                    ],
                ],
                [
                    'id'    => 'token',
                    'title' => 'Membuka sesi dan memberi token',
                    'steps' => [
                        'Sesi buka sendiri pada **Jam buka**. Untuk membuka lebih awal, centang **Buka sekarang** di bagian **Selama sesi** lalu tekan **Simpan jam**.',
                        'Tulis token di papan, atau tekan **Tampilkan token besar** untuk proyektor. Tekan **Kembali ke sesi** untuk kembali.',
                        'P1: siswa menekan **Daftar**, mengetik token, lalu mendaftar. P2–P5: siswa **Masuk** dengan akunnya, lalu mengetik token. Huruf kecil dan spasi tidak masalah.',
                        'Bila kelas berlangsung lebih lama, ubah **Jam tutup**, lalu **Simpan jam** (batas 4 jam tetap berlaku).',
                        'Bila token tersebar ke luar kelas, tekan **Buat token baru**. Token lama langsung tidak berlaku; siswa yang sudah di dalam tidak terganggu.',
                    ],
                    'note' => 'Sepuluh token salah dalam 10 menit membuat perangkat itu menunggu 5 menit. Token juga ditolak bila siswa salah kelas, bukan anggota sesi susulan, sesi belum buka atau sudah tutup, atau siswa sudah mengundurkan diri.',
                ],
                [
                    'id'    => 'izin',
                    'title' => 'Izin per siswa (dari L7)',
                    'lead'  => 'Isi sebelum siswa masuk, dari lembar L7. Halaman ini hanya berisi kode; jangan menulis nama.',
                    'steps' => [
                        ['Di halaman sesi, tekan **Izin per siswa**.', ['admin/sesi-lapangan', 'Sesi lapangan', 'field_sessions.manage']],
                        'Per kode peserta, isi **Tanggal izin orang tua** (wajib).',
                        'Isi **Izin rekaman suara** dan **Izin pertanyaan internet**: Ya atau Tidak. Bila berubah dari Ya ke Tidak, isi **Tanggal ditarik**.',
                        'Pilih **Status**: Aktif, Keluar, Mengundurkan diri (data tetap), atau Mengundurkan diri (hapus data). Selain Aktif, isi **Tanggal status**.',
                        'Tekan **Simpan**.',
                    ],
                    'list' => [
                        '**Izin rekaman suara Tidak**: siswa tidak direkam saat wawancara.',
                        '**Izin pertanyaan internet Tidak**: siswa tidak pernah ditawari pertanyaan tambahan tentang internet di P5.',
                        'Keduanya Tidak sampai diisi Ya dari L7.',
                        'Status Mengundurkan diri menonaktifkan akun. Data sampai tanggal itu tetap tersimpan (atau dihapus lewat permintaan data).',
                    ],
                ],
                [
                    'id'    => 'kursi',
                    'title' => 'Kursi dan buklet (L2)',
                    'steps' => [
                        'Di halaman sesi, tekan **Kursi dan buklet (L2)**.',
                        'Per kode peserta, isi **Baris** dan **Kursi**: baris dihitung dari depan, kursi dari kiri, dilihat dari papan tulis. Mulai dari 1.',
                        'Simpan. **Buklet**, **Tes awal**, dan **Tes akhir** terisi otomatis (lihat [[buklet|buklet]]).',
                    ],
                    'list' => [
                        'Di P1, isi segera setelah semua siswa mendaftar (sekitar 5 menit). Salin kode dari layar siswa: di P1 dari bagian atas modul tes, di pertemuan lain dari Profil.',
                        'Jangan menyalin nama atau nama pengguna dari layar siswa.',
                        'Siswa tanpa kursi tidak bisa membuka tantangan 2–5. Tes awal juga belum bisa dimulai sampai semua siswa punya kursi.',
                        'Kursi terkunci setelah tes awal dimulai.',
                    ],
                ],
                [
                    'id'    => 'tes',
                    'title' => 'Modul tes (P1 dan P5)',
                    'steps' => [
                        'Pastikan semua siswa sudah punya kursi dan sesi sedang buka.',
                        'Di panel **Modul tes** pada halaman sesi, tekan **Mulai tes awal** (P1) atau **Mulai tes akhir** (P5).',
                        'Siswa yang menunggu di halaman tes kini bisa menekan **Mulai**.',
                        'Pantau kolom **Status tes**: belum, sedang, selesai, susulan, atau hilang.',
                    ],
                    'warn' => '**Mulai tes akhir** menutup tantangan untuk semua siswa sesi itu. Tekan hanya setelah waktu menyelesaikan tantangan (20 menit, dihitung dengan jam tangan) habis.',
                    'list' => [
                        'Batas waktu tes tersembunyi dari siswa: 40 menit untuk Bagian 1, 35 menit untuk Bagian 2 dan 3. Siswa hanya mendapat pengingat 5 menit sebelum habis.',
                        '**Tambah waktu** (di halaman sesi) untuk perangkat mati atau gangguan: isi kode peserta, blok, menit tambahan, dan alasan. Paling banyak 30 menit per blok, hanya untuk blok yang masih berjalan. Blok yang sudah habis diselesaikan di sesi susulan.',
                        'Tes susulan hanya dibuka sampai 3 hari setelah sesi utama.',
                        'Skala sikap terbuka di P5 setelah semua tantangan, modul tes, dan cerita penutup selesai. Susulannya boleh sampai 7 hari setelah P5.',
                    ],
                ],
                [
                    'id'    => 'pantau',
                    'title' => 'Pemantauan selama sesi',
                    'steps' => [
                        ['Buka Pemantauan (juga dari tombol di halaman sesi). Angkanya diperbarui tiap 30 detik.', ['admin/pemantauan', 'Pemantauan', 'monitoring.view']],
                        'Lihat ubin yang berwarna merah dan tangani satu per satu.',
                    ],
                    'table' => [
                        ['Angka', 'Yang dilakukan'],
                        [
                            ['**Belum masuk**', 'Datangi siswa itu: token, akun, atau perangkat bermasalah.'],
                            ['**Belum punya kursi**', 'Lengkapi Kursi dan buklet (L2).'],
                            ['**Catatan hilang**', 'Periksa internet perangkat. Catat di L1, beri tahu admin bila terus naik.'],
                            ['**Perangkat layar kecil ditolak**', 'Ganti perangkat atau perbesar jendela peramban.'],
                            ['**Izin belum diisi**', 'Lengkapi Izin per siswa dari L7.'],
                        ],
                    ],
                    'note' => 'Halaman ini tidak menampilkan jawaban, skor, atau bintang. Admin hanya melihat angka, tanpa kode peserta.',
                ],
                [
                    'id'    => 'lupa-sandi',
                    'title' => 'Siswa lupa kata sandi',
                    'steps' => [
                        ['Buka Atur ulang kata sandi siswa.', ['admin/peserta/reset-sandi', 'Atur ulang kata sandi siswa', 'participants.reset_by_code']],
                        'Ketik **Kode peserta** dari L2, lalu ketik `RESET`.',
                        'Tekan **Buat kata sandi sementara**. Kata sandi lama langsung tidak berlaku.',
                        'Bacakan atau tuliskan kata sandi sementara untuk siswa itu saja. Kata sandi ini hanya tampil sekali dan tidak disimpan.',
                    ],
                ],
                [
                    'id'    => 'tutup',
                    'title' => 'Menutup sesi dan sesi susulan',
                    'steps' => [
                        'Di bagian **Sesudah sesi**, tekan **Tutup sesi sekarang** dan setujui. Sesi juga tertutup sendiri pada Jam tutup.',
                        'Tulis jam tutup di L1. Token berhenti berlaku, permainan siswa dijeda, dan pengerjaan yang belum selesai dibiarkan apa adanya.',
                        'Ada siswa yang tertinggal? Pada sesi utama yang sudah tutup, tekan **Buat sesi susulan**.',
                        'Isi tanggal dan jam. Siswa yang belum masuk sudah tercentang di **Anggota susulan**; hanya mereka yang bisa memakai token susulan.',
                        'Sesi susulan mendapat kode dan token baru. Jalankan dalam 3 hari bila ada tes yang perlu disusul.',
                    ],
                    'note' => 'Rencana satu sesi tidak memiliki sesi susulan.',
                ],
                [
                    'id'    => 'unggah-lembar',
                    'title' => 'Mengetik dan mengunggah lembar',
                    'lead'  => 'Dalam 24 jam setelah sesi, lembar kertas diketik ke templat Excel dan diunggah.',
                    'steps' => [
                        'Ketik lembar ke templat yang diunduh dari halaman sesi.',
                        'Di halaman sesi, tekan **Unggah lembar yang sudah diketik**. Pilih berkas, lalu tekan **Periksa sebelum disimpan**.',
                        'Baca **Kesalahan** dan **Peringatan**. Bila panel menduga ada nama siswa, periksa lagi lalu centang **Sudah saya periksa, tidak ada nama siswa.**',
                        'Tekan **Simpan**. Pratinjau berlaku 30 menit. Unggahan ulang menggantikan unggahan lembar yang sama.',
                        '**Ketikan kedua**: sekitar 10% baris diketik ulang oleh **orang lain**. Unggah dengan kotak **Ketikan kedua (cek)** tercentang, lalu tekan **Simpan ketikan kedua**.',
                        'Panel menunjukkan sel yang berbeda. Betulkan di ketikan pertama, lalu unggah ulang ketikan pertama.',
                    ],
                    'warn' => 'Jangan pernah mengunggah L7, kolom **Token** di L1, atau kolom **Kutipan ucapan siswa** di L5.',
                ],
                [
                    'id'    => 'permintaan-orang-tua',
                    'title' => 'Orang tua meminta datanya dihapus',
                    'lead'  => 'Peneliti atau ketua yang menerima permintaan mencatatnya di panel. Menu **Permintaan data** muncul selama ada penelitian dengan mode penelitian menyala.',
                    'steps' => [
                        ['Buka Permintaan data dan pilih **Ajukan permintaan hapus**.', ['admin/permintaan-data', 'Permintaan data', 'deletion.request']],
                        'Ketik kode peserta dan tekan **Periksa kode**. Cocokkan sekolah, rombel, dan status izin dengan L7. Panel tidak menampilkan nama.',
                        'Pilih **Jenis permintaan**: hapus semua data, hapus jawaban pertanyaan internet, atau hapus rekaman.',
                        'Isi kapan dan lewat apa permintaan diterima. Centang bahwa identitas pemohon cocok dengan formulir izin, dan isi waktunya.',
                        'Kirim. Status izin ikut berubah saat itu juga. Ketua mengonfirmasi, admin menjalankan penghapusan (lihat [Permintaan data: konfirmasi dan centang](#ketua-permintaan)).',
                        'Setelah dijalankan, centang bagian Anda: **Rekaman dan transkripnya** dan **Baris berkode di lembar kertas L2–L6 dihitamkan**.',
                    ],
                    'warn' => 'Jangan menulis nama anak atau orang tua di permintaan, alasan, atau catatan mana pun.',
                ],
                [
                    'id'    => 'mode-penelitian',
                    'title' => 'Yang dialami siswa dalam mode penelitian',
                    'list'  => [
                        '**Permainan hanya terbuka di sesi lapangan yang buka.** Di luar itu siswa melihat layar "Sampai jumpa".',
                        '**Tiap pertemuan membuka wilayahnya sendiri.** P2 Temanggung, P3 Magelang, P4 Wonosobo, P5 semua wilayah.',
                        '**Jam disembunyikan.** Tidak ada penghitung waktu di tantangan, hasil, profil, maupun tes.',
                        '**Layar terlalu kecil ditolak** sebelum sesi atau akun dibuat. Memperbesar jendela membuka kembali.',
                        '**Tantangan yang terputus dilanjutkan**, bukan diulang, di pertemuan berikutnya.',
                        '**Mengulang tantangan** baru boleh setelah kelima tantangan wilayah itu selesai; yang dinilai tetap [[pengerjaan pertama|pengerjaan-pertama]]. Layar Kenali wilayah juga terbuka setelah wilayahnya selesai.',
                    ],
                ],
                [
                    'id'    => 'lembar',
                    'title' => 'Lembar L0–L8',
                    'table' => [
                        ['Lembar', 'Isi', 'Kapan dan oleh siapa'],
                        [
                            ['**L0**', 'Daftar periksa persiapan', 'Sehari sebelumnya dan sebelum siswa masuk. Yang tidak bisa dicentang ditulis di L1.'],
                            ['**L1**', 'Kondisi sesi dan penyimpangan (jam, internet, headset, gangguan, kode sesi, token)', 'Sepanjang sesi.'],
                            ['**L2**', 'Peta kursi, perangkat, kode peserta', 'Saat siswa masuk. Kunci untuk semua lembar lain.'],
                            ['**L3**', 'Log bantuan dan gangguan', 'Sepanjang sesi, termasuk saat tes.'],
                            ['**L4**', 'Observasi keterlibatan dan emosi', 'Selama bermain, per 20 detik.'],
                            ['**L5**', 'Wawancara retrospektif', 'P2–P4, tiga siswa per pertemuan, setelah bermain.'],
                            ['**L6**', 'Log tes awal–akhir dan angket', 'P1, P5, dan sesi susulan tes.'],
                            ['**L7**', 'Izin orang tua dan status', 'Sebelum P1, diperbarui sampai studi selesai. **Satu-satunya lembar bernama.**'],
                            ['**L8**', 'Profil sekolah', 'Sekali per sekolah, sebelum P1.'],
                        ],
                    ],
                    'list' => [
                        'Cetak per pertemuan: P1 = L0, L1, L2, L3, L6, L7; P2–P4 = L0 sampai L5; P5 = L0, L1, L2, L3, L4, L6, L7; susulan = L0, L1, L2, L3 (dan L6 bila menyusul tes).',
                        'Templat dari halaman sesi memuat lembar yang diketik. Lembar L0, L7, dan L8 kosong disiapkan tim peneliti.',
                        'Salah tulis dicoret satu garis dan diparaf; jangan memakai cairan koreksi. Isian kosong ditulis "—".',
                        'L7 disimpan di lemari terkunci (kertas) atau terenkripsi (digital), hanya untuk peneliti utama dan ketua.',
                    ],
                ],
                [
                    'id'    => 'masalah-teknis',
                    'title' => 'Kalau ada masalah teknis',
                    'list'  => [
                        'Catat waktunya dan perangkatnya di L1 atau L3, dengan kode peserta bila menyangkut satu siswa.',
                        'Beri tahu admin. Admin dapat membuka Log teknis (perangkat, event teknis, sesi) untuk mencari penyebabnya.',
                        'Jangan memperbaiki data siswa sendiri. Pembetulan data pendaftaran (umur, jenis kelamin, kelas) diminta ke admin lewat ketua.',
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function headResearcher(): array
    {
        return [
            'id'    => 'ketua',
            'title' => 'Tugas ketua peneliti',
            'roles' => ['ketua'],
            'lead'  => 'Ketua memegang pengaturan penelitian, data semua siswa (berkode), unduhan, dan keputusan atas permintaan data.',
            'sections' => [
                [
                    'id'    => 'siapkan-penelitian',
                    'title' => 'Menyiapkan penelitian dan mode penelitian',
                    'steps' => [
                        ['Buka Penelitian dan tekan **Buat penelitian baru**.', ['admin/studi', 'Penelitian', 'studies.view']],
                        'Isi kode singkat (tidak bisa diubah), nama, tahun ajaran, status, bahasa awal, cara wilayah terbuka, rencana pertemuan, lama penyimpanan data, dan keterangan.',
                        'Centang **Mode penelitian** hanya setelah persetujuan etik terbit. Mode ini butuh lama penyimpanan data minimal 7.305 hari. Bila panel menolak karena pengaturan privasi belum siap, hubungi admin.',
                        ['Siapkan paket aset: di Rilis → Paket aset tekan **Nyalakan** atau **Bangun ulang paket**, biarkan halaman terbuka sampai selesai, lalu **Terbitkan** dan **Periksa sekarang**.', ['admin/studi/rilis/paket', 'Paket aset', 'releases.manage']],
                        'Periksa kotak **Daftar siap penelitian** di halaman yang sama. Semua butir sebaiknya hijau sebelum status diubah ke **Berjalan**. Bila belum, panel meminta centang **Tetap jalankan**.',
                        'Status Berjalan mengunci paket aset yang aktif untuk penelitian itu.',
                    ],
                    'warn' => 'Mode penelitian dan rencana pertemuan tidak bisa diubah selama masih ada sesi lapangan yang buka. Jangan menyalakan mode penelitian bersama anak sebelum persetujuan etik terbit.',
                ],
                [
                    'id'    => 'pengaturan-data',
                    'title' => 'Pengaturan pengambilan data',
                    'lead'  => 'Di kartu setiap penelitian pada halaman Penelitian.',
                    'table' => [
                        ['Pengaturan', 'Bawaan', 'Rentang'],
                        [
                            ['[[Batas jeda tanpa sentuhan|jeda]]', '180 detik', '30–1.800 detik'],
                            ['Batas [[tebakan cepat|tebakan-cepat]] (tantangan)', '3 detik', '0,5–10 detik'],
                            ['Batas tebakan cepat (soal tes)', '3 detik', '0,5–10 detik'],
                            ['Lebar layar permainan terkecil', '960 piksel', '640–1.280 piksel'],
                            ['Batas waktu tes (tidak tampil ke siswa)', '40 dan 35 menit', '1–240 menit'],
                        ],
                    ],
                    'note' => 'Kolom yang dikosongkan tetap memakai nilai lama. Setiap perubahan tercatat (nilai lama dan baru).',
                ],
                [
                    'id'    => 'data-mentah',
                    'title' => 'Data mentah, sesi bermain, kritik & saran',
                    'steps' => [
                        ['[[Data mentah|data-mentah]]: pilih tab (jawaban, riwayat percobaan, pengerjaan, progres, navigasi, bantuan, event, turunan), saring, lalu unduh CSV per tabel bila perlu.', ['admin/data', 'Data mentah', 'research.browse']],
                        ['Sesi bermain: riwayat tiap sesi dan linimasa event-nya.', ['admin/sesi', 'Sesi bermain', 'research.browse']],
                        ['Kritik & saran dari siswa.', ['admin/masukan', 'Kritik & saran', 'research.browse']],
                    ],
                    'note' => 'Semua data tampil dengan kode peserta, tidak pernah dengan nama.',
                ],
                [
                    'id'    => 'unduh',
                    'title' => 'Mengunduh data',
                    'steps' => [
                        ['Buka Unduh data dan pilih satu penelitian.', ['admin/ekspor', 'Unduh data', 'research.export']],
                        'Pilih **Isi nilai** (bawaan, berlabel, atau berkode) dan lembar yang dibutuhkan. [[Kamus Data|kamus-data]] selalu ikut.',
                        'Tekan **Buat XLSX** atau **Buat CSV-ZIP**, tunggu, lalu unduh. [[Data turunan|data-turunan]] dihitung ulang otomatis bila data mentah sudah berubah.',
                    ],
                    'list' => [
                        'Identitas siswa selalu berkode. Jawaban pertanyaan internet hanya tampil sebagai jumlah gabungan.',
                        'Berkas tersimpan 7 hari dan hanya bisa diunduh oleh pembuatnya. Setiap unduhan tercatat.',
                        'Simpan salinan di perangkat tim seperlunya saja; salinan harus dihapus saat ada permintaan hapus.',
                    ],
                ],
                [
                    'id'    => 'turunan',
                    'title' => 'Data turunan dan kunci ambang',
                    'steps' => [
                        ['Di kartu penelitian, bagian **Data turunan**, tekan **Hitung ulang** setelah data berubah (lembar diunggah, ambang diubah, data dihapus).', ['admin/studi', 'Penelitian', 'studies.view']],
                        'Saat versi kamus data dibekukan (gerbang G4), tekan [[Kunci ambang|kunci-ambang]] di bagian **Ambang data turunan**.',
                    ],
                    'warn' => 'Setelah dikunci, batas jeda dan tebakan cepat tidak bisa diubah dari panel. Membuka kunci hanya bisa dilakukan pengembang, dengan alasan yang tercatat sebagai penyimpangan.',
                ],
                [
                    'id'    => 'ketua-permintaan',
                    'title' => 'Permintaan data: konfirmasi dan centang',
                    'steps' => [
                        ['Buka Permintaan data. Periksa permintaan berstatus menunggu konfirmasi.', ['admin/permintaan-data', 'Permintaan data', 'deletion.confirm']],
                        'Tekan **Konfirmasi**, atau **Tolak** dengan alasan. Permintaan yang Anda ajukan sendiri tidak perlu dikonfirmasi lagi.',
                        'Admin menjalankan penghapusan di Tata kelola. Setelah itu, centang **Salinan di perangkat tim** (panel menunjukkan unduhan yang perlu dibersihkan).',
                        'Tunggu centang dari peneliti (rekaman, lembar kertas) dan admin (**Cadangan diganti**).',
                        'Terakhir, beri tahu orang tua lalu centang **Orang tua diberi tahu**. Batasnya 72 jam sejak penghapusan dijalankan; panel menunjukkan sisa waktunya.',
                        'Tekan **Unduh berita acara** dan simpan bersama L7.',
                    ],
                ],
                [
                    'id'    => 'tindak-lanjut',
                    'title' => 'Tindak lanjut',
                    'steps' => [
                        ['Buka Tindak lanjut. Panel menunjukkan kode siswa yang jawaban pertanyaan internetnya perlu didahulukan.', ['admin/tindak-lanjut', 'Tindak lanjut', 'risk_flags.view']],
                        'Tindak lanjuti sesuai protokol penelitian. Tulis catatan di berkas terpisah, bukan di sistem.',
                    ],
                    'warn' => 'Penanda tidak disimpan dan tidak bisa diunduh. Jangan memotret atau mencetak halaman ini.',
                ],
                [
                    'id'    => 'salinan-anak',
                    'title' => 'Salinan data satu anak dan daftar izin berkode',
                    'steps' => [
                        'Cocokkan identitas pemohon dengan L7 lebih dulu.',
                        ['Buka Salinan data satu anak. Isi kode peserta dan keperluannya (melihat atau salinan).', ['admin/peserta/salinan', 'Salinan data satu anak', 'research.export']],
                        'Jawaban pertanyaan internet tidak disertakan kecuali anak setuju, atau orang tua tetap meminta setelah dijelaskan. Jangan sertakan bila bisa merugikan anak.',
                        'Tekan **Buat dan unduh salinan**. Berkas ZIP langsung terunduh dan tidak disimpan di panel.',
                        'Daftar izin berkode diunduh dari halaman penutupan studi (**Unduh daftar izin berkode**). Unduh lagi setiap kali izin berubah.',
                    ],
                ],
                [
                    'id'    => 'penutupan',
                    'title' => 'Penutupan studi',
                    'steps' => [
                        'Ubah status penelitian menjadi **Selesai**.',
                        'Selesaikan semua permintaan hapus dan unduh daftar izin berkode terbaru.',
                        'Buka **Periksa nama di kritik dan saran**, ganti nama yang tersisa, lalu tekan **Simpan pemeriksaan**.',
                        'Minta admin menjalankan **Ganti nama dengan kode** dan mengganti cadangan.',
                        'Musnahkan L7, formulir izin, dan berita acara di luar sistem sesuai protokol.',
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function admin(): array
    {
        return [
            'id'    => 'admin',
            'title' => 'Tugas admin',
            'roles' => ['admin'],
            'lead'  => 'Admin mengurus sistem: akun, sekolah, konten, rilis, dan tata kelola. Admin memegang nama siswa, tetapi tidak membuka data penelitian.',
            'sections' => [
                [
                    'id'    => 'akun-staf',
                    'title' => 'Akun staf',
                    'steps' => [
                        ['Buka Staf. Isi nama pengguna, nama tampilan, surel, peran, dan kata sandi awal (minimal 12 karakter), lalu tekan **Buat akun**.', ['admin/staf', 'Staf', 'staff.manage']],
                        'Serahkan nama pengguna dan kata sandi awal langsung kepada pemilik akun. Pemiliknya wajib menggantinya saat pertama masuk.',
                        'Lupa kata sandi: ketik `RESET` di **Lupa sandi?**, lalu **Buat sandi sementara**. Kata sandi itu tampil sekali saja.',
                        'Staf berhenti: hapus centang **Akun aktif (boleh masuk)** atau tekan **Nonaktifkan**. Akun tidak pernah dihapus.',
                    ],
                ],
                [
                    'id'    => 'sekolah',
                    'title' => 'Sekolah dan kode penelitian',
                    'steps' => [
                        ['Buka Sekolah. Rapikan nama sekolah yang diketik siswa: **Gabungkan otomatis** yang sudah jelas, gabungkan sisanya satu per satu, atau **Sahkan sebagai sekolah baru**.', ['admin/sekolah', 'Sekolah', 'schools.manage']],
                        'Beri kode penelitian lewat NPSN: S, U, atau V diikuti dua angka (misalnya `S03`). Kode harus unik.',
                    ],
                    'note' => 'Kode tidak bisa diubah setelah sekolah punya siswa. Hanya admin yang melihat pasangan NPSN dan kode penelitian.',
                ],
                [
                    'id'    => 'siswa-admin',
                    'title' => 'Akun siswa',
                    'steps' => [
                        ['Cari siswa lewat kotak pencarian di atas atau menu Siswa. Admin melihat kartu akun (kode, nama, nama pengguna, sekolah, status), tanpa data penelitian.', ['admin/peserta', 'Siswa', 'participants.accounts']],
                        'Lupa kata sandi: di halaman siswa ketik `RESET`, lalu **Buat sandi sementara**.',
                        'Atas permintaan ketua: **Betulkan data pendaftaran** (umur, jenis kelamin, kelas) dengan alasan tanpa nama, atau **Aktifkan lagi**.',
                    ],
                ],
                [
                    'id'    => 'konten',
                    'title' => 'Konten dan impor bank soal',
                    'steps' => [
                        ['Tantangan: tekan **Rincian verifikasi**. **Harus diperbaiki** wajib dibereskan; **Perlu dicek** boleh tetap bila disengaja. Tekan **Periksa ulang** setelah memperbaiki.', ['admin/konten', 'Tantangan', 'content.view']],
                        ['Impor bank soal: **Unduh templat Excel**, isi tanpa mengubah judul kolom atau nama lembar.', ['admin/konten/impor-bank', 'Impor bank soal', 'content.edit']],
                        'Pilih berkas lalu tekan **Periksa berkas** (belum menyimpan apa pun). Baca kesalahan dan peringatan.',
                        'Bila tidak ada kesalahan, tekan **Simpan soal ke permainan**. Satu baris gagal berarti tidak ada yang tersimpan.',
                    ],
                    'note' => 'Kunci jawaban soal yang sudah dijawab siswa tidak bisa diubah lewat impor. Siswa yang mulai bermain setelah disimpan mendapat soal baru.',
                ],
                [
                    'id'    => 'narasi',
                    'title' => 'Rekaman narasi',
                    'steps' => [
                        ['Buka Rekaman narasi. **Kemajuan rekaman** menunjukkan per bahasa: disetujui, menunggu persetujuan, belum ada.', ['admin/konten/narasi', 'Rekaman narasi', 'content.view']],
                        'Unduh daftar rekaman dan naskah PDF untuk pengisi suara.',
                        'Tekan **Unggah rekaman**. Nama berkas harus sama dengan kodenya (misalnya `intro-01.mp3`). Rekaman yang sudah disiapkan sebelumnya dimasukkan lewat **Ambil rekaman**.',
                        'Dengarkan, lalu tekan **Setujui semua rekaman bahasa …**. Hanya rekaman yang disetujui terdengar oleh siswa.',
                    ],
                    'warn' => '**Batalkan semua rekaman** menghapus berkas dan tidak bisa dibatalkan. Siswa tidak mendengar narasi bahasa itu sampai rekaman baru diunggah dan disetujui.',
                ],
                [
                    'id'    => 'gambar',
                    'title' => 'Gambar dan kelengkapan aset',
                    'steps' => [
                        ['Gambar & mockup: per baris, buka **Detail** untuk melihat ukuran dan letaknya, lalu **Unggah gambar asli**.', ['admin/media', 'Gambar & mockup', 'content.view']],
                        'Tekan **Cek berkas hilang/rusak** untuk memeriksa apakah ada gambar yang hilang atau rusak (tidak mengubah apa pun).',
                        ['Kelengkapan aset: selesaikan ubin **Belum ada** dan **Perlu dicek** sampai semuanya **Lengkap**.', ['admin/media/kelengkapan', 'Kelengkapan aset', 'content.view']],
                    ],
                    'note' => 'Gambar contoh (mockup) harus diganti gambar asli sebelum pengambilan data.',
                ],
                [
                    'id'    => 'siap-penelitian',
                    'title' => 'Daftar siap penelitian',
                    'lead'  => 'Tampil di halaman Penelitian. Butir yang belum lolos juga muncul di Ringkasan admin dengan tombol **Periksa**.',
                    'list'  => [
                        'Tidak ada gambar contoh.',
                        'Rekaman narasi disetujui.',
                        'Verifikasi konten siap penelitian lolos (gambar contoh dihitung sebagai kesalahan).',
                        'Paket aset terbit, lengkap, dan terverifikasi.',
                    ],
                ],
                [
                    'id'    => 'rilis',
                    'title' => 'Rilis dan paket aset',
                    'steps' => [
                        ['Rilis: isi nama versi dan perubahannya, tekan **Simpan versi (belum aktif)**, lalu **Aktifkan** di daftar versi.', ['admin/studi/rilis', 'Rilis', 'releases.manage']],
                        ['Paket aset: tekan **Nyalakan** atau **Bangun ulang paket**, biarkan halaman terbuka sampai selesai, periksa ringkasannya, lalu **Terbitkan**.', ['admin/studi/rilis/paket', 'Paket aset', 'releases.manage']],
                        'Tekan **Periksa sekarang** untuk memastikan paket lengkap dan utuh.',
                    ],
                    'warn' => 'Aktifkan versi atau ubah paket hanya di antara dua gelombang pengambilan data, tidak di tengahnya. Saat penelitian berjalan, panel meminta centang dan alasan.',
                ],
                [
                    'id'    => 'tata-kelola',
                    'title' => 'Tata kelola: penghapusan, audit, perawatan data',
                    'steps' => [
                        ['Permintaan orang tua yang sudah dikonfirmasi ketua muncul di Tata kelola sebagai **Permintaan orang tua**.', ['admin/tata-kelola', 'Tata kelola', 'deletion.execute']],
                        'Periksa jumlah catatan, ketik `HAPUS`, lalu tekan **Hapus sekarang**.',
                        'Setelah cadangan data diganti, centang **Cadangan diganti** di halaman permintaan. Lakukan sebelum orang tua diberi tahu.',
                        ['Riwayat aktivitas (audit): saring jenis dan tanggal, buka **Lihat rincian**.', ['admin/tata-kelola/audit', 'Riwayat aktivitas', 'audit.view']],
                        '**Jalankan perawatan data sekarang** sama dengan perawatan harian otomatis: menjeda sesi lama, membuang berkas unduhan lama, dan membuat permintaan hapus untuk data yang lewat batas simpan. Data penelitian tidak dihapus langsung.',
                    ],
                    'note' => 'Penutupan studi: setelah ketua memenuhi syaratnya, buka halaman penutupan studi, tekan **Hitung**, ketik `GANTI`, lalu **Ganti nama dengan kode**. Langkah ini tidak bisa dibatalkan; ganti cadangan sesudahnya.',
                ],
                [
                    'id'    => 'log-teknis',
                    'title' => 'Log teknis dan pemantauan',
                    'steps' => [
                        ['Log teknis: tab Perangkat, Event teknis, dan Sesi; cari dengan kode peserta, unduh CSV bila perlu. Isinya bukan data penelitian.', ['admin/log-teknis', 'Log teknis', 'tech_logs.view']],
                        ['Pemantauan: admin melihat angka saja untuk membantu peneliti saat ada gangguan.', ['admin/pemantauan', 'Pemantauan', 'monitoring.view']],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function never(): array
    {
        return [
            'id'    => 'larangan',
            'title' => 'Yang tidak boleh dilakukan',
            'roles' => ['admin', 'peneliti', 'ketua'],
            'lead'  => 'Berlaku untuk semua peran.',
            'sections' => [
                [
                    'id'    => 'larangan-daftar',
                    'title' => 'Daftar larangan',
                    'list'  => [
                        'Jangan menulis nama siswa atau orang tua di panel, lembar yang diunggah, alasan, atau catatan. Pakai kode peserta. Nama hanya ada di L7.',
                        'Jangan menyalin nama atau nama pengguna dari layar siswa.',
                        'Jangan memotret atau merekam video anak.',
                        'Jangan mengunggah L7, kolom Token di L1, atau kutipan ucapan siswa di L5.',
                        'Jangan menyalakan mode penelitian bersama anak sebelum persetujuan etik terbit.',
                        'Jangan membagikan kata sandi sementara kepada siapa pun selain pemiliknya.',
                        'Jangan meminta nama siswa kepada admin, dan admin tidak meminta data penelitian.',
                        'Jangan menghapus data di luar Tata kelola, dan jangan melewati konfirmasi ketua.',
                        'Jangan menyimpan unduhan data di perangkat tim lebih lama dari yang diperlukan.',
                        'Jangan mengaktifkan versi baru atau mengubah paket aset saat kelas berjalan.',
                    ],
                ],
            ],
        ];
    }
}
