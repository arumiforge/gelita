<?php

namespace App\Libraries;

/**
 * Owner of every event type (research revision 10, part 3:
 * docs/revisi-10-analitik/29_IMPLEMENTASI_DATA_PENELITIAN.md §4, K7).
 * The only list of event types: EventService (what is accepted, what only
 * the server writes), RawDataService and the export (`navigasi`, `selesai`,
 * `event` sheets), and the admin tech log page read it.
 *
 * - RESEARCH: answers the analytics of 10; goes to the `ketua` download.
 * - ADMIN: keeps the system running; only on the admin tech log page.
 * - REMOVED: no longer written; a queued client event of these types is
 *   refused as `event_type_unknown`. Old rows were changed or deleted by
 *   migration 2026-01-01-003600.
 *
 * A later part adds its event in RESEARCH (and SERVER_ONLY, NAVIGATION or
 * COMPLETION when it belongs there); nothing else needs to change.
 */
final class EventCatalog
{
    public const RESEARCH = [
        // sessions
        'session_started', 'session_resumed', 'session_paused', 'session_abandoned', 'session_completed',
        // regions and story
        'level_opened', 'level_completed', 'intro_completed', 'dialogue_completed',
        // challenges
        'challenge_opened', 'challenge_checked', 'challenge_completed', 'challenge_abandoned',
        'challenge_tries_exhausted', 'clue_failed', 'answer_submitted', 'wrong_target_clicked',
        'empty_tap', 'passage_reopened',
        'scenario_failed',   // part 6 (32 §5.6): a scenario closed after its third try, server only
        'item_shown',   // owner, 9 Nov 2026 (35 §6.2): a Cari Objek clue or a scenario came on screen; exact time per item
        // help and narration
        'hint_opened', 'audio_play', 'audio_autoplay', 'audio_replay', 'audio_completed',
        // screens and reading
        'screen_viewed', 'overlay_opened', 'library_opened', 'library_page_viewed', 'reading_progress',
        'page_hidden', 'page_visible',
        'input_idle',   // part 7 (33 §5.2, K4): one per input pause of 20 s or more, sent by the client
        // other
        'locale_changed', 'feedback_submitted',
    ];

    public const ADMIN = ['password_changed', 'viewport_changed', 'package_changed', 'events_dropped'];

    /** Removed types => what replaces them (null = nothing). */
    public const REMOVED = [
        'answer_selected'   => null,
        'audio_pause'       => null,
        'dialogue_advanced' => 'dialogue_completed',
        'answer_changed'    => 'answer_submitted',
    ];

    /** Types the client may never send (data that must not be forged). */
    public const SERVER_ONLY = ['screen_viewed', 'challenge_tries_exhausted', 'clue_failed', 'events_dropped', 'scenario_failed'];

    /** Research events of the `navigasi` sheet. */
    public const NAVIGATION = [
        'screen_viewed', 'overlay_opened', 'dialogue_completed', 'passage_reopened', 'library_opened',
        'library_page_viewed', 'locale_changed', 'page_hidden', 'page_visible', 'reading_progress',
        'input_idle',   // part 7 (33 §5.2): read with page_hidden/page_visible for active time (Analitik 5)
    ];

    /** Research events of the `selesai` sheet. */
    public const COMPLETION = [
        'challenge_completed', 'challenge_abandoned', 'challenge_tries_exhausted', 'clue_failed', 'scenario_failed',
        'level_completed', 'session_completed', 'intro_completed', 'feedback_submitted',
    ];

    /** @return list<string> every type EventService writes (RESEARCH + ADMIN) */
    public static function accepted(): array
    {
        return [...self::RESEARCH, ...self::ADMIN];
    }

    public static function isResearch(string $type): bool
    {
        return in_array($type, self::RESEARCH, true);
    }

    public static function isAdmin(string $type): bool
    {
        return in_array($type, self::ADMIN, true);
    }

    public static function isServerOnly(string $type): bool
    {
        return in_array($type, self::SERVER_ONLY, true);
    }
}
