(() => {
  'use strict';
  const $=id=>document.getElementById(id), game=$('game');
  const regions={tmg:{name:'Temanggung',level:1},mgl:{name:'Magelang',level:2},wnb:{name:'Wonosobo',level:3}};
  const text={
    id:{shards:'Serpihan cahaya',selected:'WILAYAH DIPILIH',tapMap:'Ketuk wilayah untuk menjelajah',kenali:'Kenali',continue:'Lanjut',demo:'Contoh keadaan peta',menu:'Menu',library:'Pustaka Kedu',profile:'Profil',home:'Peta Kedu',fullscreen:'Layar penuh',scenario:'Lihat contoh progres 9/12',logout:'Keluar',begin:'Lihat contoh awal perjalanan',difficulty:['Mudah','Sedang','Sulit'],available:'Tersedia',locked:'Terkunci',complete:'Tuntas',progress:'Sedang dijelajahi',hint:'Empat tantangan menanti di wilayah ini.',lockedHint:'Wilayah ini belum terbuka pada contoh tampilan.',prev:'Bagian sebelumnya',next:'Bagian berikutnya',play:'Putar cerita',pause:'Jeda cerita',parts:'Bagian',of:'dari',go:'Wilayah dipilih: ',titles:['Dataran Kedu','Tiga Wilayah','Kenali Dulu'],
      story:['Inilah Dataran Kedu. Dulu dataran ini menyimpan cahaya ilmu dan budaya. Sekarang lihatlah... Kabut Lupa menutupi hampir semuanya.','Aku harus menjelajahi tiga wilayah untuk memulihkannya: Temanggung, lalu Magelang, dan terakhir Wonosobo. Di setiap wilayah, empat Serpihan Cahaya sedang menunggu.','Ketuk tombol Kenali untuk membaca cerita Mbah Kedu tentang wilayah itu. Kalau sudah siap, ketuk wilayah yang sudah terbuka. Ayo, kita berangkat!'],
      kenaliText:{tmg:'Inilah Temanggung, Le. Tanahnya diapit dua gunung, Sindoro dan Sumbing. Kalau pagi cerah, kedua puncaknya tampak berdiri berdampingan, seperti kakak dan adik yang saling menjaga.',mgl:'Magelang, Le, adalah rumah Candi Borobudur, candi Buddha terbesar di dunia. Candi itu dibangun lebih dari seribu tahun lalu, batu demi batu, tanpa mesin modern.',wnb:'Wonosobo adalah pintu menuju Dataran Tinggi Dieng, yang dijuluki negeri di atas awan. Letaknya sangat tinggi, sampai-sampai awan dan kabut sering lewat di antara rumah penduduk.'}},
    en:{shards:'Light shards',selected:'SELECTED REGION',tapMap:'Tap a region to explore',kenali:'Discover',continue:'Continue',demo:'Example map state',menu:'Menu',library:'Kedu Library',profile:'Profile',home:'Kedu Map',fullscreen:'Fullscreen',scenario:'View example progress 9/12',logout:'Sign out',begin:'View example first journey',difficulty:['Easy','Medium','Hard'],available:'Available',locked:'Locked',complete:'Complete',progress:'Exploring',hint:'Four challenges await in this region.',lockedHint:'This region is locked in this example.',prev:'Previous part',next:'Next part',play:'Play story',pause:'Pause story',parts:'Part',of:'of',go:'Selected region: ',titles:['The Kedu Plain','Three Regions','Discover First'],
      story:['This is the Kedu Plain. This plain once held the light of knowledge and culture. Now look... the Mist of Forgetting covers almost all of it.','I must explore three regions to bring it back: Temanggung, then Magelang, and finally Wonosobo. In each region, four Shards of Light are waiting.','Tap Discover to read Mbah Kedu’s story about the region. When you are ready, tap an unlocked region. Come on, let’s go!'],
      kenaliText:{tmg:'This is Temanggung, my boy. Its land lies between two mountains, Sindoro and Sumbing. On a clear morning, their peaks stand side by side, like an older and a younger sibling looking after each other.',mgl:'Magelang, my boy, is home to Borobudur Temple, the largest Buddhist temple in the world. It was built more than a thousand years ago, stone by stone, without modern machines.',wnb:'Wonosobo is the gateway to the Dieng Plateau, known as the land above the clouds. It is so high up that clouds and mist often drift between people’s houses.'}}
  };
  const music=new Audio('../../../../assets/audio/music/map.mp3');music.loop=true;music.volume=.18;music.preload='none';
  const click=new Audio('../../../../assets/audio/sfx/click.mp3');click.volume=.35;click.preload='none';
  const lock=new Audio('../../../../assets/audio/sfx/lock.mp3');lock.volume=.4;lock.preload='none';
  const audio=[music,click,lock], motion=matchMedia('(prefers-reduced-motion: reduce)');
  let language='id',scenario='begin',selected='tmg',part=0,chars=0,playing=true,timer=null,toastTimer=null,active=null,returnFocus=null,muted=true;
  const sheets={menu:$('menu-sheet'),language:$('language-sheet'),kenali:$('kenali-sheet')};
  const trigger={menu:$('menu-trigger'),language:$('language-trigger'),kenali:$('kenali-trigger')};
  const t=()=>text[language];
  const progress=key=>scenario==='begin'?0:key==='wnb'?1:4;
  const opened=key=>scenario==='progress'||key==='tmg';
  function scale(){const s=Math.min(innerWidth/1920,innerHeight/1080);game.style.transform='scale('+s+')';game.style.left=(innerWidth-1920*s)/2+'px';game.style.top=(innerHeight-1080*s)/2+'px';game.dataset.scale=String(s)}
  addEventListener('resize',scale);scale();
  function stopTyping(){clearInterval(timer);timer=null}
  function updateStoryControls(){
    $('previous-trigger').disabled=part===0;$('next-trigger').disabled=part===2;
    $('previous-trigger').ariaLabel=t().prev;$('next-trigger').ariaLabel=t().next;
    $('play-trigger').ariaLabel=playing?t().pause:t().play;$('play-trigger').setAttribute('aria-pressed',String(playing));
    $('play-trigger').firstElementChild.src='../../../../assets/ikon/icon.'+(playing?'pause':'play')+'.svg';
    [...$('dots').children].forEach((dot,i)=>dot.classList.toggle('on',i===part));
    $('dots').ariaLabel=t().parts+' '+(part+1)+' '+t().of+' 3';$('story-title').textContent=t().titles[part];
    game.classList.toggle('typing',playing&&chars<t().story[part].length);
  }
  function tick(){
    if(document.hidden||active)return;
    const value=t().story[part];chars=Math.min(value.length,chars+2);$('story-text').textContent=value.slice(0,chars);
    if(chars===value.length){stopTyping();playing=false;$('story-announcement').textContent=value;updateStoryControls()}
  }
  function startTyping(){
    stopTyping();
    if(motion.matches){chars=t().story[part].length;$('story-text').textContent=t().story[part];playing=false}
    else if(playing&&chars<t().story[part].length)timer=setInterval(tick,34);
    updateStoryControls();
  }
  function showPart(n){part=Math.max(0,Math.min(2,n));chars=0;playing=true;$('story-text').textContent='';$('story-announcement').textContent='';startTyping()}
  function finishTyping(){stopTyping();chars=t().story[part].length;$('story-text').textContent=t().story[part];playing=false;updateStoryControls()}
  $('previous-trigger').onclick=()=>showPart(part-1);$('next-trigger').onclick=()=>showPart(part+1);
  $('play-trigger').onclick=()=>{if(chars===t().story[part].length)chars=0;playing=!playing;startTyping()};
  motion.addEventListener('change',()=>{if(motion.matches)finishTyping()});
  function announce(value){clearTimeout(toastTimer);$('toast').textContent=value;$('toast').hidden=false;toastTimer=setTimeout(()=>$('toast').hidden=true,2200)}
  function play(sound){sound.play().catch(()=>announce(language==='id'?'Suara belum dapat diputar.':'Sound is unavailable.'))}
  function select(key){
    selected=key;
    for(const [code,region] of Object.entries(regions)){
      const button=$('pin-'+code),isOpen=opened(code),p=progress(code);
      button.classList.toggle('locked',!isOpen);button.classList.toggle('selected',code===key);button.setAttribute('aria-pressed',String(code===key));
      button.querySelector('small').textContent=!isOpen?t().locked:p===4?t().complete+' · 4/4':p+' / 4';
      button.ariaLabel=region.name+', '+(!isOpen?t().locked:p===4?t().complete:t().available);
      document.querySelector('[data-slice="'+code+'"]').classList.toggle('open',isOpen);
    }
    $('route-mgl').classList.toggle('locked',!opened('mgl'));$('route-wnb').classList.toggle('locked',!opened('wnb'));
    const p=progress(key),isOpen=opened(key);
    $('region-title').textContent=regions[key].name;$('region-level').textContent=(language==='id'?'Wilayah ':'Region ')+regions[key].level+' · '+t().difficulty[regions[key].level-1];
    $('region-status').textContent=!isOpen?t().locked:p===4?t().complete:p===0?t().available:t().progress;$('region-status').classList.toggle('complete',p===4);
    $('region-fill').style.width=p*25+'%';$('region-count').textContent=p+' / 4';
    $('rating').ariaLabel=(scenario==='begin'?'0':'3')+(language==='id'?' dari 3 bintang contoh':' of 3 example stars');
    $('rating').querySelectorAll('img').forEach(star=>star.classList.toggle('empty',scenario==='begin'));
    $('region-hint').textContent=isOpen?'':t().lockedHint;$('region-panel').classList.toggle('is-locked',!isOpen);$('region-next').disabled=!isOpen;$('kenali-trigger').disabled=!isOpen;
    $('shard-count').innerHTML=(scenario==='begin'?'0':'9')+' <small>/ 12</small>';$('shard-fill').style.width=scenario==='begin'?'0':'75%';
    $('scenario-trigger').querySelector('span:last-child').textContent=scenario==='begin'?t().scenario:t().begin;
  }
  document.querySelectorAll('[data-region]').forEach(b=>b.onclick=()=>{select(b.dataset.region);if(!opened(selected)&&!muted){lock.currentTime=0;play(lock)}});
  function setScenario(value){scenario=value==='progress'?'progress':'begin';select(scenario==='progress'?'wnb':'tmg')}
  function close(){
    if(!active)return;active=null;Object.values(sheets).forEach(el=>el.hidden=true);$('scrim').hidden=true;
    document.querySelectorAll('.surface').forEach(el=>el.inert=false);Object.values(trigger).forEach(el=>el.setAttribute('aria-expanded','false'));
    if(returnFocus&&returnFocus.isConnected&&!returnFocus.disabled)returnFocus.focus();returnFocus=null;
  }
  function open(name){
    const from=trigger[name]||document.activeElement;close();returnFocus=from;active=name;
    document.querySelectorAll('.surface').forEach(el=>el.inert=true);$('scrim').hidden=false;sheets[name].hidden=false;
    if(trigger[name])trigger[name].setAttribute('aria-expanded','true');sheets[name].querySelector('button').focus();
  }
  $('menu-trigger').onclick=()=>open('menu');$('language-trigger').onclick=()=>open('language');
  document.querySelectorAll('[data-close]').forEach(b=>b.onclick=close);$('scrim').onclick=close;
  document.addEventListener('keydown',event=>{
    if(event.key==='Escape'&&active){event.preventDefault();close()}
    if(event.key==='Tab'&&active){
      const buttons=[...sheets[active].querySelectorAll('button:not(:disabled)')],first=buttons[0],last=buttons[buttons.length-1];
      if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus()}
      else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus()}
    }
  });
  function setLanguage(value){
    language=value==='en'?'en':'id';document.documentElement.lang=language;
    document.querySelectorAll('[data-text]').forEach(el=>el.textContent=t()[el.dataset.text]);
    $('flag').src='../../../../assets/ikon/icon.language-'+language+'.webp';$('flag').alt=language==='id'?'Bahasa Indonesia':'English';
    document.querySelectorAll('[data-language]').forEach(el=>el.setAttribute('aria-pressed',String(el.dataset.language===language)));
    select(selected);showPart(part);
  }
  document.querySelectorAll('[data-language]').forEach(b=>b.onclick=()=>{setLanguage(b.dataset.language);close()});
  $('kenali-trigger').onclick=()=>{$('kenali-title').textContent=t().kenali+' '+regions[selected].name;$('kenali-text').textContent=t().kenaliText[selected];open('kenali')};
  $('region-next').onclick=()=>announce(t().go+regions[selected].name);
  $('home-trigger').onclick=()=>{close();select('tmg')};
  $('scenario-trigger').onclick=()=>{setScenario(scenario==='begin'?'progress':'begin');close()};
  $('fullscreen-trigger').onclick=()=>{close();if(document.fullscreenElement)document.exitFullscreen?.().catch(()=>{});else document.documentElement.requestFullscreen?.().catch(()=>announce(language==='id'?'Layar penuh belum tersedia.':'Fullscreen is unavailable.'))};
  document.querySelectorAll('[data-demo]').forEach(b=>b.onclick=()=>{const label=t()[b.dataset.demo];close();announce(label)});
  $('sound-trigger').onclick=()=>{
    muted=!muted;$('sound-trigger').setAttribute('aria-pressed',String(!muted));
    $('sound-trigger').ariaLabel=muted?(language==='id'?'Aktifkan suara':'Enable sound'):(language==='id'?'Matikan suara':'Mute sound');
    $('sound-icon').src='../../../../assets/ikon/icon.'+(muted?'sound-off':'sound')+'.svg';if(muted)audio.forEach(a=>a.pause());else play(music);
  };
  game.addEventListener('click',event=>{if(!muted&&event.target.closest('button')?.id!=='sound-trigger'){click.currentTime=0;play(click)}},true);
  function shutdown(){stopTyping();audio.forEach(a=>a.pause())}
  addEventListener('pagehide',shutdown);
  document.addEventListener('visibilitychange',()=>{if(document.hidden){audio.forEach(a=>a.pause());stopTyping()}else{startTyping();if(!muted)play(music)}});
  window.gelitaMapPreview={get state(){return {language,scenario,selected,part,chars,playing,muted,active}},audio,setScenario,select,showPart,finishTyping,open,close,shutdown};
  select(selected);showPart(0);
})();
