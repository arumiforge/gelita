# Settings

Keep this current; the install step copies the two model lines into `public/assets/audio/produksi.json` for every file it installs. Checked on 15 November 2026 against what the account offers (music: `eleven_music_v1`, `eleven_music_v2`, `eleven_music_v2_5`, the last the default; sound effects: only `eleven_text_to_sound_v2`). If a take is made with another model, change the line before installing it.

ElevenLabs music model: Eleven Music v2.5 (`eleven_music_v2_5`)
ElevenLabs sound-effects model: Sound Effects v2 (`eleven_text_to_sound_v2`)

## How the settings are used

| Sounds | ElevenLabs tool | Length to ask for | Other settings |
|---|---|---|---|
| Music (`music/…`, 15 tracks) | Music | `durasi_detik` of `sounds.csv` plus about 10 s (the mastering cuts 3 s for the loop, and silence at both ends) | Instrumental on (`instrumental`, or lyrics type *instrumental*); the prompt of `sounds.csv` as written |
| Sound effects (`sfx/…`, 6 effects) | Sound Effects | `durasi_detik`, at least 0.5 s (`sfx/click`: ask 0.5 s; the mastering trims the silence after the tap) | Loop off; prompt influence about 0.6 (the app's default is 0.3) so the result stays close to the prompt |

- **One look for all music.** Keep the same model and the same way of prompting for every track, or the game stops sounding like one piece. If one track needs a different prompt to come out right, write the change in the table below.
- **No effect added afterwards.** Every take goes through `master.sh` and nothing else (`README.md` → *Mastering*).
- **Takes that were remade**, prompt changes, and anything a later session should know go here:

| Sound | What was changed, and why | Date |
|---|---|---|
| | | |
