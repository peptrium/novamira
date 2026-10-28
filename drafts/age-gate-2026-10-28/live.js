const {chromium}=require('playwright');(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'});
await p.goto('https://peptrium.com/?gx=1',{waitUntil:'load',timeout:60000});await p.waitForTimeout(3500);
await p.evaluate(()=>document.querySelectorAll('[consent-skip-blocker],dialog').forEach(e=>e.remove()));
await p.screenshot({path:'vorher.png'});
await p.evaluate(()=>{const g=document.getElementById('sp-gate');if(g)g.remove();document.body.classList.remove('sp-gate-locked');});
await p.screenshot({path:'bg.png'});await b.close();})();
