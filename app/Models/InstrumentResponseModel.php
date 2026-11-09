<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One answer unit of a sitting (K7): raw response, times, changes, score (34 §7.5, §11.1).
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md).
 */
class InstrumentResponseModel extends Model
{
    protected $table         = 'instrument_responses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['part_id', 'sitting_id', 'item_id', 'unit', 'response', 'score', 'error_codes', 'not_reached', 'first_shown_at', 'shown_at', 'answered_at', 'view_count', 'time_on_item_ms', 'last_view_ms', 'answer_changes', 'revision', 'updated_at'];
}
