<?php

use App\Controllers\Game\RegisterController;
use App\Validation\GelitaRules;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * `uji.` and `sim_` are kept for test accounts and the simulator (research
 * revision 10, part 2: 28 K18, §19.1 `ReservedPrefixTest`).
 *
 * @internal
 */
final class ReservedPrefixTest extends CIUnitTestCase
{
    public function testStudentsCannotRegisterWithTheReservedPrefixes(): void
    {
        $rules = new GelitaRules();

        foreach (['uji.bahasa1', 'UJI.media2', 'sim_001', 'Sim_abc'] as $name) {
            $this->assertFalse($rules->not_reserved_prefix($name), $name);
        }

        foreach (['sekar_ayu', 'ujian5', 'simon', 'bayu.sim_'] as $name) {
            $this->assertTrue($rules->not_reserved_prefix($name), $name);
        }

        $this->assertStringContainsString('not_reserved_prefix', (new RegisterController())->rulesFor(3)['username']['rules']);
    }
}
