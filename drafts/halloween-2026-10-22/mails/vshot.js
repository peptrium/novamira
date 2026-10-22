const {chromium}=require('playwright');
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
for(const n of ['teaser','early','letzte']){const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2});await p.goto('file://'+__dirname+'/vorschau-'+n+'.html',{waitUntil:'networkidle',timeout:60000});await p.waitForTimeout(800);await p.screenshot({path:__dirname+'/mail-'+n+'.png',fullPage:true});}
await b.close();})();
