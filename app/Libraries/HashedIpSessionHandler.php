<?php

namespace App\Libraries;

use CodeIgniter\Session\Handlers\Database\MySQLiHandler;
use Config\Session as SessionConfig;

/**
 * Session database (MySQL/MariaDB) yang menulis hash IP ke ci_sessions.ip_address,
 * bukan IP mentah (aturan sistem: IP mentah tidak pernah disimpan).
 */
class HashedIpSessionHandler extends MySQLiHandler
{
    public function __construct(SessionConfig $config, string $ipAddress)
    {
        helper('gelita');

        parent::__construct($config, (string) hash_ip($ipAddress));
    }
}
