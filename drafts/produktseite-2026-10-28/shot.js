const { chromium } = require('playwright');
(async () => {
  const p = new URL(process.env.https_proxy);
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy: { server: `${p.protocol}//${p.host}`, username: decodeURIComponent(p.username), password: decodeURIComponent(p.password) } });
  const pg=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2});
  for(const n of ['D','E','F']){await pg.goto('file://'+process.cwd()+'/variante-'+n+'.html');await pg.waitForTimeout(3500);await pg.screenshot({path:'full-'+n+'.png',fullPage:true});}
  await b.close();
})();
