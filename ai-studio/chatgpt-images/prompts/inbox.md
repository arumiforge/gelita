# Pictures dropped in `inbox/`

When you made pictures in the ChatGPT app by hand and put the downloads in `ai-studio/chatgpt-images/inbox/` (keep their original names). Paste the block below into a new ChatGPT conversation. Reference photos in `inbox/` (names starting with `reference-`) are not results and stay where they are.

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita (make your own branch from main).

I put new result pictures in ai-studio/chatgpt-images/inbox/. Handle them as ai-studio/chatgpt-images/AGENTS.md → "Files in inbox/" says:

1. Read AGENTS.md completely. List the files in inbox/ that do not start with "reference-" and are not .json files: those are my results.
2. For each one, work out which TASKS.md row it belongs to. If you cannot tell for certain, leave it and ask me; never guess.
3. Run the checks of AGENTS.md and of the row's prompt. If it passes: convert to WebP quality 90 at the row's exact canvas, save it in out/ under the row's file name, delete it from inbox/, and set the row to "generated" with today's date in Notes. If it fails: leave it in inbox/ and set the row to "redo: <failed check>".
4. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: <codes>", owner identity (Arumi Studios <arumiforge@gmail.com>), no tool or co-author lines. Push and open a pull request into main.
5. Give me a short table: file, row, result (generated or redo with the reason).
```
