// Transkripsi batch: node asr.mjs jobs.json out.json
// jobs.json = [{ "id": "...", "f32": "/path/16k-mono.f32", "lang": "indonesian"|"english" }, ...]
import { pipeline, env } from '@huggingface/transformers';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
env.localModelPath = path.join(here, 'node_modules/sts-whisper-small/models/');
env.allowRemoteModels = false;
env.allowLocalModels = true;

const [jobsFile, outFile] = process.argv.slice(2);
const jobs = JSON.parse(fs.readFileSync(jobsFile, 'utf8'));

const asr = await pipeline('automatic-speech-recognition', 'Xenova/whisper-small', { dtype: 'q8', device: 'cpu' });

const out = {};
for (const job of jobs) {
  const buf = fs.readFileSync(job.f32);
  const audio = new Float32Array(buf.buffer, buf.byteOffset, buf.byteLength / 4);
  const res = await asr(audio, { language: job.lang, task: 'transcribe', chunk_length_s: 30, stride_length_s: 5 });
  out[job.id] = res.text.trim();
  process.stderr.write(`${job.id}: ${out[job.id]}\n`);
}
fs.writeFileSync(outFile, JSON.stringify(out, null, 1));
