# Draf Terjemahan Bahasa Inggris Bank Soal Baru

Per 3 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Sumber: [08_DRAF_BANK_SOAL.md](08_DRAF_BANK_SOAL.md) (per 26 Oktober 2026, dengan perbaikan cek fakta 27 Oktober 2026 dari [25](25_CEK_FAKTA_BUDAYA.md)) · Status: **`disetujui pengembang`** (3 November 2026; didraf AI, usulan AI diterima pengembang apa adanya) · Semua butir tetap **`needs_verification`**

Dokumen ini berisi pasangan bahasa Inggris untuk setiap teks bank soal baru yang tampil di game: instruksi, bacaan, pernyataan, petunjuk, bagian unggahan, dan seluruh teks Skenario Digital. Dasarnya keputusan pengembang 2 November 2026 ([04](04_CATATAN_KEPUTUSAN.md); [31 → Keputusan pengembang](31_IMPLEMENTASI_BANK_SOAL_BARU.md#22-keputusan-pengembang) no. 8 dan 11): **AI mendraf terjemahan sekarang, pengembang memeriksanya, lalu butir yang direvisi dari validasi diterjemahkan ulang.**

Bahasa Inggris tidak dipakai validator. Validasi ahli, uji keterbacaan, dan cek fakta tetap memakai teks Indonesia di 08. Kalau 08 berubah, baris yang sama di sini ikut diterjemahkan ulang (lihat [Cara memperbarui](#cara-memperbarui)).

## Daftar isi

- [Cara membaca](#cara-membaca)
- [Aturan penerjemahan](#aturan-penerjemahan)
- [Istilah tetap](#istilah-tetap)
- [Temanggung (mudah)](#temanggung-mudah)
- [Magelang (sedang)](#magelang-sedang)
- [Wonosobo (sulit)](#wonosobo-sulit)
- [Catatan untuk diperiksa pengembang](#catatan-untuk-diperiksa-pengembang)
- [Cara memperbarui](#cara-memperbarui)
- [Dipakai di mana](#dipakai-di-mana)

---

## Cara membaca

- Urutan dan judul bagian sama dengan 08, jadi setiap butir dicari dengan kodenya (`wnb-3-a04`) di kedua dokumen. Teks Indonesia **tidak diulang** di sini; bandingkan dengan 08.
- Kunci, skor, kode kesalahan, kode alasan, Bloom, sub-aspek, dan level AKM **tidak berubah** dan tidak ditulis ulang, kecuali kunci Isi Rumpang (kata bahasa Inggrisnya perlu ditetapkan).
- Kunci Benar atau Salah (benar/salah) dan kunci bagian ketuk *Periksa Unggahan* (A2, C10, dan seterusnya) sama persis dengan 08.
- Butir babak 1 Cari Objek (`*-4-x01`–`x04`, PBK) memakai terjemahan bank lama di `docs/bank-soal/data/*.php`. Dua butir yang isinya diubah cek fakta 27 Oktober 2026 diterjemahkan ulang dan ditandai.
- Kartu misi dan instruksi Skenario Digital sudah punya pasangan bahasa Inggris di [12 → Kartu misi](12_CERITA_PENGHUBUNG_DAN_NARASI.md); di sini hanya dikutip supaya lengkap.
- Panjang kata bahasa Inggris dihitung kasar (± 5 kata), sama seperti 08.

## Aturan penerjemahan

1. **Ejaan Inggris Britania**, seperti bank lama dan berkas bahasa: *colour*, *metres*, *honoured*, *organiser*, *Mum*, *Dad*.
2. **Nama diri tetap**: nama orang, desa, kecamatan, candi, sanggar, warung, dan rumah gerabah fiktif, aplikasi *Kedugram*, nama akun (`@jalan2_bima`), dan alamat `.example` (termasuk jalurnya, misalnya `/masuk`). Sapaan di depan nama tetap, seperti di bank lama (*Pak Darto*): *Bu Sari*, *Pak Anton*, *Kak Dewi*, *Kak Laras*. Sapaan "Kak" tanpa nama di komentar (cara menyapa pengunggah) dibuang, karena tidak punya padanan.
3. **Istilah budaya tetap dalam bahasa Indonesia atau Jawa**, dengan penjelasan yang sama seperti di teks Indonesia: *kuda lumping*, *kuluk*, *stupa*, *getuk*, *kendil*, *bundengan*, *kowangan*, *embun upas*, *carica*. *Waisak* menjadi *Vesak*, *Masehi* menjadi *CE*, *Kartu Keluarga* menjadi *Family Card*.
4. **Kata ganti netral.** Teks Indonesia memakai "ia" dan "-nya" yang tidak menyebut jenis kelamin. Terjemahan menghindari *he*/*she* untuk tokoh yang tidak disebut jenis kelaminnya; nama tokoh diulang atau dipakai *they*. Kalimat jadi sedikit berulang, tetapi tidak menambah informasi yang tidak ada di teks asli. *Mum*/*Dad* dipakai karena teks asli menyebut ibu atau ayah.
5. **Angka dan jam** mengikuti format Inggris: *3,150 metres*, *2,672*, *Rp50,000*, *07:30*.
6. **Huruf besar dan simbol** dari teks Indonesia dipertahankan: *TRUE*/*FALSE* di instruksi (tombolnya *True*/*False*, `Game.verdictBenar`/`verdictSalah`), huruf kapital di komentar kasar (*WRONG!*, *NONSENSE!*), emoji, ✓, 💬, 📢, 🔍.
7. **Bahasa gaul komentar** diganti padanan yang sama ringannya: "Wkwk" → *LOL*, "nggak zaman" → *so uncool*, "jadul" → *old-fashioned*. Kata kasar tidak ditambah dan tidak dikurangi.
8. **Isi Rumpang**: setiap kata di bank kata punya tepat satu pasangan bahasa Inggris, dan tidak ada dua kata bahasa Inggris yang sama dalam satu variasi. `ErrorCodeResolver` memetakan kata Inggris ke kata Indonesia lewat pasangan ini untuk mencari kode kesalahan ([31 §10.2](31_IMPLEMENTASI_BANK_SOAL_BARU.md#102-di-mana-diturunkan)). Kalimat di sekitar rumpang ditulis supaya (a) kunci masuk secara tata bahasa, (b) pengecoh tetap menggoda dengan alasan yang sama seperti di 08, dan (c) tidak ada *a*/*an* sebelum rumpang yang membocorkan bunyi awal kunci.
9. **Skenario Digital**: umpan balik tidak memakai *wrong* atau *failed*; penjelasan setelah kesempatan habis juga tidak memakai *right*, *correct*, atau *wrong* (padanan aturan di 08 → *Cara membaca*). "Tepat." diterjemahkan *Good choice.*, "Pilihan yang aman." *A safe choice.*, "bagus" *well done*. Alasan ditulis sebagai kalimat utuh, sama seperti di teks Indonesia; kalimat tanya alasannya tetap `Js.scenarioReasonQuestion` (*The most important thing I'm thinking about here is …*, dari [32 §13](32_IMPLEMENTASI_SKENARIO_DIGITAL.md#13-teks-antarmuka)).
10. **Bahasa anak**: kalimat pendek, kata sehari-hari, *you*/*your* untuk "kamu"/"-mu", seperti teks Indonesia untuk siswa kelas 5.

## Istilah tetap

Dipakai sama di semua wilayah. Istilah antarmuka yang sudah ada di berkas bahasa atau dokumen implementasi diambil dari sana.

| Indonesia | English | Sumber |
| --- | --- | --- |
| Benar atau Salah | True or False | `Game.engine_boleh` |
| pilih BENAR atau SALAH | choose TRUE or FALSE | 12 (kartu misi `mgl-3`) |
| Babak 2: Periksa Unggahan | Round 2: Check the Post | 31 §17 |
| Skenario Digital | Digital Scenarios | 32 §13 |
| unggahan | post | 31 §17 |
| bagian (unggahan) | part (of the post) | 31 §17 |
| petunjuk | clue | glosarium (`clue`) |
| layar informasi | information screen | baru |
| papan informasi | information board | baru |
| kartu informasi, kartu info | info card | baru |
| keterangan unggahan | caption | baru |
| akun resmi | official account | baru |
| pengelola | managers | baru |
| pesan berantai | chain message | baru |
| diteruskan berkali-kali | forwarded many times | baru |
| grup kelas, grup keluarga | class group, family group | baru |
| suka (jumlah) | likes | baru |
| Bersponsor | Sponsored | baru |
| [Iklan] | [Ad] | baru |
| Unduh, Bagikan | Download, Share | baru |
| Kirim pesan ke sanggar / warung / rumah gerabah | Send a message to the studio / food stall / pottery workshop | baru |
| sanggar | studio (nama diri tetap: *Sanggar Kuda Pelangi*) | baru |
| tugas kelas / kelompok / sekolah | class / group / school assignment | baru |
| majalah dinding | class noticeboard | baru |
| langkah pertama yang tepat | the right first step | baru |
| menghargai | respect, show respect for | baru |
| orang tua | your parents (data: *your parent's phone number*) | baru |
| wali kelas | class teacher | baru |
| kata sandi | password | glosarium |
| akun belajar | learning account | baru |
| kabar bohong | false news | baru |
| tangkapan layar | screenshot | baru |

---

# Temanggung (mudah)

## `tmg-2` Isi Rumpang

**Instruction:** "Read the text. Tap an empty box, then tap the right word. Two of the words are not used."

### Variasi a: *Two Mountains of Temanggung*

> Temanggung Regency in Central Java lies between two volcanoes, Mount Sindoro and Mount Sumbing. People call them the twin mountains because they look alike. Mount Sindoro is about 3,150 metres high, while Mount **(1)** is about 3,370 metres high. Long ago, both mountains erupted and spread **(2)**. The ash made the soil on their slopes fertile. In this **(3)** soil, farmers grow arabica coffee. The coffee grows at a height of more than 900 metres. So the coffee gardens are in a **(4)** place.

± 80 kata

| Kode | Rumpang | Kunci (id) | Kunci (en) |
| --- | --- | --- | --- |
| tmg-2-a01 | (1) | Sumbing | Sumbing |
| tmg-2-a02 | (2) | abu | ash |
| tmg-2-a03 | (3) | subur | fertile |
| tmg-2-a04 | (4) | tinggi | high |

Pengecoh: *Sindoro* → *Sindoro*, *pasir* → *sand*.

### Variasi b: *A Spring on the Slopes of Sindoro*

> On the slopes of Mount Sindoro there is a spring called Umbul Jumprit. Its water is clear and never dries up, even in the **(1)** season. The dry season is the time of year when rain rarely falls. Water from Umbul **(2)** flows on to become the source of the Progo River. This spring is also important to Buddhists. Every Vesak, they take holy water from here. This **(3)** water is then carried to Borobudur Temple. Local people look after the trees around the spring, because tree roots help store water. If those trees are cut down, the spring could **(4)**.

± 85 kata

| Kode | Rumpang | Kunci (id) | Kunci (en) |
| --- | --- | --- | --- |
| tmg-2-b01 | (1) | kemarau | dry |
| tmg-2-b02 | (2) | Jumprit | Jumprit |
| tmg-2-b03 | (3) | suci | holy |
| tmg-2-b04 | (4) | kering | dry up |

Pengecoh: *hujan* → *rainy*, *penuh* → *overflow*. Lihat [catatan no. 1](#catatan-untuk-diperiksa-pengembang).

## `tmg-3` Benar atau Salah

**Instruction:** "Read the information screen, then choose TRUE or FALSE for each statement."

### Variasi a: *The Liyangan Site*

> **INFORMATION SCREEN · Temanggung Culture Exhibition**
> Put up by: Culture Exhibition Committee, Lereng Sindoro Primary School · Updated Saturday, 12 July
>
> The Liyangan Site is in Purbosari Village, Ngadirejo District, Temanggung. The site was found in 2008 while local people were digging for sand. Under the ground, researchers found the remains of wooden houses, a temple building, a stone road, and burnt grains of rice. All of them had been buried by sand and rocks from an eruption of Mount Sindoro long ago.

| Kode | Statement |
| --- | --- |
| tmg-3-a01 | The Liyangan Site was found in 2008. |
| tmg-3-a02 | The Liyangan Site was buried by sand and rocks from an eruption of Mount Merapi. |
| tmg-3-a03 | This screen does not say who put it up, so we do not know who to ask if something on it is not accurate. |
| tmg-3-a04 | [Picture: burnt grains of rice] This picture shows one of the things found at the Liyangan Site. |

### Variasi b: *Pringapus Temple and the Gondosuli Inscription*

> **INFORMATION SCREEN · Temanggung Culture Exhibition**
> Put up by: Culture Exhibition Committee, Lereng Sindoro Primary School · Updated Saturday, 12 July
>
> Pringapus Temple is a Hindu temple from the 9th century in Temanggung. Inside its chamber is a statue of Nandi, the bull honoured by Hindus. Temanggung also has the Gondosuli Inscription, which is thought to have been made around 832 CE. An inscription is writing on stone or metal that records an important event.

| Kode | Statement |
| --- | --- |
| tmg-3-b01 | Pringapus Temple is a Buddhist temple. |
| tmg-3-b02 | The Gondosuli Inscription is thought to have been made around 832 CE. |
| tmg-3-b04 | [Picture: a seated stone bull statue] This picture shows the statue inside the chamber of Pringapus Temple. |
| tmg-3-b03 | This screen was put up with no name on it, so there is no one to ask about what it says. |

### Kartu jangkar Temanggung (sama di variasi a dan b)

> **Class 5A Group** · Bu Sari (class teacher) · 07:15
> Children, tomorrow, Thursday, we are visiting the Culture Exhibition at the village hall. Meet at school at 07:30, OK? Bring a hat and your own water bottle. The exhibition closes at 11:00.

± 35 kata

| Kode | Statement |
| --- | --- |
| tmg-3-x01 | According to the message, students meet at the village hall at 07:30. |
| tmg-3-x02 | Students are asked to bring their own water bottle. |

## `tmg-4` Cari Objek

### Babak 1: Temukan Benda (PBK)

Dari bank lama (`temanggung.php`), tidak berubah.

| Kode | Clue |
| --- | --- |
| tmg-4-x01 | Find the woven bamboo horse used for the kuda lumping dance. |
| tmg-4-x02 | Find the coffee beans that grow on the slopes of Mount Sindoro and Mount Sumbing. |
| tmg-4-x03 | Find the statue of Nandi the bull, like the one inside Pringapus Temple. |
| tmg-4-x04 | Find the uceng fish, a small river fish that is a special food of Temanggung. |

Pengecoh (label bank lama): *Angklung*, *Ondel-ondel*, *Pinisi boat*, *Sasando*.

### Babak 2: Periksa Unggahan (D3)

**Instruction:** "This is a post about Temanggung's culture. Read the clue, then tap the right part of the post."

#### Variasi a: Unggahan foto kuda lumping

| No | Part | Text |
| --- | --- | --- |
| A1 | Uploader's account name | `@jalan2_bima` |
| A2 | Small text in the corner of the photo | "Photo: Sanggar Kuda Pelangi" |
| A3 | Caption | "Kuda lumping dance at the village field! The horses are made of teak wood 😍" |
| A4 | Exhibition information board | "Kuda lumping uses horses made of woven bamboo, painted in bright colours." |
| A5 | Comment 1 | **Nadia**: "Awesome! Thank you for sharing a photo of Temanggung's art 👏" |
| A6 | Comment 2 | **Doni**: "Ancient dance, so uncool! 😝" |
| A7 | Button | "Send a message to the studio" |
| A8 | Button | "Share" |

| Kode | Clue |
| --- | --- |
| tmg-4-a01 | This photo was not made by the account owner. Tap the text that shows who made the photo. |
| tmg-4-a02 | Read the information board. Tap the part of the post that does not match the board. |
| tmg-4-a03 | You want to put this photo on your class noticeboard. Tap the right first step. |
| tmg-4-a04 | Tap the comment that shows respect for this art. |

#### Variasi b: Unggahan video ikan uceng

| No | Part | Text |
| --- | --- | --- |
| B1 | Uploader's account name | `@sekar.kuliner` |
| B2 | Small text in the corner of the video | "Video: Warung Tepi Kali Bu Tini" |
| B3 | Caption | "Fried uceng, a sea fish from Temanggung. So crunchy! 😋" |
| B4 | Exhibition information board | "Uceng is a small river fish, a special food of Temanggung." |
| B5 | Comment 1 | **Raka**: "Wow, now I want to try it. Thanks for the info 🙏" |
| B6 | Comment 2 | **Tio**: "Ew, such tiny fish, gross! 🤢" |
| B7 | Button | "Send a message to the food stall" |
| B8 | Button | "Share" |

| Kode | Clue |
| --- | --- |
| tmg-4-b01 | This video was not made by the account owner. Tap the text that shows who made the video. |
| tmg-4-b02 | Read the information board. Tap the part of the post that does not match the board. |
| tmg-4-b03 | You want to use this video for a class assignment. Tap the right first step. |
| tmg-4-b04 | Tap the comment that shows respect for this special food. |

## `tmg-5` Skenario Digital

**Instruction** (dari 12): "Read the story. Choose what you would do, then choose your reason."

### Variasi a

**tmg-5-a01 · Personal data.** You find a quiz website with prizes. It says: "Fill in your full name, home address, and school name, and enter the draw to win a bicycle!" You don't know who made the website.

| Action | Feedback |
| --- | --- |
| Fill in your name, address, and school so you can enter the draw | Strangers could use your home address and school name to find you. Information like that is best kept to yourself. |
| Close the website and tell your parents | A safe choice. Your parents can help check whether the quiz is real. |
| Close the website and go on playing something else | You are safe now. It would be even better to tell your parents, so they know websites like that exist. |

**Explanation after tries run out:** A website with an unknown maker could use your address and school name. By closing the website and telling your parents, your data stays safe and your parents can check the quiz.

| Reason | Kode |
| --- | --- |
| Strangers could use my address and school | R-RISK |
| We're just not allowed to fill in anything online | R-ATUR |
| My friends have all entered that draw | R-SOS |

**tmg-5-a02 · Password.** You are playing a game on a tablet. Someone you don't know sends a message: "I can level you up fast. Send me your password!"

| Action | Feedback |
| --- | --- |
| Send your password so you level up fast | Anyone who knows your password can get into your account and use it. A password is only for the account owner. |
| Show the message to your parents, then report it | Good choice. Your password is safe, and a grown-up can help you report it. |
| Don't reply, then leave the game | Your password is safe. Tell your parents too, so the account can be reported. |

**Explanation after tries run out:** A password is only for the account owner. By showing the message to your parents and reporting it, your password stays safe and a grown-up can help stop that account.

| Reason | Kode |
| --- | --- |
| Someone who knows my password could use my account | R-RISK |
| My teacher says passwords must not be given away | R-ATUR |
| I don't feel like playing games today | R-ACAK |

**tmg-5-a03 · Permission to share your own photo.** You have a photo of yourself dancing kuda lumping. You want to post it on your mum's social media account. Your mum is cooking in the kitchen.

| Action | Feedback |
| --- | --- |
| Post it yourself with Mum's phone and tell her later | That account belongs to your mum. Before posting anything on someone else's account, we need the owner's permission. |
| Ask your older brother or sister first whether it can be posted | Asking is good. But the account belongs to your mum, so the permission has to come from her. |
| Wait until Mum finishes cooking, then ask her | Good choice. Mum can decide, and she can help choose who is allowed to see the photo. |

**Explanation after tries run out:** That account belongs to your mum, so she is the one who decides. By waiting and then asking her, you ask the account owner for permission.

| Reason | Kode |
| --- | --- |
| Mum owns the account, so Mum decides | R-ETIK |
| If I get caught without asking, I'll be told off | R-ATUR |
| So my friends can quickly see me dance | R-SOS |

**tmg-5-a04 · False news.** A message appears in the family group: "Starting tomorrow, all schools in Temanggung are closed for a week! Share this with every group!" The message does not say who made it.

| Action | Feedback |
| --- | --- |
| Forward it to the class group so your friends know | News without a source isn't always true. If you forward it, many friends could get confused too. |
| First ask your class teacher whether it is true | Good choice. Teachers and parents can check with a clear source, such as a school announcement. |
| Don't forward it and don't ask anyone | You didn't help spread it, well done. Asking a teacher or your parents would help even more. |

**Explanation after tries run out:** News with no maker isn't always true. By asking your class teacher first, you learn the truth from a clear source before anyone else gets confused.

| Reason | Kode |
| --- | --- |
| News without a source isn't always true | R-RISK |
| Dad doesn't let me reply to messages in groups | R-ATUR |
| So my friends hear it from me first | R-SOS |

### Variasi b

**tmg-5-b01 · Personal data.** You want to enter an online drawing competition. The form asks for your name, class, your parent's phone number, and your full home address. The page does not show the organiser's name.

| Action | Feedback |
| --- | --- |
| Fill in everything so you can enter right away | Your parent's phone number and your home address are personal data. Don't give them to an organiser you don't know. |
| Stop filling it in and ask your parents first | A safe choice. Your parents can find out who the organiser is. |
| Stop filling it in, then look for another competition | Your data is safe. It would be better if your parents knew too. |

**Explanation after tries run out:** The organiser of this competition is unknown, yet it asks for your parent's phone number and your home address. By stopping and asking your parents, your data stays safe and they can find out who the organiser is.

| Reason | Kode |
| --- | --- |
| Strangers could use personal data | R-RISK |
| We're just not allowed to fill in any forms | R-ATUR |
| I don't really like drawing anyway | R-ACAK |

**tmg-5-b02 · Password.** In a game chat room, an account you don't know writes: "Hi, I can give you free coins. Write your password here!"

| Action | Feedback |
| --- | --- |
| Write your password to get free coins | A password written in a chat room can be read by others. Your account could be taken over. |
| Show your parents, then report the account | Good choice. Your account is safe and that account can be reported. |
| Don't answer, then leave the chat room | Your account is safe. Tell your parents too, OK? |

**Explanation after tries run out:** A password written in a chat room can be read by others. By showing your parents and reporting the account, your account stays safe and that account can be stopped.

| Reason | Kode |
| --- | --- |
| Someone who knows my password could take my account | R-RISK |
| My teacher says passwords must be kept secret | R-ATUR |
| My friends don't write their passwords either | R-SOS |

**tmg-5-b03 · Permission to share your own photo.** You have a video of yourself learning to dance. You want to post it on your dad's social media account. Your dad is at the coffee farm.

| Action | Feedback |
| --- | --- |
| Post it yourself with Dad's phone and tell him later | That account belongs to your dad. You need the owner's permission before posting. |
| Ask a friend first whether it can be posted | Asking is good. But the one who can give permission is the account owner, your dad. |
| Wait until Dad comes home, then ask him | Good choice. Dad can decide and help set who is allowed to watch. |

**Explanation after tries run out:** That account belongs to your dad, so he is the one who can give permission. By waiting for him to come home and then asking, you ask the account owner for permission.

| Reason | Kode |
| --- | --- |
| Dad owns the account, so Dad decides | R-ETIK |
| If I get caught without asking, I'll be told off | R-ATUR |
| So lots of people will like my dance video | R-SOS |

**tmg-5-b04 · False news.** A message appears in the class group: "Drinking coffee makes children smarter! Share this with all your friends!" The message does not say who made it.

| Action | Feedback |
| --- | --- |
| Forward it to other groups so many people know | News without a source isn't always true. Coffee contains caffeine and is not a drink for children. |
| First ask your teacher whether it is true | Good choice. Teachers and parents can help find a source you can trust. |
| Don't forward it and don't ask anyone | You didn't help spread it, well done. Asking would help your friends more. |

**Explanation after tries run out:** A message with no maker isn't always true. By asking your teacher first, you avoid spreading news that isn't certain.

| Reason | Kode |
| --- | --- |
| News without a source isn't always true | R-RISK |
| Mum doesn't let me open messages in the class group | R-ATUR |
| I just don't like drinking coffee | R-ACAK |

---

# Magelang (sedang)

## `mgl-2` Isi Rumpang

**Instruction:** "Read the text. Tap an empty box, then tap the right word. Two of the words are not used."

### Variasi a: *Borobudur Temple*

> Borobudur Temple stands in Magelang Regency. This Buddhist temple was built in the 8th and 9th centuries. At the bottom there are six square terraces, and above them there are three round terraces. So Borobudur has **(1)** terraces. On the round terraces stand 72 stupas with holes. A stupa is a bell-shaped building. At the top of the temple there is one bell-shaped building that is the biggest. That building is called the main **(2)**. Inside each stupa with holes sits a statue of the Buddha. So on the round terraces there are **(3)** Buddha statues inside stupas. The temple walls are decorated with 2,672 relief panels, which are carved pictures. Of these, 1,460 panels tell stories, while 1,212 panels are only decoration. So there are **(4)** story panels than decoration panels. The temple stones can be damaged if people step on them or climb them. That is why visitors are **(5)** to climb the stupas.

± 135 kata

| Kode | Rumpang | Kunci (id) | Kunci (en) |
| --- | --- | --- | --- |
| mgl-2-a01 | (1) | sembilan | nine |
| mgl-2-a02 | (2) | stupa | stupa |
| mgl-2-a03 | (3) | 72 | 72 |
| mgl-2-a04 | (4) | banyak | more |
| mgl-2-a05 | (5) | dilarang | forbidden |

Pengecoh: *enam* → *six*, *sedikit* → *fewer*.

### Variasi b: *The Topeng Ireng Dance*

> Topeng Ireng is a folk dance from Magelang Regency. The dance began to grow around the 1950s in Tuksongo Village, Borobudur District. The dancers wear a kuluk, a colourful headdress. It is made of many feathers. When a dancer jumps, the feathers on the **(1)** sway too. Small bells are tied around the dancers' ankles. Every time the feet stamp, the bells **(2)**. One group can have many dancers who move as one. If one dancer moves faster than the others, their dance is no longer **(3)**. It is said that the dance was born from the story of village people who once fought to drive out the colonisers. That is why its moves are firm and full of spirit, like a line of **(4)** ready for battle. At a show on the village field, the organisers put up a rope barrier because the dancers jump in every direction. The audience sitting behind the rope stays **(5)** from the dancers' leaps.

± 135 kata

| Kode | Rumpang | Kunci (id) | Kunci (en) |
| --- | --- | --- | --- |
| mgl-2-b01 | (1) | kuluk | kuluk |
| mgl-2-b02 | (2) | berbunyi | ring |
| mgl-2-b03 | (3) | kompak | together |
| mgl-2-b04 | (4) | prajurit | soldiers |
| mgl-2-b05 | (5) | aman | safe |

Pengecoh: *diam* → *stop*, *penjajah* → *colonisers*. Lihat [catatan no. 2](#catatan-untuk-diperiksa-pengembang).

## `mgl-3` Benar atau Salah

**Instruction:** "Read the post and its comments, then choose TRUE or FALSE for each statement."

### Variasi a: *Looking After Borobudur Temple*

> **@pengelola.candi** ✓ official account · 5 May
>
> Borobudur Temple is the largest Buddhist temple in the world. It was built in the 8th and 9th centuries without cement; its stones were fitted together so that they lock. Its walls are decorated with 2,672 relief panels. Of these, 1,460 panels tell stories, while the rest are decoration. In 1991, UNESCO named Borobudur a World Heritage Site.
>
> Let's look after Borobudur together. Follow the routes and rules from the temple managers. Do not climb the stupas and do not touch the reliefs, because being touched by hands again and again can wear the stone down and damage it. Do not draw on the walls and do not drop litter.
>
> 💬 **Dimas:** Borobudur is the most beautiful temple in the world! 😍
> 💬 **Bu Rina:** Borobudur was named a World Heritage Site in 1991.
> 💬 **Nanda:** I heard it's fine to touch the reliefs, as long as you do it gently.

± 135 kata

| Kode | Statement |
| --- | --- |
| mgl-3-a01 | The second paragraph of the post mainly explains how to look after Borobudur Temple. |
| mgl-3-a02 | According to the post, all of Borobudur's reliefs tell stories. |
| mgl-3-a03 | Visitors may touch the reliefs as long as their hands have been washed clean. |
| mgl-3-a04 | Dimas's comment is an opinion. |
| mgl-3-a05 | Bu Rina's comment is an opinion. |
| mgl-3-a06 | On whether the reliefs may be touched, the official account's post is more trustworthy than Nanda's comment. |

### Variasi b: *Mount Tidar*

> **@infowisata.kotamagelang** ✓ official account · 11 April
>
> In the middle of Magelang City there is a green hill called Mount Tidar. It is about 503 metres above sea level. Many people climb its stairs to exercise. Climbers are asked to stay on the stairway path. In folk tales, Mount Tidar is called the nail of the island of Java. It is said that Java once wobbled and shook, and was then nailed down at Mount Tidar to keep it still. A story like this is called a legend, not a scientific explanation.
>
> In the valley of Mount Tidar, the Military Academy has stood since 1957. Magelang City celebrates its birthday every 11 April. The date was taken from the Mantyasih Inscription, which was made in 907 CE.
>
> 💬 **Kak Dewi:** The view from Mount Tidar is most beautiful in the morning! 🌄
> 💬 **Pak Anton:** The Mantyasih Inscription was made in 907 CE.
> 💬 **Raffi:** I heard you can take a shortcut through the forest to get to the top faster.

± 140 kata

| Kode | Statement |
| --- | --- |
| mgl-3-b01 | The first paragraph of the post is mainly about Mount Tidar. |
| mgl-3-b02 | The Mantyasih Inscription was made after the Military Academy was built. |
| mgl-3-b03 | According to the post, everything it says about Mount Tidar is a legend. |
| mgl-3-b04 | Kak Dewi's comment is a fact. |
| mgl-3-b05 | Pak Anton's comment is a fact. |
| mgl-3-b06 | On the path for climbing Mount Tidar, the official account's post is more trustworthy than Raffi's comment. |

### Kartu jangkar Magelang (sama di variasi a dan b)

> 🔍 **where the Topeng Ireng dance comes from**
>
> **[Ad]** Cheap Dance Costume Hire! · `kostum-tari.example`
> Full Topeng Ireng costumes from Rp50,000. Order now!
>
> **Rani's Travel Notes** · `rani-jalan.example`
> I watched Topeng Ireng yesterday, it was so much fun! I heard this dance is from Bali, right?
>
> **Central Java Culture Encyclopaedia** · `budaya-jateng.example`
> Topeng Ireng is a folk dance that grew around the 1950s in Tuksongo Village, Borobudur District, Magelang.

± 60 kata

| Kode | Statement |
| --- | --- |
| mgl-3-x01 | Of the three search results, only one clearly answers where Topeng Ireng comes from. |
| mgl-3-x02 | The first result is the most trustworthy because it is at the very top. |

## `mgl-4` Cari Objek

### Babak 1: Temukan Benda (PBK)

Dari bank lama (`magelang.php`); `mgl-4-x03` diterjemahkan ulang.

| Kode | Clue |
| --- | --- |
| mgl-4-x01 | Find the small bell-shaped structure full of holes. Borobudur has 72 of these on its round terraces. |
| mgl-4-x02 | Find the colourful feather headdress worn by dancers of the folk dance from Tuksongo Village, Magelang. |
| mgl-4-x03 | Find the snack made of cassava that is cooked, pounded, and sweetened. This snack gave Magelang a special nickname. **Diterjemahkan ulang:** bank lama *steamed*; 08 kini "dimasak" (cek fakta MGL-35 di [25](25_CEK_FAKTA_BUDAYA.md): getuk dikukus atau direbus). |
| mgl-4-x04 | Find the object made of clay that is shaped and then fired, made by the people of Klipoh Hamlet. |

Pengecoh (label bank lama): *Bamboo hobby horse*, *Sweet carica in a jar*, *Reog dadak merak*, *Angklung*, *Bakpia*, *Metal pot*.

### Babak 2: Periksa Unggahan (D3)

**Instruction:** "This is a post about Magelang's culture. Read the clue carefully, then tap the right part of the post."

#### Variasi a: Unggahan video Topeng Ireng

| No | Part | Text |
| --- | --- | --- |
| C1 | Uploader's account name | `@vlog.arka` |
| C2 | Small text in the corner of the video | "Video: Sanggar Cahya Kuluk" |
| C3 | Caption | "Topeng Ireng dance, an original dance from Temanggung! 🔥" |
| C4 | Info card | "Topeng Ireng: a folk dance that grew around the 1950s in Tuksongo Village, Borobudur District, Magelang." |
| C5 | Number of likes | "540 likes" |
| C6 | Comment 1 | **Salsa**: "It's not from Temanggung. The info card says it's from Tuksongo, Magelang 🙏" |
| C7 | Comment 2 | **Bayu**: "Haha, the costume looks like a rooster 🐔" |
| C8 | Comment 3 | **Lina**: "Cool! Temanggung really is great 👍" |
| C9 | Button | "Download" |
| C10 | Button | "Send a message to the studio" |

| Kode | Clue |
| --- | --- |
| mgl-4-a01 | Tap the text that shows who made this video. |
| mgl-4-a02 | Compare the video caption with the info card. Tap the comment that corrects the caption accurately and politely. |
| mgl-4-a03 | You want to use this video for a group assignment. Tap the right first step. |
| mgl-4-a04 | Tap the comment that does not show respect for the dancers. |

#### Variasi b: Unggahan foto gerabah Klipoh

| No | Part | Text |
| --- | --- | --- |
| D1 | Uploader's account name | `@dapur.nayla` |
| D2 | Small text in the corner of the photo | "Photo: Rumah Gerabah Sekar Lempung" |
| D3 | Caption | "Beautiful pottery from Klipoh Hamlet, Wonosobo! 🏺" |
| D4 | Info card | "Klipoh Hamlet is about 3 km from Borobudur Temple, Magelang. The people there make kendil pots, cobek mortars, and kuali pans from clay." |
| D5 | Number of likes | "610 likes" |
| D6 | Comment 1 | **Tegar**: "Klipoh is in Magelang, near Borobudur. It's on the info card 🙏" |
| D7 | Comment 2 | **Rani**: "LOL, old-fashioned kendil, who still uses those 😂" |
| D8 | Comment 3 | **Ayu**: "Pretty! Wonosobo really is cool 👍" |
| D9 | Button | "Download" |
| D10 | Button | "Send a message to the pottery workshop" |

| Kode | Clue |
| --- | --- |
| mgl-4-b01 | Tap the text that shows who made this photo. |
| mgl-4-b02 | Compare the photo caption with the info card. Tap the comment that corrects the caption accurately and politely. |
| mgl-4-b03 | You want to use this photo for a group assignment. Tap the right first step. |
| mgl-4-b04 | Tap the comment that does not show respect for this craft. |

## `mgl-5` Skenario Digital

**Instruction** (dari 12): "Read the story. Choose what you would do, then choose your reason."

### Variasi a

**mgl-5-a01 · Personal data.** You try a quiz called "Which Wayang Character Are You?" in an app. Before showing the result, the app asks for your date of birth, your mum's full name, and the name of your school.

| Action | Feedback |
| --- | --- |
| Fill in everything so the quiz result comes out fast | Dates of birth and mothers' names are often used to open accounts. That data could be misused. |
| Fill in only your date of birth and leave the rest empty | Your date of birth is personal data too. A quiz for fun doesn't need data like that. |
| Stop, then ask your parents whether it's safe | Good choice. A quiz that asks for personal data should make us suspicious, and your parents can help check it. |
| Stop playing without telling anyone | Your data is safe. Telling your parents would help if the app shows up again. |

**Explanation after tries run out:** This quiz asks for your date of birth and your mum's full name, even though a quiz does not need that data. By stopping and asking your parents, your data stays safe.

| Reason | Kode |
| --- | --- |
| People could use that data to break into accounts | R-RISK |
| Mum doesn't let me play quizzes in any app | R-ATUR |
| My friends have already shared their quiz results | R-SOS |
| I don't really like wayang stories anyway | R-ACAK |

**mgl-5-a02 · Password.** Your best friend, Laras, says, "Lend me your game account, please. Tell me the password, I just want to look around."

| Action | Feedback |
| --- | --- |
| Tell Laras the password, because Laras is your best friend | Even with a best friend, a password is only for the account owner. A password that has been shared can spread by accident. |
| Tell Laras now, and change the password later | Until it is changed, someone else can use your account. It's safer not to share it at all. |
| Say no kindly, then ask Laras to play together | Good choice. Your account is safe and your friendship stays strong. |
| Say no without explaining anything to Laras | Not giving your password is the right thing to do. Explaining why in a kind way helps Laras understand and not take it the wrong way. |

**Explanation after tries run out:** A password is only for the account owner, even when your own best friend asks. By saying no kindly and asking Laras to play together, your account is safe and you stay friends.

| Reason | Kode |
| --- | --- |
| A password can spread even by accident | R-RISK |
| My teacher says passwords must be kept secret | R-ATUR |
| Laras will be angry if I don't say | R-SOS |
| My account level is still very low anyway | R-ACAK |

**mgl-5-a03 · Permission to share a friend's photo.** At a school dance competition, you take a photo of Dimas slipping and landing sitting down on the stage. The photo is funny. You want to send it to the class group.

| Action | Feedback |
| --- | --- |
| Send it to the class group so everyone can laugh | The photo is about Dimas. Sharing it without permission could embarrass Dimas. |
| Show it to your friends while laughing | Laughing at a friend who fell can hurt their feelings, even if the photo is not sent. |
| Ask Dimas first whether it can be shared | Good choice. Photos of other people are only shared if they agree. |
| Keep the photo on your phone without asking Dimas | You didn't share it, well done. It would be even better to ask Dimas, because it's a photo of Dimas. |

**Explanation after tries run out:** Dimas might be embarrassed if the photo spreads. By asking Dimas first, you only share the photo if Dimas agrees.

| Reason | Kode |
| --- | --- |
| Dimas might be embarrassed if the photo is shared | R-ETIK |
| There's a school rule against taking photos | R-ATUR |
| My friends will surely like seeing a funny photo | R-SOS |
| My phone's memory is almost full | R-ACAK |

**mgl-5-a04 · Chain message.** In the class group, a friend forwards a message: "Info from my mum's friend: entry tickets to Borobudur Temple are free for primary school children all this month! Share it!" The message does not name any organisation or official source.

| Action | Feedback |
| --- | --- |
| Forward it to the family group so everyone gets the chance | This news has no clear source. If it turns out to be untrue, many families could be disappointed. |
| Ask your friends to go there straight away tomorrow | It's better to check the news first before using it to make plans. |
| First check the temple's official website with your parents | Good choice. An official source can confirm whether the news is true. |
| Don't forward it and don't respond at all | You didn't help spread it, well done. Checking an official source would help your friends too. |

**Explanation after tries run out:** The message does not name an official source. By checking the temple's official website with your parents, you find out whether it is true before passing it on.

| Reason | Kode |
| --- | --- |
| News with no clear source isn't always true | R-RISK |
| Dad doesn't let me open messages in the class group | R-ATUR |
| So people think I know lots of things | R-SOS |
| I've already been to Borobudur | R-ACAK |

### Variasi b

**mgl-5-b01 · Personal data.** There is a video competition called "Special Snacks of Magelang". Entrants must say their full name and home address at the start of the video. All the videos will be posted on an account that anyone can see.

| Action | Feedback |
| --- | --- |
| Say your full name and home address, as the rules say | A video on a public account can be watched by anyone. It's best not to say your home address. |
| Say only your full name and the name of your school | Your full name and school can also be used by strangers to find you. |
| Ask your parents; say only your nickname | Good choice. You can still enter the competition without giving away personal data. |
| Decide not to enter, without telling anyone | Your data is safe. Your parents might be able to help you enter in a safe way. |

**Explanation after tries run out:** Anyone can watch that video, so strangers could learn your full name and home address. By asking your parents and saying only your nickname, you can still enter the competition safely.

| Reason | Kode |
| --- | --- |
| Strangers could find out my home address | R-RISK |
| We're just not allowed to enter online competitions | R-ATUR |
| Other entrants say their home address too | R-SOS |
| I like getuk more than other snacks | R-ACAK |

**mgl-5-b02 · Password.** Arif, a friend in your group, says, "Tell me the password for your learning account. Then I can do our group assignment from my house."

| Action | Feedback |
| --- | --- |
| Tell Arif the password so the assignment gets done fast | Your learning account holds your data and marks. A password is only for the account owner. |
| Tell Arif now, and change the password later | Until it is changed, someone else can use your account. It's safer not to share it. |
| Say no kindly, then ask Arif to work together | Good choice. Your account is safe and the group assignment keeps going. |
| Say no without explaining anything to Arif | Not giving your password is the right thing to do. Explaining why in a kind way helps Arif understand, and the assignment keeps going. |

**Explanation after tries run out:** A password is only for the account owner, even when a groupmate asks. By saying no kindly and asking Arif to work together, your account is safe and the assignment still gets done.

| Reason | Kode |
| --- | --- |
| A password can spread even by accident | R-RISK |
| My teacher says passwords must be kept secret | R-ATUR |
| Arif won't want to be my friend anymore | R-SOS |
| This group assignment is really hard | R-ACAK |

**mgl-5-b03 · Permission to share a friend's photo.** You take a photo of your friends at Topeng Ireng practice. In the photo, Sekar is not wearing the kuluk yet and Sekar's hair is messy. You want to post the photo on the class account.

| Action | Feedback |
| --- | --- |
| Post it on the class account because the photo is lively | Sekar is in that photo. Sekar needs to be asked before it is posted. |
| Send it to a few friends and make fun of Sekar's hair | Making fun of how a friend looks can hurt their feelings. |
| Ask Sekar first, or choose another photo | Good choice. Everyone in a photo has the right to say yes or no. |
| Keep the photo on your phone without asking Sekar | You didn't share it, well done. Asking Sekar would be even better. |

**Explanation after tries run out:** Sekar might not want lots of people to see the photo like that. By asking Sekar first or choosing another photo, you respect Sekar's feelings.

| Reason | Kode |
| --- | --- |
| Sekar might be embarrassed if the photo is posted | R-ETIK |
| There's a school rule against posting photos | R-ATUR |
| The class account gets busy when there are funny photos | R-SOS |
| My phone camera is new and takes good pictures | R-ACAK |

**mgl-5-b04 · Chain message.** A message appears in the class group: "Info from my uncle's friend: the getuk sold at the market this week gives you a stomach ache! Don't buy it, share this!" The message gives no source or proof.

| Action | Feedback |
| --- | --- |
| Forward it to the family group so everyone is careful | News without proof can harm innocent getuk sellers. |
| Post "Don't buy getuk!" on social media | Before spreading a warning, the news needs to be checked first. |
| First check trusted news with your parents | Good choice. Checking a clear source stops false news from spreading. |
| Don't forward it and don't respond at all | You didn't help spread it, well done. Checking and telling your friends would help more. |

**Explanation after tries run out:** The message gives no source or proof. By checking trusted news with your parents, you avoid spreading news that isn't certain.

| Reason | Kode |
| --- | --- |
| News without proof can harm the sellers | R-RISK |
| Mum doesn't let me open messages in the class group | R-ATUR |
| So I'm the first to tell my friends | R-SOS |
| I very rarely go to the market | R-ACAK |

---

# Wonosobo (sulit)

## `wnb-2` Isi Rumpang

**Instruction:** "Read the article. Tap an empty box, then tap the right word. Two of the words are not used."

### Variasi a: *The Land Above the Clouds*

> 🌐 `dieng-wisata.example` › Articles
> **The Land Above the Clouds**
> Written by the Editorial Team · 3 August
>
> Part of the Dieng Plateau is in Wonosobo Regency. The plateau lies more than 2,000 metres above sea level. Dieng is often covered in mist, **(1)** it is called the land above the clouds. In the dry season, the morning dew can freeze into ice **(2)** the temperature near the ground drops below 0 °C. Visitors who come in that season should bring warm clothes.
>
> Dieng is also a good place for carica, a mountain papaya. Carica grows best at 1,400–2,400 metres, **(3)** it struggles to grow in hot lowlands. Sembungan Village is about 2,300 metres high, so carica grows **(4)** in that village. Coastal areas are hot lowlands, so carica grows **(5)** there. From all this information, we know that carica needs **(6)** air.

± 130 kata

| Kode | Rumpang | Kunci (id) | Kunci (en) |
| --- | --- | --- | --- |
| wnb-2-a01 | (1) | sehingga | so |
| wnb-2-a02 | (2) | karena | because |
| wnb-2-a03 | (3) | tetapi | but |
| wnb-2-a04 | (4) | dapat | well |
| wnb-2-a05 | (5) | sulit | poorly |
| wnb-2-a06 | (6) | sejuk | cool |

Pengecoh: *agar* → *so that*, *panas* → *hot*. Kode khusus tetap: *so that* di rumpang (2) dan (3) → INF-HUB. Lihat [catatan no. 3 dan 4](#catatan-untuk-diperiksa-pengembang).

### Variasi b: *Embun Upas in Dieng*

> 🌐 `jelajah-dieng.example` › Guide Stories
> **Embun Upas in Dieng**
> Written by Kak Laras, tour guide · 20 July
>
> In the dry season, the sky over the Dieng Plateau is often very clear. At night, heat from the earth's surface quickly escapes into space **(1)** there are no clouds to hold it in. Cold air **(2)** sinks and collects in the hollows of the plateau. The temperature near the ground can drop below 0 °C, **(3)** the morning dew freezes into thin ice. People in Dieng call this thin ice embun upas.
>
> To visitors, embun upas looks beautiful, **(4)** for farmers the ice can damage potato leaves. Visitors who want to see embun upas must come in the **(5)**, because the ice melts quickly once the sun gets strong. From all this information, embun upas forms most easily after a **(6)** night.

± 125 kata

| Kode | Rumpang | Kunci (id) | Kunci (en) |
| --- | --- | --- | --- |
| wnb-2-b01 | (1) | karena | because |
| wnb-2-b02 | (2) | lalu | then |
| wnb-2-b03 | (3) | sehingga | so |
| wnb-2-b04 | (4) | tetapi | but |
| wnb-2-b05 | (5) | pagi | morning |
| wnb-2-b06 | (6) | cerah | clear |

Pengecoh: *siang* → *afternoon*, *berawan* → *cloudy*.

## `wnb-3` Benar atau Salah

**Instruction:** "Read both messages and the story, then choose TRUE or FALSE for each statement."

### Variasi a: *Two Messages about Telaga Warna*

> 💬 **Raka's Big Family Group** · ↪ *Forwarded many times*
> "Drinking water from Telaga Warna keeps you young! Share this with every group!"
> *(no maker's name and no date)*
>
> 📢 **@pengelola.telagawarna** ✓ official account · 2 August
> "The water of Telaga Warna contains sulphur, so its colour can look green, blue, or yellowish. The lake water is not for drinking. Bring your own drinking water when you visit."
>
> Raka wants to know which message can be trusted. Raka checks who made each message, then asks the class teacher. The teacher explains that water containing sulphur is not safe to drink. Dito, Raka's cousin, has also only read the chain message. Dito says, "I reckon it's true."

± 105 kata

| Kode | Statement |
| --- | --- |
| wnb-3-a01 | Both messages talk about the water of Telaga Warna, but they say opposite things. |
| wnb-3-a02 | The chain message must be true because it has been forwarded many times. |
| wnb-3-a03 | According to the managers' post, the water of Telaga Warna can look green, blue, or yellowish because it contains sulphur. |
| wnb-3-a04 | The managers' post is more trustworthy than the chain message because its maker and date are clear. |
| wnb-3-a05 | Asking Dito is just as helpful as asking the teacher. |
| wnb-3-a06 | Based on the evidence in the text, thirsty visitors may drink the water of Telaga Warna as long as it looks clear. |

### Variasi b: *News about the Craters*

> The Dieng Plateau was formed by volcanic activity. That is why there are several craters there. Gas that is harmful to health can come out around the craters. Officers put up signs and barrier fences so that visitors stay at a safe distance.
>
> 💬 **Dinda's Family Travel Group** · ↪ *Forwarded many times*
> "The craters are safe, just go up close so your photos look good!"
> *(no maker's name)*
>
> 📢 **Official account of the volcano monitoring agency** ✓ · 2 August
> "Visitors to the craters in Dieng must follow the signs and keep a safe distance from the craters."
>
> In the end, Dinda's family still went on their trip, but they stood behind the barrier fence when taking photos.

± 100 kata

| Kode | Statement |
| --- | --- |
| wnb-3-b01 | The chain message and the official announcement give different advice about going near the craters. |
| wnb-3-b02 | According to the text, officers put up barrier fences because the craters in Dieng are too hot to go near. |
| wnb-3-b03 | The chain message can be trusted because it has spread widely. |
| wnb-3-b04 | The official announcement can be trusted because the organisation that made it is clear. |
| wnb-3-b05 | The officers' signs and fences are better to follow than the chain message, because the officers know about the danger of the gas at the craters. |
| wnb-3-b06 | Based on the evidence in the text, Dinda's family had to cancel their trip because the official announcement banned visitors from coming to the craters. |

### Kartu jangkar Wonosobo (sama di variasi a dan b)

> **@carica.mantap** · *Sponsored*
> "The TASTIEST sweet carica in all of Dieng! Guaranteed to make you smart and never get sick! Order now, 50% off today only!"
> ❤️ 2,300 likes

± 25 kata

| Kode | Statement |
| --- | --- |
| wnb-3-x01 | This post is an advert, as shown by the label "Sponsored". |
| wnb-3-x02 | Because 2,300 people liked it, what the post says must be true. |

## `wnb-4` Cari Objek

### Babak 1: Temukan Benda (PBK)

Dari bank lama (`wonosobo.php`); `wnb-4-x04` diterjemahkan ulang.

| Kode | Clue |
| --- | --- |
| wnb-4-x01 | Find the musical instrument shaped like a curved hood of woven bamboo. The hood once protected duck herders from the sun and rain, and then strings were added to pluck. |
| wnb-4-x02 | Find the mountain fruit that looks like a papaya but is small. It grows well in cool air and is often made into sweets. |
| wnb-4-x03 | Find the small bamboo basket that is dipped again and again into boiling water. Its name was used to name Wonosobo's special noodles. |
| wnb-4-x04 | Find the leaves that are brewed into a warm drink. They are picked at the Tambi estate, which has been there since Dutch colonial times. **Diterjemahkan ulang:** bank lama *since 1865*; 08 kini "sejak zaman Belanda" (cek fakta 27 Oktober 2026, [25](25_CEK_FAKTA_BUDAYA.md)). |

Pengecoh (label bank lama): *Ordinary papaya*, *Rigen*, *A bowl of ongklok noodles*, *Angklung*, *Sasando*, *Coffee beans*, *Topeng Ireng headdress*, *Getuk*.

### Babak 2: Periksa Unggahan (D3)

**Instruction:** "This is a post about Wonosobo's culture. Read the clue carefully, then tap the right part of the post."

#### Variasi a: Unggahan video bundengan

| No | Part | Text |
| --- | --- | --- |
| E1 | Uploader's account name | `@jelajah.awan` |
| E2 | Small text in the corner of the video | "Video: Sanggar Sekar Kowangan" |
| E3 | Caption | "Angklung from West Java, so cool! 🎶 I made this video myself, you know." |
| E4 | Culture House information board | "Bundengan is a musical instrument from Wonosobo. Its shape comes from the kowangan, the bamboo hood worn by duck herders." |
| E5 | Number of likes | "8,000 likes" |
| E6 | Comment 1 | **Wulan**: "That's a bundengan from Wonosobo. It's on the Culture House board 🙏" |
| E7 | Comment 2 | **Galih**: "WRONG! You don't even know that, how embarrassing! 😡" |
| E8 | Comment 3 | **Putra**: "LOL, same thing, they're both made of bamboo." |
| E9 | Comment 4 | **Intan**: "I thought it was an angklung too 😅" |
| E10 | Button | "Share" |
| E11 | Button | "Download" |
| E12 | Button | "Send a message to the studio" |

| Kode | Clue |
| --- | --- |
| wnb-4-a01 | The uploader wrote that they made this video themselves. Tap the sign that shows who really made the video. |
| wnb-4-a02 | 8,000 people liked this post, but something in it is not accurate. Compare it with the information board, then tap the part that most needs correcting, so other people don't get the wrong idea too. |
| wnb-4-a03 | You want to use this video for a school assignment that is due tomorrow. Tap the right first step. |
| wnb-4-a04 | Tap the comment that corrects the caption accurately and politely, and names its source. |

#### Variasi b: Unggahan video mi ongklok

| No | Part | Text |
| --- | --- | --- |
| F1 | Uploader's account name | `@makan.terus` |
| F2 | Small text in the corner of the video | "Video: Warung Kabut Pagi" |
| F3 | Caption | "Ongklok noodles, a special dish from Magelang! 😋 I made this video myself, you know." |
| F4 | Culture House information board | "Ongklok noodles (mi ongklok) are a special food of Wonosobo. The name comes from the ongklok, a small bamboo basket used to boil the noodles." |
| F5 | Number of likes | "7,500 likes" |
| F6 | Comment 1 | **Dewi**: "Ongklok noodles are from Wonosobo. It's on the Culture House board 🙏" |
| F7 | Comment 2 | **Aldi**: "NONSENSE! Go and learn first! 😡" |
| F8 | Comment 3 | **Yoga**: "LOL, same thing, they're all just noodles." |
| F9 | Comment 4 | **Nisa**: "I thought they were from Magelang too 😅" |
| F10 | Button | "Share" |
| F11 | Button | "Download" |
| F12 | Button | "Send a message to the food stall" |

| Kode | Clue |
| --- | --- |
| wnb-4-b01 | The uploader wrote that they made this video themselves. Tap the sign that shows who really made the video. |
| wnb-4-b02 | 7,500 people liked this post, but something in it is not accurate. Compare it with the information board, then tap the part that most needs correcting, so other people don't get the wrong idea too. |
| wnb-4-b03 | You want to use this video for a school assignment that is due tomorrow. Tap the right first step. |
| wnb-4-b04 | Tap the comment that corrects the caption accurately and politely, and names its source. |

## `wnb-5` Skenario Digital

**Instruction** (dari 12): "Read the story. Choose what you would do, then choose your reason."

### Variasi a

**wnb-5-a01 · Personal data.** An account you don't know sends a message: "Congratulations! You've won a free family trip to Telaga Warna!" A few minutes later, it writes again: "Send a photo of your student card and your parent's phone number now, only 3 places left!"

| Action | Feedback |
| --- | --- |
| Send the photo straight away before the places run out | A prize from a stranger can be a trick to get your data. Pressure like "only 3 left" is a warning sign. |
| Reply first to ask who they are | Replying lets the stranger know your account is active, and they can keep trying to talk you into it. |
| Show your parents, then block the account | Good choice. Your data is safe, and the scam account can be stopped. |
| Just ignore it without telling anyone | Your data is safe. Telling your parents would help protect other people too. |

**Explanation after tries run out:** A prize from an unknown account, especially one that rushes you, can be a trick to get your data. By showing your parents and blocking the account, your family's data stays safe.

| Reason | Kode |
| --- | --- |
| A prize from a stranger can be a trick | R-RISK |
| My parents' data isn't mine to share | R-ETIK |
| My teacher says not to reply to strangers | R-ATUR |
| My friends are sending their data too | R-SOS |
| I don't really like going on trips anyway | R-ACAK |

**wnb-5-a02 · Password and one-time code (OTP).** You get a message from an account called "Official Game Bintang Team ✓": "Your account will be deleted today! Send the code that just arrived on your mum's phone to save it." Soon after, your mum's phone gets a text message with a six-digit code.

| Action | Feedback |
| --- | --- |
| Quickly send the code so the account isn't deleted | A code like that is a key to an account. The real team never asks for it, even if the account has a tick. |
| Reply first to ask whether they are really the official team | A scammer will always say "yes". Replying means they keep trying to talk you into it. |
| Show the message and the text to your mum | Good choice. The code stays safe, your mum knows someone is trying to get into her account, and you can report the account together. |
| Delete the message and don't tell your mum | The code is safe. Your mum needs to know, because the code came to her phone. |

**Explanation after tries run out:** A code from a text message is a key for getting into an account, and even an account that looks official can be fake. By showing the message and the text to your mum, the code stays safe and you can report it together.

| Reason | Kode |
| --- | --- |
| An official team never asks for that code | R-RISK |
| The code came to Mum's phone, it isn't mine | R-ETIK |
| I'm not allowed to reply to messages from strangers | R-ATUR |
| My friend once sent a code and it was fine | R-SOS |
| I actually don't play that game much | R-ACAK |

**wnb-5-a03 · Permission to share a chat.** In a private message, Bima tells you about feeling sad after losing a bundengan competition. That afternoon, friends in the class group ask, "What's up with Bima? Why so quiet all the time?" You still have your chat with Bima.

| Action | Feedback |
| --- | --- |
| Forward a screenshot of Bima's chat to the group | Bima's private chat may only be shared if Bima agrees. Screenshots can spread anywhere. |
| Tell the group what the chat said in your own words | Even without a screenshot, Bima's private story still spreads without Bima's permission. |
| Ask the group to ask Bima directly, then say hello to Bima | Good choice. Bima's secret is kept, and you still show you care. |
| Don't answer the group and don't say hello to Bima | Bima's secret is safe. Saying hello to Bima privately would make Bima feel less alone. |

**Explanation after tries run out:** What Bima said in a private message is Bima's secret. By asking your friends to ask Bima directly, the secret is kept and you can still say hello to Bima.

| Reason | Kode |
| --- | --- |
| Bima may not want the story shared | R-ETIK |
| Screenshots can spread anywhere | R-RISK |
| There's a class rule against gossiping in the group | R-ATUR |
| My friends in the group are really curious | R-SOS |
| I like the sound of the bundengan too | R-ACAK |

**wnb-5-a04 · False news, two steps.** A message with a photo of a damaged road is going round the class group: "The road to Telaga Warna was cut off by a landslide today! Share this!" Your friend Fikri replies: "I've already shared it to my family group." Then you see the same photo in an old news story from another area.

| Action | Feedback |
| --- | --- |
| Share it to other groups too, so people are careful | The photo turns out to be an old one from another place. Sharing it makes more people get the wrong idea. |
| Reply in the group: "Fikri, you're stupid, that's a hoax!" | You're right about the photo, but rude words embarrass Fikri and make others not want to listen. |
| Politely tell Fikri that it's an old photo | Good choice. The false news can stop, and Fikri is still respected. You can also suggest checking the managers' official account. |
| Don't share it and don't respond at all | You didn't help spread it, well done. Telling someone politely can stop false news. |

**Explanation after tries run out:** The same photo is in an old news story from another area, so the message may not be true. By politely telling Fikri, the news can stop without embarrassing Fikri.

| Reason | Kode |
| --- | --- |
| An old photo can make people get the wrong idea | R-RISK |
| If I tell Fikri politely, Fikri won't feel embarrassed | R-ETIK |
| My teacher said we mustn't spread news | R-ATUR |
| Everyone in the group already believes it | R-SOS |
| I've never been to Telaga Warna | R-ACAK |

### Variasi b

**wnb-5-b01 · Personal data.** The account "Dieng Carica Prize Draw" announces: "Join our prize draw and win a bicycle!" To join, you must send a photo of your Family Card and your parent's phone number. Your friend Sinta says, "I've already joined. They say it's safe."

| Action | Feedback |
| --- | --- |
| Send a photo of the Family Card so you can join | A Family Card holds data about your whole family. A real prize draw does not ask for it. |
| Send your parent's phone number first, and the Family Card photo later | Your parent's phone number is also personal data that can be used to trick people. |
| Show your parents, and warn Sinta too | Good choice. Your data is safe, and you help Sinta too. |
| Don't join, and don't talk about it with Sinta | Your data is safe. Sinta may need help, because Sinta has already sent the data. |

**Explanation after tries run out:** A Family Card and your parent's phone number are your family's personal data, even if your friend says it's safe. By showing your parents and warning Sinta, you keep your data safe and help your friend.

| Reason | Kode |
| --- | --- |
| Strangers could misuse my family's data | R-RISK |
| The Family Card has data on my whole family | R-ETIK |
| My teacher says not to join online prize draws | R-ATUR |
| Sinta has joined, so it must be safe | R-SOS |
| I already have a bicycle at home | R-ACAK |

**wnb-5-b02 · Password.** You get a message from "School Admin": "Your learning account will be blocked. Sign in again at `sekolah-kita.example/masuk` and type your password." You notice the address is different from the school website you usually use.

| Action | Feedback |
| --- | --- |
| Open the link and type your password there | A different address is a warning sign. A fake website can steal your password. |
| Reply to ask whether it's really the school admin | A fake sender will say "yes". It's safer to ask your teacher directly. |
| Show the message to your teacher without opening it | Good choice. Your teacher can make sure, and other friends can be warned. |
| Delete the message and don't tell anyone | Your password is safe. Telling your teacher would protect your friends too. |

**Explanation after tries run out:** The website address is different from the usual school website, so the message may not be from the school. By showing the message to your teacher without opening it, your password stays safe and your teacher can check.

| Reason | Kode |
| --- | --- |
| The address is different, so it could be a fake website | R-RISK |
| My classmates' data could be seen too | R-ETIK |
| I'm not allowed to open any links on a phone | R-ATUR |
| Other friends seem to have signed in again already | R-SOS |
| I don't have any schoolwork right now | R-ACAK |

**wnb-5-b03 · Permission to share a friend's photo.** On a school trip to Dieng, you take a photo of Nadia asleep on the bus with mouth wide open. Friends in the class group find out and write: "Send us the photo, it must be funny!"

| Action | Feedback |
| --- | --- |
| Send the photo to the group because your friends asked | Nadia doesn't know about the photo. It may only be shared if Nadia agrees. |
| Turn it into a funny sticker, then send it to the group | A sticker made from a photo of someone asleep can embarrass them and keep going round. |
| Say in the group that you need Nadia's permission first | Good choice. You protect Nadia's feelings even when your friends push you. |
| Don't answer the group, and keep the photo | The photo didn't spread, well done. Explaining your reason could make other friends think too. |

**Explanation after tries run out:** Nadia doesn't know about the photo and might be embarrassed if it spreads. By saying in the group that you need Nadia's permission first, you protect Nadia's feelings even when your friends push you.

| Reason | Kode |
| --- | --- |
| Nadia may not want the photo shared | R-ETIK |
| A photo that's been sent can spread anywhere | R-RISK |
| Our teacher doesn't allow photos of friends on the bus | R-ATUR |
| My friends in the group asked for it | R-SOS |
| The trip to Dieng is really long | R-ACAK |

**wnb-5-b04 · False news, two steps.** A video in the class group is titled "Embun upas can make people sick, don't go outside early in the morning!" The video doesn't give a source. Your friend Fikri replies: "I've already sent it to my family group."

| Action | Feedback |
| --- | --- |
| Send it to other groups too, so people are careful | A video with no source isn't always true. Spreading it can make lots of people worried. |
| Reply in the group: "Fikri, you're stupid, that's not even a thing!" | Reminding someone is good, but rude words embarrass Fikri. |
| Politely remind Fikri to check the source | Good choice. The news gets checked first, and Fikri is still respected. |
| Don't send it and don't respond at all | You didn't help spread it, well done. Telling someone politely can stop the news. |

**Explanation after tries run out:** A video with no source isn't always true, and it has started to spread. By politely reminding Fikri to check the source, the news can be checked before it spreads further.

| Reason | Kode |
| --- | --- |
| A video with no source can make people worried | R-RISK |
| If I tell Fikri politely, Fikri won't feel embarrassed | R-ETIK |
| Our teacher said we mustn't watch videos in the group | R-ATUR |
| Lots of people in the group already believe the video | R-SOS |
| I've never seen embun upas | R-ACAK |

---

## Catatan untuk diperiksa pengembang

Pilihan penerjemahan yang mengubah sedikit cara butir bekerja, atau yang punya pilihan lain. Semua usulan AI di bawah **disetujui pengembang apa adanya** (3 November 2026); tidak ada baris terjemahan yang diubah.

1. **`tmg-2-b` (Isi Rumpang): *dry* dan *dry up* ada di bank kata yang sama.** "Kemarau" paling wajar diterjemahkan *dry (season)*, dan "kering" untuk mata air *dry up*. Keduanya berbeda sebagai teks, jadi pemetaan kode tetap jalan, tetapi dua kata yang mirip bisa sedikit membantu siswa: *dry up* hanya cocok di rumpang (4). Di teks Indonesia "tidak pernah kering" dan kunci (4) "kering" juga sama-sama tersurat, jadi gema katanya setara. Pilihan lain: kunci (4) *disappear*, yang menjauh dari makna "kering".
2. **`mgl-2-b` (Isi Rumpang): pengecoh *diam* → *stop*.** Di teks Indonesia "diam" menyasar rumpang (2) dan, menurut catatan 08, juga bersaing secara bentuk kata di rumpang (5) ("tetap diam"). Dalam bahasa Inggris kata kerja *stop* cocok di (2) ("the bells stop"), tetapi tidak di (5) ("stays stop"). Di (5) yang masih bersaing *together* ("stays together"). Kode sasaran (INF-HUB di rumpang 2) tidak berubah. Pilihan lain: rumpang (2) dijadikan kata sifat ("the bells sound *loud*", pengecoh *quiet*), supaya *quiet* juga bersaing di (5); maknanya sedikit bergeser dari "berbunyi".
3. **`wnb-2-a` (Isi Rumpang): kunci (4) dan (5) menjadi keterangan *well* / *poorly*.** "Dapat" dan "sulit" tidak bisa masuk ke satu kerangka kalimat Inggris yang sama tanpa *to*. Dengan *grows well* / *grows poorly* keduanya bersaing satu sama lain di (4) dan (5), seperti "dapat" dan "sulit" di teks Indonesia. Kalimat sumber diubah dari "tumbuh baik … sulit tumbuh" menjadi *grows best … struggles to grow*, supaya tidak ada kunci yang tersurat. Akibatnya (5) sedikit lebih sulit daripada teks Indonesia, karena di sana "sulit tumbuh" tersurat.
4. **`wnb-2-a`: pasangan *so* / *so that*.** Padanan "sehingga" (akibat) dan "agar" (tujuan) yang menjadi inti pengecoh. Kode khusus *so that* di rumpang (2) dan (3) tetap INF-HUB.
5. **Kata ganti tokoh.** Mengikuti aturan no. 4, nama tokoh diulang (Laras, Arif, Dimas, Sekar, Bima, Sinta, Nadia, Fikri, Raka, Dito), jadi beberapa kalimat terasa berulang. Bila pengembang lebih suka *he*/*she* sesuai nama, cukup diganti di berkas data; butirnya tidak berubah.
6. **Sapaan "Kak" tanpa nama** di komentar Salsa, Tegar, Wulan, dan Dewi dibuang. Di depan nama (Kak Dewi, Kak Laras) tetap, seperti bank lama.
7. **Kata kasar** "bodoh" (`wnb-5-a04`, `wnb-5-b04`) menjadi *stupid*, sama kerasnya dengan teks Indonesia, karena butir itu justru menguji tindakan kasar.
8. **Panjang teks.** Teks bahasa Inggris rata-rata 5–15% lebih panjang. Kotak bagian ketuk *Periksa Unggahan* ditetapkan dalam persen adegan ([31 §9](31_IMPLEMENTASI_BANK_SOAL_BARU.md#9-area-ketuk-tidak-bersentuhan-perubahan-no-17)); saat berkas data ditulis, kotak itu perlu dicek dengan teks bahasa Inggris juga, terutama E3, F3, F4, D4, dan C4. Pilihan tindakan Skenario Digital yang terpanjang (`wnb-5-b04` pilihan 2, `mgl-5-a02` pilihan 1) juga perlu dicek di mock-up.
9. **Tanggal dan hari** ("Sabtu, 12 Juli", "5 Mei") diterjemahkan biasa. Tidak ada butir yang menguji tanggal, kecuali `mgl-3-b02` (urutan tahun 907 dan 1957), yang angkanya tetap.

## Cara memperbarui

- Pengembang memeriksa dokumen ini dan menaikkan statusnya ke `disetujui pengembang` (3 November 2026, dicatat di [04](04_CATATAN_KEPUTUSAN.md) dan `00`). Perubahan berikutnya ditandai di tabelnya, lalu diperiksa lagi.
- Bila 08 berubah (perbaikan sebelum validasi, matriks revisi setelah validasi, atau uji keterbacaan), baris yang sama di sini diterjemahkan ulang oleh AI dan diperiksa lagi oleh pengembang. Butir yang berubah ditandai di tabelnya dengan tanggal dan asal perubahannya (`asal: peneliti + ahli` untuk hasil validasi).
- Butir yang baru diterjemahkan ulang mendapat lagi `review.note` "terjemahan belum diperiksa" di berkas data sampai pengembang memeriksanya.

## Dipakai di mana

- **Gelombang kode D** ([31 §7.3](31_IMPLEMENTASI_BANK_SOAL_BARU.md#73-berkas-data-ditulis-ulang)): kolom bahasa Inggris berkas data `docs/bank-soal/data/*.php` diisi dari dokumen ini, bukan dari teks Indonesia. Selama dokumen ini belum disetujui, butirnya bercatatan `review.note` "terjemahan belum ada" ([31 §6.4](31_IMPLEMENTASI_BANK_SOAL_BARU.md#64-bentuk-berkas-data); keputusan no. 11). Sejak persetujuan 3 November 2026 catatan itu diganti "Teks Inggris: dari 36, disetujui pengembang 3 November 2026" (`docs/bank-soal/tools/import_08.py`), dan `gelita:content:verify` tidak lagi menyebut 13 butir itu.
- **Isi Rumpang**: pasangan kata di tabel kunci dan pengecoh menjadi pasangan bank kata (`wordBank()`), yang dipakai `ErrorCodeResolver` untuk siswa yang bermain dalam bahasa Inggris.
- **Narasi**: teks bahasa Inggris ini juga naskah rekaman `instruksi-*`, `unggahan-*`, dan `skenario-*` versi Inggris ([12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) → daftar rekaman), setelah disetujui.
- **Bukan untuk validasi.** Validator, uji keterbacaan, dan cek fakta memakai 08. Data penelitian dari siswa yang bermain dalam bahasa Inggris tetap tercatat dengan kode butir yang sama.
