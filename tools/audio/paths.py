"""Lokasi model, keluaran sementara, dan folder audio permainan."""
import os
from pathlib import Path

TOOLS = Path(__file__).resolve().parent
REPO = TOOLS.parent.parent
MODELS = Path(os.environ.get('GELITA_AUDIO_MODELS', TOOLS / 'models'))
OUT = Path(os.environ.get('GELITA_AUDIO_OUT', TOOLS / 'out'))
PUBLIC_AUDIO = REPO / 'public' / 'assets' / 'audio'

# Model TTS (lihat setup-models.sh)
JV_MODEL = MODELS / 'jv'                     # Mimic 3 jv_ID/google-gmu_low
PIPER = {
    'kristin': MODELS / 'piper' / 'kristin',  # en_US-kristin-medium
    'jenny': MODELS / 'piper' / 'jenny',      # en_GB-jenny_dioco-medium
    'joe': MODELS / 'piper' / 'joe',          # en_US-joe-medium
}
ASR_SCRIPT = TOOLS / 'asr.mjs'               # Whisper small (npm sts-whisper-small), hanya untuk QA
