# Next row

When ChatGPT ends with "Next: <code>", open a **new** conversation, paste the block below, and replace `<CODE>` with that code (for example `S-03`, `U-12`, `PK-07`).

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita (use your open branch of this step, or make one from main).

Make the picture of row <CODE> of ai-studio/chatgpt-images/TASKS.md, and only that row.

1. Read ai-studio/chatgpt-images/AGENTS.md completely, including "Checks", "Rows from document 40" and "Rows from document 41". Its rules come before anything I say here.
2. If the row's Status is not "todo" or "redo: …", stop and tell me its Status.
3. Open the prompt the row points at (file and heading in the row) and use its fenced text block word for word. For U-24 to U-33 replace {N} with the avatar number; for U-30 add the block "Tambahan untuk U-30".
4. Attach exactly the files in the row's Attach cell, at those paths (at most five). If a path does not exist, set "blocked: missing file <path>" and stop.
5. Generate, or edit with a mask when the row is an edit. Run every check that AGENTS.md and the prompt give for this kind of row before saving. If a check fails, regenerate here and name it; after three failures set "blocked: <check>" and stop.
6. Save in ai-studio/chatgpt-images/out/ under exactly the name in the row's "Save in out/ as" cell, WebP quality 90, exactly the row's canvas size.
7. In TASKS.md change only that row: Status "generated", Notes today's date.
8. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: <CODE>", owner identity (Arumi Studios <arumiforge@gmail.com>), no tool or co-author lines. Push, and open a pull request into main if none is open for this step.
9. Show me the picture, then end with one line: "Next: <code of the next open row of the same step>" or "Step done". Steps: S-W to S-05; U-01 to U-35; PK-01 to PK-32; U-36 and SD-01 to SD-10 (skip blocked rows).
```
