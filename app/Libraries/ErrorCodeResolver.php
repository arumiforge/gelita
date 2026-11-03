<?php

namespace App\Libraries;

use App\Entities\ChallengeItem;

/**
 * The error code of one wrong try (31 §10), derived by the server when the
 * try is graded and stored in `item_tries.error_code` (K5). Codes come from
 * the bank through ContentRepository (same cache as the bank, flushed on
 * import); the browser never sees them (§10.3).
 *
 * | Item type        | Choice                         | Code from                                 |
 * | fill_blank_bank  | word put in the blank          | item_error_codes `word` (Indonesian word) |
 * | verdict_card     | verdict other than the key     | challenge_items.error_code                |
 * | find_object      | object tapped (item_key)       | item_error_codes `object`                 |
 * | find_part        | part tapped (part_key)         | item_error_codes `part`                   |
 * | scenario_choice  | action option_key (part 6)     | scenario_options.code                     |
 *
 * A student playing in English places English words: the word is mapped back
 * to its Indonesian pair through the word bank of the variant (answers and
 * decoys) before the lookup. An unknown choice gives null and a log line.
 */
class ErrorCodeResolver
{
    /**
     * @param array<string, mixed> $answer     the graded answer
     * @param string|null          $clickedRef item_key of the tapped object, part_key of the tapped part, or option_key of the action
     */
    public function resolve(ChallengeItem $item, array $answer, string $locale, ?string $clickedRef = null): ?string
    {
        $content = service('contentRepository');
        $code    = match ($item->interaction_type) {
            'fill_blank_bank' => $this->wordCode($item, (string) ($answer['text'] ?? ''), $locale),
            'verdict_card'    => $this->nullIfBlank((string) ($item->error_code ?? '')),
            'find_object'     => $content->errorCodes($item->id)['object'][(string) $clickedRef]['code'] ?? null,
            'find_part'       => $content->errorCodes($item->id)['part'][strtoupper((string) $clickedRef)]['code'] ?? null,
            'scenario_choice' => $this->optionCode($item, (string) $clickedRef),
            default           => null,
        };

        if ($code === null && $item->interaction_type !== 'puzzle_arrange') {
            log_message('warning', 'No error code for item {key}, choice {choice}', [
                'key'    => $item->item_key,
                'choice' => $clickedRef ?? json_encode($answer, JSON_UNESCAPED_UNICODE),
            ]);
        }

        return $code;
    }

    private function wordCode(ChallengeItem $item, string $word, string $locale): ?string
    {
        $codes = service('contentRepository')->errorCodes($item->id)['word'] ?? [];
        $ref   = BankSource::wordRef($word);

        if ($locale !== 'id' && ! isset($codes[$ref])) {
            $ref = $this->indonesianWord($item, $ref) ?? $ref;
        }

        return $codes[$ref]['code'] ?? null;
    }

    /** English word of the variant's word bank → its Indonesian pair (normalised). */
    private function indonesianWord(ChallengeItem $item, string $english): ?string
    {
        $content = service('contentRepository');

        foreach ($content->itemBank($item->challenge_node_id) as $other) {
            if ($other->variant_group === $item->variant_group && $other->interaction_type === 'fill_blank_bank'
                && BankSource::wordRef((string) $other->answerKey('text_en')) === $english) {
                return BankSource::wordRef((string) $other->answerKey('text_id'));
            }
        }

        $node = $content->node($item->challenge_node_id);

        foreach ((array) ($node?->variants()[(string) $item->variant_group]['distractors'] ?? []) as $entry) {
            if (is_array($entry) && BankSource::wordRef((string) ($entry['text_en'] ?? $entry['en'] ?? '')) === $english) {
                return BankSource::wordRef((string) ($entry['text_id'] ?? $entry['id'] ?? ''));
            }
        }

        return null;
    }

    private function optionCode(ChallengeItem $item, string $optionKey): ?string
    {
        foreach (service('contentRepository')->scenarioOptions($item->id) as $option) {
            if ($option['kind'] === 'action' && (string) $option['option_key'] === $optionKey) {
                return $this->nullIfBlank((string) ($option['code'] ?? ''));
            }
        }

        return null;
    }

    private function nullIfBlank(string $value): ?string
    {
        return trim($value) === '' ? null : trim($value);
    }
}
