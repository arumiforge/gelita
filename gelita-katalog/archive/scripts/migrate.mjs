import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const root=path.dirname(fileURLToPath(import.meta.url));
const read=p=>fs.readFileSync(path.join(root,p),'utf8').replace(/^\uFEFF/,'');
const write=(p,s)=>{fs.mkdirSync(path.dirname(path.join(root,p)),{recursive:true});fs.writeFileSync(path.join(root,p),s)};
if(fs.existsSync(path.join(root,'migration-map.json')))throw Error('Migration already applied; use build.mjs instead');
const data=JSON.parse(read('data.json'));
for(const f of ['data.json','index.html','PROMPTS-ENGLISH.md','README.txt','STATUS-GAMBAR.json'])write('archive/before-2026-10-11/'+f,read(f));
write('archive/audio-baseline.json',JSON.stringify(data.filter(a=>a.size==='Audio'),null,2));
const generated=JSON.parse(read('generation-manifest.json'));
const mapping=[];
const groups={Karakter:'karakter',Background:'latar',Peta:'peta',Logo:'logo',Antarmuka:'antarmuka',Ikon:'ikon',Lencana:'lencana',Pustaka:'pustaka',Tantangan:'tantangan'};
const matched=new Set([1,4,8,13,14,15,16,17,18,19,20,21,22,23,24,25,26,28,33,34,35,36,37,38,39,40,45,46,47,48,49,50,51,52,53,54,59,60,61,62,64,66,67,68,69,70,95,96,110,111,112,143,144,145,146,147,148,149,150]);
const neutral=new Set([97,98,101,102,103,104,105,279,281]);
const review=new Set([30,31,90]);
const style='Premium stylized 3D animated adventure game rendering, dimensional materials, soft cinematic lighting, warm lantern gold and deep navy, culturally respectful Kedu setting. No painted gouache or paper texture.';
const jaka='Jaka must match reference.01: approximately 11-year-old Javanese boy, tousled dark-brown hair with cowlick, brown eyes, cream short-sleeved shirt, RED SCARF, brown knee-length shorts, orange-brown batik waist cloth, sandals and brown leather backpack. No lurik shirt, iket/headwrap or blue vest on Jaka.';
for(const a of data){
 if(a.size==='Audio')continue;
 const id=Number(a.id),g=generated.find(x=>x.id===a.id);
 const dir=a.key.startsWith('reference.')?'referensi':groups[a.cat];
 const base=a.key.replace(/\.(png|svg|ico|webp)$/i,'');
 a.legacy_file=a.file||null;
 const old=a.generated_asset||a.preview;
 const rename=src=>{const dest=`assets/${dir}/${base}${path.extname(src)}`; if(fs.existsSync(path.join(root,src))){if(fs.existsSync(path.join(root,dest)))throw Error('Collision '+dest);fs.mkdirSync(path.dirname(path.join(root,dest)),{recursive:true});fs.renameSync(path.join(root,src),path.join(root,dest));mapping.push({old:src,new:dest,key:a.key});}return dest};
 if(g){const dest=`assets/referensi/${base}.png`;fs.mkdirSync(path.dirname(path.join(root,dest)),{recursive:true});fs.copyFileSync(g.path,path.join(root,dest));a.generated_asset=dest;a.preview=dest;a.prompt=g.prompt;a.generation_status='Referensi 3D baru';a.provenance={tool:'built-in image_gen',date:'2026-10-11',kind:'Rekonstruksi baru; bukan pemulihan berkas asli'};}
 else {const fields=new Map();for(const field of ['generated_asset','preview'])if(a[field]){const src=a[field];if(!fields.has(src))fields.set(src,rename(src));a[field]=fields.get(src)}
 // Preserve alternate formats, including SVG buttons, under the same semantic code.
 if(old){const stem=path.basename(old,path.extname(old));const oldDir=path.dirname(old);for(const ext of ['.png','.webp','.svg']){const src=`${oldDir}/${stem}${ext}`;if(fs.existsSync(path.join(root,src)))rename(src);}}
 }
 a.file=a.generated_asset||a.preview;
 a.style_target='3D / Jaka syal merah';
 let status='Belum sesuai',reason='Tampilan ilustrasi/lukisan lama belum konsisten dengan acuan render 3D.';
 if(matched.has(id)||g){status='Sesuai gaya';reason='Bentuk bervolume, material dan pencahayaan selaras dengan arah 3D pada pemeriksaan visual.'}
 if(a.file.endsWith('.svg')||neutral.has(id)){status='Tidak berlaku';reason='Ikon/UI datar atau media faktual; tidak dinilai dengan kostum Jaka maupun gaya karakter 3D.'}
 if(review.has(id)){status='Perlu tinjau';reason='Gaya/identitas belum cukup pasti dari pratinjau; jangan dihitung sebagai sesuai.'}
 if(id>=113&&id<=130)reason='Jaka memakai lurik dan iket tanpa syal merah; kostum tidak sesuai reference.01.';
 if(id>=131&&id<=142)reason='Frame Mbah Kedu memakai pendekatan ilustrasi lama dan perlu diselaraskan dengan avatar 3D reference.02.';
 if([278,280,282].includes(id))reason='Ilustrasi tips Jaka menggunakan kostum tanpa syal merah dan gaya lama.';
 a.style_review={status,reason,date:'2026-10-11',method:'Tinjauan visual lembar kontak seluruh raster; gambar baru diperiksa individual. Bukan sertifikasi budaya atau kelayakan produksi.'};
 a.content_review='Perlu pemeriksaan isi dan budaya sebelum produksi.';
 if(id===19)a.content_review='Simbol bahasa English tampak kosong; gunakan label EN terpisah.';
 if(id===95)a.content_review='Logo utama lama berupa ornamen kosong tanpa tulisan GELITA.';
 if(id===96)a.content_review='Emblem panel lama mendatar, tidak sesuai bentuk persegi target.';
 if([154,155,156,157,158,159,160,161,162,163].includes(id))a.content_review='Ilustrasi medali lama; periksa pula latar kotak-kotak pada beberapa pratinjau.';
 a.production='Gaya dinilai terpisah; ukuran, alpha, isi budaya dan integrasi aplikasi belum disahkan.';
 if(id<=12)a.priority='Referensi utama';
 if(id===3){a.title='Jaka — avatar syal merah';a.desc='Avatar 3D Jaka dengan syal merah, kemeja krem dan ransel kulit.'}
 if(id===5)a.desc='Prolog 3D dengan Jaka bersyal merah dan lanskap Kedu; tanpa panel dialog tertanam.';
 a.note='Acuan aktif 11 Oktober 2026: 3D dan Jaka syal merah. '+reason+' Ukuran target adalah spesifikasi, bukan bukti siap produksi.';
 if(!g){
  a.prompt=a.prompt.replace(/painterly 2D digital gouache with soft brush texture and subtle paper grain/gi,style).replace(/clean painterly edges/gi,'clean dimensional edges').replace(/\b3D render, CGI, /g,'').replace(/\b3D render, /g,'');
  a.negative=(a.negative||'').replace(/\b3D render, CGI, /g,'').replace(/\b3D render, /g,'');
  if(status!=='Tidak berlaku')a.prompt='CURRENT APPROVED ART DIRECTION (2026-10-11)\n'+style+'\n'+(a.cat==='Karakter'||/Jaka/i.test(a.desc+' '+a.title)?jaka+'\n':'')+'Preserve the subject and functional requirements below. For Mbah Kedu use reference.02; the red scarf applies only to Jaka.\n\n'+a.prompt;
 }
}
write('data.json',JSON.stringify(data,null,2)+'\n');
write('migration-map.json',JSON.stringify(mapping,null,2)+'\n');
// Keep source documents as clearly marked historical evidence; current instructions live in README.
for(const f of ['sources/README.md','sources/rencana-katalog-aset.md']){
 const original=read(f);write('archive/source-documents/'+path.basename(f),original);
 write(f,'> ARSIP SUMBER — bukan status proyek saat ini.\n> Keputusan aktif 11 Oktober 2026: katalog statis sudah tersedia, gaya 3D/Jaka syal merah. Rujukan aplikasi dan workbook di bawah berasal dari repositori asal dan tidak disertakan dalam paket ini.\n> Baca [README proyek](../README.md), [konteks](../CONTEXT.md), dan [progres](../PROGRESS.md).\n\n'+original);
}
console.log(JSON.stringify({renamed:mapping.length,generated:generated.length,statuses:data.filter(a=>a.size!=='Audio').reduce((o,a)=>(o[a.style_review.status]=(o[a.style_review.status]||0)+1,o),{})},null,2));
