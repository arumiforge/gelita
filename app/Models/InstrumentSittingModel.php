<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One student's sitting of one instrument kind on one occasion (`pre`/`post` × `test`/`questionnaire`/`risk`, `p5` × `attitude`; 34 §7.2, §11.1).
 * Research revision 10, part 8 (docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md).
 */
class InstrumentSittingModel extends Model
{
    protected $table         = 'instrument_sittings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['participant_id', 'study_id', 'field_session_id', 'occasion', 'kind', 'form', 'version_id', 'is_makeup', 'late', 'is_preview', 'risk_offered', 'risk_reconsent', 'risk_reconsent_at', 'started_at', 'submitted_at', 'last_item_id', 'updated_at'];
}
