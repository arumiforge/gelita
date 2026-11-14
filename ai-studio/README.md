# AI studio

Work that other AI tools do for GELITA, one folder per tool. Each tool reads its own instructions, writes only in its own folder, and leaves finished files there. Claude Code sessions then install those files into the game. Nothing in this folder is loaded by the game itself.

| Folder | Tool | Makes | Instructions | Work list |
|---|---|---|---|---|
| `chatgpt-images/` | ChatGPT (Codex or the ChatGPT app) | Pictures: story backgrounds, two Susun Gambar pictures, three region maps, one challenge card | `chatgpt-images/AGENTS.md` | `chatgpt-images/TASKS.md` (35 pictures) |
| `elevenlabs-voice/` | ElevenLabs | Backup narration, 187 lines in two languages | `elevenlabs-voice/README.md` | `elevenlabs-voice/lines.csv` |

## How work moves

1. **The tool** makes files in its `out/` folder and marks them in its work list.
2. **The owner** looks or listens, and marks each one accepted or to redo. For pictures that is the status cell in `TASKS.md`; for recordings it is deleting a take that is not good enough.
3. **A Claude Code session** installs what was accepted, on its own branch with tests, and marks it installed. Ask it with, for example: "Install the accepted pictures from `ai-studio/chatgpt-images/`."

## For the Claude Code session that installs

- **Story backgrounds** (`bg.cerita-…`): one picture fills several slots (the *For the installer* cell lists them). Follow `docs/revisi-10-analitik/37_PROMPT_GAMBAR_SUSUN_DAN_CERITA.md` → *Cara memakai* step 5 and `38_…` → *Setelah gambar dibuat* no. 3. The interim pictures in `gelita-katalog/mockup/gambar-mockup/cerita-layar/` go once the real ones are in.
- **Susun Gambar**: only after the tile check and the two-person assembly test, and a re-check against `08` (37 → *Daftar periksa hasil*). The Candi Pawon picture note in the bank data changes with it (37 → *Catatan pemasangan*).
- **Region maps**: replace the catalogue file, then measure the five stops into `CatalogImporter::MAP_STOPS` and update `CatalogImportTest` (38 → *Setelah gambar dibuat* no. 1).
- **Challenge card**: replace `public/assets/ui/node-card-skenario.webp` (38 no. 2).
- **Recordings**: `elevenlabs-voice/README.md` → *Installing*. The manifest that marks them as synthetic is required.
- Pictures and recordings stay drafts of the developer until the owner has accepted them; cultural checks by the validators still apply (37 and 38 → *Kebijakan perubahan*). Nothing is installed on the production server without the owner.

## Adding a tool

Give it its own folder with one instruction file, one work list, and one `out/` folder, and add a row to the table above. Keep the rule that the tool writes only in its folder.
