const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome'});
for(const n of ['m1','m2','m3','m4']){const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2});await p.goto('file://'+__dirname+'/prev-'+n+'.html',{waitUntil:'load'});await p.waitForTimeout(800);await p.screenshot({path:__dirname+'/handy-'+n+'.jpg',type:'jpeg',quality:85});}
await b.close();})();
