# Glossary

One concept, one word, in every layer. "UI (id)" is what users read; "Code (en)" is the identifier to use in new code; "Stored" is the value already saved in the database or exports (never rename it).

## Game (students)

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| GELITA, Mbah Kedu, Jaka | — | Proper names; never translate. `mbah_kedu` → slug `kedu` |
| wilayah | `level`, `region` (map) | table `levels`; route `/wilayah/{code}` |
| peta | `map` | `MapController` |
| gerbang | `gate` | `GateController` |
| tantangan | `challenge`, `node` (a slot on the map) | `challenge_nodes` |
| misi (layar sebelum tantangan) | `brief` | route `/misi/…` |
| soal, butir | `item` | `challenge_items` |
| pengerjaan | `attempt` | `challenge_attempts` |
| kesempatan | `tries` (limit is 3) | `TRIES_EXHAUSTED`, `TRIES_REMAINING` |
| percobaan | `try` | `item_tries` |
| petunjuk (bantuan di tantangan) | `hint` | `hints`, `hint_used` |
| petunjuk Cari Objek | `clue` | `CLUE_CLOSED`, `clue_failed` |
| bintang | `stars` | rule `bintang-1` |
| Serpihan Cahaya | `shard` | `shards_total` |
| tuntas | `completed` / `done` | status `completed` |
| Susun Gambar | `puzzle` engine | `puzzle`, `puzzle_arrange` |
| Isi Rumpang | `rumpang` engine (cloze) | `rumpang`, `rumpang_bacaan` |
| Benar atau Salah | `boleh` engine (true/false cards) | `boleh` |
| Cari Objek | `cari` engine, `hunt` | `cari`, `cari_objek` |
| keping | `piece` | puzzle pieces |
| bacaan | `passage` | `reading_passages` |
| pustaka | `library` | `library_pages` |
| narasi, rekaman | `narration`, `audio asset` | `audio_assets` |
| refleksi | `reflection` | `ReflectionController` |
| Tantangan selesai (layar hasil) | `result screen`, `finished` | route `/selesai/{id}`; slots `ui.result-*`, `bg.result.{wilayah}` |
| Detail hasil | `result details` | popover on the result screen |
| menu (HUD baru) | `menu HUD`, `compact HUD` | `components/hud-menu` on every page: full size with section `hudStyle` = `menu`, else `.is-compact`; `hudShards` = `off` hides the shard pill; `hudRegion` = `off` hides the region name (region map, since 21 Oct 2026) |
| kartu misi | `mission card`, `brief` | `game/mission-brief`, `.mission-card`, `.mission-seal` |
| tombol utama | `main button` | `.btn-primary` on `#stage`, skin `ui.result-button-primary` |
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
| tombol Kembali berpapan (HUD) | `back plate` | `a.hud-back-plate` in the HUD's top-left corner on the `logo-centre` pages (Mulai, Masuk, Daftar, Gerbang), on the navy plate `kenal-plate-navy.webp` (`--plate-navy`), since 20 Oct 2026 |
| efek sinematik pembuka | `cinematic entrance`, `cue sheet` | `game/cine.js`, `data-cine` = `welcome`/`gate`/`account`; `html.cine-ok`, `.is-cine-live`, `.is-cine-done`; session key `gelita.cine.seen` |
| cahaya gambar latar (matahari) | `picture light`, `light source` | `findLight()` in `game/cine.js`; `createScreenFx(…, { light })`, effect `sun` (welcome page) |
| papan tombol hijau | `green plate` | `public/assets/ui/plate-green.webp` (`--plate-green`): Lihat hasil on the region map |
| gambar buku Pustaka | `library icon` | `library_icon()`, `public/assets/ui/icon-pustaka.webp`, `.library-icon`; on the Pustaka buttons |
| gambar bintang | `star picture` | `star_src()`/`star_img()`/`stars_html()`: slot `ui.result-star`, else `public/assets/ui/star.webp`; empty star `.is-off` (grey, faded); admin keeps the line icon |
| Wilayah dipilih (panel) | `region panel`, `selected region` | `.kedu-panel`, `data-region-select` |
| Lihat cerita pembuka (panel wilayah) | `opening story link` | `.kedu-opening` → `/dialog/{code}`, label `Game.watchRegionOpening` |
| tingkat kesulitan (wilayah) | `difficulty`, `difficulty badge` | stored `mudah`, `sedang`, `sulit` in `levels.difficulty`; `.region-difficulty` (`partials/region-difficulty`) in the HUD section `hudBadge` on `/dialog/{code}` (body class `is-region-dialogue`) |
| kartu sambutan (setelah daftar) | `welcome card`, `welcome modal` | flash `welcome`; `.welcome-card`, opened as a modal by `game/intro.js` |
| latar slide (cerita) | `slide background`, `backdrop` | `dialogues.background_media_id`; slot `bg.story.{context}[.{wilayah}].{n}` (`DialogueModel::backgroundKey()`); `data-bg` on the slide, `core/backdrop.js`; catalogue `bg.cerita-{intro, penutup, kenal-{wilayah}, dialog-{wilayah}, tuntas-{wilayah}}-NN` |
| musik latar | `background music`, `track` | `public/assets/audio/music/{name}.mp3`; layout section `music` → `<body data-music>`; `Sfx.music()`; scene tracks `welcome`, `opening`, `ending`, `kenal-`/`dialog-`/`tuntas-{wilayah}` (`tools/audio/music_scenes.py`) |
| musik mengecil saat narasi | `ducking` | `core/audio.js` `DUCKED` (0.2), `narrationChanged()` |
| efek layar 3D | `screen effects`, `screen fx` | `game/screen-fx.js` (three.js); `data-fx3d`, `screen_fx_attr()`, `.has-fx3d`; switch `Config\Gelita::$screenFx` |
| paralaks | `parallax` | `game/parallax.js`; `--plx`/`--ply` and `.has-parallax` on `#stage` |
| salinan ringkas (JS/CSS) | `minified mirror`, `minified assets` | `public/assets/min/{version}/`; command `gelita:aset:minify`; `AssetMinifier`; switch `Config\Gelita::$minifyAssets` |

## People, data, and the panel

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| siswa | `participant` | `participants`; admin route `/admin/peserta` |
| guru, admin, staf | `staff`, `teacher` | `staff_users`; stored roles `guru`, `admin` |
| sekolah | `school` | `schools` |
| sesi bermain | `game session` | `game_sessions` |
| kata sandi (sandi) | `password` | never "password" in UI |
| masuk / keluar | `login` / `logout` | |
| Ringkasan | `dashboard` | |
| Data mentah | `raw data` | dimension codes `peserta`, `sesi`, … (stored, keep) |
| Unduh data | `export` | `data_exports`; route `/admin/ekspor` |
| Kritik & saran | `feedback` | `participant_feedback`; route `/admin/masukan` |
| Penelitian | `research study` | `research_studies` |
| Rilis | `game release` | `game_releases` |
| Tata kelola | `governance` | |
| Kelengkapan aset | `asset checklist` | `AssetChecklist` |
| bank soal | `question bank` | workbook `gelita-bank-soal-kelas5.xlsx` |
| paket aset, mode paket | `asset package`, `package mode` | table `asset_packages`; codes `pkt-YYYY-MM-DD-NN`; `game_releases.asset_version`; commands `gelita:paket:*`; route `/api/paket/aktif` |
| Mode paket: Nyala / Mati | `package switch` | no column of its own; the active release's `asset_version` names a package (on) or is empty (off) |
| pratinjau paket | `package preview` | `?paket=` (staff only), session key `package_preview`, `AssetPackageService::previewCode()`/`servingCode()` |
| Izinkan mode daring untuk perangkat lab | `online_lab_allowed` | `writable/paket/pengaturan.json`; field of `/api/paket/aktif` |
| Bangun ulang paket / Terbitkan | `rebuild` / `publish` | routes `/admin/studi/rilis/paket/bangun`, `/admin/studi/rilis/paket/{code}/terbitkan` |
| paket terkunci (penelitian) | `study package lock` | `research_studies.package_code` |
| tanpa paket | no package | `asset_version = ''`; release code `tanpa-paket-YYYY-MM-DD-NN` |
| mode lengkap / daring / nonaktif | `package_mode` | stored `lengkap`, `daring`, `nonaktif` in `game_sessions.package_mode` |
| kelas 5 | `grade 5` | doc and branch names keep "kelas5" |
| Katalog Aset | `asset catalogue` | folder `gelita-katalog/`; command `gelita:katalog:impor`; credit `Katalog Aset GELITA (gambar sementara)` |
| gambar gerak (tokoh) | `frame` | `char.{jaka|kedu}.{pose}.{1–24}`; route `/admin/media/{id}/unggah-gerak` |
| naskah rekaman (PDF untuk pengisi suara) | `voice-actor script` | `NarrationScriptPdf`; route `/admin/konten/narasi/naskah-pdf?bahasa=id\|en\|semua` |
| Batalkan semua rekaman | `cancel all recordings` | `NarrationCatalog::cancelAll()`; route `POST /admin/konten/narasi/batalkan`; audit `narration_cancel_bulk` |

Add a row whenever a new concept appears. If a term here conflicts with existing code, the existing stored value wins and the table gets corrected.
