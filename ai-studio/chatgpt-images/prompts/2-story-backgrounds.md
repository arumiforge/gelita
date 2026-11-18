# Step 2: start page and ending story (`S-W`, `S-02` to `S-05`)

Paste the block below into a new ChatGPT conversation. ChatGPT makes the first open row and then names the next; for each next row, paste `next-row.md` into a new conversation.

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita (make your own branch from main for your commit).

1. Read ai-studio/chatgpt-images/AGENTS.md completely, including "Checks" (Every picture, Story backgrounds) and "Rows from document 40". Its rules come before anything I say here.
2. Open ai-studio/chatgpt-images/TASKS.md. This step covers the rows S-W, S-02, S-03, S-04, S-05, in that order. Take the first of them whose Status is "todo" or "redo: …". Do ONE row in this conversation, because I can attach at most five files per conversation.
3. Open the prompt the row points at (docs/revisi-10-analitik/40_PROMPT_GAMBAR_ANTARMUKA.md, the heading named in the row) and use its fenced text block word for word. The table above the block repeats the canvas, the attachments and the story line.
4. Attach exactly the files in the row's Attach cell, at those paths. Never search for similar files; if a path does not exist, set the row to "blocked: missing file <path>" and stop.
5. Generate. Before saving, check: no people at all, no light rays, the lower third calm and darker (for S-W: the centre and the lower half calm and dark), the top 8% free of detail, no text or letters anywhere (signboards blank), only what the prompt names, the mountains and places as in the attached photos, no sea or coast. If a check fails, regenerate here and name the failed check; after three failures set "blocked: <check>" and stop.
6. Save the picture in ai-studio/chatgpt-images/out/ under exactly the name in the row's "Save in out/ as" cell, WebP quality 90, exactly 1920 × 1080 (scale proportionally, never stretch, never add borders).
7. In TASKS.md change only that row's Status to "generated" and write today's date in Notes (one short note only if I need to know something).
8. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: <code>", using the owner's identity (Arumi Studios <arumiforge@gmail.com>) and no tool or co-author lines. Push and open a pull request into main, or add the commit to your open pull request of this step.
9. Show me the picture, then end with one line: "Next: <code of the next open row of this step>" or "Step 2 done".
```
