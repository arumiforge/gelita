<?php

namespace App\Commands;

use App\Models\AuditLogModel;
use App\Models\StaffUserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Reset sandi staf dari server, untuk admin yang lupa sandinya dan tidak punya
 * admin lain yang dapat mereset lewat `/admin/staf`.
 *
 * Perilakunya sama dengan reset di panel (StaffUserModel::resetToTemporary()):
 * sandi sementara dicetak sekali, wajib diganti saat masuk, dan kunci login
 * dibuka. Sandi tidak diketik di sini karena CLI::prompt() menampilkan input.
 */
class StaffPassword extends BaseCommand
{
    protected $group       = 'GELITA';
    protected $name        = 'gelita:staff:password';
    protected $description = 'Mengatur ulang sandi staf ke sandi sementara yang wajib diganti saat masuk.';
    protected $usage       = 'gelita:staff:password <username>';
    protected $arguments   = [
        'username' => 'Nama pengguna staf, mis. admin',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $username = trim((string) ($params[0] ?? ''));

        if ($username === '') {
            CLI::error('Wajib nama pengguna staf. Pemakaian: php spark ' . $this->usage);

            return EXIT_ERROR;
        }

        $staffModel = model(StaffUserModel::class);
        $staff      = $staffModel->findByUsername($username);

        if ($staff === null) {
            CLI::error("Akun staf {$username} tidak ditemukan.");

            return EXIT_ERROR;
        }

        $temporary = $staffModel->resetToTemporary($staff->id);

        model(AuditLogModel::class)->record('staff_password_reset', [
            'target_type' => 'staff_user',
            'target_id'   => (string) $staff->id,
            'metadata'    => ['via' => 'cli'],
        ]);

        CLI::write("Sandi {$staff->username} ({$staff->role}) diatur ulang. Sandi sementara:", 'green');
        CLI::write($temporary, 'yellow');
        CLI::write('Sandi ini hanya tampil sekali. Saat masuk, panel langsung meminta sandi baru.');

        if ((int) $staff->is_active !== 1) {
            CLI::write('Akun ini nonaktif: aktifkan lagi dari /admin/staf sebelum dapat dipakai masuk.', 'yellow');
        }

        return EXIT_SUCCESS;
    }
}
