const { chromium } = require('playwright');
(async () => {
  const p = new URL(process.env.https_proxy);
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy: { server: `${p.protocol}//${p.host}`, username: decodeURIComponent(p.username), password: decodeURIComponent(p.password) } });
  let pg=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2});await pg.goto('file://'+process.cwd()+'/v2.html');await pg.waitForTimeout(3500);await pg.screenshot({path:'full-v2.png',fullPage:true});
  pg=await b.newPage({viewport:{width:600,height:600},deviceScaleFactor:2});await pg.goto('file://'+process.cwd()+'/v2-spritzen.html');await pg.waitForTimeout(2500);await pg.screenshot({path:'v2-spritzen.png',fullPage:true});
  await b.close();
})();
