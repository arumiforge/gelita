<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use Config\Services;

/**
 * Data mentah kecil yang lengkap untuk uji RawDataService dan ekspor (Dok 3
 * §12.1), di atas skema ResetTables (SQLite grup `tests`):
 *
 * - dua sekolah; GLT-000001 (Ani, sekolah 1) dan GLT-000002 (Budi, sekolah 2),
 *   masing-masing satu sesi, progres, dan catatan perangkat;
 * - tantangan tmg-1 (Susun Gambar) dan tmg-3 (Benar atau Salah, variasi a
 *   berjudul "Candi Pringapus"), satu soal masing-masing;
 * - Ani: pengerjaan tmg-3 selesai (2 percobaan), petunjuk dibuka, satu
 *   suara diputar, kritik & saran; Budi: pengerjaan tmg-1 berjalan;
 * - event navigasi, penyelesaian, dan satu event yang ditandai hapus.
 */
trait RawDataWorld
{
    use ResetTables;

    private function createRawDataWorld(): BaseConnection
    {
        $db = $this->createResetTables();

        $this->insertRows($db, 'schools', [
            ['name' => 'SD Negeri Alpha', 'code' => '11111111'],
            ['name' => 'SD Negeri Beta', 'code' => '22222222'],
        ]);

        foreach (['temanggung' => 'mudah', 'magelang' => 'sedang', 'wonosobo' => 'sulit'] as $code => $difficulty) {
            $db->table('levels')->insert([
                'sequence' => $db->table('levels')->countAllResults() + 1, 'code' => $code, 'name_id' => ucfirst($code), 'name_en' => ucfirst($code),
                'difficulty' => $difficulty, 'focus_id' => 'Fokus', 'focus_en' => 'Focus',
            ]);
        }

        $this->insertRows($db, 'learning_indicators', [
            ['code' => 'budaya', 'name_id' => 'Budaya', 'name_en' => 'Culture'],
            ['code' => 'literasi', 'name_id' => 'Literasi', 'name_en' => 'Literacy'],
        ]);

        $this->insertRows($db, 'challenge_nodes', [
            ['level_id' => 1, 'sequence' => 1, 'engine_type' => 'puzzle', 'title_id' => 'Susun Candi', 'title_en' => 'Temple', 'indicator_id' => 1],
            [
                'level_id' => 1, 'sequence' => 3, 'engine_type' => 'boleh', 'title_id' => 'Benar atau Salah', 'title_en' => 'True or False', 'indicator_id' => 2,
                'config_json' => json_encode(['variants' => ['a' => ['title_id' => 'Candi Pringapus', 'title_en' => 'Pringapus Temple']]]),
            ],
        ]);

        $this->insertRows($db, 'media_assets', [
            ['asset_key' => 'challenge.item.tmg-1-a01', 'asset_type' => 'image', 'storage_path' => 'x.png', 'mime_type' => 'image/png'],
            ['asset_key' => 'audio.narasi.id.intro-01', 'asset_type' => 'audio', 'storage_path' => 'x.mp3', 'mime_type' => 'audio/mpeg'],
        ]);
        $db->table('audio_assets')->insert([
            'media_asset_id' => 2, 'locale' => 'id', 'context_code' => 'intro', 'transcript' => 'Pada suatu hari…', 'approval_status' => 'approved',
        ]);
        $db->table('reading_passages')->insert(['level_id' => 1, 'passage_key' => 'tmg-3-a', 'body_id' => 'Bacaan', 'body_en' => 'Reading']);

        $this->insertRows($db, 'challenge_items', [
            [
                'challenge_node_id' => 1, 'item_key' => 'tmg-1-a01', 'variant_group' => 'a', 'interaction_type' => 'puzzle_arrange',
                'prompt_id' => 'Candi Borobudur', 'answer_key_json' => '{"order":[0,1,2,3]}', 'config_json' => '{"grid":2,"shape":"square"}',
                'media_asset_id' => 1, 'scorable' => 1, 'is_active' => 1,
            ],
            [
                'challenge_node_id' => 2, 'item_key' => 'tmg-3-a01', 'variant_group' => 'a', 'interaction_type' => 'verdict_card',
                'prompt_id' => 'Candi Pringapus ada di Temanggung.', 'answer_key_json' => '{"verdict":"benar"}',
                'config_json' => '{"skill":"informasi_tersurat"}', 'bloom_level' => 'C1', 'passage_id' => 1, 'scorable' => 1, 'is_active' => 1,
            ],
        ]);

        $db->table('research_studies')->insert(['code' => 'STUDI-UJI', 'name' => 'Studi uji', 'status' => 'active']);
        $db->table('game_releases')->insert([
            'release_code' => 'GELITA-UJI', 'app_version' => '2.0.0', 'content_version' => 'kelas5-1', 'asset_version' => '1', 'scoring_version' => 'bintang-1', 'is_active' => 1,
        ]);

        $this->insertRows($db, 'participants', [
            [
                'participant_code' => 'GLT-000001', 'username' => 'ani.5', 'password_hash' => 'RAHASIA-HASH', 'display_name' => 'Ani Lestari',
                'age' => 10, 'gender' => 'perempuan', 'class_level' => '5', 'school_id' => 1, 'school_name_snapshot' => 'SD Negeri Alpha',
                'province_code' => '33', 'province_name_snapshot' => 'Jawa Tengah', 'pw_first_submit_criteria' => 4, 'pw_weak_submit_count' => 1,
                'created_at' => '2026-10-01 08:00:00', 'assented_at' => '2026-10-01 08:00:05',
            ],
            [
                'participant_code' => 'GLT-000002', 'username' => 'budi.5', 'password_hash' => 'RAHASIA-HASH', 'display_name' => 'Budi Santoso',
                'age' => 11, 'gender' => 'laki-laki', 'class_level' => '5', 'school_id' => 2, 'school_name_snapshot' => 'SD Negeri Beta',
                'province_code' => '33', 'province_name_snapshot' => 'Jawa Tengah', 'pw_first_submit_criteria' => 5, 'pw_weak_submit_count' => 0,
                'created_at' => '2026-10-02 09:00:00',
            ],
        ]);

        $this->insertRows($db, 'game_sessions', [
            [
                'session_code' => 'S-ANI', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'completed',
                'started_at' => '2026-10-01 08:01:00', 'last_active_at' => '2026-10-01 08:40:00', 'duration_ms' => 2340000,
                'device_type' => 'tablet', 'is_touch' => 1, 'screen_size' => '1280x800',
            ],
            [
                'session_code' => 'S-BUDI', 'participant_id' => 2, 'study_id' => 1, 'release_id' => 1, 'locale' => 'en', 'status' => 'active',
                'started_at' => '2026-10-02 09:01:00', 'last_active_at' => '2026-10-02 09:10:00', 'duration_ms' => 540000,
                'device_type' => 'desktop', 'is_touch' => 0,
            ],
        ]);

        $this->insertRows($db, 'session_progress', [
            ['session_id' => 1, 'current_level_id' => 1, 'current_node_id' => 2, 'completed_nodes' => 1, 'total_stars' => 2],
            ['session_id' => 2, 'current_level_id' => 1, 'current_node_id' => 1, 'completed_nodes' => 0, 'total_stars' => 0],
        ]);

        $this->insertRows($db, 'device_logs', [
            ['session_id' => 1, 'participant_id' => 1, 'via' => 'register', 'device_type' => 'tablet', 'client_hints_json' => '{"headers":{}}', 'user_agent' => 'Mozilla/5.0', 'created_at' => '2026-10-01 08:01:00'],
            ['session_id' => 2, 'participant_id' => 2, 'via' => 'login', 'device_type' => 'desktop', 'created_at' => '2026-10-02 09:01:00'],
        ]);

        $this->insertRows($db, 'challenge_attempts', [
            [
                'session_id' => 1, 'challenge_node_id' => 2, 'variant_group' => 'a', 'status' => 'completed', 'completion_reason' => 'solved',
                'selected_item_ids_json' => '[2]', 'scorable_items' => 1, 'first_pass_correct' => 0, 'final_correct' => 1, 'check_count' => 2,
                'hint_count' => 1, 'stars' => 2, 'started_at' => '2026-10-01 08:10:00.250', 'completed_at' => '2026-10-01 08:12:00.750', 'duration_ms' => 120500,
            ],
            [
                'session_id' => 2, 'challenge_node_id' => 1, 'variant_group' => 'a', 'status' => 'in_progress',
                'selected_item_ids_json' => '[1]', 'scorable_items' => 1, 'started_at' => '2026-10-02 09:05:00',
            ],
        ]);

        $this->insertRows($db, 'item_responses', [
            [
                'challenge_attempt_id' => 1, 'challenge_item_id' => 2, 'status' => 'answered', 'first_answer_json' => '{"verdict":"salah"}',
                'final_answer_json' => '{"verdict":"benar"}', 'first_pass_correct' => 0, 'is_correct' => 1, 'try_count' => 2, 'solved_on_try' => 2,
                'change_count' => 1, 'started_at' => '2026-10-01 08:10:00', 'answered_at' => '2026-10-01 08:12:00', 'duration_ms' => 9000,
            ],
            ['challenge_attempt_id' => 2, 'challenge_item_id' => 1, 'status' => 'pending', 'started_at' => '2026-10-02 09:05:00'],
        ]);

        $this->insertRows($db, 'item_tries', [
            ['challenge_attempt_id' => 1, 'challenge_item_id' => 2, 'try_no' => 1, 'check_no' => 1, 'answer_json' => '{"verdict":"salah"}', 'is_correct' => 0, 'first_input_ms' => 1200, 'last_input_ms' => 3400, 'occurred_at' => '2026-10-01 08:11:00'],
            ['challenge_attempt_id' => 1, 'challenge_item_id' => 2, 'try_no' => 2, 'check_no' => 2, 'answer_json' => '{"verdict":"benar"}', 'is_correct' => 1, 'hint_opened_before' => 1, 'last_input_ms' => 9000, 'occurred_at' => '2026-10-01 08:12:00'],
        ]);

        $event = static fn (int $session, string $type, string $at, array $payload = [], array $extra = []): array => $extra + [
            'event_uuid' => sprintf('00000000-0000-4000-8000-%012d', random_int(1, 999999999)), 'client_event_id' => 'srv-' . bin2hex(random_bytes(6)),
            'session_id' => $session, 'participant_id' => $session, 'event_type' => $type, 'occurred_at' => $at, 'server_received_at' => $at,
            'payload_json' => $payload === [] ? null : json_encode($payload), 'level_id' => null, 'challenge_node_id' => null,
            'challenge_attempt_id' => null, 'challenge_item_id' => null, 'sequence_no' => 0, 'deleted_at' => null,
        ];

        $this->insertRows($db, 'game_event_logs', [
            $event(1, 'screen_viewed', '2026-10-01 08:02:00', ['screen' => 'map_kedu', 'from' => 'intro'], ['sequence_no' => 1]),
            $event(1, 'hint_opened', '2026-10-01 08:11:30', ['hint_id' => 5], ['level_id' => 1, 'challenge_node_id' => 2, 'challenge_attempt_id' => 1, 'sequence_no' => 2]),
            $event(1, 'challenge_completed', '2026-10-01 08:12:00', ['stars' => 2], ['level_id' => 1, 'challenge_node_id' => 2, 'challenge_attempt_id' => 1, 'sequence_no' => 3]),
            $event(2, 'screen_viewed', '2026-10-02 09:02:00', ['screen' => 'map_kedu', 'from' => null], ['sequence_no' => 1]),
            $event(1, 'page_hidden', '2026-10-01 08:30:00', ['screen' => 'map_kedu'], ['sequence_no' => 4, 'deleted_at' => '2026-10-05 10:00:00']),
        ]);

        $db->table('audio_usage_events')->insert([
            'session_id' => 1, 'audio_asset_id' => 1, 'action' => 'play', 'play_index' => 1, 'listened_ms' => 4200, 'completed' => 1, 'occurred_at' => '2026-10-01 08:01:30',
        ]);

        $db->table('participant_feedback')->insert([
            'participant_id' => 1, 'session_id' => 1, 'rating' => 5, 'liked_most' => 'Ceritanya', 'suggestion' => '=1+1', 'submitted_at' => '2026-10-01 08:45:00',
        ]);

        $this->insertRows($db, 'staff_users', [
            ['username' => 'admin_uji', 'password_hash' => 'x', 'role' => 'admin', 'display_name' => 'Admin', 'is_active' => 1],
            ['username' => 'ketua_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1],
            ['username' => 'peneliti_uji', 'password_hash' => 'x', 'role' => 'peneliti', 'display_name' => 'Peneliti', 'is_active' => 1],
        ]);

        foreach (['rawDataService', 'exportService', 'reportService'] as $name) {
            Services::resetSingle($name);
        }

        return $db;
    }

    /**
     * Baris satu per satu: kolom yang tidak disebut memakai nilai bawaan
     * skema (insertBatch menuntut kunci yang sama di setiap baris).
     *
     * @param list<array<string, mixed>> $rows
     */
    private function insertRows(BaseConnection $db, string $table, array $rows): void
    {
        foreach ($rows as $row) {
            $db->table($table)->insert($row);
        }
    }
}
