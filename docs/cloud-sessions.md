# Cloud sessions with several Claude accounts

GELITA is developed in Claude Code cloud sessions (claude.ai/code), from more than one Claude account. A session runs in a fresh container, knows nothing of earlier sessions, and ends without warning when the account reaches its usage limit. This guide says what a session gets from the repository, what has to be set up once per account, and how work is handed from one session to the next.

Written 14 November 2026, extended 15 November 2026 (skills, hooks, reviewer, plugins, Desktop). The settings screens of claude.ai change; if a name below no longer matches, the meaning still holds.

## 1. What every session gets from the repository

These travel with the clone, so they are the same for every account:

| What | Where | Gives the session |
|---|---|---|
| Working rules | `CLAUDE.md` | Identity, branches, tests, the quick map, known pitfalls |
| Topic rules | `.claude/rules/` | Language, UI copy, glossary, parallel agents, research-revision sessions; most load only with the files they concern |
| Session settings | `.claude/settings.json` | Commits as `Arumi Studios <arumiforge@gmail.com>` whatever the account, no attribution lines, the reply-in-English reminder, the hooks below, no plugin suggestion cards |
| Skills | `.claude/skills/` | `account-setup` (section 2), `continue-work` and `finish-step` (section 3), `dev-install` (dev server and `tools/qa` checks), `new-migration` |
| Reviewer | `.claude/agents/gelita-reviewer.md` | A read-only subagent that reviews a diff against this project's rules and pitfalls |
| Hooks | `.claude/hooks/` | After an edit: `php -l` on a PHP file, and `LanguageFilesTest` when a language file changed. Before a command: a second full PHPUnit run is refused while one is running |
| Environment script | `tools/dev/cloud-setup.sh` | Master copy of the setup script each account pastes into its environment (section 2) |
| Dev server | `tools/dev/serve.sh` | A development install on MariaDB in one command |
| CI | `.github/workflows/tests.yml` | Every test file in its own process on each pull request; the check to require is `tests` |
| State of the work | `docs/PROGRESS.md` | Open items, and one row per active branch saying where its session stopped |
| Decisions | `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`, `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md` | Why things are the way they are |

These do **not** travel, so nothing may depend on them:

- The chat itself and Claude's auto memory. A new session only knows what was committed and pushed.
- Files that were not pushed. The container is discarded.
- The account's environment (setup script, variables, network), its GitHub connection, and its plugins, skills and connectors. They are set per account (section 2). An environment can be shared between accounts only inside one Team or Enterprise organization.
- Plugins named in a repository's `.claude/settings.json`. A cloud session does not install them, so this repository names none and keeps what it needs as its own skills, reviewer and hooks.

## 2. Checklist per Claude account

Do this once for each account that will work on GELITA.

1. **Connect GitHub.** In claude.ai/code, connect the GitHub account that can push to `arumiforge/gelita`, and make sure the Claude GitHub App is installed on that repository. Without it a session can read nothing, or can read but not push.
2. **Create a cloud environment for GELITA** (the name is free; "Gelita" is used in this repository's documents). By hand as below, or start a session on the repository and say "set up this account": the skill `account-setup` proposes the same environment for one approval, and it is used from the next session on.
   - **Setup script:** paste the whole of `tools/dev/cloud-setup.sh`. It installs MariaDB, PHP 8.5 and Node 24 (the server's stack since 23 November 2026; the image brings PHP 8.3 and Node 22) and `vendor/` in about 45 seconds. Without it a session starts with no `vendor/`, and a plain `composer install` takes about 10 minutes and 12 GB there (`CLAUDE.md` → *Cloud sessions*).
   - **Environment variables:** `BASH_DEFAULT_TIMEOUT_MS=600000` and `BASH_MAX_TIMEOUT_MS=1200000`. The full test suite takes about 7.5 minutes, longer than the default command limit.
   - **Network access:** *Limited*, with the package managers allowed, plus the hosts the Pustaka commands fetch from: `commons.wikimedia.org`, `upload.wikimedia.org`, `i.ytimg.com`, `www.youtube.com`, `www.youtube-nocookie.com`, `vimeo.com`, `player.vimeo.com`, `i.vimeocdn.com`, `drive.google.com`. Unrestricted access works too; it is only wider than the project needs.
3. **Plugins, skills and connectors.** None is required besides GitHub, and turning them off takes no ability away: the model, its file, shell, web and GitHub tools, the browser, subagents, and `/code-review`, `/security-review` and `/simplify` are built in. Every enabled skill is listed to the model on every turn, used or not, and some plugins add rules of their own to every session, so sessions on different accounts behave differently. Switches are per account, in the Claude app (*Customize* → plugins and skills), not per repository.
   - **Turn off:** plugins that add their own working rules (ponytail, superpowers), the Claude Education Skills Library, Backend Design, design-skills, fullstack-dev-skills, frontend-design, feature-dev, engineering, data, design, enterprise-search, cloudflare, canva, desktop-commander, browser-use, pdf-viewer, diagrams, modern-web-guidance, claude-md-management, skill-creator, claude-code-setup, and the chat skills (`deep-research`, `learn`, `morning`, `canvas-design`, and the like) unless the account uses them in ordinary chats.
   - **Leave on:** the document skills (`xlsx`, `docx`, `pdf`, `pptx`); `xlsx` helps with the question-bank workbook.
   - **Turn on for one job, then off again:** claude-security for a full audit, the Education library when hints or test instruments are designed, skill-creator when a skill is written.
4. **Check the first session.** Start a session on the repository in that environment and ask it to confirm: `vendor/autoload.php` exists, `mariadbd` is installed, both variables are set, and one test file is green (`vendor/bin/phpunit --no-coverage tests/unit/TryLimitTest.php`).

When `tools/dev/cloud-setup.sh` changes, paste the new version into the environment of **every** account. The script runs when the environment's container is built, not in each session, so an account with an old copy keeps working the old way without any warning.

## 3. Handing work from one session to the next

### While working

A session can stop between any two steps, so it works as if every step were its last:

- **Commit and push after every finished step**, not at the end. A step is finished when its tests are green.
- **Keep `docs/PROGRESS.md` current in the same push**: the open items of the work as a checklist a stranger could follow (what, where, and anything already promised to the owner), and the branch's row under *Active branches* (branch, scope, PR number, what was verified and what was not).
- **Record decisions when they are made**, in the decision log, not from memory at the end.
- **Say what was not run.** If only some tests ran after the last commit, the row says so.

### Starting the next session

The owner starts a session, on any account, with one sentence that names the place and gives the permission:

> Continue the work in `docs/PROGRESS.md` → *section name*. PR #N, branch `X`; you may push to that branch.

The last part matters. A cloud session is given a branch of its own (a name like `claude/…`) and pushes nowhere else unless the owner says so. With the sentence above it works on branch `X` and leaves its own branch untouched.

The session then follows `.claude/skills/continue-work/`: fetch and check out the named branch, read its PR and its `docs/PROGRESS.md` rows, see what CI says, and go on with the first open item. Before it stops it updates the PR description and the rows again.

### Rules that keep this safe

- **One session per branch at a time.** Two sessions pushing to one branch overwrite or block each other. Work that should run in parallel gets one branch per session, with files split as in `.claude/rules/parallel-agents.md`.
- **After the PR is merged**, the next session starts a new branch from `main`; it does not push to the merged branch.
- **Sessions cannot delete branches.** The unused `claude/…` branch of a continuing session, and any branch GitHub did not delete on merge, are listed for the owner at the end of the session.
- **Nothing against production.** No session runs `gelita:reset`, migrations, or deploy scripts on the production server, whichever account it runs on.

## 4. Other AI tools

Tools other than Claude Code (ChatGPT for pictures, ElevenLabs for backup narration) do not follow `CLAUDE.md`. They read the root `AGENTS.md`, work in their own folder under `ai-studio/`, and never change the application. A Claude Code session installs what the owner accepted (`ai-studio/README.md`).

## 5. Claude Desktop on Windows

- **Cloud session from Desktop** (the recommended way): choose the GELITA environment under *Cloud* in the environment selector. It is the same session as in the browser; nothing else is set up on the PC.
- **Local session:** the repository's `.claude/` folder is read as in the cloud, and so are `%USERPROFILE%\.claude` and the account's skills. The PC needs PHP 8.2 or later, Composer, Git for Windows, and MariaDB or MySQL (`docs/08_DEPLOYMENT.md`, Jalur W). The hooks and `tools/dev/*.sh` are bash scripts and need Git Bash or WSL. Optional, local only: the plugin `php-lsp@claude-plugins-official` with the `intelephense` binary gives PHP diagnostics after edits; cloud sessions do not start language servers.
- Local sessions were not tried for this guide (15 November 2026).

