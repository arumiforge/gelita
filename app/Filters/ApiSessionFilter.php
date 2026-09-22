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

    public function before(RequestInterface $request, $arguments = null)
    {
        $status = $this->checkParticipant(true)['status'];

        if ($status === 'OK') {
            return null;
        }

        if ($status === 'MUST_CHANGE') {
            return api_error('PASSWORD_CHANGE_REQUIRED', lang('Auth.mustChange'), 403);
        }

        return api_error('INVALID_SESSION', lang('Game.sessionExpired'), 401);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
