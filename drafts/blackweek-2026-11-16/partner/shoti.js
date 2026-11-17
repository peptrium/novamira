const {chromium}=require('playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:540,height:900},deviceScaleFactor:2});
await p.goto('file://'+process.cwd()+'/info.html');await p.waitForTimeout(1200);await p.screenshot({path:'Partner-Info-Black-Week.png',fullPage:true});await b.close();})();
