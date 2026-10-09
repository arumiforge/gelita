<?php

namespace App\Controllers\Admin;

use App\Libraries\AdminTable;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Kritik & saran dari Balai Refleksi (Dok 3 §8.3): tabel kode, sekolah,
 * bintang (1–5), empat jawaban (dipotong, klik untuk lengkap), waktu.
 * Guru hanya melihat masukan siswa sekolahnya.
 */
class FeedbackController extends BaseAdminController
{
    public function index(): string|ResponseInterface
    {
        $filters = $this->scopedFilters();
        $columns = self::listColumns();
        $builder = $this->builder($filters);
        $sorts   = array_column(array_filter($columns, static fn (array $c): bool => ! empty($c['sort'])), 'key');

        if ($this->wantsCsv()) {
            $sort = AdminTable::validSort((string) $this->request->getGet('sort'), $sorts) ?? '-submitted_at';

            return $this->tableCsv('kritik-saran', $builder, $columns, $sort, 'f.id', $filters);
        }

        $params = AdminTable::params($this->request, $sorts, '-submitted_at');

        return $this->panel('admin/feedback/index', lang('Admin.menuFeedback'), [
            'filters' => $filters,
            'table'   => AdminTable::page($builder, $columns, $params['page'], $params['per_page'], $params['sort'], 'f.id'),
        ]);
    }

    /**
     * Kolom empat jawaban + bintang + waktu; dipakai juga tab Kritik & saran
     * di detail siswa.
     *
     * @return list<array<string, mixed>>
     */
    public static function answerColumns(bool $withSession = false): array
    {
        $columns = [
            ['key' => 'rating', 'label' => 'Bintang (1–5)', 'type' => 'stars', 'sort' => 'f.rating'],
            ['key' => 'liked_most', 'label' => 'Yang paling disukai', 'type' => 'clip'],
            ['key' => 'hardest_part', 'label' => 'Yang paling sulit', 'type' => 'clip'],
            ['key' => 'new_learning', 'label' => 'Hal baru yang dipelajari', 'type' => 'clip'],
            ['key' => 'suggestion', 'label' => 'Saran', 'type' => 'clip'],
            ['key' => 'submitted_at', 'label' => 'Waktu', 'type' => 'datetime', 'sort' => 'f.submitted_at'],
        ];

        if ($withSession) {
            array_unshift($columns, ['key' => 'session_code', 'label' => 'Kode sesi', 'type' => 'code']);
        }

        return $columns;
    }

    /** @return list<array<string, mixed>> */
    private static function listColumns(): array
    {
        return [
            ['key' => 'participant_code', 'label' => 'Kode siswa', 'type' => 'code', 'sort' => 'p.participant_code'],
            ['key' => 'school_name', 'label' => 'Sekolah', 'type' => 'text', 'sort' => 'school_name'],
            ...self::answerColumns(),
        ];
    }

    /** @param array<string, mixed> $filters sudah dipaksa ke cakupan sekolah */
    private function builder(array $filters): BaseBuilder
    {
        $builder = db_connect()->table('participant_feedback f')
            ->select('f.id, f.participant_id, p.participant_code, COALESCE(s.name, p.school_name_snapshot) AS school_name', false)
            ->select('f.rating, f.liked_most, f.hardest_part, f.new_learning, f.suggestion, f.submitted_at')
            ->join('participants p', 'p.id = f.participant_id')
            ->join('schools s', 's.id = p.school_id', 'left')
            ->where('p.deleted_at', null);

        if (isset($filters['school_id'])) {
            $builder->where('p.school_id', (int) $filters['school_id']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('f.submitted_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to'])) {
            $builder->where('f.submitted_at <=', $filters['date_to'] . ' 23:59:59');
        }

        return $builder;
    }
}
