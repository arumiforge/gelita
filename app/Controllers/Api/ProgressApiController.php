<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Progres authoritative: satu-satunya sumber kebenaran status level, node,
 * serpihan, dan bintang. Client tidak pernah menghitungnya sendiri.
 */
class ProgressApiController extends BaseApiController
{
    public function show(): ResponseInterface
    {
        $session = $this->gameSession();
        $nodes   = [];

        foreach (service('contentRepository')->levels() as $level) {
            $nodes[$level->id] = $this->nodeOverview($session, $level->id);
        }

        return $this->ok([
            'progress'    => $this->progressSummary($session),
            'levels'      => $this->levelOverview($session),
            'nodes'       => $nodes,
            'unlock_mode' => $this->unlockMode($session),
            'server_time' => $this->serverTime(),
        ]);
    }
}
