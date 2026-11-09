<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * A time block of a test sitting (`p1`, `p23`) with its hidden limit and extra minutes (34 §7.4, K3, K9).
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md).
 */
class InstrumentBlockModel extends Model
{
    protected $table         = 'instrument_blocks';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['sitting_id', 'block', 'started_at', 'limit_minutes', 'extra_minutes', 'closed_at', 'closed_reason'];
}
