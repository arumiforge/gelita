const fs=require('fs'),path=require('path'),assert=require('assert/strict');
const {pathToFileURL}=require('url');
const {execFileSync}=require('child_process');
const {chromium}=require(process.env.PLAYWRIGHT_PACKAGE||'playwright');
const root=path.join(__dirname,'..');
const packet=path.join(root,'mockup/gambar-mockup/tantangan-selesai-temanggung');
const saveWebp=(buffer,file)=>execFileSync('python',['-c','import io,sys; from PIL import Image; Image.open(io.BytesIO(sys.stdin.buffer.read())).save(sys.argv[1],"WEBP",lossless=True)',file],{input:buffer});
(async()=>{
  const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'msedge'});
  const page=await browser.newPage({viewport:{width:1920,height:1080},reducedMotion:'reduce'});
  const errors=[];page.on('pageerror',error=>errors.push(error.message));
  await page.goto(pathToFileURL(path.join(packet,'preview-ui-lengkap.html')).href);
  await page.waitForFunction(()=>[...document.images].every(image=>image.complete&&image.naturalWidth>0));
  await page.evaluate(()=>document.fonts.ready);
  assert.ok(await page.locator('.stat-value').evaluateAll(elements=>elements.every(el=>el.scrollWidth<=el.clientWidth)), 'Stat values overflow their cards');
  const images=[];
  const capture=async(state)=>{const folder=path.join(packet,'variasi-ui',state);fs.mkdirSync(folder,{recursive:true});const file=path.join(folder,`mockup-ui-${state}-16-9.webp`);saveWebp(await page.screenshot({animations:'disabled'}),file);images.push(path.relative(root,file).replaceAll('\\','/'))};
  await capture('tampilan-utama');
  await page.locator('#language-trigger').click();
  assert.equal(await page.locator('#language-popover').isVisible(),true);
  assert.equal(await page.locator('[data-language]').count(),2);
  assert.ok((await page.locator('[data-language="id"] img').getAttribute('src')).endsWith('bahasa-indonesia-flag.webp'));
  assert.ok((await page.locator('[data-language="en"] img').getAttribute('src')).endsWith('bahasa-english-uk-flag.webp'));
  await capture('bahasa-terbuka');
  await page.locator('[data-language="en"]').click();
  assert.equal(await page.locator('#result-title').innerText(),'Challenge complete!');
  assert.ok((await page.locator('#current-language-icon').getAttribute('src')).endsWith('bahasa-english-uk-flag.webp'));
  await page.locator('#language-trigger').click();await page.locator('[data-language="id"]').click();
  await page.locator('#sound-trigger').click();assert.equal(await page.locator('#sound-trigger').getAttribute('aria-pressed'),'true');
  await page.locator('#sound-trigger').click();
  await page.locator('#menu-trigger').click();assert.equal(await page.locator('#menu-sheet').isVisible(),true);
  await capture('menu-terbuka');
  await page.keyboard.press('Escape');assert.equal(await page.locator('#menu-sheet').isVisible(),false);
  assert.equal(await page.evaluate(()=>document.activeElement.id),'menu-trigger');
  await page.locator('#detail-trigger').click();assert.equal(await page.locator('#detail-sheet strong').innerText(),'Belum');
  await capture('detail-hasil-terbuka');
  await page.keyboard.press('Escape');
  const touch=[];
  for(const size of [{width:640,height:360},{width:854,height:480}]){
    await page.setViewportSize(size);
    await page.waitForFunction(()=>Math.abs(Number(document.getElementById('game').dataset.scale)-Math.min(innerWidth/1920,innerHeight/1080))<.00001);
    for(const state of [null,'language','menu','detail']){
      await page.evaluate(state=>{window.gelitaPreview.close();if(state)window.gelitaPreview.open(state)},state);
      const selector=state==='language'?'#language-popover button':state==='menu'?'#menu-sheet button':state==='detail'?'#detail-sheet button':'#hud button,#result-panel button';
      const controls=await page.locator(selector).evaluateAll(elements=>elements.map(el=>{const b=el.getBoundingClientRect();return{id:el.id||el.textContent.trim(),x:b.x,y:b.y,width:b.width,height:b.height}}));
      for(const b of controls){assert.ok(b.width>=43.9&&b.height>=43.9,`Small target ${JSON.stringify(b)}`);assert.ok(b.x>=-0.1&&b.y>=-0.1&&b.x+b.width<=size.width+.1&&b.y+b.height<=size.height+.1,`Out of frame ${JSON.stringify({size,state,b})}`)}
      touch.push({viewport:size,state:state||'main',minimum_height:Math.min(...controls.map(b=>b.height)),controls:controls.length});
    }
  }
  await page.evaluate(()=>window.gelitaPreview.close());
  assert.deepEqual(errors,[]);
  const report={date:new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Bangkok'}),timezone:'Asia/Bangkok',checked_at_utc:new Date().toISOString(),passed:true,images,checks:['All local images loaded','Four complete UI states rendered at 1920x1080','Indonesia and UK flag catalogue icons; language choices, translations and active flag switch','Speaker UI toggle','Menu open/Escape and focus return','Detail first-attempt information','Fixed 16:9 canvas scaling','Visible touch controls at least 44px and inside game viewport at 640x360 and 854x480','No JavaScript page errors'],touch_measurements:touch,limitations:['This is a standalone coded preview, not the original game','Route, account and reward actions are preview placeholders','Speaker state does not create or play audio','Fullscreen implementation present; physical device/browser fullscreen behavior not verified','Coded preview and state images are derivatives, not replacement approval of the selected mockup']};
  fs.writeFileSync(path.join(root,'reports/mockup-ui-browser-check.json'),JSON.stringify(report,null,2)+'\n');
  console.log(JSON.stringify({passed:true,states:images.length,touch_viewports:2,errors}));
  await browser.close();
})().catch(error=>{console.error(error);process.exit(1)});
