const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const {pathToFileURL}=require('node:url');
const {chromium}=require(process.env.PLAYWRIGHT_PACKAGE||'playwright');
const sharp=require(process.env.SHARP_PACKAGE||'sharp');
const root=path.resolve(__dirname,'..');
(async()=>{
 const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'msedge'});
 try{
  const page=await browser.newPage({viewport:{width:1200,height:900}}),errors=[];
  page.on('pageerror',e=>errors.push(e.message));
  await page.goto(pathToFileURL(path.join(root,'index.html')).href);
  await page.locator('[data-group="Karakter"]').click();assert.equal(await page.locator('.card').count(),240);
  const poses=JSON.parse(fs.readFileSync(path.join(root,'provenance/character-generation-results.json'),'utf8')).map(a=>a.key);
  for(const pose of poses){
   await page.locator('#search').fill(pose+'.');assert.equal(await page.locator('.card').count(),24);
   const keys=await page.locator('button.open').evaluateAll(elements=>elements.map(e=>e.dataset.key));
   assert.deepEqual(keys,Array.from({length:24},(_,i)=>pose+'.'+(i+1)));
   await page.locator('button.open[data-key="'+pose+'.24"]').click();
   await page.waitForFunction(()=>{const im=document.querySelector('#detail img');return im.complete&&im.naturalWidth===700&&im.naturalHeight===900});
   await page.keyboard.press('Escape');
  }
  await page.goto(pathToFileURL(path.join(root,'review/character-animation.html')).href);
  assert.equal(await page.locator('#pose option').count(),10);
  for(const pose of poses){
   await page.locator('#pose').selectOption(pose);
   await page.locator('#frame').fill('24');
   await page.waitForFunction(pose=>{const im=document.querySelector('#image');return im.src.endsWith(pose+'.24.webp')&&im.complete&&im.naturalWidth===700},pose);
  }
  await page.locator('#pose').selectOption('char.jaka.idle');
  await page.locator('#play').click();await page.waitForFunction(()=>Number(document.querySelector('#frame').value)>1);await page.locator('#play').click();
  await sharp(await page.screenshot()).webp({lossless:true}).toFile(path.join(root,'review/character-preview-desktop.webp'));
  await page.setViewportSize({width:390,height:844});assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
  await sharp(await page.screenshot()).webp({lossless:true}).toFile(path.join(root,'review/character-preview-mobile.webp'));
  assert.deepEqual(errors,[]);
  const report={date:'2026-10-12',passed:true,checks:['240 character cards','All ten poses have 24 cards in numeric order','Frame 24 of every pose opens as 700 × 900','Ten preview options, slider and playback work','Preview desktop/mobile without horizontal overflow','No JavaScript page errors'],limits:'Local catalog and secondary-motion preview only; game integration is not tested'};
  fs.writeFileSync(path.join(root,'reports/character-browser-check-report.json'),JSON.stringify(report,null,2)+'\n');console.log(report);
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
