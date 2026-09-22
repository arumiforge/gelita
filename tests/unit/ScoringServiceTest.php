<?php

use App\Services\ScoringService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Rumus skor diuji lewat compute(), yang tidak menyentuh database.
 *
 * @internal
 */
final class ScoringServiceTest extends CIUnitTestCase
{
    /** Profil baku GELITA (scoring_profiles seeder) */
    private const PROFILE = [
        'id'                              => 1,
        'code'                            => 'GELITA_V1',
        'version'                         => '1.0',
        'first_pass_weight'               => '0.7000',
        'final_weight'                    => '0.2000',
        'independence_weight'             => '0.1000',
        'hint_penalty_per_use'            => '10.0000',
        'retry_penalty_per_extra_attempt' => '5.0000',
        'three_star_min_score'            => '85.00',
        'three_star_min_first_pass'       => '80.00',
        'two_star_min_score'              => '65.00',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        helper('gelita');
    }

    public function testPerfectAttemptGetsThreeStars(): void
    {
        $result = (new ScoringService())->compute(
            ['scorable' => 4, 'first_pass' => 4, 'final' => 4],
            ['hint_count' => 0, 'retry_count' => 0, 'completed' => true],
            self::PROFILE,
        );

        $this->assertSame(100.0, $result['first_pass_accuracy']);
        $this->assertSame(100.0, $result['score']);
        $this->assertSame(3, $result['stars']);
        $this->assertSame('1.0', $result['scoring_version']);
    }

    public function testHintAndRetryLowerIndependenceOnly(): void
    {
        $result = (new ScoringService())->compute(
            ['scorable' => 4, 'first_pass' => 4, 'final' => 4],
            ['hint_count' => 2, 'retry_count' => 1, 'completed' => true],
            self::PROFILE,
        );

        // 100 - (2 * 10) - (1 * 5) = 75
        $this->assertSame(75.0, $result['independence']);
        // 0.7*100 + 0.2*100 + 0.1*75 = 97.5
        $this->assertSame(97.5, $result['score']);
        $this->assertSame(3, $result['stars']);
    }

    public function testLateCorrectionsDoNotReachTwoStars(): void
    {
        $result = (new ScoringService())->compute(
            ['scorable' => 4, 'first_pass' => 2, 'final' => 4],
            ['hint_count' => 0, 'retry_count' => 1, 'completed' => true],
            self::PROFILE,
        );

        $this->assertSame(50.0, $result['first_pass_accuracy']);
        $this->assertSame(100.0, $result['final_accuracy']);
        // 0.7*50 + 0.2*100 + 0.1*95 = 64.5
        $this->assertSame(64.5, $result['score']);
        $this->assertSame(1, $result['stars']);
    }

    public function testIndependenceNeverGoesBelowZero(): void
    {
        $result = (new ScoringService())->compute(
            ['scorable' => 2, 'first_pass' => 0, 'final' => 0],
            ['hint_count' => 20, 'retry_count' => 9, 'completed' => true],
            self::PROFILE,
        );

        $this->assertSame(0.0, $result['independence']);
        $this->assertSame(0.0, $result['score']);
        $this->assertSame(1, $result['stars']);
    }

    public function testUnfinishedAttemptGetsZeroStars(): void
    {
        $result = (new ScoringService())->compute(
            ['scorable' => 4, 'first_pass' => 4, 'final' => 4],
            ['hint_count' => 0, 'retry_count' => 0, 'completed' => false],
            self::PROFILE,
        );

        $this->assertSame(100.0, $result['score']);
        $this->assertSame(0, $result['stars']);
    }

    public function testAttemptWithoutScorableItemsScoresZeroWithoutDivisionByZero(): void
    {
        $result = (new ScoringService())->compute(
            ['scorable' => 0, 'first_pass' => 0, 'final' => 0],
            ['hint_count' => 0, 'retry_count' => 0, 'completed' => true],
            self::PROFILE,
        );

        $this->assertSame(0, $result['scorable_items']);
        $this->assertSame(0.0, $result['first_pass_accuracy']);
        $this->assertSame(10.0, $result['score']);
    }

    public function testThreeStarsNeedFirstPassAccuracyNotOnlyScore(): void
    {
        $service = new ScoringService();

        // skor cukup, tetapi ketepatan percobaan pertama di bawah ambang
        $this->assertSame(2, $service->stars(90.0, 70.0, true, self::PROFILE));
        $this->assertSame(3, $service->stars(90.0, 80.0, true, self::PROFILE));
    }
}
