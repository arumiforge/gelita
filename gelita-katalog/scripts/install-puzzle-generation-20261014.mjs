// One-time authorized puzzle update. Re-running after installation is refused.
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const read=p=>JSON.parse(fs.readFileSync(path.join(root,p),'utf8'));
const data=read('data.json');
const original=read('archive/before-puzzle-update-20261014/data.json');
assert.deepEqual(data,original,'Catalog has changed since the saved snapshot; review before installing');
const records=read('provenance/puzzle-generation-20261014/results.json');
const jobs=read('provenance/puzzle-generation-20261014/jobs.json');
assert.equal(records.length,25);assert.equal(new Set(records.map(r=>r.key)).size,25);
assert.deepEqual(new Set(records.map(r=>r.key)),new Set(jobs.map(r=>r.key)));
const tiles=read('reports/puzzle-tile-review-20261014.json');assert.equal(tiles.total,25);assert.equal(tiles.failures,0);
let nextId=Math.max(...data.map(a=>Number(a.id)));
const assets=[];
for(const record of records){
 const job=jobs.find(j=>j.key===record.key);
 let entry=data.find(a=>a.key===record.key);const replaced=Boolean(entry);
 if(!entry){
  entry={id:String(++nextId),key:record.key,cat:'Tantangan',size:'1200 × 1200',ratio:'1:1',alpha:false,format:'WebP',priority:'Kelas 5 terbaru',region:record.code.startsWith('TMG')?'Temanggung':record.code.startsWith('MGL')?'Magelang':'Wonosobo',style_target:'3D / Jaka syal merah',prompt_mode:'ChatGPT Image',prompt_language:'English',legacy_file:null,style_review:{status:'Sesuai gaya',basis:'user_override',date:'2026-10-14',method:'Mengikuti permintaan pengguna untuk membuat aset dari perubahan terbaru',reason:'Mengikuti arahan visual aktif dalam permintaan pengguna; bukan audit visual atau pengesahan produksi.'},previous_style_review:{status:'Tidak berlaku',date:'2026-10-14',method:'Entri baru',reason:'Belum ada visual katalog sebelum generasi ini.'}};
  data.push(entry);
 }
 const file='assets/tantangan/'+record.key+'.webp';
 const check=tiles.assets.find(a=>a.key===record.key);
 Object.assign(entry,{title:job.title,desc:job.desc_id,file,preview:file,generated_asset:file,actual:'1200 × 1200',actual_format:'WEBP',actual_alpha:false,dimension_status:'Sesuai target',prompt:job.prompt,negative:job.prompt.split('\nCONSTRAINTS\n')[1],prompt_revision:{date:'2026-10-14',scope:'Panduan puzzle terbaru; 25 slot, termasuk 21 variasi d–j'},generation_prompt:record.actual_prompt,generated_by:'built-in image_gen; normalisasi WebP lossless',status:'Ilustrasi AI — perlu tinjau',generation_status:'Ilustrasi AI — perlu tinjau',content_review:'needs_verification: isi budaya/agama, bentuk faktual, dan keterangan bank soal perlu diperiksa peneliti. Uji susun oleh dua orang belum dijalankan.',production:'Dimensi, format dan metrik keping diperiksa; belum disahkan untuk pengambilan data atau integrasi game.',note:'Revisi puzzle 14 Oktober 2026. '+check.putusan+'. Tidak ada keping polos/kembar menurut ambang panduan; catatan kemiripan tetap tersedia. Status gaya mengikuti keputusan pengguna, bukan audit baru.',puzzle:{prompt_code:job.code,item_key:job.key.replace('challenge.item.','').replace(/-01$/,'-a01').replace(/-03$/,'-b01').replace(/-07$/,'-c01'),grid:check.grid,description_id:job.desc_id,description_en:job.desc_en,review_status:'needs_verification',tile_result:check.putusan,human_assembly_test:false},puzzle_provenance:{date:'2026-10-14',manifest:'provenance/puzzle-generation-results-20261014.json',source_native:record.native_file,references:record.references,revision:record.revision||1,replaced}});
 // mgl-1-01 = a; wnb-1-07 = c; mapping above preserves all 21 new item keys.
 assert.ok(fs.existsSync(path.join(root,record.candidate_file)));
 fs.copyFileSync(path.join(root,record.candidate_file),path.join(root,file));
 assets.push({...record,id:entry.id,file,replaced,sha256:crypto.createHash('sha256').update(fs.readFileSync(path.join(root,file))).digest('hex')});
}
assert.equal(data.length,611);assert.equal(assets.filter(a=>!a.replaced).length,21);assert.equal(assets.filter(a=>a.replaced).length,4);
assert.deepEqual(data.filter(a=>a.size==='Audio'),read('archive/audio-baseline.json'));
fs.writeFileSync(path.join(root,'data.json'),JSON.stringify(data,null,2)+'\n');
fs.writeFileSync(path.join(root,'provenance/puzzle-generation-results-20261014.json'),JSON.stringify({date:'2026-10-14',base_entries:590,original_catalog_snapshot:'archive/before-puzzle-update-20261014/data.json',original_asset_hashes:'archive/before-puzzle-update-20261014/asset-hashes.json',method:'built-in image_gen; square proportional normalization to 1200px WebP lossless',generation_calls:30,selected_assets:25,added:21,replaced:4,review:'reports/puzzle-tile-review-20261014.json',cultural_validation:false,human_assembly_test:false,game_integration:false,assets},null,2)+'\n');
console.log('Installed 21 new puzzle slots and replaced 4: 611 entries, 514 visuals, 97 unchanged audio objects');
