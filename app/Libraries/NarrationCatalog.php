<?php

namespace App\Libraries;

use App\Models\AuditLogModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Status rekaman narasi seluruh baris naskah: halaman admin Narasi, daftar
 * rekaman untuk pengisi suara, dan persetujuan massal.
 *
 * "Audio narasi" = audio yang ditautkan ke baris `dialogues` keenam konteks
 * naskah, atau yang diimpor NarrationImporter (asset_key
 * `audio.narasi.{locale}.*`). Audio lain (mis. narasi pembuka kartu misi)
 * tidak pernah ikut persetujuan massal.
 */
final class NarrationCatalog
{
    /** Nama konteks naskah di panel, urut alur permainan (docs/naskah-cerita.md). */
    public const CONTEXT_LABELS = [
        'intro'        => 'Cerita pembuka',
        'map_intro'    => 'Narasi Peta Kedu',
        'region_intro' => 'Kenali wilayah',
        'level_open'   => 'Dialog masuk wilayah',
        'level_done'   => 'Wilayah tuntas',
        'ending'       => 'Penutup',
    ];

    public const CHARACTER_LABELS = ['narator' => 'Narator', 'jaka' => 'Jaka', 'mbah_kedu' => 'Mbah Kedu'];

    /** Status audio satu bahasa → label panel. */
    public const STATUS_LABELS = [
        'none'     => 'belum ada',
        'draft'    => 'draft',
        'review'   => 'ditinjau',
        'rejected' => 'ditolak',
        'inactive' => 'aset nonaktif',
        'approved' => 'disetujui',
    ];

    private BaseConnection $db;

    private NarrationImporter $importer;

    public function __construct(?BaseConnection $db = null, ?NarrationImporter $importer = null)
    {
        $this->db       = $db ?? db_connect();
        $this->importer = $importer ?? new NarrationImporter($this->db);
    }

    /**
     * Baris naskah aktif beserta status audio ID dan EN.
     *
     * @return list<array<string, mixed>> baris dialogues + code, level_code, level_name,
     *                                    audio: array{id: array, en: array} (status, src, asset_key, audio_id)
     */
    public function rows(): array
    {
        $lines    = $this->importer->lines();
        $locales  = config('Gelita')->locales;
        $audioIds = [];

        foreach ($lines as $line) {
            foreach ($locales as $locale) {
                if (! empty($line['audio_' . $locale . '_asset_id'])) {
                    $audioIds[] = (int) $line['audio_' . $locale . '_asset_id'];
                }
            }
        }

        $audio = [];

        if ($audioIds !== []) {
            $found = $this->db->table('audio_assets aa')
                ->select('aa.id, aa.approval_status, aa.locale, aa.duration_ms, ma.asset_key, ma.storage_path, ma.is_active')
                ->join('media_assets ma', 'ma.id = aa.media_asset_id')
                ->whereIn('aa.id', array_values(array_unique($audioIds)))
                ->get()
                ->getResultArray();

            foreach ($found as $row) {
                $audio[(int) $row['id']] = $row;
            }
        }

        $rows = [];

        foreach ($lines as $line) {
            $line['audio'] = [];

            foreach ($locales as $locale) {
                $id  = (int) ($line['audio_' . $locale . '_asset_id'] ?? 0);
                $row = $audio[$id] ?? null;

                $line['audio'][$locale] = $row === null
                    ? ['status' => 'none', 'src' => null, 'asset_key' => null, 'audio_id' => null, 'duration_ms' => null]
                    : [
                        'status'      => (int) $row['is_active'] === 1 ? (string) $row['approval_status'] : 'inactive',
                        'src'         => (string) $row['storage_path'],
                        'asset_key'   => (string) $row['asset_key'],
                        'audio_id'    => $id,
                        'duration_ms' => $row['duration_ms'] === null ? null : (int) $row['duration_ms'],
                    ];
            }

            $rows[] = $line;
        }

        return $rows;
    }

    /**
     * Kemajuan per bahasa. `draft` menghitung semua rekaman terpasang yang
     * belum terdengar pemain (draft, ditinjau, ditolak, aset nonaktif).
     *
     * @param list<array<string, mixed>> $rows hasil rows()
     *
     * @return array<string, array{total: int, approved: int, draft: int, none: int}>
     */
    public function progress(array $rows): array
    {
        $out = [];

        foreach (config('Gelita')->locales as $locale) {
            $out[$locale] = ['total' => count($rows), 'approved' => 0, 'draft' => 0, 'none' => 0];

            foreach ($rows as $row) {
                $status = $row['audio'][$locale]['status'];
                $out[$locale][$status === 'approved' ? 'approved' : ($status === 'none' ? 'none' : 'draft')]++;
            }
        }

        return $out;
    }

    /**
     * Baris per kelompok tampilan: konteks global satu kelompok, konteks
     * wilayah satu kelompok per wilayah.
     *
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array{context: string, level_code: string|null, label: string, rows: list<array<string, mixed>>}>
     */
    public function groups(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = $row['context_code'] . '|' . ($row['level_code'] ?? '');

            $groups[$key] ??= [
                'context'    => (string) $row['context_code'],
                'level_code' => $row['level_code'],
                'label'      => (self::CONTEXT_LABELS[$row['context_code']] ?? $row['context_code'])
                    . ($row['level_name'] === null ? '' : ' · ' . $row['level_name']),
                'rows'       => [],
            ];

            $groups[$key]['rows'][] = $row;
        }

        return array_values($groups);
    }

    /**
     * Id audio narasi berstatus draft pada satu bahasa.
     *
     * @return list<int>
     */
    public function draftIds(string $locale): array
    {
        $column = 'audio_' . $locale . '_asset_id';

        $linked = array_map('intval', array_column(
            $this->db->table('dialogues')
                ->select($column)
                ->where($column . ' IS NOT NULL')
                ->whereIn('context_code', array_keys(NarrationImporter::PREFIXES))
                ->get()
                ->getResultArray(),
            $column,
        ));

        $builder = $this->db->table('audio_assets aa')
            ->select('aa.id')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->where('aa.locale', $locale)
            ->where('aa.approval_status', 'draft')
            ->groupStart()
            ->like('ma.asset_key', NarrationImporter::assetKey($locale, ''), 'after');

        if ($linked !== []) {
            $builder->orWhereIn('aa.id', array_values(array_unique($linked)));
        }

        return array_map('intval', array_column($builder->groupEnd()->get()->getResultArray(), 'id'));
    }

    /** Setujui seluruh audio narasi draft satu bahasa; mengembalikan jumlahnya. */
    public function approveDrafts(string $locale, ?int $staffId): int
    {
        $ids = $this->draftIds($locale);

        if ($ids !== []) {
            $this->db->table('audio_assets')->whereIn('id', $ids)->update([
                'approval_status' => 'approved',
                'approved_by'     => $staffId,
                'approved_at'     => date('Y-m-d H:i:s'),
            ]);

            service('contentRepository')->flush();
        }

        model(AuditLogModel::class)->record('audio_approve_bulk', [
            'staff_user_id' => $staffId,
            'target_type'   => 'audio_asset',
            'target_id'     => 'narasi.' . $locale,
            'metadata'      => ['locale' => $locale, 'count' => count($ids), 'audio_ids' => array_slice($ids, 0, 200)],
        ]);

        return count($ids);
    }

    /**
     * Daftar rekaman untuk pengisi suara: satu baris per baris naskah.
     *
     * @return array{headers: list<string>, rows: list<list<string|int|null>>}
     */
    public function recordingList(): array
    {
        $headers = [
            'kode_berkas', 'konteks', 'wilayah', 'urutan', 'tokoh', 'pose', 'efek',
            'judul_id', 'judul_en', 'teks_id', 'teks_en', 'audio_id', 'audio_en',
        ];
        $rows = [];

        foreach ($this->rows() as $row) {
            $rows[] = [
                $row['code'],
                $row['context_code'],
                $row['level_code'],
                (int) $row['sequence'],
                self::CHARACTER_LABELS[$row['character_code']] ?? $row['character_code'],
                $row['pose'],
                $row['effect'],
                $row['title_id'],
                $row['title_en'],
                $row['text_id'],
                $row['text_en'],
                self::STATUS_LABELS[$row['audio']['id']['status'] ?? 'none'] ?? null,
                self::STATUS_LABELS[$row['audio']['en']['status'] ?? 'none'] ?? null,
            ];
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** Tulis daftar rekaman sebagai XLSX; mengembalikan path berkas. */
    public function writeRecordingList(string $path): string
    {
        $list   = $this->recordingList();
        $writer = new ExcelWriter($path);
        $writer->startSheet('Daftar rekaman', $list['headers']);

        foreach ($list['rows'] as $row) {
            $writer->row($row);
        }

        $writer->finish();

        return $path;
    }
}
