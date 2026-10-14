# Prompt dan spesifikasi gambar

## 1. Lanskap yang sudah dibuat 12 Oktober — dipakai ulang

Prompt utuh: [prompt-lanskap.txt](prompt-lanskap.txt). Tool: built-in image_gen; satu generasi lanskap baru, memakai gambar mockup pilihan dan reference.01 sebagai input. Reference.01 hanya acuan finishing 3D, bukan instruksi menambahkan Jaka.

Hasil: [lanskap 1920 × 1080](aset-baru/bg-tantangan-selesai-temanggung-3d.webp). Keluaran asli disimpan sebagai WebP dalam [provenance](../../../provenance/mockup-tantangan-selesai-temanggung/bg-tantangan-selesai-temanggung-3d-asli.webp).

Brief: lanskap petualangan 3D senja terinspirasi Kedu/Temanggung, gunung berlapis dengan kabut biru, teras hijau di kiri, air dan refleksi hangat di kanan. Tidak memuat UI, tulisan, karakter, lentera, medali, atau bingkai. Bagian di balik panel direkonstruksi; tidak diklaim sebagai potongan identik dari mockup. Target opak 1920 × 1080. Konversi/normalisasi ukuran deterministik tidak menambah detail AI.

## 2. Brief reproduksi mockup pilihan

Brief berikut adalah panduan membuat referensi serupa di masa depan, **bukan klaim prompt historis persis** yang menghasilkan gambar pilihan. Jangan meregenerasi mockup pilihan hanya untuk memperbaiki ukuran keluaran.

```text
Use case: ui-mockup.
Create one full-screen GELITA educational adventure game result screen.
Fixed 16:9 landscape canvas, 1920 x 1080; no portrait layout or responsive
reflow. Reference: the selected mockup-tantangan-selesai-temanggung-16-9.webp.

Use premium stylized 3D sunset mountain-valley art; midnight navy
#0B1320/#1B2740 panels, brass gold #DFC087/#F0DFB8/#B58137 edges,
warm ivory text and restrained emerald success accents. Fine gold
ornaments, dimensional objects, soft cinematic light, no painted texture.

Top HUD floats over landscape without a background strip. Left:
existing GELITA wordmark, divider, Temanggung. Right: actual catalogue
ID language medallion, clear gold speaker, hamburger menu. No home,
fullscreen, profile, logout, lantern or collection counter on the HUD.

One wide navy result panel with thin gold frame. Small title SUSUN GAMBAR,
large Tantangan selesai!, exactly two filled gold stars and one unfilled
blue outline star. No lantern or emblem above the heading.

Left body: EXACT existing avatar.2 medallion, label Serpihan cahaya,
value 1 / 12, twelve progress slots, only one lit gold.
Right body: two stat cards only, Waktu 2:05 and Percobaan 2 dari 3.
Green check badge Semua benar. Encouraging text:
Kamu mencoba 2 kali. Terus berusaha itu hebat!

Bottom row: back arrow + Peta Temanggung, collapsed Detail hasil with
chevron, dominant gold Berikutnya button with right arrow.
Language and menu are closed. When implemented, language opens actual
ID/EN icons with Bahasa Indonesia / English. Menu groups Home/Peta Kedu
according to actual routes, fullscreen, profile/avatar and logout.

Keep approved composition. Normal labels and buttons remain readable
after uniform game scaling to a landscape phone. UI numbers here are
example state only. No XP, currency, new +1 reward, watermark, phone
hardware, extra lantern, duplicated progress or third statistics card.
```

Gambar AI tidak membuktikan ukuran klik. Implementasi tetap harus mengukur target sentuh setelah penskalaan, seperti dijelaskan dalam [keterangan](keterangan-mockup.md).

## 3. Ikon layar penuh — spesifikasi vektor native

Dibuat sebagai SVG, bukan generasi raster. Ikon masuk dan keluar berada di [aset-baru](aset-baru/). Keluarga katalog tidak memiliki slot `icon.fullscreen` pada pemeriksaan ini.

```text
Create an SVG UI control for GELITA on a 24 x 24 viewBox.
No background disc; fill none; stroke width 2; rounded caps and joins.
Use the catalogue gold gradient: #F0DFB8 at 0%, #DFC087 at 45%,
#B58137 at 100%. Four L-shaped corners with clear optical margins.
Enter-fullscreen: corners point outward around the outer square.
Exit-fullscreen: corresponding corners point inward.
Include an accessible SVG title. No words drawn into the geometry,
lantern, micro-ornaments, raster textures, glow or new logo.
```

## 4. Aset cukup dipakai ulang

Medali serpihan, logo, ID/EN dan seluruh ikon kontrol yang terdaftar di [kebutuhan aset](kebutuhan-aset.md) tidak memerlukan prompt generasi ulang. Gunakan sumber aslinya. Progres dan state dibuat melalui kode; panel/tombol/bintang memakai gambar revisi 13 Oktober dan glow medali memakai overlay SVG.

Jika slot baru kemudian memerlukan gambar besar melebihi sumber yang ada, tulis brief baru dengan acuan sumber tersebut dan catat generasi aktual; jangan menganggap pembesaran biasa sebagai tambahan detail.

## 5. Brief UI lengkap dan sumber yang sudah dibuat

Riwayat sketsa 12 Oktober: sumber delapan komponen UI SVG telah dibuat di `aset-ui/` dan dahulu dirangkai dalam preview. Panel/tombol/HUD utama kini diganti WebP revisi 13 Oktober; kartu dan overlay masih dipakai. Ini pembuatan vektor/kode native; tidak ada klaim delapan gambar AI terpisah. Gunakan brief berikut jika mengembangkan bahan UI selanjutnya:

```text
Deliver a complete GELITA result-screen UI kit, not a landscape-only image.
Keep the selected first 16:9 landscape mockup as the visual reference.
Include: navy result-panel skin with fine gold double border and restrained
corner ornaments; beveled warm-gold primary-button skin; navy outlined
secondary-button skin; round navy/gold HUD control skin; compact navy
stat-card skin; matching language-popover, menu, and result-detail skins.
Colours: navy #0B1320/#1B2740, gold #F0DFB8/#DFC087/#B58137.
Use scalable SVG with clean geometry. Decorative layers contain no
embedded labels, numbers, icons, lanterns, character or reward artwork.
UI text, click targets, statistics, progress and interaction states are
HTML/CSS/JS layers above these skins. Reuse catalogue logo, shard medallion,
ID/EN pictures, and control icons. Show main screen, language choices,
grouped menu and first-attempt detail as complete 16:9 screen states.
Keep fixed-canvas scaling; do not create portrait responsive reflow.
```

## Prompt aktual panel dan tombol — 13 Oktober

Sumber rujukan adalah screenshot full UI pilihan pengguna, bukan lanskap saja. Target tiap gambar hanya satu skin transparan tanpa label; label dibuat oleh aplikasi.

### ui.result-panel

Ukuran: 1536 × 752; metode: built-in image_gen.

```text
Edit ONLY the empty panel in image 1 to match image 2 (the approved GELITA screenshot) much more precisely. Keep the exact navy forest silhouettes, mountain and cloud engraving, quiet interior and fine corner ornament. The gold rim in image 1 is too thick: reduce total gold-frame thickness to roughly 40 percent of its current thickness. The desired rim is a slender 4-7 pixels on a 1536px-wide panel, with subtle double hairlines and a very small bevel, not a broad brass picture frame. Make the actual panel outline proportions about 2.05:1 (wide 1536 x 752), with only 4-8px transparent padding around its outer contour, not large blank margins. Top and bottom should be straighter, with restrained softly rounded corners, no extra raised cloud lobes. Transparent alpha outside the panel. Navy interior solid/near opaque. NO text, no UI contents, no stars, no cards, no medallion, no lines dividing columns, no buttons. Return only this single blank standalone panel artwork.
```

### ui.result-button-primary

Ukuran: 600 × 132; metode: built-in image_gen.

```text
Use case: precise-object-edit / standalone UI asset. Input image: approved full UI screenshot; it is the sole appearance reference.
Create ONLY the blank gold primary button skin of the Berikutnya button in the supplied GELITA screenshot. Target 600 x 132, pill silhouette about 5.9:1 when used. Warm light antique-gold enamel central surface, dimensional rounded bevel, thin raised gold rim, top edge glossy warm white-gold highlight, soft amber halo just outside rim, low gold shadow below, subtle gold-on-gold cloud/batik scroll engraving concentrated at far left and far right ends. Centre remains clear for dynamic black text. Closely match the original screenshot's button colour, restrained brightness, edge and pattern. Whole pill fully visible, very small transparent padding. NO text, NO letters, NO arrow, NO icon, NO label, NO landscape, no other buttons. Background outside pill is truly transparent. Orthographic straight-on premium stylized 3D adventure-game UI texture.
GELITA active direction: polished stylized 3D adventure for Indonesian grade-five children, navy and brass gold, no painterly textures, no watermarks. Preserve all design cues of the reference. Output exactly one isolated asset, not a screenshot or UI kit sheet.
```

### ui.result-button-secondary

Ukuran: 480 × 112; metode: built-in image_gen.

```text
Use case: precise-object-edit / standalone UI asset. Input image: approved full UI screenshot; it is the sole appearance reference.
Create ONLY the blank dark secondary map button skin from the supplied GELITA screenshot, same as Peta Temanggung but WITHOUT any lettering or arrow. Target 480 x 112, wide pill proportions about 5:1. Deep navy lightly translucent-looking enamel centre, delicate raised warm-gold border, fine pale-gold top rim highlight, rounded capsule ends, subtle dark shadow and depth, quiet clean empty interior. Match screenshot exactly in colour and restrained bevel. Entire button visible with very little transparent padding. No text, no glyphs, no ornament in centre, no scenery, no extra objects. Real transparent alpha outside pill. Straight-on UI texture.
GELITA active direction: polished stylized 3D adventure for Indonesian grade-five children, navy and brass gold, no painterly textures, no watermarks. Preserve all design cues of the reference. Output exactly one isolated asset, not a screenshot or UI kit sheet.
```

### ui.result-button-detail

Ukuran: 320 × 112; metode: built-in image_gen.

```text
Use case: precise-object-edit / standalone UI asset. Input image: approved full UI screenshot; it is the sole appearance reference.
Create ONLY the blank Detail hasil button skin from the supplied GELITA screenshot. Target 320 x 112, rounded rectangle proportions about 3.3:1 rather than capsule. Deep navy enamel with understated central blue highlight, thin bronze-gold outline, corners radius about 20 percent of height, slight 3D edge and dark outer shadow. Empty centre for dynamic label. Closely match original screenshot's Detail hasil shape and material. NO text, NO chevron, NO arrows, no labels, no landscape, no other assets. All edges visible with tiny transparent outer margin, truly transparent background outside button. Straight-on premium adventure-game UI texture.
GELITA active direction: polished stylized 3D adventure for Indonesian grade-five children, navy and brass gold, no painterly textures, no watermarks. Preserve all design cues of the reference. Output exactly one isolated asset, not a screenshot or UI kit sheet.
```

### ui.result-hud-control

Ukuran: 128 × 128; metode: built-in image_gen.

```text
Use case: precise-object-edit / standalone UI asset. Input image: approved full UI screenshot; it is the sole appearance reference.
Create ONLY one empty circular navy-gold HUD button base matching the speaker/menu/ID controls in the supplied GELITA screenshot. Target 128 x 128. Deep midnight navy softly dimensional face, fine beveled antique-gold rim, subtle lighter highlight upper-left, restrained soft shadow/glow outside, circular symmetrical contour. Whole disc in frame with very little transparent padding. Centre completely empty: NO ID, no EN, no letters, no speaker, no hamburger, no icons, no symbols, no lantern. Genuine transparent background outside the disc. Match screenshot precisely, not an overornate medallion.
GELITA active direction: polished stylized 3D adventure for Indonesian grade-five children, navy and brass gold, no painterly textures, no watermarks. Preserve all design cues of the reference. Output exactly one isolated asset, not a screenshot or UI kit sheet.
```

### ui.result-star

Ukuran: 128 × 128; metode: built-in image_gen.

```text
Use case: precise-object-edit / standalone UI asset. Input image: approved full UI screenshot; it is the sole appearance reference.
Create ONLY one filled earned rating star matching the two glowing gold stars in the supplied GELITA screenshot. Five-point star, slightly rounded points, dimensional bevel, warm pale-gold face, fine luminous edge, controlled amber glow, softly embossed adventure-game 3D appearance. Target 128 x 128. Whole star and small glow entirely visible with modest transparent margin. No face, no text, no extra stars, no number, no circle badge, no background scene. Genuine transparent background outside the star and its subtle glow. Faithfully match the screenshot's star shape and lighting.
GELITA active direction: polished stylized 3D adventure for Indonesian grade-five children, navy and brass gold, no painterly textures, no watermarks. Preserve all design cues of the reference. Output exactly one isolated asset, not a screenshot or UI kit sheet.
```

### bg.result.temanggung

Ukuran: 1920 × 1080; metode: promoted existing 2026-10-12 background; no new generation.

```text
Use case: stylized-concept / background-only asset.
Create the clean standalone landscape background needed by the approved GELITA challenge-complete mockup in input image 1. Image 1 is the chosen layout and landscape reference, but remove ALL interface content and reconstruct the entire landscape behind it coherently. Image 2 (GELITA reference.01, Jaka character sheet) is ONLY a reference for premium polished stylized 3D animated adventure finish and materials; DO NOT include Jaka, any character or any text from that sheet.

Asset destination: mockup reference package, background for fixed 16:9 landscape mobile game. Target canvas 1920 x 1080, wide exact 16:9. Full bleed opaque background, no alpha.

Scene and composition:
An imaginative scenic landscape inspired by the Kedu/Temanggung mountain valleys, not a factual geographic map. Sunset with layered volcanic mountain silhouettes receding into cool blue mist, warm amber sun in the upper-right quarter, rich orange-pink-violet cloud formations. Across the left foreground and middle distance, lush dimensional green forest foliage and terraced cultivated slopes sweep down into the valley. Across the right third, a calm lake or broad river reflects the gold sunset with gentle highlights. Complete the broad centre with low-detail terraces, valley floor, distant vegetation and atmospheric water so it remains quiet beneath a future result panel. Foreground foliage softly frames lower corners without blocking the view. Closely match the approved screenshot's warm golden sunset, depth, camera angle, terrain and lake placement while creating a complete original backdrop behind the missing panel.

Style:
Premium stylized 3D animated adventure environment for Indonesian grade-five pupils, cohesive with GELITA's Jaka reference. Rounded dimensional vegetation, rich but restrained material detail, soft physically plausible lighting, cinematic depth and atmospheric perspective. Colour palette: deep navy #0B1320 and #1B2740 shadows, brass-gold warm light #DFC087 and #F0DFB8, natural greens, earthy browns, warm sunset coral and cool blue haze. No painterly brush texture or paper grain. Distinct separation of foreground, midground and mountains.

UI-safe composition:
The central approximately 75 percent width and middle 65 percent height should have low visual complexity so a navy result panel can be layered there later. Keep upper-left and upper-right HUD locations reasonably calm. Lighting remains beautiful without harsh white glare beneath possible controls. No embedded dark panel or added HUD strip. All panel edges, UI text, buttons, stars, medallions, icons and particles will be rendered separately by the web app.

Strict exclusions:
NO letters, words, GELITA logo, labels, interface, buttons, borders, gold frame, HUD, cards, stars, medallion, crystal shards, lanterns, reward symbols, characters, foreground people, watermark, signature, diagram, phone hardware or duplicate screenshot. Do not invent a specific identifiable temple or place claim. Do not reproduce the reference character sheet. The output is landscape art ONLY. Deliver one full-bleed clean 16:9 background.
```

### ui.result-shard-effects

Ukuran: 512 × 512; metode: native SVG glow, arcs and sparkles; existing avatar.2 unchanged.

```text
Create a transparent 512x512 native SVG effect overlay for the existing GELITA avatar.2 shard medallion. Warm gold halo, three subtle curved light streaks and a few tiny four-point sparkles, exact warm palette #FFF2BD #F3CA73 #DFAB49. Leave central medallion identity unchanged; no words, new crystal, lantern, characters, frame or opaque background. Use restrained blur only for glints and the ring.
```

Panel memiliki dua percobaan; prompt awal dan koreksi tersimpan lengkap pada manifest generasi. Efek SVG native tidak disebut generasi AI.

## Ikon bahasa bendera — prompt aktual 13 Oktober

Brief mockup sebelumnya adalah riwayat keputusan ID/EN. Keputusan aktif kini bendera Indonesia/UK dalam medali navy emas. Tool: built-in ImageGen, dua panggilan; keluaran dinormalisasi proporsional ke WebP 256 × 256 tanpa menggambar ulang bendera.

### icon.language-id

```text
Use case: stylized-concept. Asset type: standalone language selection icon for GELITA mobile game UI. Reference image is ONLY a reference for the circular navy and antique gold HUD rim, materials and restrained bevel. Create a single perfectly round front-facing 3D UI medallion on genuine transparent background, target design 256x256. A slender antique-gold raised rim surrounds a midnight navy recessed face. Inside is a large flat enamel national flag with subtly rounded rectangular corners, centred, crisp and easy to recognize at 32px. Keep the flag large (about 70% of disc diameter wide) and its flag colours faithful, not tinted gold. Rim and silhouette should match the reference; controlled top-left highlight and very small shadow. No pole, waving cloth, text, letters, country name, ID, EN, extra symbols, landscape, character, watermark, extra medallions or UI kit sheet. Entire rim visible, small transparent padding only. Flag: Indonesia, precisely two equal horizontal bands, RED top (#E32219) and WHITE bottom, rectangular ratio 3:2. No blue, no emblem, no extra bands. The flag is fully visible and not clipped by the rim.
```

### icon.language-en

```text
Use case: stylized-concept. Asset type: standalone language selection icon for GELITA mobile game UI. Reference image is ONLY a reference for the circular navy and antique gold HUD rim, materials and restrained bevel. Create a single perfectly round front-facing 3D UI medallion on genuine transparent background, target design 256x256. A slender antique-gold raised rim surrounds a midnight navy recessed face. Inside is a large flat enamel national flag with subtly rounded rectangular corners, centred, crisp and easy to recognize at 32px. Keep the flag large (about 70% of disc diameter wide) and its flag colours faithful, not tinted gold. Rim and silhouette should match the reference; controlled top-left highlight and very small shadow. No pole, waving cloth, text, letters, country name, ID, EN, extra symbols, landscape, character, watermark, extra medallions or UI kit sheet. Entire rim visible, small transparent padding only. Flag: United Kingdom Union Jack, rectangular ratio 2:1. Deep blue field (#012169), upright red Saint George cross edged in white, broad white diagonal Saint Andrew saltire with narrower offset red diagonal Saint Patrick saltire. Correct recognizable asymmetric Union Jack, not a symmetric red X and not a USA flag. The entire rectangular flag remains visible without crop.
```
