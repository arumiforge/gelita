<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Menyinkronkan tabel `dialogues` dengan data cerita
 * (app/Database/Seeds/data/story.php, salinan docs/naskah-cerita.md).
 * Dipakai `php spark gelita:story:update` pada server yang sudah berjalan.
 *
 * Aturan per baris naskah, dicocokkan lewat (level, konteks, urutan):
 *
 * - belum ada di tabel → disisipkan (aktif);
 * - teksnya masih teks seeder lama (data/story-legacy.php) → diperbarui
 *   seluruhnya (tokoh, pose, efek, judul, teks);
 * - teksnya sudah sama dengan naskah → tokoh, pose, efek, atau judul yang
 *   masih kosong diisi (mis. pose/efek setelah migration 003700). Nilai lain
 *   yang berbeda dari naskah dianggap suntingan admin;
 * - teksnya lain lagi, atau tokoh/pose/efek/judulnya disunting = suntingan
 *   admin → dilewati dan dilaporkan, kecuali $force.
 *
 * Bila naskah diubah lagi kelak, teks versi sebelumnya perlu ditambahkan ke
 * data/story-legacy.php; tanpa itu baris lama terbaca sebagai suntingan admin.
 *
 * Kolom audio dan latar (`audio_id_asset_id`, `audio_en_asset_id`,
 * `background_media_id`) tidak pernah disentuh, begitu juga `is_active`
 * baris yang diperbarui. Baris di luar jumlah naskah pada kelompok yang
 * sama (urutan lebih besar) dinonaktifkan, tidak dihapus: rekaman audio
 * dan riwayat event yang merujuknya tetap utuh.
 *
 * level_id NULL tidak dijaga UNIQUE oleh MySQL, jadi baris global bernomor
 * urut ganda mungkin ada. Yang dipakai adalah id terkecil; sisanya
 * dinonaktifkan agar cerita tidak tampil dua kali.
 */
final class StorySync
{
    private BaseConnection $db;

    /** @var list<array<string, mixed>> */
    private array $story;

    /** @var array<string, array{0: string, 1: string}> */
    private array $legacy;

    /**
     * @param list<array<string, mixed>>|null                  $story  bawaan: data/story.php
     * @param array<string, array{0: string, 1: string}>|null  $legacy bawaan: data/story-legacy.php
     */
    public function __construct(?BaseConnection $db = null, ?array $story = null, ?array $legacy = null)
    {
        $this->db     = $db ?? db_connect();
        $this->story  = $story ?? self::storyData();
        $this->legacy = $legacy ?? require APPPATH . 'Database/Seeds/data/story-legacy.php';
    }

    /** @return list<array<string, mixed>> */
    public static function storyData(): array
    {
        return require APPPATH . 'Database/Seeds/data/story.php';
    }

    /**
     * @return array{
     *     inserted: list<string>, updated: list<string>, unchanged: list<string>,
     *     skipped: list<string>, deactivated: list<string>, missing_levels: list<string>
     * } label baris, mis. `intro #3` atau `magelang/level_open #9`
     */
    public function run(bool $force = false, bool $dryRun = false): array
    {
        $report = [
            'inserted'       => [],
            'updated'        => [],
            'unchanged'      => [],
            'skipped'        => [],
            'deactivated'    => [],
            'missing_levels' => [],
        ];

        $levels = [];

        foreach ($this->db->table('levels')->select('id, code')->get()->getResultArray() as $row) {
            $levels[(string) $row['code']] = (int) $row['id'];
        }

        $groups = [];

        foreach ($this->story as $line) {
            $code = $line['level'];

            if ($code !== null && ! isset($levels[$code])) {
                $report['missing_levels'][$code] = $code;

                continue;
            }

            $groups[($code ?? '_') . '|' . $line['context']][] = $line;
        }

        $report['missing_levels'] = array_values($report['missing_levels']);

        if (! $dryRun) {
            $this->db->transBegin();
        }

        try {
            foreach ($groups as $lines) {
                $levelCode = $lines[0]['level'];
                $levelId   = $levelCode === null ? null : $levels[$levelCode];
                $context   = (string) $lines[0]['context'];
                $existing  = $this->existing($levelId, $context);

                foreach ($lines as $line) {
                    $this->syncLine($line, $levelId, $existing[(int) $line['sequence']] ?? [], $force, $dryRun, $report);
                }

                $this->deactivateBeyond($existing, count($lines), $levelCode, $context, $dryRun, $report);
            }

            if (! $dryRun) {
                $this->db->transCommit();
            }
        } catch (\Throwable $e) {
            if (! $dryRun) {
                $this->db->transRollback();
            }

            throw $e;
        }

        return $report;
    }

    /**
     * Baris kelompok (level, konteks) per nomor urut, id terkecil dulu.
     *
     * @return array<int, list<array<string, mixed>>>
     */
    private function existing(?int $levelId, string $context): array
    {
        $rows = $this->db->table('dialogues')
            ->where('level_id', $levelId)
            ->where('context_code', $context)
            ->orderBy('sequence', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $bySequence = [];

        foreach ($rows as $row) {
            $bySequence[(int) $row['sequence']][] = $row;
        }

        return $bySequence;
    }

    /**
     * @param array<string, mixed>       $line
     * @param list<array<string, mixed>> $rows baris yang sudah ada pada posisi ini
     * @param array<string, list<string>> $report
     */
    private function syncLine(array $line, ?int $levelId, array $rows, bool $force, bool $dryRun, array &$report): void
    {
        $label = $this->label($line['level'], (string) $line['context'], (int) $line['sequence']);
        $data  = $this->storyColumns($line);

        if ($rows === []) {
            if (! $dryRun) {
                $this->db->table('dialogues')->insert([
                    'level_id'     => $levelId,
                    'context_code' => $line['context'],
                    'sequence'     => (int) $line['sequence'],
                    'is_active'    => 1,
                ] + $data);
            }

            $report['inserted'][] = $label;

            return;
        }

        $row = array_shift($rows);

        // Nomor urut ganda pada baris global: cukup satu yang tampil
        foreach ($rows as $duplicate) {
            if ((int) $duplicate['is_active'] === 1) {
                $this->deactivate((int) $duplicate['id'], $dryRun);
                $report['deactivated'][] = $label . ' (nomor urut ganda, id ' . $duplicate['id'] . ')';
            }
        }

        $current = [$this->normalize($row['text_id'] ?? ''), $this->normalize($row['text_en'] ?? '')];
        $story   = [$this->normalize($line['text_id']), $this->normalize($line['text_en'])];
        $legacy  = $this->legacy[($line['level'] ?? '_') . '|' . $line['context'] . '|' . $line['sequence']] ?? null;
        $isOld   = $legacy !== null && $current === [$this->normalize($legacy[0]), $this->normalize($legacy[1])];

        if ($current !== $story && ! $isOld && ! $force) {
            $report['skipped'][] = $label;

            return;
        }

        $changes = [];
        $edited  = false;

        foreach ($data as $column => $value) {
            $old = $row[$column] ?? null;
            $old = $old === null || $old === '' ? null : (string) $old;

            if (in_array($column, ['text_id', 'text_en'], true)) {
                if ($this->normalize((string) $old) !== $this->normalize((string) $value)) {
                    $changes[$column] = $value;
                }
            } elseif ($old !== $value) {
                // Teks naskah + kolom lain yang sudah terisi berbeda = suntingan admin
                $edited = $edited || ($current === $story && $old !== null);
                $changes[$column] = $value;
            }
        }

        if ($edited && ! $isOld && ! $force) {
            $report['skipped'][] = $label;

            return;
        }

        if ($changes === []) {
            $report['unchanged'][] = $label;

            return;
        }

        if (! $dryRun) {
            $this->db->table('dialogues')->where('id', (int) $row['id'])->update($changes);
        }

        $report['updated'][] = $label;
    }

    /**
     * Baris bernomor urut di atas jumlah naskah → nonaktif.
     *
     * @param array<int, list<array<string, mixed>>> $existing
     * @param array<string, list<string>>             $report
     */
    private function deactivateBeyond(array $existing, int $count, ?string $levelCode, string $context, bool $dryRun, array &$report): void
    {
        foreach ($existing as $sequence => $rows) {
            if ($sequence <= $count) {
                continue;
            }

            foreach ($rows as $row) {
                if ((int) $row['is_active'] === 1) {
                    $this->deactivate((int) $row['id'], $dryRun);
                    $report['deactivated'][] = $this->label($levelCode, $context, $sequence) . ' (di luar jumlah baris naskah)';
                }
            }
        }
    }

    private function deactivate(int $id, bool $dryRun): void
    {
        if (! $dryRun) {
            $this->db->table('dialogues')->where('id', $id)->update(['is_active' => 0]);
        }
    }

    /**
     * @param array<string, mixed> $line
     *
     * @return array<string, ?string>
     */
    private function storyColumns(array $line): array
    {
        return [
            'character_code' => (string) $line['character'],
            'pose'           => $line['pose'],
            'effect'         => $line['effect'],
            'title_id'       => $line['title_id'],
            'title_en'       => $line['title_en'],
            'text_id'        => (string) $line['text_id'],
            'text_en'        => (string) $line['text_en'],
        ];
    }

    /** Suntingan lewat textarea bisa menambah CRLF atau spasi di ujung: bukan perubahan isi. */
    private function normalize(string $text): string
    {
        return trim(str_replace("\r\n", "\n", $text));
    }

    private function label(?string $levelCode, string $context, int $sequence): string
    {
        return ($levelCode === null ? '' : $levelCode . '/') . $context . ' #' . $sequence;
    }
}
