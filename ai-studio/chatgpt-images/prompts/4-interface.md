# Step 4: interface parts (`U-01` to `U-35`)

After step 3. Paste the block below into a new ChatGPT conversation; for every next row, paste `next-row.md` into a new conversation. The rows build on each other (the navy plate attaches the gold one, the avatars attach avatar 1, and so on), so keep the order.

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita (make your own branch from main for your commit).

1. Read ai-studio/chatgpt-images/AGENTS.md completely, especially "Rows from document 40". Its rules come before anything I say here.
2. Open ai-studio/chatgpt-images/TASKS.md. This step covers the rows U-01 to U-35 in number order. Take the first of them whose Status is "todo" or "redo: …". If U-01 still says "blocked: waiting for U-00", stop and tell me to do step 3 first. Do ONE row in this conversation (at most five attachments per conversation).
3. Open the prompt the row points at in docs/revisi-10-analitik/40_PROMPT_GAMBAR_ANTARMUKA.md and use its fenced text block word for word. It always starts with the block "GELITA CARVED GOLD UI — 2026-11-18". For U-24 to U-33 the heading is "U-24 sampai U-33 · Cincin avatar emas berukir": replace {N} with the avatar number in the row; for U-30 also paste the extra block "Tambahan untuk U-30" under it.
4. Attach exactly the files in the row's Attach cell, at those paths, including the style board ai-studio/chatgpt-images/out/reference.27.webp where named. If a path does not exist, set "blocked: missing file <path>" and stop.
5. If the row is an edit (U-07, U-15 to U-17, U-24 to U-33), edit the attached picture with a mask over only the part the prompt names; everything else stays locked. Start again from the original picture after a failure, never from a failed result.
6. Before saving, check: same gold, carving, diamond studs and light as the style board; no text, letters or numbers; transparent outside the object unless the prompt says otherwise; exact canvas. For plates, frame and track (U-01 to U-05): ornaments only inside the ends or corners the prompt gives, the middle plain (stretch the middle to double length and look: nothing may look stretched). For the status markers (U-09 to U-11): shrink to 19 px, the check mark, the light shard and the padlock must be told apart in black and white. If a check fails, regenerate here and name it; after three failures set "blocked: <check>" and stop.
7. Save in ai-studio/chatgpt-images/out/ under exactly the name in the row's "Save in out/ as" cell, WebP quality 90, at exactly the row's canvas size, keeping real transparency (no fake checkerboard).
8. In TASKS.md change only that row: Status "generated", Notes today's date (one short note only if I need to know something).
9. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: <code>", owner identity (Arumi Studios <arumiforge@gmail.com>), no tool or co-author lines. Push and open a pull request into main, or add the commit to your open pull request of this step.
10. Show me the picture next to the style board, then end with one line: "Next: <code of the next open row of this step>" or "Step 4 done".
```
