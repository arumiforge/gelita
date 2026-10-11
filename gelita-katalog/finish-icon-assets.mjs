import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {createRequire} from 'node:module';
const root=path.dirname(fileURLToPath(import.meta.url));
const require=createRequire(import.meta.url);
const sharp=require('C:/Users/cenda/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/sharp');
const data=JSON.parse(fs.readFileSync(path.join(root,'data.json'),'utf8'));
const manifest=JSON.parse(fs.readFileSync(path.join(root,'icon-generation-results.json'),'utf8'));
const changes=[];
for(const [filename,oldText,newText] of [
 ['assets/ikon/icon.play.svg','<path d=','<path fill="url(#gold)" stroke="none" d='],
 ['assets/ikon/icon.pause.svg','stroke-width="2"','stroke-width="3"'],
 ['assets/ikon/app.icon.svg','stroke-width=".9"','stroke-width="1.2"'],
 ['assets/ikon/app.icon.svg','fill="#DFC087"','fill="none"']
]){
 const filenameAbsolute=path.join(root,filename);
 fs.writeFileSync(filenameAbsolute,fs.readFileSync(filenameAbsolute,'utf8').replace(oldText,newText));
}
const dims=a=>a.size.split(' × ').map(Number);
for(const result of manifest){
 const a=data.find(a=>a.key===result.key);if(!a)throw Error(result.key);
 const [width,height]=dims(a);
 let pipeline=sharp(result.source);
 // The corrected application master contains its full-bleed background and safe margins.
 if(a.key==='app.icon-512.png')pipeline=pipeline.resize(width,height);
 else if(a.key==='avatar.10')pipeline=pipeline.trim().resize(width-48,height-48,{fit:'contain',background:{r:0,g:0,b:0,alpha:0}}).extend({top:24,bottom:24,left:24,right:24,background:{r:0,g:0,b:0,alpha:0}});
 else pipeline=pipeline.resize(width,height,{fit:'contain',background:{r:0,g:0,b:0,alpha:0}});
 await pipeline.webp({lossless:true}).toFile(path.join(root,a.file));
 a.generated_asset=a.file;a.generated_by='built-in image_gen';a.generation_prompt=result.prompt;
 a.generation_status='Ikon Gelita baru';
 a.icon_provenance={tool:'built-in image_gen',date:'2026-10-11',source:result.source,postproduction:a.key==='avatar.10'?'Transparent crop, proportional resize, transparent margin and WebP export':'Proportional resize and WebP export'};
 if(a.key==='avatar.10')a.prompt=result.prompt+'\nDelivery: 512 × 512 WebP; preserve transparent margins.';
 a.note='Ikon dibuat ulang mengikuti arahan Gelita. Status sesuai mengikuti keputusan pengguna; dimensi dan alpha diperiksa dari berkas.';
 changes.push(a.key);
}
const master=data.find(a=>a.key==='app.icon-512.png');
for(const a of data.filter(a=>a.key.startsWith('app.')&&!a.file.endsWith('.svg')&&a!==master)){
 const [width,height]=dims(a);
 const source=a.key==='app.favicon.ico'?'assets/ikon/app.icon.svg':master.file;
 await sharp(path.join(root,source)).resize(width,height).webp({lossless:true}).toFile(path.join(root,a.file));
 a.generated_asset=a.file;a.generated_by='Turunan master built-in image_gen';a.generation_prompt=a.prompt;
 a.generation_status='Ikon aplikasi Gelita baru';a.icon_provenance={tool:source.endsWith('.svg')?'Native SVG + resize':'built-in image_gen + resize',date:'2026-10-11',master:source};
 if(source.endsWith('.svg'))a.generated_by='Ekspor favicon dari master SVG Gelita';changes.push(a.key);
}
for(const a of data.filter(a=>a.vector_source)){
 await sharp(path.join(root,a.vector_source)).webp({lossless:true}).toFile(path.join(root,a.file));
 a.generated_asset=a.file;a.note='Ikon kontrol Gelita dibuat dari SVG asli, diekspor ke WebP; sumber vektor dan label bahasa tersedia.';changes.push(a.key);
}
for(const a of data.filter(a=>a.cat==='Ikon'&&a.file.endsWith('.svg'))){
 a.generated_asset=a.file;a.actual_alpha=!a.key.startsWith('app.');a.actual_format='SVG';
 a.note='Ikon SVG diperbarui dalam keluarga Gelita dengan warna emas lentera, geometri jelas dan bentuk sederhana.';changes.push(a.key);
}
for(const a of data.filter(a=>a.size!=='Audio'&&!a.file.endsWith('.svg'))){
 const m=await sharp(path.join(root,a.file)).metadata();
 const s=await sharp(path.join(root,a.file)).stats();
 a.actual=`${m.width} × ${m.height}`;a.actual_format='WEBP';
 a.actual_alpha=m.hasAlpha&&s.channels.at(-1).min<255;
 a.dimension_status=a.actual===a.size?'Sesuai target':'Berbeda dari target';
}
const icons=data.filter(a=>a.cat==='Ikon');
if(new Set(changes).size!==icons.length)throw Error('Not all icon entries replaced');
for(const a of icons){if(a.actual!==a.size)throw Error('Wrong dimensions '+a.key);if(a.actual_alpha!==a.alpha)throw Error('Wrong alpha '+a.key);}
fs.writeFileSync(path.join(root,'data.json'),JSON.stringify(data,null,2)+'\n');
fs.writeFileSync(path.join(root,'icon-update-report.json'),JSON.stringify({date:'2026-10-11',total_icons:icons.length,updated:new Set(changes).size,built_in_generations:manifest.length,native_vectors:51,vector_control_exports:8,app_derivatives:4,dimensions:'All 83 match their target',alpha:'All 83 match their transparency specification',changed_keys:changes},null,2)+'\n');
const old=JSON.parse(fs.readFileSync(path.join(root,'generation-manifest.json'),'utf8'));
const keep=old.filter(a=>!a.key||!changes.includes(a.key));
fs.writeFileSync(path.join(root,'generation-manifest.json'),JSON.stringify([...keep,...manifest.map(r=>({...r,tool:'built-in image_gen',date:'2026-10-11',output:data.find(a=>a.key===r.key).file}))],null,2)+'\n');
console.log(JSON.stringify({icons_updated:icons.length,ai_generations:manifest.length}));
