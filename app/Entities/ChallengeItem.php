<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use App\Libraries\PuzzleCut;
use CodeIgniter\Entity\Entity;

class ChallengeItem extends Entity
{
    use Bilingual;

    /** Kunci config_json yang boleh dikirim ke browser */
    private const SAFE_CONFIG_KEYS = ['x', 'y', 'w', 'grid', 'shape'];

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                 => 'int',
        'challenge_node_id'  => 'int',
        'sequence'           => 'int',
        'variant_group'      => '?string',
        'passage_id'         => '?int',
        'answer_key_json'    => '?json-array',
        'config_json'        => '?json-array',
        'media_asset_id'     => '?int',
        'audio_prompt_id'    => '?int',
        'audio_prompt_en_id' => '?int',
        'indicator_id'       => '?int',
        'scorable'           => 'boolean',
        'is_active'          => 'boolean',
    ];

    /**
     * Akses answer_key_json. Tanpa argumen → seluruh kunci.
     * TIDAK BOLEH dikirim ke browser.
     */
    public function answerKey(?string $key = null)
    {
        $answer = $this->answer_key_json ?: [];

        return $key === null ? $answer : ($answer[$key] ?? null);
    }

    public function conf(string $key, $default = null)
    {
        $config = $this->config_json ?: [];

        return $config[$key] ?? $default;
    }

    /** Objek jebakan pada engine `cari` — tidak dihitung sebagai target */
    public function isDecoy(): bool
    {
        return $this->conf('decoy') === true;
    }

    /** Posisi & ukuran objek pada engine `cari`, dalam persen */
    public function position(): array
    {
        return [
            'x' => (float) $this->conf('x', 50),
            'y' => (float) $this->conf('y', 50),
            'w' => (float) $this->conf('w', 12),
        ];
    }

    /**
     * Id audio narasi petunjuk (engine `cari`) pada bahasa itu; tidak jatuh
     * ke rekaman bahasa lain, karena suaranya harus sama dengan teks yang tampil.
     */
    public function promptAudioId(string $locale): ?int
    {
        $id = $locale === 'en' ? $this->audio_prompt_en_id : $this->audio_prompt_id;

        return $id === null || (int) $id <= 0 ? null : (int) $id;
    }

    /** Kunci verdict untuk verdict_card (`benar` | `salah`) */
    public function verdict(): ?string
    {
        $verdict = $this->answerKey('verdict');

        return $verdict === null ? null : mb_strtolower(trim((string) $verdict));
    }

    /**
     * Ukuran papan dan bentuk keping Susun Gambar (PuzzleCut::resolve):
     * isian soal, lalu √(panjang kunci), lalu $nodeGrid (ukuran lama
     * tantangan), lalu aturan tingkat wilayah.
     *
     * @return array{grid: int, shape: string}
     */
    public function puzzleLayout(?string $difficulty = null, mixed $nodeGrid = null): array
    {
        $order = $this->answerKey('order');

        return PuzzleCut::resolve($this->config_json ?: [], is_array($order) ? $order : null, $nodeGrid, $difficulty);
    }

    /** Umpan balik saat objek salah diklik — dikirim SETELAH klik, bukan di payload soal */
    public function wrongFeedback(string $locale): ?string
    {
        $text = (string) $this->conf('wrong_feedback_' . $locale, '');

        if (trim($text) === '') {
            $text = (string) $this->conf('wrong_feedback_id', '');
        }

        return trim($text) === '' ? null : $text;
    }

    /** Hanya kunci tampilan; `decoy`, `wrong_feedback_*`, dan kunci lain tetap di server. */
    public function safeConfig(): array
    {
        $config = $this->config_json ?: [];
        $safe   = [];

        foreach (self::SAFE_CONFIG_KEYS as $key) {
            if (array_key_exists($key, $config)) {
                $safe[$key] = $config[$key];
            }
        }

        return $safe;
    }

    /** Bentuk aman untuk dikirim ke browser: TANPA answer_key_json */
    public function toPlayerArray(string $locale): array
    {
        return [
            'id'               => $this->id,
            'item_key'         => $this->item_key,
            'interaction_type' => $this->interaction_type,
            'prompt'           => $this->text('prompt', $locale),
            'source_text'      => $this->text('source_text', $locale),
            'passage_id'       => $this->passage_id,
            'media'            => $this->media_asset_id ? media_src($this->media_asset_id) : null,
            // Susun Gambar: ukuran papan & bentuk keping selalu ikut (bukan rahasia)
            'config'           => $this->interaction_type === 'puzzle_arrange'
                ? $this->puzzleLayout() + $this->safeConfig()
                : $this->safeConfig(),
        ];
    }
}
