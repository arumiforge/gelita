# Voices

Fill this in before the first take, and keep it current. The install step copies these details into each recording's `voice_profile`.

ElevenLabs model: v4 (`eleven_v4`)

| Speaker (`tokoh`) | Character | Indonesian voice (name, voice ID) | English voice (name, voice ID) | Settings (stability, similarity, style) |
|---|---|---|---|---|
| Narator | Neutral storyteller | narator (`RiC7WfZdhpblZ6cqa2Dr`) | narator (`RiC7WfZdhpblZ6cqa2Dr`) | story: stability 0.65, similarity 0.85, [warmly] [slowly]; other lines: stability 0.80, similarity 0.85, no tags; no effect |
| Jaka | Boy, curious and polite | Jaka (`XvqPaxRmAYmYehRouVnM`) | Jaka english (`N4XCfNUh8sOhcRAo7UPE`) | story: stability 0.65, similarity 0.85, [warmly] [slowly]; no effect |
| Mbah Kedu | Elderly man, calm and kind | Mbah Kedu (`H1jaNlN43es4HneQ6wdS`), custom voice | Mbah Kedu — English Narration (`fWH0Fha1DCawTa59sMfX`) | story: stability 0.65, similarity 0.85, [warmly] [slowly]; clues: stability 0.80, similarity 0.85, no tags; no effect |

## Several ElevenLabs accounts

A voice ID works only in the account that made it. The five voices are kept in `voices/` (description, labels, preview, clone samples, and the voice ID and share link per account), with the steps to bring them to another account: `voices/README.md`. When the takes move to another account, the voice table above gets that account's voice IDs.

Narator's takes are made on `akun-1` with its own voice, in both languages. A second Narator voice designed on `akun-2` (Free plan, 15 November 2026) is not used: the owner went back to `akun-1` the same day, before any take (`voices/narator/voice.json`).

## How the settings are used

Chosen by the owner on 15 November 2026 after a pilot of five takes (three settings for one story line, two clue lines). Model v4 has two voice settings, stability and similarity; it has no style and no speed setting, so the "style" in the column heading stays empty.

| Lines | `konteks` in `lines.csv` | Typed into ElevenLabs | Stability | Similarity |
|---|---|---|---|---|
| Story | `intro`, `map_intro`, `region_intro`, `level_open`, `scenario_open`, `level_done`, `ending`, `map_done` | `[warmly] [slowly] ` followed by the text | 0.65 | 0.85 |
| Clues | `hunt_clue`, `post_clue` | the text only, no tags | 0.80 | 0.85 |
| Situations and instructions (Narator) | `scenario_situation`, `challenge_instruction` | the text only, no tags | 0.80 | 0.85 |

- **Language:** `language_code` is set to `id` for Indonesian takes and `en` for English takes, so numbers and place names are read in the take's language.
- **Narator in English (owner, 15 November 2026): slower, one pace.** The first English pilots ran at 146–194 words per minute, too fast for grade 5. Tags barely slow model v4 down, so every English Narator take is mastered with `WPM=130 WORDS=<words in teks_en> ./master.sh …`, which slows it to 130 words per minute with ffmpeg's `atempo` (pitch kept; never sped up; rubberband was tried first and dropped because the owner heard an echo in earphones). Story lines are typed with `[warmly] [slowly] [clearly] `; scored situations and instructions stay without tags. All 47 measure 129–130 words per minute. Indonesian takes keep their own pace (no `WPM`).
- **English voice (owner, 15 November 2026):** Mbah Kedu's English takes use a voice of their own (`fWH0Fha1DCawTa59sMfX`, named *Mbah Kedu — English Narration* in ElevenLabs), with the same model, the same two rows above, and the same tags. With it, his `voice_profile` for English is exactly 200 characters, the limit; a longer voice name or settings cell would be cut off, so shorten something before adding to that row.
- **Direction tags** (v4 only) are words in square brackets before the text. They are not spoken and are not part of the line; the text after them is `teks_id` exactly as written. One pair of tags per line, the same pair for every story line. They cost about 18 characters of credits per line.
- **Clues are read flat on purpose.** They are part of scored questions (`README.md` → *Voice direction*), so they get no tags and a higher stability, the same for every clue.
- **No audio effect.** No reverb, filter, or background sound: the voice has to stay clear on school speakers, sit over the game's music, and match the voice actors' recordings that replace these takes later.
- **Mastering:** every take goes through `master.sh` before it is saved in `out/` (`README.md` → *Mastering*).
- **One take per line.** Changing a setting means redoing every line of that kind, or the speaker stops sounding like one person.

## Narator and Jaka: before the first take

Both get a synthetic voice too (owner, 15 November 2026), approved by the researcher like Mbah Kedu's and still to be reviewed by the validators (`docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`, 15 November 2026). Their voices exist since 15 November 2026 (table above; designed by the owner: Narator one voice for both languages, Jaka one per language), but no take yet: 47 lines each per language, 94 files per language. The settings cells follow the rows below and were not piloted yet: make two or three pilot takes per voice and correct the cell before the rest. The list was checked on 15 November 2026: `lines.csv` matches the game's script line for line, and no file of theirs exists yet.

**State (15 November 2026): both are done, accepted by the owner, and installed on a development install.** Narator's 94 takes were made first (decision log *Narator's ElevenLabs takes…*), Jaka's 94 the same day (decision log *Jaka's ElevenLabs takes*). The points below were how the work was prepared and stay as the record.

- **Jaka in English: the same pace as Narator.** Story row with `[warmly] [slowly] [clearly] `, `Kedu` typed `/kəˈdu/`, and every take mastered with `WPM=130 WORDS=<words in teks_en>`. 40 of 47 measure 129–130 words per minute. The other seven are short lines with pauses (`dialog-wonosobo-10` *I'm scared, Mbah...* at 98, `dialog-magelang-05`, `tuntas-temanggung-05`, `tuntas-magelang-05`, `dialog-temanggung-04`, `layar-wonosobo-03`, `tuntas-wonosobo-04` at 110–125), which the voice read slower on its own; `master.sh` never speeds a take up.

- **Settings.** The table above covers every `konteks` they have. Jaka: story row only (47 lines, six of them `map_intro` and `map_done`). Narator: story row for `intro` and `ending` (8 lines), the third row for the 24 `scenario_situation` lines (scored, so flat like the clues) and the 15 `challenge_instruction` lines (instructions, read plain and steady).
- **Room in the voice cell.** With the same settings cell as Mbah Kedu's, the voice cell (name and voice ID, backticks not counted) may be at most 52 characters per language, or the `voice_profile` is cut off at 200. `NarrationVoicesTest` checks every speaker and language.
- **Credits.** About 12,600 to 12,700 per language for the two speakers together (11,642 characters of Indonesian text, 11,715 of English, plus about 1,000 for the tags of the 55 story lines).
- **Lines whose text holds something a voice may misread.** Listen to these first, and write any respelling that was needed in the table below.

| What | Lines | To hear |
|---|---|---|
| `4 × 4` | `instruksi-mgl-1`, `instruksi-wnb-1` (in brackets there) | "empat kali empat" / "four by four" |
| `✓` | `skenario-wnb-5-a02` | "bertanda centang" / "with a check mark" (owner, 15 November 2026; typed as in *Pronunciation fixes*) |
| Web address | `skenario-wnb-5-b02` | "sekolah strip kita titik example garis miring masuk" / "sekolah dash kita dot example slash masuk" (owner, 15 November 2026; typed as in *Pronunciation fixes*) |
| `HP`, `SMS`, `SD` | `skenario-tmg-5-b01`, `skenario-mgl-5-a04`, `skenario-wnb-5-a01`, `-a02`, `-b01` (Indonesian) | Letter by letter: "ha-pe", "es-em-es", "es-de" |
| English words in Indonesian lines | `game`, `game-mu`, `levelmu` (`skenario-tmg-5-a02`, `-b02`, `skenario-mgl-5-a02`, `skenario-wnb-5-a02`), `puzzle` (`instruksi-wnb-1`) | Said the way children say them, not letter by letter |
| Capitals | `BENAR`/`SALAH`, `TRUE`/`FALSE` in `instruksi-tmg-3`, `-mgl-3`, `-wnb-3` | Same loudness as the rest, not spelled out |
| Digit | `tinggal 3` / `only 3 places left` in `skenario-wnb-5-a01` | Read as a number, no stress (scored line) |
| Sound word | `Brrr...` in `dialog-wonosobo-01` (Jaka) | A shiver, not letters |
| Local words in English takes | Mbah, kentongan, kuda lumping, wayang, Topeng Ireng, kuluk, getuk, bundengan, Carica, embun upas, Telaga Warna, Dieng | Indonesian pronunciation |

- **Same text, separate files.** `instruksi-tmg-2` and `instruksi-mgl-2` have the same text, and so do `instruksi-tmg-5`, `-mgl-5`, `-wnb-5`. Each still gets its own file (`README.md` → *Making a recording*, step 5).
- **`peta-03` (Jaka) has a new text since 15 November 2026.** It names *Kenali* after the region is done, as research mode opens it, and both button names (*Mulai atau Lanjut*); `lines.csv` holds the new text.

## Pronunciation fixes

Respellings typed into ElevenLabs instead of the written text. The text in `lines.csv` is never changed.

| Written | Typed into ElevenLabs | Language | Used in (file codes) |
|---|---|---|---|
| `"Tim Resmi Game Bintang ✓"` | `"Tim Resmi Game Bintang, bertanda centang"` | Indonesian | `skenario-wnb-5-a02` |
| `"Official Game Bintang Team ✓"` | `"Official Game Bintang Team, with a check mark"` | English | `skenario-wnb-5-a02` |
| `sekolah-kita.example/masuk` | `sekolah strip kita titik example garis miring masuk` | Indonesian | `skenario-wnb-5-b02` |
| `sekolah-kita.example/masuk` | `sekolah dash kita dot example slash masuk` (the Indonesian words keep their Indonesian sound) | English | `skenario-wnb-5-b02` |
| `Kedu` (also in `Mbah Kedu`) | `/kəˈdu/` (IPA, the Javanese *e pepet*; model v4 reads IPA between slashes) | English | every English line with Kedu, from Narator's English takes on (owner, 15 November 2026); Indonesian takes keep the written word, and Mbah Kedu's accepted English takes stay as they are |

The four rows above are the agreed readings (`docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md` → *Arahan suara*; English ones set by the owner on 15 November 2026). No take has been made with them yet: if the voice still misreads one, adjust the typed text and keep the reading.
