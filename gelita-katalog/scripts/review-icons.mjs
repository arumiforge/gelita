import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {createRequire} from 'node:module';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const require=createRequire(import.meta.url);
const sharp=require(process.env.SHARP_PACKAGE || 'sharp');
const data=JSON.parse(fs.readFileSync(path.join(root,'data.json'),'utf8'));
const esc=s=>s.replaceAll('&','&amp;').replaceAll('<','&lt;');
async function sheet(assets,file,columns,tile){
 const width=columns*tile,height=Math.ceil(assets.length/columns)*tile;
 const composites=[];
 for(let i=0;i<assets.length;i++){
  const a=assets[i],x=i%columns*tile,y=Math.floor(i/columns)*tile;
  const box=tile-40;
  const thumbnail=await sharp(path.join(root,a.file)).resize(box,box,{fit:'contain',background:{r:0,g:0,b:0,alpha:0}}).toBuffer();
  composites.push({input:thumbnail,left:x+20,top:y+7});
  const label=Buffer.from(`<svg width="${tile}" height="28"><text x="${tile/2}" y="20" fill="#F0DFB8" font-family="sans-serif" font-size="${tile>160?12:10}" text-anchor="middle">${esc(a.key.replace(/\.(png|svg|ico)$/,''))}</text></svg>`);
  composites.push({input:label,left:x,top:y+tile-32});
 }
 await sharp({create:{width,height,channels:4,background:'#122038'}}).composite(composites).webp({lossless:true}).toFile(path.join(root,file));
}
await sheet(data.filter(a=>a.cat==='Ikon'&&!a.key.startsWith('icon.')),'review/ikon-ilustratif.webp',7,210);
await sheet(data.filter(a=>a.key.startsWith('icon.')),'review/ikon-kontrol.webp',10,128);
console.log('Two icon contact sheets saved.');
