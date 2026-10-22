const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome'});
const p=await b.newPage({viewport:{width:1200,height:720}});for(const n of ['a','b','c']){await p.goto('file://'+__dirname+'/'+n+'.html');await p.waitForTimeout(400);await p.screenshot({path:__dirname+'/hero-'+n+'.jpg',type:'jpeg',quality:86});}await b.close();})();
