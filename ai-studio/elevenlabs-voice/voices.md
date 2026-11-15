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
| Story | `intro`, `region_intro`, `level_open`, `level_done`, `scenario_open`, `ending` | `[warmly] [slowly] ` followed by the text | 0.65 | 0.85 |
| Clues | `hunt_clue`, `post_clue` | the text only, no tags | 0.80 | 0.85 |

- **Language:** `language_code` is set to `id` for Indonesian takes and `en` for English takes, so numbers and place names are read in the take's language.
- **English voice (owner, 15 November 2026):** Mbah Kedu's English takes use a voice of their own (`fWH0Fha1DCawTa59sMfX`, named *Mbah Kedu — English Narration* in ElevenLabs), with the same model, the same two rows above, and the same tags. With it, his `voice_profile` for English is exactly 200 characters, the limit; a longer voice name or settings cell would be cut off, so shorten something before adding to that row.
- **Direction tags** (v4 only) are words in square brackets before the text. They are not spoken and are not part of the line; the text after them is `teks_id` exactly as written. One pair of tags per line, the same pair for every story line. They cost about 18 characters of credits per line.
- **Clues are read flat on purpose.** They are part of scored questions (`README.md` → *Voice direction*), so they get no tags and a higher stability, the same for every clue.
- **No audio effect.** No reverb, filter, or background sound: the voice has to stay clear on school speakers, sit over the game's music, and match the voice actors' recordings that replace these takes later.
- **Mastering:** every take goes through `master.sh` before it is saved in `out/` (`README.md` → *Mastering*).
- **One take per line.** Changing a setting means redoing every line of that kind, or the speaker stops sounding like one person.

Narator and Jaka have no voice yet. When they get one, use the same two rows: story settings for story lines, clue settings for the Narator's `scenario_situation` lines (also scored). `challenge_instruction` lines are instructions, not questions; read them like clues, plain and steady.

## Pronunciation fixes

Respellings typed into ElevenLabs instead of the written text. The text in `lines.csv` is never changed.

| Written | Typed into ElevenLabs | Language | Used in (file codes) |
|---|---|---|---|
