<?php

namespace App\Controllers\Game;

use App\Controllers\Concerns\InstrumentPages;
use App\Services\InstrumentGate;
use App\Services\InstrumentService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * T11, the online-risk items ang-21–ang-23, and T12 after the post-test
 * (research revision 10, part 8: 34 §8; rules of 30 §5.4). Every student
 * reaches T12 at the same point; the panel never shows who saw T11.
 */
class RiskItemsController extends BaseGameController
{
    use InstrumentPages;

    /** GET /tes/tambahan: T11 */
    public function ask(): string|RedirectResponse
    {
        $sitting = $this->riskSitting();

        if ($sitting === null) {
            return $this->toModule();
        }

        if ((int) $sitting['risk_offered'] !== 1 || $sitting['risk_reconsent'] !== null) {
            return $this->toModule();
        }

        return $this->page('instrument/risk-ask', lang('Instrument.barRisk'));
    }

    /** POST /tes/tambahan: Ya → the items, Tidak → T12 */
    public function answerAsk(): RedirectResponse
    {
        $sitting = $this->riskSitting();

        if ($sitting !== null) {
            service('instruments')->reconsent($sitting, (string) $this->request->getPost('jawab') === 'ya');
        }

        return $this->toModule();
    }

    /** GET /tes/tambahan/{n}: one item per screen ("Pertanyaan 1 dari 3") */
    public function item(int $no): string|RedirectResponse
    {
        [$sitting, $part, $items] = $this->openItems();
        $item = $items[$no - 1] ?? null;

        if ($item === null) {
            return $this->toModule();
        }

        service('instruments')->markShown($sitting, $item['units'], $item['id']);
        $meta = service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta'];

        return $this->page('instrument/questions', lang('Instrument.barRisk'), [
            'action'      => 'tes/tambahan/' . $no,
            'heading'     => lang('Instrument.questionOf', [$no, count($items)]),
            'progress'    => null,
            'note'        => $meta['risk_intro'] ?? null,
            'footnote'    => null,
            'items'       => [$item],
            'values'      => array_column(service('instruments')->responses((int) $sitting['id'], (int) $part['id']), 'response', 'unit'),
            'meta'        => $meta,
            'hasPrevious' => $no > 1,
            'last'        => false,
        ]);
    }

    /** POST /tes/tambahan/{n} */
    public function saveItem(int $no): RedirectResponse
    {
        [$sitting, $part, $items] = $this->openItems();
        $item = $items[$no - 1] ?? null;

        if ($item === null) {
            return $this->toModule();
        }

        $meta = service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta'];

        if (! service('instruments')->save($sitting, $part, $this->postedValues([$item], $meta))) {
            return redirect()->to(site_url('tes/tambahan/' . $no))->with('instrument_notice', lang('Instrument.notSaved'));
        }

        if ((string) $this->request->getPost('tujuan') === 'sebelumnya') {
            return redirect()->to(site_url('tes/tambahan/' . max(1, $no - 1)));
        }

        return redirect()->to(site_url($no >= count($items) ? 'tes/pesan' : 'tes/tambahan/' . ($no + 1)));
    }

    /** GET /tes/pesan: T12 for every student; showing it closes the risk step. */
    public function support(): string|RedirectResponse
    {
        $sitting = $this->riskSitting();

        if ($sitting === null) {
            return $this->toModule();
        }

        if ((int) $sitting['risk_offered'] === 1 && $sitting['risk_reconsent'] === null) {
            return redirect()->to(site_url('tes/tambahan'));
        }

        service('instruments')->submitSitting($sitting);

        return $this->page('instrument/message', lang('Instrument.barRisk'), [
            'title'      => lang('Game.riskThanksTitle'),
            'paragraphs' => [lang('Game.riskThanksP1'), lang('Game.riskThanksP2'), lang('Game.riskThanksP3')],
            'link'       => [lang('Game.riskThanksNext'), 'tes/selesai'],
        ]);
    }

    /** @return array<string, mixed>|null */
    private function riskSitting(): ?array
    {
        $context = $this->moduleContext();

        if ($context === null || $context['step'] !== InstrumentGate::STEP_RISK) {
            return null;
        }

        return service('instruments')->openRisk($context);
    }

    /** @return array{0: ?array, 1: ?array, 2: list<array<string, mixed>>} */
    private function openItems(): array
    {
        $sitting = $this->riskSitting();

        if ($sitting === null || (int) $sitting['risk_reconsent'] !== 1) {
            return [$sitting, null, []];
        }

        $part = service('instruments')->part((int) $sitting['id'], 'risk');

        return InstrumentService::isClosed($part) || $part === null ? [$sitting, null, []] : [$sitting, $part, service('instruments')->items($sitting, 'risk')];
    }
}
