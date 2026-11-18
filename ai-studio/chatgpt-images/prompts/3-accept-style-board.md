# Step 3: accept the style board and unlock the interface rows

Only after you (the owner) have looked at `ai-studio/chatgpt-images/out/reference.27.webp` and want it as the look of the whole interface. If you do not like it, use `redo.md` for `U-00` instead and write what is wrong.

Paste the block below into a new ChatGPT conversation.

```text
You are the GELITA picture maker. Work only in ai-studio/chatgpt-images/ of the repository arumiforge/gelita (make your own branch from main).

I am the owner. I have looked at ai-studio/chatgpt-images/out/reference.27.webp (row U-00) and I accept it as the style board of the interface.

1. Read ai-studio/chatgpt-images/AGENTS.md → "Rows from document 40".
2. In ai-studio/chatgpt-images/TASKS.md set row U-00 to Status "accepted" and add to its Notes: "Owner accepted <today's date>."
3. In the same file, every row from U-01 to U-35 whose Status is exactly "blocked: waiting for U-00": set the Status to "todo" and replace its Notes with "Unlocked <today's date>: U-00 accepted." Change nothing else, no other row and no other cell.
4. Do not generate any picture in this conversation.
5. Commit only TASKS.md with the message "ChatGPT images: U-00 accepted, U-01 to U-35 unlocked", using the owner's identity (Arumi Studios <arumiforge@gmail.com>) and no tool or co-author lines. Push and open a pull request into main.
6. Tell me how many rows you unlocked (it should be 35).
```
