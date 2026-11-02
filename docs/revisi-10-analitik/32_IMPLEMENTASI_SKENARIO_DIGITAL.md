# Dokumen Implementasi Bagian 6 — Skenario Digital, Narasi Instruksi, dan Aturan Narasi

Per 2 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 2, 6, dan 18 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `draf pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 1–2 November 2026). Ini dokumen, bukan perubahan sistem. Sejak 1 November 2026 kode boleh dibangun sekarang, di cabang pendek dari `main`, tetapi tidak dipakai dengan anak sebelum persetujuan etik terbit ([04](04_CATATAN_KEPUTUSAN.md), entri 1 November 2026). Kode bagian ini dibangun di gelombang E, setelah format bank bagian 5 (`31`), bersamaan dengan bagian 3 (`29`) dan 4 (`30`).
>
> Isi skenario (situasi, pilihan, umpan balik, kode) masih `needs_verification` dan menunggu validasi ahli. Dokumen ini hanya mengatur **mekanismenya**; revisi dari validasi cukup diubah di workbook lalu diimpor ulang.
>
> **Bagian 6 dari 9.** Peran dan peta kemampuan dari bagian 1 ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)), kemajuan per siswa dari bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)), katalog event dan `ready_ms` dari bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)), dan format workbook serta `error_code` dari bagian 5 (`31`, ditulis paralel).

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Satu skenario dari awal sampai akhir](#4-satu-skenario-dari-awal-sampai-akhir)
5. [Engine `skenario` di server](#5-engine-skenario-di-server)
6. [Engine `skenario` di peramban](#6-engine-skenario-di-peramban)
7. [Tantangan kelima di peta, cerita, dan Serpihan Cahaya](#7-tantangan-kelima-di-peta-cerita-dan-serpihan-cahaya)
8. [Narasi instruksi (perubahan no. 6)](#8-narasi-instruksi-perubahan-no-6)
9. [Aturan narasi (perubahan no. 18)](#9-aturan-narasi-perubahan-no-18)
10. [Data untuk kamus dan unduhan](#10-data-untuk-kamus-dan-unduhan)
11. [Skema database](#11-skema-database)
12. [Rute](#12-rute)
13. [Teks antarmuka](#13-teks-antarmuka)
14. [Titik sambung ke perubahan lain](#14-titik-sambung-ke-perubahan-lain)
15. [Pengujian dan kriteria selesai](#15-pengujian-dan-kriteria-selesai)
16. [Dokumen yang ikut diperbarui saat implementasi](#16-dokumen-yang-ikut-diperbarui-saat-implementasi)
17. [Pertanyaan terbuka](#17-pertanyaan-terbuka)
18. [Keputusan pengembang](#18-keputusan-pengembang)
19. [Akibat ke dokumen lain](#19-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Kode dibangun di gelombang E. Prasyaratnya:
- bagian 1 sudah digabung (peta kemampuan; bagian ini tidak menambah kemampuan baru);
- bagian 2 sudah digabung (`participant_progress`, dipakai gerbang cerita penghubung);
- bagian 5 sudah digabung (sheet `scenarios` dan `scenario_options` di workbook, `BankRules::ENGINES` berisi lima tantangan, kolom `item_tries.error_code`).

Bagian 3 dikerjakan bersamaan. Bila bagian 3 belum digabung saat bagian ini mulai, kolom `ready_ms` dan `EventCatalog` dibuat lebih dulu oleh bagian yang digabung pertama; yang kedua menyesuaikan ([§14](#14-titik-sambung-ke-perubahan-lain)).

**Batas.** Dokumen ini mengatur engine Skenario Digital, tempat tantangan kelima di game, narasi instruksi untuk kelima belas tantangan, dan aturan narasi untuk butir yang dinilai. Isi skenario ada di [08](08_DRAF_BANK_SOAL.md), cerita penghubung dan kartu misi di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md), dan bentuk workbook di `31`.

**Saat implementasi:**
- Kerjakan di satu cabang pendek dari `main`. Commit berbahasa Inggris (`.claude/rules/language.md`).
- `vendor/bin/phpunit --no-coverage` harus hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi (aturan kerja repo).

**Prinsip yang tetap berlaku:**
- Server satu-satunya sumber kebenaran: skor tindakan, kode kesalahan, kode alasan, dan tindakan yang tepat tidak pernah dikirim ke peramban sebelum dibutuhkan untuk umpan balik.
- Tulisan yang bisa berebut ke `challenge_attempts` dan `item_responses` memakai UPDATE bersyarat (`ChallengeService::updateIf()`/`updateOrStale()`), bukan `SELECT … FOR UPDATE`.
- Mekanisme di kode, isi di data (workbook, `story.php`, berkas bahasa).
- Teks yang dilihat siswa selalu punya pasangan bahasa Inggris.

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 2 November 2026 (sama dengan `main` di *commit* `97f82d5`). Nomor baris bisa bergeser, jadi cari nama metodenya.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Tantangan kelima Skenario Digital di ketiga wilayah (`tmg-5`, `mgl-5`, `wnb-5`), 4 skenario per variasi, variasi a dan b | [01 → Skenario Digital](01_REVISI_10_ANALITIK.md#skenario-digital-tantangan-kelima); [06](06_KISI_KISI_BANK_SOAL.md#kisi-kisi-per-wilayah-dan-tantangan); [08](08_DRAF_BANK_SOAL.md) `tmg-5`, `mgl-5`, `wnb-5` | §5, §7 |
| 2 | Dua langkah: tindakan, lalu alasan; alasan ditanyakan apa pun tindakannya | 01, alur satu skenario | §4, §6 |
| 3 | Skor tindakan 0–1–2 dihitung di server; alasan hanya dikode (R-RISK, R-ETIK, R-ATUR, R-SOS, R-ACAK), tidak diskor sampai setelah uji coba | 01; [06 → Kode kesalahan](06_KISI_KISI_BANK_SOAL.md#kode-kesalahan-di-setiap-pilihan); [11 → Analitik 10](11_KAMUS_DATA.md#analitik-10-pilihan-dan-alasan-di-skenario-digital) | §5.3 |
| 4 | Tiga kesempatan **per skenario**; satu percobaan = satu tindakan dan satu alasan; skenario tertutup saat tindakan tepat dipilih atau kesempatan ketiga selesai | 01 (25 Oktober 2026); [11 → Percobaan per jenis tantangan](11_KAMUS_DATA.md#percobaan-per-jenis-tantangan) | §5.4 |
| 5 | Yang dianalisis tindakan dan alasan **pertama**; `reason_code` dan skor tindakan disimpan di setiap percobaan | 11 → Analitik 10, *Yang harus disediakan sistem* | §5.5, §10 |
| 6 | Umpan balik ramah tanpa kata "salah" atau "gagal"; setelah kesempatan ketiga tindakan aman ditampilkan dan dijelaskan | 01; [18 → Skenario Digital](18_MOCKUP_LAYAR_BARU.md#1-skenario-digital-mk-sd-01--mk-sd-05) | §6.3 |
| 7 | Event penutupan skenario setelah kesempatan ketiga, setara `clue_failed` | 11 → Analitik 1 jenis (a); 29 §6.4 | §5.6 |
| 8 | Saat pilihan tindakan dan pilihan alasan tampil dicatat (`ready_ms`) | 11 → *Yang harus disediakan sistem*; [29 §6.1](29_IMPLEMENTASI_DATA_PENELITIAN.md#61-saat-butir-bisa-dijawab-ready_ms) | §5.5, §6.4 |
| 9 | Pilihan diacak per pengerjaan; tidak ada isyarat visual pada pilihan tepat | 06 → *Yang perlu disiapkan sistem nanti* no. 1; 18 | §5.2 |
| 10 | Cerita penghubung `scenario_open` sekali per siswa per studi, sebelum kartu misi tantangan kelima | [12 → Yang perlu disiapkan di tahap 3](12_CERITA_PENGHUBUNG_DAN_NARASI.md#yang-perlu-disiapkan-di-tahap-3) no. 1 | §7.2 |
| 11 | `level_done` dipicu tantangan kelima; `map_done` setelah 15 tantangan; Serpihan Cahaya jadi 15 | 12 no. 2; 01 no. 2 | §7.3 |
| 12 | Kartu misi tiga tantangan baru dan tujuh yang berubah | [12 → Kartu misi](12_CERITA_PENGHUBUNG_DAN_NARASI.md#kartu-misi) | §7.4 |
| 13 | Gambar situasi (opsional), latar cerita penghubung, gambar kartu tantangan peta | 08; 12 no. 6; 18 | §7.5 |
| 14 | Narasi instruksi dengan kode konteks sendiri, supaya mengulang instruksi terpisah dari memutar narasi soal | 01 no. 6; [11 → Analitik 7](11_KAMUS_DATA.md#analitik-7-penggunaan-bantuan) (`instruction_replays`) | §8 |
| 15 | Bacaan dan pernyataan D1 tidak pernah dibacakan; situasi Skenario Digital dan petunjuk *Periksa Unggahan* hanya lewat tombol ▶ dan tercatat | 01 no. 18; 12 → *Yang dibacakan di Skenario Digital*; 18 → *Keputusan yang dipakai di semua layar* | §9 |
| 16 | Impor narasi mengenali awalan `layar-`, `skenario-`, `unggahan-`, `instruksi-` | 12 no. 4 | §8.3, §9.3 |
| 17 | Arahan suara datar untuk situasi dan petunjuk yang dinilai, diperiksa saat rekaman disetujui | 12 → *Arahan suara* | §9.4 |

---

## 2. Keputusan yang dikunci

Keputusan ini tidak perlu ditanyakan ulang saat implementasi. K1, K7, K8, dan K10 diputuskan pengembang 2 November 2026 ([§18](#18-keputusan-pengembang)); sisanya menurunkan 01, 06, 08, 11, 12, dan 18.

| Kode | Keputusan |
| --- | --- |
| K1 | **Kode engine tersimpan `skenario`** di `challenge_nodes.engine_type`, sejajar dengan `rumpang`, `boleh`, dan `cari`. Jenis butirnya `scenario_choice` (`challenge_items.interaction_type`). Kedua kode tidak pernah diganti setelah data pertama tersimpan. |
| K2 | **Satu butir = satu skenario.** Satu tantangan Skenario Digital berisi 4 butir per variasi (a, b). Variasi pengerjaan pertama mengikuti buklet siswa (bagian 5, `31`); pengerjaan ulang mengikuti aturan pengerjaan ulang bagian 7. |
| K3 | **Satu percobaan = satu tindakan dan satu alasan**, dikirim bersama sesudah langkah 2. Alasan tidak dikirim sendiri dan tidak pernah dinilai benar atau salah. |
| K4 | **Kesempatan dihitung per skenario** (3), seperti petunjuk Cari Objek. Skenario tertutup **benar** saat tindakan berskor 2 dipilih, atau tertutup **tidak benar** setelah percobaan ketiga. Tindakan berskor 1 ("kurang tepat") memakai satu kesempatan. Pengerjaan tertutup saat keempat skenario tertutup. |
| K5 | **Bintang tetap `bintang-1`** dan `ScoringService` tidak berubah: butir benar = tindakan berskor 2 di akhir; benar di percobaan pertama = tindakan pertama berskor 2. Skor 0–1–2 hanya data penelitian, tidak mengubah bintang atau serpihan. |
| K6 | **Kunci tidak pernah ke peramban.** Pilihan dikirim dengan rujukan buram per pengerjaan (HMAC, seperti `objectRef()` Cari Objek). Skor, kode DIG-\*, kode R-\*, dan umpan balik hanya ada di `answer_key_json`; umpan balik pilihan yang dikirim dan penjelasan tindakan aman baru dikirim di respons `/responses`. |
| K7 | **Urutan pilihan diacak sekali per pengerjaan**, dengan benih dari id pengerjaan dan id butir, jadi tetap sama di ketiga kesempatan dan setelah muat ulang. Urutan yang tampil disimpan di `answer_json` setiap percobaan. |
| K8 | **Tanpa sakelar studi.** Engine selalu ada di kode. Tantangan kelima muncul bila bank yang diimpor memuatnya; total Serpihan Cahaya dihitung dari jumlah node aktif, jadi 12 menjadi 15 tanpa angka tertulis di kode. Aturan narasi (§9) berlaku juga di luar studi. |
| K9 | **Cerita penghubung `scenario_open` sekali per siswa per studi**, diputar sebagai gerbang sebelum kartu misi tantangan kelima selama siswa belum punya pengerjaan di tantangan itu. Pengerjaan ulang tidak memutarnya lagi; siswa boleh menontonnya ulang dari peta wilayah. |
| K10 | **Narasi instruksi hanya lewat tombol ▶**, di samping teks instruksi di layar tantangan, untuk kelima belas tantangan. Tidak pernah berbunyi sendiri. Kode konteks audio `challenge_instruction`; setiap putaran adalah pilihan siswa dan dihitung `instruction_replays`. |
| K11 | **Situasi Skenario Digital dan petunjuk *Periksa Unggahan* hanya lewat tombol ▶**, tidak otomatis, dan setiap putaran tercatat dengan id pengerjaan. Kode konteks audio `scenario_situation` dan `post_clue`. Pilihan tindakan, pilihan alasan, dan umpan balik tidak dibacakan. |
| K12 | **Bacaan dan pernyataan D1 tidak pernah dibacakan.** Importer dan panel menolak memasang rekaman ke butir Isi Rumpang, Benar atau Salah, atau bacaan; tidak ada tombol suara di keduanya. |
| K13 | **Penutupan skenario setelah kesempatan ketiga** menulis event server `scenario_failed` (hanya server), setara `clue_failed`. |
| K14 | **Nomor migrasi blok `2026-01-01-0040xx`**, sementara; dinomori ulang sesuai urutan digabung bila perlu. |

---

## 3. Yang tidak berubah

| Bagian | Aturan |
| --- | --- |
| Aturan 3 kesempatan | `ChallengeAttempt::maxTries()` (3), `ChallengeRuleException` (`TRIES_EXHAUSTED`, `CLUE_CLOSED`, `ATTEMPT_CHANGED`) tetap. Skenario memakai `CLUE_CLOSED` untuk kiriman ke skenario yang sudah tertutup, seperti petunjuk Cari Objek. |
| Bintang | `ScoringService::starsFromCounts()` (`app/Services/ScoringService.php:30-41`) dan `finalize()` tidak berubah. `ItemResponseModel::countCorrect()` (`app/Models/ItemResponseModel.php:87-107`) tetap membaca `first_pass_correct` dan `is_correct`. |
| Serpihan Cahaya | Satu serpihan per tantangan tuntas (`solved` atau `tries_exhausted`). `GameProgress::progressSummary()` sudah menjumlah node aktif (`app/Controllers/Concerns/GameProgress.php:198-216`), jadi tidak ada angka 12 yang perlu diganti. |
| Kerangka tantangan | `game/challenge.js` tetap kerangka bersama: `submit()`, `ruleError()`, penanda kesempatan, `state.leaving`, tombol Keluar, `challenge_abandoned`. Engine baru memakai jalur `submit()` yang sama dengan Cari Objek. |
| Engine lain | Susun Gambar, Isi Rumpang, Benar atau Salah, dan Cari Objek tetap, kecuali tombol ▶ instruksi (§8) yang tampil di semua layar tantangan. |
| Kartu misi | `game/mission-brief` dan narasi kartu misi `challenge_nodes.audio_intro_id`/`audio_intro_en_id` tetap (narasi cerita kartu, bukan instruksi). |
| Antrean event | `core/events.js` dan `/api/events`; event audio tetap lewat `/api/audio-events` (`audio_usage_events`). |

---

## 4. Satu skenario dari awal sampai akhir

1. Layar menampilkan skenario pertama yang belum tertutup: situasi di panel kiri, instruksi dan langkah di kanan (tata letak `mk-sd-*` di [18](18_MOCKUP_LAYAR_BARU.md#1-skenario-digital-mk-sd-01--mk-sd-05)).
2. **Langkah 1.** Pilihan tindakan tampil. Satu dipilih (tanda ● dan cahaya, tidak hanya warna); tombol *Lanjut ke alasan* aktif.
3. **Langkah 2.** Tindakan pilihan diulang kecil ("Pilihanmu: …"), lalu pilihan alasan dengan kalimat tanya tetap "Yang paling penting kupikirkan di sini adalah …". Siswa boleh kembali ke langkah 1 sebelum mengirim.
4. **Kirim.** Server menilai, menyimpan, dan membalas.
5. **Umpan balik.** Skor 2: umpan balik pilihan tepat, skenario tertutup, *Lanjut ke cerita berikutnya*. Skor 0 atau 1 dengan kesempatan tersisa: umpan balik pilihan itu dan *Coba lagi* (kembali ke langkah 1, pilihan dikosongkan, urutan sama). Kesempatan ketiga habis: tindakan aman ditampilkan dengan penjelasan setelah kesempatan habis dari bank, lalu *Lanjut*.
6. Setelah skenario keempat tertutup, pengerjaan ditutup server (`solved` bila keempatnya benar, selain itu `tries_exhausted`), lalu modal hasil dan layar selesai seperti tantangan lain.

---

## 5. Engine `skenario` di server

### 5.1 Bentuk butir di database

Butir `scenario_choice` memakai kolom `challenge_items` yang sudah ada (`2026-01-01-001200_CreateChallengeItems`); tidak ada kolom butir baru.

| Kolom | Isi |
| --- | --- |
| `item_key` | Kode butir dari bank, misalnya `tmg-5-a02` |
| `variant_group` | `a` atau `b` |
| `prompt_id`, `prompt_en` | Teks situasi |
| `media_asset_id` | Gambar situasi (opsional, §7.5) |
| `audio_prompt_id`, `audio_prompt_en_id` | Rekaman situasi `skenario-{kode butir}` (§9.3) |
| `config_json` (aman, dikirim) | `actions`: daftar `{key, text_id, text_en}`; `reasons`: daftar `{key, text_id, text_en}`; `topic` (kode topik, misalnya `data_pribadi`) |
| `answer_key_json` (rahasia) | `actions`: peta `key → {score, error_code, feedback_id, feedback_en}`; `reasons`: peta `key → {reason_code}`; `explanation_id`, `explanation_en` (penjelasan setelah kesempatan habis) |
| `scorable` | 1 |
| `bloom_level` | Label Bloom pengembang dari 08 |

`key` pilihan adalah kunci tetap dari bank (misalnya `t1`…`t4`, `r1`…`r5`), dipakai untuk `first_action_key` di kamus. Kunci ini tidak pernah dikirim mentah ke peramban (K6, §5.2).

- `ChallengeItem::SAFE_CONFIG_KEYS` (`app/Entities/ChallengeItem.php:14`, sekarang `x`, `y`, `w`, `grid`, `shape`; dipakai `safeConfig()` baris 111–124) **tidak** memuat `actions` dan `reasons`, supaya kunci tetap tidak terkirim lewat `toPlayerArray()` (baris 126–141). Pilihan dikirim lewat bagian payload sendiri (§5.2).
- Metode baru di `ChallengeItem`: `scenarioActions()`, `scenarioReasons()`, `actionKey(string $key): ?array` (dari `answer_key_json`), `reasonCode(string $key): ?string`, `scenarioExplanation(string $locale): ?string`, `safeAction(): ?array` (tindakan berskor 2).
- Aturan bentuk bank (satu tindakan berskor 2, satu berskor 1, sisanya 0; setiap tindakan tidak tepat berkode DIG-\*; setiap alasan berkode R-\*; jumlah 3/4/4 tindakan dan 3/4/5 alasan per tingkat) diperiksa `BankRules` dan `DifficultyRules` milik bagian 5 (`31`). Bagian ini hanya membaca hasil impornya.

### 5.2 Payload untuk peramban

`ChallengeService::buildPayload()` (`app/Services/ChallengeService.php:1106-1180`) mendapat cabang baru, sejajar dengan `cari` (baris 1157–1160):

```php
if ($node->engine_type === 'skenario') {
    unset($payload['items']);
    $payload += $this->scenarioPayload($attempt, $items, $locale, $responses);
}
```

`scenarioPayload()` (baru) mengembalikan `scenarios`: satu baris per butir, urut `sequence`:
- `id`, `prompt`, `media`, `audio` dan `audio_id` (hanya rekaman yang disetujui, seperti `clueAudio()` baris 1282–1293);
- `actions` dan `reasons`: daftar `{ref, text}` **dalam urutan acak pengerjaan** (K7);
- `state`: `tries` (percobaan terpakai), `closed` (bool), dan bila sudah tertutup: `outcome` (`safe` atau `exhausted`), `chosen` (ref tindakan dan alasan terakhir), `feedback`, dan untuk `exhausted` juga `safe_action` dan `explanation`. Skenario yang masih terbuka tidak membawa skor, kode, atau umpan balik.

**Rujukan buram.** `optionRef(int $attemptId, int $itemId, string $kind, string $key)` = 20 karakter pertama `hash_hmac('sha256', 'skenario-option|…', encryption.key)`, mengikuti `objectRef()` (baris 1295–1304). `resolveScenarioAnswer()` (baru) menerjemahkan ref kiriman kembali ke kunci di server; ref yang tidak dikenal → `\InvalidArgumentException` (400), seperti `resolveObjectAnswer()` (baris 1315 dan seterusnya).

**Acak sekali per pengerjaan (K7).** Urutan dihitung dari `crc32($attemptId . ':' . $itemId . ':' . $kind)` sebagai benih `mt_srand()` lokal di `scenarioOrder()`, lalu `shuffle()`; benih global dikembalikan sesudahnya. Hasilnya sama di setiap muat ulang dan di ketiga kesempatan, dan berbeda antarpengerjaan. Tidak perlu kolom baru. Bila tindakan tepat jatuh di urutan pertama, urutan **tidak** diubah: keseimbangan posisi diperiksa di data, bukan dipaksa (pemaksaan justru membuat posisi pertama tidak pernah tepat).

### 5.3 Penilaian

`ChallengeService::grade()` (baris 749–758) mendapat lengan `'scenario_choice' => $this->gradeScenario($item, $answer)`. `gradeScenario()` mengembalikan benar bila skor tindakan 2. Skor dan kode dibaca oleh `scenarioOutcome()` (baru), yang mengembalikan `['score' => 0|1|2, 'error_code' => ?string, 'reason_code' => string, 'feedback' => ?string]`.

Alasan tidak memengaruhi benar atau tidak (K3). `reason_level` 0–2 tidak dihitung sistem; bila nanti dipakai setelah uji coba, ia diturunkan lapis data turunan (bagian 9) dari `reason_code`.

### 5.4 Kiriman satu percobaan: `submitScenario()`

`ChallengeService::submitAnswer()` (baris 185–270) sekarang hanya menerima `find_object` (baris 198–200). Pemeriksaan itu menjadi percabangan:

```php
if ($item->interaction_type === 'scenario_choice') {
    return $this->submitScenario($attempt, $session, $node, $item, $answer, $meta);
}
```

`submitScenario()` (baru, privat):
1. `assertInProgress()` sudah dipanggil di awal `submitAnswer()`. Ambil `item_responses` butir itu; bila sudah `answered` → `ChallengeRuleException::clueClosed()` (409), seperti baris 209–211.
2. `resolveScenarioAnswer()` → kunci tindakan dan alasan. Keduanya wajib; yang kosong → `ChallengeRuleException::incomplete()` (422).
3. Di dalam transaksi (`transBegin()`):
   - `scenarioOutcome()`; `$tryNo = try_count + 1`; `$closed = score === 2 || $tryNo >= maxTries()`.
   - Tulis `item_responses` dengan `updateOrStale()` dan versi `['try_count' => $response->try_count]`, sama dengan `applyAnswer()` (baris 826–892): `try_count`, `first_answer_json` dan `first_pass_correct` bila percobaan pertama, `solved_on_try` bila skor 2, dan bila `$closed`: `final_answer_json`, `is_correct`, `status = answered`, `answered_at`, `duration_ms`.
   - `recordTry()` (baris 894–912) dengan kolom baru `action_key`, `action_score`, `reason_key`, `reason_code`, `reason_ready_ms` dan `ready_ms` (§11), serta `error_code` dari bagian 5.
   - Event `answer_submitted` dengan `try_no`, `first_pass`, `action_score`, `closed` (tanpa kode, karena event dibaca bersama `item_tries`).
   - Bila tertutup tanpa skor 2: event `scenario_failed` (§5.6).
   - Bila `pendingItemIds()` kosong: `closeAttempt()` dengan `allCorrect() ? 'solved' : 'tries_exhausted'`, seperti baris 256–258.
   - `transCommit()`; kegagalan → `rollback()` (baris 1472 dan seterusnya), yang menjawab dari keadaan terbaru bila barisnya sudah diubah kiriman lain (`ATTEMPT_CHANGED`).
4. Respons: `score_band` (`safe`, `partial`, `risky`, dipakai klien hanya untuk memilih kalimat dan ikon, bukan warna merah), `feedback`, `try_no`, `tries_left`, `scenario_closed`, dan bila tertutup tanpa skor 2: `safe_action` (teks) dan `explanation`. Ditambah `progress` dan `completionFields()` seperti `submitAnswer()`.

**Dua tab atau kiriman ganda.** Dua kiriman bersamaan untuk skenario yang sama membawa `try_count` lama yang sama; `updateOrStale()` membuat yang kalah gagal, lalu `rollback()` menjawab dari keadaan terbaru. Indeks unik `item_tries (challenge_attempt_id, challenge_item_id, try_no)` menjadi pengaman kedua.

### 5.5 Penutupan dan kesempatan per butir

Tiga tempat sekarang memperlakukan `cari` sebagai satu-satunya engine yang kesempatannya per butir. Ketiganya membaca metode baru `ChallengeNode::triesPerItem(): bool` (benar untuk `cari` dan `skenario`):

| Tempat | Sekarang | Sesudah |
| --- | --- | --- |
| `ChallengeService::closingReason()` (baris 615–628) | `$node->engine_type === 'cari'` | `$node->triesPerItem()` |
| `ChallengeService::submitCheck()` (baris 325–327) | Menolak `cari` | Menolak semua `triesPerItem()`; skenario tidak memakai `/check` |
| `game/challenge.js` penanda kesempatan | Per tantangan untuk engine batch | Engine `skenario` menggambar penanda kesempatan skenario aktif sendiri, seperti petunjuk Cari Objek |

`challenge_attempts.check_count` tetap 0 untuk Skenario Digital, seperti Cari Objek.

### 5.6 Event `scenario_failed`

Event server baru, ditulis di `submitScenario()` saat skenario tertutup tanpa tindakan tepat. Payload `try_count`; rujukan `level_id`, `challenge_node_id`, `challenge_attempt_id`, `challenge_item_id`.

- Masuk `EventCatalog::RESEARCH` dan `EventCatalog::SERVER_ONLY` (bagian 3, [29 §4](29_IMPLEMENTASI_DATA_PENELITIAN.md#4-katalog-event)). Bila bagian 3 belum digabung: `Config\Gelita::$eventTypes` (`app/Config/Gelita.php:96-112`) dan `EventService::SERVER_ONLY` (`app/Services/EventService.php:22`).
- Dipakai `skipped_failed_count` (Analitik 1 jenis a) di bagian 9.
- Nama dipilih sejajar dengan `clue_failed`; tidak memakai `clue_failed` supaya Cari Objek dan Skenario Digital bisa dihitung terpisah tanpa melihat engine.

### 5.7 Simulasi dan pemeriksaan konten

- `Simulator` (`app/Libraries/Simulation/Simulator.php`, cabang engine sekitar baris 444) mendapat cabang `skenario`: memilih ref tindakan dan alasan dari payload menurut profil siswa simulasi, lalu memanggil `submitAnswer()`. Siswa `sim_*` tetap hanya untuk pengembangan.
- `ContentImportService` mengimpor sheet `scenarios` dan `scenario_options` ke bentuk §5.1 (rinciannya milik bagian 5). `ContentVerifier` melaporkan skenario tanpa rekaman situasi sebagai **peringatan**, bukan galat, karena situasi tetap terbaca tanpa suara.
- `Admin\ContentController` (peta engine → jenis butir, baris 37) mendapat `'skenario' => ['scenario_choice']`; halaman butir menampilkan tindakan, skor, kode, alasan, dan umpan balik sebagai tabel baca (`content.view`) dan sunting (`content.edit`).

---

## 6. Engine `skenario` di peramban

### 6.1 Berkas dan pendaftaran

- Berkas baru `public/assets/js/engines/skenario.js`, ekspor `mount(ctx)` dengan bentuk yang sama dengan `engines/cari.js` dan `engines/boleh.js`.
- `game/challenge.js` (`ENGINES`, baris 55–60) mendapat `skenario: () => import('../engines/skenario.js')`.
- `Config\Gelita::$engineTypes` (`app/Config/Gelita.php:14`) mendapat `'skenario'`; `ChallengeController::play()` (`app/Controllers/Game/ChallengeController.php:58-62`) menolak engine yang tidak ada di daftar ini.
- Tampilan baru `app/Views/game/challenge/skenario.php` di atas `_frame.php`, seperti tampilan engine lain. Pilihan memakai papan biru `--plate-navy` yang sudah dipakai Isi Rumpang dan Benar atau Salah.
- Salinan ringkas `public/assets/min/` dibuat ulang lewat `gelita:aset:minify`; sumbernya tetap tidak diringkas.

### 6.2 Keadaan layar

| Keadaan | Yang tampil | Tombol di bilah tantangan |
| --- | --- | --- |
| `action` | Situasi, ▶ *Dengarkan*, penanda langkah "1 Tindakan — 2 Alasan", pilihan tindakan | *Lanjut ke alasan* (aktif setelah memilih) |
| `reason` | "Pilihanmu: …", kalimat tanya alasan, pilihan alasan | *Kembali*, *Kirim jawaban* |
| `feedback` | Umpan balik pilihan, kesempatan tersisa | *Coba lagi* atau *Lanjut ke cerita berikutnya* |
| `explained` | "Ini tindakan yang aman", teks tindakan aman, penjelasan | *Lanjut ke cerita berikutnya* |
| selesai | Modal hasil dan layar selesai dari kerangka | *Lanjut* (`#btn-next`) |

Penanda "Cerita 2 dari 4" di kepala panel menunjukkan skenario aktif. Tidak ada jam selama studi (bagian 7). Pilihan yang dipilih ditandai bentuk (●) dan cahaya, tidak hanya warna. Umpan balik "kurang tepat" dan "berisiko" tidak memakai warna merah atau kata "salah"/"gagal" (18).

### 6.3 Umpan balik

Teks umpan balik dan penjelasan datang dari respons server (bank). Kalimat bingkainya berkas bahasa (§13). Saat muat ulang di keadaan `feedback`, layar kembali ke langkah 1 skenario yang sama dengan kesempatan yang tersisa (umpan balik terakhir tidak diulang); skenario yang sudah tertutup tampil dalam keadaan akhirnya dari `state`.

### 6.4 Waktu

Semua dalam milidetik sejak tantangan dibuka (`sinceOpen()` di `game/challenge.js`, baris 75–77), rujukan yang sama dengan bagian 3:

| Kiriman | Arti |
| --- | --- |
| `ready_ms` | Pilihan tindakan tampil untuk percobaan ini (layar skenario tampil, atau *Coba lagi* diketuk) |
| `reason_ready_ms` | Pilihan alasan tampil (terakhir kali, bila siswa kembali ke langkah 1 lalu maju lagi) |
| `first_input_ms` | Pilihan tindakan pertama di percobaan ini |
| `last_input_ms` | *Kirim jawaban* diketuk |

`selected()` (baris 79–86) tetap dipanggil untuk setiap pilihan tindakan dan alasan, dengan `change.step` = `action` atau `reason`. Pengiriman event `answer_selected` dihapus bagian 3; waktunya tetap dipakai. Server menjepit keempat nilai dengan `clientMs()` (baris 914–923) dan mengosongkan `ready_ms` atau `reason_ready_ms` yang lebih besar dari `last_input_ms`, mengikuti 29 §6.1.

---

## 7. Tantangan kelima di peta, cerita, dan Serpihan Cahaya

### 7.1 Node dan peta wilayah

- Node kelima tiap wilayah berasal dari sheet `nodes` workbook (bagian 5): `sequence` 5, `engine_type` `skenario`, kartu misi, gambar kartu, dan tingkat.
- Peta wilayah membaca node dari `ContentRepository::nodesForLevel()`; kartu tantangan `.node-card` mendapat gambar mesin `public/assets/ui/node-card-skenario.webp` (sepia, dalam bingkai `node-card-frame.webp`). Gambar ini dipesan ke tim Katalog Aset (§7.5); sampai ada, kartu memakai gambar umum yang sama dengan engine tanpa gambar.
- `engine_label()` (dipakai `mission-brief.php` baris 36) mendapat label "Skenario Digital" / *Digital Scenarios*.
- Tantangan dalam wilayah tetap terbuka berurutan (`GameProgress::nodeUnlocked()`); tantangan kelima terbuka setelah tantangan keempat tuntas.

### 7.2 Cerita penghubung `scenario_open`

- **Konteks.** `Config\Gelita::$dialogueContexts['level']` (`app/Config/Gelita.php:65-68`) mendapat `scenario_open`; `$dialogueBackgroundContexts` (baris 77) juga, supaya latar slide `bg.story.scenario_open.{wilayah}.{n}` dipakai. Editor dialog panel (`ContentController::dialogues()`) dan `ContentRepository::dialogues()` membaca daftar itu tanpa perubahan lain.
- **Isi.** 18 baris `layar-{wilayah}-NN` dari [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#cerita-penghubung-tantangan-kelima-scenario_open) masuk `app/Database/Seeds/data/story.php` (konteks `scenario_open`, satu bagian baru di antara dialog wilayah dan wilayah tuntas) dan naskah aktif bersama-sama (`StoryDataTest`).
- **Halaman.** Rute baru `GET /penghubung/{wilayah}` → `DialogueController::bridge()`, memakai tampilan cerita yang sama dengan `/dialog/{wilayah}` (`game/dialogue`, `game/narrator.js`), musik adegan `dialog-{wilayah}`. Slide terakhir berakhir dengan tombol ke kartu misi tantangan kelima (`/misi/{wilayah}/5`).
- **Gerbang (K9).** `ChallengeController::brief()` dan `play()` (baris 19–39, 41 dan seterusnya) memanggil `scenarioGate()` (baru, `BaseGameController`) setelah `dialogueGate()`: bila node berengine `skenario`, siswa belum punya pengerjaan di node itu (lintas sesi game, lewat `participant_progress` dan `ChallengeAttemptModel` bagian 2), dan penanda sesi PHP `gelita.scenario_shown` untuk wilayah itu belum ada, alihkan ke `/penghubung/{wilayah}`. `bridge()` memasang penandanya, seperti `markDialogueShown()` (baris 144–151).
- "Sekali per siswa per studi" dijamin oleh syarat "belum punya pengerjaan": begitu pengerjaan pertama dibuka, gerbang tidak pernah muncul lagi, juga di pertemuan lain. Penanda sesi hanya mencegah gerbang berulang bila siswa kembali ke peta sebelum membuka tantangan.
- **Tonton ulang.** Kartu tantangan kelima di peta wilayah mendapat tautan kecil "Lihat cerita penghubung" bila cerita sudah pernah dibuka. Tidak memengaruhi data.
- Slide terakhir mengirim `dialogue_completed` dengan `context = scenario_open` (bagian 3, [29 §5.3](29_IMPLEMENTASI_DATA_PENELITIAN.md#53-dialogue_advanced-diganti-dialogue_completed)).

### 7.3 Wilayah tuntas dan Serpihan Cahaya

- `level_done` dan `/tuntas/{wilayah}` sudah dibaca dari `regionStatus() === 'completed'` (`DialogueController::done()`), yaitu semua node wilayah tuntas, jadi otomatis dipicu tantangan kelima. Tidak ada perubahan kode; tesnya ditulis ulang (§15.2).
- `map_done` dan `/penutup` membaca `allNodesCompleted()` (`GameProgress.php:220-225`), jadi otomatis setelah 15 tantangan.
- Total serpihan = jumlah node aktif (`progressSummary()`, baris 198–216). Papan Serpihan Cahaya di HUD (`hud-shards-frame.webp`) mengisi bilah dari hitungan nyata, jadi tidak perlu gambar baru. Teks yang menyebut "12 serpihan" di berkas bahasa dan cerita dicari dan diganti angka dari data atau kalimat tanpa angka (§13).
- Simbol kristal per wilayah (`.lantern-gem`, `data-stage`) tetap per wilayah tuntas.

### 7.4 Kartu misi

Kartu misi 15 tantangan, termasuk tiga kartu baru dan tujuh yang berubah di [12 → Kartu misi](12_CERITA_PENGHUBUNG_DAN_NARASI.md#kartu-misi), masuk sheet `nodes` workbook (bagian 5) dan diimpor ke `challenge_nodes.description_*` dan `instruction_*`. Tidak ada kode baru di sini; bagian ini hanya memeriksa bahwa `mission-brief.php` (baris 84–97) menampilkan kartu tantangan kelima tanpa perubahan.

### 7.5 Gambar

| Gambar | Slot | Wajib? |
| --- | --- | --- |
| Kartu tantangan peta, engine Skenario Digital | `public/assets/ui/node-card-skenario.webp` | Ya, sebelum G3 |
| Latar cerita penghubung, satu per wilayah | Katalog `bg.cerita-layar-{wilayah}-NN` → `bg.story.scenario_open.{wilayah}.{n}` | Ya, sebelum G3 (12 no. 6) |
| Gambar situasi per butir (24) | `challenge_items.media_asset_id`, katalog `item.skenario.{kode butir}` | Opsional (08); tablet atau pesan tiruan tanpa layar sungguhan dan tanpa merek |
| Latar layar tantangan kelima | `challenge_nodes.background_media_id`, sama dengan latar wilayah bila kosong | Tidak |

Gambar situasi tidak boleh memuat petunjuk jawaban (misalnya ikon bahaya). Nama berkas tidak memuat kata seperti "aman" atau "tipuan".

---

## 8. Narasi instruksi (perubahan no. 6)

### 8.1 Temuan kode

Sekarang tidak ada narasi instruksi. Kartu misi memutar `challenge_nodes.audio_intro_id`/`audio_intro_en_id` (`mission-brief.php:30`, 92–97) dengan transkrip **cerita** kartu (`description`), atau instruksi bila cerita kosong. Rekaman itu dipasang manual di panel (`ContentController`, baris 249), tanpa pola impor. Di layar tantangan, instruksi hanya teks (`challenge/_frame.php:106-109`). Akibatnya Analitik 7 tidak bisa membedakan "mengulang instruksi" dari narasi lain.

### 8.2 Sesudah

- **Kolom baru** `challenge_nodes.audio_instruction_id` dan `audio_instruction_en_id` (rujukan `audio_assets`, `ON DELETE SET NULL`), di migrasi §11.
- **Kode konteks audio** `challenge_instruction` di `audio_assets.context_code`; tokohnya Narator.
- **Tombol ▶ (K10)** di samping teks instruksi di `challenge/_frame.php`, memakai `component('audio-player', …)` dengan id pengerjaan, sehingga setiap putaran menulis `audio_usage_events.challenge_attempt_id`. Tidak ada `autoplay`. Bila rekaman belum ada atau belum disetujui, tombol tidak tampil; teks instruksi tetap.
- Kartu misi tidak memutar narasi instruksi; narasi cerita kartu tetap di tempatnya.
- `instruction_replays` (Analitik 7) = banyak aksi `play`/`replay` dengan `context_code = challenge_instruction` di satu pengerjaan. Karena tidak ada putaran otomatis, semua putaran dihitung; definisi ini dicatat untuk `kamus-1.1` (§19).

### 8.3 Impor

- `NarrationImporter` (`app/Libraries/NarrationImporter.php`) mendapat pola baru `instruksi-{node}`, misalnya `instruksi-tmg-5`, untuk setiap node aktif kelima belas tantangan. `{node}` = `node_ref()`, sama dengan pola `petunjuk-{node}-NN` (baris 27–34, `CLUE_PREFIX` baris 90). Transkrip = `instruction_id`/`instruction_en` node; kolom tujuan `audio_instruction_id`/`audio_instruction_en_id`.
- Konstanta baru `INSTRUCTION_PREFIX = 'instruksi'`, `INSTRUCTION_CONTEXT = 'challenge_instruction'`, `INSTRUCTION_COLUMNS`.
- `NarrationCatalog` (daftar rekaman XLSX, halaman Narasi), `NarrationScriptPdf` (naskah pengisi suara), dan `docs/audio.md` memuat kelompok *Instruksi tantangan* (15 berkas per bahasa).

---

## 9. Aturan narasi (perubahan no. 18)

### 9.1 Yang dibacakan

| Bagian | Dibacakan? | Cara | Kode konteks audio | Kode berkas |
| --- | --- | --- | --- | --- |
| Cerita penghubung | Ya | Otomatis, seperti cerita lain (`game/narrator.js`) | `scenario_open` (konteks dialog) | `layar-{wilayah}-NN` |
| Instruksi tantangan | Ya | Hanya ▶ (K10) | `challenge_instruction` | `instruksi-{tantangan}` |
| Situasi Skenario Digital | Ya | Hanya ▶ *Dengarkan* (K11) | `scenario_situation` | `skenario-{kode butir}` |
| Petunjuk *Periksa Unggahan* | Ya | Hanya ▶ di bilah petunjuk (K11) | `post_clue` | `unggahan-{kode butir}` |
| Petunjuk Temukan Benda (PBK) | Ya | Tombol ▶ seperti sekarang | `hunt_clue` (tetap) | `petunjuk-{tantangan}-NN` |
| Pilihan tindakan, alasan, umpan balik | Tidak | — | — | — |
| Bacaan dan pernyataan D1 | Tidak, tidak pernah (K12) | — | — | — |

### 9.2 Penjagaan di kode

- **Tanpa autoplay untuk butir.** `engines/skenario.js` membuat `narrationPlayer(node, { attemptId, shared: true })` tanpa `autoplay`, seperti `engines/cari.js` (baris 124–150), yang sekarang juga tidak pernah memutar petunjuk sendiri. Berpindah skenario atau memilih pilihan memanggil `pauseNarration()`.
- **Tercatat.** Setiap putaran situasi, petunjuk, dan instruksi mengirim `play`/`replay`/`pause`/`complete` ke `/api/audio-events` dengan `attempt_id`, jadi masuk `audio_usage_events` dan `challenge_attempts.audio_use_count`. Konteksnya dibaca dari `audio_assets.context_code`; tidak perlu event baru.
- **D1 tanpa suara.** `ChallengeService::buildPayload()` tidak mengirim `audio` untuk butir `fill_blank_bank` dan `verdict_card`, dan `passagesFor()` (baris 1411 dan seterusnya) tidak mengirim audio bacaan, walaupun kolomnya terisi. `NarrationImporter` dan panel media menolak memasang rekaman ke butir dan bacaan D1 dengan pesan panel "Bacaan dan pernyataan tidak dibacakan."
- **`item_narration_before`** (Analitik 7) diturunkan lapis data turunan dari `audio_usage_events` (butir lewat `audio_assets` → `challenge_items.audio_prompt_*`) dan waktu `item_tries`; sistem tidak menghitungnya.

### 9.3 Impor

`NarrationImporter` mengenali awalan baru (12 no. 4):

| Awalan | Sumber transkrip | Kolom tujuan | Tokoh |
| --- | --- | --- | --- |
| `layar-{wilayah}-NN` | `dialogues` konteks `scenario_open` | `dialogues` audio (seperti `dialog-`) | Jaka, Mbah Kedu |
| `skenario-{kode butir}` | `challenge_items.prompt_*` butir `scenario_choice` | `audio_prompt_id`/`audio_prompt_en_id` | Narator |
| `unggahan-{kode butir}` | `prompt_*` butir petunjuk *Periksa Unggahan* | `audio_prompt_id`/`audio_prompt_en_id` | Mbah Kedu |
| `instruksi-{tantangan}` | `challenge_nodes.instruction_*` | `audio_instruction_id`/`audio_instruction_en_id` | Narator |

- `PREFIXES` (baris 76–85) mendapat `'scenario_open' => 'layar'`.
- Berkas butir memakai **kode butir**, bukan nomor urut, supaya variasi a dan b tidak tertukar (12).
- Nomor `petunjuk-{tantangan}-NN` dihitung hanya dari butir Temukan Benda (`x01`–`x04`), bukan semua butir `find_object` di node. Kalau tidak, rekaman Mbah Kedu bisa terpasang ke butir *Periksa Unggahan*. Pemisahan babak milik bagian 5; importer membaca penanda babaknya (`config_json.round`).

### 9.4 Arahan suara

Saat menyetujui rekaman `skenario-*` dan `unggahan-*` di panel Narasi, pendengar mencentang "Nada datar dan sama untuk setiap situasi" sebelum *Setujui* aktif. Kotak centang ini hanya untuk konteks `scenario_situation` dan `post_clue`, disimpan sebagai bagian aksi audit `narration_approved` (tanpa kolom baru), dan isinya mengikuti [12 → Arahan suara](12_CERITA_PENGHUBUNG_DAN_NARASI.md#arahan-suara-untuk-situasi-dan-petunjuk-yang-dinilai).

---

## 10. Data untuk kamus dan unduhan

| Data | Tempat | Analitik |
| --- | --- | --- |
| Kunci tindakan, skor tindakan, kode DIG-\*, kunci alasan, kode R-\* per percobaan | `item_tries.action_key`, `action_score`, `error_code` (bagian 5), `reason_key`, `reason_code` | 6, 9, 10 |
| Pilihan pertama | Percobaan `try_no = 1` (`first_action_key`, `first_action_score`, `first_action_code`, `first_reason_code` di lapis turunan) | 10 |
| `action_reason_pattern` | Diturunkan bagian 9 dari baris pertama, aturan [11 → Analitik 10](11_KAMUS_DATA.md#analitik-10-pilihan-dan-alasan-di-skenario-digital) | 10 |
| Urutan pilihan yang tampil | `item_tries.answer_json.order` (`actions`, `reasons`: daftar kunci) | Pemeriksaan posisi |
| Saat pilihan tindakan dan alasan tampil | `item_tries.ready_ms`, `reason_ready_ms` | 5, 6 (`decision_ms`, tebakan cepat) |
| Skenario ditutup setelah kesempatan ketiga | Event `scenario_failed` | 1 jenis (a) |
| Putaran situasi dan instruksi | `audio_usage_events` + `audio_assets.context_code` | 7 |

- **Lembar `percobaan`** di unduhan `ketua` (29 §7.3) mendapat kolom `action_key`, `action_score`, `reason_code`, `reason_ready_ms`. `reason_key` tidak diekspor (kodenya sudah cukup). Kolom ini kosong untuk engine lain.
- **Lembar `bantuan`** (29 §5.4) mendapat kolom `audio_context` dari `audio_assets.context_code`, supaya `challenge_instruction`, `scenario_situation`, `post_clue`, dan `hunt_clue` terbedakan.
- **Kunci jawaban** di unduhan `ketua` (29 §7.5) memuat skor dan kode setiap pilihan skenario dan kode setiap alasan.
- Lembar *Kamus Data* (`ResearchDictionary`) mendapat entri kolom baru dengan status `baru` untuk `kamus-1.1`.

---

## 11. Skema database

Satu migrasi, `2026-01-01-004000_AddScenarioEngineColumns` (nomor sementara, K14). Tidak menghapus data.

| Tabel | Perubahan |
| --- | --- |
| `item_tries` | Kolom baru `action_key` VARCHAR(20) null, `action_score` TINYINT UNSIGNED null, `reason_key` VARCHAR(20) null, `reason_code` VARCHAR(20) null, `reason_ready_ms` INT UNSIGNED null. Indeks `(reason_code)` |
| `challenge_nodes` | Kolom baru `audio_instruction_id`, `audio_instruction_en_id` (rujukan `audio_assets`, `ON DELETE SET NULL`), seperti `audio_intro_*` di `2026-01-01-001100_CreateChallengeNodes` baris 30–31, 49–50 |

Yang **tidak** perlu kolom baru:
- `challenge_nodes.engine_type` VARCHAR(20) dan `challenge_items.interaction_type` VARCHAR(40) sudah menampung `skenario` dan `scenario_choice`.
- `dialogues.context_code` dan `audio_assets.context_code` (VARCHAR(80)) menampung `scenario_open`, `challenge_instruction`, `scenario_situation`, `post_clue`.
- `item_tries.ready_ms` dibuat bagian 3 (migrasi `003600`); `item_tries.error_code` dibuat bagian 5. Bila salah satu belum digabung saat migrasi ini ditulis, migrasi ini **tidak** membuatnya; tes menunggu bagian itu.

`RetentionService` tidak berubah: kolom baru ikut terhapus bersama barisnya. `MediaUsage` (`app/Libraries/MediaUsage.php:208-215`) mendapat `audio_instruction_id`/`audio_instruction_en_id` supaya rekaman instruksi tidak terhitung "tidak dipakai".

---

## 12. Rute

| Metode dan jalur | Target | Catatan |
| --- | --- | --- |
| `GET /penghubung/(:segment)` | `DialogueController::bridge/$1` | Baru; cerita penghubung wilayah |
| `POST /api/attempts/(:num)/responses` | `ChallengeApiController::respond/$1` (tetap, `Routes.php:89`) | Menerima `answer: {action, reason}` dan `ready_ms`, `reason_ready_ms`, `first_input_ms`, `last_input_ms` untuk butir `scenario_choice` |
| `POST /api/attempts/(:num)/check` | Tetap | Menolak engine `skenario` (§5.5) |

Tidak ada rute panel baru: butir skenario disunting di halaman butir yang ada (`/admin/konten/…`), rekaman di halaman Narasi yang ada. Hak aksesnya kemampuan bagian 1 (`content.view`, `content.edit`).

---

## 13. Teks antarmuka

Teks siswa di `app/Language/id/Game.php` dan `Js.php`, dengan pasangan di `en/`. Teks panel di `Admin.php` (Indonesia saja). Yang bertanda *draf* belum ada di dokumen sumber dan menunggu tinjauan ahli media bersama mock-up.

| Kunci | id | en |
| --- | --- | --- |
| `Game.engine_skenario` | Skenario Digital | Digital Scenarios |
| `Js.scenarioListen` | Dengarkan | Listen |
| `Js.scenarioListening` | Sedang dibacakan… | Reading aloud… |
| `Js.scenarioStepAction` | 1 Tindakan | 1 Action |
| `Js.scenarioStepReason` | 2 Alasan | 2 Reason |
| `Js.scenarioToReason` | Lanjut ke alasan | Go to reason |
| `Js.scenarioYourChoice` | Pilihanmu: {0} | Your choice: {0} |
| `Js.scenarioReasonQuestion` | Yang paling penting kupikirkan di sini adalah … | The most important thing I'm thinking about here is … |
| `Js.scenarioSend` | Kirim jawaban | Send answer |
| `Js.scenarioTriesLeft` (*draf*) | Kamu masih punya {0} kesempatan. Pilih lagi tindakan dan alasanmu. | You still have {0} tries. Choose your action and reason again. |
| `Js.scenarioRetry` | Coba lagi | Try again |
| `Js.scenarioSafeAction` (*draf*) | Ini tindakan yang aman | This is the safe action |
| `Js.scenarioClosed` (*draf*) | Kesempatanmu untuk cerita ini sudah habis. Ayo lanjut ke cerita berikutnya. | You've used all your tries for this story. Let's go on to the next one. |
| `Js.scenarioNext` | Lanjut ke cerita berikutnya | Go to the next story |
| `Js.scenarioProgress` | Cerita {0} dari {1} | Story {0} of {1} |
| `Js.instructionListen` | Dengarkan instruksi | Listen to the instructions |
| `Game.watchBridgeStory` | Lihat cerita penghubung | Watch the linking story |
| `Admin.narration_tone_check` | Nada datar dan sama untuk setiap situasi | — |
| `Admin.narration_d1_refused` | Bacaan dan pernyataan tidak dibacakan. | — |
| `Admin.event_scenario_failed` | Skenario ditutup setelah kesempatan habis | — |
| `Admin.engine_skenario` | Skenario Digital | — |

Teks yang menyebut jumlah serpihan ("12 serpihan", "dua belas") dicari di `app/Language/*`, `story.php`, dan naskah aktif saat implementasi, lalu diganti angka dari data (`{0}`) atau kalimat tanpa angka.

---

## 14. Titik sambung ke perubahan lain

| Perubahan | Yang disediakan bagian ini | Yang dirancang di sana |
| --- | --- | --- |
| Bagian 5 (`31`): no. 1, 3, 4, 8, 17, 20 | Bentuk butir `scenario_choice` di database (§5.1), engine dan payload | Sheet `scenarios` dan `scenario_options`, impor, `BankRules::ENGINES` (lima tantangan), `DifficultyRules` (3/4/4 tindakan, 3/4/5 alasan), kolom `item_tries.error_code`, variasi dari buklet, babak Cari Objek (`config_json.round`), label AKM dan dimensi |
| Bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)) | Gerbang `scenarioGate()` memakai kemajuan per siswa | `participant_progress`, `ChallengeAttemptModel::completedForParticipant()`, wilayah per pertemuan |
| Bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)) | `reason_ready_ms`, `scenario_failed`, kolom lembar `percobaan` dan `bantuan` | `item_tries.ready_ms`, `EventCatalog`, `dialogue_completed`, lembar unduhan, kunci jawaban |
| Bagian 7 (`33`): no. 5, 16, 19 | Engine tanpa jam (memakai sakelar jam bagian 7) | Jam disembunyikan, pengerjaan ulang setelah kelima tantangan tuntas, waktu aktif dari selang narasi |
| Bagian 9 (`35`): no. 9 | Data mentah §10 | `first_action_*`, `action_reason_pattern`, `skipped_failed_count`, `instruction_replays`, `item_narration_before`, `decision_ms` |
| Bagian 8 (`34`): no. 21, 22 | — | Tes D2 di modul tes terpisah dari Skenario Digital; tidak ada kode bersama |

---

## 15. Pengujian dan kriteria selesai

### 15.1 Tes baru

| Tes | Yang diperiksa |
| --- | --- |
| `ScenarioEngineTest` | Pilih tindakan skor 2 → skenario tertutup benar, `first_pass_correct` 1; skor 1 lalu 2 → benar di percobaan kedua; tiga kali skor 0/1 → tertutup tidak benar, `scenario_failed` tertulis; kiriman keempat → `CLUE_CLOSED`; alasan kosong → `INCOMPLETE`; ref asing → 400 |
| `ScenarioPayloadTest` | Payload tidak memuat `score`, `error_code`, `reason_code`, umpan balik, atau kunci pilihan mentah sebelum skenario tertutup; urutan sama setelah muat ulang dan di ketiga kesempatan; berbeda antarpengerjaan |
| `ScenarioTriesTest` | `item_tries` menyimpan `action_key`, `action_score`, `reason_key`, `reason_code`, `ready_ms`, `reason_ready_ms`, urutan di `answer_json`; nilai waktu di luar batas atau lebih besar dari `last_input_ms` disimpan kosong |
| `ScenarioCloseTest` | Pengerjaan tertutup `solved` bila keempat skenario benar, `tries_exhausted` bila ada yang tidak; bintang mengikuti `bintang-1`; `/check` menolak engine `skenario` |
| `ScenarioRaceTest` | Kiriman basi (entitas lama, `try_count` lama) kalah lewat `updateOrStale()` dan dijawab dari keadaan terbaru; tidak ada dua `item_tries` bernomor sama (pola `Factories::injectMock` seperti tes balapan lain) |
| `ScenarioBridgeTest` | Tantangan kelima pertama kali → dialihkan ke `/penghubung/{wilayah}`; setelah ditonton → kartu misi; setelah pengerjaan pertama ada, juga di sesi game lain → tidak dialihkan lagi |
| `FifteenShardsTest` | Bank dengan lima node per wilayah: `shards_total` 15; `/tuntas/{wilayah}` terbuka setelah tantangan kelima, bukan keempat; `/penutup` setelah 15 |
| `InstructionNarrationTest` | `instruksi-{tantangan}` diimpor ke `audio_instruction_*` dengan konteks `challenge_instruction`; tombol ▶ tampil hanya bila rekaman disetujui; tanpa `autoplay` di HTML |
| `NarrationRulesTest` | Payload Isi Rumpang dan Benar atau Salah tanpa audio walaupun kolomnya terisi; importer menolak rekaman untuk butir D1; `skenario-*` dan `unggahan-*` terpasang ke butir yang benar menurut kode butir; `petunjuk-*` tidak terpasang ke butir *Periksa Unggahan* |

### 15.2 Tes yang ditulis ulang

- `TryLimitTest` dan `GradeFiveWorld`: dunia uji memuat node kelima bila bank bagian 5 sudah memuatnya; tes yang menghitung 4 node per wilayah atau 12 serpihan memakai jumlah dari data.
- `StoryDataTest`: konteks `scenario_open` dan 112 baris naskah.
- `NarrationImporterTest` (atau tes impor narasi yang ada): awalan baru.
- `SimulationTest`: simulator menyelesaikan tantangan kelima.
- `LanguageFilesTest`: kunci baru berpasangan.
- Tes `DialogueController::done()` yang mengandaikan wilayah tuntas setelah tantangan keempat.

### 15.3 Cek manual

1. `gelita:reset` di instalasi pengembangan dengan bank bagian 5; mainkan `tmg-5` sampai tuntas dengan satu skenario ditutup setelah tiga kesempatan. Periksa `item_tries`, `scenario_failed`, bintang, serpihan 5/15.
2. Muat ulang di tengah skenario: urutan pilihan sama, kesempatan tersisa benar.
3. Periksa sumber halaman dan respons jaringan: tidak ada skor, kode, atau kunci sebelum skenario tertutup.
4. Tekan ▶ situasi dan ▶ instruksi, lalu pindah skenario: narasi berhenti; `audio_usage_events` berisi id pengerjaan dan konteks yang benar. Pastikan tidak ada yang berbunyi sendiri di layar tantangan.
5. Tablet dan Chromebook di panggung 16:9: teks situasi dan lima alasan Wonosobo terbaca tanpa gulir panjang.
6. Cerita penghubung tampil sekali, lalu tidak lagi setelah keluar dan masuk di pertemuan lain.

### 15.4 Selesai bila

- Semua tes §15.1 dan §15.2 hijau dengan `vendor/bin/phpunit --no-coverage`.
- Cek manual §15.3 lolos di Chromium dan satu tablet.
- Tidak ada kunci, skor, atau kode skenario di payload atau HTML sebelum skenario tertutup.
- `main` tetap bisa dirilis: bank lama tanpa node kelima tetap jalan dengan 12 serpihan.
- Glosarium, `docs/audio.md`, naskah aktif, dan dokumen di §16 diperbarui di cabang yang sama.

---

## 16. Dokumen yang ikut diperbarui saat implementasi

| Dokumen | Yang diubah |
| --- | --- |
| `.claude/rules/glossary.md` | Baris baru §19 no. 1 |
| `docs/audio.md` | Kelompok rekaman baru dan jumlah ± 187 per bahasa |
| Naskah aktif (`docs/naskah-cerita.md`) dan `story.php` | `scenario_open`, judul bagian 5 dan 7, pemetaan `petunjuk-*` ke `x01`–`x04` |
| `docs/07` (fitur) dan `docs/01` (database) | Engine `skenario`, kolom baru, event `scenario_failed` |
| `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` | Keputusan dan penyimpangan saat implementasi |
| `docs/PROGRESS.md` | Baris cabang kode bagian 6 |

---

## 17. Pertanyaan terbuka

1. **Gambar situasi (24) dibuat atau tidak.** 08 mencatatnya opsional. Usulan AI: buat setelah validasi ahli media, karena isinya bisa berubah. Ditanyakan saat tim katalog siap.
2. **Kalimat bingkai bertanda *draf*** di §13 menunggu tinjauan ahli media (instrumen media v1.1, mock-up `mk-sd-04` dan `mk-sd-05`).
3. **Umpan balik skor 2.** Bank 08 menulis umpan balik untuk setiap tindakan, juga yang tepat; layar memakainya apa adanya. Bila ahli media meminta umpan balik tepat yang lebih pendek, cukup diubah di bank.
4. **Nomor migrasi `004000`** dinomori ulang saat digabung, bila bagian 5 atau 7 memakai blok yang sama.
5. **Keseimbangan posisi tindakan tepat.** Pengacakan per pengerjaan tidak memaksa posisi; bila uji coba menunjukkan tindakan tepat terlalu sering di satu posisi untuk sekelompok siswa, ditinjau ulang setelah KG3a.

---

## 18. Keputusan pengembang

Diambil pengembang dalam wawancara 2 November 2026 (`asal: pengembang`, `disetujui pengembang` untuk keempat keputusan; dokumen tetap `draf pengembang`).

1. **Kode engine tersimpan `skenario`** (K1), sejajar dengan `rumpang`, `boleh`, dan `cari`. Tidak dipilih: `scenario` (bahasa Inggris, berbeda dari kode engine lain) dan `pilih` (menamai tindakan, bukan isi).
2. **Narasi instruksi hanya lewat tombol ▶** (K10), tidak pernah otomatis, di kelima belas tantangan. Tidak dipilih: sekali otomatis saat layar dibuka ditambah ▶ (usulan AI), dan diputar di kartu misi.
3. **Tanpa sakelar studi** (K8): engine selalu ada, tantangan kelima muncul bila bank memuatnya, dan aturan narasi berlaku di luar studi juga. Tidak dipilih: di balik `field_mode`.
4. **Urutan pilihan diacak sekali per pengerjaan** (K7), tetap di ketiga kesempatan, dan urutannya disimpan. Tidak dipilih: diacak ulang setiap kesempatan.

---

## 19. Akibat ke dokumen lain

Untuk sesi koordinator berikutnya; dokumen lain tidak diubah di cabang ini.

1. **Glosarium** (`.claude/rules/glossary.md`), saat implementasi: *Skenario Digital* → engine `skenario`, butir `scenario_choice`; *tindakan*, *alasan* → `action`, `reason` (`action_key`, `reason_code`); *cerita penghubung* → `scenario_open`, rute `/penghubung/{wilayah}`; *narasi instruksi* → `challenge_instruction`, `audio_instruction_*`; *situasi* → `scenario_situation`; *petunjuk Periksa Unggahan* (rekaman) → `post_clue`; event `scenario_failed`.
2. **[01](01_REVISI_10_ANALITIK.md) → Catatan perubahan sistem:** baris no. 2, 6, dan 18 merujuk dokumen ini; no. 6 ditambah "hanya lewat tombol ▶".
3. **[11](11_KAMUS_DATA.md), untuk `kamus-1.1`:** `instruction_replays` = semua putaran `challenge_instruction` (tidak ada putaran otomatis); event setara `clue_failed` bernama `scenario_failed`; kolom `reason_ready_ms`; `first_action_key` memakai kunci tetap bank (`t1`…); urutan pilihan di `answer_json.order`.
4. **[12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) → Yang dibacakan:** baris instruksi menjadi "Ya, hanya lewat tombol ▶"; *Yang perlu disiapkan di tahap 3* no. 1–4 merujuk §7.2, §8.3, dan §9.3.
5. **[18](18_MOCKUP_LAYAR_BARU.md):** mock-up tantangan perlu tombol ▶ instruksi (layar `mk-sd-*`, `mk-pu-*`), bila ahli media menilainya; tidak mengubah keputusan yang sudah ada.
6. **[29](29_IMPLEMENTASI_DATA_PENELITIAN.md):** katalog event mendapat `scenario_failed` (riset, hanya server); §6.1 baris Skenario Digital merujuk §6.4 dokumen ini; lembar `percobaan` dan `bantuan` mendapat kolom §10.
7. **`31` (bagian 5):** butir `scenario_choice` mengikuti bentuk §5.1 (`config_json.actions`/`reasons` dengan kunci tetap; skor, kode, umpan balik, dan penjelasan di `answer_key_json`); penanda babak Cari Objek di `config_json.round` dibaca importer narasi.
8. **`33` (bagian 7):** sakelar jam berlaku juga untuk engine `skenario`; aturan pengerjaan ulang berlaku juga untuk tantangan kelima.
9. **`35` (bagian 9):** turunan `action_reason_pattern`, `skipped_failed_count` dari `scenario_failed`, `instruction_replays`, `item_narration_before` dari §10.
10. **[06](06_KISI_KISI_BANK_SOAL.md) → Yang perlu disiapkan sistem nanti** no. 1: "pilihan diacak per pengerjaan" diperjelas "sekali per pengerjaan, tetap di ketiga kesempatan".
