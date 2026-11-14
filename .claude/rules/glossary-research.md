---
paths:
  - "app/{Commands,Config,Controllers,Database,Entities,Exceptions,Filters,Libraries,Models,Services,Validation}/**"
  - "tests/**"
  - "docs/revisi-10-analitik/**"
  - "docs/bank-soal/**"
---

# Glossary: research revision 10, asset packages, panel and data rights

Continues `glossary.md` (same columns, same rules). Add research, panel, and data terms here.

## Game (students)

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| babak (Cari Objek) | `round` | `challenge_items.round` 1 = Temukan Benda (variant `x`), 2 = Periksa Unggahan (`a`, `b`); since 3 Nov 2026 (31) |
| Periksa Unggahan | `post check`, `find_part` | item type `find_part`; tables `post_scenes`, `post_parts`; payload `round2`; UI "Babak 2: Periksa Unggahan" (`Game.huntRoundTwoTitle`) |
| bagian ketuk (unggahan) | `tap part`, `part_key` | `post_parts.part_key` (A1, C10, …), box x/y/w/h in percent of the 16:9 scene; `button.post-part` in `engines/cari.js` |
| kartu jangkar | `anchor card` | shared group `x` of Benar atau Salah (`BankRules::SHARED_GROUP`), passage `{node}-x`; UI "Bacaan kedua" (`Game.anchorCardTitle`) |
| buklet | `booklet` | B1–B4, `participant_seats.booklet` (part 2); `BookletRules`, `VariantPicker`, seam `BookletSource`; `challenge_attempts.variant_source` (`booklet`, `random`, `retake`), `booklet_at_open`; `BOOKLET_MISSING` |
| kode kesalahan | `error code` | `item_error_codes`, `challenge_items.error_code`, `item_tries.error_code` (server-derived, `ErrorCodeResolver`), `item_tries.manual_code` (L5); list in `ErrorCodes` (03) |
| beban permukaan | `surface load` | `challenge_items.surface_json` (`SurfaceLoad`: `gap_count`, `passage_words`, `tap_min_dp`, …), frozen at import |
| label pengembang / label ahli | `label source` | `akm_level_source`, `bloom_level_source` = `developer` or `expert`; workbook column `label_source` |
| Skenario Digital (data) | `scenario`, `scenario_choice` | item type `scenario_choice`, table `scenario_options` (actions with score, reasons); engine `skenario` is part 6's |
| Skenario Digital (tantangan kelima) | `skenario` engine | engine `skenario`, item `scenario_choice`, route `/tantangan/{wilayah}/5`; `engines/skenario.js`, `ChallengeService::submitScenario()` (revision 10, part 6: 32, since 8 Nov 2026) |
| tindakan, alasan (skenario) | `action`, `reason` | `item_tries.action_key`, `action_score` (2 aman, 1 sebagian, 0 berisiko), `reason_key`, `reason_code` |
| kesempatan per soal | `tries per item` | `ChallengeNode::triesPerItem()`: three tries per scenario, not per challenge |
| cerita penghubung | `bridge story` | context `scenario_open`, route `/penghubung/{wilayah}` (`DialogueController::bridge`), recordings `layar-{wilayah}-NN`, catalogue `bg.cerita-layar-{wilayah}-NN`; once per student and study (`scenarioGate()`) |
| narasi instruksi | `instruction narration` | context `challenge_instruction`, `challenge_nodes.audio_instruction_id`/`_en_id`, recordings `instruksi-{node}`; plays only through ▶ |
| situasi (skenario) | `situation` | context `scenario_situation`, recordings `skenario-{item}`; picture slot `item.skenario.{item}` (`CatalogImporter::MOCKUP_DIRS`, catalogue `mockup/gambar-mockup/skenario/`) |
| rekaman petunjuk Periksa Unggahan | `post clue recording` | context `post_clue`, recordings `unggahan-{item}` |
| skenario ditutup (kesempatan habis) | `scenario_failed` | server-only event, `EventCatalog::SERVER_ONLY` |
| soal tampil (petunjuk atau skenario) | `item shown`, `item_shown` | client research event when a Cari Objek clue or a Skenario Digital scenario comes on screen, payload `round`, `via` (`start`, `next`, `jump`, `round2`), `t_ms`; the derived item time counts only while the item was shown (owner, 9 Nov 2026; 35 §19 no. 16) |
| jeda masukan | `input pause`, `input_idle` | client research event for input pauses ≥ 20 s (`Config\Gelita::$inputIdleLogMs`), payload `idle_ms`, `started_at`, `end`, `screen`; `core/idle-tracker.js`, `core/activity.js` (revision 10, part 7: 33, since 9 Nov 2026) |
| potongan pengerjaan | `attempt segment` | `challenge_attempt_segments` (`segment_no`, `opened_at`, `left_at`, `left_via`); a first attempt left in one game session moves to the next one with a new segment (33 K3) |
| pengerjaan ulang | `retake` | `challenge_attempts.is_retake`; in research mode only after the region's five challenges are done (node status `done_locked`: done medal, stars and *Lihat hasil*, no *Ulangi*; `node_done()`, `node_status_class()`; `RETAKE_LOCKED`, `Game.retakeLater`; owner, 9 Nov 2026); stars still count (33 K5) |
| layar terlalu kecil | `small-screen gate` | `components/small-screen`, `game/screen-check.js`; research mode refuses a stage under `research_studies.min_stage_px` (960) at `/token` and `/daftar` step 0 (`stage_w`, audit `small_screen_refused`); outside it a closable warning |
| jam tersembunyi (mode penelitian) | `research clock` | no timer or `elapsed_ms` in the challenge frame while `field_mode` is on (33 §7) |
| modul tes | `instrument module` | route `/tes` (Bootstrap layout `layouts/instrument`, no game HUD), `InstrumentService`, `gelita:instrumen:impor`, content in `app/Database/Seeds/data/instrumen/{tes,angket,sikap}.php` (revision 10, part 8: 34, since 9 Nov 2026) |
| tes awal, tes akhir, angket | `pre-test`, `post-test`, `questionnaire` | `instrument_sittings.occasion` `pre`/`post`/`p5`, `kind` `test`/`questionnaire`/`risk`/`attitude`; forms A/B from `participant_seats.pre_form`/`post_form` |
| bagian tes | `test part` | `mb`, `ae`, `bd`; `instrument_parts` (`raw_score`, `answered_count`), answers `instrument_responses` |
| blok waktu, tambah waktu | `time block`, `extra minutes` | `instrument_blocks.block` `p1`/`p23`, hidden limit, `extra_minutes` via *Tambah waktu* on the field-session page |
| pertanyaan tambahan (butir risiko) | `risk items` | route `/tes/tambahan`, sitting kind `risk`, T11/T12 (`riskAsk*`, `riskThanks*`); gate `ConsentService::riskItemsAllowed()`; only aggregate in downloads (`risiko_gabungan`) |
| skala sikap | `attitude scale` | route `/refleksi/sikap` in Balai Refleksi at P5, sitting kind `attitude`, items skp-01–12 |
| pratinjau instrumen | `instrument preview` | `Config\Gelita::$instrumentPreview` (`gelita.instrumentPreview`): `uji.*` accounts try the module, sittings `is_preview = 1`, never in research data |

## People, data, and the panel

| UI (id) | Code (en) | Stored / notes |
|---|---|---|
| paket aset, mode paket | `asset package`, `package mode` | table `asset_packages`; codes `pkt-YYYY-MM-DD-NN`; `game_releases.asset_version`; commands `gelita:paket:*`; route `/api/paket/aktif` |
| Mode paket: Nyala / Mati | `package switch` | no column of its own; the active release's `asset_version` names a package (on) or is empty (off) |
| pratinjau paket | `package preview` | `?paket=` (staff only), session key `package_preview`, `AssetPackageService::previewCode()`/`servingCode()` |
| Izinkan mode daring untuk perangkat lab | `online_lab_allowed` | `writable/paket/pengaturan.json`; field of `/api/paket/aktif` |
| Bangun ulang paket / Terbitkan | `rebuild` / `publish` | routes `/admin/studi/rilis/paket/bangun`, `/admin/studi/rilis/paket/{code}/terbitkan` |
| paket terkunci (penelitian) | `study package lock` | `research_studies.package_code` |
| tanpa paket | no package | `asset_version = ''`; release code `tanpa-paket-YYYY-MM-DD-NN` |
| mode lengkap / daring / nonaktif | `package_mode` | stored `lengkap`, `daring`, `nonaktif` in `game_sessions.package_mode` |
| gambar gerak (tokoh) | `frame` | `char.{jaka|kedu}.{pose}.{1–24}`; route `/admin/media/{id}/unggah-gerak` |
| naskah rekaman (PDF untuk pengisi suara) | `voice-actor script` | `NarrationScriptPdf`; route `/admin/konten/narasi/naskah-pdf?bahasa=id\|en\|semua` |
| daftar rekaman (CSV) | `recording list` | `NarrationCatalog::recordingList()`; command `gelita:narration:list`; `ai-studio/elevenlabs-voice/lines.csv` with columns `kode_berkas`, `konteks`, `wilayah`, `urutan`, `tokoh`, `teks_id`, `teks_en`, `berkas_id`, `berkas_en` (`ada`/`belum`) |
| narasi cadangan (suara sintetis) | `backup narration` | ElevenLabs only, voice actors stay first (owner, 14 Nov 2026); installed with `produksi.json` so `audio_assets.production_method` = `tts` (else `own_recording`), `voice_profile` |
| Batalkan semua rekaman | `cancel all recordings` | `NarrationCatalog::cancelAll()`; route `POST /admin/konten/narasi/batalkan`; audit `narration_cancel_bulk` |
| mode penelitian | `field mode` | `research_studies.field_mode` (0 by default), with `meeting_plan` (`lima_pertemuan`, `satu_sesi`); `ResearchStudyModel::fieldModeStudy()`; revision 10, part 2 (28) |
| sesi lapangan, sesi susulan | `field session`, `make-up session` | `field_sessions` (code `S03-A-P2`, `-S`, `-S2`); `FieldSessionService`; route `/admin/sesi-lapangan` |
| token sesi | `session token` | `field_session_tokens`; routes `/token`, `/daftar/0`; screen `/sampai-jumpa` when closed; API `FIELD_SESSION_CLOSED` |
| kode sekolah penelitian | `research school code` | `schools.research_code` (`S03`, `U01`, `V01`) |
| rombel | `class group` | `participants.rombel`, `field_sessions.rombel` (one capital letter) |
| kode peserta (mode penelitian) | `participant code` | `S03-A07` from `participant_code_counters` (`ParticipantCodeService`); `GLT-000001` outside field mode; `UJI-01` for test accounts |
| kursi, buklet, bentuk tes | `seat`, `booklet`, `test form` | `participant_seats` (`booklet` `B1`–`B4`, `pre_form`/`post_form` `A`/`B`); `SeatRules`; route `/admin/sesi-lapangan/{id}/kursi` |
| kemajuan siswa | `participant progress` | `participant_progress` per student and study (replaced `session_progress`, migration `003500`) |
| Pemantauan | `monitoring panel` | route `/admin/pemantauan`, `GET /api/admin/monitoring`; capability `monitoring.view` (admin: numbers only) |
| akun uji | `test account` | `uji.*` accounts, command `gelita:uji:akun`, school `V01` |
| katalog event | `event catalog` | `App\Libraries\EventCatalog` (`RESEARCH`, `ADMIN`, `REMOVED`, `SERVER_ONLY`); `dialogue_completed` replaced `dialogue_advanced`, `answer_changed` merged into `answer_submitted` (revision 10, part 3: 29) |
| Log teknis | `tech log` | route `/admin/log-teknis`, `TechLogService`, capability `tech_logs.view` (admin) |
| Kamus Data (lembar unduhan) | `data dictionary` | `ResearchDictionary` (`kamus-1.1` since the developer approved it, 9 Nov 2026, `DRAFT` false; `kamus-1.1-usulan` while it was a draft after part 9; was `kamus-1.0`; thresholds re-set after KG3b come as `kamus-1.2`), `data_exports.dictionary_version` |
| riwayat unduhan | `download history` | audit `export_download`, `data_exports.participant_count` |
| lembar lapangan (diketik) | `field sheet` | `FieldSheetSpec` (`lembar-1.0`), `field_sheet_imports`, `field_sheet_rows`; route `/admin/sesi-lapangan/{id}/lembar`; command `gelita:lembar:templat` |
| ketikan kedua | `check entry` | `field_sheet_imports.purpose` = `check` (first typing `main`) |
| izin berkode per siswa | `consent register` | `participant_consents`; route `/admin/sesi-lapangan/{id}/izin`; download `/admin/ekspor/izin`; sheet `izin` in the main download (ketua only) (revision 10, part 4: 30) |
| status izin | `consent status` | stored `active`, `left`, `withdrawn_keep`, `withdrawn_delete` |
| akun nonaktif | `deactivated` | `participants.deactivated_at`; login and token refuse it (`Game.loginInactive`) |
| versi teks persetujuan | `assent version` | `participants.assent_version` (`t4-1`, `umum-1`) |
| permintaan data (orang tua) | `data request` | `data_deletion_requests.kind` = `all`, `risk_items`, `recording`; route `/admin/permintaan-data`; `DataRequestService` |
| berita acara | `deletion minutes` | `/admin/permintaan-data/{id}/berita-acara` (PDF) |
| penanda tindak lanjut | `risk follow-up flag` | `RiskFollowUpService`, rule `tl-1`, never stored; route `/admin/tindak-lanjut` |
| salinan data satu anak | `participant copy` | `ParticipantCopyService`, route `/admin/peserta/salinan` (ketua) |
| pembetulan data pendaftaran | `registration correction` | `participants.corrected_at` |
| ganti nama dengan kode (penutupan studi) | `identity replacement` | `StudyClosureService`, confirm word `GANTI` |
| cadangan diganti | `backup replacement` | `deploy/linux/backup.sh --ganti`, `deploy/windows/backup.ps1 -Ganti` |
| IP berhash di sesi peramban | `hashed IP session` | `HashedIpSessionHandler`, switch `gelita.researchPrivacy` (off by default) |
| pengaturan pengambilan data (studi) | `study settings` | `POST /admin/studi/{id}/pengaturan` → `StudyController::updateSettings`; `research_studies.idle_threshold_ms` (180000), `rapid_guess_ms` (3000), `test_rapid_guess_ms` (3000), `min_stage_px` (960); audit `study_settings_changed`; refused once `thresholds_locked_at` is set (part 9) |
| batas waktu modul tes (studi) | `instrument settings` | `research_studies.instrument_settings` JSON (null = `Config\Gelita::$instrumentDefaults`), `POST /admin/studi/{id}/instrumen` |
| Mulai tes awal / akhir | `test start` | `field_sessions.test_started_at`, `test_started_by`; `POST /admin/sesi-lapangan/{id}/mulai-tes` (`FieldTestService`), audit `field_test_started`; filter `instrumentRedirect` closes the challenges meanwhile |
| status tes | `test status` | `belum`, `sedang`, `selesai`, `susulan`, `hilang` (`InstrumentService::testStatus()`); field-session page, monitoring, printed L6 *tes* sheet in the template (never uploaded) |
| lapis data turunan | `derived data layer` | tables `derived_participants`, `derived_challenges`, `derived_attempts`, `derived_items`, `derived_tries`, `derived_test_responses`, `derived_field_sessions`, `derived_observations` (cascade from the run and from `participants`); pure calculators in `App\Libraries\Derived\`, `DerivedDataService` (`service('derivedData')`); download sheets `turunan_*` (`DerivedSheets`) (revision 10, part 9: 35, since 9 Nov 2026) |
| putaran hitung | `derived run` | `derived_runs` (thresholds, fingerprint, `dictionary_version`); current run `research_studies.derived_run_id`, `derived_running_since` (claimed by a conditional UPDATE); `data_exports.derived_run_id`; recomputed on download when stale |
| Hitung ulang | `recompute` | `POST /admin/studi/{id}/hitung-ulang` (`DerivedDataController`, `research.export`); command `gelita:turunan:hitung --studi=KODE [--cek]` |
| Kunci ambang | `threshold lock` | `research_studies.thresholds_locked_at`, `POST /admin/studi/{id}/ambang/kunci` (`studies.manage`, audit `study_thresholds_locked`); unlock only by `gelita:turunan:ambang --studi=KODE --buka --alasan=…` (audit `study_thresholds_unlocked`); locked at G4 |
| saat mundur | `withdrawal cutoff` | derived rows stop at the withdrawal moment (`consent_ok`, `withdrawn_on`); raw sheets carry `after_withdrawal`, computed at download time from `participant_consents` and `deactivated_at` (35 K4) |
