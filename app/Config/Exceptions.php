<?php

namespace Config;

use App\Libraries\GelitaExceptionHandler;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

class Exceptions extends BaseConfig
{
    public bool $log = true;

    /** @var list<int> */
    public array $ignoreCodes = [404];

    public string $errorViewPath = APPPATH . 'Views/errors';

    /** @var list<string> */
    public array $sensitiveDataInTrace = [];

    public bool $logDeprecations = true;

    public string $deprecationLogLevel = LogLevel::WARNING;

    /**
     * /api/* → JSON seragam {success:false, code, message, request_id};
     * halaman HTML → view error bergaya GELITA.
     */
    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new GelitaExceptionHandler($this);
    }
}
