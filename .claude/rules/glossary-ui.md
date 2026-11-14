---
paths:
  - "app/Views/**"
  - "app/Helpers/**"
  - "public/assets/css/**"
  - "public/assets/js/**"
  - "public/assets/ui/**"
---

# Glossary: screens, pictures, and CSS hooks

Continues `glossary.md` (same columns, same rules). Add screen parts and UI pictures here.

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| Tantangan selesai (layar hasil) | `result screen`, `finished` | route `/selesai/{id}`; slots `ui.result-*`, `bg.result.{wilayah}` |
| Detail hasil | `result details` | popover on the result screen |
| menu (HUD baru) | `menu HUD`, `compact HUD` | `components/hud-menu` on every page: full size with section `hudStyle` = `menu`, else `.is-compact`; `hudShards` = `off` hides the shard pill; `hudRegion` = `off` hides the region name (region map, since 21 Oct 2026) |
| kartu misi | `mission card`, `brief` | `game/mission-brief`, `.mission-card`, `.mission-seal` |
| tombol utama | `main button` | `.btn-primary` on `#stage`, on the gold pills (`gold-pill-*`, since 22 Oct 2026; the `ui.result-button-primary` skin no longer shows) |
| diorama Peta Kedu (peta pulau) | `diorama`, `island map` | slot `map.kedu-diorama` (the owner's island picture since 20 Oct 2026, catalogue `mockup/…/peta-kedu/revisi-05`); `Config\Gelita::$mapDiorama` |
| bagian wilayah yang terkunci (gelap) | `region shade`, `region mask` | `img.kedu-shade` through `public/assets/ui/kedu-region-{wilayah}.webp` (feathered land mask, rivers as borders) |
| avatar Jaka (potret cerita Peta Kedu, layar Kenali) | `Jaka avatar`, `portrait` | slot `ui.jaka-avatar`, the owner's framed picture `gelita-katalog/mockup/gambar-mockup/kenali/aset/` (since 19 Oct 2026; was `reference.03`); `partials/kedu-portrait` |
| avatar Mbah Kedu (layar Kenali) | `Mbah Kedu avatar` | slot `ui.kedu-avatar` from catalogue `reference.02`; `.kenal-avatar` (round, on the caption's left end) |
| papan nama (layar Kenali, cerita Peta Kedu) | `nameplate` | `.kenal-nameplate` on the navy plate, over the avatar ring's lower part; on Peta Kedu inside `.kedu-speaker` (since 20 Oct 2026) |
| papan tombol emas / biru (layar Kenali) | `gold plate`, `navy plate` | `public/assets/ui/kenal-plate-{gold,navy}.webp`; Lanjut gold, Kembali navy on Kenali; the navy plate also backs the challenge answer choices (`--plate-navy`: Isi Rumpang words, Benar/Salah), since 20 Oct 2026 |
| cincin nomor tantangan | `challenge badge`, `number ring` | `public/assets/ui/challenge-badge.webp`; `.badge-pos` in the challenge header |
| bilah tombol tantangan | `action bar` | `.arena-actions` (32, 648, 1216 × 56): gold `.btn-action` between two gold rules; `#btn-check`, `#btn-next` |
| medali status tantangan | `status badge`, `status medallion` | `status_badge_src()`: three states only (owner, 20 Oct 2026): `public/assets/ui/status-done.webp` (green ✓) = completed, `status-progress.webp` (gold arc, no number since 21 Oct 2026) = open, `status-open.webp` (navy) + a lock = locked; on the shard dots, region-map pins and challenge list (`.node-no`), and the Peta Kedu done badge. No "started"/"Sedang dikerjakan" state. Every badge picture (and the challenge number ring) wears `--badge-halo`, a black rim that follows its outline |
| papan Serpihan Cahaya (HUD) | `shard plate` | the owner's carved frame `public/assets/ui/hud-shards-frame.webp` (about 4.9 : 1, empty groove, small spaced label above the groove) on `.hud-shards .lantern-toggle`, passed as `--shards-frame`; the bar is filled by the page from the real count with `hud-shards-fill.webp` (`--shards-fill`) plus glow and sweep; the frame's crystal glows per completed region (`.lantern-gem`, `data-stage`), since 20 Oct 2026 |
| kartu tantangan (peta wilayah) | `challenge card` | `.node-card` in `.map-side`; engine picture `public/assets/ui/node-card-{engine}.webp`, in sepia inside the green scarf frame `node-card-frame.webp` (`.has-frame`, since 20 Oct 2026); per status since 21 Oct 2026: bright sepia done, darker sepia open, grey locked |
| papan judul wilayah (peta wilayah) | `head plate` | `public/assets/ui/map-head-plate.webp` (`--head-plate`), `.map-head.has-plate`: the region name on the owner's ornate navy plate, since 21 Oct 2026 |
| bilah kemajuan perunggu (peta wilayah) | `progress track` | `public/assets/ui/map-progress-track.webp` (`--progress-track`), `.map-progress.has-track`: gold fill in its channel, a notch per challenge (`--steps`), since 21 Oct 2026 |
| lambang kompas (panel berornamen) | `crest`, `compass crest` | `public/assets/ui/ornate-crest.webp` (the owner's emblem, 20 Oct 2026), `.ornate-crest` in `partials/ornate-frame`, `.gate-crest` on `/gerbang` |
| lambang kartu gerbang | `gate emblem` | `public/assets/ui/gate-icon-{intro,map}.webp` (book, map scroll), `.start-emblem` on the two `/gerbang` cards |
| logo di tengah HUD | `logo-centre` | section `hudLayout` = `logo-centre` (`.is-logo-centre`): `/mulai`, `/masuk`, `/daftar/1–3`, `/gerbang`; no shard plate; Kembali (`hudBack`) on the navy plate in the top-left corner (`.hud-back-plate`, since 20 Oct 2026) |
| nama wilayah di tengah HUD (tantangan) | `region-centre` | section `hudLayout` = `region-centre` (`.is-region-centre`, `span.hud-region-centre`): the four challenge screens, logo left, no shard plate (since 21 Oct 2026) |
| Ketuk layar untuk melanjutkan | `tap prompt` | `Game.tapToContinue`, `button.result-kedu-tap` on the region-complete card (replaces the close cross, 21 Oct 2026) |
| tombol Kembali berpapan (HUD) | `back plate` | `a.hud-back-plate` in the HUD's top-left corner on the `logo-centre` pages (Mulai, Masuk, Daftar, Gerbang, mission brief, region-done scene), on the navy plate `kenal-plate-navy.webp` (`--plate-navy`), since 20 Oct 2026; section `hudBackLabel` replaces "Kembali" (Peta wilayah on `/tuntas`) |
| efek sinematik pembuka | `cinematic entrance`, `cue sheet` | `game/cine.js`, `data-cine` = `welcome`/`gate`/`account`; `html.cine-ok`, `.is-cine-live`, `.is-cine-done`; session key `gelita.cine.seen` |
| cahaya gambar latar (matahari) | `picture light`, `light source` | `findLight()` in `game/cine.js`; `createScreenFx(…, { light })`, effect `sun` (welcome page) |
| tombol bulat emas | `round button`, `round icon` | `round_icon()`/`round_icon_src()`/`round_style_attr()`, `public/assets/ui/round-{name}.webp` (256 × 256): sheet 1 (21 Oct 2026) `profile, login, register, lang-id, lang-en, sound, mute, menu, back, next, pause, loading, replay, blank`; sheet 2 (same day) `help, info, lock, play, map, clock, logout, close, wrong, right, neutral, ring-green, eye`. HUD controls and menu rows, Mulai/Gerbang cards, narrator controls (`--round-art`, `--round-art-play`), stepper (neutral / green ring with the number), password rules (neutral / wrong / right), eye button, Kelas lock, letter avatars (`--round-neutral` on `#stage`), busy ring (`--round-loading`) |
| papan emas polos bertulisan | `gold plate`, `gold plate button` | `gold_plate_button()`, `.btn-gold-plate.is-{short,medium}`, `public/assets/ui/plate-gold-{1..4}.webp` (blank, the word written on it): Masuk, Daftar (`short` = 3), Berikutnya (`medium` = 1), since 21 Oct 2026; replaces the lettered `btn-{login,next,register}` plates |
| papan Pasang GELITA | `install plate` | `.btn-install-plate`, `public/assets/ui/btn-install.webp` (download medallion on the right), welcome page bottom-right corner |
| Tentang GELITA | `about page` | route `/tentang` → `HomeController::about`, `game/about`; HUD menu row `.hud-about` on every page |
| Masuk staf (menu) | `staff login row` | `.hud-staff` in the HUD menu, only before a student signs in (was the welcome-page corner link `welcome-staff` until 21 Oct 2026) |
| latar gerbang gelap (Mulai, Daftar, Tentang) | `dim art`, `gate picture` | `public/assets/ui/bg-mulai.webp` + `body.is-dim-art` (deep dark layer); panels `.ornate-panel.is-bare` (no fill, border, shadow, ornaments) |
| papan tombol hijau | `green plate` | `public/assets/ui/plate-green.webp` (`--plate-green`): Lihat hasil on the region map |
| papan tombol berukir (Kembali/Ulangi, Lanjut, Masuk) | `carved plate`, `button plate` | `plate_attrs()` with `back`, `next`, `enter`, `.btn-plate.is-{kind}`; `public/assets/ui/plate-{back,next,enter}.webp`; stretches to its label (border image), since 21 Oct 2026 |
| papan Balai Refleksi (gambar bertulisan) | `reflection plate` | `picture_link('reflection', …)`, `public/assets/ui/btn-reflection.webp` / `btn-reflection-en.webp`; Peta Kedu, the ending's final button, the profile nav, the Balai Refleksi heading (since 21 Oct 2026) |
| narasi Peta Kedu setelah semua tuntas | `map_done` (story context) | `dialogues.context_code` `map_done`, recordings `peta-tuntas-NN`; replaces `map_intro` on `/peta` once every challenge is done (since 21 Oct 2026) |
| Lihat cerita penutup (Peta Kedu) | `closing story link` | `.kedu-opening.kedu-ending` → `/penutup`, label `Game.watchEnding` (was "Tonton penutup" until 21 Oct 2026) |
| cahaya mengitari wilayah dipilih | `selected-region orbit` | `.kedu-city[aria-current] .kedu-city-caption::before/::after`, `@property --orbit`; gold halo `.kedu-medal::before` (since 21 Oct 2026) |
| gambar buku Pustaka | `library icon` | `library_icon()`, `public/assets/ui/icon-pustaka.webp` (the owner's round book emblem since 21 Oct 2026, 256 × 299), `.library-icon`; on the Pustaka buttons, the region-library nav (`'icon' => 'library'`), and `.shelf-emblem` on the Pustaka Kedu cards |
| bilah pemuatan perunggu | `loading track` | `public/assets/ui/loading-track.webp` (`--loading-track`), `.curtain-beam.has-track` on every curtain and `.progress` on the Pustaka Kedu cards; gold fill in its channel, since 21 Oct 2026 |
| gambar bintang | `star picture` | `star_src()`/`star_img()`/`stars_html()`: slot `ui.result-star`, else `public/assets/ui/star.webp`; empty star `.is-off` (grey, faded); admin keeps the line icon |
| Wilayah dipilih (panel) | `region panel`, `selected region` | `.kedu-panel`, `data-region-select` |
| Lihat cerita pembuka (panel wilayah) | `opening story link` | `.kedu-opening` → `/dialog/{code}`, label `Game.watchRegionOpening` |
| tingkat kesulitan (wilayah) | `difficulty`, `difficulty badge` | stored `mudah`, `sedang`, `sulit` in `levels.difficulty`; `.region-difficulty` (`partials/region-difficulty`) in the HUD section `hudBadge` on `/dialog/{code}` (body class `is-region-dialogue`), and `.curtain-difficulty` on the region curtain (since 21 Oct 2026) |
| kartu sambutan (setelah daftar) | `welcome card`, `welcome modal` | flash `welcome`; `.welcome-card`, opened as a modal by `game/intro.js` |
| latar slide (cerita) | `slide background`, `backdrop` | `dialogues.background_media_id`; slot `bg.story.{context}[.{wilayah}].{n}` (`DialogueModel::backgroundKey()`); `data-bg` on the slide, `core/backdrop.js`; catalogue `bg.cerita-{intro, penutup, kenal-{wilayah}, dialog-{wilayah}, tuntas-{wilayah}}-NN` |
| musik latar | `background music`, `track` | `public/assets/audio/music/{name}.mp3`; layout section `music` → `<body data-music>`; `Sfx.music()`; scene tracks `welcome`, `opening`, `ending`, `kenal-`/`dialog-`/`tuntas-{wilayah}` |
| musik mengecil saat narasi | `ducking` | `core/audio.js` `DUCKED` (0.2), `narrationChanged()` |
| efek layar 3D | `screen effects`, `screen fx` | `game/screen-fx.js` (three.js); `data-fx3d`, `screen_fx_attr()`, `.has-fx3d`; switch `Config\Gelita::$screenFx` |
| paralaks | `parallax` | `game/parallax.js`; `--plx`/`--ply` and `.has-parallax` on `#stage` |
| bingkai dialog | `dialog frame`, `framed dialog` | `public/assets/ui/modal-frame-4x3.webp` (`--modal-frame-compact`) and `modal-frame-16x9.webp` (`--modal-frame-wide`), cut from the owner's 4:3 and 16:9 sheets; `data-frame` and `--modal-w` set by `fitFrame()` in `core/modal.js`; game only, also the picture viewer (since 22 Oct 2026) |
| papan emas kapsul (tombol utama) | `gold pill` | `public/assets/ui/gold-pill-{1..4}.webp` (`--gold-pill-n` on `#stage`, `stage_picture_urls()`): the face of every `.btn-primary` and gold main button since 22 Oct 2026, replacing the `ui.result-button-primary` skin; `.btn-danger` wears it turned red by a filter (same day) |
| koin kesempatan, koin serpihan | `try coin`, `shard coin` | `.tries-dot`, `.result-shards-bar i`: the round `blank` face (`--round-blank`) left/earned, the `neutral` face used/missing (since 22 Oct 2026) |
| daftar pilihan (dropdown) | `dropdown`, `fancy select` | `core/select.js`, `select[data-fancy-select]`, `.fancy-select-*`; gold triangle `public/assets/ui/select-arrow.webp` (`--select-arrow`) |
| salinan ringkas (JS/CSS) | `minified mirror`, `minified assets` | `public/assets/min/{version}/`; command `gelita:aset:minify`; `AssetMinifier`; switch `Config\Gelita::$minifyAssets` |
