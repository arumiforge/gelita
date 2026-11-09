<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Routes of the test module `/tes…` (research revision 10, part 8: 34 §5,
 * §12). Runs after `gameSession`: outside field mode (and outside the
 * validators' preview) the module does not exist, so every path but `/tes`
 * itself goes back to the gate; `/tes` shows the closed message.
 */
class InstrumentGateFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (service('instrumentGate')->current() !== null) {
            return null;
        }

        return trim($request->getPath(), '/') === 'tes' ? null : redirect()->to(site_url('tes'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
