<?php

namespace App\Models;

use App\Entities\ChallengeNode;
use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class ChallengeNodeModel extends Model
{
    use EncodesJson;

    protected $table          = 'challenge_nodes';
    protected $primaryKey     = 'id';
    protected $returnType     = ChallengeNode::class;
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $allowedFields  = [
        'level_id', 'sequence', 'engine_type', 'variant_code',
        'title_id', 'title_en', 'instruction_id', 'instruction_en',
        'description_id', 'description_en', 'indicator_id', 'scoring_profile_id',
        'background_media_id', 'scene_media_id', 'audio_intro_id', 'audio_intro_en_id',
        'config_json', 'map_x', 'map_y', 'content_version', 'is_active',
    ];
    protected $validationRules = [
        'level_id'    => 'required|is_natural_no_zero',
        'sequence'    => 'required|greater_than[0]|less_than[6]',
        'engine_type' => 'required|valid_engine_type',
        'title_id'    => 'required|max_length[250]',
        'title_en'    => 'required|max_length[250]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['config_json'];

    /** @return list<ChallengeNode> */
    public function forLevel(int $levelId): array
    {
        return $this->where('level_id', $levelId)
            ->where('is_active', 1)
            ->orderBy('sequence', 'ASC')
            ->findAll();
    }

    public function findByLevelSequence(int $levelId, int $sequence): ?ChallengeNode
    {
        return $this->where('level_id', $levelId)->where('sequence', $sequence)->first();
    }

    /** @return list<ChallengeNode> seluruh node aktif, urut level lalu sequence */
    public function allActive(): array
    {
        return $this->select('challenge_nodes.*')
            ->join('levels', 'levels.id = challenge_nodes.level_id')
            ->where('challenge_nodes.is_active', 1)
            ->orderBy('levels.sequence', 'ASC')
            ->orderBy('challenge_nodes.sequence', 'ASC')
            ->findAll();
    }
}
