const {chromium}=require('playwright');
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
for(const [f,h,full] of [['sheet',1920,true],['story',1920,false]]){const p=await b.newPage({viewport:{width:1080,height:h}});await p.goto('file://'+__dirname+'/'+f+'.html',{waitUntil:'networkidle',timeout:60000});await p.waitForTimeout(1500);await p.screenshot({path:__dirname+'/'+(f==='sheet'?'partner-info':'story-vorlage')+'.png',fullPage:full});}
await b.close();})().catch(e=>{console.error(e.message);process.exit(1)});
