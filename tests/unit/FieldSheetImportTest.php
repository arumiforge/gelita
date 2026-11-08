<?php

use App\Libraries\StaffAccess;
use App\Models\FieldSheetImportModel;
use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Encryption;
use Tests\Support\Database\FieldSheetWorld;

/**
 * Uploading typed field sheets (research revision 10, part 3: 29 §10.4,
 * K12, K13, §19 no. 14–17): preview then save; errors hold the save with
 * row and column; codes of another class group are refused; the L1 token
 * and the L5 quote are dropped; L5 manual codes; a new upload supersedes the
 * old one; name warnings must be confirmed; the admin gets 404.
 *
 * @internal
 */
final class FieldSheetImportTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldSheetWorld;

    private array $fieldSession;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-lembar';
        $this->fieldSession            = $this->createFieldSheetWorld(2);
    }

    protected function afterTearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        Factories::reset('libraries');
    }

    public function testErrorsHoldTheSaveWithRowAndColumn(): void
    {
        $bad = $this->csv('l3_bantuan.csv', [
            ['Jam mulai (WIB)', 'Jam selesai (WIB)', 'Kursi', 'Kode peserta', 'Di layar', 'Pemberi', 'Jenis', 'Diminta siswa?', 'Uraian singkat'],
            ['08:21:30', '—', '1', 'S03-A01', 'tmg-2', 'TMN', 'ISI', 'Ya', 'Teman menunjuk kata'],
            ['8:1', '', '2', 'S03-A02', 'tmg-2', 'XXX', 'ISI', 'Ya', ''],
            ['08:30:00', '08:20:00', '2', 'S03-B07', 'tmg-3', 'GRU', 'STR', 'Tidak', ''],
        ]);

        $preview = $this->sheets()->preview($this->fieldSession, $bad, 'l3_bantuan.csv', 'main');
        $errors  = $preview['sheets']['l3_bantuan']['errors'];

        $this->assertSame(4, $preview['errors']);
        $this->assertContains([3, 'Jam mulai (WIB)', lang('Admin.fieldSheetErrTime')], $errors);
        $this->assertSame([3, 'Pemberi'], array_slice(array_values(array_filter($errors, static fn (array $e): bool => $e[1] === 'Pemberi'))[0], 0, 2));
        $this->assertContains([4, 'Kode peserta', lang('Admin.fieldSheetCodeOtherClass', ['S03-B07'])], $errors);
        $this->assertContains([4, 'Jam selesai (WIB)', lang('Admin.fieldSheetErrTimeOrder')], $errors);

        try {
            $this->sheets()->save($this->fieldSession, $preview, 2, false);
            $this->fail('errors hold the save');
        } catch (DomainException $e) {
            $this->assertSame(lang('Admin.fieldSheetHasErrors'), $e->getMessage());
        }

        $good    = $this->csv('l3_bantuan.csv', [
            ['start_time', 'end_time', 'seats', 'participant_code', 'screen', 'helper', 'help_type', 'asked', 'description'],
            ['08:21:30', '—', '1', 'S03-A01', 'tmg-2', 'TMN', 'ISI', 'Ya', 'Teman menunjuk kata'],
            ['08:47:55', '08:49:30', '01–02', '(lihat L2)', 'tmg-2', 'GRU', 'ISI', 'Tidak', 'Guru membacakan soal'],
        ]);
        $preview = $this->sheets()->preview($this->fieldSession, $good, 'l3_bantuan.csv', 'main');
        $saved   = $this->sheets()->save($this->fieldSession, $preview, 2, false);

        $this->assertSame([['sheet' => 'l3_bantuan', 'rows' => 2, 'replaced' => false]], array_map(static fn (array $s): array => array_diff_key($s, ['import_id' => 1]), $saved));
        $rows = $this->conn->table('field_sheet_rows')->orderBy('row_no')->get()->getResultArray();
        $this->assertSame([1, 1], [(int) $rows[0]['participant_id'], (int) $rows[0]['seat_no']]);
        $this->assertSame($this->fieldSession['session_date'] . ' 08:21:30', substr((string) $rows[0]['started_at'], 0, 19));
        $this->assertNull($rows[1]['participant_id']);
        $this->assertSame('ISI', json_decode((string) $rows[0]['values_json'], true)['help_type']);
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'field_sheet_import')->countAllResults());
        $this->assertSame(64, strlen((string) $this->conn->table('field_sheet_imports')->get()->getRow('file_sha256')), 'only the hash of the file');
    }

    public function testWrongSessionTokenAndQuoteAreHandled(): void
    {
        $l1 = $this->csv('l1_kondisi.csv', [
            ['Kode sesi lapangan', 'S03-A-P3'],
            ['Isian', 'Cara mengisi', 'Contoh', 'Isi di sini'],
            ['Kode sesi lapangan', '', '', 'S03-A-P3'],
            ['Token', '', '', 'KD7RMA'],
            ['Headset', '', '', '24 dari 26'],
        ]);
        $preview = $this->sheets()->preview($this->fieldSession, $l1, 'l1_kondisi.csv', 'main');
        $this->assertContains([0, 'field_session_code', lang('Admin.fieldSheetWrongSession', ['S03-A-P3'])], $preview['sheets']['l1_kondisi']['errors']);

        $l1 = $this->csv('l1_kondisi.csv', [
            ['Isian', 'Cara mengisi', 'Contoh', 'Isi di sini'],
            ['Kode sesi lapangan', '', '', 'S03-A-P2'],
            ['Token', '', '', 'KD7RMA'],
            ['Headset', '', '', '24 dari 26'],
            ['Menit tersedia dan dipakai', '', '', '90 / 84'],
        ]);
        $preview = $this->sheets()->preview($this->fieldSession, $l1, 'l1_kondisi.csv', 'main');
        $values  = $preview['sheets']['l1_kondisi']['rows'][0]['values'];
        $this->assertSame([24, 26, 90, 84], [$values['headset_count'], $values['headset_of'], $values['minutes_available'], $values['minutes_used']]);
        $this->sheets()->save($this->fieldSession, $preview, 2, false);
        $this->assertStringNotContainsString('KD7RMA', json_encode($this->conn->table('field_sheet_rows')->get()->getResultArray()), 'the token is not imported');

        $l5 = $this->csv('l5_wawancara.csv', [
            ['Kode peserta', 'Butir', 'Percobaan', 'Jawaban pertama siswa', 'Kode dari game', 'Kutipan ucapan siswa', 'Pengode 1', 'Pengode 2', 'Sepakat', 'Kode manual'],
            ['S03-A01', 'tmg-2-a01', '1', 'agar', 'INF-HUB', '"Aku pilih agar, soalnya"', 'DIG-PRI + R-LAIN', 'INF-HUB', 'Tidak', '—'],
        ]);
        $preview = $this->sheets()->preview($this->fieldSession, $l5, 'l5_wawancara.csv', 'main');
        $this->assertSame(0, $preview['errors'], json_encode($preview['sheets']['l5_wawancara']['errors']));
        $this->sheets()->save($this->fieldSession, $preview, 2, false);
        $stored = json_encode($this->conn->table('field_sheet_rows')->get()->getResultArray());
        $this->assertStringNotContainsString('soalnya', $stored, 'the quote is not imported');
        $this->assertStringContainsString('DIG-PRI + R-LAIN', $stored, 'coder columns kept as typed (§19 no. 16)');
    }

    public function testManualCodeRules(): void
    {
        [$wrong, $right] = $this->tries();
        $line = static fn (string $item, int $try, string $code): array => ['S03-A01', $item, (string) $try, 'x', '', 'A', 'A', 'Ya', $code];
        $head = ['participant_code', 'item_key', 'try_no', 'first_answer', 'game_code', 'coder_1', 'coder_2', 'agree', 'manual_code'];

        $preview = $this->sheets()->preview($this->fieldSession, $this->csv('l5_wawancara.csv', [
            $head,
            $line($wrong['item_key'], 1, 'INF-LAIN'),                // the dimension's *-LAIN: accepted
            $line($wrong['item_key'], 1, 'DIG-LAIN'),                // another dimension's *-LAIN
            $line($wrong['item_key'], 1, 'R-LAIN'),
            $line($wrong['item_key'], 1, 'INF-HUB + R-LAIN'),
            $line($right['item_key'], 1, 'INF-LAIN'),                // a correct try
            $line($wrong['item_key'], 1, (string) $wrong['error_code']), // same as the game: warning
        ]), 'l5_wawancara.csv', 'main');
        $errors = array_column($preview['sheets']['l5_wawancara']['errors'], 0);

        $this->assertSame([3, 4, 5, 6], $errors);
        $this->assertContains(lang('Admin.fieldSheetWarnSameCode', [7]), $preview['sheets']['l5_wawancara']['warnings']);

        // The accepted row alone is saved and written to the try
        $preview = $this->sheets()->preview($this->fieldSession, $this->csv('l5_wawancara.csv', [$head, $line($wrong['item_key'], 1, 'INF-LAIN')]), 'l5_wawancara.csv', 'main');
        $this->sheets()->save($this->fieldSession, $preview, 2, false);
        $this->assertSame(['INF-LAIN', $wrong['error_code']], $this->tryCodes((int) $wrong['id']), 'manual_code beside the unchanged bank code');

        // A new upload without the code clears it
        $preview = $this->sheets()->preview($this->fieldSession, $this->csv('l5_wawancara.csv', [$head, $line($wrong['item_key'], 1, '—')]), 'l5_wawancara.csv', 'main');
        $this->sheets()->save($this->fieldSession, $preview, 2, false);
        $this->assertSame([null, $wrong['error_code']], $this->tryCodes((int) $wrong['id']));
    }

    public function testReuploadSupersedesAndTheNewestCounts(): void
    {
        $file = fn (string $who): string => $this->csv('l4_bromp.csv', [
            ['round_no', 'obs_time', 'seat_no', 'participant_code', 'screen', 'behavior', 'emotion', 'note'],
            ['1', '07:55:20', '1', 'S03-A01', 'tmg-1', 'ON', 'FOK', $who],
        ]);

        $first  = $this->sheets()->save($this->fieldSession, $this->sheets()->preview($this->fieldSession, $file('pertama'), 'l4_bromp.csv', 'main'), 2, false)[0];
        $second = $this->sheets()->save($this->fieldSession, $this->sheets()->preview($this->fieldSession, $file('kedua'), 'l4_bromp.csv', 'main'), 2, false)[0];

        $this->assertTrue($second['replaced']);
        $imports = model(FieldSheetImportModel::class);
        $this->assertSame($second['import_id'], (int) $imports->current((int) $this->fieldSession['id'], 'l4_bromp')['id']);
        $this->assertSame($second['import_id'], (int) $imports->find($first['import_id'])['superseded_by']);

        // Two uploads at once: the older one's supersede step comes last and must not hide the newer
        $supersede = new ReflectionMethod($this->sheets(), 'supersede');
        $this->assertFalse($supersede->invoke($this->sheets(), (int) $this->fieldSession['id'], 'l4_bromp', $first['import_id']));
        $this->assertSame(1, $imports->where('sheet', 'l4_bromp')->where('superseded_at', null)->countAllResults(), 'exactly one counts');
    }

    public function testNameWarningMustBeConfirmed(): void
    {
        $preview = $this->sheets()->preview($this->fieldSession, $this->csv('l1_penyimpangan.csv', [
            ['Jam mulai (WIB)', 'Jam selesai (WIB)', 'Kode', 'Kejadian', 'Siswa terdampak', 'Tindakan'],
            ['08:01:10', '08:04:20', 'DEV-NET', 'Wi-Fi putus saat Sekar mengerjakan', 'semua', '—'],
            ['09:20:00', '', 'DEV-WKT', 'Bintang jatuh dari kursi', 'semua', ''],
        ]), 'l1_penyimpangan.csv', 'main');

        $this->assertSame([[2, 'Kejadian']], $preview['sheets']['l1_penyimpangan']['names'], 'the word of a name in the class group, never the name itself');
        $this->assertSame(0, $preview['errors']);

        try {
            $this->sheets()->save($this->fieldSession, $preview, 2, false);
            $this->fail('the name warning needs the confirmation');
        } catch (DomainException $e) {
            $this->assertSame(lang('Admin.fieldSheetConfirmNames'), $e->getMessage());
        }

        $this->sheets()->save($this->fieldSession, $preview, 2, true);
        $this->assertSame(1, (int) $this->conn->table('field_sheet_imports')->get()->getRow('names_confirmed'));
    }

    public function testAdminGetsNotFound(): void
    {
        $routes = service('routes');
        $routes->loadRoutes();
        $routes->setHTTPVerb('POST');

        foreach (['admin/sesi-lapangan/([0-9]+)/lembar/unggah', 'admin/sesi-lapangan/([0-9]+)/lembar/simpan'] as $path) {
            $this->assertSame(['staffAuth', 'staffCan:field_sessions.manage'], $routes->getFiltersForRoute($path), $path);
        }

        $this->assertFalse(StaffAccess::allows('admin', 'field_sessions.manage'));
        $this->assertTrue(StaffAccess::allows('peneliti', 'field_sessions.manage'));
    }

    /** A wrong and a right try of student 1's first Isi Rumpang attempt. */
    private function tries(): array
    {
        // Played outside field mode: the import only needs the student's first attempt
        $this->conn->table('research_studies')->update(['field_mode' => 0, 'unlock_mode' => 'free']);
        $this->resetFieldServices();
        $service = new ChallengeService();
        $nodeId  = $this->nodeId($this->conn, 'tmg-2');
        $service->openNode(model(App\Models\GameSessionModel::class)->find(1), $nodeId);
        $attempt = model(App\Models\ChallengeAttemptModel::class)->currentInProgress(1, $nodeId);
        $answers = [];
        $items   = array_values(array_filter(service('contentRepository')->itemBank($nodeId), static fn ($i): bool => in_array($i->id, $attempt->selectedItemIds(), true) && $i->interaction_type === 'fill_blank_bank'));

        foreach ($items as $n => $item) {
            $answers[$item->id] = ['text' => (string) $items[$n === 0 ? 1 : ($n === 1 ? 0 : $n)]->answerKey('text_id')];
        }

        $service->submitCheck($attempt, $answers);
        $this->conn->table('research_studies')->update(['field_mode' => 1]);
        $this->resetFieldServices();

        $rows = $this->conn->table('item_tries')->select('item_tries.id, item_tries.is_correct, item_tries.error_code, challenge_items.item_key')
            ->join('challenge_items', 'challenge_items.id = item_tries.challenge_item_id')
            ->where('challenge_attempt_id', $attempt->id)->orderBy('item_tries.id')->get()->getResultArray();
        $wrong = array_values(array_filter($rows, static fn (array $r): bool => (int) $r['is_correct'] === 0 && $r['error_code'] !== null))[0];
        $right = array_values(array_filter($rows, static fn (array $r): bool => (int) $r['is_correct'] === 1))[0];

        return [$wrong, $right];
    }

    /** @return array{0: ?string, 1: ?string} manual_code, error_code */
    private function tryCodes(int $tryId): array
    {
        $row = $this->conn->table('item_tries')->where('id', $tryId)->get()->getRowArray();

        return [$row['manual_code'], $row['error_code']];
    }
}
