const {chromium}=require('playwright');
exports.open=async function(dsf){const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const c=await b.newContext({viewport:{width:390,height:(+process.env.VH||844)},deviceScaleFactor:dsf||1,isMobile:true,hasTouch:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'});
await c.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'},{name:'sp_nlp_closed',value:'1',domain:'peptrium.com',path:'/'}]);
return {b,c};};
exports.go=async function(p,url){await p.goto(url,{waitUntil:'load',timeout:90000});await p.waitForTimeout(3500);
await p.evaluate(()=>{document.querySelectorAll('[consent-skip-blocker],dialog,#sp-gate,#sp-nlp,#sp-checkout-info-overlay').forEach(e=>e.remove());document.body.classList.remove('sp-gate-locked');document.documentElement.style.overflow='';document.body.style.overflow='';});};
