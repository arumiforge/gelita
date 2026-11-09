<?php

namespace App\Controllers\Concerns;

use App\Services\InstrumentGate;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Shared parts of the module controllers (research revision 10, part 8: 34
 * §7, §8): the gate context, the plain layout's data, and reading a page's
 * answers from the POST body. Used by Game\InstrumentController,
 * QuestionnaireController, and RiskItemsController (all behind `gameSession`
 * and `instrumentGate`).
 */
trait InstrumentPages
{
    /** @var array<string, mixed>|null */
    private ?array $instrumentContext = null;

    /** @return array<string, mixed>|null the gate context of this request */
    protected function moduleContext(bool $fresh = false): ?array
    {
        if ($fresh) {
            service('instruments')->forget();
            $this->instrumentContext = null;
        }

        return $this->instrumentContext ??= service('instrumentGate')->context($this->participant(), $this->session());
    }

    /** Back to the module's next step (index decides). */
    protected function toModule(?string $notice = null): RedirectResponse
    {
        $redirect = redirect()->to(site_url('tes'));

        return $notice === null ? $redirect : $redirect->with('instrument_notice', $notice);
    }

    /**
     * A module page in the plain layout.
     *
     * @param array<string, mixed> $data
     */
    protected function page(string $view, string $bar, array $data = []): string
    {
        helper('instrument');

        return view($view, $data + [
            'bar'          => $bar,
            'researchCode' => $this->participant()->participant_code,
        ], ['saveData' => false]);
    }

    /** Top bar of an occasion and kind (34 §7.1). */
    protected function barFor(string $occasion, string $kind): string
    {
        return match ($kind) {
            'questionnaire' => lang('Instrument.barQuestionnaire'),
            'risk'          => lang('Instrument.barRisk'),
            default         => lang($occasion === 'post' ? 'Instrument.barPost' : 'Instrument.barPre'),
        };
    }

    /**
     * Raw responses of a page's items from the POST body, unit → value
     * (null = empty). Only choices of the data file are kept.
     *
     * @param list<array<string, mixed>> $items
     *
     * @return array<string, ?string>
     */
    protected function postedValues(array $items, array $meta): array
    {
        $posted  = (array) ($this->request->getPost('a') ?? []);
        $declined = (array) ($this->request->getPost('d') ?? []);
        $out     = [];

        foreach ($items as $item) {
            $show = $item['show'];

            switch ($item['type']) {
                case 'pg':
                case 'd2':
                    $value = (string) ($posted[$item['units'][0]] ?? '');
                    $out[$item['units'][0]] = array_key_exists($value, (array) $show['options']) ? $value : null;
                    break;

                case 'single':
                case 'likert4':
                    foreach ($item['units'] as $unit) {
                        $value      = is_scalar($posted[$unit] ?? null) ? (string) $posted[$unit] : '';
                        $options    = $item['type'] === 'likert4' ? (array) ($meta['choices'] ?? []) : (array) $show['options'];
                        $out[$unit] = $value !== '' && array_key_exists($value, $options) ? $value : null;
                    }
                    break;

                case 'multi':
                    if (! empty($declined[$item['code']])) {
                        // "Tidak mau menjawab" on a tick-all risk item: code 7 in every box (34 §8.1)
                        foreach ($item['units'] as $unit) {
                            $out[$unit] = '7';
                        }
                        break;
                    }

                    $any = false;

                    foreach ($item['units'] as $unit) {
                        $any = $any || ! empty($posted[$unit]);
                    }

                    // No box ticked: every column of the item stays empty (16 → Pemasukan data)
                    foreach ($item['units'] as $unit) {
                        $out[$unit] = $any ? (! empty($posted[$unit]) ? '1' : '0') : null;
                    }
                    break;

                case 'pgk':
                    $letters = array_keys((array) ($meta['scales'][$show['scale'] ?? 'bs'] ?? []));
                    $given   = (array) ($posted[$item['units'][0]] ?? []);
                    $value   = '';

                    foreach (array_keys((array) $show['statements']) as $no) {
                        $letter = (string) ($given[$no] ?? '');
                        $value .= in_array($letter, array_map('strval', $letters), true) ? $letter : '-';
                    }

                    $out[$item['units'][0]] = trim($value, '-') === '' ? null : $value;
                    break;

                case 'match':
                    $given = (array) ($posted[$item['units'][0]] ?? []);
                    $pairs = [];
                    $any   = false;

                    foreach (array_keys((array) $show['left']) as $no) {
                        $letter  = (string) ($given[$no] ?? '');
                        $ok      = array_key_exists($letter, (array) $show['right']);
                        $any     = $any || $ok;
                        $pairs[] = $no . ($ok ? $letter : '-');
                    }

                    $out[$item['units'][0]] = $any ? implode(' ', $pairs) : null;
                    break;
            }
        }

        return $out;
    }

    /** The validators' preview choice (34 §5.5). */
    protected function rememberPreview(string $occasion): void
    {
        session()->set(InstrumentGate::PREVIEW_KEY, ['occasion' => in_array($occasion, ['pre', 'post'], true) ? $occasion : 'pre']);
        service('instrumentGate')->forget();
        $this->instrumentContext = null;
    }
}
