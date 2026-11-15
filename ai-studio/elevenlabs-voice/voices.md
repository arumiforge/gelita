# Voices

Fill this in before the first take, and keep it current. The install step copies these details into each recording's `voice_profile`.

ElevenLabs model: v4 (`eleven_v4`)

| Speaker (`tokoh`) | Character | Indonesian voice (name, voice ID) | English voice (name, voice ID) | Settings (stability, similarity, style) |
|---|---|---|---|---|
| Narator | Neutral storyteller | | | |
| Jaka | Boy, curious and polite | | | |
| Mbah Kedu | Elderly man, calm and kind | Mbah Kedu (`H1jaNlN43es4HneQ6wdS`), custom voice | Mbah Kedu — English Narration (`fWH0Fha1DCawTa59sMfX`) | story: stability 0.65, similarity 0.85, [warmly] [slowly]; clues: stability 0.80, similarity 0.85, no tags; no effect |

## How the settings are used

Chosen by the owner on 15 November 2026 after a pilot of five takes (three settings for one story line, two clue lines). Model v4 has two voice settings, stability and similarity; it has no style and no speed setting, so the "style" in the column heading stays empty.

| Lines | `konteks` in `lines.csv` | Typed into ElevenLabs | Stability | Similarity |
|---|---|---|---|---|
| Story | `intro`, `map_intro`, `region_intro`, `level_open`, `scenario_open`, `level_done`, `ending`, `map_done` | `[warmly] [slowly] ` followed by the text | 0.65 | 0.85 |
| Clues | `hunt_clue`, `post_clue` | the text only, no tags | 0.80 | 0.85 |
| Situations and instructions (Narator) | `scenario_situation`, `challenge_instruction` | the text only, no tags | 0.80 | 0.85 |

- **Language:** `language_code` is set to `id` for Indonesian takes and `en` for English takes, so numbers and place names are read in the take's language.
- **English voice (owner, 15 November 2026):** Mbah Kedu's English takes use a voice of their own (`fWH0Fha1DCawTa59sMfX`, named *Mbah Kedu — English Narration* in ElevenLabs), with the same model, the same two rows above, and the same tags. With it, his `voice_profile` for English is exactly 200 characters, the limit; a longer voice name or settings cell would be cut off, so shorten something before adding to that row.
- **Direction tags** (v4 only) are words in square brackets before the text. They are not spoken and are not part of the line; the text after them is `teks_id` exactly as written. One pair of tags per line, the same pair for every story line. They cost about 18 characters of credits per line.
- **Clues are read flat on purpose.** They are part of scored questions (`README.md` → *Voice direction*), so they get no tags and a higher stability, the same for every clue.
- **No audio effect.** No reverb, filter, or background sound: the voice has to stay clear on school speakers, sit over the game's music, and match the voice actors' recordings that replace these takes later.
- **Mastering:** every take goes through `master.sh` before it is saved in `out/` (`README.md` → *Mastering*).
- **One take per line.** Changing a setting means redoing every line of that kind, or the speaker stops sounding like one person.

## Narator and Jaka: before the first take

Both get a synthetic voice too (owner, 15 November 2026). Neither has a voice yet: 47 lines each per language, 94 files per language. The list was checked on 15 November 2026: `lines.csv` matches the game's script line for line, and no file of theirs exists yet.

- **Settings.** The table above covers every `konteks` they have. Jaka: story row only (47 lines, six of them `map_intro` and `map_done`). Narator: story row for `intro` and `ending` (8 lines), the third row for the 24 `scenario_situation` lines (scored, so flat like the clues) and the 15 `challenge_instruction` lines (instructions, read plain and steady).
- **Room in the voice cell.** With the same settings cell as Mbah Kedu's, the voice cell (name and voice ID, backticks not counted) may be at most 52 characters per language, or the `voice_profile` is cut off at 200.
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

The four rows above are the agreed readings (`docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md` → *Arahan suara*; English ones set by the owner on 15 November 2026). No take has been made with them yet: if the voice still misreads one, adjust the typed text and keep the reading.
