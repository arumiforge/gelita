<?php

use App\Exceptions\ChallengeRuleException;
use App\Libraries\BookletRules;
use App\Libraries\BookletSource;
use App\Libraries\VariantPicker;
use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * First-attempt variants follow the booklet in research mode (31 §11):
 * all four booklets × three regions give the table of 06, a missing booklet
 * keeps scored challenges closed (BOOKLET_MISSING, K10), Susun Gambar stays
 * random (K12), and field mode off keeps the random draw. The booklet source
 * of part 2 is stubbed through the BookletSource seam.
 *
 * @internal
 */
final class BookletVariantTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->originalKey           = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-bank-31';
        $this->conn                  = $this->createGradeFiveWorld();
        $this->service               = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        Factories::reset('libraries');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testEveryBookletGivesTheVariantsOfTheTable(): void
    {
        $n = 10;

        foreach (BookletRules::TABLE as $booklet => $byRegion) {
            $session = $this->newStudent(++$n);
            $this->stub(true, [$session->participant_id => $booklet]);

            foreach (['tmg' => 1, 'mgl' => 2, 'wnb' => 3] as $prefix => $sequence) {
                foreach ([2, 3, 4] as $challenge) {
                    $attempt = $this->open("{$prefix}-{$challenge}", $session->id);

                    $this->assertSame([$byRegion[$sequence], 'booklet', $booklet], [$attempt->variant_group, $attempt->variant_source, $attempt->booklet_at_open], "{$booklet} {$prefix}-{$challenge}");
                }

                // Susun Gambar never follows the booklet (K12)
                $puzzle = $this->open("{$prefix}-1", $session->id);
                $this->assertSame('random', $puzzle->variant_source);
                $this->assertNull($puzzle->booklet_at_open);
                $this->assertContains($puzzle->variant_group, ['a', 'b']);
            }
        }
    }

    public function testWithoutABookletScoredChallengesStayClosed(): void
    {
        $this->stub(true, []);

        try {
            $this->open('tmg-2');
            $this->fail('A scored challenge should not open without a booklet.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['BOOKLET_MISSING', 409], [$e->errorCode, $e->status]);
        }

        $this->assertSame(0, $this->conn->table('challenge_attempts')->countAllResults(), 'nothing is written');
        $this->assertSame('random', $this->open('tmg-1')->variant_source, 'Susun Gambar stays open');

        $node = service('contentRepository')->node($this->nodeId($this->conn, 'mgl-3'));
        $this->assertTrue((new VariantPicker(Factories::libraries('BookletSource')))->bookletMissing($this->session(), $node, []), 'the mission brief knows it up front');
        $this->assertFalse((new VariantPicker(Factories::libraries('BookletSource')))->bookletMissing($this->session(), $node, ['a']), 'a retake never waits for a booklet');
    }

    public function testFieldModeOffKeepsTheRandomDraw(): void
    {
        $this->stub(false, [1 => 'B1']);

        $attempt = $this->open('wnb-3');

        $this->assertSame(['random', null], [$attempt->variant_source, $attempt->booklet_at_open]);
        $this->assertContains($attempt->variant_group, ['a', 'b']);
    }

    public function testRetakeTakesTheOtherVariant(): void
    {
        $this->stub(true, [1 => 'B3']);

        $first = $this->open('mgl-2');
        $this->assertSame(['b', 'booklet'], [$first->variant_group, $first->variant_source]);
        $this->service->abandonAttempt($first);

        $second = $this->open('mgl-2');
        $this->assertSame(['a', 'retake', null], [$second->variant_group, $second->variant_source, $second->booklet_at_open]);
    }

    /** The default source reads part 2's tables only when they exist; without them field mode is off. */
    public function testDefaultSourceWithoutPartTwoTablesIsFieldModeOff(): void
    {
        $source = new BookletSource($this->conn);

        $this->assertFalse($source->fieldModeActive(1));
        $this->assertNull($source->bookletFor(1, 1));
        $this->assertSame('a', BookletRules::variantFor('B4', 2));
    }

    /** @param array<int, string> $booklets participant id → booklet */
    private function stub(bool $fieldMode, array $booklets): void
    {
        Factories::injectMock('libraries', 'BookletSource', new class ($fieldMode, $booklets) extends BookletSource {
            public function __construct(private bool $on, private array $booklets)
            {
            }

            public function fieldModeActive(int $studyId): bool
            {
                return $this->on;
            }

            public function bookletFor(int $participantId, int $studyId): ?string
            {
                return $this->booklets[$participantId] ?? null;
            }
        });
    }
}
