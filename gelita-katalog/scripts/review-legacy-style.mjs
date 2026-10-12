import fs from 'node:fs';
import path from 'node:path';
import {createRequire} from 'node:module';
import {fileURLToPath} from 'node:url';
import crypto from 'node:crypto';
const require=createRequire(import.meta.url),sharp=require(process.env.SHARP_PACKAGE||'sharp');
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const data=JSON.parse(fs.readFileSync(path.join(root,'data.json'),'utf8'));
// Historical mismatch records with no subsequent reference/icon/character
// regeneration provenance. This report never changes the user-approved status.
const selected=data.filter(a=>a.size!=='Audio'&&a.cat!=='Ikon'&&!a.provenance&&!a.character_provenance&&['Belum sesuai','Perlu tinjau'].includes(a.previous_style_review?.status));
const files=selected.map(a=>({key:a.key,file:a.file,filename:path.basename(a.file),historical_status:a.previous_style_review.status,reason:a.previous_style_review.reason,sha256:crypto.createHash('sha256').update(fs.readFileSync(path.join(root,a.file))).digest('hex')}));
const report={date:'2026-10-12',basis:'Historical visual-review records, excluding assets regenerated since that review; current catalog user_override is unchanged',old_style:files.filter(a=>a.historical_status==='Belum sesuai'),uncertain:files.filter(a=>a.historical_status==='Perlu tinjau')};
fs.writeFileSync(path.join(root,'reports/remaining-legacy-style.json'),JSON.stringify(report,null,2)+'\n');
fs.writeFileSync(path.join(root,'reports/remaining-legacy-style.txt'),report.old_style.map(a=>a.filename).join('\n')+'\n');
for(let offset=0;offset<selected.length;offset+=24){
 const cells=[];
 for(const [i,a] of selected.slice(offset,offset+24).entries()){
  const left=i%4*400,top=Math.floor(i/4)*280;
  const thumb=await sharp(path.join(root,a.file)).resize(390,245,{fit:'contain',background:'#eee9df'}).webp({lossless:true}).toBuffer();
  cells.push({input:thumb,left,top});
  const label=Buffer.from(`<svg width="400" height="35"><rect width="400" height="35" fill="#eee9df"/><text x="5" y="23" font-family="Arial" font-size="14" fill="#222">${a.key}</text></svg>`);
  cells.push({input:label,left,top:top+245});
 }
 await sharp({create:{width:1600,height:1680,channels:3,background:'#eee9df'}}).composite(cells).webp({lossless:true}).toFile(path.join(root,`review/legacy-style-current-${offset/24+1}.webp`));
}
console.log(JSON.stringify({old:report.old_style.length,uncertain:report.uncertain.length,sheets:Math.ceil(selected.length/24)}));
