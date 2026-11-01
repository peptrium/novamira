const {chromium}=require('playwright');(async()=>{const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome'});
for (const [w,f] of [[400,'v2-mobil.png'],[700,'v2-desktop.png']]){const p=await b.newPage({viewport:{width:w,height:800},deviceScaleFactor:2});await p.goto('file://'+process.cwd()+'/bericht-oktober-v2.html');await p.waitForTimeout(1200);await p.screenshot({path:f,fullPage:true});}
await b.close();})();
