<?php

namespace Tests\Support\Database;

use App\Models\FieldSessionModel;
use App\Services\FieldSheetService;
use CodeIgniter\Database\BaseConnection;

/**
 * FieldWorld plus what the field-sheet tests of research revision 10,
 * part 3 (29 §16.1) share: a P2 field session of class group S03-A with two
 * students (S03-A01 "Jaka", S03-A02 "Sekar Ayu") who have seats, booklets
 * B1 and B2, and a typed CSV written to the scratch folder.
 */
trait FieldSheetWorld
{
    use FieldWorld;

    private BaseConnection $conn;

    /** @var list<string> */
    private array $tempFiles = [];

    private function createFieldSheetWorld(int $meetingNo = 2): array
    {
        $this->conn = $this->createFieldWorld();
        $second     = $this->student($this->conn, 'S03-A02', 'sekar.ayu');
        $this->conn->table('participants')->where('id', $second)->update(['display_name' => 'Sekar Ayu']);
        $this->conn->table('participant_seats')->insertBatch([
            ['participant_id' => 1, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 1, 'booklet' => 'B1', 'pre_form' => 'A', 'post_form' => 'B'],
            ['participant_id' => $second, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 2, 'booklet' => 'B2', 'pre_form' => 'B', 'post_form' => 'A'],
        ]);

        $session = $this->fieldSession($this->conn, $meetingNo, 'KD7RMA');

        return model(FieldSessionModel::class)->find($session['id']);
    }

    protected function tearDown(): void
    {
        array_map(static fn (string $f) => is_file($f) && unlink($f), $this->tempFiles);
        $this->dropResetTables();
        parent::tearDown();
    }

    /** @param list<list<string>> $lines */
    private function csv(string $name, array $lines): string
    {
        $dir = sys_get_temp_dir() . '/gelita-lembar-' . bin2hex(random_bytes(4));
        mkdir($dir);
        $path = $dir . '/' . $name;
        $out  = fopen($path, 'wb');

        foreach ($lines as $line) {
            fputcsv($out, $line, ',', '"', '');
        }

        fclose($out);
        $this->tempFiles[] = $path;

        return $path;
    }

    private function sheets(): FieldSheetService
    {
        return new FieldSheetService();
    }
}
