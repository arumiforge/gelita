# Glossary

One concept, one word, in every layer. "UI (id)" is what users read; "Code (en)" is the identifier to use in new code; "Stored" is the value already saved in the database or exports (never rename it).

## Game (students)

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| GELITA, Mbah Kedu, Jaka | — | Proper names; never translate. `mbah_kedu` → slug `kedu` |
| wilayah | `level`, `region` (map) | table `levels`; route `/wilayah/{code}` |
| peta | `map` | `MapController` |
| gerbang | `gate` | `GateController` |
| tantangan | `challenge`, `node` (a slot on the map) | `challenge_nodes` |
| misi (layar sebelum tantangan) | `brief` | route `/misi/…` |
| soal, butir | `item` | `challenge_items` |
| pengerjaan | `attempt` | `challenge_attempts` |
| kesempatan | `tries` (limit is 3) | `TRIES_EXHAUSTED`, `TRIES_REMAINING` |
| percobaan | `try` | `item_tries` |
| petunjuk (bantuan di tantangan) | `hint` | `hints`, `hint_used` |
| petunjuk Cari Objek | `clue` | `CLUE_CLOSED`, `clue_failed` |
| bintang | `stars` | rule `bintang-1` |
| Serpihan Cahaya | `shard` | `shards_total` |
| tuntas | `completed` / `done` | status `completed` |
| Susun Gambar | `puzzle` engine | `puzzle`, `puzzle_arrange` |
| Isi Rumpang | `rumpang` engine (cloze) | `rumpang`, `rumpang_bacaan` |
| Benar atau Salah | `boleh` engine (true/false cards) | `boleh` |
| Cari Objek | `cari` engine, `hunt` | `cari`, `cari_objek` |
| keping | `piece` | puzzle pieces |
| bacaan | `passage` | `reading_passages` |
| pustaka | `library` | `library_pages` |
| narasi, rekaman | `narration`, `audio asset` | `audio_assets` |
| refleksi | `reflection` | `ReflectionController` |

## People, data, and the panel

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| siswa | `participant` | `participants`; admin route `/admin/peserta` |
| guru, admin, staf | `staff`, `teacher` | `staff_users`; stored roles `guru`, `admin` |
| sekolah | `school` | `schools` |
| sesi bermain | `game session` | `game_sessions` |
| kata sandi (sandi) | `password` | never "password" in UI |
| masuk / keluar | `login` / `logout` | |
| Ringkasan | `dashboard` | |
| Data mentah | `raw data` | dimension codes `peserta`, `sesi`, … (stored, keep) |
| Unduh data | `export` | `data_exports`; route `/admin/ekspor` |
| Kritik & saran | `feedback` | `participant_feedback`; route `/admin/masukan` |
| Penelitian | `research study` | `research_studies` |
| Rilis | `game release` | `game_releases` |
| Tata kelola | `governance` | |
| Kelengkapan aset | `asset checklist` | `AssetChecklist` |
| bank soal | `question bank` | workbook `gelita-bank-soal-kelas5.xlsx` |
| kelas 5 | `grade 5` | doc and branch names keep "kelas5" |

Add a row whenever a new concept appears. If a term here conflicts with existing code, the existing stored value wins and the table gets corrected.
