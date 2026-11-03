<?php

namespace App\Libraries;

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Exceptions\ChallengeRuleException;
use App\Models\ChallengeItemModel;

/**
 * Picks the variant of a new attempt (31 §11.2):
 *
 * | Case                                                         | Variant           | variant_source |
 * | First attempt, challenge 2–5, field mode on, booklet         | from the booklet  | booklet        |
 * | Same, no booklet                                             | BOOKLET_MISSING   | —              |
 * | First attempt, field mode off or study not active            | random a/b        | random         |
 * | First attempt of Susun Gambar (always, K12)                  | random a/b        | random         |
 * | Retake                                                       | not served yet    | retake         |
 *
 * "First attempt" = no variant served to the student yet
 * (ChallengeAttemptModel::variantsServed()). Shared `x` items always join
 * the variant (K11, ChallengeItemModel::pickVariant()).
 */
class VariantPicker
{
    public function __construct(private ?BookletSource $source = null)
    {
    }

    /**
     * @param list<string>        $served variants this student already got
     * @param list<ChallengeItem> $bank   active items of the challenge
     *
     * @return array{group: string, items: list<ChallengeItem>, source: string, booklet: ?string}
     *
     * @throws ChallengeRuleException BOOKLET_MISSING
     */
    public function pick(GameSession $session, ChallengeNode $node, int $levelSequence, array $served, array $bank): array
    {
        $model = model(ChallengeItemModel::class);

        if ($served !== []) {
            return $model->pickVariant($node->id, $served, $bank) + ['source' => 'retake', 'booklet' => null];
        }

        $booklet = $this->bookletOrNull($session, $node);

        if ($booklet === null) {
            return $model->pickVariant($node->id, [], $bank) + ['source' => 'random', 'booklet' => null];
        }

        return $model->pickVariant($node->id, [], $bank, BookletRules::variantFor($booklet, $levelSequence)) + ['source' => 'booklet', 'booklet' => $booklet];
    }

    /**
     * Would the first attempt of this challenge be refused for a missing
     * booklet? Used by the mission brief to show the message up front.
     */
    public function bookletMissing(GameSession $session, ChallengeNode $node, array $served): bool
    {
        if ($served !== []) {
            return false;
        }

        try {
            $this->bookletOrNull($session, $node);
        } catch (ChallengeRuleException) {
            return true;
        }

        return false;
    }

    /**
     * The booklet that decides this first attempt, or null when the attempt
     * is random (field mode off, study not active, or Susun Gambar).
     *
     * @throws ChallengeRuleException BOOKLET_MISSING
     */
    private function bookletOrNull(GameSession $session, ChallengeNode $node): ?string
    {
        if ($node->engine_type === 'puzzle') {
            return null;
        }

        $source  = $this->source ?? new BookletSource();
        $studyId = (int) $session->study_id;

        if (! $source->fieldModeActive($studyId)) {
            return null;
        }

        return $source->bookletFor((int) $session->participant_id, $studyId) ?? throw ChallengeRuleException::bookletMissing();
    }
}
