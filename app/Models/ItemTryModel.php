<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

/**
 * Satu baris = satu percobaan satu soal (Dok 1 §5.4). Hanya ditambah; tidak
 * pernah diubah.
 */
class ItemTryModel extends Model
{
    use EncodesJson;

    protected $table         = 'item_tries';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'challenge_attempt_id', 'challenge_item_id', 'try_no', 'check_no', 'answer_json',
        'is_correct', 'hint_opened_before', 'first_input_ms', 'last_input_ms', 'occurred_at',
        'error_code', 'manual_code',
    ];
    protected $validationRules = [
        'challenge_attempt_id' => 'required|is_natural_no_zero',
        'challenge_item_id'    => 'required|is_natural_no_zero',
        'try_no'               => 'required|is_natural_no_zero',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['answer_json'];

    /**
     * Seluruh percobaan satu pengerjaan, urut soal lalu nomor percobaan.
     *
     * @return list<array<string, mixed>>
     */
    public function forAttempt(int $attemptId): array
    {
        return $this->where('challenge_attempt_id', $attemptId)
            ->orderBy('challenge_item_id', 'ASC')
            ->orderBy('try_no', 'ASC')
            ->findAll();
    }
}
