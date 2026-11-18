# Step 1: the style board `U-00`

Paste the block below into a new ChatGPT conversation.

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita, on the branch main (make your own branch for your commit).

1. Read ai-studio/chatgpt-images/AGENTS.md completely, including the section "Rows from document 40". Its rules come before anything I say here, except the row I name.
2. Open ai-studio/chatgpt-images/TASKS.md and find row U-00 (the carved-gold interface style board).
3. Open the prompt the row points at: docs/revisi-10-analitik/40_PROMPT_GAMBAR_ANTARMUKA.md, heading "U-00 · Papan acuan gaya antarmuka emas berukir". Use the fenced text block under that heading word for word.
4. Attach exactly the five files in the row's Attach cell, at those paths. Do not search for other files.
5. Generate the picture. Check it against the prompt and AGENTS.md → Checks before you save: one family of parts, the same gold and carving on every part, light from the upper left, no text, letters, numbers, labels or colour swatches, plain deep-navy background. If a check fails, regenerate in this conversation and name the failed check; after three failures set the row to "blocked: <check>" and stop.
6. Save it as ai-studio/chatgpt-images/out/reference.27.webp, WebP quality 90, exactly 1536 × 1024.
7. In TASKS.md change only row U-00: Status "generated", Notes today's date plus one short note if I need to know something.
8. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: U-00", using the repository owner's identity (Arumi Studios <arumiforge@gmail.com>) and no tool or co-author lines. Push your branch and open a pull request into main.
9. Show me the picture and tell me in two sentences how it matches the round button and the plates it was made from. Do not start any other row: the other interface rows wait until I accept this one.
```
