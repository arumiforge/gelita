#!/bin/bash
# PreToolUse (Bash): one full PHPUnit run peaks above 7 GB, so refuse to start
# a second one while one is running (CLAUDE.md → Tests). Exit code 2 blocks
# the command and shows stderr to Claude.
cmd=$(php -r 'echo json_decode(stream_get_contents(STDIN), true)["tool_input"]["command"] ?? "";' 2>/dev/null)

# A full run: PHPUnit (or `composer test`) with no filter and no test path.
# shortcut: matches the text, not the parsed command, so a command that only
# mentions vendor/bin/phpunit is held back too while a full run is running.
is_full_run() {
    [[ $1 =~ (vendor/bin/phpunit|composer[[:space:]]+(run[[:space:]]+)?test)([[:space:]]|$) ]] \
        && [[ ! $1 =~ (--filter|--testsuite|tests/) ]]
}

is_full_run "$cmd" || exit 0

# GELITA_HOOK_PS replaces the process list in tests/unit/ClaudeHooksTest.php.
while IFS= read -r line; do
    if is_full_run "$line"; then
        echo "A full PHPUnit run is already running (process ${line%% *}). Wait for it to finish, or run only the tests of your change (--filter, or one test file)." >&2
        exit 2
    fi
done <<< "${GELITA_HOOK_PS-$(pgrep -af phpunit)}"
exit 0
