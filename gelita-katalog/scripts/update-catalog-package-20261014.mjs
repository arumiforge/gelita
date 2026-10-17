// Dated production step, invoked once for the requested slot replacements.
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const target='provenance/language-slot-update-20261014.json';
if(fs.existsSync(path.join(root,target)))throw Error('Revision already recorded; do not rerun dated production.');
const read=p=>JSON.parse(fs.readFileSync(path.join(root,p),'utf8'));
const sha=p=>crypto.createHash('sha256').update(fs.readFileSync(path.join(root,p))).digest('hex');
const data=read('data.json');
const backup='archive/before-catalog-update-20261014';
const fields=['title','desc','prompt','negative','prompt_revision','note','status','generated_by','generation_status','generation_prompt','vector_source','language_provenance','content_review','production'];
const manifest={date:'2026-10-14',authorization:'User requested updating Indonesia and English icons with the new icons',method:'Reuse existing approved flag assets; preserve slot codes, filenames, dimensions and style history',original_catalog_snapshot:backup+'/data.json',allowed_changed_fields:fields,assets:[]};
for(const [key,language] of [['reference.19','en'],['reference.20','id']]){
  const a=data.find(x=>x.key===key),flag=data.find(x=>x.key==='icon.language-'+language);
  const previous=backup+'/'+path.basename(a.file);
  fs.copyFileSync(path.join(root,a.file),path.join(root,previous));
  manifest.assets.push({key,file:a.file,source:flag.file,backup:previous,previous_sha256:sha(a.file),sha256:sha(flag.file)});
  fs.copyFileSync(path.join(root,flag.file),path.join(root,a.file));
  Object.assign(a,{title:flag.title,desc:'Ikon bendera bahasa terbaru untuk slot resmi '+key+'; dipakai bersama seluruh wilayah.',prompt:flag.prompt,negative:flag.negative,prompt_revision:{date:'2026-10-14',scope:'Replace active ID/EN label brief with existing Indonesia/UK flag icon'},note:'Menggunakan ulang ikon bendera 13 Oktober; gambar dan sumber SVG huruf lama tetap tersimpan sebagai riwayat, bukan sumber raster aktif.',status:'Aset tersedia — perlu integrasi',generated_by:'Reuse built-in image_gen flag asset from 2026-10-13',generation_status:'Slot bahasa diperbarui dengan ikon bendera',generation_prompt:flag.generation_prompt,vector_source:null,language_provenance:{manifest:target,source:flag.file,backup:previous,date:'2026-10-14'},content_review:'Salinan byte-identik dengan ikon bendera terbaru; tidak merupakan audit baru seluruh katalog.',production:'Aktif pada katalog; pemetaan kode lama tetap tersedia. Integrasi aplikasi asli belum dilakukan.'});
}
const packet='mockup/gambar-mockup/tantangan-selesai-temanggung/aset-katalog/';
for(const [name,src] of [['bahasa-english-en.webp','icon.language-en'],['bahasa-indonesia-id.webp','icon.language-id']]){
  fs.copyFileSync(path.join(root,packet+name),path.join(root,backup,name));
  fs.copyFileSync(path.join(root,data.find(a=>a.key===src).file),path.join(root,packet+name));
}
fs.writeFileSync(path.join(root,'data.json'),JSON.stringify(data,null,2)+'\n');
fs.writeFileSync(path.join(root,target),JSON.stringify(manifest,null,2)+'\n');
console.log('Updated two existing language slots; counts, codes, style history preserved.');
