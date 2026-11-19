# Step 6: shard counter crystal and Skenario Digital parts (`U-36`, `SD-01` to `SD-10`)

Since 19 November 2026, when the owner approved the Skenario Digital mockup. Paste the block below into a new ChatGPT conversation; for every next row, paste `next-row.md` into a new conversation. The `SD-` rows build on each other (the long button attaches the short one, the cards attach the first card), so keep the order. `U-36` stands alone and may be made first.

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita (make your own branch from main for your commit).

1. Read ai-studio/chatgpt-images/AGENTS.md completely, especially "Rows from document 40" and "Rows from document 41 (codes SD-…) and U-36". Its rules come before anything I say here.
2. Open ai-studio/chatgpt-images/TASKS.md. This step covers U-36, then SD-01 to SD-10 in number order. Take the first of them whose Status is "todo" or "redo: …". Do ONE row in this conversation (at most five attachments per conversation).
3. Open the prompt the row points at (U-36: docs/revisi-10-analitik/40_PROMPT_GAMBAR_ANTARMUKA.md; SD rows: docs/revisi-10-analitik/41_RENCANA_TAMPILAN_SKENARIO_DAN_HUD.md, section 8) and use its fenced text block word for word. It always starts with the block "GELITA CARVED GOLD UI — 2026-11-18".
4. Attach exactly the files in the row's Attach cell, at those paths. The mockup screenshots in docs/revisi-10-analitik/gambar/41/ show only where a part sits and how large it is; do not copy their look. If a path does not exist, set "blocked: missing file <path>" and stop.
5. Before saving, check: same gold, carving, diamond studs and light as the style board; no text, letters, numbers, check marks or symbols; transparent outside the object; exact canvas. Shrink the result to the display size named in the prompt and lay the longest label on it: the face under the text must stay calm and readable. Selected and unselected cards must line up exactly and differ in black and white; no red, no green. U-36: only the crystal, no socket, no glow outside its outline; it must fit inside the empty socket of out/hud-shards-frame.webp. If a check fails, regenerate here and name it; after three failures set "blocked: <check>" and stop.
6. Save in ai-studio/chatgpt-images/out/ under exactly the name in the row's "Save in out/ as" cell, WebP quality 90, at exactly the row's canvas size, keeping real transparency (no fake checkerboard).
7. In TASKS.md change only that row: Status "generated", Notes today's date (one short note only if I need to know something).
8. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: <code>", owner identity (Arumi Studios <arumiforge@gmail.com>), no tool or co-author lines. Push and open a pull request into main, or add the commit to your open pull request of this step.
9. Show me the picture shrunk to its display size with its longest label on it, then end with one line: "Next: <code of the next open row of this step>" or "Step 6 done".
```
