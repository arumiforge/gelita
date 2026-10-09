# Parallel agents and subagents

## Before spawning
- Split work by file ownership: each agent gets an explicit list of files or directories it may change. Two agents never own the same file. Shared files (`CLAUDE.md`, `docs/PROGRESS.md`, `app/Config/Routes.php`, language files) belong to one agent or to the coordinating session.
- If a task needs a file owned by another agent, the agent stops and reports it instead of editing.

## Prompt template (write in English)
Every agent prompt is self-contained; the agent has none of your context.

```
Goal: <one sentence, the outcome>
Context: <why, and what is already decided; link plan section, e.g. Dok 3 §8.2>
You may change: <exact files/dirs>
Do not change: <files owned by others>
Rules:
- Code, identifiers, and comments in English.
- User-facing text in friendly Indonesian via app/Language (see .claude/rules/ui-copy.md); add the en/ counterpart.
- Public URL paths are Indonesian slugs; controllers and methods English.
- Use terms from .claude/rules/glossary.md.
- Run vendor/bin/phpunit --no-coverage; it must be green.
Done when: <checkable criteria>
Report back (in English): files changed, tests run and results, decisions or deviations, open questions.
```

## Parallel sessions on branches
- One short-lived branch per part, cut from `main`, listed with its owner in `docs/PROGRESS.md`.
- Each session writes only its own section of the decision log and updates only its own rows in `docs/PROGRESS.md`.
- Remove the branch row from `docs/PROGRESS.md` once its PR is merged.
