<?php

use App\Libraries\Derived\ParticipantCalculator;
use App\Libraries\Derived\ParticipantRun;
use App\Libraries\Derived\RunInput;
use App\Libraries\Derived\Thresholds;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §6.5, §7; 11 → Data siswa yang bisa dianalisis; 16; 17).
 *
 * @internal
 */
final class ParticipantCalculatorTest extends CIUnitTestCase
{
    public function testAnalysableAt49ItemsNotAt48(): void
    {
        $this->assertSame([1, 0], $this->completeness(49));
        $this->assertSame([0, 0], $this->completeness(48));
        $this->assertSame([1, 1], $this->completeness(61));
    }

    public function testRapidShareOverFirstTriesWithADecisionTime(): void
    {
        $row = $this->compute(4, static fn (int $i): array => ['ready_ms' => 0, 'last_input_ms' => $i === 1 ? 1000 : 5000]);

        $this->assertEqualsWithDelta(0.25, (float) $row['rapid_share'], 1e-9);
    }

    public function testConsentAndCutoff(): void
    {
        $p = ['assented_at' => 1, 'deactivated_at' => null];

        $this->assertSame(1, ParticipantCalculator::consentOk($p, ['parent_consent_on' => '2026-11-01', 'status' => 'active']));
        $this->assertSame(0, ParticipantCalculator::consentOk($p, ['parent_consent_on' => null, 'status' => 'active']), 'consent not filled');
        $this->assertSame(0, ParticipantCalculator::consentOk($p, null), 'no consent row (account from before part 4)');
        $this->assertSame(0, ParticipantCalculator::consentOk(['assented_at' => null], ['parent_consent_on' => '2026-11-01', 'status' => 'active']), 'no child assent');
        $this->assertSame(1, ParticipantCalculator::consentOk($p, ['parent_consent_on' => '2026-11-01', 'status' => 'withdrawn_keep']));

        $byDay = ParticipantCalculator::cutoff($p, ['status' => 'withdrawn_keep', 'status_on' => '2026-11-12']);
        $this->assertSame(['2026-11-12', 'status_on'], [$byDay['withdrawn_on'], $byDay['cutoff_basis']]);
        $this->assertSame('2026-11-12 23:59:59.999', App\Libraries\Derived\Clock::stored($byDay['withdrawn_at']));

        $byAccount = ParticipantCalculator::cutoff(['deactivated_at' => 1_700_000_000_000], ['status' => 'withdrawn_keep', 'status_on' => '2026-11-12']);
        $this->assertSame([1_700_000_000_000, 'deactivated_at'], [$byAccount['withdrawn_at'], $byAccount['cutoff_basis']]);
        $this->assertNull(ParticipantCalculator::cutoff($p, ['status' => 'left', 'status_on' => '2026-11-12'])['withdrawn_at']);
    }

    public function testQuestionnaireFormulasWithMissingCodes(): void
    {
        $answers = [
            'phone_access' => '2', 'home_phone' => '1', 'home_tablet' => '0', 'home_computer' => '1', 'home_console' => '0', 'home_device_none' => '1', 'home_internet' => '2',
            'game_time_school' => '3', 'game_time_holiday' => '4',
            'act_video' => '2', 'act_game' => '1', 'act_chat' => '0', 'act_schoolwork' => '1', 'act_search' => '0', 'act_read' => '0', 'act_post' => '0',
            'acct_whatsapp' => '2', 'acct_youtube' => '1', 'acct_tiktok' => '1', 'acct_instagram' => '0', 'acct_facebook' => '0', 'acct_game' => '2',
            'parent_couse' => '8', 'parent_talk' => '2', 'parent_rule_time' => '1', 'parent_rule_content' => '0',
            'safety_info_parent' => '1', 'safety_info_teacher' => '1', 'safety_info_family' => '0', 'safety_info_friend' => '0', 'safety_info_media' => '0', 'safety_info_none' => '0', 'safety_info_dk' => '0',
        ];
        $units = array_combine(array_keys($answers), array_map(static fn (string $u): string => 'ang-' . substr(md5($u), 0, 2), array_keys($answers)));
        $q     = ParticipantCalculator::questionnaire($answers, $units);

        $this->assertSame('lengkap', $q['questionnaire_status']);
        $this->assertSame([1, 2, 6, 3, 2, 0, 1], [$q['own_phone'], $q['device_count'], $q['access_index'], $q['activity_breadth'], $q['own_accounts'], $q['social_media_own'], $q['social_media_use']]);
        $this->assertNull($q['parent_active'], '8 is missing, so the index is empty (16)');
        $this->assertSame([1, 2, 'sedang'], [$q['parent_rules'], $q['safety_info_sources'], $q['game_time_group']]);
        $this->assertSame('ang-02', $q['q_flags'], 'nothing ticked together with a device');

        $answers['home_tablet'] = '-';
        $this->assertNull(ParticipantCalculator::questionnaire($answers, $units)['access_index'], 'one part missing empties the index');
        $this->assertSame('hilang', ParticipantCalculator::questionnaire(null, $units)['questionnaire_status']);
    }

    public function testAttitudeScoresReverseNegativesAndNeedTenAnswers(): void
    {
        $all = [];

        foreach (range(1, 12) as $n) {
            $all[sprintf('skp_%02d', $n)] = '4';
        }

        $a = ParticipantCalculator::attitude($all);
        // 8 positives at 4, 4 negatives reversed to 1: mean 36 / 12 = 3
        $this->assertSame([3.0, 12, 1], [(float) $a['sikap_score'], $a['sikap_n_answered'], $a['sikap_flag_straight']]);
        $this->assertSame(3.0, (float) $a['sikap_sk1'], '(4 + 4 + 1) / 3');

        $nine = array_merge($all, ['skp_01' => '-', 'skp_02' => '-', 'skp_03' => '-']);
        $b    = ParticipantCalculator::attitude($nine);
        $this->assertSame([null, 9, 0], [$b['sikap_score'], $b['sikap_n_answered'], $b['sikap_flag_straight']]);
        $this->assertNull($b['sikap_sk2'], 'skp-03 missing');

        $ten = array_merge($all, ['skp_01' => '-', 'skp_02' => '-']);
        $this->assertNotNull(ParticipantCalculator::attitude($ten)['sikap_score']);
    }

    public function testTestStatus(): void
    {
        $pre = ['occasion' => 'pre', 'submitted_at' => 1, 'parts' => ['mb' => ['raw_score' => 7, 'submitted_at' => 1, 'closed_at' => null]], 'blocks' => ['p1' => 'limit']];
        $t   = ParticipantCalculator::tests([$pre]);

        $this->assertSame(['hanya_awal', 7, 'p1'], [$t['test_status'], $t['pre_d1'], $t['pre_capped']]);
        $this->assertSame('hilang', ParticipantCalculator::tests([])['test_status']);
        $this->assertSame('lengkap', ParticipantCalculator::tests([$pre, ['occasion' => 'post'] + $pre])['test_status']);
    }

    /** @return array{0: int, 1: int} analysable, complete */
    private function completeness(int $tries): array
    {
        $row = $this->compute($tries);

        return [$row['analysable'], $row['complete']];
    }

    private function compute(int $tries, ?callable $timing = null): array
    {
        $items = [];

        foreach (range(1, 61) as $i) {
            $items[$i] = ['id' => $i, 'item_key' => 'x-' . $i, 'node_id' => 1, 'variant_group' => 'a', 'interaction_type' => 'verdict_card', 'dimension' => 'D1', 'scorable' => 1, 'key_box' => null];
        }

        $list = [];

        foreach (range(1, $tries) as $i) {
            $list[$i] = ['id' => $i, 'attempt_id' => 1, 'item_id' => $i, 'try_no' => 1, 'is_correct' => 1, 'occurred_at' => '2026-11-10 08:05:00', 'hint_opened_before' => 0]
                + ($timing === null ? [] : $timing($i));
        }

        $rows = ParticipantRun::compute(RunInput::fromArray([
            'participant'   => ['id' => 1, 'code' => 'S03-A01', 'school_code' => 'S03', 'booklet' => 'B1', 'assented_at' => '2026-11-01 08:00:00'],
            'consent'       => ['parent_consent_on' => '2026-11-01', 'status' => 'active', 'status_on' => null],
            'nodes'         => [1 => ['id' => 1, 'level_seq' => 1, 'sequence' => 1, 'engine' => 'boleh']],
            'items'         => $items,
            'fieldSessions' => [1 => ['id' => 1, 'meeting_no' => 2, 'makeup_no' => 0, 'date' => '2026-11-10', 'opens_at' => '2026-11-10 07:30:00', 'closes_at' => '2026-11-10 10:00:00', 'closed_at' => null, 'regions' => [1]]],
            'sessions'      => [1 => ['id' => 1, 'field_session_id' => 1, 'meeting_no' => 2, 'started_at' => '2026-11-10 08:00:00']],
            'attempts'      => [1 => ['id' => 1, 'session_id' => 1, 'node_id' => 1, 'attempt_no' => 1, 'variant_group' => 'a', 'status' => 'completed', 'started_at' => '2026-11-10 08:01:00', 'completed_at' => '2026-11-10 08:06:00', 'is_retake' => 0, 'hint_count' => 0]],
            'tries'         => $list,
        ]), new Thresholds());

        return $rows['derived_participants'][0];
    }
}
