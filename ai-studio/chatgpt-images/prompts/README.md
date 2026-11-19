# Ready-to-paste prompts for ChatGPT

**With Codex (repository access): use only `codex-all.md`.** One task makes every open row in order (style board, start page and ending story, Pustaka, and, once you have accepted the style board, the interface), committing after each picture. When it stops, run the same prompt again; it continues from `TASKS.md`. The step files below are for the ChatGPT app, where each conversation takes at most five attached files.

Start prompts for the rows of `../TASKS.md`. The owner copies the text block of one file into a **new** ChatGPT conversation (Codex or the ChatGPT app with this repository connected) and sends it. ChatGPT then reads `../AGENTS.md` and `../TASKS.md` itself; the prompts only say which rows to do and in which order. The picture prompts themselves stay in the documents the rows point at (`docs/revisi-10-analitik/40_PROMPT_GAMBAR_ANTARMUKA.md` for the rows of 18 November 2026).

ChatGPT refuses more than five attached files in one conversation, and every row attaches up to five. So each prompt makes ChatGPT work **one row per conversation**: it finishes one row, then tells the owner which row comes next, and the owner pastes `next-row.md` into a new conversation.

## Order

| Step | File | Rows | When |
|---|---|---|---|
| 1 | `1-style-board.md` | `U-00` | Now. Everything in the interface waits for it |
| 2 | `2-story-backgrounds.md` | `S-W`, `S-02` to `S-05` | Now, in parallel with step 1 |
| 3 | `3-accept-style-board.md` | sets `U-00` accepted and unlocks `U-01` to `U-35` | After the owner has looked at `out/reference.27.webp` and likes it |
| 4 | `4-interface.md` | `U-01` to `U-35` | After step 3 |
| 5 | `5-pustaka.md` | `PK-01` to `PK-32` | After step 4 (or earlier if the owner wants) |
| 6 | `6-skenario-digital.md` | `U-36`, `SD-01` to `SD-14` | Now (owner approved the Skenario Digital mockup, 19 November 2026) |
| any | `next-row.md` | the next open row of a step | Every time ChatGPT says "next: …" |
| any | `redo.md` | one row the owner wants made again | When a picture is not right |
| any | `inbox.md` | files the owner dropped in `../inbox/` | After generating pictures in the ChatGPT app by hand |

After a batch, ChatGPT commits only inside `ai-studio/chatgpt-images/` on its own branch and opens a pull request (`../AGENTS.md` → *Saving your work*). The owner then marks each row `accepted` or `redo: reason`, and asks a Claude Code session to install the accepted ones ("Install the accepted pictures from `ai-studio/chatgpt-images/`").

Every Pustaka row has its reference photos since 18 November 2026 (the owner's photos of Andong, Tidar, Umbul Jumprit and Parakan are in `../inbox/`, sources in `reference-sources-2026-11-18.json`).
