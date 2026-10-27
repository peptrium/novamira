const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2});
await p.goto('file://'+__dirname+'/vorschau.html');await p.addStyleTag({content:'nav{display:none}details>p{display:block}'});
await p.evaluate(()=>document.querySelectorAll('details').forEach(d=>d.open=true));
const arts=await p.$$('article');let i=8;for(const a of arts){await a.screenshot({path:__dirname+'/entwurf-artikel-'+(i++)+'.png'});}
await b.close();})();
