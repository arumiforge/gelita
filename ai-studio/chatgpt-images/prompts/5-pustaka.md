# Step 5: Pustaka pictures in 3D (`PK-01` to `PK-32`)

Paste the block below into a new ChatGPT conversation; for every next row, paste `next-row.md` into a new conversation. Rows that wait for your reference photo (`PK-19` Umbul Jumprit, `PK-20` Parakan) are skipped until the photo files are in `ai-studio/chatgpt-images/inbox/` and a Claude Code session adds them to the row. Andong (`PK-01`) and Tidar (`PK-09`) have their photos since 18 November 2026.

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita (make your own branch from main for your commit).

1. Read ai-studio/chatgpt-images/AGENTS.md completely, especially "Checks" (Every picture) and "Rows from document 40". Its rules come before anything I say here.
2. Open ai-studio/chatgpt-images/TASKS.md. This step covers the rows PK-01 to PK-32 in number order. Take the first of them whose Status is "todo" or "redo: …"; skip rows that are "blocked: …". Do ONE row in this conversation (at most five attachments per conversation).
3. Open the prompt the row points at in docs/revisi-10-analitik/40_PROMPT_GAMBAR_ANTARMUKA.md and use its fenced text block word for word. The table above it gives the canvas, the attachments and the caption the picture must fit.
4. Attach exactly the files in the row's Attach cell, at those paths. The old Pustaka picture is attached for its content only: never copy its oil-painting look. If a path does not exist, set "blocked: missing file <path>" and stop.
5. Before saving, check: stylized 3D like the style reference, never an oil painting or brush texture; the picture shows exactly what the caption in the prompt says; no recurring boy in lurik or iket, and people only where the prompt names them (counted, seen from the side or behind, an adult present when there are children); nobody touches a stupa or relief; tobacco only as green leaves, never cigarettes or smoke; no steam or smoke in Wonosobo pictures; temples, costumes and food as in the attached photos; no text, letters, numbers, play buttons or logos. If a check fails, regenerate here and name it; after three failures set "blocked: <check>" and stop.
6. Save in ai-studio/chatgpt-images/out/ under exactly the name in the row's "Save in out/ as" cell, WebP quality 90, at exactly the row's canvas size (960 × 640, or 1280 × 720 for video covers), opaque.
7. In TASKS.md change only that row: Status "generated", Notes today's date (one short note only if I need to know something).
8. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: <code>", owner identity (Arumi Studios <arumiforge@gmail.com>), no tool or co-author lines. Push and open a pull request into main, or add the commit to your open pull request of this step.
9. Show me the picture with its caption, then end with one line: "Next: <code of the next open row of this step>" or "Step 5 done".
```
