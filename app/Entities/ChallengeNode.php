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

    /**
     * Konfigurasi bawaan per engine (perombakan kelas 5). Setiap pengerjaan
     * memakai satu variasi soal (`selection = variant`); aturan per tingkat
     * (ukuran papan, jumlah pengecoh) dibaca dari App\Libraries\DifficultyRules.
     */
    public static function defaultConfig(string $engine): array
    {
        return match ($engine) {
            'rumpang' => [
                'selection'        => 'variant',
                'variants'         => [],
                'as_reading'       => true,
                'use_word_bank'    => true,
                'distractor_count' => 0,
            ],
            'boleh' => [
                'selection'       => 'variant',
                'variants'        => [],
                'verdict_options' => ['benar', 'salah'],
            ],
            default => ['selection' => 'variant', 'variants' => []],
        };
    }

    /**
     * Isi Rumpang sebagai satu bacaan: semua soal variasi tampil berurutan
     * (setiap soal = satu potongan bacaan dengan satu rumpang); bank kata
     * berisi semua jawaban + kata pengecoh variasi itu.
     */
    public function isReading(): bool
    {
        return $this->engine_type === 'rumpang' && (bool) $this->config('as_reading');
    }

    /** Pilihan penilaian engine `boleh`: selalu Benar / Salah. */
    public function verdictOptions(): array
    {
        return ['benar', 'salah'];
    }

    /**
     * Pengaturan per variasi dari config_json['variants'].
     *
     * @return array<string, array<string, mixed>> keyed by variant_group
     */
    public function variants(): array
    {
        $out = [];

        foreach ((array) $this->config('variants', []) as $group => $entry) {
            if (is_array($entry) && trim((string) $group) !== '') {
                $out[(string) $group] = $entry;
            }
        }

        return $out;
    }

    /**
     * Judul tampil satu variasi; jatuh ke judul node bila variasi tidak
     * memberi judul pada bahasa itu.
     */
    public function variantTitle(?string $group, string $locale): string
    {
        $entry = $group === null ? [] : ($this->variants()[$group] ?? []);
        $title = trim((string) ($entry['title_' . $locale] ?? ''));

        if ($title === '') {
            $title = trim((string) ($entry['title_id'] ?? ''));
        }

        return $title !== '' ? $title : $this->text('title', $locale);
    }

    /**
     * Pengecoh bank kata Isi Rumpang satu variasi, dari
     * config_json['variants'][$group]['distractors'].
     *
     * @return list<string> teks pengecoh pada locale yang diminta
     */
    public function distractors(string $locale, ?string $group = null): array
    {
        if ($group === null) {
            return [];
        }

        $out = [];

        foreach ((array) ($this->variants()[$group]['distractors'] ?? []) as $entry) {
            if (is_string($entry)) {
                if (trim($entry) !== '') {
                    $out[] = $entry;
                }

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
