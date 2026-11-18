const {chromium}=require('playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1750,height:950},deviceScaleFactor:2});
await p.goto('file://'+process.cwd()+'/code/entwurf.html');await p.waitForTimeout(2500);await p.screenshot({path:'code/entwurf.png',fullPage:true});await b.close();})();
