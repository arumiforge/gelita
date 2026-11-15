#!/bin/bash
# PostToolUse (Edit|Write): the repository has no linter, so syntax-check an
# edited PHP file and keep the id/en language files paired. Exit code 2 shows
# stderr to Claude.
file=$(php -r 'echo json_decode(stream_get_contents(STDIN), true)["tool_input"]["file_path"] ?? "";' 2>/dev/null)
[[ $file == *.php && -f $file ]] || exit 0

out=$(php -l "$file" 2>&1) || { echo "$out" >&2; exit 2; }

if [[ $file == */app/Language/* ]]; then
    cd "$(dirname "$0")/../.." || exit 0
    [ -x vendor/bin/phpunit ] || exit 0
    out=$(vendor/bin/phpunit --no-coverage tests/unit/LanguageFilesTest.php 2>&1) || { echo "$out" | tail -n 25 >&2; exit 2; }
fi
exit 0
