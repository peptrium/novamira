const L=require('./lib.js');const fs=require('fs');const bw=fs.readFileSync('bw.js','utf8');
(async()=>{const {b,c}=await L.open(2);const p=await c.newPage();p.on('pageerror',e=>console.log('ERR',e.message));
for(const ph of ['bw','bf']){
 await L.go(p,'https://peptrium.com/?bw=1');await p.evaluate(x=>{window.BWP=x;window.BWNOW='2026-11-17T09:12:33+01:00';if(x==='bf')window.BWNOW='2026-11-27T12:47:08+01:00';},ph);await p.addScriptTag({content:bw});await p.waitForTimeout(600);
 await p.screenshot({path:'01-start-'+ph+'.png'});
}
await L.go(p,'https://peptrium.com/alle-produkte/?bw=1');await p.evaluate(()=>{window.BWP='bw';window.BWNOW='2026-11-17T09:12:33+01:00';});await p.addScriptTag({content:bw});await p.waitForTimeout(600);
const y=await p.evaluate(()=>{const s=document.querySelector('.bw-strip')||document.querySelector('.sp-pc');return s?s.getBoundingClientRect().top+scrollY-130:400});await p.evaluate(y=>scrollTo(0,y),y);await p.waitForTimeout(800);
await p.screenshot({path:'03-karten.png'});
await L.go(p,'https://peptrium.com/produkt/retatrutide/?bw=1');await p.evaluate(()=>{window.BWP='bw';window.BWNOW='2026-11-17T09:12:33+01:00';});await p.addScriptTag({content:bw});await p.waitForTimeout(600);
const y2=await p.evaluate(()=>document.getElementById('rx-buybox').getBoundingClientRect().top+scrollY-140);await p.evaluate(y=>scrollTo(0,y),y2);await p.waitForTimeout(800);
await p.screenshot({path:'04-kaufbox.png'});
await b.close();})();
