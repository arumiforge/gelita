<?php

namespace App\Commands;

use App\Commands\Concerns\CliOptions;
use App\Libraries\StorySync as Sync;
use App\Models\AuditLogModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Updates the story lines (`dialogues`) of a server that already has content
 * to the current script, app/Database/Seeds/data/story.php. The seeder only
 * inserts missing lines, so without this command a revised script reaches a
 * running server only by retyping each line in the dialogue editor.
 *
 * A line is replaced only when it still holds the current script or an
 * earlier version listed in data/story-previous.php. Text edited in the panel
 * is reported and kept unless `--timpa` is given. Recordings are not touched;
 * the command lists replaced lines whose recording still speaks the old text.
 * Rules in App\Libraries\StorySync.
 */
class StorySync extends BaseCommand
{
    use CliOptions;

    protected $group       = 'GELITA';
    protected $name        = 'gelita:story:sync';
    protected $description = 'Memperbarui teks cerita dan dialog di basis data agar sama dengan naskah terbaru (data/story.php).';
    protected $usage       = 'gelita:story:sync [--dry-run] [--timpa]';
    protected $options     = [
        '--dry-run' => 'Tampilkan yang akan diubah tanpa menulis apa pun',
        '--timpa'   => 'Timpa juga baris yang sudah disunting di panel',
    ];

    public function run(array $params)
    {
        db_sync_timezone();

        $dryRun    = $this->flag('dry-run', $params);
        $overwrite = $this->flag('timpa', $params);

        if ($dryRun) {
            CLI::write('Uji coba (--dry-run): tidak ada yang ditulis.', 'yellow');
        }

        try {
            $sync = new Sync();
            $plan = $sync->plan();
        } catch (\Throwable $e) {
            CLI::error('Naskah belum dapat dibandingkan: ' . $e->getMessage());

            return EXIT_ERROR;
        }

        $by = [];

        foreach ($plan as $entry) {
            $by[$entry['status']][] = $entry;
        }

        $this->listCodes('Diperbarui ke naskah terbaru', $by['update'] ?? [], 'green');
        $this->listCodes('Ditambahkan (belum ada di basis data)', $by['missing'] ?? [], 'green');
        $this->listCodes(
            $overwrite ? 'Disunting di panel, ditimpa (--timpa)' : 'Disunting di panel, dibiarkan (pakai --timpa untuk menimpa)',
            $by['edited'] ?? [],
            'yellow',
        );
        $this->listCodes('Wilayahnya belum terpasang, dilewati', $by['no_level'] ?? [], 'yellow');

        CLI::write(sprintf(
            '%d baris naskah: %d sudah sama, %d diperbarui, %d ditambahkan, %d disunting di panel%s, %d dilewati.',
            count($plan),
            count($by['same'] ?? []),
            count($by['update'] ?? []),
            count($by['missing'] ?? []),
            count($by['edited'] ?? []),
            $overwrite ? ' (ditimpa)' : ' (dibiarkan)',
            count($by['no_level'] ?? []),
        ));

        $replaced = array_merge($by['update'] ?? [], $overwrite ? ($by['edited'] ?? []) : []);

        if ($dryRun) {
            $this->warnAudio(array_values(array_filter($replaced, static fn (array $e): bool => $e['has_audio'])));

            return EXIT_SUCCESS;
        }

        $result = $sync->apply($plan, $overwrite);

        if ($result['stale'] !== []) {
            CLI::write('Berubah saat perintah berjalan, tidak ditimpa: ' . implode(', ', $result['stale']), 'yellow');
        }

        $written = count($result['updated']) + count($result['overwritten']) + count($result['inserted']);

        CLI::write("{$written} baris ditulis.", $written > 0 ? 'green' : 'white');

        $this->warnAudio(array_values(array_filter(
            $replaced,
            static fn (array $e): bool => in_array($e['code'], $result['stale_audio'], true),
        )));

        if ($written > 0) {
            model(AuditLogModel::class)->record('story_sync', [
                'target_type' => 'dialogues',
                'target_id'   => 'naskah',
                'metadata'    => [
                    'via'            => 'cli',
                    'command'        => $this->name,
                    'updated'        => count($result['updated']),
                    'overwritten'    => count($result['overwritten']),
                    'inserted'       => count($result['inserted']),
                    'edited_skipped' => $overwrite ? 0 : count($by['edited'] ?? []),
                    'stale'          => count($result['stale']),
                ],
            ]);
        }

        return EXIT_SUCCESS;
    }

    /** @param list<array<string, mixed>> $entries */
    private function listCodes(string $label, array $entries, string $color): void
    {
        if ($entries === []) {
            return;
        }

        CLI::write($label . ' (' . count($entries) . '):', $color);
        CLI::write('  ' . implode(', ', array_column($entries, 'code')));
    }

    /** @param list<array<string, mixed>> $entries lines whose text changes while a recording stays linked */
    private function warnAudio(array $entries): void
    {
        if ($entries === []) {
            return;
        }

        CLI::write(count($entries) . ' baris yang teksnya berubah masih tertaut ke rekaman lama: ' . implode(', ', array_column($entries, 'code')), 'yellow');
        CLI::write('  Rekaman itu masih membacakan teks lama. Jangan pakai "Setujui semua rekaman" sebelum baris-baris ini direkam ulang.', 'yellow');
    }
}
