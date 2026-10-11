import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const root=path.dirname(fileURLToPath(import.meta.url));
const file=path.join(root,'data.json');const data=JSON.parse(fs.readFileSync(file,'utf8'));
for(const a of data.filter(a=>a.key.startsWith('icon.'))){
 if(a.key==='icon.text'||a.key==='icon.sparkle'){
  const svgFile=path.join(root,a.file);let svg=fs.readFileSync(svgFile,'utf8');
  const geometry=a.key==='icon.text'?'<path d="M4 6h16 M4 12h16 M4 18h10"/>':'<path d="M12 2v5 M12 17v5 M2 12h5 M17 12h5 M5 5l3.5 3.5 M15.5 15.5 19 19 M19 5l-3.5 3.5 M8.5 15.5 5 19"/>';
  svg=svg.slice(0,svg.indexOf('</title>')+8)+geometry+'</svg>';
  fs.writeFileSync(svgFile,svg);
 }
 a.prompt=a.prompt.replace('single left-pointing chevron','left-pointing arrow with a short shaft').replace('single right-pointing chevron','right-pointing arrow with a short shaft').replace('single upward chevron','upward arrow with a short shaft').replace('single downward chevron','downward arrow with a short shaft');
 a.generation_prompt=a.prompt;
}
const favicon=data.find(a=>a.key==='app.favicon.ico');
favicon.prompt='Create the GELITA favicon as a 32 × 32 WebP export from assets/ikon/app.icon.svg. Use the same Lantern of Kedu emblem on a full-bleed opaque deep navy background, simplified into clearly readable warm-gold shapes with a bright flame. Keep the handle and base inside the central safe area. Soft gold shading may support the brand; omit ornamental microdetails at this size. No people, scarf, lettering, watermark, texture or transparent background. Keep the SVG master editable.';
favicon.generation_prompt=favicon.prompt;
fs.writeFileSync(file,JSON.stringify(data,null,2)+'\n');
console.log('Native symbol prompts and tiny favicon delivery aligned.');
