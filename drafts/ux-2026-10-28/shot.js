const { chromium } = require('playwright');
(async () => {
  const p = new URL(process.env.https_proxy);
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy: { server: `${p.protocol}//${p.host}`, username: decodeURIComponent(p.username), password: decodeURIComponent(p.password) } });
  const pg=await b.newPage({viewport:{width:900,height:1000},deviceScaleFactor:2});
  for(const n of ['p3-produktseite','p4-startseite','rechner']){await pg.goto('file://'+process.cwd()+'/'+n+'.html');await pg.waitForTimeout(3000);await pg.locator('.wrap').screenshot({path:n+'.png'});}
  await b.close();
})();
