<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One imported version of `tes`, `angket`, or `sikap` (34 §6.2, §11.1).
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md).
 */
class InstrumentVersionModel extends Model
{
    protected $table         = 'instrument_versions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['instrument', 'version', 'content_hash', 'status', 'is_active', 'meta_json', 'imported_at'];
}
