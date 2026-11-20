<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Session as SessionConfig;

/**
 * The session ID is replaced every `timeToUpdate` seconds; the old row must
 * stay until garbage collection. With it deleted, a request that left the
 * browser with the old ID at the same moment (a pagehide beacon and the next
 * page always leave together) lost its login: `/masuk` for the page, or
 * 401 INVALID_SESSION for the batch (production load test, 21 November 2026).
 *
 * Installs made before the fix carry `session.regenerateDestroy = true` in
 * `.env`; the config must not follow it.
 *
 * @internal
 */
final class SessionRegenerationTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        putenv('session.regenerateDestroy');
        unset($_ENV['session.regenerateDestroy'], $_SERVER['session.regenerateDestroy']);
        parent::tearDown();
    }

    public function testTheOldSessionRowIsKeptOnRegeneration(): void
    {
        $this->assertFalse((new SessionConfig())->regenerateDestroy);
    }

    public function testAnOldEnvLineCannotTurnItBackOn(): void
    {
        putenv('session.regenerateDestroy=true');
        $_ENV['session.regenerateDestroy']    = 'true';
        $_SERVER['session.regenerateDestroy'] = 'true';

        $this->assertFalse((new SessionConfig())->regenerateDestroy);
    }

    public function testTheEnvTemplateNoLongerSetsIt(): void
    {
        $template = (string) file_get_contents(ROOTPATH . 'env');

        $this->assertDoesNotMatchRegularExpression('/^\s*session\.regenerateDestroy\s*=/m', $template);
    }

    public function testLoginStillDestroysTheSessionItReplaces(): void
    {
        // Session fixation: the ID a visitor had before logging in must not become a logged-in one
        $source = (string) file_get_contents(APPPATH . 'Services/SessionService.php');

        $this->assertStringContainsString('$session->regenerate(true);', $source);
    }
}
