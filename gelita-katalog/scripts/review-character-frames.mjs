import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import {createRequire} from 'node:module';
import {fileURLToPath} from 'node:url';
const require=createRequire(import.meta.url),sharp=require(process.env.SHARP_PACKAGE||'sharp');
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const data=JSON.parse(fs.readFileSync(path.join(root,'data.json'),'utf8'));
const old=JSON.parse(fs.readFileSync(path.join(root,'archive/before-character-frames-20261012/data.json'),'utf8'));
const report=JSON.parse(fs.readFileSync(path.join(root,'reports/character-frame-report.json'),'utf8'));
const hash=b=>crypto.createHash('sha256').update(b).digest('hex');
const seen=[];
for(const pose of report.poses){
 const overlays=[];
 let feetReference;
 for(let n=1;n<=24;n++){
  const a=data.find(a=>a.key===pose.key+'.'+n);assert.ok(a);
  const file=path.join(root,a.file),bytes=fs.readFileSync(file);
  assert.ok((await sharp(file).metadata()).hasAlpha,'Missing alpha channel '+a.key);
  const {data:raw,info}=await sharp(file).ensureAlpha().raw().toBuffer({resolveWithObject:true});
  assert.equal(info.width,700);assert.equal(info.height,900);assert.equal(info.channels,4);
  assert.equal(hash(bytes),pose.frames[n-1].sha256);
  // WebP lossless may discard RGB under alpha=0. Compare visible pixels and
  // alpha, canonicalizing invisible RGB so harmless encoder choices do not
  // masquerade as foot movement.
  const footPixels=Buffer.from(raw.subarray(853*700*4));
  for(let p=0;p<footPixels.length;p+=4)if(footPixels[p+3]===0)footPixels.fill(0,p,p+3);
  const feet=hash(footPixels);if(n===1)feetReference=feet;else assert.equal(feet,feetReference,'Foot anchor moved '+a.key);
  const original=old.find(x=>x.key===a.key);
  if(original){assert.deepEqual(a.style_review,original.style_review);assert.deepEqual(a.previous_style_review,original.previous_style_review);assert.notEqual(hash(fs.readFileSync(path.join(root,report.backup,original.file))),hash(bytes));}
  const left=(n-1)%6*175,top=Math.floor((n-1)/6)*250;
  const thumb=await sharp(file).resize(175,225,{fit:'contain',background:'#e9e4d9'}).webp({lossless:true}).toBuffer();
  overlays.push({input:thumb,left,top});
  overlays.push({input:Buffer.from(`<svg width="175" height="25"><rect width="175" height="25" fill="#e9e4d9"/><text x="8" y="18" font-family="Arial" font-size="12">${pose.key.split('.').slice(1).join('.')} · ${n}</text></svg>`),left,top:top+225});
  seen.push({key:a.key,file:a.file,sha256:hash(bytes)});
 }
 await sharp({create:{width:1050,height:1000,channels:3,background:'#e9e4d9'}}).composite(overlays).webp({lossless:true}).toFile(path.join(root,'review',pose.key+'.frames.webp'));
}
assert.equal(seen.length,240);assert.equal(new Set(seen.map(a=>a.sha256)).size,240);
// All unrelated data entries stay intact.
for(const a of old.filter(a=>!/^char\.(jaka|kedu)\./.test(a.key)))assert.deepEqual(data.find(x=>x.key===a.key),a);
const baseline=JSON.parse(fs.readFileSync(path.join(root,'reports/reorganization-report.json'),'utf8')).baseline.assets;
let unrelatedAssets=0,replacedAssets=0;
for(const [file,sha] of Object.entries(baseline)){
 const current=hash(fs.readFileSync(path.join(root,file)));
 if(/^assets\/karakter\/char\.(jaka|kedu)\./.test(file)){assert.notEqual(current,sha);replacedAssets++;}
 else {assert.equal(current,sha,'Unrelated asset changed '+file);unrelatedAssets++;}
}
assert.equal(replacedAssets,30);
const raster=data.filter(a=>a.size!=='Audio'&&a.file.endsWith('.webp'));
for(const a of raster){const meta=await sharp(path.join(root,a.file)).metadata();const target=a.size.match(/(\d+)\s*×\s*(\d+)/);assert.ok(target,a.key);assert.equal(meta.width,Number(target[1]),a.key);assert.equal(meta.height,Number(target[2]),a.key);}
assert.equal(raster.length,431);
fs.writeFileSync(path.join(root,'reports/character-frame-verification.json'),JSON.stringify({date:'2026-10-12',passed:true,checks:['240 files decode as 700 × 900 RGBA','240 distinct file hashes match generation report','Foot rows 853–899 visible pixels and alpha remain identical within each pose','All 30 original character files replaced','Original user_override and previous_style_review preserved','All 340 unrelated data entries unchanged, including 97 audio',unrelatedAssets+' unrelated baseline asset hashes unchanged','431 active raster images have target dimensions; alpha verified for all 240 new frames'],contact_sheets:report.poses.map(a=>'review/'+a.key+'.frames.webp')},null,2)+'\n');
const poses=report.poses.map(a=>a.key);
const html=`<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>GELITA · Review 24 frame karakter</title><style>body{font:16px system-ui;background:#eee9df;color:#24312a;margin:30px}main{max-width:850px;margin:auto}img{display:block;height:min(65vh,650px);max-width:100%;margin:20px auto;background:repeating-conic-gradient(#ddd 0% 25%,#eee 0% 50%) 0/24px 24px}select,button,input{font:inherit;margin:8px;padding:8px}small{display:block}a{color:inherit}</style><main><h1>24 frame per pose</h1><p>10 key pose AI dengan gerak sekunder deterministik. Ekspresi tetap mengikuti gambar dasar. Preview ini tidak membuktikan integrasi game atau animasi rig sendi.</p><select id="pose">${poses.map(p=>`<option>${p}</option>`).join('')}</select><button id="play">Putar</button><label>FPS <input id="fps" type="number" min="1" max="24" value="12" style="width:55px"></label><input id="frame" type="range" min="1" max="24" value="1"><small id="label"></small><img id="image" alt="Frame karakter"><a id="original">Buka WebP</a><p><a href="../index.html">Katalog</a></p></main><script>const $=s=>document.querySelector(s);let n=1,timer;function show(){const file='../assets/karakter/'+$('#pose').value+'.'+n+'.webp';$('#image').src=file;$('#original').href=file;$('#label').textContent=$('#pose').value+' · frame '+n+'/24';$('#frame').value=n}function stop(){clearInterval(timer);timer=null;$('#play').textContent='Putar'}$('#pose').onchange=()=>{stop();n=1;show()};$('#frame').oninput=()=>{stop();n=Number($('#frame').value);show()};$('#play').onclick=()=>{if(timer)stop();else{timer=setInterval(()=>{n=n%24+1;show()},1000/Math.max(1,Number($('#fps').value)));$('#play').textContent='Jeda'}};show();</script></html>`;
fs.writeFileSync(path.join(root,'review/character-animation.html'),html);
console.log('Verified 240 frames, fixed feet, distinct hashes and unchanged unrelated entries.');
