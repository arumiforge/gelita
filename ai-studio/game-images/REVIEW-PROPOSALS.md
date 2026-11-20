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
| 01 | 18 | 13 | 0 | 1 | 4 |
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
| 19 | 18 | 5 | 0 | 0 | 13 |
| **All** | **211** | **159** | **5** | **10** | **36** |

## Part 1: the benchmark

### Sheet 01: cards, screen backgrounds, Jaka, app icons

13 keep, 4 drop, 1 redo later.

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
| 10 | `icon-pustaka` | `keep` | Pustaka icon (`content_helper`). Almost the same book as no. 7, but the two never stand on one screen |
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
