<?php

namespace App\Controllers\Game;

use App\Controllers\Concerns\InstrumentPages;
use App\Libraries\InstrumentImporter;
use App\Services\InstrumentGate;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * The digital-access questionnaire (research revision 10, part 8: 34 §7.7;
 * 16): straight after the pre-test, one section A–H per screen, no time
 * limit. Screen 0 is the opening text. Every move saves the screen.
 */
class QuestionnaireController extends BaseGameController
{
    use InstrumentPages;

    /** GET /tes/angket/{n} */
    public function section(int $no): string|RedirectResponse
    {
        $sitting = $this->openSitting();

        if ($sitting === null) {
            return $this->toModule();
        }

        $meta = $this->meta($sitting);

        if ($no <= 0) {
            return $this->page('instrument/questionnaire-intro', lang('Instrument.barQuestionnaire'), [
                'meta' => $meta, 'preRefused' => $this->preRefused(),
            ]);
        }

        $code = InstrumentImporter::QUESTIONNAIRE_SECTIONS[$no - 1] ?? null;

        if ($code === null) {
            return redirect()->to(site_url('tes/angket/1'));
        }

        $instruments = service('instruments');
        $items       = $instruments->items($sitting, $code);
        $instruments->markShown($sitting, array_merge(...array_map(static fn (array $i): array => $i['units'], $items)), $items[0]['id'] ?? null);
        $count = count(InstrumentImporter::QUESTIONNAIRE_SECTIONS);

        return $this->page('instrument/questions', lang('Instrument.barQuestionnaire'), [
            'action'       => 'tes/angket/' . $no,
            'heading'      => $code . '. ' . $meta['sections'][$code]['title'],
            'progress'     => lang('Instrument.sectionOf', [$no, $count]),
            'note'         => $meta['sections'][$code]['note'] ?? null,
            'footnote'     => $meta['footnote'] ?? null,
            'items'        => $items,
            'values'       => array_column($instruments->responses((int) $sitting['id']), 'response', 'unit'),
            'meta'         => $meta,
            'hasPrevious'  => $no > 1,
            'last'         => $no === $count,
            'finishAction' => 'tes/angket/selesai',
        ]);
    }

    /** POST /tes/angket/{n} */
    public function saveSection(int $no): RedirectResponse
    {
        $sitting = $this->openSitting();

        if ($sitting === null) {
            return $this->toModule();
        }

        if (! $this->saveScreen($sitting, $no)) {
            return redirect()->to(site_url('tes/angket/' . $no))->with('instrument_notice', lang('Instrument.notSaved'));
        }

        $count  = count(InstrumentImporter::QUESTIONNAIRE_SECTIONS);
        $target = (string) $this->request->getPost('tujuan') === 'sebelumnya' ? $no - 1 : $no + 1;

        return redirect()->to(site_url('tes/angket/' . max(1, min($count, $target))));
    }

    /** POST /tes/angket/selesai: saves the last screen and sends the questionnaire. */
    public function finish(): RedirectResponse
    {
        $sitting = $this->openSitting();

        if ($sitting !== null) {
            $this->saveScreen($sitting, count(InstrumentImporter::QUESTIONNAIRE_SECTIONS));
            service('instruments')->submitSitting($sitting);
        }

        return $this->toModule();
    }

    private function saveScreen(array $sitting, int $no): bool
    {
        $code = InstrumentImporter::QUESTIONNAIRE_SECTIONS[$no - 1] ?? null;

        if ($code === null) {
            return true;
        }

        $instruments = service('instruments');
        $part        = $instruments->part((int) $sitting['id'], $code);

        return $part === null || $instruments->save($sitting, $part, $this->postedValues($instruments->items($sitting, $code), $this->meta($sitting)));
    }

    /** @return array<string, mixed>|null the open questionnaire sitting */
    private function openSitting(): ?array
    {
        $context = $this->moduleContext();

        if ($context === null || $context['step'] !== InstrumentGate::STEP_QUESTIONNAIRE) {
            return null;
        }

        return service('instruments')->openQuestionnaire($context);
    }

    private function meta(array $sitting): array
    {
        return service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta'];
    }

    /** K11: the pre-test was refused because the student played already. */
    private function preRefused(): bool
    {
        $context = $this->moduleContext();

        return ! $context['preview'] && $context['test_allowed'] && ! $context['carry_over']
            && service('instruments')->sitting($context['participant']->id, (int) $context['study']['id'], 'pre', 'test') === null;
    }
}
