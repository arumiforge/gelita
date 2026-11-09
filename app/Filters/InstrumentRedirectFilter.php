<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter `instrumentRedirect` (research revision 10, part 8: 34 §5.2, §5.4,
 * K1). Once a researcher pressed Mulai tes awal/akhir, the challenge pages of
 * that field session go to `/tes` and their API answers 409
 * `INSTRUMENT_STARTED` (game/challenge.js then loads `/tes` with
 * `state.leaving`, so no false `challenge_abandoned`). The opening story, the
 * ending, and Balai Refleksi go to `/tes` while a step of the module is open.
 * Open attempts are left as they are (28 K14). Registered by path in
 * Config\Filters::$filters; reads the student itself, so the order of filters does not matter.
 */
class InstrumentRedirectFilter implements FilterInterface
{
    /** Paths that are no challenge route: closed while any step of the module is open. */
    private const STORY_PATHS = ['intro', 'intro/selesai', 'penutup', 'refleksi'];

    public function before(RequestInterface $request, $arguments = null)
    {
        if ((int) session('participant_id') <= 0 || ! service('fieldSessions')->fieldModeOn()) {
            return null;
        }

        $path   = trim($request->getPath(), '/');
        $target = service('instrumentGate')->redirectFor($path, ! in_array($path, self::STORY_PATHS, true));

        if ($target === null) {
            return null;
        }

        if (str_starts_with($path, 'api/')) {
            return api_error('INSTRUMENT_STARTED', lang('Instrument.closed'), 409, ['redirect' => site_url($target)]);
        }

        return redirect()->to(site_url($target));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
