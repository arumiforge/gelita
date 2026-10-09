<?php

use App\Services\RawDataService;
use App\Services\StaffScope;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\RawDataWorld;

/**
 * Tabel data mentah admin di tingkat layanan (Dok 3 §8.4, §12.1
 * `AdminTableTest`): kontrak `RawDataService::page()` — kolom, urutan,
 * halaman —, CSV per tabel sama dengan isi tabel, dan cakupan guru
 * (sekolahnya saja, anonim, tanpa Event Mentah dan kunci jawaban).
 * Tampilan tabelnya (komponen, JS) diuji sesi admin.
 *
 * @internal
 */
final class AdminTableTest extends CIUnitTestCase
{
    use RawDataWorld;

    private RawDataService $admin;
    private RawDataService $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRawDataWorld();
        $this->admin   = new RawDataService(StaffScope::admin());
        $this->teacher = new RawDataService(StaffScope::teacher(1));
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testPageReturnsTheDocumentedShape(): void
    {
        $page = $this->admin->page('jawaban', [], 1, 25, null);

        $this->assertSame(['rows', 'total', 'page', 'per_page', 'sort', 'columns'], array_keys($page));
        $this->assertSame([2, 1, 25, '-started_at'], [$page['total'], $page['page'], $page['per_page'], $page['sort']]);

        foreach ($page['columns'] as $column) {
            $this->assertSame(['key', 'label', 'type'], array_keys($column));
            $this->assertContains($column['type'], ['code', 'text', 'int', 'ms', 'datetime', 'bool', 'enum', 'json']);
            $this->assertNotSame($column['key'], $column['label'], 'judul kolom berbahasa Indonesia');
        }

        $keys = array_column($page['columns'], 'key');
        $this->assertSame($keys, array_keys($page['rows'][0]), 'baris memuat tepat kolom yang diumumkan, urut sama');
        $this->assertContains('answer_key', $keys, 'admin melihat kunci jawaban');
        $this->assertSame('Durasi (mdtk)', $page['columns'][array_search('duration_ms', $keys, true)]['label']);

        // Terbaru dulu: pengerjaan Budi (2 Okt) sebelum Ani (1 Okt)
        $this->assertSame(['GLT-000002', 'GLT-000001'], array_column($page['rows'], 'participant_code'));

        $ani = $page['rows'][1];
        $this->assertSame('temanggung', $ani['level']);
        $this->assertSame('tmg-3', $ani['node_ref']);
        $this->assertSame('tmg-3-a01', $ani['item_key']);
        $this->assertSame('informasi_tersurat', $ani['skill']);
        $this->assertSame('literasi', $ani['indicator']);
        $this->assertSame('{"verdict":"salah"}', $ani['first_answer']);
        $this->assertSame('{"verdict":"benar"}', $ani['answer_key']);
        $this->assertSame([0, 1, 2, 9000], [$ani['first_pass_correct'], $ani['is_correct'], $ani['solved_on_try'], $ani['duration_ms']]);
        $this->assertSame('2026-10-01T08:12:00.000+07:00', $ani['answered_at']);
    }

    public function testSortingAndPagination(): void
    {
        $asc = $this->admin->page('sesi', [], 1, 25, 'participant_code');
        $this->assertSame('participant_code', $asc['sort']);
        $this->assertSame(['GLT-000001', 'GLT-000002'], array_column($asc['rows'], 'participant_code'));

        $desc = $this->admin->page('sesi', [], 1, 25, '-duration_ms');
        $this->assertSame([2340000, 540000], array_column($desc['rows'], 'duration_ms'));

        // Kolom tak dikenal atau dihitung PHP → urutan bawaan
        $this->assertSame('-started_at', $this->admin->page('sesi', [], 1, 25, 'password_hash')['sort']);
        $this->assertSame('-started_at', $this->admin->page('pengerjaan', [], 1, 25, 'variant_title')['sort']);
        $this->assertNotContains('variant_title', $this->admin->sortable('pengerjaan'));
        $this->assertContains('node_ref', $this->admin->sortable('pengerjaan'));

        // Halaman dijepit, per_page hanya 25/50/100
        $clamped = $this->admin->page('sesi', [], 99, 7, 'participant_code');
        $this->assertSame([1, 25, 2], [$clamped['page'], $clamped['per_page'], count($clamped['rows'])]);

        // Banyak halaman: 30 percobaan tambahan, 25 per halaman
        $db = db_connect();

        for ($i = 3; $i <= 32; $i++) {
            $db->table('item_tries')->insert([
                'challenge_attempt_id' => 2, 'challenge_item_id' => 1, 'try_no' => $i, 'answer_json' => '{"order":[1,0,2,3]}',
                'occurred_at' => sprintf('2026-10-02 09:%02d:00', $i),
            ]);
        }

        $first  = $this->admin->page('percobaan', [], 1, 25, 'try_no');
        $second = $this->admin->page('percobaan', [], 2, 25, 'try_no');
        $this->assertSame([32, 25, 7], [$first['total'], count($first['rows']), count($second['rows'])]);
        $this->assertSame([1, 2, 3, 4], array_slice(array_column($first['rows'], 'try_no'), 0, 4));
        $this->assertSame(32, end($second['rows'])['try_no']);
    }

    public function testFiltersNarrowRows(): void
    {
        $this->assertSame(1, $this->admin->page('sesi', ['school_id' => '2'], 1, 25, null)['total']);
        $this->assertSame(1, $this->admin->page('sesi', ['locale' => 'en'], 1, 25, null)['total']);
        $this->assertSame(1, $this->admin->page('pengerjaan', ['node_id' => 2], 1, 25, null)['total']);
        $this->assertSame(2, $this->admin->page('pengerjaan', ['variant_group' => 'a'], 1, 25, null)['total']);
        $this->assertSame(2, $this->admin->page('percobaan', ['participant_code' => 'GLT-000001', 'date_from' => '2026-10-01', 'date_to' => '2026-10-01'], 1, 25, null)['total']);
        $this->assertSame(0, $this->admin->page('percobaan', ['date_from' => '2026-10-03'], 1, 25, null)['total']);
        $this->assertSame(2, $this->admin->page('sesi', ['date_from' => 'kemarin', 'evil' => '1'], 1, 25, null)['total'], 'filter rusak diabaikan');
    }

    public function testCsvEqualsTableRows(): void
    {
        foreach (['jawaban', 'percobaan', 'pengerjaan', 'progres', 'selesai', 'navigasi', 'bantuan', 'perangkat', 'sesi', 'masukan'] as $dimension) {
            $page   = $this->admin->page($dimension, [], 1, 100, null);
            $handle = fopen('php://temp', 'w+b');
            $count  = $this->admin->streamCsv($dimension, [], $handle);
            rewind($handle);

            $this->assertSame("\xEF\xBB\xBF", fread($handle, 3), $dimension . ': BOM UTF-8');

            $header = fgetcsv($handle, null, ',', '"', '');
            $this->assertSame(array_column($page['columns'], 'key'), $header, $dimension . ': judul CSV = kode kolom');

            $lines = [];

            while (($line = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $lines[] = array_combine($header, $line);
            }

            fclose($handle);

            $this->assertSame($page['total'], $count, $dimension);
            $this->assertCount($count, $lines, $dimension);

            $expected = array_map(
                static fn (array $row): array => array_map(static fn ($v): string => $v === null ? '' : (string) $v, $row),
                $page['rows'],
            );
            $actual = $lines;

            usort($expected, static fn (array $a, array $b): int => strcmp(json_encode($a), json_encode($b)));
            usort($actual, static fn (array $a, array $b): int => strcmp(json_encode($a), json_encode($b)));

            $this->assertSame($expected, $actual, $dimension . ': isi CSV sama dengan isi tabel');
        }
    }

    public function testReadableAnswersChangeOnlyTheAnswerColumns(): void
    {
        $coded    = $this->admin->page('jawaban', [], 1, 25, null);
        $readable = $this->admin->page('jawaban', [], 1, 25, null, true);
        $ani      = $readable['rows'][1];

        $this->assertSame(array_column($coded['columns'], 'key'), array_column($readable['columns'], 'key'));
        $this->assertSame(
            ['first_answer' => 'text', 'final_answer' => 'text', 'answer_key' => 'text'],
            array_intersect_key(array_column($readable['columns'], 'type', 'key'), array_flip(RawDataService::ANSWER_COLUMNS)),
            'sentences render as plain text, not as JSON',
        );
        $this->assertSame(['Salah', 'Benar', 'Benar'], [$ani['first_answer'], $ani['final_answer'], $ani['answer_key']]);
        $this->assertSame(
            array_diff_key($coded['rows'][1], array_flip(['first_answer', 'final_answer', 'answer_key'])),
            array_diff_key($ani, array_flip(['first_answer', 'final_answer', 'answer_key'])),
            'codes, times, and 1/0 stay as stored',
        );

        $tries = $this->admin->page('percobaan', ['participant_code' => 'GLT-000001'], 1, 25, 'try_no', true);
        $this->assertSame(['Salah', 'Benar'], array_column($tries['rows'], 'answer'));

        $handle = fopen('php://temp', 'w+b');
        $this->admin->streamCsv('jawaban', [], $handle, true);
        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        $this->assertStringContainsString(',Salah,Benar,', $csv, 'the table CSV follows the same choice');
        $this->assertStringNotContainsString('{', $csv, 'no JSON left');
    }

    public function testTeacherSeesOnlyOwnSchoolAnonymouslyWithoutKeysOrRawEvents(): void
    {
        $sessions = $this->teacher->page('sesi', [], 1, 25, null);
        $this->assertSame(['GLT-000001'], array_column($sessions['rows'], 'participant_code'));

        // Memaksa sekolah lain lewat filter tidak menembus cakupan
        $this->assertSame(0, $this->teacher->page('sesi', ['school_id' => 2], 1, 25, null)['total']);
        $this->assertSame(0, $this->teacher->page('jawaban', ['participant_code' => 'GLT-000002'], 1, 25, null)['total']);

        $people = $this->teacher->page('peserta', [], 1, 25, null);
        $keys   = array_column($people['columns'], 'key');

        foreach (RawDataService::IDENTITY_COLUMNS as $identity) {
            $this->assertNotContains($identity, $keys);
        }

        $this->assertContains('school_ref', $keys);
        $this->assertSame('SCH-000001', $people['rows'][0]['school_ref']);
        $this->assertStringNotContainsString('Ani', json_encode($people['rows']));

        foreach (['jawaban', 'butir'] as $dimension) {
            $page = $this->teacher->page($dimension, [], 1, 25, null);
            $this->assertNotContains('answer_key', array_column($page['columns'], 'key'), $dimension);
            $this->assertStringNotContainsString('"verdict":"benar"}"', json_encode($page['rows']), $dimension);
        }

        $this->assertNotContains('event', $this->teacher->dimensions(StaffScope::teacher(1)));

        $this->expectException(DomainException::class);
        $this->teacher->page('event', [], 1, 25, null);
    }

    public function testTeacherCsvAndQueryKeepTheScope(): void
    {
        $handle = fopen('php://temp', 'w+b');
        $this->assertSame(1, $this->teacher->streamCsv('peserta', [], $handle));
        rewind($handle);
        $csv = stream_get_contents($handle);

        $this->assertStringContainsString('GLT-000001', $csv);
        $this->assertStringNotContainsString('GLT-000002', $csv);
        $this->assertStringNotContainsString('Ani Lestari', $csv);
        $this->assertStringNotContainsString('ani.5', $csv);

        // Guru tanpa sekolah tidak melihat apa-apa
        $nobody = new RawDataService(StaffScope::teacher(null));
        $this->assertSame(0, $nobody->page('sesi', [], 1, 25, null)['total']);

        // query() memakai cakupan yang diberikan, bukan yang terikat
        $this->assertSame(2, $this->teacher->query('sesi', [], StaffScope::admin())->countAllResults());
    }

    public function testIdentityQueryNeverReadsSecrets(): void
    {
        $sql = (string) $this->admin->query('peserta', [], StaffScope::admin())->getCompiledSelect();

        $this->assertStringNotContainsString('password_hash', $sql);
        $this->assertStringNotContainsString('failed_login', $sql);
        $this->assertStringNotContainsString('locked_until', $sql);

        $anonymous = (string) $this->admin->query('peserta', [], StaffScope::admin(true))->getCompiledSelect();
        $this->assertStringNotContainsString('display_name', $anonymous);
        $this->assertStringNotContainsString('username', $anonymous);
    }

    public function testDeletedEventsAndHelpUnion(): void
    {
        $navigation = $this->admin->page('navigasi', [], 1, 25, 'occurred_at');
        $this->assertSame(['screen_viewed', 'screen_viewed'], array_column($navigation['rows'], 'event_type'), 'page_hidden yang ditandai hapus tidak ikut');
        $this->assertSame(['map_kedu', 'intro'], [$navigation['rows'][0]['screen'], $navigation['rows'][0]['from_screen']]);

        $help = $this->admin->page('bantuan', [], 1, 25, 'occurred_at');
        $this->assertSame(['audio', 'petunjuk'], array_column($help['rows'], 'jenis'));
        $this->assertSame(['audio.narasi.id.intro-01', 'intro', 'play', 4200, 1], [
            $help['rows'][0]['asset_key'], $help['rows'][0]['audio_context'], $help['rows'][0]['action'], $help['rows'][0]['listened_ms'], $help['rows'][0]['completed'],
        ]);
        $this->assertSame([5, 'tmg-3', 1, 'temanggung'], [$help['rows'][1]['hint_id'], $help['rows'][1]['node_ref'], $help['rows'][1]['attempt_id'], $help['rows'][1]['level']]);
        $this->assertSame(1, $this->admin->page('bantuan', ['level_id' => 1], 1, 25, null)['total'], 'filter wilayah: suara di luar tantangan tidak ikut');

        $done = $this->admin->page('selesai', [], 1, 25, null);
        $this->assertSame(['challenge_completed'], array_column($done['rows'], 'event_type'));
        $this->assertSame('{"stars":2}', $done['rows'][0]['payload']);
    }

    public function testUnknownDimensionAndUnboundScope(): void
    {
        try {
            $this->admin->page('nilai', [], 1, 25, null);
            $this->fail('dimensi tak dikenal harus ditolak');
        } catch (InvalidArgumentException) {
            $this->addToAssertionCount(1);
        }

        $this->expectException(LogicException::class);
        (new RawDataService())->page('sesi', [], 1, 25, null);
    }
}
