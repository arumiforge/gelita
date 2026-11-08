<?php

use CodeIgniter\Config\BaseService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Panel form for a Skenario Digital item's actions and reasons (owner,
 * 8 November 2026): saved with the item, checked with the bank-import rules,
 * scores and codes locked once the scenario has been answered.
 *
 * @internal
 */
final class ScenarioOptionsFormTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private BaseConnection $sqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlite = $this->createGradeFiveWorld();
        $this->injectStaff('admin');
        Config\Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testTextsScoresAndCodesAreSaved(): void
    {
        [$itemId, $options] = $this->scenario();
        $first              = $options[0];
        $posted             = $this->posted($options);
        $posted[$first['id']]['text_id'] = 'Teks tindakan yang baru.';

        $this->save($itemId, $posted)->assertRedirect();

        $row = $this->sqlite->table('scenario_options')->where('id', $first['id'])->get()->getRowArray();
        $this->assertSame('Teks tindakan yang baru.', $row['text_id']);
    }

    public function testBankRulesRefuseTwoSafeActions(): void
    {
        [$itemId, $options] = $this->scenario();
        $posted             = $this->posted($options);

        foreach ($options as $option) {
            if ($option['kind'] === 'action') {
                $posted[$option['id']]['score'] = '2';
                $posted[$option['id']]['code']  = '';
            }
        }

        $this->save($itemId, $posted)->assertSessionHas('error', 'Setiap skenario punya tepat satu tindakan skor 2, tepat satu skor 1, dan sisanya skor 0.');
        $this->assertSame(array_column($options, 'score', 'id'), array_column($this->sqlite->table('scenario_options')->where('challenge_item_id', $itemId)->get()->getResultArray(), 'score', 'id'));
    }

    public function testAnsweredScenarioKeepsItsScoresButTakesNewTexts(): void
    {
        [$itemId, $options] = $this->scenario();
        $this->sqlite->table('item_responses')->insert(['challenge_attempt_id' => 1, 'challenge_item_id' => $itemId, 'status' => 'answered']);
        $action = array_values(array_filter($options, static fn (array $o): bool => $o['kind'] === 'action' && (int) $o['score'] === 0))[0];
        $posted = $this->posted($options);
        $posted[$action['id']]['score'] = '1';

        $this->save($itemId, $posted)->assertSessionHas('error');
        $this->assertSame((int) $action['score'], (int) $this->sqlite->table('scenario_options')->where('id', $action['id'])->get()->getRow('score'));

        // A disabled select posts nothing: the stored score and code stay, the text is saved
        unset($posted[$action['id']]['score'], $posted[$action['id']]['code']);
        $posted[$action['id']]['text_id'] = 'Hanya teksnya yang berubah.';
        $this->save($itemId, $posted)->assertSessionMissing('error');
        $this->assertSame('Hanya teksnya yang berubah.', $this->sqlite->table('scenario_options')->where('id', $action['id'])->get()->getRow('text_id'));
    }

    /** @return array{0: int, 1: list<array<string, mixed>>} */
    private function scenario(): array
    {
        $itemId = (int) $this->sqlite->table('challenge_items')->where('item_key', 'tmg-5-a01')->get()->getRow('id');

        return [$itemId, $this->sqlite->table('scenario_options')->where('challenge_item_id', $itemId)->orderBy('kind')->orderBy('sequence')->get()->getResultArray()];
    }

    /** @return array<int, array<string, string>> the form as the browser posts it */
    private function posted(array $options): array
    {
        $out = [];

        foreach ($options as $o) {
            $out[$o['id']] = ['text_id' => (string) $o['text_id'], 'text_en' => (string) $o['text_en'], 'code' => (string) $o['code']]
                + ($o['kind'] === 'action' ? ['score' => (string) $o['score'], 'feedback_id' => (string) $o['feedback_id'], 'feedback_en' => (string) $o['feedback_en']] : []);
        }

        return $out;
    }

    private function save(int $itemId, array $options): \CodeIgniter\Test\TestResponse
    {
        $item = $this->sqlite->table('challenge_items')->where('id', $itemId)->get()->getRowArray();

        return $this->withSession(['staff_id' => 1, 'staff_role' => 'admin', 'gelita_csrf' => self::CSRF_HASH])
            ->call(Method::POST, 'admin/konten/item/' . $itemId, [
                'gelita_csrf'      => self::CSRF_HASH . '00000000000000000000000000000000',
                'interaction_type' => 'scenario_choice',
                'prompt_id'        => (string) $item['prompt_id'],
                'variant_group'    => (string) $item['variant_group'],
                'sequence'         => (string) $item['sequence'],
                'is_active'        => '1',
                'options'          => $options,
            ]);
    }

    private function injectStaff(?string $role): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($role === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 1, 'username' => 'admin_uji', 'email' => null, 'role' => $role, 'display_name' => 'Admin Uji',
                'school_id' => null, 'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
