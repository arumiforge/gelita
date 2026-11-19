# Instructions for AI agents other than Claude Code

Claude Code sessions follow `CLAUDE.md` and `.claude/rules/`, not this file.

Any other agent (ChatGPT, Codex, and similar) works in exactly one folder of `ai-studio/` and nowhere else:

| You were asked to | Your folder | Read first |
|---|---|---|
| Generate or rename pictures | `ai-studio/chatgpt-images/` | `ai-studio/chatgpt-images/AGENTS.md`, then `TASKS.md` there |
| Produce narration with ElevenLabs | `ai-studio/elevenlabs-voice/` | `ai-studio/elevenlabs-voice/README.md` |
| Produce music or sound effects with ElevenLabs | `ai-studio/elevenlabs-sound/` | `ai-studio/elevenlabs-sound/README.md`, then `sounds.csv` there |

Rules that hold everywhere:

- Write only inside your folder. Read other files when your instructions point at them.
- Never change application code, tests, documents, `ai-studio/game-images/` (the game's picture masters; no tool's work folder), or `public/`. Never run the game's commands or anything against a server.
- Commits use the repository owner's identity and carry no tool or co-author lines. Work on your own branch and open a pull request into `main`. Never merge a pull request, yours or anyone's: a Claude Code session merges (owner, 19 November 2026).
- If the task you were given is not in the table above, stop and tell the owner.
