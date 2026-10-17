// Page inventory is embedded by build; no fetch or network required.
(() => {
  const pages=MOCKUPS.pages, available=pages.filter(p=>p.status==='available');
  function stopOtherPreviews(current){document.querySelectorAll('.mockup-live,.candidate-live').forEach(live=>{if(live!==current&&!live.hidden)live.querySelector('.mockup-stop,.candidate-stop')?.click()})}
  const root=document.getElementById('mockupFeature');
  root.innerHTML=available.map(p=>`<article class="mockup-feature" data-mockup="${esc(p.id)}"><div class="mockup-stage"><a class="mockup-image-link" href="${esc(p.states[p.default_state||0].image)}" target="_blank" rel="noopener" aria-label="Buka gambar mockup ${esc(p.title)} ukuran penuh"><img class="mockup-image" src="${esc(p.states[p.default_state||0].image)}" alt="${esc(p.title)} — preview UI lengkap dengan ikon bahasa terbaru" loading="lazy" decoding="async"></a><div class="mockup-state-buttons" role="group" aria-label="Keadaan mockup ${esc(p.title)}">${p.states.map((s,i)=>`<button type="button" data-state="${i}" aria-pressed="${i===(p.default_state||0)}">${esc(s.label)}</button>`).join('')}</div><p class="mockup-state-caption" role="status">Render preview HTML · UI utama</p></div><div class="mockup-story"><span class="mockup-label">01 / Desain pilihan</span><h3>${esc(p.title)}</h3><p>${esc(p.description)}</p><div class="mockup-facts"><span>16:9 landscape</span><span>Indonesia / English</span><span>Konsep lintas wilayah</span></div><button class="btn btn-gold mockup-launch" type="button" aria-expanded="false">Coba di halaman ini <span aria-hidden="true">↘</span></button><a class="mockup-direct" href="${esc(p.preview)}" target="_blank" rel="noopener">Buka preview penuh ↗</a><a class="mockup-direct" href="${esc(p.brief)}">Aset &amp; petunjuk implementasi ↗</a><p class="mockup-footnote">Gambar keadaan UI adalah hasil render preview. Halaman aplikasi asli belum diintegrasikan.</p></div><div class="mockup-live" hidden><div class="mockup-live-bar"><span>Preview interaktif · kanvas tetap 16:9</span><button type="button" class="mockup-stop">Tutup preview ×</button></div><div class="mockup-frame-wrap"></div></div></article>`).join('');
  for(const article of root.querySelectorAll('[data-mockup]')){
    const p=pages.find(x=>x.id===article.dataset.mockup);
    const launcher=article.querySelector('.mockup-launch'),live=article.querySelector('.mockup-live');
    article.querySelectorAll('[data-state]').forEach(button=>button.onclick=()=>{
      const state=p.states[Number(button.dataset.state)],image=article.querySelector('.mockup-image');
      image.src=state.image;image.alt=p.title+' — '+state.label;
      article.querySelector('.mockup-image-link').href=state.image;
      article.querySelectorAll('[data-state]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));
      article.querySelector('.mockup-state-caption').textContent=state.kind==='selected-reference'?'Referensi pilihan pengguna · 12 Oktober 2026':'Render preview HTML · '+state.label;
    });
    function close(){live.hidden=true;live.querySelector('.mockup-frame-wrap').replaceChildren();launcher.setAttribute('aria-expanded','false');launcher.textContent='Coba di halaman ini ↘';launcher.focus({preventScroll:true});}
    launcher.onclick=()=>{
      if(!live.hidden){close();return;}
      stopOtherPreviews(live);
      // Created only on an explicit click; audio never starts on page load.
      document.querySelectorAll('audio').forEach(a=>a.pause());
      const frame=document.createElement('iframe');frame.src=p.preview;frame.title='Preview interaktif '+p.title;frame.allow='fullscreen';frame.setAttribute('allowfullscreen','');
      live.querySelector('.mockup-frame-wrap').replaceChildren(frame);live.hidden=false;
      launcher.setAttribute('aria-expanded','true');launcher.textContent='Tutup preview ×';
      live.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'center'});
    };
    article.querySelector('.mockup-stop').onclick=close;
  }
  const planned=pages.filter(p=>p.status==='planned');
  const candidates=planned.filter(p=>p.candidate);
  document.getElementById('mockupPlannedCount').textContent=planned.length+' brief siap'+(candidates.length?' · '+candidates.length+' kandidat visual':'');
  document.getElementById('mockupPlans').innerHTML=planned.map((p,i)=>`<article class="mockup-plan${p.candidate?' mockup-plan-candidate':''}" data-plan="${esc(p.id)}"><span class="mockup-plan-number">${String(i+2).padStart(2,'0')}</span><div class="mockup-plan-content"><span class="mockup-plan-status">${p.candidate?esc(p.candidate.label):'Brief siap · gambar belum dibuat'}</span><h3>${esc(p.title)}</h3>${p.candidate?`<a class="mockup-candidate-image" href="${esc(p.candidate.image)}" target="_blank" rel="noopener" aria-label="Buka kandidat ${esc(p.title)} ukuran penuh"><img src="${esc(p.candidate.image)}" alt="${esc(p.title)} — kandidat full UI, belum dipilih pengguna" loading="lazy" decoding="async"></a><div class="mockup-state-buttons" role="group" aria-label="Keadaan dan riwayat kandidat ${esc(p.title)}">${(p.candidate.states||[]).map((s,n)=>`<button type="button" data-candidate-state="${n}" aria-pressed="${n===0}">${esc(s.label)}</button>`).join('')}</div><p class="candidate-caption" role="status">Kandidat belum dipilih · ${esc(p.candidate.states?.[0]?.label||'Full UI')}</p>`:''}<p>${esc(p.description)}</p><div class="mockup-plan-links"><a href="${esc(p.brief)}">Baca brief ↗</a><a href="${esc(p.prompt)}">Prompt desain ↗</a>${p.candidate?`<a href="${esc(p.candidate.brief)}">Paket kandidat ↗</a><a href="${esc(p.candidate.prompt)}">Prompt aktual ↗</a>${p.candidate.preview?`<a href="${esc(p.candidate.preview)}" target="_blank" rel="noopener">Buka preview interaktif ↗</a>`:''}`:''}</div>${p.candidate?.preview?`<button class="btn btn-gold candidate-launch" type="button" aria-expanded="false">Coba kandidat di halaman ini ↘</button><div class="candidate-live" hidden><div class="mockup-live-bar"><span>Preview kandidat · belum dipilih</span><button type="button" class="mockup-stop candidate-stop">Tutup preview ×</button></div><div class="mockup-frame-wrap"></div></div>`:''}</div></article>`).join('');
  document.querySelectorAll('.mockup-plan-candidate').forEach(article=>{
    const p=pages.find(p=>p.id===article.dataset.plan),c=p.candidate;
    article.querySelectorAll('[data-candidate-state]').forEach(button=>button.onclick=()=>{
      const s=c.states[+button.dataset.candidateState],img=article.querySelector('.mockup-candidate-image img');
      img.src=s.image;img.alt=p.title+' — '+s.label;article.querySelector('.mockup-candidate-image').href=s.image;
      article.querySelectorAll('[data-candidate-state]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));
      article.querySelector('.candidate-caption').textContent=(s.kind==='candidate-history'?'Riwayat kandidat · ':'Render preview kandidat · ')+s.label;
    });
    if(!c.preview)return;const launch=article.querySelector('.candidate-launch'),live=article.querySelector('.candidate-live');
    function close(){live.hidden=true;live.querySelector('.mockup-frame-wrap').replaceChildren();launch.setAttribute('aria-expanded','false');launch.textContent='Coba kandidat di halaman ini ↘';launch.focus({preventScroll:true})}
    article.querySelector('.candidate-stop').onclick=close;
    launch.onclick=()=>{if(!live.hidden){close();return}stopOtherPreviews(live);document.querySelectorAll('audio').forEach(a=>a.pause());const frame=document.createElement('iframe');frame.src=c.preview;frame.title='Preview kandidat '+p.title;frame.allow='fullscreen';frame.setAttribute('allowfullscreen','');live.querySelector('.mockup-frame-wrap').replaceChildren(frame);live.hidden=false;launch.setAttribute('aria-expanded','true');launch.textContent='Tutup kandidat ×';live.scrollIntoView({block:'center',behavior:'instant'})};
  });
  if(candidates.length)document.querySelector('.mockup-roadmap').open=true;
})();
