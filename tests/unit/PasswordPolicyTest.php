<?php

use App\Libraries\PasswordPolicy;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PasswordPolicyTest extends CIUnitTestCase
{
    public function testStrongPasswordMeetsAllFiveRules(): void
    {
        $r = (new PasswordPolicy())->check('Kedu#2026');

        $this->assertSame(5, $r['met']);
        $this->assertSame('strong', $r['level']);
        $this->assertTrue($r['acceptable']);
    }

    public function testMediumPasswordIsRejected(): void
    {
        $r = (new PasswordPolicy())->check('kedu2026');

        $this->assertSame(3, $r['met']);
        $this->assertSame('medium', $r['level']);
        $this->assertFalse($r['acceptable']);
        $this->assertFalse($r['criteria']['upper']);
        $this->assertFalse($r['criteria']['symbol']);
    }

    public function testWeakPassword(): void
    {
        $r = (new PasswordPolicy())->check('abc');

        $this->assertSame('weak', $r['level']);
        $this->assertFalse($r['acceptable']);
    }

    public function testPasswordContainingUsernameIsRejected(): void
    {
        $r = (new PasswordPolicy())->check('Jaka#2026!', 'jaka');

        $this->assertSame('strong', $r['level']);
        $this->assertTrue($r['contains_username']);
        $this->assertFalse($r['acceptable']);
    }

    public function testTooLongPasswordFailsLengthRule(): void
    {
        $r = (new PasswordPolicy())->check('Aa1!' . str_repeat('x', 70));

        $this->assertFalse($r['criteria']['length']);
        $this->assertFalse($r['acceptable']);
    }

    public function testResultNeverContainsThePassword(): void
    {
        $r = (new PasswordPolicy())->check('Kedu#2026');

        $this->assertStringNotContainsString('Kedu#2026', json_encode($r));
    }

    public function testClientConfigComesFromGelitaConfig(): void
    {
        $client = (new PasswordPolicy())->toClient();

        $this->assertSame(72, $client['max_bytes'], 'batas bcrypt ikut dikirim agar meter sama dengan check()');
        unset($client['max_bytes']);
        $this->assertSame(config('Gelita')->passwordPolicy, $client);
    }
}
