const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome'});
for(const n of process.argv.slice(2)){const p=await b.newPage({viewport:{width:1080,height:1500}});await p.goto('file://'+__dirname+'/'+n+'.html',{waitUntil:'load'});await p.waitForTimeout(500);const h=await p.evaluate(()=>parseInt(getComputedStyle(document.body).height));await p.setViewportSize({width:1080,height:h});await p.screenshot({path:__dirname+'/'+n+'.jpg',type:'jpeg',quality:88});}
await b.close();})();
