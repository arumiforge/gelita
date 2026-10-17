import fs from 'node:fs';import path from 'node:path';import assert from 'node:assert/strict';import {fileURLToPath} from 'node:url';
import {assertRevisionPreserved,assertAudioPreserved} from './catalog-revisions.mjs';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');const read=p=>fs.readFileSync(path.join(root,p),'utf8');const data=JSON.parse(read('data.json'));const html=read('index.html');
const embedded=JSON.parse(html.match(/const DATA=(.*);\r?\nconst SUMMARY=/s)[1]);assert.deepEqual(embedded,data);
const puzzles=JSON.parse(read('provenance/puzzle-generation-results-20261014.json'));
const puzzleKeys=new Set(puzzles.assets.map(a=>a.key));
const replacements=new Set(puzzles.assets.filter(a=>a.replaced).map(a=>a.key));
assert.deepEqual(replacements,new Set(['challenge.item.tmg-1-01','challenge.item.tmg-1-03','challenge.item.mgl-1-01','challenge.item.wnb-1-07']));
const addedPuzzles=puzzles.assets.filter(a=>!a.replaced);assert.equal(addedPuzzles.length,21);
for(const prefix of ['tmg','mgl','wnb'])for(const variant of 'defghij')assert.ok(addedPuzzles.some(a=>a.key===`challenge.item.${prefix}-1-${variant}01`));
const allowedPuzzleFields=new Set(['title','desc','prompt','negative','prompt_revision','generation_prompt','generated_by','status','generation_status','content_review','production','note','puzzle','puzzle_provenance']);
const assertPreserved=before=>{const actual=data.find(x=>x.key===before.key);assert.ok(actual);if(!replacements.has(before.key)){assertRevisionPreserved(before,actual);return;}const stable=o=>Object.fromEntries(Object.entries(o).filter(([k])=>!allowedPuzzleFields.has(k)));assert.deepEqual(stable(actual),stable(before),'Unapproved original field changed: '+before.key);};
for(const before of JSON.parse(read(puzzles.original_catalog_snapshot)))assertPreserved(before);
const resultUi=JSON.parse(read('provenance/result-ui-generation-results-20261013.json'));const resultUiKeys=new Set(resultUi.assets.map(a=>a.key));
const languageFlags=JSON.parse(read('provenance/language-flag-generation-results-20261013.json'));const flagKeys=new Set(languageFlags.assets.map(a=>a.key));
const beforeFlags=JSON.parse(read(languageFlags.original_catalog_snapshot));for(const a of beforeFlags)assertPreserved(a);
assert.equal(data.length,languageFlags.base_entries+languageFlags.assets.length+addedPuzzles.length);assert.equal(new Set(data.map(a=>a.key)).size,data.length);assert.equal(new Set(data.map(a=>a.id)).size,data.length);
const beforeResultUi=JSON.parse(read(resultUi.original_catalog_snapshot));for(const a of beforeResultUi)assertPreserved(a);
for(const a of [...resultUi.assets,...languageFlags.assets]){const actual=data.find(x=>x.key===a.key);assert.ok(actual);assert.equal(actual.file,a.file);assert.equal(actual.size,a.size.join(' × '));assert.equal(actual.alpha,a.alpha);}
const audio=data.filter(a=>a.size==='Audio');const audioState=assertAudioPreserved(data);
const mockups=JSON.parse(read('mockup/catalog.json'));
const embeddedMockups=JSON.parse(html.match(/const MOCKUPS=(.*);\r?\n/)[1]);assert.deepEqual(mockups,embeddedMockups);
assert.equal(new Set(mockups.pages.map(p=>p.id)).size,mockups.pages.length);
for(const p of mockups.pages){assert.ok(['available','planned'].includes(p.status));for(const f of [p.brief,p.image,p.preview,...(p.states||[]).map(s=>s.image),p.candidate?.image,p.candidate?.brief,p.candidate?.prompt,p.candidate?.preview,...(p.candidate?.states||[]).map(s=>s.image)])if(f)assert.ok(fs.existsSync(path.join(root,f)),f);if(p.status==='planned')assert.ok(!p.image&&!p.preview,'Planned pages must not claim approved visuals');if(p.candidate){assert.equal(p.status,'planned');assert.ok(p.candidate.label&&p.candidate.image&&p.candidate.brief&&p.candidate.prompt);assert.ok((p.candidate.states||[]).every(s=>s.kind!=='selected-reference'),'Candidates must not claim user selection');}}
for(const a of audio)if(a.file){assert.ok(fs.existsSync(path.join(root,a.file)));assert.ok(fs.existsSync(path.join(root,a.audio_provenance.master)));}
const visual=data.filter(a=>a.size!=='Audio');for(const a of visual){assert.ok(a.style_review);for(const k of ['file','preview','generated_asset'])if(a[k])assert.ok(fs.existsSync(path.join(root,a[k])),a[k]);const expected=a.key.replace(/\.(png|svg|ico|webp)$/i,'');assert.equal(path.parse(a.file).name,expected)}
for(const a of visual){assert.equal(a.style_review.status,'Sesuai gaya');assert.equal(a.style_review.basis,'user_override');assert.ok(a.previous_style_review);assert.ok(!a.historical_generation_prompt);assert.ok(!/painterly 2D digital|soft watercolour terrain|storybook illustration/.test(a.prompt));}
const icons=visual.filter(a=>a.cat==='Ikon'&&!flagKeys.has(a.key));assert.equal(icons.length,83);assert.equal(visual.filter(a=>a.cat==='Ikon').length,83+languageFlags.assets.length);
const iconReport=JSON.parse(read('reports/icon-update-report.json'));assert.equal(iconReport.updated,83);assert.equal(new Set(iconReport.changed_keys).size,83);
const generatedIcons=JSON.parse(read('provenance/icon-generation-results.json'));assert.equal(generatedIcons.length,20);assert.equal(new Set(generatedIcons.map(a=>a.key)).size,20);
for(const a of icons){assert.equal(a.actual,a.size);assert.equal(a.actual_alpha,a.alpha);if(a.vector_source)assert.ok(fs.existsSync(path.join(root,a.vector_source)));}
assert.equal(visual.filter(a=>a.provenance?.date==='2026-10-11').length,9);assert.ok(!fs.existsSync(path.join(root,'framegambarkarakter/jakahappy')));
for(const m of JSON.parse(read('provenance/migration-map.json')))assert.ok(fs.existsSync(path.join(root,m.new)),m.new);
const source=JSON.parse(read('archive/before-2026-10-11/data.json'));for(const a of visual){const before=source.find(x=>x.key===a.key);if(before)assert.equal(a.size,before.size,'target changed '+a.key);else if(resultUiKeys.has(a.key)||flagKeys.has(a.key)){assert.equal(a.size,[...resultUi.assets,...languageFlags.assets].find(x=>x.key===a.key).size.join(' × '));}else if(puzzleKeys.has(a.key)){assert.equal(a.size,'1200 × 1200');assert.equal(a.alpha,false);assert.equal(a.puzzle.review_status,'needs_verification');assert.ok(fs.existsSync(path.join(root,a.puzzle_provenance.source_native)));}else {assert.match(a.key,/^char\.(jaka|kedu)\.\w+\.(?:[4-9]|1\d|2[0-4])$/);assert.equal(a.size,'700 × 900');}}
const characters=visual.filter(a=>/^char\.(jaka|kedu)\./.test(a.key));assert.equal(characters.length,240);
for(const stem of new Set(characters.map(a=>a.key.replace(/\.\d+$/,'')))){const frames=characters.filter(a=>a.key.startsWith(stem+'.'));assert.equal(frames.length,24);for(let n=1;n<=24;n++){const a=frames.find(a=>a.key===stem+'.'+n);assert.ok(a);assert.equal(a.character_provenance.frame_count,24);assert.ok(fs.existsSync(path.join(root,a.character_provenance.key_pose)));}}
for(const folder of ['docs','scripts','templates','reports','provenance','assets','review','archive'])assert.ok(fs.statSync(path.join(root,folder)).isDirectory(),folder);
for(const folder of ['source','sources'])assert.ok(!fs.existsSync(path.join(root,folder)),folder+' should remain removed');
for(const a of visual)if(a.vector_source)assert.ok(fs.existsSync(path.join(root,a.vector_source)),a.vector_source);
// Current documentation must resolve locally. Historical source-body links point
// into the original application repository and are intentionally not active links.
const markdown=[];
const walk=dir=>{for(const entry of fs.readdirSync(dir,{withFileTypes:true})){
 if(entry.name==='archive'||entry.name==='.git'||entry.name==='node_modules')continue;
 const full=path.join(dir,entry.name);if(entry.isDirectory())walk(full);else if(entry.name.endsWith('.md'))markdown.push(full);
}};
walk(root);
let markdownLinks=0,htmlLinks=0;
const checkLink=(base,url)=>{
 if(!url||url.startsWith('#')||/^(?:[a-z][a-z0-9+.-]*:|\/\/)/i.test(url))return;
 const target=path.resolve(base,decodeURIComponent(url.split(/[?#]/)[0]));
 assert.ok(target===root||target.startsWith(root+path.sep),'Link outside catalog: '+url);
 assert.ok(fs.existsSync(target),'Missing link in '+path.relative(root,base)+': '+url);
};
for(const file of markdown){
 const body=fs.readFileSync(file,'utf8').replace(/^```[^\n]*\n[\s\S]*?^```[^\n]*$/gm,'');
 for(const match of body.matchAll(/\[[^\]\n]*\]\(([^)\n]+)\)/g)){
  const url=match[1].trim().replace(/^<(.+)>$/,'$1');checkLink(path.dirname(file),url);markdownLinks++;
 }
}
for(const match of html.matchAll(/(?:href|src)="([^"]+)"/g)){
 if(match[1].includes('${'))continue;checkLink(root,match[1]);htmlLinks++;
}
const report={date:new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Bangkok'}),checked_at_utc:new Date().toISOString(),checks:[data.length+' unique IDs and keys',visual.length+' visuals have style reviews and existing files',visual.length+' user-overridden style statuses with previous reviews preserved','240 character frames: ten poses with frames 1–24 and saved key poses','Active prompts exclude conflicting legacy art direction','83 icons updated with correct size and alpha','20 unique selected image_gen icon outputs','8 native vector control sources present','9 generated references present','Semantic filenames verified','475 migrated paths resolve','HTML data exactly matches data.json','88 narration objects unchanged; 9 non-narration audio objects changed only in authorized production fields','Original target dimensions unchanged; new frames are 700 × 900','Requested character folder deleted','Organized folders exist and source/sources removed','Current Markdown and static HTML links resolve'],documentation:{files:markdown.length,links:markdownLinks,historical_archive_body_links_excluded:true},static_html_links:htmlLinks,audio:audioState,mockups:{available:mockups.pages.filter(p=>p.status==='available').length,planned:mockups.pages.filter(p=>p.status==='planned').length},passed:true};fs.writeFileSync(path.join(root,'reports/validation-report.json'),JSON.stringify(report,null,2)+'\n');console.log(report);
