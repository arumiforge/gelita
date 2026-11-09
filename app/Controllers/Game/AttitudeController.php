<?php

namespace App\Controllers\Game;

use App\Libraries\InstrumentImporter;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * The attitude scale "Menjaga Warisan" in Balai Refleksi (research revision
 * 10, part 8: 34 §9; 17): instructions, the example `skp-ct-01`, twelve
 * statements one per screen in a fixed order, four choices without a middle,
 * Lewati and Kembali, the bronze progress bar, one send. Game layout, no
 * narration, no Mbah Kedu, no stars, no feedback. Server-rendered and saved
 * per screen like the test module (K6). Only in field mode (P5 and its
 * make-ups) or the validators' preview (K4, K12).
 */
class AttitudeController extends BaseGameController
{
    /** GET /refleksi/sikap */
    public function index(): string|RedirectResponse
    {
        [$sitting, $meta] = $this->openScale();

        if ($sitting === null) {
            return $meta;
        }

        return $this->screen('game/attitude', ['mode' => 'intro', 'meta' => $meta]);
    }

    /** GET /refleksi/sikap/contoh */
    public function example(): string|RedirectResponse
    {
        return $this->showItem(0);
    }

    /** GET /refleksi/sikap/{n} */
    public function item(int $no): string|RedirectResponse
    {
        return $this->showItem($no);
    }

    /** POST /refleksi/sikap/{n}: `lanjut` saves the choice, `lewati` empties it, `kembali` goes back */
    public function saveItem(int $no): RedirectResponse
    {
        [$sitting, $meta] = $this->openScale();

        if ($sitting === null) {
            return $meta;
        }

        $item = $this->itemAt($sitting, $no);

        if ($item === null) {
            return redirect()->to(site_url('refleksi/sikap'));
        }

        $action = (string) $this->request->getPost('aksi');
        $value  = (string) $this->request->getPost('pilihan');
        $value  = in_array($value, InstrumentImporter::ATTITUDE_CODES, true) ? $value : null;
        $part   = service('instruments')->part((int) $sitting['id'], 'sikap');
        $values = [$item['units'][0] => $action === 'lewati' ? null : $value];

        if ($action !== 'kembali' || $value !== null) {
            service('instruments')->save($sitting, $part, $values);
        }

        if ($action === 'kembali') {
            return redirect()->to(site_url($no <= 0 ? 'refleksi/sikap' : ($no === 1 ? 'refleksi/sikap/contoh' : 'refleksi/sikap/' . ($no - 1))));
        }

        return redirect()->to(site_url($no >= 12 ? 'refleksi/sikap/kirim' : 'refleksi/sikap/' . ($no + 1)));
    }

    /** GET /refleksi/sikap/kirim */
    public function review(): string|RedirectResponse
    {
        [$sitting, $meta] = $this->openScale();

        if ($sitting === null) {
            return $meta;
        }

        $values   = $this->values($sitting);
        $answered = 0;

        for ($i = 1; $i <= 12; $i++) {
            $answered += ($values[sprintf('skp_%02d', $i)] ?? null) === null ? 0 : 1;
        }

        return $this->screen('game/attitude', ['mode' => 'review', 'meta' => $meta, 'answered' => $answered]);
    }

    /** POST /refleksi/sikap/kirim: one send, then Balai Refleksi (17 → Diisi ulang). */
    public function submit(): RedirectResponse
    {
        [$sitting, $meta] = $this->openScale();

        if ($sitting === null) {
            return $meta;
        }

        service('instruments')->submitSitting($sitting);

        return redirect()->to(site_url('refleksi'));
    }

    // ------------------------------------------------------------ internal

    private function showItem(int $no): string|RedirectResponse
    {
        [$sitting, $meta] = $this->openScale();

        if ($sitting === null) {
            return $meta;
        }

        $item = $this->itemAt($sitting, $no);

        if ($item === null) {
            return redirect()->to(site_url('refleksi/sikap'));
        }

        service('instruments')->markShown($sitting, $item['units'], $item['id']);

        return $this->screen('game/attitude', [
            'mode' => 'item', 'meta' => $meta, 'item' => $item, 'no' => $no,
            'value' => $this->values($sitting)[$item['units'][0]] ?? null,
        ]);
    }

    /**
     * The open attitude sitting and its meta, or [null, redirect] when the
     * scale is not open for the student (34 §9.1).
     *
     * @return array{0: ?array, 1: mixed}
     */
    private function openScale(): array
    {
        $session = $this->session();

        if (! $this->allNodesCompleted($session)) {
            return [null, redirect()->to(site_url('peta'))->with('error', lang('Game.reflectionLocked'))];
        }

        $state = service('instrumentGate')->reflectionState($this->participant(), $session);

        if (($state['state'] ?? 'open') !== 'scale') {
            return [null, redirect()->to(site_url('refleksi'))];
        }

        try {
            $sitting = service('instruments')->openAttitude($state['context']);
        } catch (\DomainException) {
            return [null, redirect()->to(site_url('peta'))->with('error', lang('Game.reflectionLocked'))];
        }

        return [$sitting, service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta']];
    }

    /** Item `skp-ct-01` at 0, `skp-01`–`skp-12` at 1–12. */
    private function itemAt(array $sitting, int $no): ?array
    {
        foreach (service('instruments')->items($sitting, 'sikap') as $item) {
            if ((int) $item['position'] === $no) {
                return $item;
            }
        }

        return null;
    }

    /** @return array<string, ?string> */
    private function values(array $sitting): array
    {
        return array_column(service('instruments')->responses((int) $sitting['id']), 'response', 'unit');
    }

    /** @param array<string, mixed> $data */
    private function screen(string $view, array $data): string
    {
        return view($view, $this->hudData() + $data);
    }
}
