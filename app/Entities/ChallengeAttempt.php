<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Satu pengerjaan tantangan (Dok 1 §4): satu variasi soal, dimainkan dari awal
 * sampai selesai atau ditinggalkan.
 */
class ChallengeAttempt extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];
    protected $casts   = [
        'id'                     => 'int',
        'session_id'             => 'int',
        'challenge_node_id'      => 'int',
        'attempt_no'             => 'int',
        'variant_group'          => '?string',
        'selected_item_ids_json' => 'json-array',
        'scorable_items'         => 'int',
        'first_pass_correct'     => 'int',
        'final_correct'          => 'int',
        'check_count'            => 'int',
        'max_tries'              => 'int',
        'hint_count'             => 'int',
        'answer_change_count'    => 'int',
        'audio_use_count'        => 'int',
        'stars'                  => 'int',
        'duration_ms'            => '?int',
    ];

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /** @return list<int> */
    public function selectedItemIds(): array
    {
        return array_map('intval', $this->selected_item_ids_json ?: []);
    }

    public function hasItem(int $itemId): bool
    {
        return in_array($itemId, $this->selectedItemIds(), true);
    }

    /**
     * Persentase soal yang Berhasil pada Percobaan Pertama — hanya untuk
     * tampilan siswa (Balai Refleksi), bukan angka penelitian.
     */
    public function firstPassPercent(): float
    {
        $scorable = (int) $this->scorable_items;

        return $scorable > 0 ? round(100 * (int) $this->first_pass_correct / $scorable, 1) : 0.0;
    }

    /** Batas percobaan per soal yang berlaku saat pengerjaan dibuat. */
    public function maxTries(): int
    {
        return (int) ($this->attributes['max_tries'] ?? config('Gelita')->maxTries);
    }

    /** Sisa kesempatan pengerjaan batch (dihitung dari tekan "Cek jawabanku"). */
    public function triesLeft(): int
    {
        return max(0, $this->maxTries() - (int) $this->check_count);
    }

    /** Semua kesempatan pengerjaan batch sudah terpakai. */
    public function isExhausted(): bool
    {
        return $this->triesLeft() === 0;
    }
}
