# Dok 3 §12.3 — real-device checklist (owner)

The automated part of §12.3 is done in the container (see the decision log, "Dok 3 — tests and done criteria (§12)"). Only real hardware is left. This list is for the owner; tick each line and note anything odd (device, browser, screen, what happened).

## Before you start

- A test install (never production), after `gelita:reset`, with the study's *Cara wilayah terbuka* set to **bebas** so any region can be opened. Optional: `php spark gelita:simulasi --siswa=12` for filled admin tables.
- One new student account per device (register through `/daftar`).
- Sound on. Fullscreen button in the HUD used on tablet and phone.

## 1. Play one full region on each device

Devices: a laptop (Chrome or Edge), a tablet (Android Chrome or iPad Safari), and a phone held landscape in fullscreen.

For each device, from `/masuk` to the region's "tuntas" screen:

- [ ] Intro and region dialog: narration plays, tap moves on, text readable without zooming.
- [ ] Music: after the first tap, background music plays on the region map and on each challenge screen; with the HUD sound button off it stays silent, and switching sound back on starts it again (display plan Phase 0).
- [ ] Map: the 16:9 stage fills the screen with bars on the sides or top; the page itself never scrolls.
- [ ] Susun Gambar: drag and tap-to-swap both work; pieces do not jump when the finger lifts.
- [ ] Isi Rumpang: tap a blank, then a word; the passage panel scrolls inside its box only.
- [ ] Benar atau Salah: every card can be answered; the statement image opens larger and closes.
- [ ] Cari Objek: every object can be tapped without hitting its neighbour; the clue text changes.
- [ ] Tries indicator, hint dialog, result dialog, and finished screen look right; no Jaka on challenge screens.
- [ ] Pustaka and Kenali open with the same content as before the overhaul; lightbox closes.
- [ ] Phone only: turning to portrait shows the rotate screen; turning back resumes where you were.
- [ ] Nothing is cut off or overlapping at the device's real size (note the model if it is).

## 2. Screen reader (one pass is enough)

On the laptop with NVDA (Windows) or VoiceOver (macOS), or VoiceOver on the iPad:

- [ ] Each blank is read as "Rumpang N", and after filling as "Rumpang N: word".
- [ ] Each statement card is read with its statement, then "Benar"/"Salah".
- [ ] Each Cari Objek object is read by name; a new clue is announced without moving focus.
- [ ] The hint and result dialogs are read when they open; Escape closes the hint dialog.

## 3. Exports on your own computer

- [ ] Download one XLSX and one CSV-ZIP from *Unduh data* and open them in Excel or LibreOffice: Indonesian characters display correctly and every sheet has a Kamus Data entry.
- [ ] In R, read a CSV with `read.csv("peserta.csv", fileEncoding = "UTF-8-BOM")` or `readr::read_csv()`. Plain `read.csv()` also works when R runs in a UTF-8 locale (default on Windows R 4.2+, macOS, and most Linux desktops).

## 4. Touch and keyboard (display plan Phase F)

On the tablet (iPad Safari or Android Chrome) and the phone held landscape:

- [ ] Fast double taps on buttons, words, and pieces never zoom the page; pinch zoom still works (iOS and Android).
- [ ] Every button, option, word, blank, piece, object, pin, and card shows a short press effect; nothing stays highlighted after the finger lifts (no "sticky hover").
- [ ] Small controls are easy to hit with a finger: slide dots, "Keluar", narrator buttons, HUD icons, hint chip, language switch, word chips, and blanks.
- [ ] A long press on a picture, piece, object, or character shows no copy/save menu and selects no text; story and reading text can still be selected.
- [ ] Susun Gambar: drag a piece with a finger onto another to swap them; the piece follows the finger; dropping outside the board or on the same piece changes nothing; tapping two pieces still swaps.
- [ ] Cari Objek: the ⤢ button enlarges the scene to the whole screen with the clue at the top; objects can be tapped while enlarged; the button (or Esc on a keyboard) shrinks it back; it closes by itself when the challenge ends.
- [ ] Story slides, region dialog, Kenali, and Pustaka: swiping left goes to the next page, swiping right to the previous one; swiping inside a scrolling text box only scrolls it.
- [ ] `/masuk`, `/daftar` steps 1–3, `/ganti-sandi`, and the reflection form: the on-screen keyboard never covers the field being typed in; the screen moves back when the keyboard closes. The keyboard shows "Next"/"Done" keys, and usernames and school names are not autocorrected.
- [ ] iPhone/iPad: tapping a text field does not zoom the page in.
- [ ] Toasts: a message card appears top right, closes when tapped, and is readable above an open dialog.

When done, tell Claude in the project thread what you found; the PROGRESS line for §12.3 is ticked only after this list.
