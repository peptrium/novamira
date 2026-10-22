const {chromium}=require('playwright');
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const p=await b.newPage({viewport:{width:1200,height:720}});await p.goto('file://'+__dirname+'/hero.html',{waitUntil:'networkidle'});await p.waitForTimeout(800);await p.screenshot({path:__dirname+'/nl-hw-hero.jpg',type:'jpeg',quality:86});await b.close();})();
