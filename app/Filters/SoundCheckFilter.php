<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter `soundCheck` (owner, 15 November 2026, decision 4 in
 * docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md; built 16 November 2026).
 * In research mode the first game or test page of every game session goes to
 * `/cek-suara` first: one sound plays and the student answers whether they
 * heard it. The answer is a research event (`sound_check_answered`), so the
 * data shows whether the narration could be heard.
 *
 * Only GET pages of a signed-in student with a game session in the study of
 * research mode; the API, `/cek-suara` itself, and every page outside research
 * mode pass. Once answered, the PHP session remembers the game session
 * (`sound_checked`), so a new sign-in (a new game session) asks again.
 * Registered by path in Config\Filters::$filters and reads the student
 * itself, so the order of filters does not matter.
 */
class SoundCheckFilter implements FilterInterface
{
    /** Session key: the game session whose sound check is answered. */
    public const DONE = 'sound_checked';

    /** Session key: where the student was going. */
    public const TARGET = 'sound_check_target';

    /** Session key: the student answered "Tidak terdengar"; the page shows what to do. */
    public const HELP = 'sound_check_help';

    public function before(RequestInterface $request, $arguments = null)
    {
        if ($request->getMethod() !== 'GET') {
            return null;
        }

        $sessionId = (int) session('game_session_id');

        if ((int) session('participant_id') <= 0 || $sessionId <= 0 || (int) session(self::DONE) === $sessionId) {
            return null;
        }

        if (! self::researchMode($sessionId)) {
            return null;
        }

        $path  = trim($request->getPath(), '/');
        $query = (string) $request->getUri()->getQuery();
        session()->set(self::TARGET, '/' . $path . ($query !== '' ? '?' . $query : ''));

        return redirect()->to(site_url('cek-suara'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    /** Research mode for this game session; false when the database cannot be read. */
    public static function researchMode(int $gameSessionId): bool
    {
        helper('gelita');

        return research_screen_config($gameSessionId)['researchMode'];
    }
}
