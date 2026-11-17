const {chromium}=require('playwright');const TK='zc96rgpowe';
const urls=['/','/alle-produkte/','/produktkategorie/zubehoer/','/produkt/retatrutide/','/produkt/ghk-cu/','/produkt/bac-water/','/produkt/peptrium-pen-retatrutide/','/produkt/cagrilintid/','/produkt/tb-500/','/produkt/glow/','/alle-peptrium-pens/','/abo-modell/','/peptid-rechner/','/ratgeber/','/mein-konto/','/kasse/'];
async function ctx(b,mob){const u=new URL(process.env.https_proxy);
 const c=await b.newContext(mob?{viewport:{width:390,height:844},isMobile:true,hasTouch:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'}:{viewport:{width:1440,height:900}});
 await c.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'},{name:'sp_nlp_closed',value:'1',domain:'peptrium.com',path:'/'}]);return c;}
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
for(const ph of ['bw','bf'])for(const mob of [true,false]){const c=await ctx(b,mob);const p=await c.newPage();const errs=[];p.on('pageerror',e=>errs.push(e.message));
 await p.goto('https://peptrium.com/?sp_bw=vorschau&k='+TK+(ph==='bf'?'&p=bf':''),{waitUntil:'load',timeout:90000});
 for(const path of urls){errs.length=0;let st=0;try{const r=await p.goto('https://peptrium.com'+path,{waitUntil:'load',timeout:90000});st=r.status();}catch(e){console.log(path,'LOADERR');continue;}
  await p.waitForTimeout(2500);
  const info=await p.evaluate(()=>({ov:document.documentElement.scrollWidth-innerWidth,js:!!document.getElementById('sp-bwv-js'),tb:!!document.querySelector('.bw-tb'),hero:!!document.querySelector('.bwh'),cards:document.querySelectorAll('.sp-pc .pr.bw-pr').length,cardsAll:document.querySelectorAll('.sp-pc[data-id]').length,bb:!!document.querySelector('.bw-bb'),price:(document.getElementById('rx-price')||{}).textContent||'',strip:!!document.querySelector('.bw-strip'),nan:/NaN|undefined €|Infinity/.test(document.body.innerText)}));
  console.log(ph,mob?'M':'D',st,path,JSON.stringify(info),errs.length?'ERR:'+errs.slice(0,2).join(' | '):'');}
 await c.close();}
await b.close();})();
