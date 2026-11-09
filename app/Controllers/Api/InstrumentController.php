<?php

namespace App\Controllers\Api;

use App\Libraries\InstrumentImporter;
use App\Models\ParticipantSeatModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * `GET /api/instrument/status` (research revision 10, part 8: 34 §5.3): two
 * booleans for the opening screen, nothing else (InstrumentKeyLeakTest).
 */
class InstrumentController extends BaseApiController
{
    public function status(): ResponseInterface
    {
        $context = service('instrumentGate')->current();
        $started = $context !== null && $context['started'];
        $ready   = false;

        if ($context !== null) {
            $seat  = $context['preview'] ? null : model(ParticipantSeatModel::class)->findFor($context['participant']->id, (int) $context['study']['id']);
            $ready = $context['preview'] || in_array($seat[$context['occasion'] . '_form'] ?? null, InstrumentImporter::TEST_FORMS, true);
        }

        return $this->response->setJSON(['started' => $started, 'form_ready' => $ready]);
    }
}
