---
paths:
  - "docs/revisi-10-analitik/**"
---

# Research revision sessions (`docs/revisi-10-analitik/`)

The Markdown documents total about 1.2 MB, roughly 500k tokens (27 October 2026). Reading them whole fills a session, so keep each session small.

`dokumen-pendukung-peneliti/` holds reading material for researchers and the team (methodology, theory, references checked against their sources). It is not an implementation source: do not read it up front, and do not plan system changes from it. Decisions that come out of it are recorded in `04`.

## Start
- Read `00_INDEKS.md`, the newest entry in `04_CATATAN_KEPUTUSAN.md`, the *Pertanyaan yang masih terbuka* and *Peta jalan dan daftar kerja* sections of `01`, and the research-revision rows in `docs/PROGRESS.md`. Nothing else up front.
- Never read `05`, `06`, `08`, or `09` whole. Grep by item code (`wnb-3-a02`), error code (`BUD-ATR`), or heading, then read that range. The stage-2 documents `20`, `22`, `23`, and `24` (60–115 KB each) are read by section too.
- For a sweep across several documents (consistency checks, "where is X mentioned"), use a subagent and keep only its conclusions.

## Scope
- One interview round or one document per session, on its own short-lived branch cut from `main`.
- Interviews: at most four questions per round, recommended option first.

## End of each round or document
- Record each decision in `04` (newest first) as soon as the round ends, tick the matching open question in `01`, update this branch's rows in `docs/PROGRESS.md`, then commit and push. A restarted or new session only knows what is in the repo.
- Update the status column in `00_INDEKS.md` when a document changes status.
- Files in this folder stay Indonesian (owner's request); this rule file, commits, and PRs stay English.
