#!/usr/bin/env bash
# Masters one ElevenLabs take to the game's narration format
# (docs/naskah-cerita.md → format: MP3 mono, 44.1 kHz, 96 kbps, about -16 LUFS,
# 0.3 s of silence at the start and the end), the same as Mbah Kedu's takes.
# A mono file plays in both earphones. (Owner, 15 November 2026: a stereo
# 192 kbps version was tried the same day and dropped again.)
#
# Usage: master.sh RAW.mp3 OUT.mp3
#        WPM=130 WORDS=34 master.sh RAW.mp3 OUT.mp3
#
# With WPM and WORDS (the line's word count) the trimmed take is slowed to that
# pace, never sped up, with atempo (pitch kept). English takes
# use WPM=130 (owner, 15 November 2026: the English voice was too fast for
# grade 5; one pace for every English line, so no scored line stands out).
# rubberband was tried first and dropped: the owner heard an echo in earphones.
#
# Nothing is added to the voice: no reverb, no filter, no noise bed. The take
# is trimmed, moved to the target loudness with one fixed gain, kept under
# about -1.5 dBFS by a limiter that only acts on the highest peaks, and padded.
# The pace is changed only when WPM and WORDS are given.
set -euo pipefail

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 RAW.mp3 OUT.mp3" >&2
    exit 2
fi

raw=$1
out=$2
target_lufs=-16
tmp=$(mktemp --suffix=.wav)
slow=$(mktemp --suffix=.wav)
trap 'rm -f "$tmp" "$slow"' EXIT

# 1. Trim silence at both ends (below -50 dB), keep everything in between.
trim='silenceremove=start_periods=1:start_threshold=-50dB:detection=peak'
ffmpeg -nostdin -v error -y -i "$raw" -ac 1 -ar 44100 \
    -af "${trim},areverse,${trim},areverse" "$tmp"

# 1b. Optional pace: tempo = WPM / measured words per minute, applied only
#     when the take is faster than WPM.
if [ -n "${WPM:-}" ] && [ -n "${WORDS:-}" ]; then
    speech=$(ffprobe -v error -show_entries format=duration -of csv=p=0 "$tmp")
    tempo=$(awk -v w="$WORDS" -v d="$speech" -v t="$WPM" 'BEGIN {printf "%.4f", t / (w / d * 60)}')
    if awk -v x="$tempo" 'BEGIN {exit !(x < 1)}'; then
        ffmpeg -nostdin -v error -y -i "$tmp" \
            -af "atempo=${tempo}" "$slow"
        mv "$slow" "$tmp"
        echo "$(basename "$out"): tempo ${tempo} to ${WPM} words per minute" >&2
    fi
fi

# 2. Fixed gain to the target loudness, then a peak limiter. The limiter and
#    the silence at both ends take a little loudness away again, so measure
#    the finished chain once and correct the gain by the difference.
loudness() {
    ffmpeg -nostdin -hide_banner -nostats -i "$1" -af "$2" -f null - 2>&1 \
        | awk '/Integrated loudness:/ {found=1} found && /I:/ {print $2; exit}'
}
limiter='alimiter=limit=0.84:level=false'
pad='adelay=300,apad=pad_dur=0.3'
measured=$(loudness "$tmp" "ebur128")
if [ -z "$measured" ]; then
    echo "Could not measure the loudness of $raw" >&2
    exit 1
fi
gain=$(awk -v t="$target_lufs" -v m="$measured" 'BEGIN {printf "%.2f", t - m}')
limited=$(loudness "$tmp" "volume=${gain}dB,${limiter},${pad},ebur128")
gain=$(awk -v g="$gain" -v t="$target_lufs" -v m="$limited" 'BEGIN {printf "%.2f", g + t - m}')

# 3. 10 ms fades against clicks, 0.3 s of silence before and after, then MP3
#    mono 44.1 kHz at 96 kbps.
duration=$(ffprobe -v error -show_entries format=duration -of csv=p=0 "$tmp")
fade_out=$(awk -v d="$duration" 'BEGIN {printf "%.3f", d - 0.01}')
encode() {
    ffmpeg -nostdin -v error -y -i "$tmp" \
        -af "volume=${1}dB,${limiter},afade=t=in:d=0.01,afade=t=out:st=${fade_out}:d=0.01,${pad}" \
        -ac 1 -ar 44100 -c:a libmp3lame -b:a 96k -map_metadata -1 "$out"
}
encode "$gain"

# 4. The MP3 coder can push a peak above the limiter's ceiling, by a different
#    amount on each encode. While the decoded file peaks above -1 dBFS, lower
#    the gain by 0.2 dB and encode again (at most 10 times).
decoded_peak() {
    ffmpeg -nostdin -hide_banner -nostats -i "$out" -af ebur128=peak=sample -f null - 2>&1 \
        | awk '/Sample peak:/ {found=1} found && /Peak:/ {print $2; exit}'
}
tries=0
while [ "$tries" -lt 10 ] && awk -v p="$(decoded_peak)" 'BEGIN {exit !(p > -1.0)}'; do
    gain=$(awk -v g="$gain" 'BEGIN {printf "%.2f", g - 0.2}')
    encode "$gain"
    tries=$((tries + 1))
done

echo "$(basename "$out"): ${measured} LUFS -> ${target_lufs} LUFS (gain ${gain} dB)"
