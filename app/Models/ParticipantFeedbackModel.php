<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantFeedbackModel extends Model
{
    protected $table         = 'participant_feedback';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'participant_id', 'session_id', 'rating',
        'liked_most', 'hardest_part', 'new_learning', 'suggestion', 'submitted_at',
    ];
    protected $validationRules = [
        'participant_id' => 'required|is_natural_no_zero',
        'rating'         => 'required|greater_than[0]|less_than[6]',
    ];
}
