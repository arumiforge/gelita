<?php

namespace App\Libraries;

/**
 * Staff roles and their capabilities (research revision 10, part 1:
 * docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md §5.1).
 *
 * The only place where staff rights are decided. Routes (filter
 * `staffCan`), the panel menu, views (`staff_can()`), and controllers read
 * this map; nothing compares role names directly. Capability names live in
 * code only and are never stored.
 */
final class StaffAccess
{
    /** Roles stored in staff_users.role. 'guru' was removed (revision 10, part 1). */
    public const ROLES = ['admin', 'peneliti', 'ketua'];

    /** Capability => roles that hold it. */
    private const CAPABILITIES = [
        'dashboard.view'             => ['admin', 'peneliti', 'ketua'],
        'participants.accounts'      => ['admin'],
        'participants.reset_by_id'   => ['admin'],
        'participants.research'      => ['peneliti', 'ketua'],
        'participants.reset_by_code' => ['peneliti', 'ketua'],
        'research.browse'            => ['ketua'],
        'research.export'            => ['ketua'],
        'content.view'               => ['admin', 'peneliti', 'ketua'],
        'content.edit'               => ['admin'],
        'studies.view'               => ['admin', 'ketua'],
        'studies.manage'             => ['ketua'],
        'releases.manage'            => ['admin'],
        'schools.manage'             => ['admin'],
        'staff.manage'               => ['admin'],
        'audit.view'                 => ['admin'],
        'retention.run'              => ['admin'],
        'deletion.request'           => ['peneliti', 'ketua'],
        'deletion.confirm'           => ['ketua'],   // part 4 (30, K1)
        'deletion.execute'           => ['admin'],
        // Hooks for parts 2-4.
        'field_sessions.manage'      => ['peneliti', 'ketua'],
        'monitoring.view'            => ['admin', 'peneliti', 'ketua'],
        'tech_logs.view'             => ['admin'],
        'risk_flags.view'            => ['ketua'],
        'identities.replace'         => ['admin'],   // part 4 (30 §10.2)
    ];

    /**
     * Unknown roles and unknown capabilities are refused, so a typo closes
     * access instead of opening it.
     */
    public static function allows(?string $role, string $capability): bool
    {
        if ($role === null || ! in_array($role, self::ROLES, true)) {
            return false;
        }

        return in_array($role, self::CAPABILITIES[$capability] ?? [], true);
    }

    /** True when the role holds at least one of $capabilities. */
    public static function allowsAny(?string $role, array $capabilities): bool
    {
        foreach ($capabilities as $capability) {
            if (self::allows($role, (string) $capability)) {
                return true;
            }
        }

        return false;
    }

    public static function isRole(?string $role): bool
    {
        return $role !== null && in_array($role, self::ROLES, true);
    }

    /** @return list<string> every capability name */
    public static function capabilities(): array
    {
        return array_keys(self::CAPABILITIES);
    }

    /** @return list<string> roles holding $capability (empty when unknown) */
    public static function rolesFor(string $capability): array
    {
        return self::CAPABILITIES[$capability] ?? [];
    }

    /** Label of a stored role for the panel (Admin.php is Indonesian only). */
    public static function label(?string $role): string
    {
        return match ($role) {
            'admin'    => lang('Admin.roleAdmin'),
            'peneliti' => lang('Admin.rolePeneliti'),
            'ketua'    => lang('Admin.roleKetua'),
            default    => (string) $role,
        };
    }
}
