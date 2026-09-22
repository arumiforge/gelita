<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use CodeIgniter\Entity\Entity;

class ChallengeItem extends Entity
{
    use Bilingual;

    /** Kunci config_json yang boleh dikirim ke browser */
    private const SAFE_CONFIG_KEYS = ['x', 'y', 'w', 'grid'];

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                => 'int',
        'challenge_node_id' => 'int',
        'sequence'          => 'int',
        'passage_id'        => '?int',
        'answer_key_json'   => '?json-array',
        'config_json'       => '?json-array',
        'media_asset_id'    => '?int',
        'indicator_id'      => '?int',
        'scorable'          => 'boolean',
        'is_active'         => 'boolean',
    ];

    /** Opsi jawaban yang di-eager-load ContentRepository (hindari N+1) */
    protected array $eagerOptions = [];

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
     * Jawaban yang diterima untuk fill_blank_free — sudah lowercase + trim.
     * Selalu menyertakan `accept_id` agar jawaban bahasa Indonesia tetap diterima.
     *
     * @return list<string>
     */
    public function acceptedAnswers(string $locale): array
    {
        $accepted = array_merge(
            (array) ($this->answerKey('accept_' . $locale) ?? []),
            (array) ($this->answerKey('accept_id') ?? []),
        );

        $normalized = [];

        foreach ($accepted as $value) {
            $value = mb_strtolower(trim((string) $value));

            if ($value !== '') {
                $normalized[$value] = true;
            }
        }

        return array_keys($normalized);
    }

    /** Kunci verdict untuk verdict_card / verdict_reason */
    public function verdict(): ?string
    {
        $verdict = $this->answerKey('verdict');

        return $verdict === null ? null : mb_strtolower(trim((string) $verdict));
    }

    /**
     * Potongan langkah untuk interaction_type `ordering`.
     *
     * @return list<array{key: string, text: string}>
     */
    public function pieces(string $locale): array
    {
        $out = [];

        foreach ((array) $this->conf('pieces', []) as $piece) {
            if (! is_array($piece)) {
                continue;
            }

            $text = (string) ($piece['text_' . $locale] ?? '');

            if (trim($text) === '') {
                $text = (string) ($piece['text_id'] ?? '');
            }

            $out[] = ['key' => (string) ($piece['key'] ?? count($out)), 'text' => $text];
        }

        return $out;
    }

    /** Potongan `ordering` dalam urutan acak — urutan kunci TIDAK ikut dikirim */
    public function shuffledPieces(string $locale): array
    {
        $pieces = $this->pieces($locale);

        return $pieces === [] ? [] : seeded_shuffle($pieces, $this->item_key . '|pieces');
    }

    /**
     * Sumber pembanding (Wonosobo node 3).
     *
     * @return list<array{label: string, kind: string, text: string}>
     */
    public function sources(string $locale): array
    {
        $out = [];

        foreach ((array) $this->conf('sources', []) as $source) {
            if (! is_array($source)) {
                continue;
            }

            $label = (string) ($source['label_' . $locale] ?? '');
            $text  = (string) ($source['text_' . $locale] ?? '');

            $out[] = [
                'label' => trim($label) !== '' ? $label : (string) ($source['label_id'] ?? ''),
                'kind'  => (string) ($source['kind'] ?? 'unknown'),
                'text'  => trim($text) !== '' ? $text : (string) ($source['text_id'] ?? ''),
            ];
        }

        return $out;
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

    /** Pilar literasi digital (Wonosobo node 5) — tetap di server, dipakai analitik */
    public function digitalPillar(): ?string
    {
        $pillar = (string) $this->conf('digital_pillar', '');

        return $pillar === '' ? null : $pillar;
    }

    /** Hanya kunci tampilan; `decoy`, `wrong_feedback_*`, `digital_pillar` tetap di server. */
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

    /** @param list<ChallengeOption> $options */
    public function setLoadedOptions(array $options): static
    {
        $this->eagerOptions = $options;

        return $this;
    }

    /** @return list<ChallengeOption> */
    public function loadedOptions(): array
    {
        return $this->eagerOptions;
    }

    /** Kunci opsi yang benar, diambil dari answer_key_json atau dari opsi is_correct */
    public function correctOptionKey(): ?string
    {
        $key = $this->answerKey('option_key');

        if ($key !== null && trim((string) $key) !== '') {
            return (string) $key;
        }

        foreach ($this->eagerOptions as $option) {
            if ($option->is_correct) {
                return (string) $option->option_key;
            }
        }

        return null;
    }

    /** Bentuk aman untuk dikirim ke browser: TANPA answer_key_json */
    public function toPlayerArray(string $locale): array
    {
        $options = [];

        foreach ($this->eagerOptions as $option) {
            $options[] = $option->toPlayerArray($locale);
        }

        return [
            'id'               => $this->id,
            'item_key'         => $this->item_key,
            'interaction_type' => $this->interaction_type,
            'prompt'           => $this->text('prompt', $locale),
            'source_text'      => $this->text('source_text', $locale),
            'passage_id'       => $this->passage_id,
            'media'            => $this->media_asset_id ? media_src($this->media_asset_id) : null,
            'options'          => $options ?: null,
            'pieces'           => $this->interaction_type === 'ordering' ? $this->shuffledPieces($locale) : null,
            'sources'          => $this->sources($locale) ?: null,
            'config'           => $this->safeConfig(),
        ];
    }
}
