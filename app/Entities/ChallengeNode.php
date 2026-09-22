<?php

namespace App\Entities;

use App\Entities\Traits\Bilingual;
use CodeIgniter\Entity\Entity;

class ChallengeNode extends Entity
{
    use Bilingual;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                  => 'int',
        'level_id'            => 'int',
        'sequence'            => 'int',
        'indicator_id'        => '?int',
        'scoring_profile_id'  => '?int',
        'background_media_id' => '?int',
        'scene_media_id'      => '?int',
        'audio_intro_id'      => '?int',
        'audio_intro_en_id'   => '?int',
        'config_json'         => '?json-array',
        'map_x'               => 'float',
        'map_y'               => 'float',
        'is_active'           => 'boolean',
    ];

    /** Nilai config dengan default per engine */
    public function config(string $key, $default = null)
    {
        $cfg = $this->config_json ?: [];

        if (array_key_exists($key, $cfg)) {
            return $cfg[$key];
        }

        return $default ?? (self::defaultConfig((string) $this->engine_type)[$key] ?? null);
    }

    public static function defaultConfig(string $engine): array
    {
        return match ($engine) {
            'puzzle'  => ['items_per_round' => 1, 'grid' => 3, 'allow_retry' => true, 'mode' => 'arrange'],
            'rumpang' => [
                'items_per_round'  => 4,
                'use_word_bank'    => true,
                'distractor_count' => 2,
                'allow_retry'      => true,
                'distractors'      => [],
            ],
            'boleh' => [
                'items_per_round' => 8,
                'allow_retry'     => true,
                'verdict_options' => ['benar', 'salah'],
                'require_reason'  => false,
            ],
            'pilihan' => ['items_per_round' => 3, 'allow_retry' => false, 'shuffle_options' => true],
            'cari'    => ['items_per_round' => 4, 'allow_retry' => true, 'show_decoys' => true],
            default   => ['items_per_round' => 1, 'allow_retry' => true],
        };
    }

    public function itemsPerRound(): int
    {
        return max(1, (int) $this->config('items_per_round'));
    }

    public function allowsRetry(): bool
    {
        return (bool) $this->config('allow_retry');
    }

    /** Pilihan penilaian untuk engine `boleh` (Benar / Salah / Pendapat) */
    public function verdictOptions(): array
    {
        $options = $this->config('verdict_options');

        return is_array($options) && $options !== [] ? array_values($options) : ['benar', 'salah'];
    }

    public function requiresReason(): bool
    {
        return (bool) $this->config('require_reason');
    }

    /**
     * Pengecoh bank kata engine `rumpang`, dari config_json['distractors'].
     *
     * @return list<string> teks pengecoh pada locale yang diminta
     */
    public function distractors(string $locale): array
    {
        $out = [];

        foreach ((array) $this->config('distractors', []) as $entry) {
            if (is_string($entry)) {
                $out[] = $entry;

                continue;
            }

            if (! is_array($entry)) {
                continue;
            }

            $text = (string) ($entry[$locale] ?? $entry['text_' . $locale] ?? '');

            if (trim($text) === '') {
                $text = (string) ($entry['id'] ?? $entry['text_id'] ?? '');
            }

            if (trim($text) !== '') {
                $out[] = $text;
            }
        }

        return $out;
    }
}
