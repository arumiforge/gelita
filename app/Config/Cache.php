<?php

namespace Config;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Cache\Handlers\ApcuHandler;
use CodeIgniter\Cache\Handlers\DummyHandler;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\Cache\Handlers\MemcachedHandler;
use CodeIgniter\Cache\Handlers\PredisHandler;
use CodeIgniter\Cache\Handlers\RedisHandler;
use CodeIgniter\Cache\Handlers\WincacheHandler;
use CodeIgniter\Config\BaseConfig;

/**
 * Cache hanya untuk konten yang jarang berubah (level, node, item bank, media).
 * Data penelitian tidak pernah di-cache.
 */
class Cache extends BaseConfig
{
    public string $handler = 'file';

    public string $backupHandler = 'dummy';

    public string $prefix = 'gelita_';

    public int $ttl = 300;

    public string $reservedCharacters = '{}()/\@:';

    /** @var array{storePath?: string, mode?: int} */
    public array $file = [
        'storePath' => WRITEPATH . 'cache/',
        'mode'      => 0640,
    ];

    /** @var array<string, bool|int|string> */
    public array $memcached = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];

    /** @var array<string, bool|int|string|null> */
    public array $redis = [
        'host'       => '127.0.0.1',
        'password'   => null,
        'port'       => 6379,
        'timeout'    => 0,
        'async'      => false,
        'persistent' => false,
        'database'   => 0,
    ];

    /** @var array<string, class-string<CacheInterface>> */
    public array $validHandlers = [
        'apcu'      => ApcuHandler::class,
        'dummy'     => DummyHandler::class,
        'file'      => FileHandler::class,
        'memcached' => MemcachedHandler::class,
        'predis'    => PredisHandler::class,
        'redis'     => RedisHandler::class,
        'wincache'  => WincacheHandler::class,
    ];

    /** @var bool|list<string> */
    public $cacheQueryString = false;

    /** @var list<int> */
    public array $cacheStatusCodes = [];
}
