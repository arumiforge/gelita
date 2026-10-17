// Explicit, field-scoped exceptions to historical snapshots. Baselines stay intact.
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const load=name=>JSON.parse(fs.readFileSync(path.join(root,name),'utf8'));
const audioRevision=load('provenance/nonnarration-audio-20261014.json');
const iconRevision=load('provenance/language-slot-update-20261014.json');
export function assertRevisionPreserved(before,actual){
  assert.ok(actual,'Missing original entry '+before.key);
  const revision=[audioRevision,iconRevision].find(r=>r.assets.some(a=>a.key===before.key));
  const allowed=new Set(revision?.allowed_changed_fields||[]);
  const stable=o=>Object.fromEntries(Object.entries(o).filter(([k])=>!allowed.has(k)));
  assert.deepEqual(stable(actual),stable(before),'Original entry changed outside authorized fields: '+before.key);
}
export function assertAudioPreserved(data){
  const baseline=load('archive/audio-baseline.json');
  const audio=data.filter(a=>a.size==='Audio');
  assert.equal(audio.length,97);
  assert.deepEqual(audio.map(a=>a.key),baseline.map(a=>a.key));
  assert.equal(audioRevision.assets.length,9);
  for(const a of baseline){
    const actual=audio.find(x=>x.key===a.key);
    if(a.cat==='Narasi')assert.deepEqual(actual,a,'Narration changed: '+a.key);
    else{assert.ok(['Musik','Efek suara'].includes(a.cat));assertRevisionPreserved(a,actual);assert.ok(actual.file);}
  }
  return {total:97,narration_unchanged:88,available:9};
}
