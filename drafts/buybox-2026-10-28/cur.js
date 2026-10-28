const { chromium } = require('playwright');
(async () => {
  const p = new URL(process.env.https_proxy);
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy: { server: `${p.protocol}//${p.host}`, username: decodeURIComponent(p.username), password: decodeURIComponent(p.password) } });
  const ctx=await b.newContext({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'});
  await ctx.addCookies([{name:'sp_age_gate',value:'1',domain:'peptrium.com',path:'/'},{name:'sp_nlp_closed',value:'1',domain:'peptrium.com',path:'/'}]);
  const pg=await ctx.newPage();
  await pg.goto('https://peptrium.com/produkt/retatrutide/?x=5',{waitUntil:'load',timeout:90000});await pg.waitForTimeout(3000);
  await pg.evaluate(()=>{document.querySelectorAll('[consent-skip-blocker],#sp-gate,#sp-nlp').forEach(e=>e.remove());const s=document.createElement('style');s.textContent='header,#sp-top-banner,#sp-header-bar,.elementor-location-header,#sp-cart-fab,#sp-social-fab{display:none!important}';document.head.appendChild(s);});
  const info=await pg.evaluate(()=>{const bb=document.getElementById('rx-buybox');const r=bb.getBoundingClientRect();
    const col=bb.closest('.pp-col')||bb.parentElement;const c=col.getBoundingClientRect();
    return {bb:[r.top+scrollY,r.height],col:[c.top+scrollY,c.height],kids:[...bb.children].map(k=>[k.className.slice(0,40),Math.round(k.getBoundingClientRect().height)])};});
  console.log(JSON.stringify(info));
  const el=await pg.$('#rx-buybox'); await el.screenshot({path:'current-buybox.png'});
  await pg.evaluate(y=>scrollTo(0,y),info.col[0]-120); await pg.waitForTimeout(500);
  await pg.screenshot({path:'current-top.png',fullPage:false});
  await b.close();
})();
