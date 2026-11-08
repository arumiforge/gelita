<?php

use App\Libraries\HashedIpSessionHandler;
use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The browser session handler follows `gelita.researchPrivacy` (research
 * revision 10, part 4: 30 §12, K12, §19.1 `HashedIpSessionTest`).
 *
 * @internal
 */
final class HashedIpSessionTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        config('Gelita')->researchPrivacy = false;
        parent::tearDown();
    }

    public function testTheSwitchPicksTheHandler(): void
    {
        config('Gelita')->researchPrivacy = false;
        $this->assertSame(DatabaseHandler::class, (new Config\Session())->driver);

        config('Gelita')->researchPrivacy = true;
        $this->assertSame(HashedIpSessionHandler::class, (new Config\Session())->driver);
        $this->assertFalse((new Config\Session())->matchIP);
    }

    public function testTheHandlerKeepsTheHashNotTheIp(): void
    {
        helper('gelita');
        $handler = new HashedIpSessionHandler(new Config\Session(), '203.0.113.7');
        $stored  = (new ReflectionProperty(CodeIgniter\Session\Handlers\BaseHandler::class, 'ipAddress'))->getValue($handler);

        $this->assertSame(hash_ip('203.0.113.7'), $stored);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $stored);
        $this->assertStringNotContainsString('203.0.113.7', $stored);
    }
}
