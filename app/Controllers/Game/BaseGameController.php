<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Controllers\Concerns\GameProgress;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Entities\Participant;
use App\Services\GameContext;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Dasar controller area game: akses sesi/peserta request ini dan data HUD.
 *
 * Route di grup `gameSession` dijamin sudah memuat GameContext oleh filter,
 * sehingga session() dan participant() tidak perlu memeriksa ulang kepemilikan.
 */
abstract class BaseGameController extends BaseController
{
    use GameProgress;

    /** Kunci sesi PHP: dialog pembuka wilayah yang sudah tampil, "sesi:level" → true */
    private const DIALOGUE_SHOWN_KEY = 'dialogues_shown';

    protected function context(): GameContext
    {
        return service('gameContext');
    }

    protected function session(): GameSession
    {
        $session = $this->context()->session;

        if ($session === null) {
            throw new \RuntimeException('Sesi permainan belum dimuat; route ini harus memakai filter gameSession.');
        }

        return $session;
    }

    protected function participant(): Participant
    {
        $participant = $this->context()->participant;

        if ($participant === null) {
            throw new \RuntimeException('Peserta belum dimuat; route ini harus memakai filter gameSession.');
        }

        return $participant;
    }

    /**
     * Data yang dipakai HUD pada setiap layar dalam permainan.
     *
     * Peserta dikirim dalam bentuk aman (toSafeArray): `password_hash` tidak
     * pernah sampai ke view mana pun (aturan 11, 05_VIEW_UI.md).
     *
     * @return array<string, mixed>
     */
    protected function hudData(): array
    {
        $session = $this->session();

        return [
            'locale'      => $session->resolvedLocale(),
            'participant' => $this->participant()->toSafeArray(),
            'progress'    => $this->progressSummary($session),
            'lantern'     => $this->lanternMap($session),
        ];
    }

    /** Level menurut kode URL; 404 bila tidak ada atau tidak aktif. */
    protected function requireLevel(string $code): \App\Entities\Level
    {
        $level = service('contentRepository')->level($code);

        if ($level === null) {
            throw PageNotFoundException::forPageNotFound("Wilayah '{$code}' tidak ditemukan.");
        }

        return $level;
    }

    /**
     * Wilayah yang baru terbuka wajib lewat dialog pembukanya, juga bila
     * halaman di dalamnya (peta wilayah, kartu misi, tantangan) dibuka lewat
     * URL yang diketik langsung. Mengembalikan redirect ke `/dialog/{code}`
     * bila dialog wilayah itu belum tampil pada sesi login ini, atau null.
     *
     * Tanda "sudah tampil" disimpan di sesi PHP per sesi permainan, bukan di
     * database: setelah keluar-masuk lagi, wilayah yang masih baru terbuka
     * kembali menampilkan dialognya — sesuai aturan "dialog selalu muncul
     * ketika wilayah baru terbuka". Panggil setelah pemeriksaan kunci level.
     */
    protected function dialogueGate(GameSession $session, Level $level): ?RedirectResponse
    {
        $shown = (array) session(self::DIALOGUE_SHOWN_KEY);

        if (! empty($shown[$this->dialogueKey($session, $level)])) {
            return null;
        }

        $score = service('scoringService')->levelScore($session->id, $level->id);

        if (! $this->isNewRegion($this->levelStatus($score, true))) {
            return null;
        }

        return redirect()->to(site_url('dialog/' . $level->code));
    }

    /**
     * Cerita pembuka wajib ditonton sampai selesai sebelum peta Kedu terbuka,
     * juga bila `/peta` dibuka lewat URL yang diketik atau redirect setelah
     * login. Mengembalikan redirect ke `/intro` bila `intro_seen_at` peserta
     * masih kosong, atau null.
     */
    protected function introGate(): ?RedirectResponse
    {
        if ($this->participant()->hasSeenIntro()) {
            return null;
        }

        return redirect()->to(site_url('intro'));
    }

    /**
     * Masuk peta Kedu dengan flash `curtain=map`: layar tirai "Membuka Peta
     * Kedu" (tahap berikutnya) membacanya untuk menutupi pemuatan peta.
     */
    protected function toMap(): RedirectResponse
    {
        return redirect()->to(site_url('peta'))->with('curtain', 'map');
    }

    /** Dipanggil DialogueController saat dialog pembuka wilayah ditampilkan. */
    protected function markDialogueShown(GameSession $session, Level $level): void
    {
        $shown = (array) session(self::DIALOGUE_SHOWN_KEY);

        $shown[$this->dialogueKey($session, $level)] = true;

        session()->set(self::DIALOGUE_SHOWN_KEY, $shown);
    }

    private function dialogueKey(GameSession $session, Level $level): string
    {
        return $session->id . ':' . $level->id;
    }

    /** Node ke-$sequence pada level; 404 bila tidak ada. */
    protected function requireNode(int $levelId, int $sequence): \App\Entities\ChallengeNode
    {
        foreach (service('contentRepository')->nodesForLevel($levelId) as $node) {
            if ($node->sequence === $sequence) {
                return $node;
            }
        }

        throw PageNotFoundException::forPageNotFound("Tantangan ke-{$sequence} tidak ditemukan.");
    }
}
