# Asynchronous logging plan

*(New, English.)* Recommendation document: which logs must reach the database synchronously, which can be sent or written later, and how to do that without losing research data. Status: **approved plan; owner decisions recorded 17 October 2026 (§10). Phases 1 and 2 are implemented (PR #123 browser, PR #122 server); next is the §7 load measurement and the Phase 3 decision.** Written 17 October 2026 against `main` at the Draft 2 / voice-over merge (PR #118).

Code references in §2–§4 describe the state before Phases 1 and 2; the decision log (`docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`, sections of 17 October 2026) records what was built and any deviations.

---

## 1. Summary

1. **Most of the load is request overhead, not the log write itself.** One telemetry batch (`POST /api/events`) runs about 11 database statements, and only one of them stores events (§3.2). The biggest win is therefore *fewer telemetry requests*, not a faster insert.
2. **Client telemetry is already asynchronous for the student** (queue in `core/events.js`, batch every 3 s, beacon on page exit, offline queue). What is still "synchronous" is the rhythm: a batch every 3 s per channel, a separate audio channel, a separate heartbeat every 60 s, and a duplicate re-send on every page load.
3. **Recommended order:**
   - **Phase 1 (client, low risk):** flush every 30 s instead of 3 s (owner decision), persist each event to the offline queue the moment it is created, send events and audio in one request, fold the heartbeat into the batch, merge the page-load re-send into the first batch (§5.1). Estimated effect: about 10× fewer telemetry requests at peak.
   - **Phase 2 (server, low risk):** release the PHP session lock at the start of telemetry endpoints, and write audio events as a batch instead of one transaction per event (§5.2).
   - **Phase 3 (server, optional):** a write-behind inbox (`event_inbox`) drained by a scheduled worker, only if measurements after Phase 1–2 still show the event insert as a bottleneck (§5.3).
4. **What stays synchronous:** everything that decides or records game state (check, submit, tries, stars, attempt open/close and the events written inside those transactions), session lifecycle, `audit_logs`, `device_logs`, and the client events the game reads back during play (`dialogue_advanced`, §4).
5. **Never sample or drop research events.** Every change keeps the existing guarantees: idempotency by `client_event_id`, `sequence_no` order, `clock_offset_ms` measured at receipt, and `events_dropped` reporting (§6).

---

## 2. What is logged today

| Stream | Written by | Path | When | Table(s) | Read back during play? |
|---|---|---|---|---|---|
| Client gameplay events (`answer_selected`, `reading_progress`, `overlay_opened`, `page_hidden`/`page_visible`, `viewport_changed`, `dialogue_advanced`, `level_opened`, `library_page_viewed`, `challenge_abandoned` via pagehide, …) | Browser, `core/events.js` `emit()` | `POST /api/events` → `EventApiController::ingest` → `EventService::ingest` | Batch every 3 s or at 20 events; beacon on hide/close | `game_event_logs` | **Yes**: `dialogue_advanced` (`MapController::heardRegionIntros`, "Belum didengar" badge) |
| Client audio telemetry (`play`, `autoplay`, `pause`, `replay`, `complete`) | Browser, `core/audio.js` → `emitAudio()` | `POST /api/audio-events` → `AudioApiController::ingest` → `EventService::recordAudio` per event | Same queue rules, separate channel | `audio_usage_events`, `game_event_logs`, `challenge_attempts.audio_use_count` | No |
| Server navigation (`screen_viewed`) | `ScreenViewFilter::after` | Every rendered game page (GET 200 HTML) | Synchronous, inside the page request | `game_event_logs` | No (research only) |
| Server game events (`challenge_opened`, `challenge_checked`, `challenge_tries_exhausted`, `wrong_target_clicked`, `clue_failed`, `hint_opened`, `challenge_completed`, `level_completed`, `challenge_abandoned`) | `ChallengeService` | Inside the transaction that changes the attempt | Synchronous | `game_event_logs` | Indirectly (same transaction as game state) |
| Session lifecycle (`session_started`/`resumed`/`paused`/`completed`/`abandoned`, `locale_changed`, `password_changed`, `package_changed`) | `SessionService`, `LoginController` | Login, logout, locale, password, package change | Synchronous | `game_event_logs` | No |
| Other server events (`intro_completed`, `library_opened`, `feedback_submitted`) | `GateController`, `LibraryController`, `ReflectionController` | Page/form request | Synchronous | `game_event_logs` | No |
| Heartbeat | `game/hud.js` every 60 s while visible | `POST /api/session/heartbeat` → `SessionService::heartbeat` | Synchronous | `game_sessions.last_active_at`, `duration_ms` | Yes (session status, idle rules) |
| Device profile | `SessionService` at register/login/resume | Login request | Synchronous | `device_logs` | No |
| Staff audit | Admin controllers, services | Admin actions | Synchronous | `audit_logs` | Yes (Tata kelola, security) |
| Application log | `log_message()` | Any request | Synchronous file append | `writable/logs/log-*.log` | No |

---

## 3. Where the server load comes from

### 3.1 Request rate per student (upper bound)

During active play the queue sends a batch whenever at least one event is waiting and 3 s have passed (`FLUSH_MS = 3000`). Per playing student:

| Source | Requests per minute |
|---|---|
| `/api/events` | up to 20 (one per 3 s while events keep coming) |
| `/api/audio-events` | up to 20 on narrated screens (separate queue, separate timer) |
| `/api/session/heartbeat` | 1 |
| Per page change | 1 beacon + 1 re-send of the same events on the next page (`resendQueued()` in `game.js`; the server answers `duplicate`) |

For three classes playing at once (90 students) the telemetry upper bound is roughly 90 × 21 ≈ 1,900 requests per minute (≈ 30 per second) outside narrated screens, before any page loads or challenge checks. `docs/08_DEPLOYMENT.md` sizes PHP-FPM at `pm.max_children = 20` (4 GB RAM), and the same pool also serves pages, checks, and exports. These are estimates from the code; §7 describes how to measure the real numbers.

### 3.2 Cost of one telemetry batch

One `POST /api/events` (verified against CodeIgniter 4.7 `DatabaseHandler`/`MySQLiHandler` and the app filters):

| Step | Statements |
|---|---|
| Session handler: `GET_LOCK`, read `ci_sessions`, `UPDATE ci_sessions` (timestamp), `RELEASE_LOCK` | 4 |
| `ApiSessionFilter` / `ParticipantCheck`: participant, game session | 2 |
| `GameContext::load`: session, participant, `session_progress` | 3 |
| `EventService::ingest`: `MAX(sequence_no)`, `INSERT IGNORE … VALUES (…), (…)` | 2 |
| Throttler (`rateLimited`) | file cache read/write |

About **11 statements per batch; one of them stores the events.** Sending 5 events in one request instead of 5 requests of 1 event saves about 40 statements.

### 3.3 Findings

- **F1 — 3-second flush.** `FLUSH_MS = 3000` (`core/events.js`) is the main driver of request count. Research data does not need to arrive within 3 s: nobody reads it live except the dashboard's "20 event terakhir" (`DashboardController::recentEvents`) and the map badge (F6).
- **F2 — Two channels.** Events and audio use separate queues and endpoints, so a narrated screen sends two requests where one would do.
- **F3 — Separate heartbeat.** A 60-second heartbeat runs even when an event batch just reached the server for the same session.
- **F4 — Re-send on every page load.** A forced flush (tab hidden or page closed) beacons the events *and* keeps a copy in the offline queue, because a beacon can be lost silently (`rencana-paket-aset.md` §7.3). The next page calls `resendQueued()` immediately, so nearly every navigation costs one extra request whose events are mostly duplicates. The safety copy is correct; sending it as a separate, immediate request is not necessary.
- **F5 — Session lock serialises a student's requests.** The database session handler holds a MySQL `GET_LOCK` per session ID until the request ends. A telemetry batch in flight makes the same student's `POST /api/attempts/{id}/check` wait. Telemetry endpoints never change the PHP session, so they can release the lock at once.
- **F6 — The game reads one client event back.** `MapController::heardRegionIntros()` reads `dialogue_advanced` rows (context `region_intro`) to decide the "Belum didengar" badge. The narrator flushes at the last slide and the curtain waits for `flush()` before navigating (`core/curtain.js`), so the row exists when the map renders. Any deferral must keep that guarantee (§4).
- **F7 — Audio is written one event at a time.** `AudioApiController::ingest` calls `EventService::recordAudio()` per event: an existence check, `MAX(play_index)`, two inserts, `MAX(sequence_no)`, and an attempt update, each event in its own transaction (about 7 statements per audio event).
- **F8 — Wide insert.** `game_event_logs` has 12 indexes (primary, two unique, nine secondary), plus the one MySQL adds for the `deleted_by` foreign key. Each row updates all of them. This only matters once request overhead (F1–F5) is gone; it is the reason Phase 3 exists.

---

## 4. Which logs may be asynchronous

A log may be sent late or written late when **all** of these hold:

1. it does not decide anything (right/wrong, stars, tries, lock/unlock, session status);
2. nothing in the game reads it back before the next batch would arrive;
3. losing the network for a while must not lose it (offline queue or server-side inbox keeps it);
4. its research meaning does not depend on *when* the server stored it (order comes from `sequence_no`, time from `occurred_at` + `clock_offset_ms`).

| Log | Decision | Reason |
|---|---|---|
| Events written in `ChallengeService` transactions (`challenge_checked`, `challenge_tries_exhausted`, `clue_failed`, `wrong_target_clicked`, `challenge_opened`/`completed`/`abandoned`, `hint_opened`, `level_completed`) | **Synchronous, unchanged** | Same transaction as the game state they describe; server-only by rule (`EventService::SERVER_ONLY`) |
| Session lifecycle, `password_changed`, `package_changed`, `locale_changed` | **Synchronous, unchanged** | Rare (a few per session); tied to state changes |
| `audit_logs` | **Synchronous, unchanged** | Accountability for staff actions; rare |
| `device_logs` | **Synchronous** (parsing may move later, §5.3 R10) | One row per login |
| `dialogue_advanced` | **Client-batched as today, written directly** (never through a delayed inbox) | Read back by the map (F6) |
| Other client events (`answer_selected`, `reading_progress`, `overlay_opened`, `page_hidden`, `page_visible`, `viewport_changed`, `level_opened`, `library_page_viewed`, `challenge_abandoned` from pagehide) | **Asynchronous: larger, rarer batches** | Research only; already idempotent and offline-safe |
| Audio telemetry | **Asynchronous: larger batches, batch write** | Research only; `audio_use_count` is display data for research, not a game rule |
| Heartbeat | **Folded into the event batch** | `last_active_at` needs minute precision, not a request of its own |
| `screen_viewed`, `intro_completed`, `library_opened`, `feedback_submitted` | **Synchronous now; may move to the inbox in Phase 3** | Server-written for integrity; cheap single rows |
| Application log (`log_message`) | **Unchanged** | File append, low volume at production threshold 4 |

---

## 5. Recommendations

### 5.1 Phase 1 — client: fewer, larger requests

Files: `public/assets/js/core/events.js`, `core/storage.js`, `game/hud.js`, `game.js`, `app/Controllers/Api/EventApiController.php`, `app/Config/Gelita.php`, `js_config()`. No schema change.

- **R1 — Flush every 30 s, at 40 events, persist on push.**
  - `FLUSH_MS` 3,000 → 30,000 (owner decision, §10) and `FLUSH_AT` 20 → 40 (keep `BATCH_MAX = 50` = `$maxEventsPerBatch`). Make both values come from `CONFIG` (server config `Gelita::$eventFlush`) so they can be tuned per school without a deploy of JS.
  - Write each event to the offline queue (`Storage.queueEvents`) when it is created, and remove it after the server accepts its batch. Today an event lives only in memory until its batch is sent, so a longer interval would mean more loss on a browser crash. Persist-on-push makes the window irrelevant: whatever is not acknowledged is re-sent next time.
  - Keep every forced flush exactly as now: tab hidden, `pagehide`, and the curtain awaiting `flush()` before navigation. Those already send whatever is waiting, so the longer timer only affects events in the middle of a long screen.
- **R2 — One request for both channels.** `POST /api/events` accepts an optional `audio` array next to `events` (same envelope, same `client_sent_at`, separate `dropped` reports per channel). The client keeps two logical queues and sends them together. `POST /api/audio-events` stays for old tabs during the rollout.
- **R3 — Heartbeat inside the batch.** `EventApiController::ingest` calls `SessionService::heartbeat()` when the last one for this session is older than 50 s (cache key per session). `game/hud.js` sends its own heartbeat only when no batch went out in the last 60 s (an idle student who keeps the tab open).
- **R4 — Page-load re-send rides the first batch.** `resendQueued()` on page load moves the stored events into the queue and lets the normal timer (or the first `FLUSH_AT`) send them, instead of flushing at once. `online` events keep the immediate re-send.

Estimated effect at peak (per playing student): from up to 21–41 requests per minute to about 2–3 (two timed batches, plus one more when a burst reaches `FLUSH_AT`). Effect on research data: none (§6).

### 5.2 Phase 2 — server: cheaper telemetry endpoints

- **R5 — Release the session lock early.** At the start of `EventApiController::ingest`, `AudioApiController::ingest`, and `SessionApiController::heartbeat`, after `ApiSessionFilter` has loaded the context, call `session()->close()`. These endpoints never write `$_SESSION`. This removes F5: a check or submit request from the same student no longer waits for a telemetry batch. Add a test that the endpoints still work with a closed session.
- **R6 — Batch audio writes.** Rewrite `EventService::recordAudio()` as `recordAudioBatch()`: one `whereIn(client_event_id)` existence check, one grouped `MAX(play_index)` per asset, play indexes assigned in memory in batch order, one `insertBatch` per table, one `UPDATE challenge_attempts SET audio_use_count = audio_use_count + n` per attempt, all in one transaction. Keep the per-event `rejected` reasons. Writes that can race follow the `updateIf()` pattern from `ChallengeService` where a race is possible (two tabs).
- **R7 — Keep the rate limit.** `rateLimited('events')` stays at 120 requests per minute per session. After Phase 1 the normal rate is far below it; check that a re-send burst after a long offline period (up to 5,000 queued events = 100 batches) still drains within a few minutes, and let the combined endpoint (R2) count as one request.

### 5.3 Phase 3 — write-behind inbox (optional, decide after measuring)

Only worth doing if, after Phase 1–2, the database write itself (F8) is the bottleneck, e.g. many schools on one server. It changes the data path, so it needs its own branch and the event-integrity browser check.

- **R8 — `event_inbox` + drain worker.**
  - New table `event_inbox` (`id`, `session_id`, `channel`, `body_json`, `received_at` DATETIME(6), `sequence_hint`, `status` `pending|done|failed`, `error`, `attempts`), primary key and one index `(status, id)`. Cheap to insert: one row per batch, two indexes.
  - `EventApiController::ingest` validates the envelope shape, stores the batch with the server receipt time (so `clock_offset_ms` stays "measured at receipt") and answers `202 {queued: n}`. The client only needs success; it does not read `accepted`/`duplicate` today.
  - `php spark gelita:events:drain` moves pending rows into `game_event_logs` / `audio_usage_events` with the existing `EventService` validation and `insertBatch`, many sessions per statement, in `id` order. `status = failed` with the error after 3 attempts; failed rows stay for inspection, never deleted automatically.
  - Scheduling: Jalur L cron every minute running the command in a 55-second loop (`--loop=55`), Jalur W Task Scheduler every minute. Retention (`gelita:retention:run`) deletes `done` rows older than 7 days.
  - **Direct path stays** for `dialogue_advanced` (F6): the endpoint writes those synchronously as today and queues the rest. Alternative: drain the session's inbox before `heardRegionIntros()`. Direct writing is simpler.
  - **Drain on read** for staff views that must be current: `RawDataService`, `ExportService`, `SessionController` timeline and the dashboard's recent events call `drain(sessionId|all)` first (bounded time), so an export never misses events that already reached the server.
  - **Server event order.** `EventService::nextServerSequence()` takes `MAX(sequence_no)` from `game_event_logs`. With an inbox, client events may still be waiting, so a server event written in between could get a lower number than client events already received. Keep the cookie floor (`gelita_seq`, already the main guard) and also include the inbox's highest received `sequence_no` for the session. The browser check `tools/qa/event-integrity.mjs` (check 5: events sit between their `screen_viewed` rows) must stay green.
  - **Health.** Ringkasan shows the inbox lag (oldest pending `received_at`) and the failed count next to the existing warnings; `docs/08` gets the cron line and a troubleshooting row.
- **R9 — `screen_viewed` and other non-transactional server events through the inbox** (optional): saves one insert per page view. Only after R8 is stable.
- **R10 — Deferred device parsing** (optional): store the raw User-Agent and Client Hints at login and let the drain run `matomo/device-detector`. Helps when a whole class logs in at the same minute. Low priority: one parse per login.

**Not recommended:** a new queue server (Redis, RabbitMQ) or the `codeigniter4/queue` package. Both server paths (Ubuntu VPS and Windows Laragon, `docs/08`) would need a new always-running service, and the volume after Phase 1 does not justify it. A database inbox stays inside the existing backups and transactions.

---

## 6. Research data guardrails

Every phase keeps these, and their tests stay green:

- **No sampling, no throttling of research events.** Fewer requests, never fewer events.
- **Idempotency:** unique `(session_id, client_event_id)`; re-sends and drain retries answer or count as `duplicate`.
- **Order:** client `sequence_no` from `Storage.nextSequence` (never below `CONFIG.sequenceFloor`); server events above the `gelita_seq` cookie floor.
- **Time:** raw `occurred_at` untouched; `clock_offset_ms` computed from `client_sent_at` and the server receipt time (not the drain time).
- **No silent loss:** the offline queue limit (`QUEUE_MAX = 5000`), `events_dropped`, and the warning toasts stay. Persist-on-push (R1) means the queue holds events that were sent but not yet acknowledged; the 5,000 limit is still far above one batch window.
- **Server-only types** (`EventService::SERVER_ONLY`) stay rejected from the client in every endpoint, including the combined batch and the inbox drain.

Tests to add or extend: `EventService` batch audio (duplicates, play index order, attempt counter), combined `events` + `audio` envelope, heartbeat-in-batch throttle, session closed early, and (Phase 3) drain idempotency, failed rows, drain-on-read before export. Browser: `tools/qa/event-integrity.mjs` with the new flush interval and a crash mid-batch (close the page without `pagehide`).

---

## 7. Measuring before and after

Measure on a MariaDB/MySQL copy, never on production.

1. **Baseline.** A Playwright script (pattern of `tools/qa/event-integrity.mjs`) with N parallel students playing a scripted path (narration, two challenges, library), N = 30 and 90. Record:
   - telemetry requests per student per minute (`access.log` count by path);
   - PHP-FPM busy workers (`pm.status_path`), queue length;
   - MySQL `Com_insert`, `Com_select`, `Innodb_rows_inserted` per second;
   - p95 latency of `POST /api/attempts/{id}/check` (the request students feel).
2. **After Phase 1, then after Phase 2:** same script, same numbers.
3. **Decide on Phase 3** only if p95 check latency or FPM saturation at N = 90 still misses the target.

Targets (proposal): at 90 students, telemetry ≤ 5 requests per student per minute, check p95 ≤ 300 ms, FPM never at `max_children`.

---

## 8. Expected effect (estimates)

| | Today | After Phase 1 | After Phase 2 |
|---|---|---|---|
| Telemetry requests per playing student per minute (peak) | 21–41 | ≈ 2–3 | ≈ 2–3 |
| Statements per audio event | ≈ 7 | ≈ 7 | ≈ 1–2 (amortised) |
| A check waits for a telemetry batch (same student) | yes | yes, rarer | no |
| Extra requests per page change | 1–2 | 0–1 | 0–1 |
| Research events lost on a browser crash | events of the last ≤ 3 s | none (persist-on-push) | none |

---

## 9. Risks and trade-offs

- **Dashboard freshness.** "20 event terakhir" lags up to 30 s (Phase 1) or one drain cycle (Phase 3). Accepted by the owner; Phase 1 adds a short note under the list (e.g. "Diperbarui tiap ±30 detik", in `app/Language/id/Admin.php`), worded from the configured interval.
- **`localStorage` writes per event** (R1). Each `emit()` rewrites the channel's queue array. With ≤ 50 pending events this is small; if profiling shows jank on old tablets, append to a per-event key instead of rewriting the array.
- **Mixed versions during rollout.** Old tabs keep calling `/api/audio-events` and the heartbeat endpoint; both stay until a release later.
- **Phase 3 adds a moving part** (cron/Task Scheduler). A stopped drain means data waits, not data lost; the Ringkasan lag warning makes it visible.
- **SQLite tests do not show lock behaviour** (CLAUDE.md). R5 and R8 must be checked on MariaDB/MySQL.

---

## 10. Owner decisions (17 October 2026)

1. **Phases 1 and 2 approved, one branch each, developed in parallel** (owner, 17 October 2026, revising "Phase 1 first"). The split is by layer so the two sessions never edit the same file:
   - **Phase 1, branch `log-1-klien`: browser only.** `public/assets/js/**`, `tools/qa/event-integrity.mjs`, `docs/06_JAVASCRIPT.md`. R1 (30 s flush, persist-on-push), R2 client side (one request carrying `events` and `audio`), R3 client side (heartbeat only when no batch went out for 60 s), R4.
   - **Phase 2, branch `log-2-server`: PHP only.** R2 and R3 server side (`/api/events` accepts `audio`; heartbeat folded into the batch), R5 (session lock released early), R6 (batched audio writes), R7, `Gelita::$eventFlush` and its `js_config()` entry, the dashboard note, `docs/04_CONTROLLER_ROUTE.md`, `docs/08_DEPLOYMENT.md`.
   - **Contract between them** (both sessions build against it): `POST /api/events` takes `{events, audio?, client_sent_at, dropped?, dropped_id?, audio_dropped?, audio_dropped_id?}`, at most 50 entries in each array, and answers `{accepted, duplicate, rejected, audio?: {accepted, duplicate, rejected}, server_time}`; the `audio` key is present in the answer whenever the request carried an `audio` array. `js_config()` exposes `eventFlush: {intervalMs, maxEvents}`. `/api/audio-events` and `/api/session/heartbeat` keep working.
   - **Merge order: Phase 2 first.** Phase 1 still works against a server without Phase 2: when the answer has no `audio` key, the client keeps those audio events and sends them through `/api/audio-events`, and it falls back to 30 000 ms / 40 events when `CONFIG.eventFlush` is missing.
2. **Flush interval: 30 s** (the proposal was 15 s). It is server-configurable (`Gelita::$eventFlush`, R1), so it can be lowered later without a JS deploy. Forced sends on tab hide, page close, and before navigation stay as they are.
3. **Phase 3: decide after measuring.** Run the §7 load test after Phases 1 and 2; build the inbox only if the targets are still missed at 90 students.
4. **Dashboard lag accepted, with a note.** "20 event terakhir" may be up to one interval behind; Phase 1 adds a short note under the list (§9).
5. Targets in §7 (90 students, telemetry ≤ 5 requests per student per minute, check p95 ≤ 300 ms, FPM never at `max_children`): not decided separately; they stay the proposal and are used as the bar for decision 3 unless the owner changes them.
