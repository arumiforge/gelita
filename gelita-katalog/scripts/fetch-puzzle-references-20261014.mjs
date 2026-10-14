import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
const destination=path.resolve('provenance/puzzle-generation-20261014/factual-references');
fs.mkdirSync(destination,{recursive:true});
const files=[['pringapus-front','Candi Pringapus - 001 Reconstructed Temple (12130538935).jpg'],['pringapus','Candi Pringapus - 003 West Side (12130801593).jpg'],['liyangan','Revitalisasi Situs Liyangan.jpg'],['gondosuli','Prasasti Gondosuli di Temanggung.jpg'],['relief','Relief yang ada di candi Borobudur Magelang Jawa Tengah Indonesia.jpg'],['pawon','042 Candi Pawon.jpg'],['arjuna','Komplek Candi Arjuna Dieng.jpg'],['bundengan','Bundengan.jpg'],['lengger','Topeng lengger.jpg']];
const records=[];
const previous=fs.existsSync(path.join(destination,'sources.json'))?JSON.parse(fs.readFileSync(path.join(destination,'sources.json'),'utf8')):[];
files.push(['borobudur-stupas','STUPA CANDI BOROBUDUR.jpg'],['borobudur-buddha','Borobudur-Temple-Park Indonesia Stupas-of-Borobudur-05.jpg'],['nandi','Candi Pringapus - 013 Nandi from Front (12130853684).jpg']);
for(const [name,title] of files){
 const saved=previous.find(r=>r.name===name);
 if(saved&&fs.existsSync(path.join(destination,name+'.jpg'))){records.push(saved);continue;}
 const url=new URL('https://commons.wikimedia.org/w/api.php');
 url.search=new URLSearchParams({action:'query',format:'json',prop:'imageinfo',iiprop:'url|extmetadata',titles:'File:'+title}).toString();
 const response=await fetch(url);if(!response.ok)throw Error(response.status);
 const json=await response.json();const info=Object.values(json.query.pages)[0].imageinfo?.[0];if(!info)throw Error('Missing reference '+title);
 let bytes;
 if(fs.existsSync(path.join(destination,name+'.jpg')))bytes=fs.readFileSync(path.join(destination,name+'.jpg'));
 else {const imageResponse=await fetch(info.url);if(!imageResponse.ok)throw Error(imageResponse.status+' '+title);bytes=Buffer.from(await imageResponse.arrayBuffer());}
 fs.writeFileSync(path.join(destination,name+'.jpg'),bytes);
 const meta=Object.fromEntries(Object.entries(info.extmetadata).map(([k,v])=>[k,v.value]));
 records.push({name,title,url:info.descriptionurl,download_url:info.url,file:'provenance/puzzle-generation-20261014/factual-references/'+name+'.jpg',sha256:crypto.createHash('sha256').update(bytes).digest('hex'),metadata:meta,role:'Factual form reference only; source image is not an active game asset'});
 fs.writeFileSync(path.join(destination,'sources.json'),JSON.stringify(records,null,2)+'\n');
 console.log(name+' '+meta.LicenseShortName);
}
fs.writeFileSync(path.join(destination,'sources.json'),JSON.stringify(records,null,2)+'\n');
