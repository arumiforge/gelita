# Proposed marks for the open rows of REVIEW.md

A Claude session proposes a mark for every picture row of [`REVIEW.md`](REVIEW.md) that had no mark on 20 November 2026 (211 rows on 16 sheets; sheet 20 is fully marked). The owner asked for this on 16 November 2026 (`docs/PROGRESS.md` → *Remaining rows of REVIEW.md*). Nothing here is a mark yet: `REVIEW.md` is unchanged until the owner confirms.

## How the owner confirms

- Per sheet, in chat: **"accept sheet NN"** takes every proposal of that sheet as written.
- To change some rows: **"sheet NN: accept, except 262 keep, 270 redo: …"**, or name only the rows to change.
- A later Claude session then copies the accepted marks (and the reason of each row into **Notes**, where it helps) into `REVIEW.md`, rebuilds the catalogue page (`php spark gelita:katalog:halaman`, so `CatalogSiteTest` stays green), and adds an `R-` row to `../chatgpt-images/TASKS.md` for every accepted `redo` (with its prompt in document 39 style). `redo later` rows get no task row yet. `drop` rows are removed from `data.json` and the folder; where the code still names the file (noted below), the reference goes first.

## How the proposals were made

- **Still used?** Every slot was checked against the code on branch `igi/laughing-fermi-ifbg3l` at e48b88a: `ImageSlots::SLOTS`, `CatalogImporter`, the views, helpers, CSS and JS, and the research bank in `docs/bank-soal/data/` (which variant a slot serves; Susun Gambar variants `a`/`b` are the ones the study serves first, `c` onward only on retakes).
- **Owner decisions applied:** one carved-gold interface look (document 40, `U-` orders); the quiet buttons of 19 November 2026 (`04`, *Tombol tenang*: every labelled button is a CSS capsule now, the face picture ordered as `PL-07` to `PL-09`); still pictures only for character poses (frames 2 to 24 are being removed on another branch); the open `PL-` orders.
- **The pictures:** the contact sheets in `review/`, and full-size views of the pictures named in the reasons. Text inside the test pictures was read where a reason names it.
- **What this is not.** No picture was compared with a photo of the real object. Where cultural accuracy matters and could not be judged here, the row says *researcher to confirm* or proposes `redo later: needs real reference`. As `REVIEW.md` says, this is the developer side's check, not expert validation.

## Summary

| Sheet | Rows | keep | redo | redo later | drop |
|---|---|---|---|---|---|
| 01 | 18 | 14 | 0 | 1 | 3 |
| 02 | 10 | 3 | 0 | 0 | 7 |
| 03 | 26 | 26 | 0 | 0 | 0 |
| 04 | 6 | 1 | 0 | 0 | 5 |
| 05 | 2 | 1 | 0 | 1 | 0 |
| 06 | 2 | 1 | 0 | 0 | 1 |
| 09 | 10 | 10 | 0 | 0 | 0 |
| 10 | 15 | 10 | 3 | 1 | 1 |
| 11 | 28 | 25 | 1 | 2 | 0 |
| 12 | 4 | 3 | 0 | 1 | 0 |
| 13 | 24 | 21 | 0 | 3 | 0 |
| 14 | 3 | 0 | 0 | 1 | 2 |
| 15 | 3 | 0 | 0 | 0 | 3 |
| 16 | 24 | 24 | 0 | 0 | 0 |
| 17 | 18 | 16 | 1 | 1 | 0 |
| 19 | 18 | 4 | 0 | 0 | 14 |
| **All** | **211** | **159** | **5** | **11** | **36** |

## Part 1: the benchmark

### Sheet 01: cards, screen backgrounds, Jaka, app icons

14 keep, 3 drop, 1 redo later.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 1 | `bg-auth-login` | `keep` | Login, token, change-password and session-closed screens; calm dark right half for the form |
| 2 | `bg-gate` | `keep` | Start-choice screen (`start-choice.php`); one family with 1 and 3 |
| 3 | `bg-mulai` | `keep` | Start, sign-up steps, about and sound check. Researcher to confirm: the stone gate is a split gate (candi bentar), the motif questioned at badge 84 |
| 4 | `btn-install` | `drop: the install button is a quiet navy button since 19 November 2026; the picture is no longer drawn` | `cinematic.css` paints `.btn-install-plate` with the navy face over it. Code first: `welcome.php` shows the button only while this file exists |
| 5 | `btn-reflection-en` | `drop: every labelled button is a quiet button with page text since 19 November 2026 (PL-07)` | Lettered teal plate, a third plate colour. Without the file, `picture_link()` falls back to the plain gold button by itself |
| 6 | `btn-reflection` | `drop: every labelled button is a quiet button with page text since 19 November 2026 (PL-07)` | As no. 5 (Balai Refleksi link at the end of the story, `reflection.php`) |
| 7 | `gate-icon-intro` | `keep` | Start-choice screen, "story" choice; carved-gold ring |
| 8 | `gate-icon-map` | `keep` | Start-choice screen, "map" choice |
| 10 | `icon-pustaka` | `keep` | Pustaka icon (`content_helper`). Almost the same book as no. 7 for another meaning (developer's first look); owner to say whether that is acceptable |
| 11 | `jaka-gate` | `redo later: closed grey shoes and fading legs; Jaka wears sandals everywhere else` | Still on the start-choice screen; the home page gets `PL-02` (Jaka with the lantern) instead |
| 12 | `jaka-wave` | `keep` | Login and change-password screens; sandals, red scarf, matches sheet 09 |
| 13 | `kedu-region-magelang` | `keep` | Mask that darkens a locked region on Peta Kedu (`Config\Gelita`); never seen as a picture |
| 14 | `kedu-region-temanggung` | `keep` | As no. 13 |
| 15 | `kedu-region-wonosobo` | `keep` | As no. 13 |
| 16 | `node-card-boleh` | `keep` | Challenge card (region map, curtain, GELITA guide); right and wrong are a tick and a cross, not colour only |
| 17 | `node-card-cari` | `keep` | Challenge card; magnifier over objects |
| 18 | `node-card-puzzle` | `keep` | Challenge card; pieces and a frame |
| 19 | `node-card-rumpang` | `keep` | Challenge card; text with blanks; one family with 16 to 20 |

### Sheet 02: plates, frames, markers

3 keep, 7 drop.

Every drop here is an old carved plate that the quiet buttons of 19 November 2026 paint over (`#stage` rules in `cinematic.css`), so no student sees it. The code still names five of them; the session that drops them removes those references and adjusts the two tests in the same change.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 26 | `challenge-badge` | `keep` | Title badge of every challenge (`challenge/_frame.php`) and the challenge curtain; one family with the avatars |
| 40 | `node-card-frame` | `keep` | The region map no longer uses it (decision of 19 November 2026), but the challenge curtain still frames the card with it (`curtain.php`). Drop it if the curtain follows the map |
| 41 | `ornate-crest` | `keep` | Crest of the ornate frame (`partials/ornate-frame.php`) and the start-choice screen; part of the benchmark |
| 42 | `plate-back` | `drop: painted over by the quiet navy button since 19 November 2026` | Code first: `plate_attrs()` in `content_helper.php` still sets it as `--plate` |
| 43 | `plate-enter` | `drop: painted over by the quiet gold button since 19 November 2026` | Code first: `plate_attrs()` |
| 44 | `plate-gold-1` | `drop: painted over by the quiet gold button since 19 November 2026` | Code first: `gold_plate_button()` (without the file it already falls back to the plain gold button); `RegisterWizardTest`, `StartGateTest` name the file |
| 45 | `plate-gold-2` | `drop: not used by the game` | No reference anywhere in the code |
| 46 | `plate-gold-3` | `drop: painted over by the quiet gold button since 19 November 2026` | As no. 44 |
| 47 | `plate-gold-4` | `drop: not used by the game` | No reference anywhere in the code |
| 49 | `plate-next` | `drop: painted over by the quiet gold button since 19 November 2026` | Code first: `plate_attrs()` (the forward buttons of every challenge) |

### Sheet 03: round gold buttons

26 keep.

The round buttons stay (decision of 19 November 2026: "tombol bulat HUD tetap"), and every one of them is used (`round_icon()`, `round_icon_src()`, `round_style_attr()`).

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 55 | `round-back` | `keep` | Benchmark; back button |
| 56 | `round-blank` | `keep` | A try left or a shard earned (`--round-blank`) |
| 57 | `round-clock` | `keep` | Challenge timer and result screen |
| 58 | `round-close` | `keep` | Close button of the picture viewer and dialogs |
| 59 | `round-eye` | `keep` | Show password (login, password field) |
| 60 | `round-help` | `keep` | Bantuan chip of every challenge |
| 61 | `round-info` | `keep` | Information buttons |
| 63 | `round-lang-id` | `keep` | HUD language button (`hud-menu.php`); the language sheet has flat flags since 19 November 2026 |
| 64 | `round-loading` | `keep` | Busy ring of login and sign-up buttons |
| 65 | `round-lock` | `keep` | Fixed class on sign-up step 1 |
| 66 | `round-login` | `keep` | Start screen, "Masuk" |
| 67 | `round-logout` | `keep` | Logout |
| 68 | `round-map` | `keep` | Map button |
| 69 | `round-menu` | `keep` | HUD menu |
| 70 | `round-mute` | `keep` | Sound off |
| 71 | `round-neutral` | `keep` | A used try or a missing shard; base of the modal icon |
| 72 | `round-next` | `keep` | Forward button |
| 73 | `round-pause` | `keep` | Pause |
| 74 | `round-play` | `keep` | Play |
| 75 | `round-profile` | `keep` | Staff login row of the menu |
| 76 | `round-register` | `keep` | Start screen, "Daftar" |
| 77 | `round-replay` | `keep` | Replay |
| 78 | `round-right` | `keep` | Right answer; a tick, not colour only |
| 79 | `round-ring-green` | `keep` | Done step of the sign-up stepper. Question: the green ring alone marks "done"; check the step also shows a tick or text (media instrument A8) |
| 80 | `round-sound` | `keep` | Sound on |
| 81 | `round-wrong` | `keep` | Wrong answer; a cross, not colour only |

## Part 2: the pictures of the list

### Sheet 04: logo, start buttons, result screen, badges

1 keep, 5 drop.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 85 | `ui.btn-start` | `drop: no longer shown; the home page writes MULAI on the gold button (19 November 2026)` | `welcome.php`: "the old lettered slot `ui.btn-start` is no longer shown". `ImageSlots` still lists it as required: correct the slot entry in the same change |
| 86 | `ui.btn-start.en` | `drop: no longer shown; the home page writes START on the gold button (19 November 2026)` | As no. 85 |
| 87 | `ui.logo` | `drop: one Lantern of Kedu; the guides fall back to the carved-gold lantern U-14` | With `ui.logo-hero` installed, this emblem is seen only in the guides' top bar (`layouts/guide.php`), whose fallback is `ui.lantern` (`U-14`). Its lantern is a third design beside `U-14` and the app icon |
| 88 | `ui.logo-hero` | `keep` | Home page, account screens, staff login, HUD; carved gold, the word GELITA is the name |
| 89 | `ui.placeholder` | `drop: not installed; the game uses assets/ui/placeholder.svg` | Thin flat line icon, another look |
| 95 | `ui.result-shard-effects` | `drop: not a game slot` | Nothing in the game loads it |

### Sheet 05: maps, pins, lanterns

1 keep, 1 redo later.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 97 | `map.kedu` | `keep` | Fallback of Peta Kedu while the island map is installed, and a required slot (`ImageSlots`, `AssetChecklist`); one look with the region maps |
| 107 | `diorama.kedu-pulau` | `redo later: Kedu is an inland plain ringed by mountains; an island in the sea works against the opening story (intro-01)` | The owner's own picture of 20 October 2026, so the owner decides. The new backgrounds of document 40 ask for "no sea, coast, islands" |

### Sheet 06: app icons, avatars, language flags

1 keep, 1 drop.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 125 | `ui.pin` | `drop: every region has its own carved-gold pin (U-18 to U-20)` | Only a fallback for a city without its own pin; smooth look and an older lantern design |
| 128 | `ui.jaka-avatar` | `keep` | Peta Kedu story strip and Kenali screen; the source of 457 and 458, same ring and face |

### Sheet 09: characters (frame 1 of each pose)

10 keep.

Frame 1 is the still picture of each pose (owner: one still picture per pose). Frames 2 to 24 of every pose are removed on another branch (`docs/PROGRESS.md` → *Still pictures only*): `drop: still pictures only (owner)`. Jaka is the same boy with the red scarf in all six poses, and Mbah Kedu matches his avatar no. 394.

One question for the owner, not a mark: Jaka's lantern in 179, 181, 182 and 184 is a plain old hurricane lamp, not the carved-gold Lantern of Kedu of `U-14` that the new home-page picture `PL-02` puts in his hand. Leave it, or later redraw the lantern in these poses?

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 179 | `char.jaka.afraid.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Clear fear pose, lantern held close |
| 180 | `char.jaka.bow.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Bow; no lantern (the developer's question of 15 November stays open) |
| 181 | `char.jaka.determined.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Determined stride with lantern |
| 182 | `char.jaka.happy.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Lantern raised, cheering |
| 183 | `char.jaka.idle.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Neutral stand; no lantern |
| 184 | `char.jaka.sad.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Head down, lantern lowered |
| 185 | `char.kedu.idle.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Same costume and face as avatar 394 |
| 186 | `char.kedu.smile.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Open-hand gesture, smiling |
| 187 | `char.kedu.weak.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Bent over the stick, matches avatar 459 |
| 188 | `char.kedu.worried.1` | `keep: frame 1 is the still pose; frames 2 to 24 drop: still pictures only (owner)` | Worried face |

### Sheet 10: backgrounds and story slides

10 keep, 3 redo, 1 redo later, 1 drop.

Three Borobudur pictures (198, 206, 209) draw the temple with many tall pointed towers round a central spire. The real temple is a low stepped mound: square terraces, then round terraces of perforated bell stupas, one large stupa on top. These pictures also have the older painted finish, not the 3D look of the new story backgrounds. The researcher's licensed Borobudur photos (as used for `M-K1` to `M-K4`) would be the reference.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 189 | `bg.auth` | `drop: not shown; the account screens use the shipped backgrounds 1 and 2 since 18 October 2026` | `ImageSlots` marks the slot `unused` |
| 191 | `bg.cerita-intro-02` | `keep` | Fits slide 2 well: the lantern in a pendapa, a grandmother telling stories, a relief, farmers, a child reading. The relief is drawn as a memory, not as a real panel |
| 192 | `bg.cerita-intro-03` | `keep` | Kabut Lupa: grey fog swirling, fading shards; nothing to misread |
| 193 | `bg.cerita-intro-04` | `redo: the three regions are drawn as floating castles with spires and a coastal town with palms; Kedu has no coast, and the regions should read as Temanggung, Magelang and Wonosobo` | The cracked lantern itself fits the line ("kacanya retak"). Painted finish. Count the shards when redoing (the line says fifteen) |
| 194 | `bg.cerita-intro-05` | `keep` | Dim joglo interior with a small lamp for Mbah Kedu's "weak" line; calm for the text |
| 198 | `bg.cerita-intro-09` | `redo: Borobudur is drawn with tall corner towers and a large lake beside it; it must be the real stepped temple, and no invented lake` | Slide 9 "Perjalanan Dimulai"; a picture every student sees |
| 204 | `bg.intro` | `keep` | Fallback of story slides without their own background (`intro.php`, `ending.php`); calm joglo at night |
| 205 | `bg.loading` | `keep` | Curtain "Membuka Peta Kedu"; plain night sky for the title |
| 206 | `bg.loading.magelang` | `redo later: Borobudur drawn with many pointed towers; the real temple is a low stepped mound of bell stupas` | Curtain towards Magelang, seen for a few seconds, so lower priority than 209 |
| 207 | `bg.loading.temanggung` | `keep` | Twin mountains over fields; fits Temanggung |
| 208 | `bg.loading.wonosobo` | `keep` | Mountain road over a sea of clouds; fits "Negeri di Atas Awan" |
| 209 | `bg.magelang.region` | `redo: Borobudur drawn with many pointed towers; it must be the real stepped temple (low terraces, bell stupas, one main stupa)` | Region dialogue and region map of Magelang: a long view for every student. The smaller temples dotted over the plain are invented too |
| 210 | `bg.map` | `keep` | Behind Peta Kedu, Profil and the Pustaka shelf; calm misty land |
| 211 | `bg.reflection` | `keep` | Balai Refleksi and the attitude scale; carved wooden gateway at sunset, calm middle |
| 212 | `bg.result.temanggung` | `keep` | Result screen of Temanggung. Question for the owner: only Temanggung has a result background; the other two regions show the usual one. Order two more, or leave it? |

### Sheet 11: Susun Gambar pictures

25 keep, 1 redo, 2 redo later.

The study serves variants `a` and `b` first: 218 and 220 (Magelang), 234 and 454 (Temanggung), 239 and 244 (Wonosobo); the others only on retakes. None of the 28 has letters. Cultural detail could not be checked against photos here; the rows say where the researcher should look before data collection.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 216 | `challenge.item.mgl-1-01` | `keep` | Perforated stupas with a Buddha inside on the round terraces; recognisable |
| 217 | `challenge.item.mgl-1-02` | `redo later: the ankle bells named in the caption cannot be seen` | Kuluk, fringes and face paint are there. Researcher to confirm the costume against the Topeng Ireng photos |
| 218 | `challenge.item.mgl-1-03` | `keep` | Variant `a`. Candi Mendut with its stair and a seated Buddha in the doorway, as the caption says. Researcher to confirm the temple form against a photo before data collection |
| 219 | `challenge.item.mgl-1-d01` | `redo: the adult reaches towards the relief; the caption says visitors may look but not touch, so every hand stays clearly away` | The rope barrier and the ship relief are good; only the gesture works against the caption |
| 221 | `challenge.item.mgl-1-f01` | `keep` | Families climbing the steps of Gunung Tidar; the signpost has no text |
| 222 | `challenge.item.mgl-1-g01` | `keep` | Layered getuk with grated coconut at a market stall |
| 223 | `challenge.item.mgl-1-h01` | `keep` | Potter at the wheel, rows of clay pots and a kiln |
| 224 | `challenge.item.mgl-1-i01` | `keep` | Monks with alms bowls, Buddhist flags, Borobudur behind. Researcher to confirm the procession is shown correctly and respectfully |
| 225 | `challenge.item.mgl-1-j01` | `keep` | Visitors on a viewpoint above Borobudur with mountain pairs on the horizon; the temple is small and less detailed |
| 226 | `challenge.item.tmg-1-01` | `keep` | Sumbing (broad, left) and Sindoro (cone, right) as the media note says; coffee in front |
| 227 | `challenge.item.tmg-1-03` | `redo later: needs real reference (the researcher's Candi Pringapus photos)` | Same composition as Candi Pawon (bicycle at the lower right); the Pustaka painting of this temple had the wrong form, so the shape should be checked |
| 228 | `challenge.item.tmg-1-04` | `keep` | At full size a woven grid shows on the horse; ankle bells are visible. Researcher to confirm the weave reads as "anyaman bambu" (the caption) |
| 229 | `challenge.item.tmg-1-d01` | `keep` | Excavated stone terraces with archaeologists; fits Situs Liyangan |
| 230 | `challenge.item.tmg-1-e01` | `keep` | Large inscribed stone under a shelter with a guide and children. The marks are a texture, not readable letters. Researcher to confirm the stone's shape |
| 231 | `challenge.item.tmg-1-f01` | `keep` | Clear spring pool with monkeys. Researcher to confirm that the split gate at the right and the monkeys belong to Umbul Jumprit |
| 232 | `challenge.item.tmg-1-g01` | `keep` | Coffee picking and drying on the slopes |
| 233 | `challenge.item.tmg-1-h01` | `keep` | Catching small fish in a clear river; a plate of fried uceng |
| 234 | `challenge.item.tmg-1-i01` | `keep` | Variant `a`. Market under bamboo, bamboo stalls, food on leaves, no plastic, as the media note asks |
| 235 | `challenge.item.tmg-1-j01` | `keep` | Craftspeople weaving and painting kuda kepang; the weave is visible |
| 236 | `challenge.item.wnb-1-05` | `keep` | Turquoise lake in forest; reads as Telaga Warna |
| 237 | `challenge.item.wnb-1-06` | `keep` | Sunrise over a sea of clouds from a rocky top |
| 238 | `challenge.item.wnb-1-07` | `keep` | Mi ongklok with thick sauce and satay; ongklok baskets hanging |
| 239 | `challenge.item.wnb-1-d01` | `keep` | Variant `a`. The researcher found it the closest of the Arjuna pictures (16 November); the temples stand on grass, not the gravel yard, a small difference in a 4 × 4 puzzle. Owner to judge |
| 240 | `challenge.item.wnb-1-e01` | `keep` | Crater behind a fence, a guide, a warning sign with a pictogram only; matches "Tetap di Balik Pagar" |
| 241 | `challenge.item.wnb-1-f01` | `keep` | Carica on its tree; the reference for 121 and 271 |
| 243 | `challenge.item.wnb-1-h01` | `keep` | Tea pickers on the slopes, an estate house behind |
| 244 | `challenge.item.wnb-1-i01` | `keep` | Variant `b`. Masked dancer and a dancer with a sampur, gamelan with gong and kendang. Researcher to confirm the mask and costume of Topeng Lengger |
| 245 | `challenge.item.wnb-1-j01` | `keep` | Frost on vegetable leaves at dawn, Arjuna-like temples far off |

### Sheet 12: Benar atau Salah pictures and Cari Objek scenes

3 keep, 1 redo later.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 246 | `challenge.item.tmg-3-a03` | `redo later: needs real reference (a photo of the burnt rice found at Liyangan); the grains read as charcoal or burnt beans` | Used by item `tmg-3-a04` (key: true), so a child must recognise rice. Painted finish |
| 249 | `challenge.mgl-4.scene` | `keep` | Empty tables in a carved pavilion with Borobudur beyond; no letters |
| 250 | `challenge.tmg-4.scene` | `keep` | Empty benches in a market square under the twin mountains |
| 251 | `challenge.wnb-4.scene` | `keep` | Bamboo stall with empty tables, tea slopes and Dieng temples |

### Sheet 13: Cari Objek objects

21 keep, 3 redo later.

All are cut-outs without letters; shared objects are the same file in every region, so a picture gives no hint of its region.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 252 | `challenge.item.mgl-4-a01` | `keep` | Perforated stupa; target in Magelang |
| 253 | `challenge.item.mgl-4-a02` | `keep` | Kuluk with feathers; same file as 280 |
| 254 | `challenge.item.mgl-4-a03` | `keep` | Layered getuk on a leaf; same file as 281 |
| 257 | `challenge.item.mgl-4-a06` | `keep` | Jar of candied carica; a decoy from another region |
| 258 | `challenge.item.mgl-4-a07` | `keep` | Dadak merak: lion mask with peacock feathers; clearly not from Kedu |
| 259 | `challenge.item.mgl-4-a08` | `redo later: needs real reference; an angklung has two or three tuned tubes in a frame, this one hangs five or six like a chime` | Decoy, same file as 266 and 277; researcher to confirm. One redo fixes all three |
| 260 | `challenge.item.mgl-4-a09` | `keep` | Bakpia, one cut open |
| 261 | `challenge.item.mgl-4-a10` | `keep` | Metal pot; the modern decoy beside the clay kendil 255 |
| 262 | `challenge.item.tmg-4-02` | `keep` | Roasted coffee beans; same file as 279 |
| 264 | `challenge.item.tmg-4-04` | `keep` | Small uceng fish |
| 265 | `challenge.item.tmg-4-06` | `keep` | Seated Nandi on a base; target in Temanggung |
| 266 | `challenge.item.tmg-4-09` | `redo later: needs real reference; an angklung has two or three tuned tubes in a frame, this one hangs five or six like a chime` | Same file as 259 |
| 267 | `challenge.item.tmg-4-10` | `keep` | Ondel-ondel figure with red face and moustache; a decoy from another region |
| 268 | `challenge.item.tmg-4-11` | `keep` | Two-masted sailing ship; reads as a pinisi (blue sails rather than the usual white: minor) |
| 269 | `challenge.item.tmg-4-12` | `keep` | Sasando with its lontar-leaf shell; same file as 278 |
| 272 | `challenge.item.wnb-4-a03` | `keep` | Woven bamboo ladle basket (ongklok) |
| 273 | `challenge.item.wnb-4-a04` | `keep` | Tea shoot with leaves |
| 274 | `challenge.item.wnb-4-a05` | `keep` | Large orange papaya; now clearly unlike the carica 271 |
| 275 | `challenge.item.wnb-4-a06` | `keep` | Flat woven bamboo tray (rigen). Researcher to confirm the form |
| 277 | `challenge.item.wnb-4-a08` | `redo later: needs real reference; an angklung has two or three tuned tubes in a frame, this one hangs five or six like a chime` | Same file as 259 |
| 278 | `challenge.item.wnb-4-a09` | `keep` | Same file as 269 |
| 279 | `challenge.item.wnb-4-a10` | `keep` | Same file as 262 |
| 280 | `challenge.item.wnb-4-a11` | `keep` | Same file as 253 |
| 281 | `challenge.item.wnb-4-a12` | `keep` | Same file as 254 |

### Sheet 14: Pustaka (1 of 2)

1 redo later, 2 drop.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 286 | `library.magelang.p2.3` | `drop: the medium is a video; the importer installs only its cover (287)` | Not installed by the game |
| 297 | `library.magelang.p6.3` | `drop: the medium is a video; the importer installs only its cover (298)` | Not installed by the game |
| 300 | `library.magelang.p8.1` | `redo later: needs real reference; a generated stone with invented script, drawn like a museum photo, presented as Prasasti Canggal` | The only Pustaka picture of sheet 14 left in the old order: not the 3D look of `PK-01` to `PK-32`, and the pseudo-letters could be taken for the real inscription. A licensed photo of the real stone (as for 289, 294, 332) or a 3D picture made from one |

### Sheet 15: Pustaka (2 of 2)

3 drop.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 318 | `library.temanggung.p8.2` | `drop: the medium is a video; the importer installs only its cover (319)` | Not installed by the game |
| 334 | `library.wonosobo.p7.1` | `drop: the medium is a video; the importer installs only its cover (335)` | Not installed by the game |
| 336 | `library.wonosobo.p8.1` | `drop: the medium is a video; the importer installs only its cover (337)` | Not installed by the game |

### Sheet 16: Skenario Digital situation pictures

24 keep.

All 24 show their scenario in one smooth cartoon 3D family; the screens inside carry icons and grey bars only, no letters, and none marks a choice as the safe one. Three notes for the researcher, none a reason to redraw:

- **344 and 348:** a finger is on the send or upload button. The situation itself says "you want to send it" (`mgl-5-a03`) or "you want to upload it" (`mgl-5-b03`), so the picture shows the situation, not an answer; it does point at the risky action. Researcher to confirm this is acceptable for H3/H5.
- **The Reason column of this sheet** describes each picture as seen on the contact sheet; it is a description, not a check of every detail.
- **351** (`tmg-5-a02`): the game on the tablet shows a stepped temple like Borobudur in a Temanggung scenario. It is inside a fictional game screen.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 342 | `item.skenario.mgl-5-a01` | `keep` | Login form on a tablet, Borobudur outside |
| 343 | `item.skenario.mgl-5-a02` | `keep` | A friend's hand reaching for the tablet's login screen; fits "Pinjam akun game-mu" |
| 344 | `item.skenario.mgl-5-a03` | `keep` | Photo from the school dance contest about to be sent; see the note above |
| 345 | `item.skenario.mgl-5-a04` | `keep` | Chat on a phone at a school desk |
| 346 | `item.skenario.mgl-5-b01` | `keep` | Phone on a tripod filming getuk at a stall |
| 347 | `item.skenario.mgl-5-b02` | `keep` | Laptop and phone with folders and an account icon |
| 348 | `item.skenario.mgl-5-b03` | `keep` | Topeng Ireng practice photo about to be uploaded; see the note above |
| 349 | `item.skenario.mgl-5-b04` | `keep` | Chat beside a plate of getuk |
| 350 | `item.skenario.tmg-5-a01` | `keep` | Prize offer with a bicycle on a tablet |
| 351 | `item.skenario.tmg-5-a02` | `keep` | A child playing a game on a tablet; see the note above |
| 352 | `item.skenario.tmg-5-a03` | `keep` | Upload screen with a dancer's photo, vegetables and batik in the kitchen |
| 353 | `item.skenario.tmg-5-a04` | `keep` | Group chat with a calendar and a building icon |
| 354 | `item.skenario.tmg-5-b01` | `keep` | Drawing contest on a tablet beside a sketchbook |
| 355 | `item.skenario.tmg-5-b02` | `keep` | Video call or game lobby with children at a gate |
| 356 | `item.skenario.tmg-5-b03` | `keep` | Video upload among coffee plants |
| 357 | `item.skenario.tmg-5-b04` | `keep` | Chat on a phone over a notebook |
| 358 | `item.skenario.wnb-5-a01` | `keep` | Phone with a gift and a card icon, a Dieng temple outside |
| 359 | `item.skenario.wnb-5-a02` | `keep` | Star rating on a tablet beside a phone |
| 360 | `item.skenario.wnb-5-a03` | `keep` | Two chat panes and a trophy |
| 361 | `item.skenario.wnb-5-a04` | `keep` | A post with a landslide-like road photo |
| 362 | `item.skenario.wnb-5-b01` | `keep` | Gift offer beside carica slices and a bicycle |
| 363 | `item.skenario.wnb-5-b02` | `keep` | School website on a tablet and a phone |
| 364 | `item.skenario.wnb-5-b03` | `keep` | A phone left on a bus seat |
| 365 | `item.skenario.wnb-5-b04` | `keep` | Video post of a landscape |

### Sheet 17: test stimulus pictures

16 keep, 1 redo, 1 redo later.

The grey line art is taken as a deliberate neutral look for the test (developer's first look, question 8). The words in 367 and 377 were read at full size; for the others this session relied on the first look of 15 November, which found no other difference from `tes.php`. The label "Bukan temanmu" (368, 372, 376, 380) gives nothing away: each item text already says "akun yang tidak kamu kenal".

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 366 | `instr.tes-ae-A01` | `keep` | Birthday-card form; "Pilih foto" spelt right (the twin of 374) |
| 367 | `instr.tes-ae-A02` | `redo later: the cooking channel has a laboratory flask on its counter, which belongs to its twin B02 (science channel)` | Small; the words match the item. Only worth an edit if the two forms are redrawn anyway |
| 368 | `instr.tes-ae-A03` | `keep` | Reading-app message from an unknown account |
| 369 | `instr.tes-ae-A04` | `keep` | Chat with a light header. Researcher to confirm the different header of its twin 377 (dark) does not matter for form equivalence |
| 370 | `instr.tes-ae-A05` | `keep` | Upload screen with a speech bubble |
| 371 | `instr.tes-ae-A06` | `keep` | Share sheet to a group |
| 372 | `instr.tes-ae-A07` | `keep` | Kedugram message request, accept and delete buttons drawn alike |
| 373 | `instr.tes-ae-A08` | `keep` | Group chat with likes |
| 375 | `instr.tes-ae-B02` | `keep` | Science live stream; words match the item |
| 376 | `instr.tes-ae-B03` | `keep` | Drawing-app message from an unknown account |
| 377 | `instr.tes-ae-B04` | `redo: edit only: remove the stray stroke under "untuk" and the mark under "kodenya"; nothing else changes` | Seen at full size; stray marks in a reading test can look like letters. A small edit like `R-06` |
| 378 | `instr.tes-ae-B05` | `keep` | Upload screen, twin of 370 |
| 379 | `instr.tes-ae-B06` | `keep` | Share sheet, twin of 371 |
| 380 | `instr.tes-ae-B07` | `keep` | Message request, twin of 372 |
| 381 | `instr.tes-ae-B08` | `keep` | Group chat, twin of 373 |
| 386 | `instr.tes-mb-A-M3` | `keep` | Long post with comments; text too small to compare here, the first look found it matching |
| 388 | `instr.tes-mb-B-M3` | `keep` | As 386 |
| 389 | `instr.tes-mb-B-M4` | `keep` | Group chat with a forwarded notice; twin of 387 |

### Sheet 19: style references

4 keep, 14 drop.

Not installed by the game except 394. Keep what an order or a prompt still attaches (checked in `TASKS.md` and documents 37 to 41), drop the rest; the carved-gold style board `U-00` (460) is the reference for interface parts.

| No | Picture | Proposed mark | Reason |
|---|---|---|---|
| 393 | `reference.01` | `keep` | Jaka's character sheet; attached to `PL-02` and named in the prompts of 37 to 41 |
| 394 | `reference.02` | `keep` | Installed as Mbah Kedu's avatar `ui.kedu-avatar` (`CatalogImporter::REFERENCE_SLOTS`); source of 459 |
| 395 | `reference.03` | `drop: replaced by the framed Jaka avatar no. 128 and its expressions 457, 458` | No order attaches it (only a code comment names its history) |
| 396 | `reference.04` | `drop: no order attaches it; the new story backgrounds follow reference.06 and the researcher's photos` | |
| 397 | `reference.05` | `drop: no order attaches it; Jaka holds a plain lamp, not the Lantern of Kedu` | |
| 398 | `reference.06` | `keep` | Attached to the open orders `PL-01`, `PL-10` to `PL-12` and most story backgrounds |
| 399 | `reference.07` | `drop: no open order attaches it; its stone gate is the split-gate motif questioned at no. 3 and badge 84` | Named only by the finished orders of 37 to 39. Least sure of this sheet: the developer's first look proposed to keep it |
| 400 | `reference.08` | `drop: no order attaches it; the island diorama 107 is the installed map` | |
| 401 | `reference.09` | `keep` | The GELITA logo; named in the prompts of 37 to 41 as the finish to match |
| 402 | `reference.10` | `drop: the navy MULAI plate is neither the game's button nor the carved-gold look` | |
| 403 | `reference.11` | `drop: no order attaches it; badge 83 is the installed Temanggung badge (U-21)` | |
| 404 | `reference.12` | `drop: lettered title plate; the region title is plain text since 19 November 2026` | |
| 405 | `reference.13` | `drop: a copy of the shipped jaka-wave (no. 12)` | |
| 406 | `reference.14` | `drop: Mbah Kedu in another outfit than his avatar and poses` | |
| 407 | `reference.15` | `drop: a village guard who is not in the story` | |
| 408 | `reference.16` | `drop: its only order, U-14 (the Lantern of Kedu), is installed` | It is also the lantern of `ui.logo` (87); keep it only if 87 is kept |
| 417 | `reference.25` | `drop: a copy of the shipped bg-auth-login (no. 1)` | |
| 418 | `reference.26` | `drop: a copy of the shipped bg-gate (no. 2)` | |

## Questions for the owner

1. **Lantern designs.** Drop `ui.logo` (87) and `reference.16` (408) so the carved-gold `U-14` is the one Lantern of Kedu outside the story? And Jaka's plain lamp in the poses of sheet 09: leave it, or redraw it later?
2. **Old plates (sheet 02, 4 to 6 of sheet 01).** The drops need a small code change first (`plate_attrs()`, `gold_plate_button()`, the install button, two tests). Do it in the session that copies the marks, or wait until `PL-07` to `PL-09` are installed?
3. **Borobudur backgrounds (198, 206, 209).** Redo the two students see longest (198, 209) now, and 206 later, as proposed?
4. **Island map (107)** and **result backgrounds (212):** the owner's call; the proposals only name the conflict.
5. **Researcher checks before data collection** on the study variants: 218 (Candi Mendut), 239 (Arjuna) and 244 (Topeng Lengger), and the cultural rows marked *researcher to confirm* on sheets 01, 11 and 13.
