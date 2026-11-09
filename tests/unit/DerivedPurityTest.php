<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §4.1, K5): the calculators are pure functions, so hand-computed
 * examples can be tested exactly. A source check: no database, configuration,
 * service, or clock.
 *
 * @internal
 */
final class DerivedPurityTest extends CIUnitTestCase
{
    public function testCalculatorsReadNoDatabaseConfigServiceOrClock(): void
    {
        $files = glob(APPPATH . 'Libraries/Derived/*.php');
        $this->assertGreaterThanOrEqual(10, count($files));

        foreach ($files as $file) {
            $code = (string) file_get_contents($file);
            $code = preg_replace('#/\*.*?\*/|//[^\n]*#s', '', $code);

            $this->assertDoesNotMatchRegularExpression('/(?<![\w>:$\\\\])(db_connect|config|service|model|time|date|microtime|strtotime|now)\s*\(/', $code, basename($file));
            $this->assertStringNotContainsString("'now'", $code, basename($file));
        }
    }
}
