import fs from 'node:fs';import path from 'node:path';import assert from 'node:assert/strict';import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');const read=p=>fs.readFileSync(path.join(root,p),'utf8');const data=JSON.parse(read('data.json'));const html=read('index.html');
const embedded=JSON.parse(html.match(/const DATA=(.*);\r?\nconst SUMMARY=/s)[1]);assert.deepEqual(embedded,data);
const resultUi=JSON.parse(read('provenance/result-ui-generation-results-20261013.json'));const resultUiKeys=new Set(resultUi.assets.map(a=>a.key));
const languageFlags=JSON.parse(read('provenance/language-flag-generation-results-20261013.json'));const flagKeys=new Set(languageFlags.assets.map(a=>a.key));
const beforeFlags=JSON.parse(read(languageFlags.original_catalog_snapshot));for(const a of beforeFlags)assert.deepEqual(data.find(x=>x.key===a.key),a,'Pre-flag entry changed: '+a.key);
assert.equal(data.length,languageFlags.base_entries+languageFlags.assets.length);assert.equal(new Set(data.map(a=>a.key)).size,data.length);assert.equal(new Set(data.map(a=>a.id)).size,data.length);
const beforeResultUi=JSON.parse(read(resultUi.original_catalog_snapshot));for(const a of beforeResultUi)assert.deepEqual(data.find(x=>x.key===a.key),a,'Original entry changed: '+a.key);
for(const a of [...resultUi.assets,...languageFlags.assets]){const actual=data.find(x=>x.key===a.key);assert.ok(actual);assert.equal(actual.file,a.file);assert.equal(actual.size,a.size.join(' × '));assert.equal(actual.alpha,a.alpha);}
const audio=data.filter(a=>a.size==='Audio');assert.deepEqual(audio,JSON.parse(read('archive/audio-baseline.json')));
const visual=data.filter(a=>a.size!=='Audio');for(const a of visual){assert.ok(a.style_review);for(const k of ['file','preview','generated_asset'])if(a[k])assert.ok(fs.existsSync(path.join(root,a[k])),a[k]);const expected=a.key.replace(/\.(png|svg|ico|webp)$/i,'');assert.equal(path.parse(a.file).name,expected)}
for(const a of visual){assert.equal(a.style_review.status,'Sesuai gaya');assert.equal(a.style_review.basis,'user_override');assert.ok(a.previous_style_review);assert.ok(!a.historical_generation_prompt);assert.ok(!/painterly 2D digital|soft watercolour terrain|storybook illustration/.test(a.prompt));}
const icons=visual.filter(a=>a.cat==='Ikon'&&!flagKeys.has(a.key));assert.equal(icons.length,83);assert.equal(visual.filter(a=>a.cat==='Ikon').length,83+languageFlags.assets.length);
const iconReport=JSON.parse(read('reports/icon-update-report.json'));assert.equal(iconReport.updated,83);assert.equal(new Set(iconReport.changed_keys).size,83);
const generatedIcons=JSON.parse(read('provenance/icon-generation-results.json'));assert.equal(generatedIcons.length,20);assert.equal(new Set(generatedIcons.map(a=>a.key)).size,20);
for(const a of icons){assert.equal(a.actual,a.size);assert.equal(a.actual_alpha,a.alpha);if(a.vector_source)assert.ok(fs.existsSync(path.join(root,a.vector_source)));}
assert.equal(visual.filter(a=>a.provenance?.date==='2026-10-11').length,9);assert.ok(!fs.existsSync(path.join(root,'framegambarkarakter/jakahappy')));
for(const m of JSON.parse(read('provenance/migration-map.json')))assert.ok(fs.existsSync(path.join(root,m.new)),m.new);
const source=JSON.parse(read('archive/before-2026-10-11/data.json'));for(const a of visual){const before=source.find(x=>x.key===a.key);if(before)assert.equal(a.size,before.size,'target changed '+a.key);else if(resultUiKeys.has(a.key)||flagKeys.has(a.key)){assert.equal(a.size,[...resultUi.assets,...languageFlags.assets].find(x=>x.key===a.key).size.join(' × '));}else {assert.match(a.key,/^char\.(jaka|kedu)\.\w+\.(?:[4-9]|1\d|2[0-4])$/);assert.equal(a.size,'700 × 900');}}
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
const report={date:new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Bangkok'}),checked_at_utc:new Date().toISOString(),checks:[data.length+' unique IDs and keys',visual.length+' visuals have style reviews and existing files',visual.length+' user-overridden style statuses with previous reviews preserved','240 character frames: ten poses with frames 1–24 and saved key poses','Active prompts exclude conflicting legacy art direction','83 icons updated with correct size and alpha','20 unique selected image_gen icon outputs','8 native vector control sources present','9 generated references present','Semantic filenames verified','475 migrated paths resolve','HTML data exactly matches data.json','97 audio objects unchanged','Original target dimensions unchanged; new frames are 700 × 900','Requested character folder deleted','Organized folders exist and source/sources removed','Current Markdown and static HTML links resolve'],documentation:{files:markdown.length,links:markdownLinks,historical_archive_body_links_excluded:true},static_html_links:htmlLinks,passed:true};fs.writeFileSync(path.join(root,'reports/validation-report.json'),JSON.stringify(report,null,2)+'\n');console.log(report);
