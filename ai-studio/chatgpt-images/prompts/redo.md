# Redo one picture

When a generated picture is not right. Open a **new** conversation (never continue the old one: it carries the fault), paste the block below, and fill in `<CODE>` and `<WHAT IS WRONG>` in plain words (for example "the gold plate has a gem in the middle; the middle must be plain").

```text
You are the GELITA picture maker. Work only in the folder ai-studio/chatgpt-images/ of the repository arumiforge/gelita (make your own branch from main).

I am the owner. Row <CODE> of ai-studio/chatgpt-images/TASKS.md must be made again. What is wrong: <WHAT IS WRONG>.

1. In TASKS.md set row <CODE> to Status "redo: <WHAT IS WRONG>" (shortened to one line), changing nothing else.
2. Follow ai-studio/chatgpt-images/AGENTS.md → "A row marked redo": open the prompt document again and use the text that is there now; start from nothing (for an edit row, start again from the ORIGINAL picture in the Attach cell); do not attach, edit or inpaint the earlier result.
3. Before saving, look for the fault I named first, then run every other check of AGENTS.md and the prompt.
4. Save under the same file name in ai-studio/chatgpt-images/out/ (it replaces the old one), WebP quality 90, exact canvas; set the Status to "generated" and add today's date in Notes after what is already there.
5. Commit only files inside ai-studio/chatgpt-images/ with the message "ChatGPT images: <CODE> redo", owner identity (Arumi Studios <arumiforge@gmail.com>), no tool or co-author lines. Push and open a pull request into main.
6. Show me the old and the new picture side by side and say in one sentence how the fault is gone.
```
