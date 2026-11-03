<?php

use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * The player payload of every challenge carries texts, opaque refs, and
 * boxes only: no keys, error codes, scores, main lures, or developer labels
 * (31 §10.3). Checked on the keys of the whole JSON tree and on code-like
 * values.
 *
 * @internal
 */
final class NoSecretsInPayloadTest extends CIUnitTestCase
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

    private const FORBIDDEN_KEYS = [
        'answer_key', 'answer_key_json', 'error_code', 'is_main', 'main', 'score', 'code', 'akm_level', 'akm_level_source',
        'dimension', 'subaspect', 'bloom_level', 'bloom_level_source', 'pirls_process', 'd3_clue_type', 'd2_topic',
        'testlet_key', 'surface_json', 'part_key', 'decoy', 'scorable', 'explanation_id', 'explanation_en',
    ];

    public function testNoEnginePayloadCarriesKeysCodesScoresOrLabels(): void
    {
        foreach (['tmg', 'mgl', 'wnb'] as $region) {
            foreach (range(1, 4) as $sequence) {
                $ref     = $region . '-' . $sequence;
                $payload = $this->service->openNode($this->session(), $this->nodeId($this->conn, $ref))['payload'];
                $keys    = [];

                array_walk_recursive($payload, static function ($value, $key) use (&$keys): void {
                    $keys[] = (string) $key;
                });
                $this->collectKeys($payload, $keys);

                foreach (self::FORBIDDEN_KEYS as $forbidden) {
                    $this->assertNotContains($forbidden, $keys, "{$ref}: {$forbidden}");
                }

                $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                $this->assertDoesNotMatchRegularExpression('/\\b(?:INF|BUD|DIG)-[A-Z]{3,4}\\b|\\bR-(?:RISK|ETIK|ATUR|SOS|ACAK)\\b/', $json, $ref);
                $this->assertDoesNotMatchRegularExpression('/"(?:[A-F]\\d{1,2})"/', $json, "{$ref}: no part key in the payload");
            }
        }
    }

    public function testRoundTwoPartsCarryOnlyRefBoxKindLabelAndText(): void
    {
        $payload = $this->service->openNode($this->session(), $this->nodeId($this->conn, 'wnb-4'))['payload'];

        $this->assertSame(['title', 'media', 'parts', 'clues'], array_keys($payload['round2']));
        $this->assertCount(12, $payload['round2']['parts']);
        $this->assertCount(4, $payload['round2']['clues']);

        foreach ($payload['round2']['parts'] as $part) {
            $this->assertSame(['ref', 'kind', 'label', 'text', 'x', 'y', 'w', 'h'], array_keys($part));
            $this->assertMatchesRegularExpression('/^[0-9a-f]{20}$/', $part['ref']);
        }
    }

    /** @param array<mixed> $tree */
    private function collectKeys(array $tree, array &$keys): void
    {
        foreach ($tree as $key => $value) {
            $keys[] = (string) $key;

            if (is_array($value)) {
                $this->collectKeys($value, $keys);
            }
        }
    }
}
