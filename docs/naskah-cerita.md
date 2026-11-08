# Naskah Cerita GELITA

Naskah lengkap seluruh narasi dan dialog permainan, dalam bahasa Indonesia dan Inggris. Naskah ini dipakai untuk dua hal:

1. **Rekaman audio.** Setiap baris punya nama berkas audio sendiri, jadi pengisi suara cukup membaca dari atas ke bawah.
2. **Isi tabel `dialogues`.** Teks di sini disalin persis ke `app/Database/Seeds/data/story.php` (`StoryDataTest` mencocokkan keduanya baris demi baris) dan dipasang saat instalasi oleh `ContentSeeder`. Sesudah instalasi, teks disunting lewat panel (editor dialog). Bila teks naskah perlu diubah, ubah dulu di berkas ini lalu di `data/story.php`, agar rekaman dan isi permainan tetap sama. When a line changes, also append its replaced title and text to `app/Database/Seeds/data/story-previous.php`; on a server that already has content, `php spark gelita:story:sync` then updates the unedited lines (`docs/08_DEPLOYMENT.md` → *Story script changed*).

## Benang merah

Dataran Kedu dahulu diterangi **Cahaya Kedu**, cahaya yang lahir dari ilmu dan budaya dan disimpan di **Lentera Kedu**. Ketika orang mulai membaca dengan terburu-buru, memercayai kabar tanpa memeriksanya, membagikan apa saja tanpa berpikir, dan melupakan cerita leluhur, naiklah **Kabut Lupa**. Kabut itu meretakkan lentera, dan cahayanya pecah menjadi **15 Serpihan Cahaya**, lima di setiap wilayah. Empat jatuh di tanah wilayah itu; yang kelima disembunyikan Kabut Lupa **di dalam layar** ponsel dan tablet warga (cerita penghubung, sejak revisi penelitian 10). **Mbah Kedu**, penjaga lentera, ikut melemah setiap kali cahayanya meredup. **Jaka** menjadi Pembawa Lentera, dan pemain (disapa "penjelajah") berjalan bersamanya.

Setiap wilayah melawan satu wujud Kabut Lupa, sesuai keterampilan membaca yang dilatih di sana:

| Urutan | Wilayah | Keterampilan | Wujud kabut | Taruhan cerita |
|---:|---|---|---|---|
| 1 | Temanggung | Menemukan informasi yang tertulis jelas | Tulisan di papan petunjuk memudar, warga tersesat | Jaka belajar tidak terburu-buru |
| 2 | Magelang | Menghubungkan informasi, membedakan fakta dan pendapat | Urutan relief Borobudur teracak, warga bertengkar soal kabar yang tidak diperiksa | Mbah Kedu mulai melemah |
| 3 | Wonosobo | Menilai informasi dan sumbernya | Pesan berantai ramalan gempa membuat warga panik | Mbah tak kuat naik, Jaka harus berani sendirian |

Tantangan kelima setiap wilayah (Skenario Digital) diawali cerita penghubung *Kabut di Layar* (`scenario_open`): anak-anak dengan tablet di bawah pohon (Temanggung), ponsel yang terus berbunyi di rumah baca (Magelang), dan layar di seluruh Dieng (Wonosobo).

Penutup: lentera menyala, Mbah Kedu pulih, dan Jaka bersama pemain menjadi Penjaga Lentera.

## Tokoh dan arahan suara

| Tokoh | Kode | Arahan suara |
|---|---|---|
| Narator | `narator` | Hangat dan tenang seperti pendongeng. Tempo sedang, memberi jeda pada kalimat penting. Tidak tampil sebagai gambar. |
| Jaka | `jaka` | Anak laki-laki sekitar 11 tahun. Bersemangat dan jujur. Suaranya naik saat senang atau takut, pelan saat menyesal. |
| Mbah Kedu | `mbah_kedu` | Kakek Jawa yang lembut dan bijak. Tempo pelan dengan logat Jawa halus. "Le" (dari *thole*, panggilan sayang untuk anak laki-laki) dibaca pendek. Suaranya lemah dan bergetar pada baris berpose `weak`. |

Di teks Inggris, Jaka tetap memanggil "Mbah" agar nuansa lokalnya terjaga, sementara papan nama di layar menulis "Grandpa Kedu". "Le" diterjemahkan menjadi "my boy".

## Konvensi berkas audio

Rekaman narasi tidak ikut di repositori: rekaman sintetis lama dihapus 17 Oktober 2026 dan diganti rekaman pengisi suara (lihat [`docs/audio.md`](audio.md)). Rekaman diimpor dengan konvensi di bawah ini.

- Folder: `public/assets/audio/narasi/id/` untuk bahasa Indonesia dan `public/assets/audio/narasi/en/` untuk bahasa Inggris.
- Nama berkas mengikuti kode di setiap judul baris di bawah, ditambah `.mp3`, misalnya `narasi/id/dialog-magelang-09.mp3`.
- Format yang disarankan: MP3 mono, 44,1 kHz, 64–96 kbps. Kenyaringan sekitar −16 LUFS, dengan jeda hening ±0,3 detik di awal dan akhir.
- Rekam suara saja, tanpa musik latar. Musik dan efek suara diputar terpisah oleh permainan.
- Satu berkas berisi satu baris. Jangan menggabungkan beberapa baris ke satu berkas, karena teks di layar maju mengikuti selesainya audio.
- Format yang diterima saat impor: MP3 (disarankan), M4A, OGG, dan WAV.
- Audio Inggris boleh menyusul. Bila berkasnya belum ada, layar tetap menampilkan teksnya dan pemain melanjutkan slide secara manual.

| Konteks (`context_code`) | Awalan berkas | Jumlah per bahasa | Tampil di |
|---|---|---:|---|
| `intro` | `intro-NN` | 9 | Cerita pembuka |
| `map_intro` | `peta-NN` | 3 | Kunjungan pertama ke Peta Kedu |
| `region_intro` | `kenal-{wilayah}-NN` | 12 | Ikon lentera "Kenali wilayah" di peta |
| `level_open` | `dialog-{wilayah}-NN` | 47 | Saat wilayah baru terbuka |
| `scenario_open` | `layar-{wilayah}-NN` | 18 | Cerita penghubung, sekali sebelum tantangan kelima (Skenario Digital) |
| `level_done` | `tuntas-{wilayah}-NN` | 15 | Saat wilayah tuntas (setelah tantangan kelima) |
| `ending` | `penutup-NN` | 5 | Setelah seluruh wilayah tuntas |
| `map_done` | `peta-tuntas-NN` | 3 | Peta Kedu setelah seluruh wilayah tuntas (menggantikan `map_intro`) |
| **Total naskah** | | **112** | Durasi rekaman sekitar 15–17 menit per bahasa |
| `hunt_clue` | `petunjuk-{node}-NN` | 12 | Petunjuk Mbah Kedu di Cari Objek babak 1 (`tmg-4`, `mgl-4`, `wnb-4`, masing-masing 4), di samping teks petunjuk — teks dari bank soal, bukan naskah ini |
| `post_clue` | `unggahan-{kode butir}` | 24 | Petunjuk *Periksa Unggahan* (Cari Objek babak 2), dibacakan Mbah Kedu, hanya lewat tombol ▶ — teks dari bank soal |
| `scenario_situation` | `skenario-{kode butir}` | 24 | Situasi Skenario Digital, dibacakan Narator, hanya lewat tombol ▶ — teks dari bank soal |
| `challenge_instruction` | `instruksi-{tantangan}` | 15 | Instruksi kelima belas tantangan, dibacakan Narator, hanya lewat tombol ▶ — teks dari bank soal |
| **Total rekaman** | | **± 187** | |

Baris `petunjuk-*` **tidak ada di naskah ini**: teksnya berasal dari bank soal, yaitu kolom pertanyaan (`prompt_id` / `prompt_en`) butir `find_object` target babak 1 di [`docs/bank-soal/data/`](bank-soal/data/) (node `tmg-4`, `mgl-4`, `wnb-4`). Dibacakan Mbah Kedu. `{node}` = kode tantangan; NN = urutan petunjuk target babak 1 menurut urutan butir, dimulai 01, sehingga `petunjuk-tmg-4-01` … `-04` sama dengan butir Temukan Benda `tmg-4-x01` … `tmg-4-x04`. Butir *Periksa Unggahan* (`a01`–`a04`, `b01`–`b04`) punya rekamannya sendiri, `unggahan-{kode butir}`.

Rekaman `unggahan-*`, `skenario-*`, dan `instruksi-*` juga **tidak ada di naskah ini**; teksnya dari bank soal (petunjuk babak 2, situasi skenario, dan kolom instruksi tantangan). Ketiganya hanya diputar saat siswa mengetuk ▶, tidak pernah otomatis, dan setiap putaran tercatat bersama id pengerjaan. Kode berkas butir memakai kode butir, bukan nomor urut, supaya variasi a dan b tidak tertukar. Situasi dan petunjuk *Periksa Unggahan* dibaca **datar dan sama untuk setiap situasi**: tanpa nada curiga, menakut-nakuti, atau mendesak; kutipan pesan dibaca biasa; alamat situs dibaca seperti tertulis; nama bagian kunci tidak ditekan (12 → *Arahan suara*). Bacaan dan pernyataan Isi Rumpang dan Benar atau Salah tidak pernah dibacakan. Benda pengecoh tidak punya petunjuk dan tidak direkam. Teks terkini ada di daftar rekaman XLSX (kelompok "Petunjuk Cari Objek"), karena admin dapat menyuntingnya di bank soal.

## Mengimpor dan menyetujui audio

Rekaman tidak perlu dipasang satu per satu. Selama nama berkasnya sama dengan kode baris (huruf besar/kecil tidak berpengaruh), GELITA menemukan barisnya sendiri. Kode wilayah dibaca dari tabel `levels`.

**Daftar kerja pengisi suara.** Panel admin → **Rekaman narasi → Unduh daftar rekaman (Excel)** menghasilkan XLSX berisi kode berkas, konteks, wilayah, tokoh, pose, efek, judul, teks ID, teks EN, dan status audio ID/EN setiap baris. Teksnya diambil dari basis data, jadi suntingan admin ikut terbawa.

**Cara memasang rekaman** (pilih salah satu; keduanya memakai `App\Libraries\NarrationImporter`):

1. **Unggah lewat panel.** **Rekaman narasi → Unggah rekaman** (`/admin/konten/narasi/unggah`): pilih bahasa, pilih banyak berkas sekaligus, lalu **Unggah rekaman**; hasilnya berstatus draft. Centang *Periksa nama berkas saja, jangan simpan dulu* untuk mencocokkan nama tanpa menyimpan. Berkas disalin ke `public/assets/uploads/` dan ikut backup. Batas per berkas adalah yang terkecil dari `Config\Gelita::$maxUploadBytes` dan `upload_max_filesize` PHP; jumlah dan ukuran total per unggahan mengikuti `max_file_uploads` (bawaan PHP 20 berkas) dan `post_max_size`. Halaman itu menampilkan batas server yang berlaku, dan `admin/narration-upload.js` menahan pilihan yang pasti melebihinya.
2. **Lewat folder.** Taruh berkas di `public/assets/audio/narasi/{id|en}/` (commit ke repositori, lalu deploy), kemudian jalankan:

   ```bash
   php spark gelita:narration:import --dry-run        # lihat dulu hasilnya
   php spark gelita:narration:import                  # kedua bahasa
   php spark gelita:narration:import --locale=en      # satu bahasa
   ```

   Tombol **Ambil rekaman Indonesia / Inggris** (dan **Periksa dulu**) di halaman Unggah rekaman melakukan hal yang sama. Berkas didaftarkan di tempatnya, tidak disalin.

**Yang terjadi pada setiap berkas yang cocok:** aset `audio.narasi.{locale}.{kode}` dibuat atau diganti; baris `audio_assets` berisi bahasa, tokoh baris itu (narator = kosong), konteks, transkrip = teks baris pada bahasa itu, durasi (MP3 dan WAV terbaca otomatis), cara produksi *rekaman sendiri*, dan status **draft**; lalu rekaman ditautkan ke baris naskahnya.

**Laporan hasil** memisahkan: baru, diganti (kembali ke draft), sama persis (dilewati, persetujuan tetap), nama tidak dikenal beserta saran nama terdekat (mis. `intro-1.mp3` → `intro-01.mp3`, `dialog-temangung-03.mp3` → `dialog-temanggung-03.mp3`), gagal (mis. bukan berkas audio), dan baris naskah yang belum punya rekaman.

**Aturan impor ulang:**

- Rekaman yang isinya berubah mengganti rekaman lama dan **kembali ke draft**, jadi harus didengar dan disetujui ulang.
- Berkas yang sama persis dengan rekaman terpasang dilewati. Perintah ini aman dijalankan berulang tanpa menghapus persetujuan.
- Dari folder, rekaman yang diunggah lewat panel dan lebih baru dari berkas folder tidak ditimpa.
- Satu kode dengan beberapa berkas (mis. `.mp3` dan `.wav`) memakai satu berkas menurut urutan mp3, m4a, ogg, wav; sisanya dilaporkan ganda.

**Menyetujui.** Rekaman draft belum terdengar pemain. Di halaman **Rekaman narasi** (`/admin/konten/narasi`) setiap baris punya pemutar kecil untuk ID dan EN beserta statusnya (belum ada / menunggu persetujuan / disetujui), dan tautan **Sunting** ke baris itu di editor dialog (petunjuk Cari Objek: ke soalnya). Setelah didengarkan, tombol **Setujui semua rekaman Indonesia / Inggris** menyetujui seluruh narasi draft satu bahasa sekaligus (dengan konfirmasi). Yang ikut hanya audio narasi naskah dan petunjuk Cari Objek; audio lain seperti narasi kartu misi tetap disetujui satu per satu di halaman **Rekaman suara** (`/admin/media/audio`, dibuka dari **Gambar & mockup**). Persetujuan massal tercatat di audit log sebagai `audio_approve_bulk` beserta jumlahnya.

## Kamus pose dan efek

Pose menentukan gambar tokoh yang tampil (slot `char.{jaka|kedu}.{pose}.{n}`). Bila gambar pose belum diunggah, dipakai pose `idle`.

| Tokoh | Pose |
|---|---|
| Jaka | `idle`, `happy`, `bow` (sudah ada); `sad`, `afraid`, `determined` (baru) |
| Mbah Kedu | `idle` (sudah ada); `smile`, `worried`, `weak` (baru) |

Efek layar diputar saat baris itu muncul. Bila perangkat meminta gerak dikurangi, efek tidak diputar.

| Efek | Tampilan |
|---|---|
| `fog` | Kabut kelabu merayap masuk dari tepi layar |
| `fog-lift` | Kabut tersibak dan layar kembali cerah |
| `glow` | Cahaya keemasan hangat berdenyut |
| `flash` | Kilat cahaya putih singkat |
| `shake` | Layar bergetar sebentar |
| `dim` | Layar meredup, seperti lentera yang melemah |
| (kosong) | Tanpa efek |

---

## 1. Cerita pembuka (`intro`)

Wajib ditonton pada kesempatan pertama. Pemain tetap dapat menekan Lanjut di setiap slide.

#### `intro-01` · Narator · efek `glow`
**Judul:** Dataran yang Bercahaya / *The Shining Plain*

**ID:** Dahulu kala, ada dataran luas bernama Dataran Kedu. Di sekelilingnya berdiri Gunung Sindoro, Sumbing, Merapi, Merbabu, dan Perbukitan Menoreh. Setiap malam, desa-desanya diterangi cahaya lembut yang disebut Cahaya Kedu.

**EN:** Long ago, there was a wide plain called the Kedu Plain. Around it stood Mount Sindoro, Mount Sumbing, Mount Merapi, Mount Merbabu, and the Menoreh Hills. Every night, its villages glowed with a gentle light called the Light of Kedu.

#### `intro-02` · Narator
**Judul:** Cahaya dari Ilmu dan Budaya / *A Light of Knowledge and Culture*

**ID:** Cahaya itu lahir dari ilmu dan budaya. Ia datang dari dongeng para kakek dan nenek, dari relief Candi Borobudur, dan dari tembang para petani. Ia juga menyala setiap kali seseorang membaca dengan teliti. Cahaya itu disimpan di dalam sebuah lentera tua bernama Lentera Kedu.

**EN:** That light was born from knowledge and culture. It came from grandparents' tales, from the reliefs of Borobudur Temple, and from the songs of the farmers. It also shone every time someone read with care. The light was kept inside an old lantern called the Lantern of Kedu.

#### `intro-03` · Narator · efek `fog`
**Judul:** Kabut Lupa / *The Mist of Forgetting*

**ID:** Namun perlahan, orang-orang mulai lupa. Mereka membaca dengan terburu-buru, memercayai kabar tanpa memeriksanya, membagikan apa saja tanpa berpikir, dan meninggalkan cerita para leluhur. Dari kebiasaan itu, naiklah kabut kelabu yang dingin. Namanya Kabut Lupa.

**EN:** But slowly, people began to forget. They read in a hurry, believed news without checking it, shared anything without thinking, and left their ancestors' stories behind. From those habits, a cold grey mist rose up. It was called the Mist of Forgetting.

#### `intro-04` · Narator · efek `shake`
**Judul:** Malam Lentera Retak / *The Night the Lantern Cracked*

**ID:** Pada suatu malam, Kabut Lupa melilit Lentera Kedu. Kacanya retak, apinya bergetar, lalu cahayanya pecah! Lima belas Serpihan Cahaya melesat ke langit dan jatuh di Temanggung, Magelang, dan Wonosobo.

**EN:** One night, the Mist of Forgetting coiled around the Lantern of Kedu. Its glass cracked, its flame trembled, and then its light shattered! Fifteen Shards of Light shot into the sky and fell across Temanggung, Magelang, and Wonosobo.

#### `intro-05` · Mbah Kedu · pose `weak` · efek `dim`
**Judul:** Penjaga yang Menua / *The Ageing Keeper*

**ID:** Aku Mbah Kedu, penjaga lentera ini. Kakiku sudah tua, jadi aku hanya bisa menemanimu pelan-pelan. Setiap kali cahayanya meredup, tubuhku ikut melemah. Aku butuh anak yang berani dan mau membaca dengan sungguh-sungguh.

**EN:** I am Mbah Kedu, which means Grandpa Kedu, the keeper of this lantern. My legs are old, so I can only walk slowly beside you. Each time its light fades, my body grows weaker too. I need a child who is brave and willing to read with all their heart.

#### `intro-06` · Jaka · pose `determined` · efek `glow`
**Judul:** Jaka, Pembawa Lentera / *Jaka, the Lantern Bearer*

**ID:** Biar aku yang pergi, Mbah! Aku akan mencari kelima belas serpihan itu satu per satu, sampai Lentera Kedu menyala lagi. Aku janji!

**EN:** Let me go, Mbah! I will find all fifteen shards, one by one, until the Lantern of Kedu shines again. I promise!

#### `intro-07` · Narator
**Judul:** Cara Mengumpulkan Serpihan / *How to Gather the Shards*

**ID:** Setiap wilayah menyimpan lima serpihan, dan setiap serpihan dijaga oleh satu tantangan. Selesaikan tantangannya untuk mendapatkan serpihan dan bintang. Lihat papan Serpihan Cahaya di bagian atas layar: cahayanya bertambah setiap kali serpihan kembali.

**EN:** Each region holds five shards, and each shard is guarded by a challenge. Complete the challenge to win its shard and stars. Watch the Shards of Light panel at the top of the screen: its light grows every time a shard returns.

#### `intro-08` · Mbah Kedu · pose `smile`
**Judul:** Tiga Kunci Cahaya / *Three Keys of Light*

**ID:** Ingat tiga kunci ini, Le. Di Temanggung, temukan informasi yang tertulis jelas. Di Magelang, hubungkan informasi dan bedakan fakta dari pendapat. Di Wonosobo, timbang setiap informasi dan dari mana asalnya.

**EN:** Remember these three keys, my boy. In Temanggung, find the information that is clearly written. In Magelang, connect information and tell facts from opinions. In Wonosobo, weigh every piece of information and where it comes from.

#### `intro-09` · Narator · efek `glow`
**Judul:** Perjalanan Dimulai / *The Journey Begins*

**ID:** Dan kamu, penjelajah, akan berjalan bersama Jaka. Bacalah dengan teliti, pikirkan baik-baik, dan putuskan dengan bijak. Kabut Lupa sudah menunggu. Mari nyalakan kembali Cahaya Kedu!

**EN:** And you, explorer, will walk beside Jaka. Read carefully, think it through, and decide wisely. The Mist of Forgetting is waiting. Let us bring back the Light of Kedu!

---

## 2. Narasi Peta Kedu (`map_intro`)

Diputar otomatis setelah loading "Membuka Peta Kedu". Pada kunjungan berikutnya, narasi ini tampil sebagai balon teks dengan tombol putar.

#### `peta-01` · Jaka · pose `idle` · efek `fog`
**Judul:** Dataran Kedu / *The Kedu Plain*

**ID:** Inilah Dataran Kedu. Dulu dataran ini menyimpan cahaya ilmu dan budaya. Sekarang lihatlah... Kabut Lupa menutupi hampir semuanya.

**EN:** This is the Kedu Plain. This plain once held the light of knowledge and culture. Now look... the Mist of Forgetting covers almost all of it.

#### `peta-02` · Jaka · pose `determined`
**Judul:** Tiga Wilayah / *Three Regions*

**ID:** Aku harus menjelajahi tiga wilayah untuk memulihkannya: Temanggung, lalu Magelang, dan terakhir Wonosobo. Di setiap wilayah, lima Serpihan Cahaya sedang menunggu.

**EN:** I must explore three regions to bring it back: Temanggung, then Magelang, and finally Wonosobo. In each region, five Shards of Light are waiting.

#### `peta-03` · Jaka · pose `happy` · efek `glow`
**Judul:** Kenali Dulu / *Get to Know Them First*

**ID:** Ketuk tombol Kenali di setiap wilayah untuk mendengar cerita Mbah Kedu tentang tempat itu. Kalau sudah siap, pilih wilayah yang sudah terbuka, lalu ketuk Lanjut. Ayo, kita berangkat!

**EN:** Tap Get to know on each region to hear Mbah Kedu's story about that place. When you are ready, choose an open region and tap Continue. Come on, let's go!

---

## 3. Kenali wilayah (`region_intro`)

Mbah Kedu memperkenalkan geografi dan budaya tiap wilayah. Narasi ini dibuka dari ikon lentera di peta. Sifatnya pilihan, tetapi ikonnya berdenyut sampai didengar. Faktanya selaras dengan bank soal dan Pustaka (`docs/bank-soal/`).

### Temanggung

#### `kenal-temanggung-01` · Mbah Kedu · pose `smile`
**Judul:** Di Antara Dua Gunung / *Between Two Mountains*

**ID:** Inilah Temanggung, Le. Tanahnya diapit dua gunung, Sindoro dan Sumbing. Kalau pagi cerah, kedua puncaknya tampak berdiri berdampingan, seperti kakak dan adik yang saling menjaga. Di lereng Sindoro ada mata air Umbul Jumprit, hulu Sungai Progo.

**EN:** This is Temanggung, my boy. It lies between two mountains, Sindoro and Sumbing. On a clear morning, their peaks stand side by side, like an older and a younger sibling looking after each other. On the slopes of Sindoro is the Umbul Jumprit spring, the source of the Progo River.

#### `kenal-temanggung-02` · Mbah Kedu · pose `idle`
**Judul:** Tanah Para Petani / *Land of Farmers*

**ID:** Tanah di lereng gunung ini sangat subur. Para petani menanam tembakau, kopi, dan sayuran. Kopi dari lereng Sindoro dan Sumbing terkenal harum sampai ke luar daerah. Sungai-sungainya yang jernih menjadi rumah ikan uceng, ikan sungai yang kecil. Uceng goreng adalah makanan khas Temanggung.

**EN:** The soil on these mountain slopes is very fertile. Farmers grow tobacco, coffee, and vegetables. Coffee from the slopes of Sindoro and Sumbing is famous for its aroma, even far beyond the region. Its clear rivers are home to the uceng, a small river fish. Fried uceng is a special dish of Temanggung.

#### `kenal-temanggung-03` · Mbah Kedu · pose `idle`
**Judul:** Jejak Masa Lalu / *Traces of the Past*

**ID:** Temanggung juga menyimpan jejak masa lalu. Ada Situs Liyangan, permukiman kuno yang pernah tertimbun pasir dan batu dari letusan Gunung Sindoro. Ada pula Candi Pringapus dengan arca lembu Nandi di dalamnya, dan Prasasti Gondosuli yang diperkirakan dibuat sekitar tahun 832 Masehi.

**EN:** Temanggung also keeps traces of the past. There is the Liyangan Site, an ancient settlement once buried under sand and stones from an eruption of Mount Sindoro. There is also Pringapus Temple, with a statue of Nandi the bull inside, and the Gondosuli Inscription, thought to date from around 832 CE.

#### `kenal-temanggung-04` · Mbah Kedu · pose `smile` · efek `glow`
**Judul:** Pesan Mbah / *Mbah's Advice*

**ID:** Saat ada perayaan, warga menari kuda lumping, yang juga disebut jaran kepang. Penarinya menunggang kuda kepang, kuda-kudaan dari anyaman bambu. Di sini kamu belajar menemukan informasi yang tertulis jelas di gambar, teks pendek, dan suara. Bacalah dengan teliti karena jawabannya sering ada di depan mata.

**EN:** At celebrations, people dance the kuda lumping, also called jaran kepang. The dancers ride a kuda kepang, a horse made of woven bamboo. Here you will learn to find information that is clearly stated in pictures, short texts, and sounds. Read carefully, because the answer is often right in front of you.

### Magelang

#### `kenal-magelang-01` · Mbah Kedu · pose `smile`
**Judul:** Tanah Candi Agung / *Land of the Great Temple*

**ID:** Magelang, Le, adalah rumah Candi Borobudur, candi Buddha terbesar di dunia. Candi itu dibangun lebih dari seribu tahun lalu, batu demi batu, tanpa mesin modern.

**EN:** Magelang, my boy, is home to Borobudur Temple, the largest Buddhist temple in the world. It was built more than a thousand years ago, stone by stone, without modern machines.

#### `kenal-magelang-02` · Mbah Kedu · pose `idle`
**Judul:** Buku dari Batu / *A Book Made of Stone*

**ID:** Dinding Borobudur dihiasi 2.672 panel relief, dan lebih dari separuhnya bercerita. Membaca relief itu seperti membaca buku raksasa dari batu: urutannya harus benar supaya ceritanya utuh.

**EN:** Borobudur's walls are decorated with 2,672 relief panels, and more than half of them tell stories. Reading those reliefs is like reading a giant book made of stone: the order must be right for the story to make sense.

#### `kenal-magelang-03` · Mbah Kedu · pose `idle`
**Judul:** Tiga Candi Segaris / *Three Temples in a Line*

**ID:** Tak jauh dari Borobudur ada Candi Mendut dan Candi Pawon. Ketiganya berdiri hampir segaris. Setiap Waisak, umat Buddha berjalan dari Mendut ke Borobudur membawa air suci dari Umbul Jumprit, Temanggung. Di tengah kota ada Gunung Tidar, si paku tanah Jawa dalam cerita rakyat.

**EN:** Not far from Borobudur stand Mendut Temple and Pawon Temple. The three temples stand almost in a straight line. Every Vesak, Buddhists walk from Mendut to Borobudur carrying holy water from Umbul Jumprit in Temanggung. In the middle of the city is Mount Tidar, the nail of Java in folk tales.

#### `kenal-magelang-04` · Mbah Kedu · pose `smile` · efek `glow`
**Judul:** Pesan Mbah / *Mbah's Advice*

**ID:** Jangan lewatkan tari Topeng Ireng yang gagah. Penarinya memakai kuluk, hiasan kepala dari bulu warna-warni. Di Magelang, satu informasi saja tidak cukup. Kamu harus menghubungkan beberapa informasi, lalu membedakan mana fakta dan mana pendapat.

**EN:** Don't miss the bold Topeng Ireng dance. The dancers wear a kuluk, a headdress of colourful feathers. In Magelang, one piece of information is not enough. You must connect several pieces of information, then tell which are facts and which are opinions.

### Wonosobo

#### `kenal-wonosobo-01` · Mbah Kedu · pose `smile` · efek `fog`
**Judul:** Negeri di Atas Awan / *The Land Above the Clouds*

**ID:** Wonosobo adalah pintu menuju Dataran Tinggi Dieng, yang dijuluki negeri di atas awan. Letaknya sangat tinggi, sampai-sampai awan dan kabut sering lewat di antara rumah penduduk.

**EN:** Wonosobo is the gateway to the Dieng Plateau, known as the land above the clouds. It is so high up that clouds and mist often drift between people's houses.

#### `kenal-wonosobo-02` · Mbah Kedu · pose `idle`
**Judul:** Candi, Telaga, dan Tradisi / *Temples, a Lake, and a Tradition*

**ID:** Di Dieng berdiri kompleks Candi Arjuna, salah satu candi Hindu tertua di Jawa. Ada juga Telaga Warna, yang airnya mengandung belerang sehingga bisa tampak hijau, biru, atau kekuningan. Warga Dieng juga punya tradisi memotong rambut gimbal, yaitu rambut yang menggumpal dengan sendirinya, pada anak-anak.

**EN:** On Dieng stands the Arjuna Temple complex, one of the oldest Hindu temples in Java. There is also Telaga Warna, the Lake of Colours, whose sulphurous water can look green, blue, or yellowish. The people of Dieng also keep a tradition of cutting the naturally matted hair of some children.

#### `kenal-wonosobo-03` · Mbah Kedu · pose `idle`
**Judul:** Hangatnya Budaya Pegunungan / *The Warmth of Mountain Culture*

**ID:** Udara di sini dingin sekali. Warga menghangatkan badan dengan teh dari kebun Tambi dan mi ongklok berkuah kental. Mi itu direbus di ongklok, keranjang bambu kecil yang dicelup-celupkan ke air mendidih. Ada juga manisan carica, si pepaya gunung.

**EN:** The air here is very cold. People warm up with tea from the Tambi estate and ongklok noodles in a thick sauce. The noodles are boiled in an ongklok, a small bamboo basket dipped into boiling water. There is also sweet carica, the mountain papaya.

#### `kenal-wonosobo-04` · Mbah Kedu · pose `worried` · efek `fog`
**Judul:** Pesan Mbah / *Mbah's Advice*

**ID:** Di sini ada tari Topeng Lengger dan bundengan, alat musik dari tudung bambu penggembala bebek. Tapi di sinilah Kabut Lupa paling tebal, Le. Kamu akan menemukan banyak kabar, dan tidak semuanya benar. Periksa sumbernya, timbang alasannya, lalu putuskan dengan bijak.

**EN:** Here you will find the Topeng Lengger mask dance and the bundengan, an instrument made from a duck herder's bamboo hood. But this is also where the Mist of Forgetting is thickest, my boy. You will come across a lot of news, and not all of it is true. Check the source, weigh the reasons, then decide wisely.

---

## 4. Dialog masuk wilayah (`level_open`)

Tampil setiap kali wilayah baru terbuka (aturan dialog gate). Jaka berdiri di kiri panggung dan Mbah Kedu di kanan. Baris dialog tidak berjudul.

### Temanggung: *Papan Petunjuk yang Memudar*

#### `dialog-temanggung-01` · Mbah Kedu · pose `worried` · efek `fog`
**ID:** Kita sudah sampai, Le. Temanggung... Dulu, dari bukit ini, lampu-lampu desa tampak seperti bintang yang jatuh ke ladang. Sekarang lihatlah. Semuanya kelabu.

**EN:** We're here, my boy. Temanggung... From this hill, the village lights used to look like stars that had fallen onto the fields. Now look. Everything is grey.

#### `dialog-temanggung-02` · Jaka · pose `afraid`
**ID:** Kabutnya tebal sekali, Mbah. Aku hampir tidak bisa melihat jalan setapak di depan kita.

**EN:** The mist is so thick, Mbah. I can hardly see the path in front of us.

#### `dialog-temanggung-03` · Mbah Kedu · pose `worried`
**ID:** Kabut Lupa turun ke sini lebih dulu. Dengar itu? Warga saling memanggil di tengah ladang. Mereka tersesat di kampung mereka sendiri.

**EN:** The Mist of Forgetting came here first. Do you hear that? People are calling to each other across the fields. They are lost in their own village.

#### `dialog-temanggung-04` · Jaka · pose `idle`
**ID:** Tersesat? Bagaimana bisa, Mbah? Bukankah di setiap persimpangan ada papan petunjuk?

**EN:** Lost? How, Mbah? Isn't there a signpost at every crossroads?

#### `dialog-temanggung-05` · Mbah Kedu · pose `worried`
**ID:** Ada. Tapi kabut membuat tulisannya samar. Orang hanya melirik sekilas, lalu menebak arah. Kalau tebakannya keliru, mereka bisa berputar-putar di ladang sampai senja.

**EN:** There is. But the mist makes the writing faint. People only glance at it, then guess the way. If they guess wrong, they can wander in circles through the fields until dusk.

#### `dialog-temanggung-06` · Jaka · pose `determined` · efek `glow`
**ID:** Kalau begitu kita harus cepat, Mbah! Aku langsung cari serpihannya saja. Pasti ada yang bersinar di balik kabut!

**EN:** Then we have to hurry, Mbah! I'll just go straight for the shards. Something must be shining behind the mist!

#### `dialog-temanggung-07` · Jaka · pose `happy`
**ID:** Lihat, ada papan petunjuk! Tulisannya "Jembatan", panahnya ke kanan... Ayo, Mbah, lewat sini!

**EN:** Look, a signpost! It says "Bridge", and the arrow points right... Come on, Mbah, this way!

#### `dialog-temanggung-08` · Mbah Kedu · pose `worried` · efek `shake`
**ID:** Tunggu, Le! Berhenti! Jangan hanya melihat panahnya. Baca dulu seluruh tulisannya.

**EN:** Wait, my boy! Stop! Don't just look at the arrow. Read all the words first.

#### `dialog-temanggung-09` · Jaka · pose `sad`
**ID:** "Jembatan rusak! Silakan lewat jalan kiri." Aduh... Kalau tadi aku langsung berlari ke kanan, kita bisa tercebur ke sungai.

**EN:** "Bridge broken! Please take the left path." Oh no... If I had run straight to the right, we could have fallen into the river.

#### `dialog-temanggung-10` · Mbah Kedu · pose `smile`
**ID:** Nah. Mata yang terburu-buru melewatkan jawaban yang tertulis jelas. Kabut Lupa paling senang kalau kita terburu-buru.

**EN:** There, you see. Hurried eyes miss answers that are written clearly. The Mist of Forgetting is happiest when we rush.

#### `dialog-temanggung-11` · Jaka · pose `sad`
**ID:** Maaf, Mbah. Aku cuma ingin cepat-cepat menolong semua orang.

**EN:** I'm sorry, Mbah. I just wanted to help everyone as fast as I could.

#### `dialog-temanggung-12` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Niatmu baik, Le. Tapi menolong juga butuh ketelitian. Lihat, begitu kamu membaca dengan benar, kabut di papan itu menipis. Di balik bukit, serpihan cahaya pertama mulai berkilau.

**EN:** Your heart is in the right place, my boy. But helping also takes care. Look, as soon as you read it properly, the mist on that sign grew thinner. Behind the hill, the first shard of light is starting to sparkle.

#### `dialog-temanggung-13` · Jaka · pose `determined`
**ID:** Aku mengerti sekarang. Aku akan membaca kata demi kata, melihat gambar dengan saksama, dan mendengarkan baik-baik.

**EN:** I understand now. I will read word by word, look at pictures closely, and listen carefully.

#### `dialog-temanggung-14` · Mbah Kedu · pose `idle`
**ID:** Lima serpihan menunggumu di Temanggung. Temukan informasi yang tertulis jelas, maka kabut ini akan mundur dengan sendirinya.

**EN:** Five shards are waiting for you in Temanggung. Find the information that is clearly written, and this mist will retreat on its own.

#### `dialog-temanggung-15` · Jaka · pose `determined` · efek `fog-lift`
**ID:** Tunggu kami, warga Temanggung! Lentera ini akan menyala untuk kalian!

**EN:** Hold on, people of Temanggung! This lantern will shine for you!

### Magelang: *Relief yang Teracak*

#### `dialog-magelang-01` · Jaka · pose `happy` · efek `glow`
**ID:** Mbah, lihat lenteraku! Serpihan dari Temanggung sudah menyala di dalamnya. Magelang pasti lebih mudah!

**EN:** Mbah, look at my lantern! The shards from Temanggung are glowing inside it. Magelang will be easy!

#### `dialog-magelang-02` · Mbah Kedu · pose `idle`
**ID:** Jangan cepat merasa hebat, Le. Kabut di Magelang punya jebakan yang lain.

**EN:** Don't feel too sure of yourself, my boy. The mist in Magelang has other traps.

#### `dialog-magelang-03` · Jaka · pose `idle`
**ID:** Jebakan apa, Mbah? Itu Candi Borobudur, kan? Megah sekali... Tapi kenapa relief-reliefnya tampak aneh?

**EN:** What traps, Mbah? That's Borobudur Temple, isn't it? It's magnificent... But why do the reliefs look strange?

#### `dialog-magelang-04` · Mbah Kedu · pose `worried` · efek `fog`
**ID:** Kabut Lupa mengacak urutannya. Awal cerita ada di akhir, akhir cerita ada di tengah. Siapa pun yang membacanya sekarang hanya mendapat potongan-potongan yang membingungkan.

**EN:** The Mist of Forgetting has jumbled their order. The beginning of the story is at the end, and the end is in the middle. Anyone who reads them now only gets confusing bits and pieces.

#### `dialog-magelang-05` · Jaka · pose `afraid`
**ID:** Mbah, dengar... Di bawah sana ada dua bapak yang sedang bertengkar.

**EN:** Mbah, listen... Down there, two men are arguing.

#### `dialog-magelang-06` · Mbah Kedu · pose `worried`
**ID:** Bapak yang satu berteriak, "Candi ini dibangun dengan semen!" Bapak yang lain membalas, "Bukan! Candi ini baru dibangun seratus tahun lalu!"

**EN:** One man shouts, "This temple was built with cement!" The other shouts back, "No! It was built only a hundred years ago!"

#### `dialog-magelang-07` · Jaka · pose `idle`
**ID:** Jadi, mana yang benar, Mbah?

**EN:** So which one is right, Mbah?

#### `dialog-magelang-08` · Mbah Kedu · pose `smile`
**ID:** Itulah tugasmu, Le. Pendapat boleh berbeda-beda, tetapi fakta bisa dibuktikan. Hubungkan informasi yang satu dengan yang lain, maka kebenaran akan terlihat.

**EN:** That is your task, my boy. Opinions may differ, but facts can be proven. Connect one piece of information with another, and the truth will appear.

#### `dialog-magelang-09` · Mbah Kedu · pose `weak` · efek `dim`
**ID:** Uhh... Maaf, Le. Kaki Mbah gemetar. Setiap kali kabut menebal, tenaga Mbah seperti ikut tersedot.

**EN:** Ohh... Forgive me, my boy. My legs are shaking. Every time the mist grows thicker, it feels like my strength is being drained away.

#### `dialog-magelang-10` · Jaka · pose `afraid` · efek `shake`
**ID:** Mbah! Mbah tidak apa-apa? Tangan Mbah dingin sekali!

**EN:** Mbah! Are you all right? Your hands are so cold!

#### `dialog-magelang-11` · Mbah Kedu · pose `weak`
**ID:** Tenang... Mbah hanya perlu duduk sebentar. Cahaya lentera dan tubuh tua ini saling terikat, Le. Semakin banyak serpihan kembali, semakin kuat Mbah.

**EN:** Don't worry... I just need to sit for a while. The lantern's light and this old body are bound together, my boy. The more shards return, the stronger I become.

#### `dialog-magelang-12` · Jaka · pose `determined`
**ID:** Kalau begitu, Mbah istirahat saja di sini. Biar aku yang menyusun cerita-cerita itu kembali.

**EN:** Then rest here, Mbah. Let me put those stories back together.

#### `dialog-magelang-13` · Mbah Kedu · pose `smile`
**ID:** Kamu sudah berbeda dari anak yang dulu berlari ke papan petunjuk tanpa membacanya.

**EN:** You are not the same child who once ran towards a signpost without reading it.

#### `dialog-magelang-14` · Jaka · pose `happy`
**ID:** Karena aku belajar dari Mbah. Baca dengan teliti dulu, baru bertindak!

**EN:** Because I learned from you, Mbah. Read carefully first, then act!

#### `dialog-magelang-15` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Ingat pesan Mbah: temukan gagasan pokoknya, susun urutannya, dan pisahkan fakta dari pendapat. Lima serpihan Magelang menunggumu.

**EN:** Remember my advice: find the main idea, put things in order, and separate facts from opinions. Five shards of Magelang are waiting for you.

#### `dialog-magelang-16` · Jaka · pose `determined` · efek `fog-lift`
**ID:** Aku berangkat, Mbah. Sebelum senja, relief-relief itu akan bercerita dengan benar lagi!

**EN:** I'm off, Mbah. Before dusk, those reliefs will tell their stories the right way again!

### Wonosobo: *Pesan Berantai di Atas Awan*

#### `dialog-wonosobo-01` · Jaka · pose `afraid` · efek `fog`
**ID:** Brrr... Dingin sekali, Mbah. Kabutnya sampai masuk ke sela-sela jariku.

**EN:** Brrr... It's freezing, Mbah. The mist is even creeping between my fingers.

#### `dialog-wonosobo-02` · Mbah Kedu · pose `worried`
**ID:** Kita di Dieng, Le, negeri di atas awan. Tapi kabut ini bukan kabut biasa. Di sinilah jantung Kabut Lupa.

**EN:** We are on Dieng, my boy, the land above the clouds. But this is no ordinary mist. This is the heart of the Mist of Forgetting.

#### `dialog-wonosobo-03` · Jaka · pose `afraid` · efek `shake`
**ID:** Mbah, dengar! Ada warga yang panik memukul kentongan. Orang-orang berlarian sambil memegang ponsel!

**EN:** Mbah, listen! A frightened villager is beating the kentongan, a wooden alarm drum. People are running around holding their phones!

#### `dialog-wonosobo-04` · Mbah Kedu · pose `worried`
**ID:** Mereka menerima pesan berantai: "Besok pagi akan ada gempa besar di Dieng! Cepat mengungsi! Sebarkan ke semua orang!"

**EN:** They have received a chain message: "A big earthquake will hit Dieng tomorrow morning! Evacuate now! Share this with everyone!"

#### `dialog-wonosobo-05` · Jaka · pose `afraid`
**ID:** Apa pesan itu benar, Mbah? Aku jadi cemas...

**EN:** Is that message true, Mbah? It makes me worried...

#### `dialog-wonosobo-06` · Mbah Kedu · pose `idle`
**ID:** Pertanyaanmu penting, Le. Jawabannya tersembunyi di balik kabut ini, dan kamulah yang akan menemukannya.

**EN:** That's an important question, my boy. The answer is hidden behind this mist, and you are the one who will find it.

#### `dialog-wonosobo-07` · Mbah Kedu · pose `weak` · efek `dim`
**ID:** Le... Mbah tidak kuat naik lebih tinggi lagi. Napas Mbah pendek, dan kaki ini sudah tidak mau melangkah.

**EN:** My boy... I cannot climb any higher. My breath is short, and these legs will not take another step.

#### `dialog-wonosobo-08` · Jaka · pose `sad`
**ID:** Tidak, Mbah! Aku tidak mau berjalan sendirian! Bagaimana kalau aku salah memilih?

**EN:** No, Mbah! I don't want to go on alone! What if I choose wrongly?

#### `dialog-wonosobo-09` · Mbah Kedu · pose `smile`
**ID:** Mbah akan menunggumu di tepi Telaga Warna. Dari sana, Mbah bisa melihat cahayamu setiap kali satu serpihan kembali.

**EN:** I will wait for you on the shore of Telaga Warna. From there, I can see your light every time a shard returns.

#### `dialog-wonosobo-10` · Jaka · pose `afraid`
**ID:** Aku takut, Mbah...

**EN:** I'm scared, Mbah...

#### `dialog-wonosobo-11` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Takut itu wajar, Le. Lentera ini menyala karena keberanianmu, bukan karena kamu tidak pernah takut.

**EN:** Being scared is normal, my boy. This lantern shines because of your courage, not because you are never afraid.

#### `dialog-wonosobo-12` · Mbah Kedu · pose `idle`
**ID:** Dengarkan pesan Mbah. Apa pun yang kamu temui, bacalah sekali lagi sebelum memutuskan.

**EN:** Listen to my advice. Whatever you come across, read it once more before you decide.

#### `dialog-wonosobo-13` · Jaka · pose `idle`
**ID:** Baca sekali lagi sebelum memutuskan... Aku akan mengingatnya, Mbah.

**EN:** Read it once more before deciding... I will remember, Mbah.

#### `dialog-wonosobo-14` · Jaka · pose `determined`
**ID:** Warga Dieng tidak butuh kepanikan. Mereka butuh kabar yang benar. Dan aku akan membantu mereka menemukannya.

**EN:** The people of Dieng don't need panic. They need the truth. And I will help them find it.

#### `dialog-wonosobo-15` · Mbah Kedu · pose `smile`
**ID:** Itu baru anak pemberani. Pergilah, Le. Lima serpihan terakhir menunggu di balik kabut.

**EN:** Now that's a brave child. Go, my boy. The last five shards are waiting behind the mist.

#### `dialog-wonosobo-16` · Jaka · pose `determined` · efek `fog-lift`
**ID:** Tunggu aku di Telaga Warna, Mbah. Saat aku kembali, Lentera Kedu akan menyala paling terang!

**EN:** Wait for me at Telaga Warna, Mbah. When I come back, the Lantern of Kedu will shine brighter than ever!

---

## 5. Cerita penghubung tantangan kelima (`scenario_open`)

Tampil sekali, saat tantangan kelima (Skenario Digital) sebuah wilayah pertama kali dibuka, sebelum kartu misinya. Siswa yang sudah punya pengerjaan di tantangan itu tidak melihatnya lagi, juga di pertemuan lain; ceritanya bisa ditonton ulang dari peta wilayah. Jaka di kiri panggung, Mbah Kedu di kanan, seperti dialog wilayah. Sumber: [`12`](revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md#cerita-penghubung-tantangan-kelima-scenario_open).

### Temanggung: *Kabut di Layar*

#### `layar-temanggung-01` · Jaka · pose `happy` · efek `glow`
**ID:** Mbah, empat serpihan Temanggung sudah kembali! Tapi cahaya lenteraku belum utuh. Di mana serpihan kelima?

**EN:** Mbah, four of Temanggung's shards are back! But my lantern's light still isn't whole. Where is the fifth shard?

#### `layar-temanggung-02` · Mbah Kedu · pose `worried` · efek `fog`
**ID:** Lihat anak-anak di bawah pohon itu, Le. Mereka menunduk menatap layar tablet. Ada kabut tipis yang keluar dari layarnya.

**EN:** Look at the children under that tree, my boy. They are bent over a tablet. A thin mist is drifting out of its screen.

#### `layar-temanggung-03` · Jaka · pose `afraid`
**ID:** Kabut Lupa bisa masuk ke dalam layar, Mbah? Pantas saja unggahan tadi ada yang keliru.

**EN:** The Mist of Forgetting can get inside a screen, Mbah? No wonder that post had mistakes in it.

#### `layar-temanggung-04` · Mbah Kedu · pose `idle`
**ID:** Kabut itu pandai bersembunyi, Le. Di layar, ia menyelinap di antara pesan, foto, dan kabar yang lewat cepat sekali. Serpihan kelima tersangkut di sana.

**EN:** That mist is good at hiding, my boy. On a screen, it slips in among the messages, photos, and news that rush past. The fifth shard is caught in there.

#### `layar-temanggung-05` · Jaka · pose `idle`
**ID:** Jadi bukan layarnya yang jahat, ya, Mbah? Kabutnya yang harus kita usir.

**EN:** So it isn't the screen that's bad, is it, Mbah? It's the mist we have to chase away.

#### `layar-temanggung-06` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Betul, Le. Layar bisa menjadi jendela ilmu. Kali ini, penjelajah, kamulah yang memegang layarnya. Bayangkan setiap cerita terjadi padamu.

**EN:** That's right, my boy. A screen can be a window to knowledge. This time, explorer, you are the one holding the screen. Imagine each story is happening to you.

### Magelang: *Ponsel yang Terus Berbunyi*

#### `layar-magelang-01` · Jaka · pose `idle`
**ID:** Mbah, empat serpihan Magelang sudah kembali. Serpihan kelima pasti tersangkut di layar lagi, ya?

**EN:** Mbah, four of Magelang's shards are back. The fifth one must be caught in a screen again, right?

#### `layar-magelang-02` · Mbah Kedu · pose `weak` · efek `dim`
**ID:** Benar, Le. Lihat rumah baca di ujung jalan itu. Ponsel anak-anak di sana terus berbunyi, dan kabut tipis keluar dari layarnya.

**EN:** That's right, my boy. Look at the reading house at the end of the road. The children's phones there keep chiming, and a thin mist is drifting out of the screens.

#### `layar-magelang-03` · Jaka · pose `idle`
**ID:** Mbah masih lemas. Mbah istirahat saja di sini, ya. Biar aku yang ke sana.

**EN:** You're still weak, Mbah. Please rest here. I'll go over there.

#### `layar-magelang-04` · Mbah Kedu · pose `weak`
**ID:** Pergilah, Le. Kamu sudah menyusun relief dan memisahkan fakta dari pendapat. Sekarang ajak penjelajah bersamamu.

**EN:** Go, my boy. You have put the reliefs back in order and separated facts from opinions. Now take the explorer with you.

#### `layar-magelang-05` · Jaka · pose `determined`
**ID:** Baik, Mbah. Kami akan berpikir dulu sebelum memilih.

**EN:** All right, Mbah. We'll think before we choose.

#### `layar-magelang-06` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Penjelajah, bayangkan setiap cerita itu terjadi padamu. Pilihanmu yang akan menyibak kabut di layar Magelang.

**EN:** Explorer, imagine each of these stories is happening to you. Your choices will clear the mist from Magelang's screens.

### Wonosobo: *Layar Terakhir*

#### `layar-wonosobo-01` · Jaka · pose `happy` · efek `glow`
**ID:** Mbah! Empat serpihan Wonosobo sudah kembali. Tinggal satu lagi, yang terakhir!

**EN:** Mbah! Four of Wonosobo's shards are back. Only one left, the very last one!

#### `layar-wonosobo-02` · Mbah Kedu · pose `worried` · efek `fog`
**ID:** Serpihan terakhir tersembunyi paling dalam, Le. Kabut Lupa membawanya ke layar-layar di seluruh Dieng, dari ponsel di rumah warga sampai tablet di sekolah.

**EN:** The last shard is hidden the deepest, my boy. The Mist of Forgetting has carried it into screens all over Dieng, from the phones in people's homes to the tablets at school.

#### `layar-wonosobo-03` · Jaka · pose `afraid`
**ID:** Di seluruh Dieng? Banyak sekali, Mbah...

**EN:** All over Dieng? That's so many, Mbah...

#### `layar-wonosobo-04` · Mbah Kedu · pose `weak` · efek `dim`
**ID:** Mbah tetap menunggu di tepi telaga ini, Le. Dari sini, Mbah bisa melihat setiap layar yang kembali terang.

**EN:** I'll keep waiting here on the lake shore, my boy. From here, I can see every screen that grows bright again.

#### `layar-wonosobo-05` · Jaka · pose `determined`
**ID:** Aku akan mengingat semua yang sudah kita lalui, Mbah. Aku akan membaca setiap cerita dengan teliti.

**EN:** I will remember everything we have been through, Mbah. I will read every story carefully.

#### `layar-wonosobo-06` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Itu bekal yang cukup. Penjelajah, inilah layar terakhir. Bayangkan setiap cerita terjadi padamu, lalu pikirkan baik-baik.

**EN:** That is all you need. Explorer, this is the last screen. Imagine each story is happening to you, then think it through.

---

## 6. Wilayah tuntas (`level_done`)

Tampil saat tantangan kelima sebuah wilayah selesai, bersamaan dengan momen "Pustaka {wilayah} terbuka!". Jumlah serpihan seluruhnya tidak disebut, karena siswa susulan bisa menuntaskan wilayah dengan urutan lain.

### Temanggung

#### `tuntas-temanggung-01` · Mbah Kedu · pose `smile` · efek `fog-lift`
**ID:** Lihat, Le! Kabut di Temanggung tersibak. Papan petunjuk bisa dibaca lagi, dan warga sudah menemukan jalan pulang.

**EN:** Look, my boy! The mist over Temanggung has parted. The signposts can be read again, and people have found their way home.

#### `tuntas-temanggung-02` · Jaka · pose `happy` · efek `glow`
**ID:** Kabut di layar tablet itu juga hilang, Mbah! Kelima serpihan sudah kembali ke lentera. Rasanya hangat sekali.

**EN:** The mist on that tablet is gone too, Mbah! All five shards are back in the lantern. It feels so warm.

#### `tuntas-temanggung-03` · Mbah Kedu · pose `smile`
**ID:** Anak-anak itu sekarang tahu, Le: data diri disimpan baik-baik, kata sandi hanya untuk pemilik akun, dan pesan yang aneh ditunjukkan dulu kepada orang tua.

**EN:** Those children know now, my boy: personal details are kept safe, a password is only for its owner, and strange messages are shown to a parent first.

#### `tuntas-temanggung-04` · Mbah Kedu · pose `smile`
**ID:** Itu hasil ketelitianmu. Pustaka Temanggung kini terbuka untukmu. Bacalah sepuasnya.

**EN:** That is the reward for your care. The Temanggung Library is now open to you. Read as much as you like.

#### `tuntas-temanggung-05` · Jaka · pose `determined`
**ID:** Terima kasih, Mbah. Berikutnya, Magelang!

**EN:** Thank you, Mbah. Next stop, Magelang!

### Magelang

#### `tuntas-magelang-01` · Mbah Kedu · pose `smile` · efek `fog-lift`
**ID:** Dengar, Le... Kedua bapak itu berhenti bertengkar. Di papan informasi tertulis: Borobudur dibangun pada abad ke-8 dan ke-9 dari batu yang disusun saling mengunci, tanpa semen. Jadi, kabar kedua bapak tadi sama-sama keliru. Sekarang mereka membaca relief bersama-sama.

**EN:** Listen, my boy... Those two men have stopped arguing. The information board says: Borobudur was built in the 8th and 9th centuries from interlocking stones, without cement. So both men's stories were wrong. Now they are reading the reliefs together.

#### `tuntas-magelang-02` · Jaka · pose `happy`
**ID:** Anak-anak di rumah baca juga sudah tenang. Dan Mbah... Mbah sudah bisa berdiri tegak lagi!

**EN:** The children at the reading house are calm again too. And Mbah... you can stand up straight again!

#### `tuntas-magelang-03` · Mbah Kedu · pose `smile`
**ID:** Mereka sekarang bertanya dulu sebelum membagikan foto teman, dan memeriksa dulu kabar yang belum jelas sumbernya.

**EN:** Now they ask first before sharing a friend's photo, and they check any news whose source isn't clear.

#### `tuntas-magelang-04` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Serpihan Magelang sudah kembali, dan tenaga Mbah ikut pulih. Pustaka Magelang kini terbuka untukmu.

**EN:** Magelang's shards have returned, and my strength is coming back. The Magelang Library is now open to you.

#### `tuntas-magelang-05` · Jaka · pose `determined`
**ID:** Tinggal satu wilayah lagi. Wonosobo, kami datang!

**EN:** Only one region left. Wonosobo, here we come!

### Wonosobo

#### `tuntas-wonosobo-01` · Jaka · pose `happy` · efek `fog-lift`
**ID:** Mbah! Aku berhasil! Aku mengajak warga bertanya kepada petugas resmi. Kata petugas, belum ada yang bisa meramal kapan gempa terjadi, dan tidak ada peringatan untuk Dieng. Warga sudah tenang lagi.

**EN:** Mbah! I did it! I got people to ask the officials. The officials say no one can yet predict when an earthquake will happen, and there is no warning for Dieng. Everyone is calm again.

#### `tuntas-wonosobo-02` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Mbah melihatnya dari tepi telaga, Le. Setiap kali kamu memilih dengan bijak, kabut di puncak dan di layar-layar itu menipis sedikit demi sedikit.

**EN:** I saw it from the lake shore, my boy. Every time you chose wisely, the mist on the peak and on all those screens grew a little thinner.

#### `tuntas-wonosobo-03` · Mbah Kedu · pose `smile`
**ID:** Kamu sudah menemukan sendiri pesan terpenting dari Kedu, Le. Sebelum percaya, periksa sumbernya. Sebelum membagikan, pikirkan akibatnya. Kalau ragu, tanyakan kepada orang yang tepat, seperti guru, orang tua, atau petugas resmi.

**EN:** You have found Kedu's most important lesson by yourself, my boy. Before you believe, check the source. Before you share, think about what could happen. When in doubt, ask the right person, such as a teacher, a parent, or an official.

#### `tuntas-wonosobo-04` · Jaka · pose `happy`
**ID:** Serpihan terakhir dari Wonosobo... sudah kembali!

**EN:** The last shard from Wonosobo... is back!

#### `tuntas-wonosobo-05` · Mbah Kedu · pose `smile`
**ID:** Pustaka Wonosobo terbuka untukmu. Saat semuanya siap, kita pulang dan menyalakan Lentera Kedu bersama-sama.

**EN:** The Wonosobo Library is open to you. When everything is ready, we will go home and light the Lantern of Kedu together.

---

## 7. Penutup (`ending`)

Tampil setelah seluruh wilayah tuntas, sebelum Balai Refleksi. Selama studi diputar di P5, setelah tes akhir.

#### `penutup-01` · Narator · efek `glow`
**Judul:** Lentera Menyala Kembali / *The Lantern Shines Again*

**ID:** Di puncak bukit, Jaka mengangkat lenteranya tinggi-tinggi. Kelima belas serpihan menyatu, dan cahaya keemasan memancar ke seluruh tanah Kedu.

**EN:** On top of the hill, Jaka lifted his lantern high. All fifteen shards joined together, and a golden light poured out across all the lands of Kedu.

#### `penutup-02` · Narator · efek `fog-lift`
**Judul:** Kabut Tersibak / *The Mist Clears*

**ID:** Kabut Lupa tersibak dari Temanggung, Magelang, sampai Wonosobo. Papan petunjuk terbaca jelas, relief bercerita dengan urutan yang benar, dan layar-layar di rumah warga tidak lagi berkabut. Pesan berantai tidak lagi langsung dipercaya dan dibagikan begitu saja.

**EN:** The Mist of Forgetting cleared from Temanggung to Magelang to Wonosobo. Signposts could be read clearly, the reliefs told their stories in the right order, and the screens in people's homes were no longer misty. Chain messages were no longer believed and shared without checking.

#### `penutup-03` · Mbah Kedu · pose `smile` · efek `glow`
**Judul:** Mbah Kedu Pulih / *Mbah Kedu Recovers*

**ID:** Badan Mbah terasa segar lagi, Le! Tapi ingat, cahaya ini bukan hanya milik lentera. Cahaya ini hidup setiap kali seseorang membaca dengan teliti dan berbagi dengan bijak.

**EN:** I feel strong again, my boy! But remember, this light does not belong to the lantern alone. It lives every time someone reads with care and shares wisely.

#### `penutup-04` · Jaka · pose `bow`
**Judul:** Janji Jaka / *Jaka's Promise*

**ID:** Aku akan terus menjaga cahaya ini, Mbah: saat membaca, saat memakai layar, dan saat budaya Kedu tampil di layar. Supaya Kabut Lupa tidak pernah kembali.

**EN:** I will keep this light safe, Mbah: when I read, when I use a screen, and when Kedu's culture appears on a screen. So that the Mist of Forgetting never comes back.

#### `penutup-05` · Mbah Kedu · pose `smile`
**Judul:** Penjaga Lentera / *Keepers of the Lantern*

**ID:** Mulai hari ini, kamulah Penjaga Lentera, Le. Kamu juga, penjelajah. Bawalah cahaya ini ke sekolahmu, ke rumahmu, dan ke mana pun kamu pergi.

**EN:** From today, you are the Keeper of the Lantern, my boy. And so are you, explorer. Carry this light to your school, to your home, and wherever you go.

---

## 8. Narasi Peta Kedu setelah semua wilayah tuntas (`map_done`)

Menggantikan narasi `map_intro` di Peta Kedu begitu kelima belas tantangan tuntas, karena Kabut Lupa sudah hilang. Bila baris ini belum ada di basis data (server lama sebelum `gelita:story:sync`), Peta Kedu tetap memutar `map_intro`.

#### `peta-tuntas-01` · Jaka · pose `happy` · efek `glow`
**Judul:** Kabut Sudah Pergi / *The Mist Is Gone*

**ID:** Lihat! Kabut Lupa sudah hilang dari tanah Kedu. Temanggung, Magelang, dan Wonosobo bercahaya lagi berkat kerja kerasmu.

**EN:** Look! The Mist of Forgetting is gone from the lands of Kedu. Temanggung, Magelang, and Wonosobo shine again thanks to your hard work.

#### `peta-tuntas-02` · Jaka · pose `determined`
**Judul:** Cahaya yang Dijaga / *A Light to Keep*

**ID:** Kelima belas Serpihan Cahaya sudah kembali. Sekarang tugas kita menjaga cahayanya, saat membaca maupun saat memakai layar.

**EN:** All fifteen Shards of Light are back. Now our job is to keep the light shining, whether we are reading or using a screen.

#### `peta-tuntas-03` · Jaka · pose `happy`
**Judul:** Jelajahi Lagi / *Explore Again*

**ID:** Kamu boleh berkeliling lagi kapan saja: baca Pustaka Kedu, lihat lagi cerita penutup, atau ulangi tantangan. Jangan lupa mampir ke Balai Refleksi, ya. Mbah Kedu ingin mendengar ceritamu!

**EN:** You can look around again any time: read the Kedu Library, watch the closing story again, or replay a challenge. Don't forget to stop by the Reflection Hall. Mbah Kedu wants to hear your story!
