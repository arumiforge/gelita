import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const root=path.dirname(fileURLToPath(import.meta.url));
const read=p=>fs.readFileSync(path.join(root,p),'utf8');
const write=(p,s)=>fs.writeFileSync(path.join(root,p),s);
const data=JSON.parse(read('data.json'));
const visuals=data.filter(a=>a.size!=='Audio');
const oldStyle='storybook illustration for an Indonesian children\'s educational game (ages 8–14), Premium stylized 3D animated adventure game rendering, dimensional materials, soft cinematic lighting, warm lantern gold and deep navy, culturally respectful Kedu setting. No painted gouache or paper texture., clean readable silhouettes, gentle volumetric light, Central Javanese Kedu highlands, warm lantern-gold highlights (#DFC087, #F0DFB8) against deep night navy and twilight blue (#0B1320, #1B2740, #26355A), earthy browns (#4A3218, #8A6A32), soft blue-grey mist, calm and hopeful mood, culturally respectful, no text ';
const style='Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.';
const identity='Jaka: match assets/referensi/reference.01.webp, approximately 11-year-old Javanese boy, tousled dark-brown hair with cowlick, brown eyes, cream short-sleeve shirt, red scarf, brown knee-length shorts, orange-brown batik waist cloth, sandals, leather backpack. Mbah Kedu: match reference.02, elderly Javanese man with white moustache and short beard, brown batik headwrap and cream/brown clothing; do not give him Jaka\'s scarf. Apply an identity only when that character is requested; never add characters to object-only or landscape assets.';
const negative='No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.';
const cleanBrief=a=>{
 let b=a.prompt.includes('VISUAL BRIEF\n')?a.prompt.split('VISUAL BRIEF\n')[1].split('\n\nCOMPOSITION')[0]:a.prompt;
 b=b.replaceAll(oldStyle,'');
 const repeat='full-body character, three-quarter view facing screen right, standing on an invisible ground line near the bottom, whole figure visible including feet and headwear with even side margins, soft warm key light from the upper left and a faint cool rim light from the right, clean dimensional edges suitable for a cut-out, no scenery, no cast shadow';
 b=b.replaceAll(repeat+' '+repeat,repeat);
 return b;
};
const symbols={};
for(const a of visuals.filter(a=>a.key.startsWith('icon.')))symbols[a.key]=a.prompt.match(/Symbol: ([^.]+)/)?.[1]||a.title;
const jobs=[];
const changed=[];
for(const a of visuals){
 const previous=a.prompt;
 a.previous_style_review=a.previous_style_review||a.style_review;
 a.style_review={status:'Sesuai gaya',reason:'Ditandai sesuai atas keputusan pengguna 11 Oktober 2026; penandaan ini tidak menyatakan gambar lama telah diperbaiki secara visual.',date:'2026-10-11',method:'Penetapan status oleh pengguna',basis:'user_override'};
 a.note='Status gaya ditetapkan sesuai atas permintaan pengguna. Prompt aktif mengikuti arahan Gelita; catatan audit visual sebelumnya disimpan di previous_style_review.';
 if(a.generation_prompt){a.historical_generation_prompt=a.historical_generation_prompt||a.generation_prompt;delete a.generation_prompt;}
 a.prompt_revision={date:'2026-10-11',scope:'Prompt aktif; prompt generasi lama dipisahkan sebagai riwayat'};
 let brief=cleanBrief(a);
 if(a.prompt.includes('SOURCE ACQUISITION BRIEF:')){
  a.prompt='GELITA DOCUMENTARY MEDIA — source acquisition, not image generation. Preserve factual photographs and authorised video in their original documentary style. The GELITA interface surrounding this media uses navy and warm gold; do not convert documentary evidence to 3D.\n\n'+a.prompt.slice(a.prompt.indexOf('SOURCE ACQUISITION BRIEF:'));
  a.negative='Do not fabricate documentary photographs, alter factual details, or invent source attribution.';
 } else if(a.key.startsWith('icon.')){
  a.alpha=true;
  a.style_target='UI vektor Gelita / emas lentera';
  a.prompt=`Create one production SVG interface icon for GELITA. Asset: ${a.key}. Symbol: ${symbols[a.key]}. Canvas: ${a.size} pixels, aspect ${a.ratio}. Transparent background. Use the existing icon family\'s geometry, rounded caps and joins, optical margins and a restrained warm-gold gradient (#F0DFB8 to #DFC087 to #B58137). Keep silhouettes clear at their target size. Standard icons use a 24-unit logical grid and 2-unit strokes; play is filled, pause has two bold bars. No background disc, ornamental microdetails, raster textures, labels or unrelated symbols. ${a.key==='icon.footsteps'?'One elongated filled sole with five rounded toes; mirror the opposite foot in CSS.':a.key==='icon.rotate-gate'?'Portrait phone with curved clockwise arrow; use a 120-unit grid and a 4-unit stroke.':''}`;
  a.negative='No opaque background, tiny decoration, text labels, watermark or fake transparency.';
  a.prompt_mode='SVG / native vector';
 } else if(a.key.startsWith('app.')){
  a.alpha=false;
  a.prompt=`Create the GELITA application identity for ${a.key}. ${style}\nOne antique hexagonal brass Lantern of Kedu, clear glass panes, restrained pierced kawung cap, ring handle and warm golden flame. Front view, large bold silhouette. Full-bleed opaque navy background. The whole lantern including ring stays within the central 68% of the square; all important content inside the maskable safe circle of radius 40% of the canvas. No people, scarf, text, scenery or outer frame. Target ${a.size} pixels. ${a.file.endsWith('.svg')?'Deliver genuine editable SVG paths with softly shaded gold and navy, not an embedded raster.':'Deliver WebP; resize the approved master without stretching. At 32 pixels retain the broad lantern silhouette and flame; omit decorative microdetails.'}`;
  a.negative='No lettering, characters, cropped ring, transparent holes in the background, watermark or excessive ornament.';
  if(a.file.endsWith('.svg'))a.prompt_mode='SVG / native vector';
 } else {
  if(a.key==='reference.19'||a.key==='reference.20')brief=brief.replace(/Leave a clean central area reserved for the exact uppercase label [“"](EN|ID)[”"], added separately as editable text\./,'Include the exact uppercase label "$1", centered and clearly readable; retain it as editable text in the companion SVG.');
  if(/library\..*\.p10\.1/.test(a.key)&&!brief.includes('Jaka'))brief='Jaka, with the approved reference.01 identity, '+brief;
  const alpha=a.alpha||/transparent|alpha background/i.test(brief);
  a.alpha=!!alpha;
  a.prompt=`GELITA ACTIVE VISUAL DIRECTION — 2026-10-11\nAsset: ${a.key}\n${style}\n${identity}\n\nSUBJECT AND COMPOSITION\n${brief}\n\nDELIVERY\nTarget canvas ${a.size} pixels; aspect ${a.ratio}. Complete subject within canvas, proportional scaling only. ${alpha?'Genuine transparent background outside the subject; preserve alpha.':'Opaque background matching the scene brief.'} Deliver WebP for raster assets.\n\nCONSTRAINTS\n${negative}`;
  a.negative=negative;
 }
 if(previous!==a.prompt)changed.push(a.key);
 if(a.cat==='Ikon'&&!a.key.startsWith('icon.')&&!a.key.startsWith('app.')&&!/^reference\.(17|18|19|20|21|22|23|24)$/.test(a.key)){
  const subject=brief;
  const prompt=`Use case: stylized-concept. Create ONE finished isolated GELITA game icon for ${a.key}. ${style}\nSubject: ${subject}\nSquare canvas, whole subject centered with even 9% margins. Genuine transparent exterior. ${a.key.startsWith('avatar.')?'Use the SAME family of thick dimensional navy circular medallion rim with thin brass edging, warm cream parchment inner disc and one large sculpted gold emblem. Preserve subject motif. No human face.':''}\nBold silhouette readable at 48 pixels; softly modeled forms, bevel highlights but no tiny ornament or scene outside the emblem. No lettering, no people, no watermark, no checkerboard. Target ${a.size} pixels, saved as WebP after proportional resizing.`;
  jobs.push({key:a.key,file:a.file,size:a.size,prompt,transparent_background:true});
 }
}
write('data.json',JSON.stringify(data,null,2)+'\n');
write('icon-generation-jobs.json',JSON.stringify(jobs,null,2)+'\n');
write('prompt-review-report.json',JSON.stringify({date:'2026-10-11',visual_prompts_reviewed:visuals.length,changed:changed.length,changed_keys:changed,user_status_override:visuals.length,audio_unchanged:true,notes:['Style and subject instructions separated','Documentary source briefs exclude synthetic 3D replacement','Vector icons specify native SVG and transparent background','Historical generation prompts are provenance, not active instructions']},null,2)+'\n');

const defs='<defs><linearGradient id="gold" x1="0" y1="0" x2="0.6" y2="1"><stop stop-color="#F0DFB8"/><stop offset=".45" stop-color="#DFC087"/><stop offset="1" stop-color="#B58137"/></linearGradient><radialGradient id="navy" cx=".38" cy=".25" r=".8"><stop stop-color="#26355A"/><stop offset="1" stop-color="#0B1320"/></radialGradient><radialGradient id="flame"><stop stop-color="#FFF9DC"/><stop offset=".5" stop-color="#FFD771"/><stop offset="1" stop-color="#DB8E29"/></radialGradient></defs>';
const lantern='<circle cx="12" cy="4.2" r="2.1"/><path d="M8 7 9.4 6h5.2L16 7l1.3 2.3H6.7z M7 10h10l1 10H6z M9 10v9m6-9v9 M5.5 21h13"/><path fill="url(#flame)" stroke="none" d="M12 11c1 3 2.4 3.7 2 5.6-.3 1.5-1.1 2.1-2 2.1-1.2 0-2.2-.8-2.2-2.1 0-1.8 1.9-2.6 2.2-5.6z"/>';
for(const a of visuals.filter(a=>a.key.startsWith('icon.'))){
 let svg=read(a.file);
 svg=svg.replace('stroke="#DFC087"','stroke="url(#gold)"').replace('stroke-width="1.8"','stroke-width="2"');
 svg=svg.replace(/(<svg[^>]*>)/,'$1'+defs+'<title>'+a.title.replaceAll('&','&amp;')+'</title>');
 if(a.key==='icon.lantern')svg=svg.slice(0,svg.indexOf('</title>')+8)+lantern+'</svg>';
 if(a.key==='icon.footsteps')svg=`<svg xmlns="http://www.w3.org/2000/svg" width="24" height="40" viewBox="0 0 24 40">${defs}<title>Jejak langkah Gelita</title><g fill="url(#gold)"><path d="M7 15c-3 5-4 11-2 15 1 3 5 4 7 2 2-2 1-6 3-10 2-4 1-7-2-8-2-1-4-1-6 1z"/><ellipse cx="7" cy="8" rx="3" ry="4"/><ellipse cx="13" cy="7" rx="2.4" ry="3.3"/><ellipse cx="17.5" cy="9" rx="1.8" ry="2.7"/><ellipse cx="20.5" cy="12" rx="1.4" ry="2.2"/><ellipse cx="21.5" cy="16" rx="1.1" ry="1.7"/></g></svg>`;
 if(a.key==='icon.rotate-gate')svg=`<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">${defs}<title>Putar perangkat ke lanskap</title><g stroke="url(#gold)" fill="none" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><rect x="42" y="28" width="36" height="64" rx="7"/><path d="M32 89a40 40 0 1 1 67-42 M86 45l14 3 3-14 M54 36h12 M56 83h8"/></g></svg>`;
 write(a.file,svg);
 a.generated_by='SVG asli — keluarga ikon Gelita diperbarui dengan kode';
 a.generation_status='Ikon Gelita baru';
 a.generation_prompt=a.prompt;
}
const app=data.find(a=>a.key==='app.icon.svg');
write(app.file,`<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">${defs}<title>Lentera Kedu — GELITA</title><rect width="512" height="512" fill="url(#navy)"/><g transform="translate(92 80) scale(13.67)" stroke="url(#gold)" stroke-width=".9" stroke-linecap="round" stroke-linejoin="round" fill="#DFC087">${lantern}</g></svg>`);
app.generation_status='Ikon aplikasi Gelita baru';app.generated_by='SVG asli — dibuat dengan kode';app.generation_prompt=app.prompt;
fs.mkdirSync(path.join(root,'assets/ikon/vektor'),{recursive:true});
const controls={'17':'icon.sound','18':'icon.sound-off','23':'icon.left','24':'icon.right'};
for(let i=17;i<=24;i++){
 const a=data.find(a=>a.key===`reference.${i}`);
 let symbol;
 if(controls[i])symbol=read(data.find(a=>a.key===controls[i]).file).replace(/^[\s\S]*?<\/title>/,'').replace(/<\/svg>\s*$/,'');
 else if(i===19||i===20)symbol=`<text x="12" y="16" font-family="sans-serif" font-size="11" font-weight="700" text-anchor="middle" stroke="none" fill="url(#gold)">${i===19?'EN':'ID'}</text>`;
 else symbol=i===21?'<path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5"/>':'<path d="M3 8h5V3m13 5h-5V3M3 16h5v5m13-5h-5v5"/>';
 const svg=`<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256">${defs}<title>${a.title}</title><circle cx="128" cy="128" r="112" fill="url(#navy)" stroke="url(#gold)" stroke-width="7"/><circle cx="128" cy="128" r="101" fill="none" stroke="#DFC087" stroke-opacity=".22" stroke-width="2"/><g transform="translate(64 64) scale(5.3333)" fill="none" stroke="url(#gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${symbol}</g></svg>`;
 a.vector_source=`assets/ikon/vektor/${a.key}.svg`;write(a.vector_source,svg);
 a.generated_by='SVG asli dengan ekspor WebP';a.generation_status='Ikon kontrol Gelita baru';a.generation_prompt=a.prompt;
}
write('data.json',JSON.stringify(data,null,2)+'\n');
console.log(JSON.stringify({prompts:visuals.length,changed:changed.length,native_icons:59,image_generation_jobs:jobs.length}));
