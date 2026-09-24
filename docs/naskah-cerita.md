# Naskah Cerita GELITA

Naskah lengkap seluruh narasi dan dialog permainan, dalam bahasa Indonesia dan Inggris. Naskah ini dipakai untuk dua hal:

1. **Rekaman audio.** Setiap baris punya nama berkas audio sendiri, jadi pengisi suara cukup membaca dari atas ke bawah.
2. **Isi tabel `dialogues`.** Tahap 2 (cerita pembuka) dan Tahap 3 (peta, kenali wilayah, dialog wilayah, wilayah tuntas, penutup) memindahkan teks di sini ke seeder dan perintah `gelita:story:update`. Bila teks perlu diubah, ubah dulu di berkas ini agar rekaman dan isi permainan tetap sama.

## Benang merah

Dataran Kedu dahulu diterangi **Cahaya Kedu**, cahaya yang lahir dari ilmu dan budaya dan disimpan di **Lentera Kedu**. Ketika orang mulai membaca dengan terburu-buru, memercayai kabar tanpa memeriksanya, dan melupakan cerita leluhur, naiklah **Kabut Lupa**. Kabut itu meretakkan lentera, dan cahayanya pecah menjadi **15 Serpihan Cahaya** yang jatuh di tiga wilayah. **Mbah Kedu**, penjaga lentera, ikut melemah setiap kali cahayanya meredup. **Jaka** menjadi Pembawa Lentera, dan pemain (disapa "penjelajah") berjalan bersamanya.

Setiap wilayah melawan satu wujud Kabut Lupa, sesuai keterampilan membaca yang dilatih di sana:

| Urutan | Wilayah | Keterampilan | Wujud kabut | Taruhan cerita |
|---:|---|---|---|---|
| 1 | Temanggung | Menemukan informasi yang tertulis jelas | Tulisan di papan petunjuk memudar, warga tersesat | Jaka belajar tidak terburu-buru |
| 2 | Magelang | Menghubungkan informasi, membedakan fakta dan pendapat | Urutan relief Borobudur teracak, warga bertengkar soal "katanya" | Mbah Kedu mulai melemah |
| 3 | Wonosobo | Menilai informasi dan sumbernya | Pesan berantai palsu tentang kawah membuat warga panik | Mbah tak kuat naik, Jaka harus berani sendirian |

Penutup: lentera menyala, Mbah Kedu pulih, dan Jaka bersama pemain menjadi Penjaga Lentera.

## Tokoh dan arahan suara

| Tokoh | Kode | Arahan suara |
|---|---|---|
| Narator | `narator` | Hangat dan tenang seperti pendongeng. Tempo sedang, memberi jeda pada kalimat penting. Tidak tampil sebagai gambar. |
| Jaka | `jaka` | Anak laki-laki sekitar 11 tahun. Bersemangat dan jujur. Suaranya naik saat senang atau takut, pelan saat menyesal. |
| Mbah Kedu | `mbah_kedu` | Kakek Jawa yang lembut dan bijak. Tempo pelan dengan logat Jawa halus. "Le" (dari *thole*, panggilan sayang untuk anak laki-laki) dibaca pendek. Suaranya lemah dan bergetar pada baris berpose `weak`. |

Di teks Inggris, Jaka tetap memanggil "Mbah" agar nuansa lokalnya terjaga, sementara papan nama di layar menulis "Grandpa Kedu". "Le" diterjemahkan menjadi "my boy".

## Konvensi berkas audio

- Folder: `public/assets/audio/narasi/id/` untuk bahasa Indonesia dan `public/assets/audio/narasi/en/` untuk bahasa Inggris.
- Nama berkas mengikuti kode di setiap judul baris di bawah, ditambah `.mp3`, misalnya `narasi/id/dialog-magelang-09.mp3`.
- Format yang disarankan: MP3 mono, 44,1 kHz, 64–96 kbps. Kenyaringan sekitar −16 LUFS, dengan jeda hening ±0,3 detik di awal dan akhir.
- Rekam suara saja, tanpa musik latar. Musik dan efek suara diputar terpisah oleh permainan.
- Satu berkas berisi satu baris. Jangan menggabungkan beberapa baris ke satu berkas, karena teks di layar maju mengikuti selesainya audio.
- Audio Inggris boleh menyusul. Bila berkasnya belum ada, layar tetap menampilkan teksnya dan pemain melanjutkan slide secara manual.

| Konteks (`context_code`) | Awalan berkas | Jumlah per bahasa | Tampil di |
|---|---|---:|---|
| `intro` | `intro-NN` | 9 | Cerita pembuka |
| `map_intro` | `peta-NN` | 3 | Kunjungan pertama ke Peta Kedu |
| `region_intro` | `kenal-{wilayah}-NN` | 12 | Ikon lentera "Kenali wilayah" di peta |
| `level_open` | `dialog-{wilayah}-NN` | 47 | Saat wilayah baru terbuka |
| `level_done` | `tuntas-{wilayah}-NN` | 12 | Saat wilayah tuntas |
| `ending` | `penutup-NN` | 5 | Setelah seluruh wilayah tuntas |
| **Total** | | **88** | Durasi rekaman sekitar 12–14 menit per bahasa |

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

**ID:** Dahulu kala, di antara Gunung Sindoro, Sumbing, Merapi, Merbabu, dan Perbukitan Menoreh, terbentang Dataran Kedu. Setiap malam, desa-desanya diterangi cahaya lembut yang disebut Cahaya Kedu.

**EN:** Long ago, surrounded by Mount Sindoro, Mount Sumbing, Mount Merapi, Mount Merbabu, and the Menoreh Hills, lay the Kedu Plain. Every night, its villages glowed with a gentle light called the Light of Kedu.

#### `intro-02` · Narator
**Judul:** Cahaya dari Ilmu dan Budaya / *A Light of Knowledge and Culture*

**ID:** Cahaya itu lahir dari ilmu dan budaya: dari dongeng para kakek dan nenek, dari relief Candi Borobudur, dari tembang para petani, dan dari setiap tulisan yang dibaca dengan teliti. Cahaya itu disimpan di dalam sebuah lentera tua bernama Lentera Kedu.

**EN:** That light was born from knowledge and culture: from grandparents' tales, from the reliefs of Borobudur Temple, from the songs of the farmers, and from every piece of writing read with care. It was kept inside an old lantern called the Lantern of Kedu.

#### `intro-03` · Narator · efek `fog`
**Judul:** Kabut Lupa / *The Mist of Forgetting*

**ID:** Namun perlahan, orang-orang mulai lupa. Mereka membaca dengan terburu-buru, memercayai kabar tanpa memeriksanya, dan meninggalkan cerita para leluhur. Dari kelupaan itu, naiklah kabut kelabu yang dingin: Kabut Lupa.

**EN:** But slowly, people began to forget. They read in a hurry, believed news without checking it, and left their ancestors' stories behind. From all that forgetting rose a cold grey mist: the Mist of Forgetting.

#### `intro-04` · Narator · efek `shake`
**Judul:** Malam Lentera Retak / *The Night the Lantern Cracked*

**ID:** Pada suatu malam, Kabut Lupa membelit Lentera Kedu. Kacanya retak, apinya bergetar, lalu cahayanya pecah! Lima belas Serpihan Cahaya melesat ke langit dan jatuh di Temanggung, Magelang, dan Wonosobo.

**EN:** One night, the Mist of Forgetting coiled around the Lantern of Kedu. Its glass cracked, its flame trembled, and then its light shattered! Fifteen Shards of Light shot into the sky and fell across Temanggung, Magelang, and Wonosobo.

#### `intro-05` · Mbah Kedu · pose `weak` · efek `dim`
**Judul:** Penjaga yang Menua / *The Ageing Keeper*

**ID:** Aku Mbah Kedu, penjaga lentera ini. Kakiku sudah terlalu tua untuk berjalan jauh, dan setiap kali cahayanya meredup, tubuhku ikut melemah. Aku butuh anak yang berani dan mau membaca dengan sungguh-sungguh.

**EN:** I am Mbah Kedu, which means Grandpa Kedu, the keeper of this lantern. My legs are too old to walk far, and each time its light fades, my body grows weaker too. I need a child who is brave and willing to read with all their heart.

#### `intro-06` · Jaka · pose `determined` · efek `glow`
**Judul:** Jaka, Pembawa Lentera / *Jaka, the Lantern Bearer*

**ID:** Biar aku yang pergi, Mbah! Aku akan mencari kelima belas serpihan itu satu per satu, sampai Lentera Kedu menyala lagi. Aku janji!

**EN:** Let me go, Mbah! I will find all fifteen shards, one by one, until the Lantern of Kedu shines again. I promise!

#### `intro-07` · Narator
**Judul:** Cara Mengumpulkan Serpihan / *How to Gather the Shards*

**ID:** Setiap wilayah menyimpan lima serpihan, dan setiap serpihan dijaga oleh satu tantangan. Selesaikan tantangannya untuk mendapatkan serpihan dan bintang. Lihat lentera di pojok kiri atas: lentera itu makin terang setiap kali serpihan kembali.

**EN:** Each region holds five shards, and each shard is guarded by a challenge. Complete the challenge to win its shard and stars. Watch the lantern in the top-left corner: it grows brighter every time a shard returns.

#### `intro-08` · Mbah Kedu · pose `smile`
**Judul:** Tiga Kunci Cahaya / *Three Keys of Light*

**ID:** Ingat tiga kunci ini, Le. Di Temanggung, temukan informasi yang tertulis jelas. Di Magelang, hubungkan informasi dan bedakan fakta dari pendapat. Di Wonosobo, nilailah setiap kabar sebelum kamu memercayainya.

**EN:** Remember these three keys, my boy. In Temanggung, find the information that is clearly written. In Magelang, connect information and tell facts from opinions. In Wonosobo, judge every piece of news before you believe it.

#### `intro-09` · Narator · efek `glow`
**Judul:** Perjalanan Dimulai / *The Journey Begins*

**ID:** Dan kamu, penjelajah, akan berjalan bersama Jaka. Bacalah dengan teliti, pikirkan baik-baik, dan putuskan dengan bijak. Kabut Lupa sudah menunggu. Mari nyalakan kembali Cahaya Kedu!

**EN:** And you, explorer, will walk beside Jaka. Read carefully, think it through, and decide wisely. The Mist of Forgetting is waiting. Let us bring back the Light of Kedu!

---

## 2. Narasi Peta Kedu (`map_intro`)

Diputar otomatis setelah loading "Membuka Peta Kedu". Pada kunjungan berikutnya, narasi ini tampil sebagai balon teks dengan tombol putar.

#### `peta-01` · Jaka · pose `idle` · efek `fog`
**Judul:** Dataran Kedu / *The Kedu Plain*

**ID:** Inilah Dataran Kedu. Dulu lembah ini menyimpan cahaya ilmu dan budaya. Sekarang lihatlah... Kabut Lupa menutupi hampir semuanya.

**EN:** This is the Kedu Plain. This valley once held the light of knowledge and culture. Now look... the Mist of Forgetting covers almost all of it.

#### `peta-02` · Jaka · pose `determined`
**Judul:** Tiga Wilayah / *Three Regions*

**ID:** Aku harus menjelajahi tiga wilayah untuk memulihkannya: Temanggung, lalu Magelang, dan terakhir Wonosobo. Di setiap wilayah, lima Serpihan Cahaya sedang menunggu.

**EN:** I must explore three regions to bring it back: Temanggung, then Magelang, and finally Wonosobo. In each region, five Shards of Light are waiting.

#### `peta-03` · Jaka · pose `happy` · efek `glow`
**Judul:** Kenali Dulu / *Get to Know Them First*

**ID:** Ketuk ikon lentera di setiap wilayah untuk mendengar cerita Mbah Kedu tentang tempat itu. Kalau sudah siap, ketuk wilayah yang lenteranya menyala. Ayo, kita berangkat!

**EN:** Tap the lantern icon on each region to hear Mbah Kedu's story about that place. When you are ready, tap the region whose lantern is lit. Come on, let's go!

---

## 3. Kenali wilayah (`region_intro`)

Mbah Kedu memperkenalkan geografi dan budaya tiap wilayah. Narasi ini dibuka dari ikon lentera di peta. Sifatnya pilihan, tetapi ikonnya berdenyut sampai didengar. Faktanya selaras dengan bank soal dan Pustaka (`docs/bank-soal/`).

### Temanggung

#### `kenal-temanggung-01` · Mbah Kedu · pose `smile`
**Judul:** Di Antara Dua Gunung / *Between Two Mountains*

**ID:** Inilah Temanggung, Le. Tanahnya diapit dua gunung, Sindoro dan Sumbing. Kalau pagi cerah, kedua puncaknya tampak berdiri berdampingan, seperti kakak dan adik yang saling menjaga.

**EN:** This is Temanggung, my boy. It lies between two mountains, Sindoro and Sumbing. On a clear morning, their peaks stand side by side, like an older and a younger sibling looking after each other.

#### `kenal-temanggung-02` · Mbah Kedu · pose `idle`
**Judul:** Tanah Para Petani / *Land of Farmers*

**ID:** Tanah di lereng gunung ini sangat subur. Para petani menanam tembakau, kopi, dan sayuran. Kopi dari lereng Sindoro dan Sumbing terkenal harum sampai ke luar daerah.

**EN:** The soil on these mountain slopes is very fertile. Farmers grow tobacco, coffee, and vegetables. Coffee from the slopes of Sindoro and Sumbing is famous for its aroma, even far beyond the region.

#### `kenal-temanggung-03` · Mbah Kedu · pose `idle`
**Judul:** Jejak Masa Lalu / *Traces of the Past*

**ID:** Temanggung juga menyimpan jejak masa lalu. Ada Situs Liyangan, permukiman kuno yang pernah tertimbun material letusan Gunung Sindoro. Ada pula Candi Pringapus, dan Prasasti Gondosuli yang dibuat pada tahun 832 Masehi.

**EN:** Temanggung also keeps traces of the past. There is the Liyangan Site, an ancient settlement once buried by material from an eruption of Mount Sindoro. There is also Pringapus Temple, and the Gondosuli Inscription, made in the year 832 CE.

#### `kenal-temanggung-04` · Mbah Kedu · pose `smile` · efek `glow`
**Judul:** Pesan Mbah / *Mbah's Advice*

**ID:** Saat ada perayaan, warga menari jaran kepang diiringi kendang dan gamelan. Di sini kamu akan belajar menemukan informasi yang tertulis jelas dari gambar, teks pendek, dan suara. Jawabannya sering ada di depan mata, asal kamu membacanya dengan teliti.

**EN:** At celebrations, people dance the jaran kepang, the hobby-horse dance, to the beat of drums and gamelan. Here you will learn to find information that is clearly stated in pictures, short texts, and sounds. The answer is often right in front of you, as long as you read carefully.

### Magelang

#### `kenal-magelang-01` · Mbah Kedu · pose `smile`
**Judul:** Tanah Candi Agung / *Land of the Great Temple*

**ID:** Magelang, Le, adalah rumah Candi Borobudur, candi Buddha terbesar di dunia. Candi itu dibangun lebih dari seribu tahun lalu, batu demi batu, tanpa mesin modern.

**EN:** Magelang, my boy, is home to Borobudur Temple, the largest Buddhist temple in the world. It was built more than a thousand years ago, stone by stone, without modern machines.

#### `kenal-magelang-02` · Mbah Kedu · pose `idle`
**Judul:** Buku dari Batu / *A Book Made of Stone*

**ID:** Dinding Borobudur dihiasi 2.672 panel relief. Setiap panel adalah bagian dari sebuah cerita. Membacanya seperti membaca buku raksasa dari batu: urutannya harus benar supaya ceritanya utuh.

**EN:** Borobudur's walls are decorated with 2,672 relief panels. Each panel is part of a story. Reading them is like reading a giant book made of stone: the order must be right for the story to make sense.

#### `kenal-magelang-03` · Mbah Kedu · pose `idle`
**Judul:** Tiga Candi Segaris / *Three Temples in a Line*

**ID:** Tak jauh dari Borobudur ada Candi Mendut dan Candi Pawon. Ketiganya berdiri hampir segaris. Di tengah kota ada Gunung Tidar, yang dalam cerita rakyat dijuluki paku tanah Jawa.

**EN:** Not far from Borobudur stand Mendut Temple and Pawon Temple. The three temples stand almost in a straight line. In the middle of the city is Mount Tidar, which folk tales call the nail of the land of Java.

#### `kenal-magelang-04` · Mbah Kedu · pose `smile` · efek `glow`
**Judul:** Pesan Mbah / *Mbah's Advice*

**ID:** Jangan lewatkan tari Topeng Ireng yang gagah, dengan hiasan kepala berbulu warna-warni. Di Magelang, satu informasi saja tidak cukup. Kamu harus menghubungkan beberapa informasi, lalu membedakan mana fakta dan mana pendapat.

**EN:** Don't miss the bold Topeng Ireng dance, with its colourful feathered headdresses. In Magelang, one piece of information is not enough. You must connect several pieces of information, then tell which are facts and which are opinions.

### Wonosobo

#### `kenal-wonosobo-01` · Mbah Kedu · pose `smile` · efek `fog`
**Judul:** Negeri di Atas Awan / *The Land Above the Clouds*

**ID:** Wonosobo adalah pintu menuju Dataran Tinggi Dieng, yang dijuluki negeri di atas awan. Letaknya sangat tinggi, sampai-sampai awan dan kabut sering lewat di antara rumah penduduk.

**EN:** Wonosobo is the gateway to the Dieng Plateau, known as the land above the clouds. It is so high up that clouds and mist often drift between people's houses.

#### `kenal-wonosobo-02` · Mbah Kedu · pose `idle`
**Judul:** Candi dan Telaga / *Temples and a Lake*

**ID:** Di Dieng berdiri kompleks Candi Arjuna, salah satu candi Hindu tertua di Jawa. Ada juga Telaga Warna. Airnya mengandung belerang, sehingga warnanya bisa tampak hijau, biru, atau kekuningan.

**EN:** On Dieng stands the Arjuna Temple complex, one of the oldest Hindu temples in Java. There is also Telaga Warna, the Lake of Colours. Its water contains sulphur, so it can look green, blue, or yellowish.

#### `kenal-wonosobo-03` · Mbah Kedu · pose `idle`
**Judul:** Hangatnya Budaya Pegunungan / *The Warmth of Mountain Culture*

**ID:** Udara di sini dingin sekali. Untuk menghangatkan badan, warga menyantap mi ongklok yang berkuah kental, lalu menikmati manisan carica, si pepaya gunung. Ada pula tari Lengger dan tradisi ruwatan anak berambut gimbal.

**EN:** The air here is very cold. To warm up, people eat mie ongklok, noodles in a thick sauce, and enjoy sweet carica, the mountain papaya. There is also the Lengger dance and the ruwatan ceremony for children with naturally matted hair.

#### `kenal-wonosobo-04` · Mbah Kedu · pose `worried` · efek `fog`
**Judul:** Pesan Mbah / *Mbah's Advice*

**ID:** Di sinilah Kabut Lupa paling tebal, Le. Kamu akan menemukan banyak kabar, dan tidak semuanya benar. Periksa sumbernya, timbang alasannya, lalu putuskan dengan bijak.

**EN:** This is where the Mist of Forgetting is thickest, my boy. You will come across a lot of news, and not all of it is true. Check the source, weigh the reasons, then decide wisely.

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
**ID:** Ada. Tapi kabut membuat tulisannya samar. Orang hanya melirik sekilas, lalu menebak arah. Satu tebakan keliru, dan mereka berputar-putar di ladang sampai senja.

**EN:** There is. But the mist makes the writing faint. People only glance at it, then guess the way. One wrong guess, and they wander in circles through the fields until dusk.

#### `dialog-temanggung-06` · Jaka · pose `determined` · efek `glow`
**ID:** Kalau begitu kita harus cepat, Mbah! Aku langsung cari serpihannya saja. Pasti ada yang bersinar di balik kabut!

**EN:** Then we have to hurry, Mbah! I'll just go straight for the shards. Something must be shining behind the mist!

#### `dialog-temanggung-07` · Jaka · pose `happy`
**ID:** Lihat, ada papan petunjuk! Panahnya ke kanan... Ayo, Mbah, lewat sini!

**EN:** Look, a signpost! The arrow points right... Come on, Mbah, this way!

#### `dialog-temanggung-08` · Mbah Kedu · pose `worried` · efek `shake`
**ID:** Tunggu, Le! Berhenti! Jangan hanya melihat panahnya. Baca dulu seluruh tulisannya.

**EN:** Wait, my boy! Stop! Don't just look at the arrow. Read all the words first.

#### `dialog-temanggung-09` · Jaka · pose `sad`
**ID:** "Jembatan rusak. Silakan lewat kiri." Aduh... Kalau tadi aku berlari ke kanan, kita bisa terperosok ke sungai.

**EN:** "Bridge broken. Please go left." Oh no... If I had run to the right, we could have fallen into the river.

#### `dialog-temanggung-10` · Mbah Kedu · pose `smile`
**ID:** Nah. Mata yang terburu-buru melewatkan jawaban yang tertulis jelas. Kabut Lupa paling senang pada anak yang tidak sabar.

**EN:** There, you see. Hurried eyes miss answers that are written clearly. The Mist of Forgetting loves an impatient child most of all.

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
**ID:** Jangan cepat merasa hebat, Le. Kabut di Magelang punya tipu daya yang lain.

**EN:** Don't feel too sure of yourself, my boy. The mist in Magelang has other tricks.

#### `dialog-magelang-03` · Jaka · pose `idle`
**ID:** Tipu daya apa, Mbah? Itu Candi Borobudur, kan? Megah sekali... Tapi kenapa relief-reliefnya tampak aneh?

**EN:** What tricks, Mbah? That's Borobudur Temple, isn't it? It's magnificent... But why do the reliefs look strange?

#### `dialog-magelang-04` · Mbah Kedu · pose `worried` · efek `fog`
**ID:** Kabut Lupa mengacak urutannya. Awal cerita ada di akhir, akhir cerita ada di tengah. Siapa pun yang membacanya sekarang hanya mendapat potongan-potongan yang membingungkan.

**EN:** The Mist of Forgetting has jumbled their order. The beginning of the story is at the end, and the end is in the middle. Anyone who reads them now only gets confusing bits and pieces.

#### `dialog-magelang-05` · Jaka · pose `afraid`
**ID:** Mbah, dengar... Di bawah sana ada dua bapak yang sedang bertengkar.

**EN:** Mbah, listen... Down there, two men are arguing.

#### `dialog-magelang-06` · Mbah Kedu · pose `worried`
**ID:** Yang satu berteriak, "Katanya candi ini dibangun dalam satu malam!" Yang lain membalas, "Katanya semua itu cuma dongeng!" Keduanya bersuara keras, tapi tak seorang pun mau memeriksa.

**EN:** One shouts, "They say this temple was built in a single night!" The other shouts back, "They say it's all just a fairy tale!" Both are loud, but neither of them wants to check.

#### `dialog-magelang-07` · Jaka · pose `idle`
**ID:** Jadi, mana yang benar, Mbah?

**EN:** So which one is right, Mbah?

#### `dialog-magelang-08` · Mbah Kedu · pose `smile`
**ID:** Itulah tugasmu, Le. Pendapat boleh berbeda, tapi fakta bisa dibuktikan. Hubungkan informasi yang satu dengan yang lain, maka kebenaran akan terlihat.

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
**ID:** Ingat pesan Mbah: temukan ide pokoknya, susun urutannya, dan pisahkan fakta dari pendapat. Lima serpihan Magelang menunggumu.

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
**ID:** Mbah, dengar! Ada kentongan dipukul. Orang-orang berlarian sambil memegang ponsel!

**EN:** Mbah, listen! Someone is beating the kentongan drum. People are running around holding their phones!

#### `dialog-wonosobo-04` · Mbah Kedu · pose `worried`
**ID:** Mereka menerima pesan berantai: "Kawah akan meledak besok pagi! Cepat mengungsi! Sebarkan ke semua orang!" Tak ada nama pengirim, tak ada sumber resmi.

**EN:** They have received a chain message: "The crater will erupt tomorrow morning! Evacuate now! Share this with everyone!" There is no sender's name and no official source.

#### `dialog-wonosobo-05` · Jaka · pose `afraid`
**ID:** Tapi bagaimana kalau pesan itu benar, Mbah? Kalau kita diam saja, orang bisa celaka!

**EN:** But what if the message is true, Mbah? If we do nothing, people could get hurt!

#### `dialog-wonosobo-06` · Mbah Kedu · pose `idle`
**ID:** Pertanyaan yang bagus, Le. Justru karena ini penting, kita harus memeriksanya. Kabar bohong paling mudah menyebar ketika orang sedang takut.

**EN:** That's a good question, my boy. It is exactly because this matters that we must check it. False news spreads most easily when people are afraid.

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
**ID:** Dengarkan pesan Mbah. Sebelum percaya, periksa sumbernya. Sebelum membagikan, pikirkan akibatnya. Kalau ragu, tanyakan kepada orang yang tepat.

**EN:** Listen to my advice. Before you believe, check the source. Before you share, think about what could happen. When in doubt, ask the right person.

#### `dialog-wonosobo-13` · Jaka · pose `idle`
**ID:** Periksa sumbernya... pikirkan akibatnya... tanyakan kepada orang yang tepat. Aku akan mengingatnya, Mbah.

**EN:** Check the source... think about what could happen... ask the right person. I will remember, Mbah.

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

## 5. Wilayah tuntas (`level_done`)

Tampil saat tantangan kelima sebuah wilayah selesai, bersamaan dengan momen "Pustaka {wilayah} terbuka!".

### Temanggung

#### `tuntas-temanggung-01` · Mbah Kedu · pose `smile` · efek `fog-lift`
**ID:** Lihat, Le! Kabut di Temanggung tersibak. Papan petunjuk bisa dibaca lagi, dan warga sudah menemukan jalan pulang.

**EN:** Look, my boy! The mist over Temanggung has parted. The signposts can be read again, and people have found their way home.

#### `tuntas-temanggung-02` · Jaka · pose `happy` · efek `glow`
**ID:** Lima serpihan sudah kembali ke lentera! Rasanya hangat sekali, Mbah.

**EN:** Five shards are back in the lantern! It feels so warm, Mbah.

#### `tuntas-temanggung-03` · Mbah Kedu · pose `smile`
**ID:** Itu buah ketelitianmu. Pustaka Temanggung kini terbuka untukmu. Bacalah sepuasnya.

**EN:** That is the reward for your care. The Temanggung Library is now open to you. Read as much as you like.

#### `tuntas-temanggung-04` · Jaka · pose `determined`
**ID:** Terima kasih, Mbah. Sekarang, ke Magelang!

**EN:** Thank you, Mbah. Now, on to Magelang!

### Magelang

#### `tuntas-magelang-01` · Mbah Kedu · pose `smile` · efek `fog-lift`
**ID:** Dengar, Le... Kedua bapak itu berhenti bertengkar. Sekarang mereka membaca relief bersama-sama, dari awal sampai akhir.

**EN:** Listen, my boy... Those two men have stopped arguing. Now they are reading the reliefs together, from beginning to end.

#### `tuntas-magelang-02` · Jaka · pose `happy`
**ID:** Dan Mbah... Mbah sudah bisa berdiri tegak lagi!

**EN:** And Mbah... you can stand up straight again!

#### `tuntas-magelang-03` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Sepuluh serpihan sudah kembali, dan tenaga Mbah ikut pulih. Pustaka Magelang kini terbuka untukmu.

**EN:** Ten shards have returned, and my strength is coming back. The Magelang Library is now open to you.

#### `tuntas-magelang-04` · Jaka · pose `determined`
**ID:** Tinggal satu wilayah lagi. Wonosobo, kami datang!

**EN:** Only one region left. Wonosobo, here we come!

### Wonosobo

#### `tuntas-wonosobo-01` · Jaka · pose `happy` · efek `fog-lift`
**ID:** Mbah! Aku berhasil! Warga sudah menanyakan kabar itu kepada petugas resmi. Kawahnya aman, tidak ada yang perlu mengungsi!

**EN:** Mbah! I did it! People asked the officials about the message. The crater is safe, and nobody needs to evacuate!

#### `tuntas-wonosobo-02` · Mbah Kedu · pose `smile` · efek `glow`
**ID:** Mbah melihatnya dari tepi telaga, Le. Setiap kali kamu memilih dengan bijak, kabut di puncak itu menipis sedikit demi sedikit.

**EN:** I saw it from the lake shore, my boy. Every time you chose wisely, the mist on that peak grew a little thinner.

#### `tuntas-wonosobo-03` · Jaka · pose `happy`
**ID:** Lima belas serpihan... semuanya sudah kembali!

**EN:** Fifteen shards... every single one is back!

#### `tuntas-wonosobo-04` · Mbah Kedu · pose `smile`
**ID:** Pustaka Wonosobo terbuka untukmu. Sekarang, mari kita pulang dan nyalakan Lentera Kedu bersama-sama.

**EN:** The Wonosobo Library is open to you. Now, let's go home and light the Lantern of Kedu together.

---

## 6. Penutup (`ending`)

Tampil setelah seluruh wilayah tuntas, sebelum Balai Refleksi.

#### `penutup-01` · Narator · efek `glow`
**Judul:** Lentera Menyala Kembali / *The Lantern Shines Again*

**ID:** Di puncak bukit, Jaka mengangkat lenteranya tinggi-tinggi. Kelima belas serpihan menyatu, dan cahaya keemasan memancar ke seluruh Dataran Kedu.

**EN:** On top of the hill, Jaka lifted his lantern high. All fifteen shards joined together, and a golden light poured out across the whole Kedu Plain.

#### `penutup-02` · Narator · efek `fog-lift`
**Judul:** Kabut Tersibak / *The Mist Clears*

**ID:** Kabut Lupa tersibak dari Temanggung, Magelang, sampai Wonosobo. Papan petunjuk terbaca jelas, relief bercerita dengan urutan yang benar, dan pesan berantai tak lagi ditelan mentah-mentah.

**EN:** The Mist of Forgetting cleared from Temanggung to Magelang to Wonosobo. Signposts could be read clearly, the reliefs told their stories in the right order, and chain messages were no longer swallowed whole.

#### `penutup-03` · Mbah Kedu · pose `smile` · efek `glow`
**Judul:** Mbah Kedu Pulih / *Mbah Kedu Recovers*

**ID:** Badan Mbah terasa segar lagi, Le! Tapi ingat, cahaya ini bukan hanya milik lentera. Cahaya ini hidup setiap kali seseorang membaca dengan teliti.

**EN:** I feel strong again, my boy! But remember, this light does not belong to the lantern alone. It lives every time someone reads with care.

#### `penutup-04` · Jaka · pose `bow`
**Judul:** Janji Jaka / *Jaka's Promise*

**ID:** Aku akan terus membaca dengan teliti, menghubungkan informasi, dan memeriksa sumbernya, Mbah. Supaya Kabut Lupa tidak pernah kembali.

**EN:** I will keep reading carefully, connecting information, and checking the source, Mbah. So that the Mist of Forgetting never comes back.

#### `penutup-05` · Mbah Kedu · pose `smile`
**Judul:** Penjaga Lentera / *Keepers of the Lantern*

**ID:** Mulai hari ini, kamulah Penjaga Lentera, Le. Kamu juga, penjelajah. Bawalah cahaya ini ke sekolahmu, ke rumahmu, dan ke mana pun kamu pergi.

**EN:** From today, you are the Keeper of the Lantern, my boy. And so are you, explorer. Carry this light to your school, to your home, and wherever you go.
