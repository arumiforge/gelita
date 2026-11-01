# Draft plan: start implementing parts 1–4 before gate G2

> **On hold (owner, 1 November 2026).** This is a draft note, not a decision. Nothing in it has been carried out. Run it only when the owner asks; if the owner decides against it, delete this file and its line in `docs/PROGRESS.md`.
>
> Until then the recorded rules stay in force: no system change before G2 (`docs/revisi-10-analitik/01_REVISI_10_ANALITIK.md` → *Peta jalan dan daftar kerja*, 25 October 2026). Implementation documents `27`–`30` may be drafted early (30 October 2026).

## Why this note exists

The owner asked whether system code can start now, before the researcher, the expert validators, and the ethics committee have reviewed the plans. The owner's assumption was that later changes would mostly touch text.

### Where things stand (1 November 2026)

- Implementation documents `27`–`30` cover system changes no. 7, 10–15, 23, and 24. All four are `draf pengembang`, waiting for the owner's review.
- The other 15 changes wait for the experts' revision matrix: no. 1–6, 8, 9, and 16–22 (Skenario Digital engine, new bank, anchors, attitude scale, test module, research mode, …).

### Assessment

- **Ethics.** The committee approves activities with children and the data collection, not writing software. Building early does not break the protocol as long as no child uses the system before approval.
- **"Only text changes later"** holds for parts 1–4. They are plumbing: staff roles, token and field sessions, participant code, monitoring, log cleanup, research export, and consent and data-rights functions. Their texts live in `app/Language` and their numbers in config. Part 4 (`30`) is the part the committee is most likely to change.
- **It does not hold for the other 15 changes.** Validation can change their format, not only their wording: the two-step scenario, Cari Objek round 2, booklets, the 4-point attitude scale, the test module. They keep waiting.
- **Methodology.** Building the system (Prototype II) before the paper prototype (Prototype I) is validated changes the Plomp stage order. That order is research design, so the researcher has a say.

### The owner's answers (1 November 2026)

1. Scope: **parts 1–4 only** (changes no. 7, 10–15, 23, 24).
2. Docs `27`–`30`: **approve as-is**, raised to `disetujui pengembang`.
3. Servers: **production too**, after the developer archives existing data before the destructive migrations.
4. **Wait for the researcher's OK.** Record the proposal, write no code until the researcher agrees at the upcoming meeting.

Then the owner put the whole plan on hold (this note).

## Step A, when the owner asks: documentation only

Work on a fresh short-lived branch cut from `main`. Docs in `docs/revisi-10-analitik/` are written in Indonesian; commits and the PR in English.

### A1. `04_CATATAN_KEPUTUSAN.md`: new dated entry at the top

Two items, each labelled with origin and status from `00` → *Label asal dan status*.

**a. Usulan: implementasi bagian 1–4 sebelum G2**
- Labels: `asal: pengembang` · `disetujui pengembang`, belum berlaku: menunggu peneliti · Prototipe II, belum merevisi produk.
- Record the four choices above and the options not chosen.
- Conditions:
  - Coding starts only after the researcher agrees, recorded in `04` as `asal: peneliti`.
  - Scope: changes no. 7, 10–15, 23, and 24 only.
  - Texts go in `app/Language` and numbers in config. Committee changes are marked `asal: komite etik`.
  - No child uses the system before ethics approval. The existing one-time re-check against the revision matrix and the ethics decision still happens before KG2/KG3, the first activity that uses a child's account.
  - Production deploy is allowed. Migration `003400` deletes `guru` accounts and their exports; `003600` deletes stored log data. Before either runs on production, the developer archives the existing data outside the system (`29` §18 no. 5). Only the developer runs them, never AI.
  - The validators' test install stays on the frozen commit from `20` prerequisite no. 4. Media experts (step 1) and cultural observers then judge the game the package describes.
- Reason: the plumbing does not depend on the experts' verdicts on items and formats. Building it early leaves time to test on MariaDB and on class devices before the pilot.
- Risk: part 4 may change because of the committee.

**b. `27`–`30` disetujui pengembang apa adanya**
- Labels: `asal: pengembang` · `disetujui pengembang`.
- Proposals in the open questions are accepted where one exists:
  - `27` §16 no. 4 (admin CSV removed);
  - `28` §21 no. 6;
  - `29` §18 no. 1, 2, 4;
  - `30` §21 no. 5.
- Code findings are accepted as written:
  - `28` §8: progress moves to the student;
  - `29`: `dialogue_advanced` becomes `dialogue_completed`; the `audio_pause` finding;
  - `30`: second backup location, retention counted from `closed_at`, schools unlinked from names at closure, PHP session GC.
- Still open, because they depend on something else:
  - change no. 22: `28` §21 no. 2–3;
  - the readability test: `28` no. 1;
  - the school-code list: `28` no. 4;
  - the researcher: `29` no. 3 (`is_touch`), `30` no. 3;
  - the committee or the hosting provider: `30` no. 4 and 6;
  - procedure notes only: `27` no. 6–7, `29` no. 5.

### A2. Docs `27`–`30`

- Status block at the top becomes `disetujui pengembang` (with the date). Add one line: implementing before G2 is proposed and waiting for the researcher (link to `04`).
- One line in each *Keputusan pengembang* section (`27` §17, `28` §22, `29` §19, `30` §22): approved as-is, with the accepted proposals listed in `04`.

### A3. `00_INDEKS.md`

- Status column for rows 27–30, in *Daftar dokumen* and in the *Tahap dokumen 3* table.
- *Langkah berikutnya* no. 6: the owner's review is done; next comes the researcher's answer on early implementation.
- *Tahap dokumen 3* intro: one sentence on the proposal.
- Header date.

### A4. `01_REVISI_10_ANALITIK.md`

- Add the proposal sentence to the opening paragraph of *Peta jalan dan daftar kerja* and to the intro of *Catatan perubahan sistem*. The 25 and 30 October rules stay in force until the researcher agrees.
- Add one unchecked item to *Pertanyaan yang masih terbuka*: "Implementasi bagian 1–4 dimulai sebelum G2?", with the developer's proposal and "menunggu peneliti".

### A5. Researcher handover brief, `dokumen-pdf/src/serah-terima-peneliti.html`, next version

- C.3: new row no. 11, early implementation of parts 1–4, with the proposal, conditions, and reasons, written for a researcher reader.
- B.6: status tags for 27–30 become `disetujui pengembang`; the "requested from researcher" column points to C.3 no. 11.
- A.2: tahap 3 row reads "Bagian 1–4 disetujui pengembang; usulan mulai sebelum G2 (C.3 no. 11)".
- F.5: one risk bullet (part 4 may change; destructive migrations need an archive first).
- G.1: the *Dokumen implementasi* agenda row includes C.3 no. 11.
- G.3: new sheet row "C.3 no. 11 implementasi bagian 1–4 sebelum G2".
- Version line and footer date.
- Re-render with `node docs/revisi-10-analitik/dokumen-pdf/src/render.mjs serah-terima-peneliti`, then update the `dokumen-pdf/README.md` row (version, page count).

### A6. `docs/PROGRESS.md`

- The early-implementation row and the 27–30 sub-rows: approved by the owner.
- New sub-row: proposal to implement parts 1–4 before G2, waiting for the researcher.
- Replace the line pointing to this note.

### A7. Print-ready PDFs and delivery

1. Commit the document changes.
2. Run `node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs`. It rebuilds only the changed documents (00, 01, 04, 27–30).
3. Commit `dokumen-pdf/cetak/` together with `manifest.json`.
4. Push and open a PR with no Claude attribution.

Delete this note in the same PR.

## Step B, only after the researcher agrees: code

One short-lived branch per part, cut from `main`, in the order the documents set: part 1 (`27`) alone first, then parts 2–4 (`28`, `29`, `30`). Each part follows its own document's test list. `vendor/bin/phpunit --no-coverage` must be green before every push. Migrations and `gelita:reset` never run against the production server from a session.

## Not in scope

- The other 15 system changes, which wait for the revision matrix.
- Documents `20`, `22`, `23`, and `26`. The proposal does not change what goes to experts or the committee.
