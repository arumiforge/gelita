<?php

namespace App\Controllers\Game;

use App\Controllers\Concerns\InstrumentPages;
use App\Libraries\InstrumentImporter;
use App\Services\InstrumentGate;
use App\Services\InstrumentService;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * The pre/post test pages (research revision 10, part 8: 34 §5, §7, K6):
 * server-rendered, one item per request; every move is a POST that saves the
 * page and redirects, so the module works without JavaScript and view times
 * come from the server clock. Routes `/tes…` (filters `gameSession`,
 * `instrumentGate`; no `screenView`, K14).
 */
class InstrumentController extends BaseGameController
{
    use InstrumentPages;

    /** GET /tes: the next step, or the opening screen. */
    public function index(): string|RedirectResponse
    {
        $context = $this->moduleContext();

        if ($context === null) {
            return $this->closedPage();
        }

        switch ($context['step']) {
            case InstrumentGate::STEP_QUESTIONNAIRE:
                return redirect()->to(site_url('tes/angket/0'));

            case InstrumentGate::STEP_RISK:
                return $this->riskStep($context);

            case InstrumentGate::STEP_DONE:
                return redirect()->to(site_url('tes/selesai'));
        }

        $instruments = service('instruments');
        $sitting     = $this->testSitting($context);

        if ($sitting === null) {
            $meta  = service('instrumentRepository')->activeVersion('tes')['meta'] ?? [];
            $ready = $context['preview'] || ($context['started'] && $this->formReady($context));

            return $this->page('instrument/start', $this->barFor($context['occasion'], 'test'), [
                'meta' => $meta, 'ready' => $ready, 'started' => $context['started'], 'preview' => $context['preview'], 'occasion' => $context['occasion'],
            ]);
        }

        $parts = $instruments->parts((int) $sitting['id']);

        foreach (InstrumentService::TEST_PARTS as $i => $code) {
            $part = $parts[$code] ?? null;

            if (InstrumentService::isClosed($part)) {
                continue;
            }

            if ($part === null) {
                // A stretch between the blocks, then the next part's examples (§7.3 steps 6–7)
                return redirect()->to(site_url($code === 'ae' ? 'tes/jeda' : 'tes/contoh/' . $code));
            }

            $last = $sitting['last_item_id'] === null ? null : (service('instrumentRepository')->content((int) $sitting['version_id'])['byId'][(int) $sitting['last_item_id']] ?? null);
            $no   = $last !== null && $last['part'] === $code ? (int) $last['position'] : 1;

            return redirect()->to(site_url('tes/bagian/' . $code . '/' . $no));
        }

        return redirect()->to(site_url('tes/selesai'));
    }

    /** POST /tes/mulai: makes the test sitting (§7.2). */
    public function start(): RedirectResponse
    {
        $context = $this->moduleContext();

        if ($context === null || $context['step'] !== InstrumentGate::STEP_TEST) {
            return $this->toModule();
        }

        if ($context['preview']) {
            $this->rememberPreview((string) $this->request->getPost('occasion'));
            $context = $this->moduleContext(true);
        }

        try {
            service('instruments')->startTest($context, $context['preview'] ? (string) $this->request->getPost('form') : null);
        } catch (\DomainException $e) {
            return $this->toModule($e->getMessage() === 'INSTRUMENT_PRE_AFTER_PLAY' ? lang('Instrument.noPreAfterPlay') : lang('Instrument.waitStart'));
        }

        return redirect()->to(site_url('tes/contoh/mb'));
    }

    /** GET /tes/contoh/{part}: examples before a part. */
    public function example(string $part): string|RedirectResponse
    {
        [$context, $sitting] = $this->openTest();

        if ($sitting === null || ! in_array($part, InstrumentService::TEST_PARTS, true) || ! $this->mayStart($sitting, $part)) {
            return $this->toModule();
        }

        $meta     = $this->testMeta($sitting);
        $content  = service('instrumentRepository')->content((int) $sitting['version_id']);
        $examples = [];
        $units    = [];

        foreach ((array) ($meta['parts'][$part]['examples'] ?? []) as $code) {
            $item = $content['items'][$code] ?? null;

            if ($item !== null) {
                $examples[] = ['item' => $item, 'stimulus' => $content['stimuli'][$item['stimulus_id']] ?? null];
                $units[]    = $item['units'][0];
            }
        }

        service('instruments')->markShown($sitting, $units);

        return $this->page('instrument/examples', $this->barFor($sitting['occasion'], 'test'), [
            'part'     => $part,
            'partMeta' => $meta['parts'][$part],
            'examples' => $examples,
            'values'   => array_column(service('instruments')->responses((int) $sitting['id']), 'response', 'unit'),
            'meta'     => $meta,
        ]);
    }

    /** POST /tes/bagian/{part}/mulai: saves the examples and starts the part (and its block). */
    public function startPart(string $part): RedirectResponse
    {
        [, $sitting] = $this->openTest();

        if ($sitting === null || ! in_array($part, InstrumentService::TEST_PARTS, true)) {
            return $this->toModule();
        }

        $meta     = $this->testMeta($sitting);
        $content  = service('instrumentRepository')->content((int) $sitting['version_id']);
        $examples = array_values(array_filter(array_map(static fn (string $c): ?array => $content['items'][$c] ?? null, (array) ($meta['parts'][$part]['examples'] ?? []))));
        $started  = service('instruments')->startPart($sitting, $part, $this->postedValues($examples, $meta));

        return $started === null ? $this->toModule() : redirect()->to(site_url('tes/bagian/' . $part . '/1'));
    }

    /** GET /tes/bagian/{part}/{no}: one item. */
    public function item(string $part, int $no): string|RedirectResponse
    {
        [, $sitting, $row] = $this->openPart($part);

        if ($row === null) {
            return $this->toModule($this->closedNotice($sitting, $part));
        }

        $instruments = service('instruments');
        $items       = $instruments->items($sitting, $part);
        $item        = $items[$no - 1] ?? null;

        if ($item === null) {
            return redirect()->to(site_url('tes/bagian/' . $part . '/1'));
        }

        $content = service('instrumentRepository')->content((int) $sitting['version_id']);
        $meta    = $content['version']['meta'];
        $instruments->markShown($sitting, $item['units'], $item['id']);

        $values = array_column($instruments->responses((int) $sitting['id'], (int) $row['id']), 'response', 'unit');
        $nav    = array_map(static fn (array $i): array => ['no' => (int) $i['position'], 'answered' => ($values[$i['units'][0]] ?? null) !== null], $items);

        return $this->page('instrument/item', $this->barFor($sitting['occasion'], 'test'), [
            'part'         => $part,
            'partMeta'     => $meta['parts'][$part],
            'item'         => $item,
            'stimulus'     => $item['stimulus_id'] === null ? null : ($content['stimuli'][$item['stimulus_id']] ?? null),
            'stimulusLead' => $this->stimulusLead($items, $item, $meta),
            'values'       => $values,
            'nav'          => $nav,
            'count'        => count($items),
            'meta'         => $meta,
        ] + $this->timer($sitting, $row));
    }

    /** POST /tes/bagian/{part}/{no}: saves, then goes where the student pressed. */
    public function saveItem(string $part, int $no): RedirectResponse
    {
        [, $sitting, $row] = $this->openPart($part);

        if ($row === null) {
            return $this->toModule($this->closedNotice($sitting, $part));
        }

        $instruments = service('instruments');
        $items       = $instruments->items($sitting, $part);
        $item        = $items[$no - 1] ?? null;
        $meta        = $this->testMeta($sitting);

        if ($item !== null && ! $instruments->save($sitting, $row, $this->postedValues([$item], $meta))) {
            // Another tab won, or the part just closed: answer from the newest state (§7.5)
            return $this->toModule(lang('Instrument.notSaved'));
        }

        $target = (string) $this->request->getPost('tujuan');

        return redirect()->to(site_url('tes/bagian/' . $part . '/' . match (true) {
            $target === 'periksa'     => 'periksa',
            $target === 'sebelumnya'  => max(1, $no - 1),
            ctype_digit($target)      => min(count($items), max(1, (int) $target)),
            default                   => $no >= count($items) ? 'periksa' : $no + 1,
        }));
    }

    /** GET /tes/bagian/{part}/periksa */
    public function review(string $part): string|RedirectResponse
    {
        [, $sitting, $row] = $this->openPart($part);

        if ($row === null) {
            return $this->toModule($this->closedNotice($sitting, $part));
        }

        $instruments = service('instruments');
        $values      = array_column($instruments->responses((int) $sitting['id'], (int) $row['id']), 'response', 'unit');
        $missing     = [];

        foreach ($instruments->items($sitting, $part) as $item) {
            if (($values[$item['units'][0]] ?? null) === null) {
                $missing[] = (int) $item['position'];
            }
        }

        return $this->page('instrument/review', $this->barFor($sitting['occasion'], 'test'), [
            'part' => $part, 'partMeta' => $this->testMeta($sitting)['parts'][$part], 'missing' => $missing,
        ] + $this->timer($sitting, $row));
    }

    /** POST /tes/bagian/{part}/kirim */
    public function submitPart(string $part): RedirectResponse
    {
        [, $sitting, $row] = $this->openPart($part);

        if ($row === null || ! service('instruments')->submitPart($sitting, $part)) {
            return $this->toModule($row === null ? $this->closedNotice($sitting, $part) : null);
        }

        $this->moduleContext(true);

        return $this->toModule();
    }

    /** GET /tes/jeda: stretch between Bagian 1 and Bagian 2–3 (§7.3 step 6). */
    public function stretch(): string|RedirectResponse
    {
        [, $sitting] = $this->openTest();

        if ($sitting === null) {
            return $this->toModule();
        }

        return $this->page('instrument/message', $this->barFor($sitting['occasion'], 'test'), [
            'title' => lang('Instrument.introTitle'), 'paragraphs' => [lang('Instrument.stretch')],
            'link'  => [lang('Instrument.continuePart', [2]), 'tes/contoh/ae'],
        ]);
    }

    /** GET /tes/selesai: the end of the module, on to the story (§4 step 8). */
    public function done(): string|RedirectResponse
    {
        $context = $this->moduleContext();

        if ($context === null) {
            return $this->closedPage();
        }

        if ($context['step'] !== InstrumentGate::STEP_DONE) {
            return $this->toModule();
        }

        $link = match (true) {
            $context['preview']             => [lang('Instrument.toGate'), 'gerbang'],
            $context['occasion'] === 'post' => [lang('Instrument.toEnding'), 'penutup'],
            default                         => [lang('Instrument.toIntro'), 'intro'],
        };

        return $this->page('instrument/message', $this->barFor($context['occasion'], 'test'), [
            'title' => lang('Instrument.doneTitle'), 'paragraphs' => [lang('Instrument.doneBody')], 'link' => $link,
        ]);
    }

    // ------------------------------------------------------------ internal

    private function closedPage(): string
    {
        return $this->page('instrument/message', lang('Instrument.moduleTitle'), [
            'title' => lang('Instrument.moduleTitle'), 'paragraphs' => [lang('Instrument.closed')], 'link' => null,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function testSitting(array $context): ?array
    {
        $sitting = service('instruments')->sitting($context['participant']->id, (int) $context['study']['id'], $context['occasion'], 'test', $context['preview']);

        return $sitting === null ? null : service('instruments')->sync($sitting);
    }

    /** @return array{0: ?array, 1: ?array} context and the open test sitting */
    private function openTest(): array
    {
        $context = $this->moduleContext();

        if ($context === null || $context['step'] !== InstrumentGate::STEP_TEST) {
            return [$context, null];
        }

        $sitting = $this->testSitting($context);

        return [$context, $sitting !== null && $sitting['submitted_at'] === null ? $sitting : null];
    }

    /** @return array{0: ?array, 1: ?array, 2: ?array} context, sitting, open part */
    private function openPart(string $part): array
    {
        [$context, $sitting] = $this->openTest();

        if ($sitting === null || ! in_array($part, InstrumentService::TEST_PARTS, true)) {
            return [$context, $sitting, null];
        }

        $row = service('instruments')->part((int) $sitting['id'], $part);

        return [$context, $sitting, InstrumentService::isClosed($row) ? null : $row];
    }

    /** "Waktu bagian ini sudah habis" when the part was closed by its limit. */
    private function closedNotice(?array $sitting, string $part): ?string
    {
        if ($sitting === null && ($context = $this->moduleContext()) !== null) {
            $sitting = service('instruments')->sitting($context['participant']->id, (int) $context['study']['id'], $context['occasion'], 'test', $context['preview']);
        }

        $row = $sitting === null ? null : service('instruments')->part((int) $sitting['id'], $part);

        return $row !== null && (int) $row['closed_by_limit'] === 1 ? lang('Instrument.partClosed') : null;
    }

    /** A part may start once the part before it is closed. */
    private function mayStart(array $sitting, string $part): bool
    {
        $parts = service('instruments')->parts((int) $sitting['id']);
        $index = (int) array_search($part, InstrumentService::TEST_PARTS, true);

        return ! isset($parts[$part]) && ($index === 0 || InstrumentService::isClosed($parts[InstrumentService::TEST_PARTS[$index - 1]] ?? null));
    }

    /** @return array<string, mixed> */
    private function testMeta(array $sitting): array
    {
        return service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta'];
    }

    /** True when the student's seat holds the occasion's form (K10). */
    private function formReady(array $context): bool
    {
        $seat = model(\App\Models\ParticipantSeatModel::class)->findFor($context['participant']->id, (int) $context['study']['id']);

        return in_array($seat[$context['occasion'] . '_form'] ?? null, InstrumentImporter::TEST_FORMS, true);
    }

    /**
     * "Bacalah teks di bawah ini…" over the passage, with the numbers of the
     * items that share it (14 → Cara membaca).
     */
    private function stimulusLead(array $items, array $item, array $meta): ?string
    {
        if ($item['part'] !== 'mb' || $item['stimulus_id'] === null) {
            return null;
        }

        $numbers = array_map(static fn (array $i): int => (int) $i['position'], array_filter($items, static fn (array $i): bool => $i['stimulus_id'] === $item['stimulus_id']));

        return lang('Instrument.stimulusLead', [min($numbers), max($numbers)]);
    }

    /**
     * Seconds to the reminder and to the limit for js/instrument/timer.js;
     * none for previews (§5.5).
     *
     * @return array{remindIn: ?int, closeIn: ?int}
     */
    private function timer(array $sitting, array $part): array
    {
        $block = $part['block_id'] === null || (int) $sitting['is_preview'] === 1 ? null : model(\App\Models\InstrumentBlockModel::class)->find((int) $part['block_id']);

        if ($block === null) {
            return ['remindIn' => null, 'closeIn' => null];
        }

        $left   = service('instruments')->secondsLeft($block);
        $study  = model(\App\Models\ResearchStudyModel::class)->find((int) $sitting['study_id']) ?? [];
        $remind = service('instruments')->settings($study)['reminder'] * 60;

        return ['remindIn' => max(0, $left - $remind), 'closeIn' => $left];
    }

    /** The risk step: T11, an item, or T12 (§8.1). */
    private function riskStep(array $context): RedirectResponse
    {
        $sitting = service('instruments')->openRisk($context);

        if ((int) $sitting['risk_offered'] === 1 && $sitting['risk_reconsent'] === null) {
            return redirect()->to(site_url('tes/tambahan'));
        }

        if ((int) $sitting['risk_reconsent'] === 1 && ! InstrumentService::isClosed(service('instruments')->part((int) $sitting['id'], 'risk'))) {
            return redirect()->to(site_url('tes/tambahan/1'));
        }

        return redirect()->to(site_url('tes/pesan'));
    }
}
