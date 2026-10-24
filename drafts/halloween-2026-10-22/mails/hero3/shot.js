const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome'});
for(const n of ['m1','m2','m3','m4']){const p=await b.newPage({viewport:{width:1080,height:1260}});await p.goto('file://'+__dirname+'/'+n+'.html',{waitUntil:'load'});await p.waitForTimeout(400);await p.screenshot({path:__dirname+'/'+n+'.jpg',type:'jpeg',quality:86});}
await b.close();})();
