const fs=require('fs'),path=require('path'),assert=require('assert');
const {pathToFileURL}=require('url');
const root=path.join(__dirname,'..');
const data=JSON.parse(fs.readFileSync(path.join(root,'data.json'),'utf8'));
const visuals=data.filter(a=>a.size!=='Audio');
const {chromium}=require(process.env.PLAYWRIGHT_PACKAGE||'playwright');
const saveWebp=(buffer,file)=>require('child_process').execFileSync('python',['-c','import io,sys; from PIL import Image; Image.open(io.BytesIO(sys.stdin.buffer.read())).save(sys.argv[1],"WEBP",lossless=True)',path.join(root,file)],{input:buffer});
(async()=>{
  const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'msedge'});
  try{
    const page=await browser.newPage({viewport:{width:1440,height:1100},acceptDownloads:true});
    const errors=[],remoteRequests=[],failedRequests=[];
    page.on('pageerror',e=>errors.push(e.message));
    page.on('request',r=>{if(/^https?:/.test(r.url()))remoteRequests.push(r.url());});
    page.on('requestfailed',r=>failedRequests.push({url:r.url(),error:r.failure()?.errorText}));
    await page.goto(pathToFileURL(path.join(root,'index.html')).href);
    await page.waitForSelector('.asset-card');
    await page.evaluate(()=>document.fonts.ready);
    // Verify copied text without replacing the user's system clipboard.
    await page.evaluate(()=>Object.defineProperty(navigator,'clipboard',{configurable:true,value:{writeText:async text=>{window.catalogCopiedText=text;}}}));
    assert.equal(await page.locator('.asset-card').count(),visuals.length);
    assert.equal(await page.evaluate(()=>bootstrap.Dropdown.VERSION),'5.3.8');
    assert.ok(await page.evaluate(()=>document.fonts.check('14px Jakarta')));
    assert.deepEqual(await page.locator('.stat strong').allTextContents(),[String(data.length),String(visuals.length),'240','97']);
    await page.waitForFunction(()=>document.querySelector('.hero-image').complete&&document.querySelector('.hero-image').naturalWidth>0);
    saveWebp(await page.screenshot(),'review/catalog-desktop.webp');

    // Mockups are a separate page gallery; derived state images do not change asset counts.
    assert.equal(await page.locator('.mockup-feature').count(),1);
    assert.equal(await page.locator('.mockup-plan').count(),12);
    for(let state=0;state<5;state++){
      await page.locator(`[data-state="${state}"]`).click();
      await page.waitForFunction(()=>{const i=document.querySelector('.mockup-image');return i.complete&&i.naturalWidth>0;});
      assert.equal(await page.locator(`[data-state="${state}"]`).getAttribute('aria-pressed'),'true');
    }
    await page.locator('[data-state="1"]').click();
    await page.locator('#mockups').scrollIntoViewIfNeeded();
    saveWebp(await page.screenshot(),'review/catalog-mockups-desktop.webp');
    if(!await page.locator('.mockup-roadmap').evaluate(el=>el.open))await page.locator('.mockup-roadmap summary').click();
    const plans=JSON.parse(fs.readFileSync(path.join(root,'mockup/catalog.json'),'utf8')).pages.filter(p=>p.status==='planned');
    assert.equal(await page.locator('.mockup-plan a').count(),plans.reduce((n,p)=>n+2+(p.candidate?3:0)+(p.candidate?.preview?1:0),0));
    for(const candidate of plans.filter(p=>p.candidate)){
      const image=page.locator(`.mockup-candidate-image img[src="${candidate.candidate.image}"]`);
      await image.scrollIntoViewIfNeeded();
      await image.evaluate(img=>img.decode());
      assert.equal(await image.evaluate(img=>img.naturalWidth),1920);
      assert.equal(await image.evaluate(img=>img.naturalHeight),1080);
    }
    await page.locator('.mockup-roadmap summary').click();
    await page.locator('.mockup-launch').click();
    const frame=page.frameLocator('.mockup-frame-wrap iframe');
    await frame.locator('#language-trigger').click();
    await frame.locator('[data-language="en"]').click();
    assert.equal(await frame.locator('#result-title').innerText(),'Challenge complete!');
    assert.equal(await frame.locator('#sound-trigger').getAttribute('aria-pressed'),'true');
    await frame.locator('#sound-trigger').click();
    const livePage=await (await page.locator('.mockup-frame-wrap iframe').elementHandle()).contentFrame();
    await livePage.waitForFunction(()=>window.gelitaPreview.audio[0].currentTime>0);
    await frame.locator('#sound-trigger').click();
    assert.ok(await livePage.evaluate(()=>window.gelitaPreview.audio.every(a=>a.paused)));
    await page.locator('.mockup-stop').click();
    assert.equal(await page.locator('.mockup-frame-wrap iframe').count(),0);
    assert.equal(await page.evaluate(()=>document.activeElement.classList.contains('mockup-launch')),true);

    await page.locator('#updatesToggle').click();
    await page.waitForFunction(()=>document.querySelector('#updatesContent').classList.contains('show'));
    assert.equal(await page.locator('#updatesContent .update-item').count(),3);
    assert.ok(await page.locator('#updatesContent a[href$=".xlsx"]').isVisible());
    await page.locator('#updatesToggle').click();
    await page.waitForFunction(()=>!document.querySelector('#updatesContent').classList.contains('show')&&!document.querySelector('#updatesContent').classList.contains('collapsing'));

    await page.locator('#style').selectOption('Sesuai gaya');
    assert.equal(await page.locator('.asset-card').count(),visuals.length);
    await page.locator('#style').selectOption('Belum sesuai');
    assert.equal(await page.locator('.asset-card').count(),0);
    await page.locator('#emptyReset').click();
    assert.equal(await page.locator('.asset-card').count(),visuals.length);
    await page.locator('#region').selectOption('Temanggung');
    assert.equal(await page.locator('.asset-card').count(),visuals.filter(a=>a.region==='Temanggung').length);
    await page.locator('#reset').click();
    await page.locator('#search').fill('char.jaka.');
    assert.equal(await page.locator('.asset-card').count(),144);
    await page.locator('[data-key="char.jaka.happy.1"]').click();
    assert.equal(await page.locator('dialog').evaluate(e=>e.open),true);
    assert.ok((await page.locator('#detail .prompt').innerText()).toLowerCase().includes('red scarf'));
    const promptText=await page.locator('#detail .prompt').innerText();
    await page.locator('#copyPrompt').click();
    assert.equal(await page.evaluate(()=>window.catalogCopiedText),promptText);
    assert.equal(await page.locator('#toast').innerText(),'Prompt berhasil disalin.');
    const promptDownload=page.waitForEvent('download');
    await page.locator('#downloadPrompt').click();
    const promptFile=await promptDownload;
    assert.equal(fs.readFileSync(await promptFile.path(),'utf8'),promptText);
    assert.ok(await page.locator('dialog #toast').isVisible());
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('dialog').evaluate(e=>e.open),false);
    assert.equal(await page.evaluate(()=>document.activeElement.dataset.key),'char.jaka.happy.1');

    await page.locator('#reset').click();
    await page.locator('#search').fill('ui.result-');
    const puzzleAssets=JSON.parse(fs.readFileSync(path.join(root,'provenance/puzzle-generation-results-20261014.json'),'utf8')).assets;
    assert.equal(puzzleAssets.length,25);
    for(const asset of puzzleAssets){
      await page.locator('#search').fill(asset.key);
      assert.equal(await page.locator('.asset-card').count(),1);
      await page.locator(`[data-key="${asset.key}"]`).click();
      await page.waitForFunction(()=>{const image=document.querySelector('#detail img');return image.complete&&image.naturalWidth===1200&&image.naturalHeight===1200;});
      assert.ok((await page.locator('#detail .prompt').innerText()).includes('Puzzle safety'));
      await page.keyboard.press('Escape');
    }
    await page.locator('#search').fill('ui.result-');
    assert.equal(await page.locator('.asset-card').count(),7);
    await page.locator('[data-key="ui.result-panel"]').click();
    await page.waitForFunction(()=>document.querySelector('#detail img').complete&&document.querySelector('#detail img').naturalWidth===1536);
    saveWebp(await page.screenshot(),'review/catalog-detail.webp');
    await page.keyboard.press('Escape');
    await page.locator('#reset').click();
    await page.locator('#search').fill('icon.language-');
    assert.equal(await page.locator('.asset-card').count(),2);
    await page.locator('[data-key="icon.language-en"]').click();
    await page.waitForFunction(()=>document.querySelector('#detail img').complete&&document.querySelector('#detail img').naturalWidth===256);
    await page.keyboard.press('Escape');
    await page.locator('#reset').click();
    await page.locator('#search').fill('app.icon-512');
    assert.equal(await page.locator('.asset-card').count(),1);
    await page.locator('[data-key="app.icon-512.png"]').click();
    await page.waitForFunction(()=>document.querySelector('#detail img').complete&&document.querySelector('#detail img').naturalWidth===512);
    assert.ok((await page.locator('#detail .prompt').innerText()).includes('Lantern of Kedu'));
    await page.keyboard.press('Escape');

    await page.locator('#reset').click();
    await page.locator('[data-group="Referensi utama"]').click();
    assert.equal(await page.evaluate(()=>document.activeElement.dataset.group),'Referensi utama');
    assert.equal(await page.locator('.asset-card').count(),26);
    await page.locator('#sort').selectOption('title');
    const titles=await page.locator('.asset-card h4').allTextContents();
    assert.deepEqual(titles,[...titles].sort(new Intl.Collator('id',{numeric:true,sensitivity:'base'}).compare));
    await page.locator('#sort').selectOption('key');
    const keys=await page.locator('.asset-card .key').allTextContents();
    assert.deepEqual(keys,[...keys].sort(new Intl.Collator('id',{numeric:true,sensitivity:'base'}).compare));
    await page.locator('[data-key="reference.03"]').click();
    const popupPromise=page.waitForEvent('popup');
    await page.getByText('Buka gambar asli ↗',{exact:true}).click();
    const popup=await popupPromise;
    await popup.waitForLoadState();
    assert.ok(popup.url().endsWith('reference.03.webp'));
    await popup.close();
    await page.keyboard.press('Escape');
    await page.locator('#catalog').scrollIntoViewIfNeeded();
    await page.waitForFunction(()=>[...document.querySelectorAll('.asset-card img')].slice(0,3).every(img=>img.complete&&img.naturalWidth>0));
    saveWebp(await page.screenshot(),'review/catalog-collection.webp');

    await page.locator('#exportMenu').click();
    assert.equal(await page.locator('#exportMenu').getAttribute('aria-expanded'),'true');
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('#exportMenu').getAttribute('aria-expanded'),'false');
    await page.locator('#exportMenu').click();
    const exported=page.waitForEvent('download');
    await page.locator('#jsonExport').click();
    const json=await exported;
    assert.equal(JSON.parse(fs.readFileSync(await json.path(),'utf8')).length,26);
    await page.locator('#exportMenu').click();
    const csvExport=page.waitForEvent('download');
    await page.locator('#csvExport').click();
    const csv=await csvExport;
    const csvText=fs.readFileSync(await csv.path(),'utf8');
    assert.ok(csvText.startsWith('\uFEFF"kode","nama"'));
    assert.equal(csvText.trim().split('\r\n').length,27);
    await page.locator('#exportMenu').click();
    await page.locator('#copyGroup').click();
    assert.equal(await page.evaluate(()=>window.catalogCopiedText),data.filter(a=>a.key.startsWith('reference.')).sort((a,b)=>new Intl.Collator('id',{numeric:true,sensitivity:'base'}).compare(a.key,b.key)).map(a=>a.key+'\n'+a.prompt).join('\n\n---\n\n'));

    await page.locator('#reset').click();
    await page.locator('[data-group="Audio"]').click();
    assert.equal(await page.locator('.asset-card').count(),97);
    assert.equal(await page.locator('#collections audio').count(),9);
    assert.ok(await page.locator('#collections audio').evaluateAll(elements=>elements.every(a=>a.paused&&!a.autoplay)));
    await page.locator('#audioKind').selectOption('available');
    assert.equal(await page.locator('.asset-card').count(),9);
    for(const a of data.filter(a=>a.size==='Audio'&&a.file)){
      await page.locator(`[data-key="${a.key}"]`).click();
      await page.locator('#detail audio').evaluate(async el=>{await el.play();});
      await page.waitForFunction(()=>document.querySelector('#detail audio').duration>0);
      assert.equal(await page.locator('#detail audio').evaluate(el=>el.error),null);
      assert.equal(await page.locator('#detail audio').evaluate(el=>el.loop),a.cat==='Musik');
      await page.keyboard.press('Escape');
      await page.waitForFunction(()=>!document.querySelector('#dialog').open&&document.querySelector('#detail audio').paused);
    }
    await page.locator('#audioKind').selectOption('Musik');
    assert.equal(await page.locator('.asset-card').count(),3);
    await page.locator('#audioKind').selectOption('Efek suara');
    assert.equal(await page.locator('.asset-card').count(),6);
    await page.locator('#audioKind').selectOption('Narasi');
    assert.equal(await page.locator('.asset-card').count(),88);
    await page.locator('[data-key]').first().click();
    assert.ok((await page.locator('#detail').innerText()).includes('Rekaman belum tersedia'));
    assert.equal(await page.locator('#copyPrompt').innerText(),'Salin brief');
    const briefText=await page.locator('#detail .prompt').innerText();
    await page.locator('#copyPrompt').click();
    assert.equal(await page.evaluate(()=>window.catalogCopiedText),briefText);
    await page.keyboard.press('Escape');
    await page.locator('#reset').click();
    await page.locator('#search').fill('zzzznomatch');
    assert.equal(await page.locator('.empty').count(),1);
    await page.locator('#emptyReset').click();
    assert.equal(await page.locator('#sort').inputValue(),'catalog');

    for(const width of [360,390,768,1024,1440,1920]){
      await page.setViewportSize({width,height:900});
      assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Overflow at '+width);
    }
    await page.setViewportSize({width:390,height:844});
    await page.evaluate(()=>scrollTo({top:0,behavior:'instant'}));
    saveWebp(await page.screenshot(),'review/catalog-mobile.webp');
    await page.locator('#catalog').scrollIntoViewIfNeeded();
    saveWebp(await page.screenshot(),'review/catalog-mobile-collection.webp');
    await page.locator('#updatesLink').click();
    await page.waitForFunction(()=>document.querySelector('#updatesContent').classList.contains('show'));
    assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
    await page.locator('#updatesToggle').click();
    await page.waitForFunction(()=>!document.querySelector('#updatesContent').classList.contains('collapsing'));
    await page.locator('#search').fill('icon.language-en');
    await page.locator('[data-key="icon.language-en"]').click();
    await page.waitForFunction(()=>document.querySelector('#detail img').complete&&document.querySelector('#detail img').naturalWidth===256);
    assert.ok(await page.locator('#close').isVisible());
    assert.ok(await page.evaluate(()=>{const d=document.querySelector('dialog');return d.scrollWidth<=d.clientWidth;}));
    saveWebp(await page.screenshot(),'review/catalog-mobile-detail.webp');
    await page.keyboard.press('Escape');
    await page.emulateMedia({reducedMotion:'reduce'});
    assert.equal(await page.evaluate(()=>getComputedStyle(document.documentElement).scrollBehavior),'auto');

    assert.deepEqual(errors,[]);
    assert.deepEqual(remoteRequests,[]);
    assert.deepEqual(failedRequests,[]);
    const report={date:new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Bangkok'}),checked_at_utc:new Date().toISOString(),passed:true,bootstrap_version:'5.3.8',checks:[visuals.length+' visual cards; 88 unchanged narration briefs and 9 playable synthesized music/sfx previews','Local Bootstrap CSS/JS and Jakarta font loaded; no remote or failed requests','Bootstrap update collapse and export dropdown; Escape support','Style, region, search and empty-state reset','144 Jaka frames; dialog/Escape and focus return; collection focus retained','7 result UI slots; 2 language flags; 512 px app icon','Natural sorting by title and key; 26 references','Original offline WebP opens; prompt TXT, filtered JSON and CSV downloads','Prompt, brief and group copy text/feedback verified with clipboard stub','Nine MP3 previews decode/play in browser, stop on dialog close; three audio-type filters and 88 narration briefs', 'Mockup gallery: selected reference, four coded states, 12 planned briefs and embedded preview', 'Embedded language switching, click-activated audio and mute; closing iframe stops playback','No horizontal overflow at 360, 390, 768, 1024, 1440 and 1920 px','Mobile updates and asset detail; reduced-motion preference','No JavaScript page errors'],screenshots:['catalog-mockups-desktop.webp','catalog-desktop.webp','catalog-collection.webp','catalog-detail.webp','catalog-mobile.webp','catalog-mobile-collection.webp','catalog-mobile-detail.webp']};
    fs.writeFileSync(path.join(root,'reports/browser-check-report.json'),JSON.stringify(report,null,2)+'\n');
    console.log(JSON.stringify(report,null,2));
  }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});


