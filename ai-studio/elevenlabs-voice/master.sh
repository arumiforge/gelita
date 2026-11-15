#!/usr/bin/env bash
# Masters one ElevenLabs take to the game's narration format
# (docs/naskah-cerita.md → format: MP3 mono, 44.1 kHz, about -16 LUFS,
# 0.3 s of silence at the start and the end).
#
# Usage: master.sh RAW.mp3 OUT.mp3
#
# Nothing is added to the voice: no reverb, no filter, no noise bed. The take
# is trimmed, moved to the target loudness with one fixed gain, kept under
# about -1.5 dBFS by a limiter that only acts on the highest peaks, and padded.
set -euo pipefail

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 RAW.mp3 OUT.mp3" >&2
    exit 2
fi

raw=$1
out=$2
target_lufs=-16
tmp=$(mktemp --suffix=.wav)
trap 'rm -f "$tmp"' EXIT

# 1. Trim silence at both ends (below -50 dB), keep everything in between.
trim='silenceremove=start_periods=1:start_threshold=-50dB:detection=peak'
ffmpeg -v error -y -i "$raw" -ac 1 -ar 44100 \
    -af "${trim},areverse,${trim},areverse" "$tmp"

# 2. Fixed gain to the target loudness, then a peak limiter. The limiter and
#    the silence at both ends take a little loudness away again, so measure
#    the finished chain once and correct the gain by the difference.
loudness() {
    ffmpeg -hide_banner -nostats -i "$1" -af "$2" -f null - 2>&1 \
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
ffmpeg -v error -y -i "$tmp" \
    -af "volume=${gain}dB,${limiter},afade=t=in:d=0.01,afade=t=out:st=${fade_out}:d=0.01,${pad}" \
    -ac 1 -ar 44100 -c:a libmp3lame -b:a 96k -map_metadata -1 "$out"

echo "$(basename "$out"): ${measured} LUFS -> ${target_lufs} LUFS (gain ${gain} dB)"
