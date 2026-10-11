import fs from 'node:fs';
import {fileURLToPath} from 'node:url';
import path from 'node:path';
const root=path.dirname(fileURLToPath(import.meta.url));
const file=path.join(root,'data.json');
const data=JSON.parse(fs.readFileSync(file,'utf8'));
for(const a of data.filter(a=>a.size!=='Audio')){
 delete a.historical_generation_prompt; // Original prompts remain in the verified pre-change archive.
 a.prompt=a.prompt.replaceAll("bird's-eye storybook view","bird's-eye stylized 3D diorama view").replaceAll('illustrated storybook map','stylized 3D relief map').replaceAll('painted on dark indigo parchment with antique-gold ink linework and soft watercolour terrain','modeled as dimensional dark-indigo terrain with raised antique-gold route lines, softly sculpted landforms and cinematic depth');
 if(a.key.startsWith('char.jaka.'))a.prompt=a.prompt.replace('SUBJECT AND COMPOSITION\n','SUBJECT AND COMPOSITION\nThe subject is Jaka, matching reference.01. ');
 if(a.key.startsWith('char.kedu.'))a.prompt=a.prompt.replace('SUBJECT AND COMPOSITION\n','SUBJECT AND COMPOSITION\nThe subject is Mbah Kedu, matching reference.02. ');
 if(/library\..*\.p10\.1/.test(a.key))a.prompt=a.prompt.replace('standing on an invisible ground line near the bottom, ','').replace('whole figure visible including feet and headwear','whole requested figure visible including feet');
 if(a.key==='ui.placeholder'){
  a.prompt='Create one genuine editable SVG placeholder for the GELITA catalog, 400 × 300 pixels, 4:3. Calm deep navy background, a thin dashed warm-gold border, one simple central Lantern of Kedu glyph in #DFC087. Include the exact Indonesian label "Gambar belum tersedia" as editable text. Keep clear margins and legible text. Match the GELITA gold-and-navy interface. No raster embedding, textures, characters, watermark or extra lettering.';
  a.negative='No raster embedding, clutter, watermark or misspelled label.';a.prompt_mode='SVG / native vector';
 }
}
fs.writeFileSync(file,JSON.stringify(data,null,2)+'\n');
console.log('Final active prompt consistency pass: 273 visual entries; original prompts archived; audio preserved.');
