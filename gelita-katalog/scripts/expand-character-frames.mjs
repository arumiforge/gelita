import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import assert from 'node:assert/strict';
import {createRequire} from 'node:module';
import {fileURLToPath} from 'node:url';

// Ten AI key poses, with 24 frames of explicitly recorded secondary motion each.
// This does not synthesize new facial expressions or claim joint-rig animation.
const require=createRequire(import.meta.url);
const sharp=require(process.env.SHARP_PACKAGE||'sharp');
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const read=p=>JSON.parse(fs.readFileSync(path.join(root,p),'utf8'));
const write=(p,v)=>fs.writeFileSync(path.join(root,p),JSON.stringify(v,null,2)+'\n');
const hash=b=>crypto.createHash('sha256').update(b).digest('hex');
const jobs=read('provenance/character-generation-results.json');
assert.equal(jobs.length,10,'All ten reviewed key poses are required before replacement');
const data=read('data.json');
const old=data.filter(a=>/^char\.(jaka|kedu)\./.test(a.key));
assert.equal(old.length,30,'Replacement is only for the original 30 character frames');
assert.deepEqual(data.filter(a=>a.size==='Audio'),read('archive/audio-baseline.json'));
const backup='archive/before-character-frames-20261012';
assert.ok(!fs.existsSync(path.join(root,backup)),'Backup already exists; do not overwrite history');
fs.mkdirSync(path.join(root,backup,'assets/karakter'),{recursive:true});
fs.copyFileSync(path.join(root,'data.json'),path.join(root,backup,'data.json'));
const before={};
for(const a of old){fs.copyFileSync(path.join(root,a.file),path.join(root,backup,a.file));before[a.key]=hash(fs.readFileSync(path.join(root,a.file)));}
fs.mkdirSync(path.join(root,'provenance/character-keyposes'),{recursive:true});
const width=700,height=900,anchor=876;
const poseReports=[];
const rendered=[];
let nextId=Math.max(...data.map(a=>Number(a.id)||0))+1;

function motion(pose,f){
 const loop=pose==='idle',t=loop?f/24:f/23;
 const phase=2*Math.PI*t;
 const wave=loop?Math.sin(phase):Math.sin(Math.PI*t);
 const settle=loop?0:t;
 const amplitudes={idle:2.8,happy:5.5,sad:2.5,afraid:4,determined:3,bow:5,smile:3.5,worried:2.8,weak:2};
 return {dx:(amplitudes[pose]||3)*wave+(loop?0:1.1*settle),
  dy:(pose==='happy'?-4:pose==='bow'?5:2.5)*wave+(loop?0:.7*settle),
  breathe:1.5*Math.sin(phase+.2),t,loop};
}

function deform(input,m){
 const out=Buffer.alloc(width*height*4);
 // Inverse continuous mesh: lower legs and foot line are held fixed, upper
 // body gently shifts/compresses. Premultiplied-alpha bilinear sampling avoids
 // dark halos at transparent edges. No whole-image translation or duplicates.
 for(let y=0;y<height;y++){
  const upper=Math.max(0,Math.min(1,(anchor-y)/(anchor-150)));
  const footLock=Math.max(0,Math.min(1,(anchor-24-y)/130));
  const w=upper*footLock;
  for(let x=0;x<width;x++){
   const chest=Math.exp(-(((y-450)/180)**2));
   const dx=m.dx*w+(x-350)*.003*m.breathe*chest*footLock;
   const dy=m.dy*w+m.breathe*chest*footLock;
   const sx=x-dx,sy=y-dy,ix=Math.floor(sx),iy=Math.floor(sy);
   if(ix<0||iy<0||ix+1>=width||iy+1>=height)continue;
   const fx=sx-ix,fy=sy-iy;
   const samples=[[(iy*width+ix)*4,(1-fx)*(1-fy)],[(iy*width+ix+1)*4,fx*(1-fy)],
    [((iy+1)*width+ix)*4,(1-fx)*fy],[((iy+1)*width+ix+1)*4,fx*fy]];
   let a=0,r=0,g=0,b=0;
   for(const [p,q] of samples){const aq=input[p+3]*q;a+=aq;r+=input[p]*aq;g+=input[p+1]*aq;b+=input[p+2]*aq;}
   const p=(y*width+x)*4;
   if(a>.5){out[p]=Math.round(r/a);out[p+1]=Math.round(g/a);out[p+2]=Math.round(b/a);out[p+3]=Math.round(a);}
  }
 }
 return out;
}

for(const job of jobs){
 const base=data.find(a=>a.key===job.key+'.1');assert.ok(base,job.key);
 const [,,pose]=job.key.split('.');
 const trimmed=await sharp(job.source).trim({threshold:10}).toBuffer();
 const fitted=await sharp(trimmed).resize(620,812,{fit:'inside',withoutEnlargement:false}).ensureAlpha().raw().toBuffer({resolveWithObject:true});
 const normalized=await sharp({create:{width,height,channels:4,background:{r:0,g:0,b:0,alpha:0}}}).composite([{input:fitted.data,raw:fitted.info,left:Math.round((width-fitted.info.width)/2),top:anchor-fitted.info.height}]).raw().toBuffer();
 const sourceFile='provenance/character-keyposes/'+job.key+'.webp';
 await sharp(normalized,{raw:{width,height,channels:4}}).webp({lossless:true}).toFile(path.join(root,sourceFile));
 const frames=[];
 for(let f=0;f<24;f++){
  const m=motion(pose,f),raw=deform(normalized,m),key=job.key+'.'+(f+1);
  const file='assets/karakter/'+key+'.webp';
  const encoded=await sharp(raw,{raw:{width,height,channels:4}}).webp({lossless:true}).toBuffer();
  const meta=await sharp(encoded).metadata();
  assert.equal(meta.width,width);assert.equal(meta.height,height);assert.ok(meta.hasAlpha);
  assert.equal(raw[3],0);assert.equal(raw[((height-1)*width+width-1)*4+3],0);
  let opaque=0;for(let p=3;p<raw.length;p+=4)if(raw[p]>10)opaque++;
  assert.ok(opaque>40000&&opaque<width*height*.8,'Invalid subject/alpha '+key);
  frames.push({key,file,sha256:hash(encoded),motion:m,opaque_pixels:opaque});
  rendered.push({file,encoded});
  const existing=data.find(a=>a.key===key);
  const a=existing||structuredClone(base);
  if(!existing){a.id=String(nextId++);a.previous_style_review={status:'Tidak ditinjau',reason:'Frame tambahan dibuat 12 Oktober 2026; tidak termasuk audit visual 11 Oktober.',date:'2026-10-12',method:'Catatan asal frame baru'};data.push(a);}
  Object.assign(a,{key,title:base.title.replace(/frame \d+$/,'frame '+(f+1)),file,preview:file,generated_asset:file,
   desc:base.desc.replace(/^(\w+) · \d+/,'$1 · 24')+' · gerak sekunder dari satu key pose 3D',actual:'700 × 900',actual_alpha:true,actual_format:'WEBP',dimension_status:'Sesuai target',
   prompt:job.prompt+'\n\nFRAME DELIVERY\nFrame '+(f+1)+' of 24. Derived from a single AI key pose with deterministic secondary motion, not independent AI generation. Feet fixed at y=876; 700 × 900 WebP with genuine alpha. Motion parameters: '+JSON.stringify(m),
   generation_prompt:job.prompt,generated_by:'image_gen + deterministic secondary-motion mesh',
   character_provenance:{date:'2026-10-12',tool:'image_gen',key_pose:sourceFile,frame:f+1,frame_count:24,method:'AI key pose + deterministic premultiplied-alpha mesh',loop:m.loop},
   prompt_revision:{date:'2026-10-12',scope:'24 frames per pose; AI key pose and derived-motion provenance'},
   note:'Key pose 3D baru; 24 frame gerak sekunder dari satu gambar dasar, bukan 24 generasi AI terpisah. Status user_override dipertahankan; riwayat audit lama tidak dihapus.',
   production:'24 frame gerak sekunder tersedia. Belum memvalidasi rig sendi, ekspresi antarframe, budaya, lisensi, atau integrasi game.'});
  if(!existing)a.style_review={status:'Sesuai gaya',basis:'user_override',date:'2026-10-12',method:'Arahan pengguna untuk frame tambahan',reason:'Frame tambahan mengikuti key pose 3D sesuai permintaan pengguna; bukan audit visual baru.'};
 }
 assert.equal(new Set(frames.map(a=>a.sha256)).size,24,'Repeated frame '+job.key);
 poseReports.push({key:job.key,key_pose:sourceFile,source:job.source,frames,unique_frames:24});
 console.log(job.key+': 24 distinct 700x900 alpha frames prepared');
}
assert.equal(data.length,580);assert.equal(new Set(data.map(a=>a.key)).size,580);
assert.deepEqual(data.filter(a=>a.size==='Audio'),read('archive/audio-baseline.json'));
const stems=[...new Set(old.map(a=>a.key.replace(/\.\d+$/,'')))];
const sortedCharacters=data.filter(a=>/^char\.(jaka|kedu)\./.test(a.key)).sort((a,b)=>stems.indexOf(a.key.replace(/\.\d+$/,''))-stems.indexOf(b.key.replace(/\.\d+$/,''))||a.character_provenance.frame-b.character_provenance.frame);
let inserted=false;
const ordered=[];
for(const a of data){if(/^char\.(jaka|kedu)\./.test(a.key)){if(!inserted){ordered.push(...sortedCharacters);inserted=true;}}else ordered.push(a);}
data.splice(0,data.length,...ordered);
// Do not replace any active image until every pose passes the checks above.
for(const r of rendered)fs.writeFileSync(path.join(root,r.file),r.encoded);
write('data.json',data);
for(const a of old)assert.notEqual(hash(fs.readFileSync(path.join(root,a.file))),before[a.key]);
write('reports/character-frame-report.json',{date:'2026-10-12',method:'10 AI key poses, 240 deterministic secondary-motion frames',frames:240,replaced:30,added:210,backup,poses:poseReports,audio_unchanged:true,limits:'Not independent AI frames or joint-rig animation. Visual/contact-sheet review is not culture, licensing, or game integration validation.'});
console.log('Replaced 30 old frames; added 210 new frames; audio unchanged.');
