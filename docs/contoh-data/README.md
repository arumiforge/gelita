# Sample download: 300 simulated students

Owner's request of 20 November 2026: a complete simulated dataset of 300 students, ready to download, that shows what the head researcher (ketua) gets from **Unduh data** and sees on her dashboard. **This is not research data.** Every student, school, answer and time was made by `gelita:simulasi`; the *Kamus Data* sheet of both files starts with a `PERHATIAN` row that says so. Never merge it with real data.

The staff guide offers both files on its page *Contoh data simulasi 300 siswa* (`/admin/panduan-staf/contoh-data`, `GuideDownloads` group `contoh`).

| File | What it is |
|---|---|
| `GELITA-contoh-data-simulasi-300-siswa.xlsx` | The workbook ketua gets with **Buat XLSX** and the default choices: every sheet she may receive except *Event Mentah*, labelled values, Kamus Data first (18.9 MB) |
| `GELITA-contoh-data-simulasi-300-siswa-csv.zip` | **Buat CSV-ZIP** with *Event Mentah* ticked too: one CSV per sheet, coded values, for SPSS, R or Python (5.0 MB; 87,463 raw events) |
| `gambar/*.webp` | The panel as `sim.ketua` saw it after the run: Ringkasan, Pemantauan, Siswa, Sesi lapangan, Data mentah, Penelitian, Unduh data |

## What is in it

Study `STUDI-KEDU-KELAS5-2026`, research mode on, plan `lima_pertemuan`, dictionary `kamus-1.1`, derived run computed at download time.

- **Schools, one after another:** six research schools `S01`–`S06` (*Sekolah Simulasi Kedu 1–6*) with 40, 41, 67, 63, 49 and 40 students in 14 class groups; each school finishes P1–P5 and its make-ups before the next starts (S01 6–17 July, S02 27 July–7 August, S03 17–28 August, S04 7–18 September, S05 28 September–9 October, S06 19–30 October 2026).
- **Field sessions:** 70 main sessions and 52 make-ups, all closed; 115 absences, most made up the next school day.
- **Students:** 298 registered (2 never came to P1 or its make-up); 291 completed all 15 challenges; 5 withdrew with their data kept and 5 moved school (consent register); 3 have no parent consent date; 45 challenges left open at the end of a meeting went on in a later one (attempt segment 2).
- **Tests and scales:** 298 pre-tests and questionnaires, 284 post-tests and risk-item sittings (212 answered the risk items), 283 attitude scales; mean raw test score 20.8 before and 26.8 after (Bagian 1–3 together). Booklets B1–B4: 85, 84, 67, 62.
- **Not simulated:** the typed field sheets (L1–L6 tabs and *Turunan Pengamatan* are empty: in a study they are typed from paper), audio events (the dev install had no narration files), and parents' data requests.

## How it was made

On a MariaDB development install (`tools/dev/serve.sh`, never a server with real data), at commit `3f446189` of branch `igi/affectionate-archimedes-cw013v`:

1. A fresh database: migrations, `DatabaseSeeder`, `php spark gelita:instrumen:impor`; research mode on with the five-meeting plan; staff accounts `sim.peneliti` and `sim.ketua`.
2. `.env`: `gelita.simulatedInResearch = true`. Without it, research data leaves out every `sim_*` student (29 §7.1, 35 K13) and ketua's dashboard and downloads stay empty. The switch is ignored in production.
3. `php spark gelita:simulasi --sekolah=6 --siswa=300 --seed=300` (930 s).
4. Signed in as `sim.ketua` with Playwright: screenshots of the panel, then **Buat XLSX** with the default choices and **Buat CSV-ZIP** with *Event Mentah* ticked, both downloaded from the download history.
5. The two committed files are those same two requests built again through `ExportService::build()` with `app.baseURL = 'https://gelita.id/'`, so the Kamus Data's links to the staff guide's glossary point to gelita.id instead of the dev install (review of 21 November 2026). The data sheets are identical to the panel downloads.

The same seed on the same starting database gives the same students, answers and times; ids, tokens and the download time differ. How the simulator plays research mode is in `docs/07_FEATURE_INTEGRATION.md` → FITUR 19 → *Research mode*.
