<?php

namespace App\Exceptions;

/**
 * Permintaan tantangan yang melanggar aturan permainan (Dok 2 §5.2) —
 * bukan galat sistem. Membawa kode galat API dan status HTTP-nya, sehingga
 * controller cukup meneruskannya sebagai respons gagal.
 */
final class ChallengeRuleException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $extra tambahan respons, mis. daftar soal yang kosong
     */
    public function __construct(
        public readonly string $errorCode,
        public readonly int $status,
        string $message,
        public readonly array $extra = [],
    ) {
        parent::__construct($message);
    }

    /**
     * Semua kesempatan pengerjaan batch sudah terpakai (409). Bila
     * pengerjaannya sudah ditutup, `redirect` menunjuk layar hasilnya.
     */
    public static function triesExhausted(?string $redirect = null): self
    {
        return new self(
            'TRIES_EXHAUSTED',
            409,
            'Semua kesempatan untuk tantangan ini sudah terpakai.',
            $redirect === null ? [] : ['redirect' => $redirect],
        );
    }

    /**
     * Ada soal yang belum benar tetapi tidak dijawab pada cek ini (422).
     *
     * @param list<int> $itemIds
     */
    public static function incomplete(array $itemIds): self
    {
        return new self('INCOMPLETE', 422, 'Masih ada soal yang belum diisi.', ['items' => array_values($itemIds)]);
    }

    /** Petunjuk Cari Objek sudah ditemukan atau dilewati (409). */
    public static function clueClosed(): self
    {
        return new self('CLUE_CLOSED', 409, 'Petunjuk ini sudah selesai.');
    }

    /**
     * Permintaan kalah balapan (409): tab lain atau permintaan yang dikirim
     * ulang lebih dulu mengubah pengerjaan ini, jadi permintaan ini tidak
     * diterapkan. Bila pengerjaannya sudah ditutup, `redirect` menunjuk
     * layar hasilnya; selain itu klien memuat ulang.
     */
    public static function attemptChanged(?string $redirect = null): self
    {
        return new self(
            'ATTEMPT_CHANGED',
            409,
            'Tantangan ini baru saja diperbarui dari tab atau permintaan lain.',
            $redirect === null ? [] : ['redirect' => $redirect],
        );
    }

    /** Pengerjaan belum boleh ditutup: masih ada kesempatan dan belum semua benar (409). */
    public static function triesRemaining(): self
    {
        return new self('TRIES_REMAINING', 409, 'Tantangan ini belum selesai: masih ada kesempatan untuk memperbaiki jawaban.');
    }

    // Research revision 10, part 2 (28 K17)

    /** The challenge before this one in its region is not completed yet (409). */
    public static function nodeLocked(): self
    {
        return new self('NODE_LOCKED', 409, lang('Game.nodeLocked'));
    }

    /**
     * Research mode is on, the study is active, and the student has no seat
     * and booklet yet: a scored challenge does not open (409, 31 K10). The
     * player sees Game.bookletMissing and goes back to the region map.
     */
    public static function bookletMissing(): self
    {
        return new self('BOOKLET_MISSING', 409, 'Siswa ini belum punya buklet, jadi tantangan yang dinilai belum dibuka.');
    }

    // Part 7 (33 §7.4)

    /**
     * Research mode: a completed challenge opens again only after every
     * challenge of its region is completed in a meeting that opens the
     * region (409). The player sees Game.retakeLater.
     */
    public static function retakeLocked(): self
    {
        return new self('RETAKE_LOCKED', 409, lang('Game.retakeLater'));
    }
}
