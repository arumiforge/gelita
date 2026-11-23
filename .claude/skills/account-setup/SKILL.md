---
name: account-setup
description: Prepare a Claude account that has not worked on GELITA before (cloud environment, checks, what the owner turns off). Use when the owner says "set up this account" or "siapkan akun ini", or in the first session of a new account.
---

# Set up a Claude account for GELITA

Everything else travels with the repository. Per account there are only the GitHub connection, the cloud environment, and the account's plugins. Full list: `docs/cloud-sessions.md` → *Checklist per Claude account*.

## 1. GitHub

`git fetch origin main` must work. If it does not, the owner connects GitHub and installs the Claude GitHub App on `arumiforge/gelita`; stop here until then. Pushing is proven by the first real push, not by a test push.

## 2. Cloud environment

1. Read the environment this session runs in with the environment tool.
2. It is right when its setup script is `tools/dev/cloud-setup.sh` unchanged and both variables are set (`BASH_DEFAULT_TIMEOUT_MS=600000`, `BASH_MAX_TIMEOUT_MS=1200000`). Then go to step 3.
3. Otherwise look at the account's other environments first: one named like GELITA may already be right, and the owner only has to pick it for the next session.
4. If none is, propose a new personal environment. The owner sees one approval prompt:
   - name `GELITA`, for the repository `arumiforge/gelita`;
   - setup script: the whole of `tools/dev/cloud-setup.sh`, unchanged;
   - the two variables above;
   - network *Limited*, package managers allowed, plus the hosts listed in `docs/cloud-sessions.md` → *Network access*.
5. If the tool refuses the variables, give the owner the two lines to add by hand in the environment's settings.
6. The setup script does not run in this session. It runs when the next session starts on that environment.

Never put a password, token or key in the script or the variables.

## 3. Check

In a session on the right environment: `vendor/autoload.php` exists, `mariadbd` is installed, `php -v` says 8.5 and `node -v` says v24 (PHP 8.3 or Node 22 means the environment holds an older copy of the script), both variables are set, and `vendor/bin/phpunit --no-coverage tests/unit/TryLimitTest.php` is green.

## 4. What only the owner can do

Plugins and account skills are switched in the Claude app, not from a session. Tell the owner to turn off the ones in `docs/cloud-sessions.md` → *Plugins, skills and connectors*. Do not suggest installing any.

## 5. Report

A short checklist: done, and still for the owner to do.
