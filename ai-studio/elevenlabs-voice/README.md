# ElevenLabs voice workspace

Backup narration for GELITA, generated with ElevenLabs. Owner decision, 14 November 2026: recordings by voice actors (real voices) stay the first choice; ElevenLabs is the backup, and no other synthetic voice is used.

Whoever works here (the owner in the ElevenLabs app, or an agent with ElevenLabs access) writes only inside `ai-studio/elevenlabs-voice/`. Installing recordings into the game is done later by a Claude Code session or by the owner on the server.

## What is here

| File | What it is |
|---|---|
| `lines.csv` | Every line to voice: 187 lines, each in Indonesian and English (about 24,000 characters per language). Also the done list: columns `berkas_id` and `berkas_en` say `ada` or `belum` |
| `voices.md` | The chosen voice per speaker and language, and any pronunciation fixes. Fill it in before the first take |
| `out/id/`, `out/en/` | The results, one file per line and language |

## Making a recording

1. Take a row of `lines.csv`. The text to voice is `teks_id` (Indonesian) or `teks_en` (English), exactly as written. The speaker is in `tokoh`: Narator, Jaka, or Mbah Kedu.
2. Use that speaker's voice from `voices.md`. One voice per speaker and language, the same for every line.
3. Save the take as `out/id/{kode_berkas}.mp3` or `out/en/{kode_berkas}.mp3`, for example `out/id/intro-01.mp3`. The file name is the whole link between a recording and its line, so it must match `kode_berkas` exactly. ElevenLabs' standard MP3 output works as it is.
4. Listen once: every word of the text is there, nothing is added, no cut-off at the end.

If a word is mispronounced (a Javanese name, a web address), fix it with a respelling in the ElevenLabs input only and write the respelling in `voices.md`. Never change `lines.csv`: it is generated from the game's script.

## Voice direction

For every line: warm and clear, the pace of someone reading to a ten-year-old. Jaka is a boy, Mbah Kedu an elderly man, the Narator a neutral storyteller.

Lines whose `konteks` is `scenario_situation` or `post_clue` are part of scored questions, and tone can give the answer away (`docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md` → *Arahan suara untuk situasi dan petunjuk yang dinilai*):

- Flat and friendly, the same pace and pitch for every situation. Never suspicious, scary, or urgent.
- Quoted messages are read plainly, not acted. No stress on words such as *sekarang*, *gratis*, *tinggal 3*, *resmi*, *dihapus hari ini*.
- Web addresses are read as written: `sekolah-kita.example/masuk` is "sekolah strip kita titik example garis miring masuk". The mark ✓ is read "bertanda centang". Emoji are not read.
- *Periksa Unggahan* clues are read without stressing the part that is the key.

## What is done

A line is done when its file exists. To refresh the done columns of `lines.csv` (and the line texts, if the script changed), a Claude Code session runs this on a development install (`tools/dev/serve.sh` sets one up):

```bash
php spark gelita:narration:list --csv=ai-studio/elevenlabs-voice/lines.csv --dir=ai-studio/elevenlabs-voice/out
```

If a line's text changed after its take was made, the take is out of date: delete the file and voice it again.

## Installing (not done from this folder)

1. Copy `out/id/` and `out/en/` to `public/assets/audio/narasi/id/` and `en/` on the install.
2. Write `public/assets/audio/narasi/produksi.json` with each file's sha256 and a `voice_profile` naming ElevenLabs, the model, and the voice from `voices.md`. With that manifest the game records the recordings as synthetic (`production_method = tts`); without it they would be recorded as real voices, which would be false in the research data (`App\Libraries\NarrationImporter::production()`). No script writes this manifest yet; the session that installs the first batch adds it.
3. Run `php spark gelita:narration:import`, then listen and approve in the panel (**Rekaman narasi**), checking the tone rules above too.

A voice actor's recording uploaded later under the same file code replaces the ElevenLabs one and is recorded as a real voice.

The research documents describe the narration as recorded by voice actors. Before ElevenLabs recordings are used with students, that change is recorded in `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md` and the researcher is told.
