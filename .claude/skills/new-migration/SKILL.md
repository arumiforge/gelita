---
name: new-migration
description: Add or change a database migration the GELITA way (numbering, base class, test schema, test, MariaDB check). Use before writing any file in app/Database/Migrations or changing a table's columns.
---

# New migration

Never run `migrate`, `migrate:rollback`, `gelita:reset`, or a deploy script against the production server. The owner deploys.

## Write it

- **File:** `app/Database/Migrations/2026-01-01-00NN00_VerbNoun.php`, the next free hundred after the last file (a follow-up of the same part may take a ten in between, as `003910` did).
- **Class:** extends `App\Database\GelitaMigration` and uses its column helpers (`id()`, `ref()`, `flag()`, `nullableFlag()`, `json()`, `datetime()`, `datetimeNow()`), so every table is InnoDB, `utf8mb4_unicode_ci`, BIGINT keys.
- **Docblock:** the plan section it implements, the steps of `up()` numbered, and what `down()` does.
- **Adds only, by default.** A migration that deletes or renames stored data needs the owner's decision in the decision log first, and a way to count what it will touch beforehand (`gelita:log:hitung` did that for `003600`).
- **Stored codes keep their names** (`.claude/rules/language.md`).

## Keep the rest in step

- **Test schema:** `tests/_support/Database/ResetTables.php` carries the same columns for SQLite. Add them there (`TYPE[(length)][?][=default]`).
- **Test:** a `*MigrationTest` after the pattern of `tests/unit/DerivedLayerMigrationTest.php`: build the schema, take away what the migration adds, run `up()`, assert, run `down()`, assert.
- **Racing writes** to the new columns use `updateIf()` / `updateOrStale()` (`App\Models\Concerns\ConditionalUpdate`).
- **A column that reaches a download** also needs its sheet and the data dictionary (`ResearchDictionary`, a new `kamus-…` version).

## Check on MariaDB

SQLite proves neither the DDL nor the locks.

1. `tools/dev/serve.sh` (skill `dev-install`); it runs `php spark migrate`.
2. `php spark migrate:rollback`, then `php spark migrate` again. On a database that was created with the new file already present, the rollback undoes every migration; that is a valid check too, only slower.
3. Stop the server and remove `.env`.

## Record

A section in the decision log: what the migration adds, what `down()` removes, and that it was run up, down and up again on MariaDB (or that it was not).
