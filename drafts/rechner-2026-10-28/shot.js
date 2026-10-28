const { chromium } = require('playwright');
(async () => {
  const p = new URL(process.env.https_proxy);
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy: { server: `${p.protocol}//${p.host}`, username: decodeURIComponent(p.username), password: decodeURIComponent(p.password) } });
  for(const n of ['R1','R2','R3']){const pg=await b.newPage({viewport:{width:n==='R2'?1620:390,height:844},deviceScaleFactor:2});await pg.goto('file://'+process.cwd()+'/'+n+'.html');await pg.waitForTimeout(3500);await pg.screenshot({path:'full-'+n+'.png',fullPage:true});await pg.close();}
  await b.close();
})();
