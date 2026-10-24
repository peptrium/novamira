const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome'});
for(const n of ['h-teaser','h-early','h-letzte']){const p=await b.newPage({viewport:{width:1080,height:860}});await p.goto('file://'+__dirname+'/'+n+'.html',{waitUntil:'load'});await p.waitForTimeout(500);await p.screenshot({path:__dirname+'/'+n+'.jpg',type:'jpeg',quality:88});}
await b.close();})();
