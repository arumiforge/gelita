#!/usr/bin/env bash
# Masters one ElevenLabs music track or sound effect to the game's format and
# writes it to out/{music|sfx}/{name}.mp3. The target (loop or not, loudness,
# bit rate) comes from this sound's row in sounds.csv, so the file name and the
# format cannot drift from the work list.
#
# Usage: master.sh CODE RAW.mp3 [START [END]]
#   CODE   a `kode` of sounds.csv, e.g. music/map or sfx/correct
#   START  optional: seconds to skip at the start of the take (e.g. 4.5)
#   END    optional: second of the take where the usable part ends (needs START; 0 is fine)
#
# Music: trimmed, then made into a seamless loop by folding its last
# LOOP_XFADE seconds over its first ones (equal-power crossfade, so the end
# runs straight into the start), moved to the target loudness with one fixed
# gain, peaks held under about -1.5 dBFS, MP3 stereo 44.1 kHz CBR. No fade at
# either end: a fade would be heard every time the loop comes round.
# Sound effect: trimmed, set to its target loudness (or peak, for a hit too
# short to measure), 2 ms fade in and 20 ms fade out against clicks, MP3 stereo.
# Nothing else is added: no reverb, no filter, no noise.
set -euo pipefail

if [ "$#" -lt 2 ] || [ "$#" -gt 4 ]; then
    echo "Usage: $0 CODE RAW.mp3 [START [END]]" >&2
    exit 2
fi

code=$1
raw=$2
start=${3:-}
end=${4:-}
here=$(cd "$(dirname "$0")" && pwd)
LOOP_XFADE=3

# The first seven columns of sounds.csv hold no commas, so a plain split is safe.
row=$(awk -F, -v c="$code" 'NR > 1 && $1 == c {print $2 "|" $3 "|" $4 "|" $5 "|" $6 "|" $7; exit}' "$here/sounds.csv")
if [ -z "$row" ]; then
    echo "Unknown code $code: use a kode of sounds.csv (e.g. music/map, sfx/click)" >&2
    exit 2
fi
IFS="|" read -r kind loop target_seconds kbps lufs peak <<<"$row"
out="$here/out/$code.mp3"
mkdir -p "$(dirname "$out")"

tmp=$(mktemp -d)
trap '[ -n "${KEEP_TMP:-}" ] && echo "kept $tmp" >&2 || rm -rf "$tmp"' EXIT

# 1. Decode, cut to START..END when given, trim silence at both ends (below -50 dB).
cut=''
if [ -n "$start" ]; then
    cut="atrim=start=${start}${end:+:end=${end}},asetpts=N/SR/TB,"
fi
trim='silenceremove=start_periods=1:start_threshold=-50dB:detection=peak'
ffmpeg -nostdin -v error -y -i "$raw" -ac 2 -ar 44100 \
    -af "${cut}${trim},areverse,${trim},areverse" "$tmp/trimmed.wav"

duration() { ffprobe -v error -show_entries format=duration -of csv=p=0 "$1"; }
loudness() {
    ffmpeg -nostdin -hide_banner -nostats -i "$1" -af "$2" -f null - 2>&1 \
        | awk '/Integrated loudness:/ {found=1} found && /I:/ {print $2; exit}'
}

# 2. Music: fold the tail over the head so the file loops without a seam.
if [ "$loop" = "ya" ]; then
    length=$(duration "$tmp/trimmed.wav")
    body_end=$(awk -v l="$length" -v x="$LOOP_XFADE" 'BEGIN {printf "%.6f", l - x}')
    if awk -v l="$length" -v x="$LOOP_XFADE" 'BEGIN {exit !(l < 4 * x)}'; then
        echo "$raw is too short for a loop (${length} s)" >&2
        exit 1
    fi
    # Three separate passes: one filter graph with asplit stalls, because the
    # tail only arrives after the whole body has been read.
    ffmpeg -nostdin -v error -y -i "$tmp/trimmed.wav" \
        -af "atrim=end=${LOOP_XFADE},afade=t=in:d=${LOOP_XFADE}:curve=qsin" "$tmp/head.wav"
    ffmpeg -nostdin -v error -y -i "$tmp/trimmed.wav" \
        -af "atrim=start=${body_end},asetpts=N/SR/TB,afade=t=out:d=${LOOP_XFADE}:curve=qsin" "$tmp/tail.wav"
    ffmpeg -nostdin -v error -y -i "$tmp/trimmed.wav" \
        -af "atrim=start=${LOOP_XFADE}:end=${body_end},asetpts=N/SR/TB" "$tmp/body.wav"
    ffmpeg -nostdin -v error -y -i "$tmp/tail.wav" -i "$tmp/head.wav" -i "$tmp/body.wav" -filter_complex \
        "[0:a][1:a]amix=inputs=2:normalize=0:duration=longest[seam];[seam][2:a]concat=n=2:v=0:a=1[out]" \
        -map '[out]' "$tmp/shaped.wav"
    fades=''
else
    cp "$tmp/trimmed.wav" "$tmp/shaped.wav"
    d=$(duration "$tmp/shaped.wav")
    fades=",afade=t=in:d=0.002,afade=t=out:st=$(awk -v d="$d" 'BEGIN {printf "%.4f", (d > 0.04 ? d - 0.02 : d / 2)}'):d=0.02"
fi

# 3. One fixed gain to the target, then a limiter on the highest peaks only.
#    The limiter takes a little loudness away, so measure the chain once more
#    and correct the gain by the difference.
limiter='alimiter=limit=0.84:level=false'
if [ -n "$lufs" ]; then
    measured=$(loudness "$tmp/shaped.wav" "ebur128")
    if [ -z "$measured" ] || [ "$measured" = "-70.0" ]; then
        echo "Could not measure the loudness of $raw (too short? give this row a peak target instead)" >&2
        exit 1
    fi
    gain=$(awk -v t="$lufs" -v m="$measured" 'BEGIN {printf "%.2f", t - m}')
    limited=$(loudness "$tmp/shaped.wav" "volume=${gain}dB,${limiter},ebur128")
    gain=$(awk -v g="$gain" -v t="$lufs" -v m="$limited" 'BEGIN {printf "%.2f", g + t - m}')
    what="${measured} LUFS -> ${lufs} LUFS"
else
    measured=$(ffmpeg -nostdin -hide_banner -nostats -i "$tmp/shaped.wav" -af volumedetect -f null - 2>&1 \
        | awk '/max_volume:/ {print $5; exit}')
    gain=$(awk -v t="$peak" -v m="$measured" 'BEGIN {printf "%.2f", t - m}')
    what="peak ${measured} dB -> ${peak} dB"
fi

ffmpeg -nostdin -v error -y -i "$tmp/shaped.wav" -af "volume=${gain}dB,${limiter}${fades}" \
    -ac 2 -ar 44100 -c:a libmp3lame -b:a "${kbps}k" -map_metadata -1 "$out"

length=$(duration "$out")
echo "$code: ${what} (gain ${gain} dB), ${length} s, ${kbps} kbps"
if awk -v l="$length" -v t="$target_seconds" 'BEGIN {exit !(l < t * 0.6 || l > t * 1.6)}'; then
    echo "  note: sounds.csv asks for about ${target_seconds} s; listen whether this length works" >&2
fi
