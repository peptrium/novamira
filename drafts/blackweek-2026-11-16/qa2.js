const {chromium}=require('playwright');const TK='zc96rgpowe';
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const c=await b.newContext({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'});
await c.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'},{name:'sp_nlp_closed',value:'1',domain:'peptrium.com',path:'/'}]);
const p=await c.newPage();const errs=[];p.on('pageerror',e=>errs.push(e.message));
const go=async u=>{await p.goto('https://peptrium.com'+u,{waitUntil:'load',timeout:90000});await p.waitForTimeout(2500);await p.evaluate(()=>document.querySelectorAll('[consent-skip-blocker],dialog,#sp-checkout-info-overlay').forEach(e=>e.remove()));};
await go('/?sp_bw=vorschau&k='+TK);
await go('/alle-produkte/');
await p.evaluate(()=>scrollTo(0,document.querySelector('.bw-strip').getBoundingClientRect().top+scrollY-120));await p.waitForTimeout(800);await p.screenshot({path:'q-kat.png'});
let r=await p.evaluate(async()=>{const c=document.querySelector('.sp-pc[data-id="555"]');const b20=[...c.querySelectorAll('.vo button')].find(x=>/20/.test(x.textContent));b20.click();await new Promise(r=>setTimeout(r,1200));return c.querySelector('.pr').innerText;});console.log('Karte 20mg:',r.replace(/\n/g,' '));
r=await p.evaluate(()=>{const c=document.querySelector('.sp-pc[data-id="431"]')||[...document.querySelectorAll('.sp-pc')].find(x=>/TB-500/.test(x.innerText));return c?c.querySelector('.pr').innerText:'-'});console.log('Karte TB-500:',r.replace(/\n/g,' '));
await go('/produkt/retatrutide/');
r=await p.evaluate(async()=>{const w=t=>new Promise(r=>setTimeout(r,t));const o={};o.start=document.getElementById('rx-price').innerText;document.querySelector('#rx-vol-tiers [data-vol="20mg"]').click();await w(1500);o.v20=document.getElementById('rx-price').innerText;o.tot=document.getElementById('rx-total').innerText;document.querySelector('#rx-tiers [data-q="3"]').click();await w(1200);o.tot3=document.getElementById('rx-total').innerText;o.bb=document.querySelector('.bw-bb .r').innerText;return o;});console.log('Kaufbox:',JSON.stringify(r));
await p.evaluate(()=>scrollTo(0,document.getElementById('rx-buybox').getBoundingClientRect().top+scrollY-120));await p.waitForTimeout(600);await p.screenshot({path:'q-bb.png'});
await p.evaluate(()=>document.querySelector('.single_add_to_cart_button').click());await p.waitForTimeout(6000);
r=await p.evaluate(()=>({bar:(document.querySelector('.bw-dbar')||{}).innerText,lines:[...document.querySelectorAll('.sp-cd-item-price')].map(e=>e.innerText.replace(/\n/g,' ')),sub:(document.getElementById('sp-cd-subtotal-val')||{}).innerText}));console.log('Drawer:',JSON.stringify(r));
await p.evaluate(()=>{const b=document.querySelector('.sp-cd-item .sp-cd-qty-btn[data-action="dec"]');b&&b.click();});await p.waitForTimeout(5000);
r=await p.evaluate(()=>({bar:(document.querySelector('.bw-dbar')||{}).innerText,lines:[...document.querySelectorAll('.sp-cd-item-price')].map(e=>e.innerText.replace(/\n/g,' ')),sub:(document.getElementById('sp-cd-subtotal-val')||{}).innerText}));console.log('Drawer nach −:',JSON.stringify(r));
await p.screenshot({path:'q-drawer.png'});
await go('/kasse/');await p.waitForTimeout(2000);
r=await p.evaluate(()=>{const bl=[...document.querySelectorAll('.wp-block-woocommerce-checkout-order-summary-block')].find(x=>x.offsetParent);return {items:[...bl.querySelectorAll('.wc-block-components-order-summary-item__total-price')].map(e=>e.innerText.replace(/\n/g,' ')),row:(bl.querySelector('.bw-co')||{}).innerText,total:(bl.querySelector('.wc-block-components-totals-footer-item')||{}).innerText}});console.log('Kasse:',JSON.stringify(r));
await p.evaluate(()=>{const x=document.querySelector('.wc-block-components-totals-footer-item');scrollTo(0,x.getBoundingClientRect().top+scrollY-520)});await p.waitForTimeout(800);await p.screenshot({path:'q-kasse.png'});
console.log('JS-Fehler:',errs.filter(e=>!/Failed to fetch/.test(e)));
// Desktop Startseite
const d=await b.newContext({viewport:{width:1440,height:900}});await d.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'},{name:'sp_nlp_closed',value:'1',domain:'peptrium.com',path:'/'}]);const q=await d.newPage();
await q.goto('https://peptrium.com/?sp_bw=vorschau&k='+TK,{waitUntil:'load',timeout:90000});await q.waitForTimeout(3500);await q.evaluate(()=>document.querySelectorAll('[consent-skip-blocker],dialog').forEach(e=>e.remove()));await q.screenshot({path:'q-desk.png'});
// normaler Besucher
const n=await b.newContext();const z=await n.newPage();await z.goto('https://peptrium.com/produkt/retatrutide/',{waitUntil:'load'});console.log('Normal:',await z.evaluate(()=>!!document.getElementById('sp-bwv-js')+' '+document.getElementById('rx-price').innerText));
await b.close();})();
