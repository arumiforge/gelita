<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\BaseHandler;
use App\Libraries\HashedIpSessionHandler;
use CodeIgniter\Session\Handlers\DatabaseHandler;

class Session extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Session Driver
     * --------------------------------------------------------------------------
     *
     * The session storage driver to use:
     * - `CodeIgniter\Session\Handlers\ArrayHandler` (for testing)
     * - `CodeIgniter\Session\Handlers\FileHandler`
     * - `CodeIgniter\Session\Handlers\DatabaseHandler`
     * - `CodeIgniter\Session\Handlers\MemcachedHandler`
     * - `CodeIgniter\Session\Handlers\RedisHandler`
     *
     * GELITA memakai DatabaseHandler (tabel `ci_sessions`, migration 002900)
     * agar sesi tahan restart dan dapat dipakai lintas worker. Services::session()
     * memetakan DatabaseHandler ke MySQLiHandler sesuai platform koneksi.
     *
     * @var class-string<BaseHandler>
     */
    public string $driver = DatabaseHandler::class;

    /**
     * --------------------------------------------------------------------------
     * Session Cookie Name
     * --------------------------------------------------------------------------
     *
     * The session cookie name, must contain only [0-9a-z_-] characters
     */
    public string $cookieName = 'gelita_session';

    /**
     * --------------------------------------------------------------------------
     * Session Expiration
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Setting to 0 (zero) means expire when the browser is closed.
     *
     * 14400 detik (4 jam) menutup satu sesi bermain di kelas tanpa memaksa
     * siswa login ulang di tengah tantangan.
     */
    public int $expiration = 14400;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to and is driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     *
     * For the 'database' driver, it's a table name.
     * Please read up the manual for the format with other session drivers.
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     */
    public string $savePath = 'ci_sessions';

    /**
     * --------------------------------------------------------------------------
     * Session Match IP
     * --------------------------------------------------------------------------
     *
     * Whether to match the user's IP address when reading the session data.
     *
     * WARNING: If you're using the database driver, don't forget to update
     *          your session table's PRIMARY KEY when changing this setting.
     *
     * Tetap false: lab sekolah sering ber-NAT/DHCP sehingga IP peserta dapat
     * berubah di tengah sesi. `ci_sessions` juga ber-PK `id` saja (migration
     * 003000), jadi mengaktifkan ini butuh perubahan skema.
     */
    public bool $matchIP = false;

    /**
     * --------------------------------------------------------------------------
     * Session Time to Update
     * --------------------------------------------------------------------------
     *
     * How many seconds between CI regenerating the session ID.
     */
    public int $timeToUpdate = 300;

    /**
     * --------------------------------------------------------------------------
     * Session Regenerate Destroy
     * --------------------------------------------------------------------------
     *
     * Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID. When set to FALSE, the data
     * will be later deleted by the garbage collector.
     *
     * Always false, whatever `.env` says (see the constructor). The ID is
     * replaced every `$timeToUpdate` seconds by the first request that is
     * not marked `X-Requested-With: XMLHttpRequest`: a page, or a pagehide
     * beacon. A request that left the browser with the old ID at the same
     * moment (the beacon and the next page always leave together) then
     * found no session: the page went to `/masuk`, or the batch got
     * 401 INVALID_SESSION. With false the old row keeps the login until the
     * garbage collector removes it, so both requests are answered. Login
     * still destroys the old session (`SessionService::bindPhpSession()`),
     * and so does logout. Found by the production load test, 21 November
     * 2026 (decision log, *Remote load test, play rounds and deadlocks*).
     */
    public bool $regenerateDestroy = false;

    /**
     * --------------------------------------------------------------------------
     * Session Database Group
     * --------------------------------------------------------------------------
     *
     * DB Group for the database session.
     */
    public ?string $DBGroup = null;

    /**
     * --------------------------------------------------------------------------
     * Lock Retry Interval (microseconds)
     * --------------------------------------------------------------------------
     *
     * This is used for RedisHandler.
     *
     * Time (microseconds) to wait if lock cannot be acquired.
     * The default is 100,000 microseconds (= 0.1 seconds).
     */
    public int $lockRetryInterval = 100_000;

    /**
     * --------------------------------------------------------------------------
     * Lock Max Retries
     * --------------------------------------------------------------------------
     *
     * This is used for RedisHandler.
     *
     * Maximum number of lock acquisition attempts.
     * The default is 300 times. That is lock timeout is about 30 (0.1 * 300)
     * seconds.
     */
    public int $lockMaxRetries = 300;

    /**
     * Part 4 (30 §12, K12): with `gelita.researchPrivacy` on, browser sessions
     * go through HashedIpSessionHandler (MySQL/MariaDB only, like production),
     * which stores hash_ip() instead of the raw IP. Off: DatabaseHandler as before.
     */
    public function __construct()
    {
        parent::__construct();

        if (config(Gelita::class)->researchPrivacy) {
            $this->driver = HashedIpSessionHandler::class;
        }

        // Not a setting: installs made before 21 November 2026 still carry
        // `session.regenerateDestroy = true` in `.env`, which logs students out
        // at random (see the property)
        $this->regenerateDestroy = false;
    }
}
