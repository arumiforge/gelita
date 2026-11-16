# ChatGPT image workspace

You are generating pictures for GELITA, an educational game for grade 5 students about the Kedu region (Temanggung, Magelang, Wonosobo). This folder is your whole workspace. Your job is three things only: generate pictures, name and convert the files, and keep the task list current.

## What you may touch

- **Write only inside `ai-studio/chatgpt-images/`**: files in `out/` and `inbox/`, and the **Status** and **Notes** cells of `TASKS.md`.
- **Read anything** the tasks point at: the prompt documents in `docs/revisi-10-analitik/`, the reference pictures in `ai-studio/game-images/assets/referensi/`, the mock-ups in `docs/revisi-10-analitik/gambar/38/`.
- **Never change anything else**: no code, no `ai-studio/game-images/`, no `public/`, no documents, no prompt text. Never run the game's commands (`php spark …`, `composer`, tests). Installing pictures into the game is done later by a Claude Code session.

## Start of every session

1. Read `TASKS.md`. It is the single list of what exists to do and what is done.
2. Work on the rows the owner names. If the owner names none, take rows with status `redo: …` first, then `todo`, from the top.
3. Look in `inbox/`. If it holds files, handle them first (see *Files in `inbox/`*).

## One picture, step by step

1. Open the prompt: the **Prompt** cell names a file and a heading. Under that heading is one fenced `text` block. Use it word for word. Do not add characters, text, or objects, and do not shorten it.
2. Attach the files in the **Attach** cell. "style" means `ai-studio/game-images/assets/referensi/reference.06.webp` or `reference.07.webp`, for render finish and mood only. For a region map, also say: *"Use the layout mock-up only for the positions of the five clearings and the path; use the old map only for mood and finish."*
3. A row that needs a **licensed photo** (the two Susun Gambar pictures) can only be made when the owner has put that photo in `inbox/` or named it. Without it, set the status to `blocked: needs reference photo` and go to the next row.
4. Generate, then check the result against *Checks* below. If a check fails, regenerate or fix in the same conversation, naming the failed check. Three failed attempts: set `blocked: <which check keeps failing>` and move on.
5. Save the picture in `out/` under exactly the name in the **Save in `out/` as** cell, as WebP (quality 90), at exactly the canvas size in the **Canvas** cell. Scale proportionally; never stretch, never add borders.
6. In `TASKS.md`, set the row's status to `generated` and write the date in **Notes** (for example `2026-11-15`). Add one short note only if the owner needs to know something (for example "sky slightly cropped at the top").

### A row marked `redo: …`

The earlier picture failed the check named in the status, and its prompt may have been rewritten since. So:

1. Open the prompt document again and use the text that is there now. Never reuse a prompt from memory or from an earlier conversation.
2. Start a new conversation and generate from nothing. Do not attach, edit, or inpaint the earlier picture: it carries the fault.
3. Before saving, look for the fault named in the status first, then run every other check.
4. Save under the same file name (the old file in `out/` is replaced), set the status to `generated`, and add the new date in **Notes** after what is already there.

## Checks

Every picture:

- No readable text, numbers, logos, app icons, or screen contents. Boards, signs, phones, and tablets are blank, blurred, or misty.
- No Jaka, no Mbah Kedu, no real or famous person. No face is turned toward the viewer: people are seen from the side or from behind, or look at what they are doing. Wherever there are children, at least one figure is unmistakably an adult (clearly taller and broader than the children, not an older child), and nobody does anything dangerous.
- The number of people is the number the prompt gives. When it says "exactly three children", count them.
- Temples, costumes, and instruments match the attached photo or reference. Nothing from another religion or region.
- The file is WebP, has the exact canvas size, and has the exact file name.

Story backgrounds (`bg.cerita-…`):

- The lower third is calm and a little darker (the game puts a text panel there): no buildings, no bright or reflecting water, no rows of small trees or other small detail. Squint at it: it should read as one quiet band. The top 8% has no important detail.
- Only what the prompt names is in the picture. No object, warning sign, or screen content that a quiz could ask about.
- The mist (*Kabut Lupa*) is the same blue-grey (#8C97AA to #5B6478) in every picture, never shaped like letters, eerie but not frightening.
- Temanggung: no tobacco plants, cigarettes, or smoke. Wonosobo: no crater, steam, smoke, cracked ground, or disaster. Steam means any steam, also from cooking pots, bowls, or cups.

Susun Gambar (`challenge.item.…`):

- One whole scene, no collage. Every cell of the grid (3 × 3 or 4 × 4, as the task says) has its own distinct detail. No plain, blurred, or dark areas. Morning or daytime.

Region maps (`map.region.…`):

- Five clearings are fully visible, in order 1 to 5 along one path, not covered by leaves. No people. The mist is only at clearing 5.

Challenge card (`node-card-skenario`):

- One phone in the centre with plain bubbles and thin mist, two blank choice cards, a small shield. Still readable in sepia at 320 px wide. No people.

## Rows from the picture review (codes `R-…`)

The owner reviewed every picture of the game on 15 November 2026 (`ai-studio/game-images/REVIEW.md`). Pictures marked for a fix are the rows `R-01` and up; their prompts are in `docs/revisi-10-analitik/39_PROMPT_GAMBAR_PERBAIKAN_TELAAH.md`. For these rows the prompt and the lines below come before the general checks above:

- **Attachments** are exactly the files in the row's **Attach** cell, at the paths written there, from the repository root. "style" keeps its meaning. Never search the repository for a similar file and never guess a location. If a path in the cell does not exist, do not make the picture: set the status to `blocked: missing file <path>`. **At most five files per conversation** (ChatGPT refuses more): no cell lists more than five, "style" counting as one; if one does, do not choose among them yourself: set `blocked: more than 5 attachments` and leave it for a Claude session.
- **`R-31` to `R-33` are edits of the round character avatars and show Jaka or Mbah Kedu on purpose.** Edit with a mask over the character only, as `R-19`: the carved gold ring and the gold background stay locked, only the expression changes. The check "No Jaka, no Mbah Kedu" does not apply to these three rows; "no letters" still does. `R-27` to `R-30` are story backgrounds with no people and no light rays.
- **`R-03` to `R-05` (Tips Jaka) show Jaka on purpose.** He must match the attached character sheet and sprite: red scarf, cream shirt, backpack, no headcloth. The check "No Jaka" does not apply to these three rows; "no letters" still does.
- **`R-12` and later rows of a `redo later` picture use licensed reference photos** from `inbox/`, named in the row's **Attach** cell. Follow the photos for the forms the prompt names; never copy a photo, its colours or its modern details (railings, offerings, signs).
- **`R-19` is an edit, not a new picture** (like the test pictures): keep the avatar's ring, diamond points, cream disc and size exactly; only the emblem in the middle changes. Edit with a **mask over the emblem only**, so the ring, the diamond points and the disc stay locked; a plain regeneration keeps changing them (ChatGPT's own advice, 16 November 2026). Start again from the original avatar, never from a failed result. `R-25` is the same kind of edit: mask the flag disc only, the gold ring stays locked.
- **`R-14` is a map and carries text on purpose:** exactly the place names of its *All text* list. The check "No readable text" does not apply to it; read every word against that list instead, as for the test pictures.
- **`R-02` is one cut-out object** on a transparent background. Nothing may mark it as an answer: no glow, outline, label or arrow.
- **`R-06` and up are test pictures: an edit, not a new picture.** Attach the current picture and ask for the same picture with only the changes the prompt names. These pictures carry text on purpose, so the check "No readable text" does not apply. Instead, read every word of the result against the list *All text* in the prompt, the unchanged parts too, and compare the result with the old picture: nothing else may have changed. If an edit damages another part, start again from the old picture, never from the failed result. They stay black-and-white line art.
- A row that is `blocked: waiting for the owner's yes` is not made until the owner changes its status.

The interface art of the game (buttons, plates, frames, small icons) will come as further rows after its sizes are measured on a Chromebook screen. Its one look is the carved gold of `public/assets/ui/round-*.webp`: a thick gold frame carved in deep relief, a dark stone or navy face, the symbol embossed in the same gold, light from the upper left.

## Files in `inbox/`

The owner sometimes generates pictures in the ChatGPT app and drops the downloads here with their original names. For each file:

1. Work out which `TASKS.md` row it belongs to. If you cannot tell for certain, leave the file and ask the owner; never guess.
2. Run the checks. If it passes, convert it to WebP at the row's canvas size, save it in `out/` under the row's file name, delete the file from `inbox/`, and set the status to `generated`.
3. If it fails a check, leave it in `inbox/` and set the row to `redo: <failed check>`.

A licensed reference photo in `inbox/` is not a result: use it as an attachment and leave it there.

## Statuses

| Status | Set by | Meaning |
|---|---|---|
| `todo` | — | Not made yet |
| `generated` | you | The file is in `out/` and passed your checks |
| `blocked: reason` | you | Cannot be made now; the reason says what is missing |
| `accepted` | the owner | Approved; do not touch the file again |
| `redo: reason` | the owner | Make it again under the same file name, then set `generated` |
| `installed` | a Claude Code session | In the game; do not touch the file again |

Never change a row that is `accepted` or `installed`, and never overwrite its file.

## Saving your work

- Commit only files in this folder. Message: `ChatGPT images: <codes>`, for example `ChatGPT images: T-K1, T-K2`.
- Commits carry the repository owner's identity (`CLAUDE.md` → *Identity and attribution*) and no tool or co-author lines.
- Work on your own branch and open a pull request into `main`. Never push to `main`.

## Not your job

Say so in **Notes** and stop if a task seems to need any of these: changing a prompt, measuring positions on a map, editing `ai-studio/game-images/data.json`, installing pictures, checking cultural facts, or deciding whether a picture is good enough for the study. The owner decides the last one; a Claude Code session does the rest.
