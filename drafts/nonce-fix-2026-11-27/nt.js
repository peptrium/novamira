const {chromium,devices}=require('playwright');const fs=require('fs');
(async()=>{const u=new URL(process.env.https_proxy);
const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const ctx=await b.newContext({...devices['iPhone 13']});
await ctx.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'}]);
await ctx.addInitScript(fs.readFileSync('nf.js','utf8'));
const p=await ctx.newPage();
await p.goto('https://peptrium.com/zubehoer/?x=2',{waitUntil:'domcontentloaded',timeout:60000});await p.waitForTimeout(3000);
const r=await p.evaluate(async()=>{
 const g=await fetch('/wp-json/wc/store/v1/cart',{credentials:'include'});const good=g.headers.get('Nonce');
 // 1) einzelner Request mit abgelaufener Nonce
 const a=await fetch('/wp-json/wc/store/v1/cart/add-item',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','Nonce':'0bad0bad00'},body:JSON.stringify({id:74,quantity:1})});
 // 2) Batch mit alter Nonce im Body
 const bt=await fetch('/wp-json/wc/store/v1/batch',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','Nonce':'1bad1bad11'},body:JSON.stringify({requests:[{path:'/wc/store/v1/cart/add-item',method:'POST',body:{id:74,quantity:1},headers:{Nonce:'1bad1bad11'}}]})});
 const bj=await bt.json();
 // 3) danach nochmal mit derselben alten Nonce -> wird vorab ersetzt
 const c=await fetch('/wp-json/wc/store/v1/cart/update-customer',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','Nonce':'0bad0bad00'},body:JSON.stringify({})});
 const cart=await (await fetch('/wp-json/wc/store/v1/cart',{credentials:'include'})).json();
 return {single:a.status,batch:bt.status,batchInner:(bj.responses||[]).map(x=>x.status),again:c.status,qty:cart.items.map(i=>i.id+'x'+i.quantity)};
});
console.log(JSON.stringify(r));await b.close();})();
