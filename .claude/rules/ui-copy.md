---
paths:
  - "app/Language/**"
  - "app/Views/**"
  - "app/Controllers/**"
  - "app/Helpers/**"
  - "app/Libraries/**"
  - "app/Services/{Export,Report,RawData}*.php"
  - "public/assets/js/**"
  - "docs/naskah-cerita.md"
---

# UI text (friendly Indonesian)

## Where strings live
- Never hardcode UI text in views, controllers, or JS. Use `lang('File.key')` in PHP and the `Js` language file for JS.
- Every new key in `app/Language/id/*` gets an English counterpart in `app/Language/en/*` (`LanguageFilesTest` enforces it). `Admin.php` is the exception: the panel is Indonesian only.

## Two audiences, two tones
- **Students (kelas 5, about 10–11 years old) — game screens.** Use "kamu" and "-mu". Short sentences, everyday words, warm and encouraging, a light "ya" or "yuk" is fine. Never blame ("salah", "gagal") without saying what to do next. Example from the app: "Jawabanmu disimpan apa adanya. Kamu tetap mendapat serpihan cahaya!"
- **Teachers and admins — the panel.** Polite and plain, but not stiff. Prefer sentences without a pronoun ("Kata sandi berhasil diganti."); when one is needed, use "Anda". No programming terms.

## Never show users
- Raw errors, stack traces, HTTP codes, exception names, or words like "null", "server error", "invalid token", "request", "session expired".
- Database codes. Translate stored codes through `admin_label()` / language keys.

## Error and empty-state pattern
Say what happened in plain words, then what to do next.
- Good (student): "Yah, halaman ini belum bisa dibuka. Coba lagi sebentar, ya."
- Bad: "500 Internal Server Error"
- Good (panel): "Belum ada data untuk filter ini. Coba ubah tanggal atau sekolahnya."
- Bad: "Query returned 0 rows."

## One word per concept
Use the terms in `glossary.md`. In particular: always "kata sandi" in full sentences ("sandi" only where space is tight and already used), never "password"; "masuk"/"keluar", never "login"/"logout" in visible text.

## Local formats
Use the helpers in `app/Helpers/ui_helper.php`; do not hand-format.
- Timezone: `Asia/Jakarta` (WIB), set in `app/Config/App.php` and on the DB connection. Store and show WIB; never UTC in the UI.
- Date with time (tables, timelines): `fmt_date()` → `12 Okt 2026 14:30`.
- Date in a sentence: full month name, `12 Oktober 2026`.
- Numeric date where space is tight (e.g. form hints): `12/10/2026` (dd/mm/yyyy), never mm/dd.
- Numbers: `fmt_num()` → `1.250` and `1.234,5`. Percentages: `fmt_pct()` → `73,5%`.
- Research exports (CSV/XLSX) keep ISO 8601 in WIB with milliseconds (`ExportLabels::UNIT_TIME`); that is for analysis software, not for reading.
- Currency, if ever needed: `Rp 1.250.000`.
