const {chromium}=require('playwright');(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const c=await b.newContext({viewport:{width:390,height:844},deviceScaleFactor:1,isMobile:true,hasTouch:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'});
await c.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'}]);
const p=await c.newPage();
await p.goto('https://peptrium.com/?bw=1',{waitUntil:'load',timeout:60000});await p.waitForTimeout(4000);
await p.evaluate(()=>document.querySelectorAll('[consent-skip-blocker],dialog,#sp-gate').forEach(e=>e.remove()));
const h=await p.evaluate(()=>{const m=document.querySelector('.sp-hero2');const tb=document.getElementById('sp-top-banner');return {hero:m?m.outerHTML.slice(0,3000):null,tb:tb?tb.outerHTML.slice(0,1500):null}});
console.log(JSON.stringify(h,null,1));await p.screenshot({path:'probe.png'});await b.close();})();
