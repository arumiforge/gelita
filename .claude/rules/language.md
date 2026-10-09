# Language rules

| What | Language |
|---|---|
| Chat replies to the owner | English |
| Identifiers (classes, methods, variables, files, tables, columns) | English |
| Code comments, docblocks, TODOs | English |
| Commit messages, PR titles and bodies, logs, developer-facing errors | English |
| New technical docs and new entries in the decision log | English |
| Prompts to subagents / parallel agents, and their reports | English |
| Text end users see (students, teachers, admins) | Friendly Indonesian, see `ui-copy.md` |
| Public page URLs (game and admin) | Short Indonesian slugs |
| JSON API paths under `/api` | English (not user-facing) |
| Stored codes already in the database or research data | Unchanged, whatever their language |

## Chat
- Always reply in English, even when the owner writes in Indonesian or mixes both.
- Switch to Indonesian only when the owner asks for it in that specific message, and only for that message.
- When quoting UI text or Indonesian file content, quote it as-is; the surrounding explanation stays English.

## Code
- New identifiers are English. Domain words with no good English equivalent use the term fixed in `glossary.md`.
- **Existing Indonesian names stay.** Do not rename existing identifiers, stored codes, route slugs, event names, spark command names (`gelita:simulasi`), or workbook columns just to translate them. Stored codes (`rumpang`, `boleh`, `cari`, `bintang-1`, dimension codes like `peserta`, `sesi`) are part of saved data and exports.
- Comments are English. When you substantially edit a block whose comments are Indonesian, rewrite those comments in English; do not mass-translate untouched files (it creates noisy diffs and merge conflicts).

## Routes and URLs
- Public paths are lowercase, hyphenated Indonesian words with no technical words or underscores: `/masuk`, `/daftar`, `/peta`, `/pustaka`, `/ganti-sandi`, `/admin/peserta`, `/admin/ekspor`.
- Controllers, methods, and route targets are English:
  ```php
  // Public URL is Indonesian; controller and method stay English.
  $routes->get('ganti-sandi', 'LoginController::changePasswordForm');
  ```
- Follow existing patterns: singular nouns for a page (`/profil`, `/pustaka/(:segment)`), verbs for actions (`/hapus`, `/unggah`, `/setujui`).
- Do not rename existing paths. `/admin/login`, `/admin/logout`, and `/admin/dashboard` are legacy exceptions; new admin paths use Indonesian.

## Documents
- Existing Indonesian docs (`docs/01`–`08`, `docs/perombakan-kelas5/*`) stay Indonesian; add new sections in English.
- Story text, narration scripts, and the question bank are content, not docs: they stay Indonesian with their English counterparts where they already exist.
