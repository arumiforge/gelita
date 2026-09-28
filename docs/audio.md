# Audio GELITA

Seluruh audio bawaan permainan ikut di repositori: **3 musik gamelan**, **6 efek suara**, dan **96 rekaman narasi × 2 bahasa**. Semuanya dibangkitkan oleh [`tools/audio/`](../tools/audio/README.md).

- Musik dan efek suara disintesis dari nol, tanpa sampel rekaman, dengan penalaan pelog–slendro. Gendingnya komposisi orisinal.
- Narasi adalah **suara sintetis (text-to-speech)** yang diolah per tokoh. Rekaman ini tercatat sebagai TTS di basis data (`audio_assets.production_method = tts`). Kapan saja dapat diganti rekaman pengisi suara tanpa mengubah kode.

| Kelompok | Lokasi | Isi | Dipakai oleh |
|---|---|---|---|
| Musik | `public/assets/audio/music/` | `map`, `region`, `challenge` (MP3 stereo 128 kbps, loop) | `core/audio.js` → `Sfx.music()` |
| Efek suara | `public/assets/audio/sfx/` | `click`, `correct`, `wrong`, `lock`, `shard`, `region-done` | `core/audio.js` → `Sfx.play()` |
| Narasi | `public/assets/audio/narasi/{id,en}/` | 88 baris naskah + 8 petunjuk arena cari per bahasa (MP3 mono 96 kbps) | `gelita:narration:import` → `dialogues` / `challenge_items` |
| Manifest | `public/assets/audio/narasi/produksi.json` | sha256 + profil suara tiap rekaman sintetis | `NarrationImporter::production()` |

## Musik gamelan

Tiga gending pendek yang berulang tanpa jeda. Karakter bunyinya gamelan Jawa Tengah: saron, demung, peking, bonang, slenthem, kenong, kethuk, kempul, gong ageng, dan kendang, ditambah gender, gambang, siter, dan suling untuk gending yang lebih lembut.

| Berkas | Suasana | Laras · bentuk · tempo | Ricikan | Loop |
|---|---|---|---|---|
| `map.mp3` | megah dan mengajak menjelajah; diputar di Peta Kedu | slendro manyura · ladrang (2 gongan × 32 ketukan) · irama tanggung, 66 ketukan/menit | demung, 2 saron barung, peking, bonang barung & panerus (mipil, gembyang), slenthem, kenong, kethuk, kempul, gong ageng, kendang, suling di gongan kedua | 58,2 s |
| `region.mp3` | tenang, suasana desa di lereng gunung; diputar di peta wilayah | pelog nem · ketawang (3 gongan × 16 ketukan) · irama dadi, 42 ketukan/menit | gender, gambang, siter, saron, slenthem, kenong, kethuk, kempul, gong, kendang lirih, suling | 68,6 s |
| `challenge.mp3` | hening agar siswa tetap fokus membaca; diputar selama tantangan | slendro sanga · ketawang, balungan nibani (4 gongan) · 52 ketukan/menit | slenthem, demung lirih, gender, kenong, kethuk, kempul, gong; tanpa kendang dan suling | 73,8 s |

Catatan teknis:

- **Laras.**
  - Pelog dan slendro memakai jarak sen khas gamelan Jawa dengan oktaf sedikit melebar (1212 sen).
  - Tiap bilah diberi selisih penalaan kecil, dan tiap ricikan sedikit berbeda dari ricikan lain. Hasilnya adalah *ombak* (denyut) seperti gamelan sungguhan yang punya embat sendiri.
- **Garap.**
  - Bonang ber-*mipil* dan peking ber-*nacah* mendahului balungan dan jatuh di ketukan kuat.
  - Gender dan gambang berkelok menuju *seleh*.
  - Kenong, kempul, kethuk, dan gong menandai struktur ladrang/ketawang.
  - Gong ageng (±41 Hz) punya ombak ±2,4 Hz dan dengung parsial kedua yang mengembang.
- **Loop mulus.** Ekor gema dan nada di akhir putaran dilipat ke awal berkas. Pukulan gong jatuh tepat di titik sambung. Header LAME menyimpan jeda encoder, sehingga panjang hasil dekode sama persis dengan panjang loop (diperiksa dengan ffmpeg).
- **Kenyaringan.** Musik sengaja lebih pelan daripada narasi (−16 LUFS), karena narasi Jaka dan Mbah Kedu diputar di atas musik Peta Kedu dan petunjuk arena `cari` di atas musik tantangan. `core/audio.js` juga memakai volume Howler dua kali (per bunyi × global, bawaan 0,8 × 0,8), sehingga musik dan efek terdengar ±4 dB lebih pelan daripada angka di bawah. Narasi tidak terkena pengurangan ini. Hasilnya, musik tetap ±8–9 LU di bawah narasi. Kompresor lembut meredam pukulan gong.

  | Berkas (MP3 didekode) | Kenyaringan terpadu | Puncak momentari | Puncak sejati |
  |---|---|---|---|
  | `map` | −20,4 LUFS | −15,6 LUFS | −5,8 dBTP |
  | `region` | −20,9 LUFS | −16,6 LUFS | −6,8 dBTP |
  | `challenge` | −21,9 LUFS | −16,4 LUFS | −7,2 dBTP |

## Efek suara

| Berkas | Bunyi | Durasi | Diputar saat |
|---|---|---|---|
| `click.mp3` | ketukan kayu gambang, pendek dan lembut | 0,13 s | tombol HUD, keping puzzle, kata rumpang, kartu boleh |
| `correct.mp3` | tiga nada bonang naik (pelog 5–6–1́) + dentang peking | 0,9 s | jawaban benar (semua arena) |
| `wrong.mp3` | dua nada demung turun yang diredam, dengung kempul tipis; tidak menghukum | 0,9 s | jawaban salah |
| `lock.mp3` | "tuk-tuk" kethuk + gemerincing kecrek yang diredam | 0,5 s | wilayah, tantangan, atau Pustaka yang masih terkunci |
| `shard.mp3` | arpeggio peking & gender naik, kilau dentingan, kenong | 3,2 s | layar selesai: serpihan kembali |
| `region-done.mp3` | frasa suwuk bonang–saron ditutup gong ageng, kenong, dan kempul | 5,1 s | layar selesai tantangan penuntas wilayah |

`shard` dan `region-done` dipanggil `game/finished.js`. Keduanya kini juga tercantum di **Media → Kelengkapan aset**.

## Narasi

### Pemeran suara

| Tokoh | Bahasa Indonesia | English | Arahan naskah yang dikejar |
|---|---|---|---|
| Narator | Mimic 3 `jv_ID/google-gmu_low` penutur #36 (perempuan), tanpa pengolahan nada, tempo ×0,96 | Piper `kristin` (perempuan, gaya buku audio LibriVox) | hangat dan tenang seperti pendongeng |
| Jaka | penutur #6 → median F0 ±276 Hz, formant ×1,07, tempo ×0,92 | Piper `jenny` → F0 ±262 Hz, formant ×1,12 | anak laki-laki ±11 tahun, bersemangat |
| Mbah Kedu | penutur #38 (laki-laki) → F0 ±104 Hz, formant ×0,95, getar lansia ringan, tempo ×0,97 | Piper `joe` → F0 ±92 Hz, formant ×0,98, getar lansia | kakek Jawa, lembut, pelan, logat Jawa halus |

Pose baris naskah ikut mengatur suara:

- **Jaka**
  - `happy`: rentang nada lebar, sedikit lebih cepat.
  - `afraid`: lebih tinggi, bergetar.
  - `determined`: lebih tegas.
  - `sad`: lebih rendah, pelan, lebih lirih.
  - `bow`: tenang.
- **Mbah Kedu**
  - `smile`: sedikit lebih cerah.
  - `worried`: lebih rendah dan pelan.
  - `weak`: lambat, bergetar, desah napas tipis, lebih lirih.

Kalimat tanya berbahasa Indonesia diberi intonasi naik, dan kalimat seru rentang nada yang lebih lebar.

**Logat Jawa.** Narasi Indonesia dibacakan model suara bahasa Jawa (OpenSLR 41). Model TTS bahasa Indonesia yang terbuka, seperti Piper `id_ID-news_tts` atau MMS, tersimpan di Hugging Face yang tidak dapat diakses dari lingkungan pembuatan audio ini. Hasilnya bahasa Indonesia berlogat Jawa (*medok*): konsonan *b/d/g/j* "berat" khas Jawa, dan sebagian kata berakhiran *-a* condong ke [ɔ]. Untuk Mbah Kedu, sesuai arahan naskah ("logat Jawa halus"), dan untuk Jaka, anak Kedu, logat ini justru cocok. Kata yang paling mudah tertukar dengan kata Jawa dikoreksi di `tools/audio/id_g2p.py`, yaitu *apa* ("opo"), *para* ("poro"), *ada* ("odo"), dan *sana* ("sono"). Sapaan *Mbah* dilafalkan [əmbah], seperti lazimnya diucapkan.

**Nama Jawa di narasi Inggris** dilafalkan dengan IPA dari `tools/audio/en_lexicon.py`: *Temanggung* /təˈmɑːŋɡʊŋ/, *Magelang* /mɑːɡəˈlɑːŋ/, *Mbah* /əmˈbɑː/, dan seterusnya.

### Spesifikasi berkas

- MP3 mono 44,1 kHz, 96 kbps CBR dengan header Xing, sehingga `MediaStore` membaca durasinya. Nilai ini memenuhi anjuran [`naskah-cerita.md`](naskah-cerita.md) (64–96 kbps) dan [`08_DEPLOYMENT.md`](08_DEPLOYMENT.md) (96–128 kbps).
- Kenyaringan −16 LUFS per berkas, diukur bersama heningnya.
  - Mbah Kedu −16,5 LUFS. Puncak glotal suara beratnya tinggi, jadi −16 hanya tercapai dengan limiter yang terdengar menekan.
  - Pose yang digeser: Jaka `determined` −15,5, `afraid` −16,5, dan `sad` −17; Mbah Kedu `weak` −18, supaya terdengar lirih.
- Puncak sejati (*true peak*) ≤ −1 dBTP pada master WAV lewat limiter look-ahead 2 ms yang pulih dalam ±20 ms. Setelah dikode ke MP3, puncak sejati paling tinggi −0,5 dBTP.
  - Pada Narator dan Jaka, limiter hanya menyentuh puncak tunggal (≤ 0,2% durasi).
  - Pada Mbah Kedu, limiter menekan lebih dari 1 dB di ±6% durasi (paling banyak ±23%). Penekanan terdalam per baris bermedian 1,4 dB (Inggris) dan 3,2 dB (Indonesia), paling banyak 6,4 dB.
- LAME pada mode CBR mengalikan sinyal 0,95× (−0,45 dB). `publish.py` mengompensasinya, sehingga kenyaringan MP3 sama dengan master WAV-nya.
- Hening 0,3 detik di awal dan akhir.
- Satu berkas satu baris, nama = kode baris (`intro-01.mp3`, `petunjuk-tmg-4-03.mp3`, …). Transkripnya persis teks naskah pada bahasa itu.

### Pemeriksaan kejelasan (QA)

Setiap baris dipilih dari beberapa kandidat sintesis: Indonesia 3–5 kandidat, Inggris 1 kandidat, dan 3–6 kandidat tambahan untuk baris yang diulang. Tiap kandidat ditranskripsi Whisper small tanpa diberi teks naskahnya, lalu transkripnya dibandingkan dengan naskah memakai *character error rate* (CER): huruf kecil, tanpa tanda baca, dan angka dieja. Kandidat dengan CER terendah yang dipakai. Rekaman final, sesudah pengolahan tempo dan kenyaringan, ditranskripsi ulang dengan cara yang sama.

CER di sini lebih tinggi daripada salah dengar yang sebenarnya:

- **Logat Jawa.** Whisper menuliskan ucapan berlogat Jawa dengan ejaan yang ia kira, misalnya *Mbah* → "Embah". Huruf yang berbeda itu terhitung salah.
- **Nama Jawa di narasi Inggris** ditulis Whisper dengan ejaan Inggris, misalnya *Kedu* → "Kudu".
- **Baris pendek.** Satu kata yang meleset pada baris seperti *"Aku takut, Mbah…"* langsung membuat CER-nya puluhan persen.

QA otomatis tidak menggantikan mendengarkan. Setujui narasi di **Konten → Narasi** setelah didengarkan.

### Memasang di server

```bash
php spark gelita:narration:import --dry-run   # 96 baris per bahasa dikenali
php spark gelita:narration:import             # audio draft, ditautkan ke naskah & petunjuk cari
```

Lalu buka **Konten → Narasi**, dengarkan, dan tekan **Setujui semua narasi draft ID/EN**.

Narasi petunjuk arena `cari` butuh bank soal produksi sudah diimpor (lihat [`08_DEPLOYMENT.md`](08_DEPLOYMENT.md)). Nomor `petunjuk-tmg-4-NN` mengikuti urutan target aktif. Di server pengembangan, butir contoh `tmg-4-91` dari `SampleItemSeeder` berada di urutan pertama dan menggeser nomor itu (dry-run melaporkan 97 baris). Nonaktifkan butir contoh `-91`/`-92` di editor butir sebelum mengimpor, seperti di server production.

Berkas yang sha256-nya tercantum di `produksi.json` tercatat `production_method = tts`, dan `voice_profile` menyebut model, penutur, dan pengolahannya. Peneliti dapat membedakan narasi sintetis dari rekaman pengisi suara di tabel `audio_assets`.

### Mengganti dengan rekaman pengisi suara

Rekaman baru cukup memakai nama berkas yang sama, lalu dipasang seperti biasa: timpa berkas di folder narasi lalu `gelita:narration:import`, atau unggah lewat **Konten → Narasi → Unggah narasi**. Karena isinya berbeda dari manifest, rekaman itu otomatis tercatat sebagai rekaman sendiri dan kembali berstatus draft. Arahan suara tiap tokoh ada di [`naskah-cerita.md`](naskah-cerita.md).

## Lisensi dan kredit

- **Musik dan efek suara**: komposisi dan sintesis orisinal untuk GELITA (`tools/audio/gamelan.py`, `compose.py`), tanpa sampel atau rekaman pihak ketiga.
- **Narasi Indonesia**: dibangkitkan dengan model Mimic 3 `jv_ID/google-gmu_low` (MycroftAI, **CC BY-SA 4.0**). Model itu dilatih dari *High quality TTS data for Javanese* (OpenSLR 41, Google & Universitas Gadjah Mada, CC BY-SA 4.0). Untuk amannya, perlakukan rekaman ini dengan syarat yang sama: cantumkan atribusi di atas dan bagikan turunannya dengan lisensi serupa.
- **Narasi Inggris**: suara Piper `en_US-kristin-medium` (domain publik, dataset LibriVox), `en_GB-jenny_dioco-medium` (suara "Jenny (Dioco)"; klip hasil boleh dipakai, termasuk komersial, atribusi dianjurkan), dan `en_US-joe-medium` (CC0).
- **Whisper small** (MIT) hanya dipakai untuk memeriksa kejelasan, bukan bagian dari permainan.
