<?php

use App\Libraries\BankSource;
use App\Libraries\BankWorkbookGuide;
use App\Services\ChallengeService;
use App\Services\ContentImportService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * Import of the sheets of 31 §6.3: a revised workbook built from changed data
 * files (text, key-free labels, codes, positions) re-imports with no code
 * change (§19.4); codes of an item are replaced as a whole and may change
 * after it was answered, a `find_part` key may not (§7.1); scenarios are
 * written once their challenge exists (part 6), and set aside before.
 *
 * @internal
 */
final class BankImportNewSheetsTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-impor-31';
        $this->conn                    = $this->createGradeFiveWorld();
        $this->service                 = new ChallengeService();
    }

    protected function tearDown(): void
    {
        array_map('unlink', array_filter($this->files, 'is_file'));
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testRevisedWorkbookReimportsWithoutCodeChanges(): void
    {
        $regions = BankSource::load(ROOTPATH . 'docs/bank-soal/data');
        $tmg     = &$regions['temanggung']['nodes'];

        $tmg['tmg-3']['variants']['a']['items'][0]['prompt'][0] = 'Situs Liyangan ditemukan warga pada tahun 2008.';
        $tmg['tmg-3']['variants']['a']['items'][0]['code']      = 'INF-KAT';
        $tmg['tmg-3']['variants']['a']['items'][0]['akm']       = 'L2';
        $tmg['tmg-3']['variants']['a']['items'][0]['labels']    = ['akm' => 'expert'];
        $tmg['tmg-2']['variants']['a']['items'][0]['trap']      = ['sindoro' => 'INF-KAT'];
        $tmg['tmg-4']['round1']['items'][0]['y']                = 30;
        $tmg['tmg-4']['round2']['a']['parts'][0]['text'][0]     = '@jalan2_bima_baru';
        unset($tmg);

        $result = (new ContentImportService())->import($this->workbook($regions), 1);
        $this->assertTrue($result['ok'], json_encode($result['errors'], JSON_UNESCAPED_UNICODE));

        $statement = $this->item('tmg-3-a01');
        $this->assertSame(['Situs Liyangan ditemukan warga pada tahun 2008.', 'INF-KAT', 'L2', 'expert', 'developer'], [
            $statement->prompt_id, $statement->error_code, $statement->akm_level, $statement->akm_level_source, $statement->bloom_level_source,
        ]);

        $codes = service('contentRepository')->errorCodes($this->item('tmg-2-a01')->id);
        $this->assertSame('INF-KAT', $codes['word']['sindoro']['code']);
        $this->assertCount(5, $codes['word'], 'replaced as a whole, no leftover rows');
        $this->assertSame(30.0, $this->item('tmg-4-x01')->position()['y']);

        $post = service('contentRepository')->postScene($this->nodeId($this->conn, 'tmg-4'), 'a');
        $this->assertSame('@jalan2_bima_baru', $post['parts'][0]['text_id']);
        $this->assertCount(8, $post['parts']);
        $this->assertSame(1, $result['written']['codes_changed'], 'the audit counts changed codes');
    }

    public function testCodesMayChangeAfterAnswersButFindPartKeysMayNot(): void
    {
        $attempt = $this->open('tmg-4');
        $this->solveRoundOne($attempt);

        $regions = BankSource::load(ROOTPATH . 'docs/bank-soal/data');
        $regions['temanggung']['nodes']['tmg-4']['round2']['a']['items'][0]['main'] = ['A1' => 'BUD-ATR', 'A5' => 'BUD-ATR'];
        $regions['temanggung']['nodes']['tmg-4']['round1']['items'][4]['code']      = 'INF-LIT';

        $this->assertTrue((new ContentImportService())->import($this->workbook($regions), 1)['ok'], 'codes are not keys');

        // The attempt's own variant has responses: its first clue's key part is fixed
        $group  = (string) $attempt->variant_group;
        $letter = strtoupper($group);
        $regions['temanggung']['nodes']['tmg-4']['round2'][$group]['items'][0]['answer'] = $letter . '3';
        $regions['temanggung']['nodes']['tmg-4']['round2'][$group]['items'][0]['main']   = [$letter . '1' => 'BUD-ATR', $letter . '4' => 'BUD-ATR'];

        $result = (new ContentImportService())->preview($this->workbook($regions));
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString("Soal tmg-4-{$group}01 sudah pernah dijawab siswa", implode(' ', array_column($result['errors'], 'message')));
    }

    public function testScenariosAreWrittenOnceTheirChallengeExists(): void
    {
        $file   = $this->workbook(BankSource::load(ROOTPATH . 'docs/bank-soal/data'));
        $before = (new ContentImportService())->preview($file);
        $this->assertTrue($before['ok']);
        $this->assertStringContainsString('Tantangan tmg-5 (Skenario Digital) belum ada di permainan, jadi 8 skenario', implode(' ', array_column($before['warnings'], 'message')));

        // Part 6 installs the fifth challenge; the same workbook then writes the scenarios
        $levelId = (int) $this->conn->table('levels')->where('code', 'temanggung')->get()->getRow('id');
        $this->conn->table('challenge_nodes')->insert(['level_id' => $levelId, 'sequence' => 5, 'engine_type' => 'skenario', 'title_id' => 'Skenario', 'title_en' => 'Scenarios', 'is_active' => 1]);

        $result = (new ContentImportService())->import($file, 1);
        $this->assertTrue($result['ok'], json_encode($result['errors'], JSON_UNESCAPED_UNICODE));

        $item    = $this->item('tmg-5-a01');
        $options = service('contentRepository')->scenarioOptions($item->id);

        $this->assertSame(['scenario_choice', 'PRI', 'D2', 'L1'], [$item->interaction_type, $item->d2_topic, $item->dimension, $item->akm_level]);
        $this->assertSame(['steps' => 1, 'topic' => 'PRI'], array_intersect_key($item->config_json, ['steps' => 1, 'topic' => 1]));
        $this->assertStringStartsWith('Situs yang tidak jelas pembuatnya', (string) $item->explanation_id);
        $this->assertCount(6, $options);
        $this->assertSame([[0, 'DIG-PRI'], [2, null], [1, 'DIG-PSF']], array_map(static fn (array $o): array => [(int) $o['score'], $o['code']], array_slice($options, 0, 3)));
        $this->assertSame(['R-RISK', 'R-ATUR', 'R-SOS'], array_column(array_slice($options, 3), 'code'));
        $this->assertSame(48, $this->conn->table('scenario_options')->countAllResults(), 'Temanggung only: Magelang and Wonosobo still wait');
    }

    /** Workbook from data files, as docs/bank-soal/build-workbook.php writes it. */
    private function workbook(array $regions): string
    {
        $flat = BankSource::flatten($regions);
        $rows = [];

        foreach ($flat['sheets'] as $name => $list) {
            $headers     = BankWorkbookGuide::headers($name);
            $rows[$name] = array_map(static fn (array $row): array => array_map(static fn (string $h): ?string => ($row[$h] ?? '') === '' ? null : (string) $row[$h], $headers), $list);
        }

        $path          = sys_get_temp_dir() . '/gelita-bank-31-' . bin2hex(random_bytes(4)) . '.xlsx';
        $this->files[] = $path;
        (new BankWorkbookGuide())->write($rows, $path, ['sheets' => array_keys($flat['sheets'])]);

        return $path;
    }
}
