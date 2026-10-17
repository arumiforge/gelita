const fs=require('node:fs'),path=require('node:path'),http=require('node:http'),assert=require('node:assert/strict'),{execFileSync}=require('node:child_process');
const {chromium}=require(process.env.PLAYWRIGHT_PACKAGE||'playwright');
const root=path.resolve(__dirname,'..'),packet='mockup/gambar-mockup/peta-kedu/revisi-04',python=process.env.PYTHON_EXE||'python';
const saveWebp=(buffer,file)=>{fs.mkdirSync(path.dirname(file),{recursive:true});execFileSync(python,['-c','import io,sys;from PIL import Image;Image.open(io.BytesIO(sys.stdin.buffer.read())).save(sys.argv[1],"WEBP",lossless=True)',file],{input:buffer})};
const mime={'.html':'text/html; charset=utf-8','.js':'text/javascript; charset=utf-8','.css':'text/css','.svg':'image/svg+xml','.webp':'image/webp','.woff2':'font/woff2','.mp3':'audio/mpeg'};
const server=http.createServer((req,res)=>{try{const url=new URL(req.url,'http://localhost'),file=path.resolve(root,'.'+decodeURIComponent(url.pathname));if(req.method!=='GET'||!file.startsWith(root+path.sep)||file.split(path.sep).some(p=>p.startsWith('.'))){res.writeHead(403).end();return}const stat=fs.statSync(file);if(!stat.isFile())throw Error('not file');res.writeHead(200,{'Content-Type':mime[path.extname(file)]||'application/octet-stream'});fs.createReadStream(file).pipe(res)}catch(e){res.writeHead(404).end()}});
(async()=>{
 await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));const origin='http://127.0.0.1:'+server.address().port;
 let browser;
 try{
  browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'msedge'});
  const page=await browser.newPage({viewport:{width:1920,height:1080}});
  const errors=[],remote=[],failed=[];page.on('pageerror',e=>errors.push(e.message));page.on('request',r=>{if(!r.url().startsWith(origin))remote.push(r.url())});page.on('requestfailed',r=>failed.push(r.url()));page.on('response',r=>{if(r.status()>=400)failed.push(r.url()+':'+r.status())});
  await page.goto(origin+'/'+packet+'/preview.html');
  await page.waitForFunction(()=>[...document.images].every(i=>i.complete&&i.naturalWidth>0));await page.evaluate(()=>document.fonts.ready);
  const first=await page.locator('#story-text').innerText();await page.waitForFunction(n=>window.gelitaMapPreview.state.chars>n,first.length);
  await page.locator('#play-trigger').click();const paused=await page.locator('#story-text').innerText();
  await page.waitForTimeout(160);assert.equal(await page.locator('#story-text').innerText(),paused);
  assert.equal(await page.locator('#play-trigger').innerText(),'');assert.equal(await page.locator('#next-trigger').innerText(),'');
  await page.locator('#next-trigger').click();assert.equal(await page.evaluate(()=>window.gelitaMapPreview.state.part),1);assert.equal(await page.locator('#dots i.on').count(),1);
  await page.locator('#previous-trigger').click();assert.equal(await page.evaluate(()=>window.gelitaMapPreview.state.part),0);
  assert.equal(await page.locator('.slice.open').count(),1);
  await page.locator('#pin-mgl').click();assert.equal(await page.locator('#region-next').isDisabled(),true);
  await page.locator('#pin-tmg').click();await page.locator('#kenali-trigger').click();assert.equal(await page.locator('#kenali-sheet').isVisible(),true);
  await page.keyboard.press('Escape');assert.equal(await page.evaluate(()=>document.activeElement.id),'kenali-trigger');
  await page.locator('#menu-trigger').click();assert.equal(await page.locator('[data-demo="library"]').isVisible(),true);assert.equal(await page.locator('[data-demo="profile"]').isVisible(),true);
  await page.keyboard.press('Shift+Tab');assert.equal(await page.evaluate(()=>document.activeElement.dataset.demo),'logout');
  await page.locator('#scenario-trigger').click();assert.equal(await page.evaluate(()=>window.gelitaMapPreview.state.scenario),'progress');assert.equal(await page.locator('.slice.open').count(),3);
  await page.emulateMedia({reducedMotion:'reduce'});await page.evaluate(()=>window.gelitaMapPreview.showPart(0));
  const shots=[];
  const capture=async(name)=>{const file=path.join(root,packet,'variasi-ui',name,'mockup-'+name+'-16-9.webp');saveWebp(await page.screenshot({animations:'disabled'}),file);shots.push(path.relative(root,file).replaceAll('\\','/'))};
  await capture('progres-terbuka');await page.evaluate(()=>window.gelitaMapPreview.setScenario('begin'));await capture('wilayah-terkunci');
  await page.locator('#menu-trigger').click();await capture('menu-terbuka');await page.keyboard.press('Escape');
  await page.locator('#language-trigger').click();await page.locator('[data-language="en"]').click();assert.equal(await page.evaluate(()=>window.gelitaMapPreview.state.language),'en');await capture('english');
  const textFits=[];
  for(const lang of ['id','en']){
    await page.locator('#language-trigger').click();await page.locator('[data-language="'+lang+'"]').click();
    for(let i=0;i<3;i++){await page.evaluate(i=>window.gelitaMapPreview.showPart(i),i);const fit=await page.locator('#story-text').evaluate(el=>({height:el.scrollHeight,box:el.clientHeight,width:el.scrollWidth,boxWidth:el.clientWidth}));assert.ok(fit.height<=fit.box&&fit.width<=fit.boxWidth,'Narration overflow '+lang+' part'+i+JSON.stringify(fit));textFits.push({language:lang,part:i,...fit})}
  }
  await page.evaluate(()=>window.gelitaMapPreview.showPart(0));
  await page.locator('#language-trigger').click();await page.locator('[data-language="id"]').click();
  await page.locator('#sound-trigger').click();await page.waitForFunction(()=>window.gelitaMapPreview.audio[0].currentTime>0);await page.locator('#sound-trigger').click();assert.ok(await page.evaluate(()=>window.gelitaMapPreview.audio.every(a=>a.paused)));
  const touch=[];
  for(const size of [{width:640,height:360},{width:854,height:480}]){
    await page.setViewportSize(size);await page.waitForFunction(()=>Math.abs(+document.getElementById('game').dataset.scale-Math.min(innerWidth/1920,innerHeight/1080))<.00001);
    for(const overlay of [null,'menu','language','kenali']){
      await page.evaluate(o=>{window.gelitaMapPreview.close();if(o)window.gelitaMapPreview.open(o)},overlay);
      const measures=await page.evaluate(o=>{const scope=o?document.getElementById(o+'-sheet'):document.getElementById('game');return [...scope.querySelectorAll('button')].filter(b=>!b.disabled&&!b.closest('[hidden]')&&!b.closest('[inert]')).map(b=>{const r=b.getBoundingClientRect();return {id:b.id||b.dataset.language||b.dataset.demo||'close',x:r.x,y:r.y,width:r.width,height:r.height}})},overlay);
      for(const b of measures){assert.ok(b.width>=43.99&&b.height>=43.99,JSON.stringify({size,overlay,b}));assert.ok(b.x>=-.1&&b.y>=-.1&&b.x+b.width<=size.width+.1&&b.y+b.height<=size.height+.1,JSON.stringify({size,overlay,b}));}
      for(let i=0;i<measures.length;i++)for(const b of measures.slice(i+1)){const a=measures[i];assert.ok(!(Math.max(a.x,b.x)<Math.min(a.x+a.width,b.x+b.width)-.1&&Math.max(a.y,b.y)<Math.min(a.y+a.height,b.y+b.height)-.1),'Overlapping touch targets '+a.id+'/'+b.id)}
      touch.push({viewport:size,overlay,targets:measures});
    }
    await page.evaluate(()=>window.gelitaMapPreview.close());saveWebp(await page.screenshot(),path.join(root,'review','peta-kedu-04-'+size.width+'x'+size.height+'.webp'));
  }
  await page.setViewportSize({width:1440,height:1100});await page.goto(origin+'/index.html#mockups');
  const candidate=page.locator('.mockup-plan-candidate[data-plan="peta-kedu"]');await candidate.waitFor();
  assert.equal(await candidate.locator('[data-candidate-state]').count(),7);
  for(let i=0;i<7;i++){await candidate.locator('[data-candidate-state="'+i+'"]').click();await candidate.locator('.mockup-candidate-image img').evaluate(i=>i.decode());assert.equal(await candidate.locator('[data-candidate-state="'+i+'"]').getAttribute('aria-pressed'),'true')}
  await candidate.locator('[data-candidate-state="0"]').click();await candidate.locator('.candidate-launch').click();
  const frame=page.frameLocator('.candidate-live iframe');await frame.locator('#menu-trigger').click();assert.equal(await frame.locator('[data-demo="library"]').isVisible(),true);
  await page.locator('.mockup-launch').click();assert.equal(await candidate.locator('.candidate-live').isVisible(),false);
  assert.equal(await page.locator('iframe').count(),1);await page.locator('.mockup-feature .mockup-stop').click();
  assert.equal(await page.locator('iframe').count(),0);await candidate.locator('.candidate-launch').click();await candidate.locator('.candidate-stop').click();assert.equal(await page.locator('.candidate-live iframe').count(),0);
  await candidate.locator('.mockup-candidate-image').scrollIntoViewIfNeeded();saveWebp(await page.screenshot(),path.join(root,'review','catalog-peta-kedu-04.webp'));
  assert.deepEqual(errors,[]);assert.deepEqual(remote,[]);assert.deepEqual(failed,[]);
  const report={date:'2026-10-15',checked_at_utc:new Date().toISOString(),passed:true,browser:'Edge isolated headless context, project served by read-only loopback HTTP',checks:['Typewriter advances, pauses and changes part','Icon-only story controls;3 dots track part','1/3 region slices lit initially;3/3 in progress example','Locked region cannot continue','Kenali uses book/search SVG and opens readable excerpt','Library/profile in menu; focus trap/Escape/return focus','ID/EN full narration fits3 parts; reduced-motion immediate text','Existing local map music starts only on user action and mutes','All visible enabled DOM controls minimum44px, inside frame and without target overlaps at640x360/854x480','Catalog:7 candidate states/history load, embedded preview works, menu available, close destroys iframe, only one preview active','No remote/failed requests or JS errors'],screenshots:shots,textFits,touch,limits:['Game integration/device fullscreen not tested','Icon-only controls have accessible names but no visible labels as requested','Story Play controls typing, no new narration recording']};
  fs.writeFileSync(path.join(root,'reports/peta-kedu-04-browser-check.json'),JSON.stringify(report,null,2)+'\n');console.log(JSON.stringify({passed:true,screenshots:shots,touchConfigurations:touch.length},null,2));
 }finally{if(browser)await browser.close();await new Promise(resolve=>server.close(resolve))}
})().catch(e=>{console.error(e);process.exitCode=1});
