# The voices, kept for any ElevenLabs account

The narration's five voices, saved so the work can go on from another ElevenLabs account when this one runs out of credits (owner, 15 November 2026). A voice ID only works inside the account that made the voice, so the repository keeps everything needed to get the same voice again.

| Folder | Speaker | Language | Name in ElevenLabs | Voice ID in `akun-1` |
|---|---|---|---|---|
| `narator/` | Narator | Indonesian and English (one voice) | narator | `RiC7WfZdhpblZ6cqa2Dr` |
| `jaka-id/` | Jaka | Indonesian | Jaka | `XvqPaxRmAYmYehRouVnM` |
| `jaka-en/` | Jaka | English | Jaka english | `N4XCfNUh8sOhcRAo7UPE` |
| `mbah-kedu-id/` | Mbah Kedu | Indonesian | Mbah Kedu | `H1jaNlN43es4HneQ6wdS` |
| `mbah-kedu-en/` | Mbah Kedu | English | Mbah Kedu — English Narration | `fWH0Fha1DCawTa59sMfX` |

All five were made with ElevenLabs Voice Design in the first account, here called `akun-1` (Starter plan). Each folder holds:

- `voice.json`: the name, description and labels exactly as ElevenLabs shows them, the clone samples, and per account the voice ID, how the voice got there, and its share link.
- `preview.mp3`: the voice's preview as ElevenLabs made it (reference audio).

The model and the settings per kind of line are not repeated here: they are in `../voices.md` and hold for every account.

## Moving to another account

Two ways, in this order (owner, 15 November 2026):

1. **Share link: the exact same voice.** In `akun-1`, open the voice in *My Voices* and share it; keep the link in `voice.json` → `accounts[0].share_link`. If the app offers only publishing to the public Voice Library, ask the owner first. In the new account, open the link and add the voice to *My Voices*. This works only while `akun-1` and the voice still exist, so make the links before that account is given up.
2. **Clone from the repository: the fallback.** If the share link no longer works, make an Instant Voice Clone in the new account (paid plan) from `preview.mp3` plus the files in `clone_samples` (about 2.5 minutes of accepted takes for Mbah Kedu). Name it as in `voice.json`. A clone comes very close but is not the same voice. Jaka's previews are only 7 and 9 seconds, too short for a good clone; his accepted takes become his clone samples once they exist, and Narator's the same.

After either way:

- Add a row to `accounts` in `voice.json`: the account's short name (`akun-2`, …; no e-mail address or workspace ID), the voice ID it shows there, `how` (`share link` or `instant clone`), the date, and its own share link if one is made.
- Put the new voice ID in the voice table of `../voices.md`. The install command copies that cell into each recording's `voice_profile`, so the research data names the voice each take was made with.
- Connect the Claude ElevenLabs connector to the new account, if takes are made through it.
- Make a pilot take of one line already accepted for that speaker and listen to both side by side.

## One voice per speaker and language

Every line of one speaker in one language uses the same voice (`../README.md` → *Making a recording*). So:

- A speaker and language whose lines are not started yet (Narator and Jaka on 15 November 2026) can be made entirely in the new account with the shared or cloned voice.
- A speaker and language already finished (Mbah Kedu) stays as it is. A new line for him later (a changed script) uses the shared voice; a clone is used only with the owner's decision, which goes into `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`.
- Never split one speaker's lines in one language between the original voice and a clone without that decision.

## Credits

On 15 November 2026 `akun-1` had 6,825 of its 40,000 monthly credits left, until the reset on 14 December 2026. Narator and Jaka need about 12,600 per language. The owner decided not to spend the rest now: the takes are made later, on an account with enough credits.
