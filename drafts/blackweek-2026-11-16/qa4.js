const {chromium}=require('playwright');const TK='zc96rgpowe';
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const c=await b.newContext({viewport:{width:390,height:844},isMobile:true,hasTouch:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'});
await c.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'}]);
const p=await c.newPage();const errs=[];p.on('pageerror',e=>errs.push(e.message));
await p.goto('https://peptrium.com/?sp_bw=vorschau&k='+TK,{waitUntil:'load',timeout:90000});await p.waitForTimeout(3000);
console.log(await p.evaluate(()=>({hero:!!document.querySelector('.bwh'),nlp:(document.querySelector('#sp-nlp .st[data-s="1"] h2')||{}).textContent,nlh:(document.querySelector('#sp-nlh h2')||{}).textContent,cards:document.querySelectorAll('.sp-pc .bw-pr').length})));
for(const path of ['/alle-produkte/','/produkt/retatrutide/','/produkt/peptrium-pen-retatrutide/','/kasse/']){errs.length=0;await p.goto('https://peptrium.com'+path,{waitUntil:'load',timeout:90000});await p.waitForTimeout(2500);console.log(path,await p.evaluate(()=>document.documentElement.scrollWidth),errs.filter(e=>!/Failed to fetch/.test(e)));}
await p.goto('https://peptrium.com/?sp_bw=aus',{waitUntil:'load'});await b.close();})();
