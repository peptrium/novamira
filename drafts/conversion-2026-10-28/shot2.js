const { chromium } = require('playwright');
(async () => {
  const p = new URL(process.env.https_proxy);
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy: { server: `${p.protocol}//${p.host}`, username: decodeURIComponent(p.username), password: decodeURIComponent(p.password) } });
  const pg=await b.newPage({viewport:{width:1800,height:1000},deviceScaleFactor:2});
  for(const n of ['1-partner-landing','2-girocode','3-nochmal-bestellen']){await pg.goto('file://'+process.cwd()+'/'+n+'.html');await pg.waitForTimeout(3000);
   await pg.locator('.wrap').screenshot({path:n+'.png'});
   const c=await pg.locator('.ph').count(); for(let i=0;i<c;i++) await pg.locator('.ph').nth(i).screenshot({path:`s-${n}-${i}.png`});}
  await b.close();
})();
