<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One part of a sitting (`ct`, `mb`, `ae`, `bd`, `A`–`H`, `risk`, `sikap`; 34 §7.3, §11.1).
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md).
 */
class InstrumentPartModel extends Model
{
    protected $table         = 'instrument_parts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['sitting_id', 'part', 'block_id', 'opened_at', 'submitted_at', 'closed_at', 'closed_by_limit', 'raw_score', 'answered_count'];
}
