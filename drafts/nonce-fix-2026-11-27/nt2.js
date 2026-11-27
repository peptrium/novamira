const {chromium,devices}=require('playwright');const fs=require('fs');
async function run(withFix){const u=new URL(process.env.https_proxy);
const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const ctx=await b.newContext({...devices['iPhone 13']});
await ctx.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'}]);
if(withFix)await ctx.addInitScript(fs.readFileSync('nf.js','utf8'));
const p=await ctx.newPage();
await p.goto('https://peptrium.com/zubehoer/?x=3',{waitUntil:'domcontentloaded',timeout:60000});await p.waitForTimeout(2500);
await p.evaluate(async()=>{const g=await fetch('/wp-json/wc/store/v1/cart',{credentials:'include'});const n=g.headers.get('Nonce');await fetch('/wp-json/wc/store/v1/cart/add-item',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','Nonce':n},body:JSON.stringify({id:555,quantity:1})});});
let swapped=0;
await p.route(/\/kasse\/(\?.*)?$/,async route=>{const r=await route.fetch();let h=await r.text();const h2=h.replace(/(%22Nonce%22%3A%22)[0-9a-f]{10}(%22)/g,'$10123456789$2').replace(/(\\?"Nonce\\?":\\?")[0-9a-f]{10}/g,'$10123456789');if(h2!==h)swapped=1;await route.fulfill({response:r,body:h2});});
const st=[];p.on('response',async r=>{if(r.url().includes('/wc/store/')){let t=r.status()+'';try{if(r.status()===207){const j=await r.json();t+='['+(j.responses||[]).map(x=>x.status+(x.body&&x.body.code?':'+x.body.code.replace('woocommerce_rest_',''):'')).join(';')+']';}}catch(e){}st.push(r.url().split('/wc/store/v1/')[1].split('?')[0]+'='+t);}});
await p.goto('https://peptrium.com/kasse/',{waitUntil:'domcontentloaded',timeout:60000});await p.waitForTimeout(7000);
await p.evaluate(()=>{document.querySelectorAll('#sp-gate,#sp-checkout-info-overlay,[consent-skip-blocker]').forEach(e=>e.remove());});
const has=await p.evaluate(()=>{try{if(wp.apiFetch.setNonce){wp.apiFetch.setNonce(new Headers({'Nonce':'0123456789','Nonce-Timestamp':String(Math.floor(Date.now()/1000)+5000)}));return 'setNonce';}}catch(e){return 'err '+e.message}return 'none';});console.log('  stale via',has);
await p.evaluate(()=>{try{wp.data.dispatch('wc/store/cart').setBillingAddress({first_name:'Testa'});wp.data.dispatch('wc/store/cart').updateCustomerData&&wp.data.dispatch('wc/store/cart').updateCustomerData({billing_address:{first_name:'Testa'}});}catch(e){}});
await p.waitForTimeout(4000);
try{await p.evaluate(()=>wp.data.dispatch('wc/store/cart').applyCoupon('GIBTSNICHT123'));}catch(e){}
await p.waitForTimeout(3000);
const notice=await p.evaluate(()=>[...document.querySelectorAll('.wc-block-components-notice-banner,.wc-block-store-notice')].map(e=>e.innerText.trim()).join(' | '));
console.log(withFix?'MIT Fix':'OHNE Fix','swapped',swapped,'status',st.join(','),'\n  notices:',notice.slice(0,300));
// Warenkorb leeren
await p.evaluate(async()=>{const g=await fetch('/wp-json/wc/store/v1/cart',{credentials:'include'});const n=g.headers.get('Nonce');const c=await g.json();for(const it of c.items)await fetch('/wp-json/wc/store/v1/cart/remove-item',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','Nonce':n},body:JSON.stringify({key:it.key})});});
await b.close();}
(async()=>{await run(false);await run(true);})();
