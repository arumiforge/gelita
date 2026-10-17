const $=s=>document.querySelector(s);
const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const isAudio=a=>a.size==='Audio';
const group=a=>isAudio(a)?'Audio':a.key.startsWith('reference.')?'Referensi utama':a.cat==='Karakter'?'Karakter':a.cat==='Background'?'Latar cerita':a.cat==='Peta'?'Peta':a.cat==='Tantangan'?'Tantangan':a.cat==='Pustaka'?'Pustaka budaya':['Logo','Antarmuka'].includes(a.cat)?'Logo & antarmuka':a.cat==='Lencana'?'Lencana':'Ikon';
const groups=['Referensi utama','Karakter','Latar cerita','Peta','Tantangan','Pustaka budaya','Logo & antarmuka','Lencana','Ikon','Audio'];
const paths={grid:'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',image:'M3 3h18v18H3zM3 17l6-6 5 5 3-3 4 4M8 7h.01',person:'M9 3h6v6H9zM5 21v-5a7 7 0 0 1 14 0v5',landscape:'m2 20 7-14 5 8 3-5 5 11H2ZM17 4h.01',map:'m3 5 6-2 6 2 6-2v16l-6 2-6-2-6 2V5ZM9 3v16M15 5v16',puzzle:'M4 4h6a2 2 0 1 1 4 0h6v6a2 2 0 1 0 0 4v6h-6a2 2 0 1 0-4 0H4v-6a2 2 0 1 1 0-4V4Z',book:'M3 4h7l2 2 2-2h7v16h-7l-2 2-2-2H3V4ZM12 6v16',layout:'M3 4h18v16H3zM3 9h18M9 9v11',medal:'m8 3 4 5 4-5M6 3h12M12 8a6 6 0 1 0 0 12 6 6 0 0 0 0-12ZM12 11l1 2 2 .3-1.5 1.5.4 2.2-1.9-1-1.9 1 .4-2.2L9 13.3l2-.3 1-2Z',spark:'m12 3 2.7 6.3L21 12l-6.3 2.7L12 21l-2.7-6.3L3 12l6.3-2.7L12 3Z',audio:'M4 14v-4M8 18V6M12 21V3M16 18V6M20 14v-4',arrow:'M5 12h14m-5-5 5 5-5 5',search:'M10.5 4a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM16 16l5 5'};
const icon=name=>`<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${paths[name]||paths.grid}"/></svg>`;
const groupIcons={'Semua visual':'grid','Referensi utama':'image','Karakter':'person','Latar cerita':'landscape','Peta':'map','Tantangan':'puzzle','Pustaka budaya':'book','Logo & antarmuka':'layout','Lencana':'medal','Ikon':'spark','Audio':'audio'};
const descriptions={'Referensi utama':'Kenali arah visual dan suasana dunia GELITA.','Karakter':'Temukan Jaka dan Mbah Kedu dalam berbagai pose.','Latar cerita':'Lanskap dan suasana untuk setiap bagian petualangan.','Peta':'Jelajahi gambaran wilayah dan perjalanan di Kedu.','Tantangan':'Bahan visual untuk kegiatan dan tantangan belajar.','Pustaka budaya':'Kenali tempat, benda, dan tradisi dalam cerita.','Logo & antarmuka':'Identitas dan elemen untuk menyusun halaman game.','Lencana':'Penanda pencapaian untuk setiap perjalanan.','Ikon':'Detail kecil untuk navigasi, bahasa, dan interaksi.','Audio':'Dengarkan 3 musik dan 6 efek suara tanpa narasi. 88 narasi masih berupa brief.'};
const tone=s=>s==='Sesuai gaya'?'ok':s==='Belum sesuai'?'bad':s==='Perlu tinjau'?'check':'neutral';
const collator=new Intl.Collator('id',{numeric:true,sensitivity:'base'});
let selected='Semua visual',visible=[],current=null,toastTimer;

$('#stats').innerHTML=[['Entri dalam koleksi',SUMMARY.total,'grid'],['Gambar & ilustrasi',SUMMARY.visual,'image'],['Frame karakter',SUMMARY.character_update.total_frames,'person'],['Audio · 9 dapat diputar',SUMMARY.audio,'audio']].map(([name,n,symbol])=>`<div class="col-6 col-sm-3"><div class="stat"><div class="stat-icon">${icon(symbol)}</div><div><strong>${n}</strong><span>${name}</span></div></div></div>`).join('');

function nav(){
  const options=['Semua visual',...groups];
  $('#navigation').innerHTML=options.map(g=>`<button class="nav-btn ${selected===g?'active':''}" type="button" aria-pressed="${selected===g}" data-group="${esc(g)}">${icon(groupIcons[g])}<span>${g==='Semua visual'?'Semua gambar':g==='Audio'?'Audio & narasi':esc(g)}</span><span class="nav-count">${DATA.filter(a=>g==='Semua visual'?!isAudio(a):group(a)===g).length}</span></button>`).join('');
  document.querySelectorAll('[data-group]').forEach(b=>b.onclick=()=>{selected=b.dataset.group;nav();render();$('#navigation .active').focus({preventScroll:true})});
}

function card(a){
  const audio=isAudio(a),status=a.style_review?.status||(a.file?'Audio tersedia':'Brief narasi');
  return `<div class="col-12 col-sm-6 col-xl-4 asset-col"><article class="card asset-card"><div class="thumb ${audio?'audio-thumb':['Background','Pustaka','Peta'].includes(a.cat)?'wide':''}">${!audio&&a.preview?`<img loading="lazy" decoding="async" src="${esc(a.preview)}" alt="${esc(a.title)}">`:icon('audio')+'<span>'+esc(a.file?'Pratinjau '+a.cat.toLowerCase():'Brief narasi')+'</span>'}<span class="region-tag">${esc(a.region||'Umum')}</span></div><div class="card-body"><h4>${esc(a.title)}</h4><div class="key">${esc(a.key)}</div><p class="asset-desc">${esc(a.desc||a.style_review?.reason)}</p><span class="tag ${tone(status)}">${esc(status)}</span>${audio&&a.file?audioPlayer(a):''}<div class="card-foot"><span>${esc(audio?(a.file?a.duration:'Arahan narasi'):a.actual?a.actual+' px':a.format)}</span><button class="open" type="button" data-key="${esc(a.key)}" aria-label="Lihat detail ${esc(a.title)}">Lihat detail ${icon('arrow')}</button></div></div></article></div>`;
}

function render(){
  const q=$('#search').value.toLowerCase().trim(),s=$('#style').value,r=$('#region').value,sort=$('#sort').value;
  visible=DATA.filter(a=>(selected==='Semua visual'?!isAudio(a):group(a)===selected)&&(!s||a.style_review?.status===s)&&(!isAudio(a)||!$('#audioKind').value||($('#audioKind').value==='available'?!!a.file:a.cat===$('#audioKind').value))&&(!r||a.region===r)&&(!q||[a.title,a.key,a.desc,a.note,a.style_review?.reason].join(' ').toLowerCase().includes(q)));
  if(sort!=='catalog')visible.sort((a,b)=>collator.compare(a[sort],b[sort]));
  $('#collectionTitle').textContent=selected==='Semua visual'?'Jelajahi semua gambar':selected==='Audio'?'Audio & narasi':selected;
  $('#collectionDescription').textContent=descriptions[selected]||'Pilih koleksi atau gunakan pencarian untuk mulai.';
  $('#count').innerHTML=`<strong>${visible.length} ${selected==='Audio'?'audio / brief':'aset'}</strong> ditemukan <span aria-hidden="true">·</span> ${SUMMARY.visual} gambar &amp; ${SUMMARY.audio} audio / brief dalam koleksi`;
  $('#audioFilter').hidden=selected!=='Audio';
  document.querySelectorAll('#collections audio').forEach(el=>el.pause());
  $('#collections').innerHTML=visible.length?groups.map(g=>{const list=visible.filter(a=>group(a)===g);return list.length?`<section class="collection" aria-label="${esc(g)}"><div class="collection-head"><h3>${g==='Audio'?'Audio & narasi':esc(g)}</h3><span>${list.length}</span></div><div class="row g-3 asset-grid">${list.map(card).join('')}</div></section>`:''}).join(''):`<div class="empty">${icon('search')}<h3>Belum menemukan yang Anda cari?</h3><p>Coba kata yang lebih singkat atau pilih wilayah lain.<br>Anda juga bisa menampilkan kembali seluruh koleksi.</p><button class="btn btn-primary" type="button" id="emptyReset">Tampilkan semua gambar</button></div>`;
  document.querySelectorAll('[data-key]').forEach(b=>b.onclick=()=>openAsset(b.dataset.key));
  if($('#emptyReset'))$('#emptyReset').onclick=reset;
}

function openAsset(key){
  current=DATA.find(a=>a.key===key);const a=current,audio=isAudio(a);
  $('#dialogKey').textContent=a.key;
  $('#detail').innerHTML=`<div class="row g-4"><div class="col-lg-6"><h2 id="detailTitle">${esc(a.title)}</h2><span class="tag ${tone(a.style_review?.status)}">${esc(a.style_review?.status||(a.file?'Audio tersedia':'Brief narasi'))}</span><p class="detail-lead">${esc(a.desc)}</p><div class="detail-preview">${audio&&a.file?audioPlayer(a):!audio&&a.preview?`<img src="${esc(a.preview)}" alt="${esc(a.title)}">`:`<div class="audio-thumb d-flex align-items-center justify-content-center flex-column">${icon('audio')}<span>Arahan audio · Rekaman belum tersedia</span></div>`}</div><div class="detail-actions">${a.file&&audio?`<a class="btn btn-primary" href="${esc(a.file)}" download>Unduh audio MP3</a><a class="btn btn-light" href="${esc(a.audio_provenance.master)}" download>Master WAV</a>`:''}${a.file&&!audio?`<a class="btn btn-primary" href="${esc(a.file)}" ${location.protocol==='file:'?'target="_blank" rel="noopener"':'download'}>${location.protocol==='file:'?'Buka gambar asli ↗':'Unduh gambar'}</a>`:''}${a.preview&&a.preview!==a.file?`<a class="btn btn-light" href="${esc(a.preview)}" target="_blank" rel="noopener">Buka pratinjau ↗</a>`:''}</div><dl class="metadata"><div><dt>${audio?'Spesifikasi audio':'Ukuran gambar'}</dt><dd>${esc(audio?(a.file?a.actual:a.cat):a.actual||'Belum tercatat')}</dd></div><div><dt>${audio?'Durasi':'Ukuran yang dibutuhkan'}</dt><dd>${esc(audio?a.duration:a.size)}</dd></div><div><dt>Format</dt><dd>${esc(a.actual_format||a.format)}</dd></div>${!audio?`<div><dt>Latar transparan</dt><dd>${a.actual_alpha===true?'Ya':a.actual_alpha===false?'Tidak':'Belum diperiksa / vektor'}</dd></div>`:''}<div class="file-row"><dt>Lokasi berkas</dt><dd>${esc(a.file||'Belum tersedia')}</dd></div></dl><details class="review-notes"><summary>Catatan penggunaan &amp; pemeriksaan</summary>${a.style_review?`<p>${esc(a.style_review.reason)}</p><p><strong>Pemeriksaan isi:</strong> ${esc(a.content_review)}</p>`:''}${a.note?`<p>${esc(a.note)}</p>`:''}${a.production?`<p>${esc(a.production)}</p>`:''}${a.provenance?.kind==='Generated missing reference'?`<p>Referensi ini dibuat dengan image_gen pada ${esc(a.provenance.date)} sebagai gambar baru, bukan pemulihan berkas asli.</p>`:''}</details></div><div class="col-lg-6"><section class="prompt-panel"><h3>${audio?'Arahan audio':'Prompt gambar · English'}</h3><p>${audio?'Gunakan brief ini sebagai panduan saat menyiapkan audio.':'Salin prompt ini untuk menyiapkan gambar sesuai arah visual GELITA.'}</p><div class="detail-actions"><button class="btn btn-primary" type="button" id="copyPrompt">${audio?'Salin brief':'Salin prompt'}</button><button class="btn btn-light" type="button" id="downloadPrompt">Simpan sebagai .txt</button></div><div class="prompt" tabindex="0" aria-label="${audio?'Teks brief audio':'Teks prompt gambar'}">${esc(a.prompt)}</div>${a.negative?`<details class="negative-notes"><summary>Hal yang perlu dihindari</summary><p>${esc(a.negative)}</p></details>`:''}</section></div></div>`;
  $('#copyPrompt').onclick=()=>copy(a.prompt,audio?'Brief berhasil disalin.':'Prompt berhasil disalin.');
  $('#downloadPrompt').onclick=()=>download(a.key+'.txt',a.prompt,'text/plain');
  document.querySelectorAll('#collections audio').forEach(el=>el.pause());
  $('#dialog').showModal();$('#dialog').scrollTop=0;
}

function download(name,text,type){const u=URL.createObjectURL(new Blob([text],{type:type+';charset=utf-8'})),a=document.createElement('a');a.href=u;a.download=name;a.click();setTimeout(()=>URL.revokeObjectURL(u),1000);toast('Berkas siap disimpan. Periksa unduhan Anda.')}
function toast(t){clearTimeout(toastTimer);const el=$('#toast');($('#dialog').open?$('#dialog'):document.body).append(el);el.textContent=t;el.style.display='block';toastTimer=setTimeout(()=>{el.style.display='none'},3200)}
async function copy(t,message='Semua prompt berhasil disalin.'){
  try{await navigator.clipboard.writeText(t);toast(message)}
  catch{const el=document.createElement('textarea');el.value=t;el.style.position='fixed';el.style.opacity='0';($('#dialog').open?$('#dialog'):document.body).append(el);el.select();const ok=document.execCommand('copy');el.remove();toast(ok?message:'Pilih teks yang Anda perlukan, lalu salin secara manual.')}
}
function reset(){$('#audioKind').value='';$('#search').value='';$('#style').value='';$('#region').value='';$('#sort').value='catalog';selected='Semua visual';nav();render()}

$('#close').onclick=()=>$('#dialog').close();
$('#dialog').addEventListener('close',()=>{document.querySelectorAll('#dialog audio').forEach(el=>el.pause());$('#toast').style.display='none';document.body.append($('#toast'))});
$('#dialog').addEventListener('click',e=>{if(e.target===$('#dialog')){const b=e.target.getBoundingClientRect();if(e.clientX<b.left||e.clientX>b.right||e.clientY<b.top||e.clientY>b.bottom)e.target.close()}});
$('#audioKind').onchange=render;$('#search').oninput=render;$('#style').onchange=render;$('#region').onchange=render;$('#sort').onchange=render;$('#reset').onclick=reset;
$('#jsonExport').onclick=()=>download('gelita-hasil.json',JSON.stringify(visible,null,2),'application/json');
$('#csvExport').onclick=()=>{const rows=[['kode','nama','kategori','status_gaya','berkas','aktual','target'],...visible.map(a=>[a.key,a.title,a.cat,a.style_review?.status||(a.file?'Audio tersedia':'Brief narasi'),a.file,a.actual,a.size])];download('gelita-hasil.csv','\uFEFF'+rows.map(row=>row.map(v=>'"'+String(v??'').replaceAll('"','""')+'"').join(',')).join('\r\n'),'text/csv')};
$('#copyGroup').onclick=()=>copy(visible.map(a=>a.key+'\n'+a.prompt).join('\n\n---\n\n'));
$('#updatesLink').onclick=()=>bootstrap.Collapse.getOrCreateInstance($('#updatesContent'),{toggle:false}).show();
nav();render();

function audioPlayer(a){return `<div class="audio-player"><audio controls preload="none" aria-label="Dengarkan ${esc(a.title)}" ${a.audio_provenance?.loop?'loop':''}><source src="${esc(a.file)}" type="audio/mpeg">Browser tidak mendukung audio. <a href="${esc(a.file)}">Unduh MP3</a></audio><small>${a.audio_provenance?.loop?'Loop instrumental':'Efek suara'} · ${esc(a.duration)} · sintesis lokal tanpa vokal</small></div>`;}
// Keep listening focused on one sound. Closing a dialog or rerendering stops playback.
document.addEventListener('play',event=>{if(event.target.tagName==='AUDIO')document.querySelectorAll('audio').forEach(a=>{if(a!==event.target)a.pause()})},true);

$('#languageIconsLink').onclick=()=>{reset();$('#search').value='Bahasa';render();};
