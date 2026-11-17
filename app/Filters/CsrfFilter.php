<?php

namespace App\Filters;

use CodeIgniter\Filters\CSRF;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Throwable;

/**
 * CodeIgniter's CSRF filter, with one quiet case (30 §19.3 finding (e)).
 *
 * The CSRF hash lives in the session. When a student's login ends while a
 * page is still open (a withdrawal deactivates the account and the next
 * request destroys the session, 30 §5.3; or the session simply expired),
 * that page's queued events and beacons still carry the old token. Thrown,
 * the SecurityException reaches the framework's exception logger as a
 * critical error before GelitaExceptionHandler turns it into a 401. An API
 * request without any login is answered here with the same plain
 * 401 INVALID_SESSION instead, and nothing is logged: it is an ended
 * session, not an attack.
 *
 * With a login (student or staff) an API request with a refused token is
 * a page left open too long: it is answered here with the same 403
 * CSRF_EXPIRED that GelitaExceptionHandler gave, and written as a warning,
 * not a critical error (owner, 17 November 2026). HTML form posts are still
 * thrown and handled as before.
 */
class CsrfFilter extends CSRF
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            return parent::before($request, $arguments);
        } catch (SecurityException $e) {
            if (! $request instanceof IncomingRequest || ! is_api_path($request->getPath())) {
                throw $e;
            }

            if ($this->hasLogin()) {
                log_message('warning', 'CSRF token refused on {path} for a signed-in page: answered 403 CSRF_EXPIRED.', ['path' => $request->getPath()]);

                return api_error('CSRF_EXPIRED', lang('Game.errPageExpired'), 403);
            }

            return api_error('INVALID_SESSION', lang('Game.sessionExpired'), 401);
        }
    }

    /** A student or staff login on this request's session (same test as GelitaExceptionHandler). */
    private function hasLogin(): bool
    {
        try {
            return session('participant_id') !== null || session('staff_id') !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
