# ElevenLabs music and sound-effects workspace

Music and sound effects for GELITA, generated with ElevenLabs. Owner decision, 15 November 2026: music and sound effects are made the same way as the backup narration (`../elevenlabs-voice/`): with ElevenLabs, in this folder, mastered with this folder's script, accepted by the owner, and installed by a Claude Code session with one command. The game's current files (gamelan synthesized by the removed generator `tools/audio/`, 14 and 19 October 2026) stay in use until a take replaces them, one file at a time.

Whoever works here (the owner in the ElevenLabs app, or an agent with ElevenLabs access) writes only inside `ai-studio/elevenlabs-sound/`.

## What is here

| File | What it is |
|---|---|
| `sounds.csv` | The work list: every sound the game plays, 15 music tracks and 6 sound effects. Per row: `kode` (the file name), `jenis`, whether it loops (`putar_ulang`), the length to aim for (`durasi_detik`), the format and loudness `master.sh` gives it (`kbps`, `lufs`, or `puncak_db` for the click, which is too short to measure), where it plays (`layar`), its mood (`suasana`), and the `prompt` to type into ElevenLabs |
| `settings.md` | The ElevenLabs model per kind and how the settings are used. The install command reads its two `model:` lines, so keep them |
| `out/music/`, `out/sfx/` | The results, one file per sound, already mastered, named after `kode` (`out/music/map.mp3`, `out/sfx/correct.mp3`) |
| `master.sh` | Brings one take to the game's format (needs `ffmpeg`): `./master.sh music/map RAW.mp3` |

The rows and file names come from the game (`App\Libraries\AssetChecklist::SOUNDS`). Never add, rename, or remove a row here: a file under another name is not installed.

## Making a take

1. Take a row of `sounds.csv`. Music is made with ElevenLabs **Music**, sound effects with **Sound Effects**; length and settings are in `settings.md`.
2. Type the `prompt` exactly as written. If a result is not right, try again with the same prompt first; change the prompt only when that does not help, and write the change in `settings.md`.
3. Download the take (MP3) and master it straight into `out/`:

   ```bash
   ./master.sh music/map RAW.mp3
   ```

   The script writes `out/music/map.mp3`. When only part of a take is good (a music take that starts with an intro or ends with a closing phrase), give the seconds to keep: `./master.sh music/map RAW.mp3 6 70` keeps 6 s to 70 s. Raw takes are not kept in the repository.
4. Listen once, the music **twice round** (play it on repeat): the join at the loop point must not be heard, and nothing in the track may stand out enough to pull a child's attention from reading.

## Direction

- **Javanese gamelan** for everything (owner, 19 October 2026): the instruments, tunings (`slendro`, `pelog`) and forms named in each prompt. No vocals, no singing, no Western drums or pads.
- **Music sits under a voice.** Narration plays on top of most tracks; the game lowers the music to a fifth while a voice speaks. So: no sudden loud strokes, no build-ups, the same energy from start to end.
- **Loops.** Every music track repeats without a pause. Ask for no intro, no ending, no fade; `master.sh` folds the last 3 seconds over the first ones so the end runs into the start.
- **`challenge` stays very quiet**: it plays while children read and answer scored questions.
- **`wrong` never scolds**: soft, low, short. No buzzer.
- **`opening`** is misty and a little mysterious, never frightening: the listeners are ten years old.
- **Sound effects are short and dry**, with no reverb tail, except `shard` and `region-done`, which may ring out.

## Mastering

`master.sh` reads the row of the given `kode` in `sounds.csv` and:

- **music**: cuts to START–END when given, trims silence at both ends, folds the last 3 s over the first 3 s with an equal-power crossfade (a seamless loop, 3 s shorter than the trimmed take), sets the loudness of `lufs` with one fixed gain, holds the highest peaks under about −1.5 dBFS, and writes MP3 stereo 44.1 kHz at `kbps`. No fade at either end.
- **sound effects**: trims silence, sets the loudness of `lufs` (or the peak of `puncak_db`), adds a 2 ms fade in and a 20 ms fade out against clicks, MP3 stereo 44.1 kHz at `kbps`.

The targets are those of the current files (measured 15 November 2026), so a new take sits at the same level as the files around it: music −20.4 to −21.9 LUFS (the calm tracks quietest, all well under the narration's −16.5), effects −15.4 to −19.1 LUFS. It adds no reverb, filter, or noise. It warns when the result is far from `durasi_detik`; a different length is fine if it sounds right.

## Accepting

A sound is done when its file exists in `out/`. The owner listens and deletes a file that is not good enough; what is left in `out/` counts as accepted when the owner says so (in the pull request or to the installing session). Sounds can be made and installed in any order; a sound without a take keeps the game's current file.

## Credits and licence

ElevenLabs' terms for music and sound effects depend on the plan the account is on. Before the first take is installed, the owner checks that the plan allows using the results in GELITA, and the installing session writes the credit in `README.md` → *Lisensi dan kredit* and in `docs/audio.md`. The credits are shared with the narration: Narator's and Jaka's lines still need about 12,600 per language (`../elevenlabs-voice/voices.md`).

## Installing (not done from this folder)

A Claude Code session, or the owner on the server, runs a trial first, then the install:

```bash
php spark gelita:sound:install --dry-run
```

```bash
php spark gelita:sound:install
```

1. `gelita:sound:install` copies `out/music/` and `out/sfx/` over `public/assets/audio/music/` and `sfx/`, and lists every installed file in `public/assets/audio/produksi.json` with its sha256 and the model from `settings.md`. The manifest is the record of which files are synthetic, and it gives the files a new cache version, so browsers load the new sound instead of an old copy.
2. It stops before copying anything if `settings.md` has no model for a kind that has takes, or if a file is not a whole MP3. A file whose name is not in `sounds.csv` is reported (with the name it probably meant) and not copied.
3. Running it again without new takes changes nothing. A new take replaces the earlier one.
4. In a session the installed files are committed on the session's branch (the game ships its music and effects in the repository, unlike narration). On a server that uses asset packages, build a new package afterwards (`php spark gelita:paket:bangun`) so devices get the new files.
