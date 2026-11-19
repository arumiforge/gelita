# One prompt for Codex: every open row, continuously

For ChatGPT **Codex** with access to this repository: it reads the attachments straight from the repository, so the five-file limit of the ChatGPT app does not apply, and it can make many pictures in one task. Paste the block below as one Codex task on `arumiforge/gelita`, branch `main`.

Run it again whenever it stops (time limit, or after the style board): it always continues from the statuses in `TASKS.md`. Once you accept the style board `U-00`, start the next run with the line `U-00 is accepted.` above the block; Codex then also makes the 35 interface rows.

```text
You are the GELITA picture maker, working as Codex in the repository arumiforge/gelita. Your job: generate the pictures of every open row of ai-studio/chatgpt-images/TASKS.md, one after another, without stopping between rows, until no row you may do is left.

RULES
1. First read ai-studio/chatgpt-images/AGENTS.md completely (including "Checks", "A row marked redo", "Rows from document 40" and "Saving your work") and ai-studio/chatgpt-images/prompts/README.md. AGENTS.md is your rule book; where it talks about attaching files in a conversation, you instead open those files from the repository and give them to your image tool as references. The five-file limit is a ChatGPT-app limit and does not apply to you, but use only the files the row's Attach cell names, never others.
2. Write only inside ai-studio/chatgpt-images/ (files in out/, and the Status and Notes cells of TASKS.md). Never change code, documents, prompts, ai-studio/game-images/ or public/. Never run php, composer or tests.
3. Work on your own branch cut from main (for example chatgpt-images-doc40). Commit with the repository owner's identity, Arumi Studios <arumiforge@gmail.com>, with messages "ChatGPT images: <codes>" and no tool or co-author lines. Commit after every finished picture; push whenever you can, so nothing is lost if the task stops. At the end open ONE pull request into main that lists every row you made, every row you blocked and why.

ORDER
Work through the rows in this order, taking each row whose Status is "todo" or "redo: …" and skipping "generated", "accepted", "installed" and "blocked: …":
  A. U-00 (the carved-gold style board).
  B. S-W, S-02, S-03, S-04, S-05 (start page and ending story).
  C. PK-01 to PK-32 (Pustaka in 3D).
  D. U-01 to U-35 (interface parts), ONLY IF the first line of this task says "U-00 is accepted." In that case first set U-00 to "accepted" with "Owner accepted <today's date>." in its Notes, and set every row whose Status is exactly "blocked: waiting for U-00" to "todo" with Notes "Unlocked <today's date>: U-00 accepted." (AGENTS.md allows this on the owner's word). Then make U-01 to U-35 in number order: later rows use earlier results as references (U-02 uses out/ui-plate-gold.webp, the avatars use out/avatar.1.webp, and so on). If the line is missing, do not touch the U-01 to U-35 rows; stop after C.
  E. U-36 (the shard counter crystal; prompt in document 40), then SD-01 to SD-14 in number order (prompts in docs/revisi-10-analitik/41_RENCANA_TAMPILAN_SKENARIO_DAN_HUD.md, section 8; AGENTS.md → "Rows from document 41"). These do not wait for the line above. SD rows are fixed-size: check them shrunk to the display size in their prompt with the longest label on them.

FOR EACH ROW
1. Open the prompt the row points at (file and heading in the "Prompt" cell, nearly always docs/revisi-10-analitik/40_PROMPT_GAMBAR_ANTARMUKA.md). Use the fenced text block under that heading word for word as the picture prompt. For U-24 to U-33 the heading is "U-24 sampai U-33 · Cincin avatar emas berukir": replace {N} with the avatar number of the row; for U-30 also append the block "Tambahan untuk U-30". Read the table above the block too: it repeats the canvas, the references and, for PK rows, the caption the picture must show.
2. Load exactly the files of the row's Attach cell as references. If one does not exist, set the row to "blocked: missing file <path>" and go on to the next row.
3. Rows that are edits (U-07, U-15 to U-17, U-24 to U-33): edit the named original with a mask over only the part the prompt names; everything else must stay pixel-identical. After a failure start again from the original, never from a failed result.
4. Generate. Then look at the result yourself and check it against the prompt and every check of AGENTS.md for this kind of row before saving, in particular:
   - all pictures: no readable text, letters, numbers or logos; no Jaka or Mbah Kedu (except where AGENTS.md allows it); the number of people the prompt gives;
   - S rows: no people, no light rays, the lower third calm and darker (S-W: the centre and lower half), no sea or coast, signboards blank;
   - PK rows: stylized 3D, never an oil painting; exactly what the caption says; no recurring boy in lurik; tobacco only as green leaves, never cigarettes or smoke; no steam or smoke in Wonosobo pictures; nobody touching a stupa or relief; no offerings or statues where the prompt says so;
   - U rows: the same gold, carving, diamond studs and light as the style board out/reference.27.webp; transparent outside the object unless the prompt says otherwise; plates, frame and track (U-01 to U-05) with ornaments only inside the ends or corners and a plain middle that can be stretched; status markers (U-09 to U-11) told apart at 19 px in black and white.
   If a check fails, regenerate and name the failed check to yourself; after three failed attempts set the row to "blocked: <the check that keeps failing>" and go on to the next row.
5. Convert and save: WebP quality 90, exactly the row's canvas size (scale proportionally from the generated size; crop only empty margin; never stretch, never add borders), real alpha where the row says transparent, opaque where it says so. File name exactly as in the row's "Save in out/ as" cell, in ai-studio/chatgpt-images/out/. Verify the size and the alpha with a script (for example Python Pillow) before you commit.
6. In TASKS.md change only that row: Status "generated", Notes today's date, plus one short note only if the owner needs to know something.
7. Commit, then continue with the next row at once.

END
When no row you may do is left, or when your time is almost up, push, open (or update) the pull request, and report in the task: a table of row, file, result (generated / blocked + reason), and which row comes next on the following run.
```
