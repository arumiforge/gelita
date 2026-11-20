<?php

namespace App\Libraries\Simulation;

use App\Services\InstrumentService;

/**
 * The test module on the simulation clock (`gelita:simulasi` in research
 * mode). InstrumentService::now() is the module's only clock (34 §7.5), so
 * every sitting, block, part, and answer time, the view times, and the
 * hidden limits follow the simulated student instead of the wall clock.
 * Swapped in with Services::injectMock('instruments') for one run only.
 */
final class SimInstrumentService extends InstrumentService
{
    /** @param \Closure(): \DateTimeImmutable $clock */
    public function __construct(private readonly \Closure $clock)
    {
    }

    public function now(): string
    {
        return ($this->clock)()->format('Y-m-d H:i:s.v');
    }
}
