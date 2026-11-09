<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * A passage, mock screen, or scene of one instrument version (34 §6.1, §11.1).
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md).
 */
class InstrumentStimulusModel extends Model
{
    protected $table         = 'instrument_stimuli';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['version_id', 'code', 'show_json', 'image_slot'];
}
