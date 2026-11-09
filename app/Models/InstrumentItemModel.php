<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One item of one instrument version; `key_json` never leaves the server (34 §6.1, §11.1).
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md).
 */
class InstrumentItemModel extends Model
{
    protected $table         = 'instrument_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['version_id', 'code', 'part', 'form', 'position', 'type', 'stimulus_id', 'show_json', 'key_json', 'units_json', 'cell_json'];
}
