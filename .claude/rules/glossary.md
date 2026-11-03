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
| bantuan (tombol 💡 di tantangan) | `hint` | `hints`, `hint_used`; UI word "Bantuan" since 21 Oct 2026 (was "Petunjuk", which now belongs only to Cari Objek clues); English UI keeps "Hint" |
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
| musik latar | `background music`, `track` | `public/assets/audio/music/{name}.mp3`; layout section `music` → `<body data-music>`; `Sfx.music()`; scene tracks `welcome`, `opening`, `ending`, `kenal-`/`dialog-`/`tuntas-{wilayah}` (`tools/audio/music_scenes.py`) |
| musik mengecil saat narasi | `ducking` | `core/audio.js` `DUCKED` (0.2), `narrationChanged()` |
| efek layar 3D | `screen effects`, `screen fx` | `game/screen-fx.js` (three.js); `data-fx3d`, `screen_fx_attr()`, `.has-fx3d`; switch `Config\Gelita::$screenFx` |
| paralaks | `parallax` | `game/parallax.js`; `--plx`/`--ply` and `.has-parallax` on `#stage` |
| bingkai dialog | `dialog frame`, `framed dialog` | `public/assets/ui/modal-frame-4x3.webp` (`--modal-frame-compact`) and `modal-frame-16x9.webp` (`--modal-frame-wide`), cut from the owner's 4:3 and 16:9 sheets; `data-frame` and `--modal-w` set by `fitFrame()` in `core/modal.js`; game only, also the picture viewer (since 22 Oct 2026) |
| papan emas kapsul (tombol utama) | `gold pill` | `public/assets/ui/gold-pill-{1..4}.webp` (`--gold-pill-n` on `#stage`, `stage_picture_urls()`): the face of every `.btn-primary` and gold main button since 22 Oct 2026, replacing the `ui.result-button-primary` skin; `.btn-danger` wears it turned red by a filter (same day) |
| koin kesempatan, koin serpihan | `try coin`, `shard coin` | `.tries-dot`, `.result-shards-bar i`: the round `blank` face (`--round-blank`) left/earned, the `neutral` face used/missing (since 22 Oct 2026) |
| daftar pilihan (dropdown) | `dropdown`, `fancy select` | `core/select.js`, `select[data-fancy-select]`, `.fancy-select-*`; gold triangle `public/assets/ui/select-arrow.webp` (`--select-arrow`) |
| salinan ringkas (JS/CSS) | `minified mirror`, `minified assets` | `public/assets/min/{version}/`; command `gelita:aset:minify`; `AssetMinifier`; switch `Config\Gelita::$minifyAssets` |
| babak (Cari Objek) | `round` | `challenge_items.round` 1 = Temukan Benda (variant `x`), 2 = Periksa Unggahan (`a`, `b`); since 3 Nov 2026 (31) |
| Periksa Unggahan | `post check`, `find_part` | item type `find_part`; tables `post_scenes`, `post_parts`; payload `round2`; UI "Babak 2: Periksa Unggahan" (`Game.huntRoundTwoTitle`) |
| bagian ketuk (unggahan) | `tap part`, `part_key` | `post_parts.part_key` (A1, C10, …), box x/y/w/h in percent of the 16:9 scene; `button.post-part` in `engines/cari.js` |
| kartu jangkar | `anchor card` | shared group `x` of Benar atau Salah (`BankRules::SHARED_GROUP`), passage `{node}-x`; UI "Bacaan kedua" (`Game.anchorCardTitle`) |
| buklet | `booklet` | B1–B4, `participant_seats.booklet` (part 2); `BookletRules`, `VariantPicker`, seam `BookletSource`; `challenge_attempts.variant_source` (`booklet`, `random`, `retake`), `booklet_at_open`; `BOOKLET_MISSING` |
| kode kesalahan | `error code` | `item_error_codes`, `challenge_items.error_code`, `item_tries.error_code` (server-derived, `ErrorCodeResolver`), `item_tries.manual_code` (L5); list in `ErrorCodes` (03) |
| beban permukaan | `surface load` | `challenge_items.surface_json` (`SurfaceLoad`: `gap_count`, `passage_words`, `tap_min_dp`, …), frozen at import |
| label pengembang / label ahli | `label source` | `akm_level_source`, `bloom_level_source` = `developer` or `expert`; workbook column `label_source` |
| Skenario Digital (data) | `scenario`, `scenario_choice` | item type `scenario_choice`, table `scenario_options` (actions with score, reasons); engine `skenario` is part 6's |

## People, data, and the panel

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| siswa | `participant` | `participants`; admin route `/admin/peserta` |
| admin, peneliti, ketua peneliti, staf | `staff`, `admin`, `researcher`, `head researcher` | `staff_users`; stored roles `admin`, `peneliti`, `ketua` (revision 10 part 1, 3 November 2026; `guru` removed by migration `003400`). Rights come from the capability map `App\Libraries\StaffAccess`, route filter `staffCan`, view helper `staff_can()` |
| atur ulang kata sandi lewat kode | `reset by code` | `/admin/peserta/reset-sandi`, `peneliti` and `ketua`; audit `participant_password_reset` with `via: kode` |
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
| mode penelitian | `field mode` | `research_studies.field_mode` (0 by default), with `meeting_plan` (`lima_pertemuan`, `satu_sesi`); `ResearchStudyModel::fieldModeStudy()`; revision 10, part 2 (28) |
| sesi lapangan, sesi susulan | `field session`, `make-up session` | `field_sessions` (code `S03-A-P2`, `-S`, `-S2`); `FieldSessionService`; route `/admin/sesi-lapangan` |
| token sesi | `session token` | `field_session_tokens`; routes `/token`, `/daftar/0`; screen `/sampai-jumpa` when closed; API `FIELD_SESSION_CLOSED` |
| kode sekolah penelitian | `research school code` | `schools.research_code` (`S03`, `U01`, `V01`) |
| rombel | `class group` | `participants.rombel`, `field_sessions.rombel` (one capital letter) |
| kode peserta (mode penelitian) | `participant code` | `S03-A07` from `participant_code_counters` (`ParticipantCodeService`); `GLT-000001` outside field mode; `UJI-01` for test accounts |
| kursi, buklet, bentuk tes | `seat`, `booklet`, `test form` | `participant_seats` (`booklet` `B1`–`B4`, `pre_form`/`post_form` `A`/`B`); `SeatRules`; route `/admin/sesi-lapangan/{id}/kursi` |
| kemajuan siswa | `participant progress` | `participant_progress` per student and study (replaced `session_progress`, migration `003500`) |
| Pemantauan | `monitoring panel` | route `/admin/pemantauan`, `GET /api/admin/monitoring`; capability `monitoring.view` (admin: numbers only) |
| akun uji | `test account` | `uji.*` accounts, command `gelita:uji:akun`, school `V01` |

Add a row whenever a new concept appears. If a term here conflicts with existing code, the existing stored value wins and the table gets corrected.
