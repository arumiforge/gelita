---
paths:
  - "docs/revisi-10-analitik/**"
---

# Research revision sessions (`docs/revisi-10-analitik/`)

The Markdown documents total about 1.2 MB, roughly 500k tokens (27 October 2026). Reading them whole fills a session, so keep each session small.

`dokumen-pendukung-peneliti/` holds reading material for researchers and the team (methodology, theory, references checked against their sources). It is not an implementation source: do not read it up front, and do not plan system changes from it. Decisions that come out of it are recorded in `04`.

## Start
- Read `00_INDEKS.md`, the newest entry in `04_CATATAN_KEPUTUSAN.md`, the *Pertanyaan yang masih terbuka* and *Peta jalan dan daftar kerja* sections of `01`, and the research-revision rows in `docs/PROGRESS.md`. Nothing else up front.
- Never read `05`, `06`, `08`, or `09` whole. Grep by item code (`wnb-3-a02`), error code (`BUD-ATR`), or heading, then read that range. The stage-2 documents `20`, `22`, `23`, and `24` (60–160 KB each) are read by section too.
- For a sweep across several documents (consistency checks, "where is X mentioned"), use a subagent and keep only its conclusions.

## Scope
- One interview round or one document per session, on its own short-lived branch cut from `main`.
- Interviews: at most four questions per round, recommended option first.
- An implementation session (parts `27`–`35`, or code for one part) reads its own part document and the source sections that document names; nothing else up front. Code is built now but never used with children before ethics approval (`04`, 1 November 2026).

## Researcher changes (1 November 2026)
- The researcher handles validation and ethics. The repo stays the master copy of `23`, `24`, and `26`: researcher edits come in through the owner, are applied with `asal: peneliti` (or `asal: komite etik`), and the rebuilt PDFs from `dokumen-pdf/cetak/` go back to the researcher. The researcher receives PDFs only.
- Since 3 November 2026 the researcher handles `22` directly, without the owner, including the group B fill-ins (institution, funding, files and tools). The repo copy of `22` stays as the PDF sent that day (page footer `05b025a`); do not edit it, rebuild it, or plan work from its *Daftar isian* unless the owner asks.

## End of each round or document
- Record each decision in `04` (newest first) as soon as the round ends, tick the matching open question in `01`, update this branch's rows in `docs/PROGRESS.md`, then commit and push. A restarted or new session only knows what is in the repo.
- Update the status column in `00_INDEKS.md` when a document changes status.
- Rebuild the print-ready PDFs only when documents are about to go to the researcher or the owner asks: `node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs` (it rebuilds only the changed ones), then commit the PDFs in `dokumen-pdf/cetak/` with `manifest.json` in one commit. Not after every document change (owner, 14 November 2026; replaces the 31 October 2026 rule): each rebuild adds binary files to the repository's history for good. Between handovers a PDF may be older than its Markdown; its page footer names the commit it was built from. Don't open the PDFs to check them; the script's page counts are enough unless a layout change was made.
- Files in this folder stay Indonesian (owner's request); this rule file, commits, and PRs stay English.

## Roles and naming (27 October 2026)
- In these files the owner is *pengembang* (never "pemilik proyek"), and AI help is written as *AI* (never a product or assistant name). The paths `.claude/rules/…` are the only exception.
- Follow `01` → *Peran pengembang, peneliti, dan ahli*: developer work (including AI-assisted scores and labels) is provisional, never counted as expert validation, and never shown to experts before they rate. Every new decision in `04` names its origin and status with the labels in `00` → *Label asal dan status*.
