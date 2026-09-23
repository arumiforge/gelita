<?php

use App\Filters\DebugToolbar;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Debug Toolbar bawaan menyalin seluruh POST ke writable/debugbar. Filter
 * App\Filters\DebugToolbar wajib menyamarkan setiap field kata sandi
 * (registrasi, login, ganti sandi, login staf, akun staf) sebelum itu terjadi.
 *
 * @internal
 */
final class DebugToolbarRedactionTest extends CIUnitTestCase
{
    public function testToolbarAliasUsesRedactingFilter(): void
    {
        $this->assertSame(DebugToolbar::class, config('Filters')->aliases['toolbar']);
    }

    public function testPasswordFieldsAreRedactedAndOtherFieldsKept(): void
    {
        $redacted = $this->redact([
            'username'         => 'jaka.kedu',
            'password'         => 'Kedu#2026',
            'password_confirm' => 'Kedu#2026',
            'current_password' => 'Lama#2025',
            'display_name'     => 'Jaka',
            'staff'            => ['new_password' => 'Guru#2026', 'role' => 'guru'],
        ]);

        $this->assertSame('jaka.kedu', $redacted['username']);
        $this->assertSame('Jaka', $redacted['display_name']);
        $this->assertSame('guru', $redacted['staff']['role']);

        $this->assertStringNotContainsString('2026', json_encode([
            $redacted['password'],
            $redacted['password_confirm'],
            $redacted['staff']['new_password'],
        ]));
        $this->assertStringNotContainsString('Lama', (string) $redacted['current_password']);
    }

    /** @param array<string, mixed> $data */
    private function redact(array $data): array
    {
        $method = new ReflectionMethod(DebugToolbar::class, 'redact');

        return $method->invoke(new DebugToolbar(), $data);
    }
}
