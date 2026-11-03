<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Sama seperti GameSessionFilter, tetapi membalas JSON:
 * 401 INVALID_SESSION atau 403 PASSWORD_CHANGE_REQUIRED.
 */
class ApiSessionFilter implements FilterInterface
{
    use ParticipantCheck;

    /** API paths that stay open outside a field session (28 §7). */
    private const OPEN_WHEN_CLOSED = ['api/events', 'api/audio-events', 'api/session/heartbeat'];

    public function before(RequestInterface $request, $arguments = null)
    {
        $check  = $this->checkParticipant(true);
        $status = $check['status'];

        if ($status === 'OK') {
            return null;
        }

        if ($status === 'CLOSED') {
            // Queued events, recordings, and heartbeats still come in (28 K13)
            if ($check['game_session_id'] > 0 && in_array(trim($request->getPath(), '/'), self::OPEN_WHEN_CLOSED, true)) {
                return null;
            }

            return api_error('FIELD_SESSION_CLOSED', lang('Game.sessionClosedBody'), 409);
        }

        if ($status === 'MUST_CHANGE') {
            return api_error('PASSWORD_CHANGE_REQUIRED', password_reset_text('Auth.mustChange'), 403);
        }

        return api_error('INVALID_SESSION', lang('Game.sessionExpired'), 401);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
