# ElevenLabs voice workspace

Backup narration for GELITA, generated with ElevenLabs. Owner decision, 14 November 2026: recordings by voice actors (real voices) stay the first choice; ElevenLabs is the backup, and no other synthetic voice is used.

Whoever works here (the owner in the ElevenLabs app, or an agent with ElevenLabs access) writes only inside `ai-studio/elevenlabs-voice/`. Installing recordings into the game is done later by a Claude Code session or by the owner on the server.

## What is here

| File | What it is |
|---|---|
| `lines.csv` | Every line to voice: 187 lines, each in Indonesian and English (about 24,000 characters per language). Also the done list: columns `berkas_id` and `berkas_en` say `ada` or `belum` |
| `voices.md` | The model, the chosen voice per speaker and language, and any pronunciation fixes. Fill it in before the first take. The install command reads the line `ElevenLabs model:` and the first table, so keep the table's columns and the speaker names as they are |
| `out/id/`, `out/en/` | The results, one file per line and language, already mastered |
| `master.sh` | Brings one take to the game's narration format (needs `ffmpeg`): `./master.sh RAW.mp3 out/id/{kode_berkas}.mp3` |

## Making a recording

1. Take a row of `lines.csv`. The text to voice is `teks_id` (Indonesian) or `teks_en` (English), exactly as written. The speaker is in `tokoh`: Narator, Jaka, or Mbah Kedu.
2. Use that speaker's voice and settings from `voices.md` (→ *How the settings are used*: story lines and clue lines have their own row). One voice per speaker and language, the same for every line.
3. Download the take (ElevenLabs' standard MP3) and master it straight into `out/`: `./master.sh RAW.mp3 out/id/{kode_berkas}.mp3`, or `out/en/…` for English, for example `out/id/intro-01.mp3`. The file name is the whole link between a recording and its line, so it must match `kode_berkas` exactly. Raw takes are not kept in the repository.
4. Listen once: every word of the text is there, nothing is added, no cut-off at the end.
5. Lines with the same text (some `a`/`b` clue pairs) still get one file each: pair a download with its line by the label you gave it in ElevenLabs, never by its text.

If a word is mispronounced (a Javanese name, a web address), fix it with a respelling in the ElevenLabs input only and write the respelling in `voices.md`. Never change `lines.csv`: it is generated from the game's script.

## Voice direction

For every line: warm and clear, the pace of someone reading to a ten-year-old. Jaka is a boy, Mbah Kedu an elderly man, the Narator a neutral storyteller.

Lines whose `konteks` is `scenario_situation` or `post_clue` are part of scored questions, and tone can give the answer away (`docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md` → *Arahan suara untuk situasi dan petunjuk yang dinilai*):

- Flat and friendly, the same pace and pitch for every situation. Never suspicious, scary, or urgent.
- Quoted messages are read plainly, not acted. No stress on words such as *sekarang*, *gratis*, *tinggal 3*, *resmi*, *dihapus hari ini*.
- Web addresses are read as written: `sekolah-kita.example/masuk` is "sekolah strip kita titik example garis miring masuk". The mark ✓ is read "bertanda centang". Emoji are not read.
- *Periksa Unggahan* clues are read without stressing the part that is the key.

## Mastering

`master.sh` trims silence at both ends, moves the take to the game's narration loudness with one fixed gain, holds the highest peaks under about −1.5 dBFS, adds 0.3 s of silence before and after, and writes MP3 mono, 44.1 kHz, 96 kbps (the format of `docs/naskah-cerita.md`). Finished files measure about −16.5 LUFS, within 0.1 of each other. It adds no reverb, filter, or noise, and it does not change pace or pitch.

Raw ElevenLabs takes differ by up to 3.5 LU from line to line, so a take that skips this step sounds louder or softer than its neighbours.

In a shell loop that reads a list from standard input, run each take through `master.sh` as usual: its `ffmpeg` calls use `-nostdin`, so they no longer swallow the rest of the list.

## What is done

A line is done when its file exists. State on 15 November 2026: Mbah Kedu's 93 lines are in both `out/id/` and `out/en/`, made with model v4 through the ElevenLabs flows *GELITA · Mbah Kedu (id) · takes* and *GELITA · Mbah Kedu (en) · takes* (one node per line, labelled with its `kode_berkas`). The owner accepted both (15 November 2026); the install was checked on a development install, and a server installs them with the steps below (also step 4 of *Instalasi baru* in `docs/08_DEPLOYMENT.md`). Narator and Jaka are still to do in both languages. To refresh the done columns of `lines.csv` (and the line texts, if the script changed), a Claude Code session runs this on a development install (`tools/dev/serve.sh` sets one up):

```bash
php spark gelita:narration:list --csv=ai-studio/elevenlabs-voice/lines.csv --dir=ai-studio/elevenlabs-voice/out
```

If a line's text changed after its take was made, the take is out of date: delete the file and voice it again.

## Installing (not done from this folder)

A Claude Code session, or the owner on the server, runs these on the install: a trial run first, then the install, then the import.

```bash
php spark gelita:narration:install --dry-run
```

```bash
php spark gelita:narration:install
```

```bash
php spark gelita:narration:import
```

1. `gelita:narration:install` copies `out/id/` and `out/en/` to `public/assets/audio/narasi/id/` and `en/`, and lists every copied file in `public/assets/audio/narasi/produksi.json` with its sha256 and a `voice_profile` built from `voices.md` (model, the speaker's voice for that language, settings). With that manifest the game records the takes as synthetic (`production_method = tts`); without it they would be recorded as real voices, which would be false in the research data. `--dry-run` shows what would happen and writes nothing.
2. It stops before copying anything if `voices.md` has no model, or no voice for a speaker and language that has takes in `out/`. Fill the sheet in first.
3. A line that already has a real recording (a voice actor's file in the narration folder, or one uploaded in the panel) keeps it: the take is listed as *Dilewati*. `--timpa` replaces real recordings too; use it only when the owner asks.
4. A new take of a line replaces the earlier ElevenLabs take of that line. Running the command again without new takes changes nothing.
5. `gelita:narration:import` then registers the files as drafts. Listen and approve in the panel (**Rekaman narasi**), checking the tone rules above too.

A voice actor's recording uploaded later under the same file code replaces the ElevenLabs one and is recorded as a real voice.

The research documents describe the narration as recorded by voice actors. Before ElevenLabs recordings are used with students, that change is recorded in `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md` and the researcher is told.
