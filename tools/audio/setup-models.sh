#!/usr/bin/env bash
# Unduh model TTS & ASR generator audio GELITA ke tools/audio/models/ (tidak di-commit).
# Setiap berkas diperiksa sha256-nya, jadi hasil generator dapat diulang persis.
#
#   bash tools/audio/setup-models.sh
#
# Butuh: curl, unzip, zstd (tar --zstd), pip, npm.
set -Eeuo pipefail

cd "$(dirname "$0")"
M="${GELITA_AUDIO_MODELS:-models}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

check() { # berkas sha256
    echo "$2  $1" | sha256sum --check --quiet || { echo "sha256 tidak cocok: $1" >&2; exit 1; }
}

# 1. Bahasa Indonesia: Mimic 3 jv_ID/google-gmu_low (VITS multi-penutur, CC BY-SA 4.0,
#    dilatih dari OpenSLR 41 — data TTS bahasa Jawa Google & UGM). Git LFS di GitHub.
mkdir -p "$M/jv"
base=https://raw.githubusercontent.com/MycroftAI/mimic3-voices/master/voices/jv_ID/google-gmu_low
for f in config.json phonemes.txt speakers.txt LICENSE README.md SOURCE; do
    curl -fsSL -o "$M/jv/$f" "$base/$f"
done
curl -fsSL -o "$M/jv/generator.onnx" \
    https://media.githubusercontent.com/media/MycroftAI/mimic3-voices/master/voices/jv_ID/google-gmu_low/generator.onnx
check "$M/jv/generator.onnx" 6813fa08c56261b2092346788ac51e97635d4f9fe148b35308bb9abcbdb84f10
check "$M/jv/phonemes.txt" ad748978c0a515f0dd17edf0bfe03caf799ab0de4dfb56c535e719974450ab2e
check "$M/jv/speakers.txt" b40baa29e2dc7f20dcecf97cdaf181e79fe881831abf9f72aae72bf1ff11becf

# 2. English: suara Piper (medium, 22,05 kHz). kristin & jenny dari modul Go
#    piper-tts-go (proxy.golang.org), joe dari paket PyPI joe-us-piper-voice.
go_voice() { # nama modul versi sha256-onnx
    local name=$1 module=$2 version=$3 sum=$4
    curl -fsSL -o "$TMP/$name.zip" "https://proxy.golang.org/github.com/$module/@v/$version.zip"
    unzip -q -o "$TMP/$name.zip" -d "$TMP/$name"
    mkdir -p "$M/piper/$name"
    tar --zstd -xf "$(find "$TMP/$name" -name dist.tzst)" -C "$M/piper/$name"
    cp "$(find "$TMP/$name" -name MODEL_CARD.txt)" "$M/piper/$name/MODEL_CARD.txt"
    check "$M/piper/$name/voice.onnx" "$sum"
}
go_voice kristin piper-tts-go/piper-voice-kristin v0.0.0-20250207071202-92b112b5d54c \
    5849957f929cbf720c258f8458692d6103fff2f0e3d3b19c8259474bb06a18d4
go_voice jenny piper-tts-go/piper-voice-jenny v0.0.0-20250207071027-3105f05c2ab9 \
    469c630d209e139dd392a66bf4abde4ab86390a0269c1e47b4e5d7ce81526b01

pip download --quiet --no-deps joe-us-piper-voice==1.0.0 -d "$TMP/joe"
unzip -q -o "$TMP"/joe/joe_us_piper_voice-1.0.0-py3-none-any.whl -d "$TMP/joe/x"
mkdir -p "$M/piper/joe"
cp "$TMP/joe/x/joe_us_piper_voice/data/en_US-joe-medium.onnx" "$M/piper/joe/voice.onnx"
cp "$TMP/joe/x/joe_us_piper_voice/data/en_US-joe-medium.onnx.json" "$M/piper/joe/voice.json"
cp "$TMP/joe/x/joe_us_piper_voice/data/MODEL_CARD" "$M/piper/joe/MODEL_CARD.txt"
check "$M/piper/joe/voice.onnx" 58afce0321b8d9c46d7cdf9c16500cc55a793b4220212dba6b70fb788b3baf06

# 3. QA: Whisper small (ONNX, transformers.js) — hanya untuk memeriksa kejelasan narasi.
#    --ignore-scripts: binari CPU onnxruntime-node sudah ada di paketnya.
npm install --ignore-scripts --no-audit --no-fund --silent

echo "Model siap di $(cd "$M" && pwd)"
