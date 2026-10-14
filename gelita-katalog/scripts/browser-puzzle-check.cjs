const fs=require('fs'),path=require('path'),assert=require('assert');
const {pathToFileURL}=require('url');
const {chromium}=require(process.env.PLAYWRIGHT_PACKAGE||'playwright');
const root=path.resolve(__dirname,'..');
const save=(buffer,file)=>require('child_process').execFileSync('python',['-c','import io,sys;from PIL import Image;Image.open(io.BytesIO(sys.stdin.buffer.read())).save(sys.argv[1],"WEBP",lossless=True)',path.join(root,file)],{input:buffer});
(async()=>{
 const browser=await chromium.launch({headless:true,channel:process.env.BROWSER_CHANNEL||'msedge'});
 try {
  const page=await browser.newPage({viewport:{width:1440,height:1100}});
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto(pathToFileURL(path.join(root,'index.html')).href);
  await page.locator('[data-group="Tantangan"]').click();
  assert.equal(await page.locator('.asset-card').count(),66);
  await page.locator('#search').fill('challenge.item.tmg-1-');
  assert.equal(await page.locator('.asset-card').count(),10);
  await page.evaluate(()=>document.fonts.ready);
  await page.waitForFunction(()=>[...document.querySelectorAll('.asset-card img')].every(i=>i.complete&&i.naturalWidth===1200));
  await page.locator('#catalog').scrollIntoViewIfNeeded();
  save(await page.screenshot(),'review/puzzle-20261014/catalog-temanggung.webp');
  for(const region of ['mgl','wnb']){
   await page.locator('#search').fill('challenge.item.'+region+'-1-');
   assert.equal(await page.locator('.asset-card').count(),10);
  }
  await page.locator('#search').fill('challenge.item.wnb-1-g01');
  await page.locator('[data-key="challenge.item.wnb-1-g01"]').click();
  await page.waitForFunction(()=>document.querySelector('#detail img').naturalWidth===1200);
  save(await page.screenshot(),'review/puzzle-20261014/catalog-bundengan.webp');
  const popupPromise=page.waitForEvent('popup');
  await page.getByText('Buka gambar asli ↗',{exact:true}).click();
  const popup=await popupPromise;await popup.waitForLoadState();
  assert.ok(popup.url().endsWith('challenge.item.wnb-1-g01.webp'));
  await popup.close();await page.keyboard.press('Escape');
  assert.deepEqual(errors,[]);
  const report={date:'2026-10-14',checked_at_utc:new Date().toISOString(),passed:true,checks:['66 challenge cards','10 puzzle cards per region including 21 new slots','25 selected 1200px puzzle details checked in browser-check.cjs','Bundengan detail and original offline WebP open correctly','No JavaScript errors'],screenshots:['review/puzzle-20261014/catalog-temanggung.webp','review/puzzle-20261014/catalog-bundengan.webp']};
  fs.writeFileSync(path.join(root,'reports/puzzle-browser-check-20261014.json'),JSON.stringify(report,null,2)+'\n');
  console.log(JSON.stringify(report,null,2));
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
