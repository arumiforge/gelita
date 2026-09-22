<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;
use CodeIgniter\Log\Handlers\HandlerInterface;

/**
 * Logger tidak pernah mencatat body request. Kata sandi (siswa maupun staf)
 * tidak boleh dikirim ke log_message() dalam bentuk apa pun.
 */
class Logger extends BaseConfig
{
    /** production: 4 (info ke atas); development: 9 */
    public $threshold = (ENVIRONMENT === 'production') ? 4 : 9;

    /** Presisi mikrodetik, selaras dengan DATETIME(6) event penelitian */
    public string $dateFormat = 'Y-m-d H:i:s.u';

    /** @var array<class-string<HandlerInterface>, array<string, int|list<string>|string>> */
    public array $handlers = [
        FileHandler::class => [
            'handles' => [
                'critical',
                'alert',
                'emergency',
                'debug',
                'error',
                'info',
                'notice',
                'warning',
            ],
            'fileExtension'   => '',
            'filePermissions' => 0644,
            'path'            => '',
        ],
    ];
}
