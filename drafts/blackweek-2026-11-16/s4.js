const L=require('./lib.js');const fs=require('fs');const bw=fs.readFileSync('bw.js','utf8');
(async()=>{const {b,c}=await L.open(2);const p=await c.newPage();p.on('pageerror',e=>console.log('ERR',e.message));
for(const ph of ['bw','bf']){await L.go(p,'https://peptrium.com/?bw='+ph);await p.evaluate(x=>{window.BWP=x;window.BWNOW=x==='bf'?'2026-11-27T12:47:08+01:00':'2026-11-17T09:12:33+01:00';},ph);await p.addScriptTag({content:bw});await p.waitForTimeout(700);await p.screenshot({path:'01-start-'+ph+'.png'});}
await b.close();})();
