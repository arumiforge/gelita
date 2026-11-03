# Prompt Gambar Situasi Skenario Digital (24)

Per 3 November 2026 · Lampiran dokumen implementasi bagian 6 ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md), §7.5 dan §18 no. 6) · Sumber situasi: [08](08_DRAF_BANK_SOAL.md) · Status: **`draf pengembang`** (disusun AI, belum diperiksa pengembang)

Dua puluh empat prompt siap tempel ke ChatGPT (pembuat gambar), satu untuk tiap situasi Skenario Digital (3 wilayah × 2 variasi × 4 skenario). Prompt ditulis dalam bahasa Inggris karena pembuat gambar paling andal dengan bahasa itu; kutipan situasi Indonesia dari 08 hanya untuk konteks pembaca dan **tidak ikut ditempel**. Keputusan pengembang 3 November 2026 ([04](04_CATATAN_KEPUTUSAN.md)): gambar situasi dibuat sekarang, tidak menunggu validasi ahli media.

## Cara memakai

1. Satu prompt = satu percakapan baru di ChatGPT (atau satu pesan, lalu minta gambar). Tempel **hanya isi kotak kode** prompt itu. Tiap prompt sudah lengkap sendiri: gaya, adegan, ukuran, dan aturan.
2. Minta ukuran lanskap 3:2. Bila ChatGPT memberi ukuran lain, tulis: *"Regenerate at 3:2 landscape, 1536 x 1024."*
3. Periksa hasilnya dengan **daftar periksa** di bawah. Bila gagal, minta perbaikan satu hal saja, misalnya: *"Same picture, but remove all readable text and replace it with grey bars."*
4. Simpan sebagai WebP (rasio 3:2, sekitar 1536 × 1024) dengan nama slot katalog `item.skenario.{kode butir}` (misalnya `item.skenario.tmg-5-a01`). Nama berkas **tidak boleh** memuat kata seperti "aman", "tipuan", atau "hoaks" (32 §7.5).
5. Serahkan ke tim Katalog Aset untuk dimasukkan ke `gelita-katalog/` (impor lewat `gelita:katalog:impor`); gambar lalu dipasang ke `challenge_items.media_asset_id`. Kredit dan asal ditulis sebagai gambar sementara sampai pengembang menyetujuinya.

## Aturan yang berlaku untuk semua gambar

Berasal dari 32 §7.5, 08 → *Cara membaca*, 18 → *Skenario Digital*, dan arah seni di `gelita-katalog/AGENTS.md`.

- **Tidak ada petunjuk jawaban.** Tanpa segitiga peringatan, tanda seru, warna merah peringatan, tengkorak, gembok bersilang, lencana centang atau perisai, jempol, emoji, atau wajah sedih atau cemas. Cahaya dan suasana sama netralnya di ke-24 gambar, selaras dengan aturan nada datar untuk narasi situasi ([32 §9.4](32_IMPLEMENTASI_SKENARIO_DIGITAL.md#94-arahan-suara)).
- **Tanpa teks terbaca.** Pembuat gambar sering salah mengeja, dan teks di gambar bisa membocorkan jawaban (misalnya alamat situs palsu pada `wnb-5-b02`). Teks diganti batang abu-abu. Teks situasi sudah ada di layar dan di narasi.
- **Tanpa merek, logo, atau layar sungguhan.** Perangkat dan aplikasi buatan, avatar pengirim berupa siluet abu-abu polos tanpa lencana.
- **Tanpa wajah dan tanpa anak dalam keadaan memalukan.** Orang hanya tampak jauh, buram, dan dari belakang. `mgl-5-a03` dan `wnb-5-b03` menyangkut foto teman yang memalukan; gambarnya sengaja tidak memperlihatkan foto itu.
- **Satu gambar per butir, untuk kedua langkah.** Situasi Wonosobo ada yang berkembang dalam dua langkah (`config_json.steps` = 2, misalnya `wnb-5-a01` dan `wnb-5-a04`), tetapi gambar tetap satu. Gambarnya hanya memuat apa yang sudah ada di langkah pertama; bukti yang muncul di langkah kedua (misalnya berita lama pada `wnb-5-a04`) tidak digambar.
- **Tokoh Jaka tidak dipakai.** "Kamu" dalam situasi adalah siswa itu sendiri; gambar menunjukkan benda dan tempat, bukan tokoh.
- **Latar mengikuti wilayah**, tetapi tidak menambah informasi budaya baru yang belum dicek 25: kebun kopi dan Sindoro–Sumbing (Temanggung), Borobudur dan Gunung Tidar (Magelang), dataran tinggi Dieng berkabut (Wonosobo).

## Daftar periksa hasil gambar

- [ ] Tidak ada huruf, angka, atau alamat yang bisa dibaca.
- [ ] Tidak ada merek, logo, atau lencana.
- [ ] Tidak ada simbol bahaya atau warna peringatan, dan tidak ada ekspresi wajah.
- [ ] Tidak ada wajah yang terlihat jelas, dan tidak ada orang dalam keadaan memalukan.
- [ ] Gambar tidak memperlihatkan tindakan yang benar atau salah (misalnya tangan yang mengetik sandi, atau orang yang berbicara dengan orang dewasa).
- [ ] Gaya, palet, dan cahaya sama dengan gambar lain dalam set; rasio 3:2; subjek utama di tengah.
- [ ] Nama berkas sesuai slot `item.skenario.{kode butir}`.

## Kebijakan perubahan

Isi situasi di 08 masih `needs_verification`. Bila validasi ahli mengubah situasi, gambar yang bersangkutan dibuat ulang dengan mengganti baris **SCENE** pada prompt-nya; gaya, format, dan aturan tidak berubah. Tanda kutip situasi di bawah ikut diperbarui dari 08.

## Blok bersama (sudah termasuk di setiap prompt)

Untuk dibaca saja; **tidak perlu ditempel terpisah**.

```text
STYLE: Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

## Temanggung (mudah)

### `tmg-5-a01` — Data pribadi (prize quiz site)

Slot katalog: `item.skenario.tmg-5-a01` · Situasi di 08: *Kamu menemukan situs kuis berhadiah ... 'Isi nama lengkap, alamat rumah, dan nama sekolahmu, lalu ikut undian sepeda!' Kamu tidak tahu siapa pembuat situs itu.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A tablet leaning on a rustic wooden table on a veranda. The screen shows a bright, friendly quiz web page: a cheerful banner with a stylized 3D prize bicycle, and below it three blank rounded form fields stacked vertically and one plain button, all with grey placeholder bars instead of text. The page header area is an empty plain bar with no logo and no name. Behind the veranda, softly blurred terraced coffee slopes of Temanggung and the twin volcanoes Sindoro and Sumbing in light haze.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `tmg-5-a02` — Kata sandi (stranger asks for password in a game)

Slot katalog: `item.skenario.tmg-5-a02` · Situasi di 08: *Kamu sedang bermain game di tablet. Seseorang yang tidak kamu kenal mengirim pesan: 'Aku bisa bikin levelmu naik cepat. Kirim kata sandimu, ya!'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A tablet held at an angle on a lap-height wooden bench, showing a generic colourful adventure game: a simple 3D game character on a trail, a level bar at the top, and a chat bubble in the corner sent by a plain grey silhouette avatar (no photo, no badge). The bubble holds two short grey placeholder lines. Setting: a Javanese family veranda with potted plants, soft blurred coffee slopes behind.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `tmg-5-a03` — Izin membagikan foto diri (mother's account, photo of dancing)

Slot katalog: `item.skenario.tmg-5-a03` · Situasi di 08: *Kamu punya foto dirimu saat menari kuda lumping. Kamu ingin mengunggahnya ke akun media sosial milik ibumu. Ibumu sedang memasak di dapur.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone lying on a traditional Javanese kitchen counter next to a basket of vegetables. The screen shows a generic social app upload screen with a photo frame holding a small stylized 3D picture of a kuda lumping dancer (a flat woven horse figure and bright costume, seen from far away, no face detail) and a plain upload arrow button. In the soft-focus background, the back of an adult figure in a batik apron cooks at a clay stove with gentle steam. Warm, homely light.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `tmg-5-a04` — Kabar bohong (family group chat, school holiday)

Slot katalog: `item.skenario.tmg-5-a04` · Situasi di 08: *Di grup keluarga muncul pesan: 'Mulai besok semua sekolah di Temanggung libur seminggu! Sebarkan ke semua grup!' Pesan itu tidak menyebut siapa pembuatnya.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a family-room mat with a woven tikar, showing a generic family group chat. One long speech bubble from a plain grey silhouette avatar holds a small calendar icon (no dates, just a grid), a tiny school-building icon, and grey placeholder lines. Two other short bubbles from other grey avatars. A cup of tea and a plate of fried snacks sit beside the phone. Warm indoor light, Javanese wooden wall in the background.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `tmg-5-b01` — Data pribadi (online drawing contest form)

Slot katalog: `item.skenario.tmg-5-b01` · Situasi di 08: *Kamu ingin ikut lomba menggambar daring. Formulirnya meminta nama, kelas, nomor HP orang tua, dan alamat rumah lengkap. Di halaman itu tidak ada nama penyelenggara.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: Top-down three-quarter view of a child's drawing table: a sketchbook, stylized 3D coloured pencils and a small paint palette, and a tablet. The tablet screen shows a contest page with a decorative paintbrush-and-palette banner, and four blank rounded form fields in a column with grey placeholder bars. The header area where an organizer name would be is simply empty. Warm morning light from a window with a view of green hills.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `tmg-5-b02` — Kata sandi (stranger offers free coins in a game chat room)

Slot katalog: `item.skenario.tmg-5-b02` · Situasi di 08: *Di ruang obrolan game, akun yang tidak kamu kenal menulis: 'Halo, aku bisa kasih koin gratis. Tulis kata sandimu di sini, ya!'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A tablet on a small wooden table showing a generic game lobby with a chat panel on the right. A message from a plain grey silhouette avatar contains a small stack of golden coins icon and grey placeholder lines; beneath it an empty message input bar. The left side shows a few colourful 3D game characters waiting in a lobby. Setting: a cosy living room corner, soft blurred coffee-plantation view through the window.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `tmg-5-b03` — Izin membagikan video diri (father's account, dance video)

Slot katalog: `item.skenario.tmg-5-b03` · Situasi di 08: *Kamu punya video dirimu sedang belajar menari. Kamu ingin mengunggahnya ke akun media sosial milik ayahmu. Ayahmu sedang di kebun kopi.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone resting on a wooden bale-bale bench at the edge of a coffee garden. The screen shows a generic video upload page with a video thumbnail (a tiny distant stylized dance rehearsal scene, no faces) and a play triangle plus a plain upload button. In the soft-focus background, rows of coffee shrubs with red coffee cherries and, far away, a small adult figure seen from behind, working among the plants.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `tmg-5-b04` — Kabar bohong (class group chat, coffee makes kids smart)

Slot katalog: `item.skenario.tmg-5-b04` · Situasi di 08: *Di grup kelas ada pesan: 'Minum kopi bikin anak-anak jadi pintar! Sebarkan ke semua teman!' Pesan itu tidak menyebut siapa pembuatnya.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a school desk beside an exercise book and a pencil case. The screen shows a generic class group chat. One bubble from a plain grey silhouette avatar contains a small coffee-cup icon next to a simple lightbulb icon and grey placeholder lines. Two other short bubbles from other grey avatars. A classroom with a window and green hills behind, soft light.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

## Magelang (sedang)

### `mgl-5-a01` — Data pribadi (wayang personality quiz app)

Slot katalog: `item.skenario.mgl-5-a01` · Situasi di 08: *Kamu mencoba kuis 'Kamu Mirip Tokoh Wayang Siapa?' di sebuah aplikasi. Sebelum hasilnya keluar, aplikasi meminta tanggal lahir, nama lengkap ibumu, dan nama sekolahmu.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A tablet on a low table showing a playful generic quiz app: a decorative frame with elegant wayang kulit puppet silhouettes (gold and navy), and three blank form fields with grey placeholder bars, one with a tiny calendar icon, one with a small person icon, one with a tiny school-building icon. A plain button below. Behind the table, a softly blurred silhouette of Borobudur Temple in early-morning mist.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `mgl-5-a02` — Kata sandi (best friend asks to borrow game account)

Slot katalog: `item.skenario.mgl-5-a02` · Situasi di 08: *Sahabatmu, Laras, berkata, 'Pinjam akun game-mu, dong. Kasih tahu kata sandinya, aku cuma mau lihat-lihat.'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A school veranda bench with a tablet lying on it. The screen shows a generic game login: a name field already filled with grey bars and a password field showing a row of dots. Next to the tablet, only a friendly open hand (stylized 3D, no face, no body) reaches in from the side, palm up. Behind the bench, a school courtyard with a blurred silhouette of Borobudur far away.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `mgl-5-a03` — Izin membagikan foto teman (school dance contest photo)

Slot katalog: `item.skenario.mgl-5-a03` · Situasi di 08: *Saat lomba tari di sekolah, kamu memotret Dimas yang terpeleset dan jatuh terduduk di panggung. Fotonya lucu. Kamu ingin mengirimnya ke grup kelas.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone held in the foreground, showing a photo gallery with several small thumbnails of a school stage with curtains and colourful dance costumes in motion, all far away and blurred, no recognisable person and nobody on the floor. One thumbnail is highlighted with a plain send arrow button below. Behind the phone, a softly blurred school hall stage with bunting. Do not draw anyone falling.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `mgl-5-a04` — Pesan berantai (free Borobudur tickets chain message)

Slot katalog: `item.skenario.mgl-5-a04` · Situasi di 08: *Di grup kelas, seorang teman meneruskan pesan: 'Info dari teman ibuku: tiket masuk Candi Borobudur gratis untuk anak SD selama bulan ini! Sebarkan!' Pesan itu tidak menyebut lembaga atau sumber resmi.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a school desk showing a generic class group chat. A forwarded-message bubble from a plain grey silhouette avatar holds a small ticket icon beside a tiny temple-stupa silhouette and grey placeholder lines, with a small curved 'forwarded' arrow. Two other short bubbles from other grey avatars. Through the classroom window, the blurred shape of Borobudur on a green hill.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `mgl-5-b01` — Data pribadi (video contest about Magelang snacks)

Slot katalog: `item.skenario.mgl-5-b01` · Situasi di 08: *Ada lomba video 'Jajanan Khas Magelang'. Peserta diminta menyebut nama lengkap dan alamat rumah di awal video. Semua video akan diunggah ke akun yang bisa dilihat siapa saja.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a small tripod recording a tray of traditional Javanese snacks (cassava cake getuk in pastel colours, wrapped in banana leaves) on a wooden table. The phone screen shows a generic camera view with a round record button and, in a corner, a small globe icon. Warm market-stall light, a blurred pasar background with hanging baskets.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `mgl-5-b02` — Kata sandi (group-mate asks for learning account password)

Slot katalog: `item.skenario.mgl-5-b02` · Situasi di 08: *Teman sekelompokmu, Arif, berkata, 'Kasih tahu kata sandi akun belajarmu, ya. Biar aku yang mengerjakan tugas kelompok dari rumahku.'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A study desk at home with a laptop showing a generic learning platform: a book icon, a folder labelled only by a plain icon, and a login box whose password field shows a row of dots. Beside the laptop, a phone with a single short chat bubble from a plain grey silhouette avatar holding a tiny key icon and grey placeholder lines. Window with a blurred view of rice fields and Mount Tidar.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `mgl-5-b03` — Izin membagikan foto teman (Topeng Ireng rehearsal photo)

Slot katalog: `item.skenario.mgl-5-b03` · Situasi di 08: *Kamu memotret teman-teman saat latihan Topeng Ireng. Di foto itu, Sekar belum memakai kuluk dan rambutnya berantakan. Kamu ingin mengunggah foto itu ke akun kelas.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone held in the foreground showing a gallery of small thumbnails of Topeng Ireng rehearsal: colourful feathered headdress (kuluk) on a stand, fringed costumes, and dancers seen only as tiny distant blurred silhouettes. One thumbnail is selected, with a plain upload arrow button below. Behind it, a village yard with a gamelan-style drum and soft afternoon light. No close-up of any person.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `mgl-5-b04` — Pesan berantai (getuk makes you sick chain message)

Slot katalog: `item.skenario.mgl-5-b04` · Situasi di 08: *Di grup kelas ada pesan: 'Info dari teman pamanku: getuk yang dijual di pasar minggu ini bikin sakit perut! Jangan beli, sebarkan!' Pesan itu tidak menyebut sumber atau bukti.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a market-stall counter next to a banana-leaf tray of pastel getuk. The screen shows a generic class group chat; one bubble from a plain grey silhouette avatar holds a small getuk icon, a curved forwarded arrow, and grey placeholder lines. Two other short bubbles from other grey avatars. A bustling but gently blurred Sunday pasar behind, warm light.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

## Wonosobo (sulit)

### `wnb-5-a01` — Data pribadi (free trip to Telaga Warna, ID card request)

Slot katalog: `item.skenario.wnb-5-a01` · Situasi di 08: *Akun yang tidak kamu kenal mengirim pesan: 'Selamat! Kamu menang wisata gratis ke Telaga Warna untuk sekeluarga!' ... lalu: 'Kirim foto kartu pelajar dan nomor HP orang tuamu sekarang, kuotanya tinggal 3!'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a table showing a chat with a plain grey silhouette avatar. The first bubble holds a stylized turquoise lake (Telaga Warna) and a gift-box icon; the second bubble, a few moments later, holds a simple student-ID-card icon and a small phone icon, with grey placeholder lines. No counters, no timers, no urgent marks. Behind, a misty Dieng plateau view through a window.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `wnb-5-a02` — Kata sandi dan kode OTP (fake official account asks for code)

Slot katalog: `item.skenario.wnb-5-a02` · Situasi di 08: *Kamu mendapat pesan dari akun bernama 'Tim Resmi Game Bintang': 'Akunmu akan dihapus hari ini! Kirim kode yang baru masuk ke HP ibumu untuk menyelamatkannya.' Tak lama kemudian, HP ibumu menerima SMS berisi kode enam angka.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: Two devices side by side on a wooden table: a tablet with a generic star-themed game and a chat message from a plain grey avatar with grey placeholder lines, and a second smartphone (the mother's) showing a text-message bubble that contains six empty rounded boxes in a row (no digits). Misty Dieng hills through the window, calm neutral light.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `wnb-5-a03` — Izin membagikan obrolan (private chat about a friend's sadness)

Slot katalog: `item.skenario.wnb-5-a03` · Situasi di 08: *Bima bercerita lewat pesan pribadi bahwa ia sedih karena kalah lomba bundengan. Sore harinya, teman-teman di grup kelas bertanya, 'Bima kenapa sih? Kok diam terus?' Kamu masih menyimpan obrolanmu dengan Bima.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a desk showing two chat windows side by side: a private chat with two short bubbles on the left, and a busy class group chat with several grey avatars on the right. Beside the phone sits a bundengan (a woven bamboo dome-shaped Wonosobo instrument) and a small participation trophy. Soft misty window light. No emotion icons.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `wnb-5-a04` — Kabar bohong, dua langkah (landslide photo, old photo)

Slot katalog: `item.skenario.wnb-5-a04` · Situasi di 08: *Di grup kelas beredar pesan dengan foto jalan rusak: 'Jalan ke Telaga Warna putus karena longsor hari ini! Sebarkan!' Temanmu, Fikri, membalas: 'Sudah kusebar ke grup keluarga.' Lalu kamu melihat foto yang sama di sebuah berita lama dari daerah lain.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a school desk showing a class group chat. The main bubble contains a small stylized picture of a mountain road partly covered by fallen earth and a curved forwarded arrow, with grey placeholder lines; a second short bubble from another grey avatar below it. Show ONLY the phone: do not draw any old newspaper or second copy of the photo. Classroom window with a blurred misty hillside.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `wnb-5-b01` — Data pribadi (carica lottery asks for family card photo)

Slot katalog: `item.skenario.wnb-5-b01` · Situasi di 08: *Akun 'Undian Carica Dieng' mengumumkan: 'Ikuti undian, hadiahnya sepeda!' Untuk ikut, peserta harus mengirim foto Kartu Keluarga dan nomor HP orang tua. Temanmu, Sinta, berkata, 'Aku sudah ikut, kok. Katanya aman.'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A flat-lay on a rustic table: a jar and a plate of golden carica (Dieng papaya) pieces, a small toy bicycle, and a smartphone in the centre showing a generic lottery page with a gift-box icon, a document-card icon, and a phone icon, with grey placeholder lines. A second small chat bubble from a plain grey avatar sits at the screen's edge. Soft misty daylight.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `wnb-5-b02` — Kata sandi (fake school-admin login link)

Slot katalog: `item.skenario.wnb-5-b02` · Situasi di 08: *Kamu mendapat pesan dari 'Admin Sekolah': 'Akun belajarmu akan diblokir. Masuk ulang lewat sekolah-kita.example/masuk dan tulis kata sandimu.' Kamu melihat alamat itu berbeda dari alamat situs sekolah yang biasa kamu pakai.*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A tablet on a desk showing a generic web login page: a simple school-building icon, a username bar, and a password bar with dots, and a browser address bar drawn as an empty plain grey bar (no text at all). Next to the tablet, a phone with one chat bubble from a plain grey silhouette avatar with grey placeholder lines. Window with a blurred view of a Dieng school yard and misty hills.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `wnb-5-b03` — Izin membagikan foto teman (photo of a friend asleep on the bus)

Slot katalog: `item.skenario.wnb-5-b03` · Situasi di 08: *Saat studi wisata ke Dieng, kamu memotret Nadia yang tertidur di bus dengan mulut terbuka. Teman-teman di grup kelas tahu, lalu menulis: 'Kirim dong fotonya, pasti lucu!'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: The interior of a school-trip bus: empty seats with a smartphone resting on one seat, its screen showing a class group chat with a few grey placeholder bubbles and a plain camera icon bubble. Through the bus window, the misty Dieng plateau with potato fields. No people visible in the bus and no photo of any person on the screen. Gentle morning light.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```

### `wnb-5-b04` — Kabar bohong, dua langkah (video about embun upas)

Slot katalog: `item.skenario.wnb-5-b04` · Situasi di 08: *Di grup kelas ada video berjudul 'Embun upas bisa membuat orang sakit, jangan keluar rumah pagi-pagi!' Video itu tidak menyebut sumber. Temanmu, Fikri, membalas: 'Sudah kukirim ke grup keluarga.'*

```text
Premium stylized 3D animated adventure game illustration for Indonesian children aged 8-14. Softly rounded dimensional forms, gentle cinematic depth of field, warm late-morning light. Palette: brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream, soft natural greens. Same finish and mood across the whole set, as one family of pictures. No painted brush strokes, no paper texture, no photorealism, no anime outlines.

SCENE: A smartphone on a desk by a window showing a class group chat. The main bubble contains a video thumbnail of a misty Dieng morning with frosty potato fields and a plain play triangle, plus grey placeholder lines; a second short bubble from another grey avatar below. Outside the window, a soft frosty-white field in morning light. No danger, no sickness, no skull, no warning icons.

FORMAT: Landscape 3:2, 1536 x 1024 pixels. Keep the main subject inside the central 85% of the frame (the game may crop the edges slightly). Opaque full-bleed background, no border, no frame.

STRICT RULES. (1) No readable text anywhere: no letters, numbers, words, URLs, or phone numbers. Wherever text would appear (messages, forms, web pages, buttons, signs), draw soft grey bars or short wavy lines as placeholders. (2) No real brands, app logos, or platform names; any device and app is generic and made up. (3) The picture must not hint at the right answer: no warning triangles, exclamation marks, red alert colours, skulls, padlock-with-cross icons, shield or check-mark badges, thumbs up or down, smileys, or sad or worried faces. Use calm, neutral lighting and the same neutral mood for every picture. (4) No people's faces are shown. If a person is part of the scene, show them only as a small, distant, softly blurred figure seen from behind. Never show a child in an embarrassing or distressing pose. (5) No watermarks, signatures, extra limbs, blurry edges, neon colours, clutter, or fake checkerboard transparency.
```
