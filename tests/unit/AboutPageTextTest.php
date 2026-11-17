<?php

use App\Libraries\BankRules;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tentang GELITA (/tentang) names every kind of challenge the game has
 * (17 November 2026: it still said four after Skenario Digital became the
 * fifth). When an engine is added to BankRules::ENGINES, this test asks for
 * the sentence in both languages to be updated.
 *
 * @internal
 */
final class AboutPageTextTest extends CIUnitTestCase
{
    public function testFeatureSentenceNamesEveryChallengeKind(): void
    {
        $count = ['id' => [5 => 'Lima'], 'en' => [5 => 'Five']];

        foreach (['id', 'en'] as $locale) {
            $sentence = lang('Game.aboutFeature1', [], $locale);

            foreach (BankRules::ENGINES as $engine) {
                $this->assertStringContainsString(lang('Game.engine_' . $engine, [], $locale), $sentence, "{$locale}: {$engine}");
            }

            $this->assertStringStartsWith($count[$locale][count(BankRules::ENGINES)] ?? '?', $sentence, "{$locale}: the number matches");
        }
    }
}
