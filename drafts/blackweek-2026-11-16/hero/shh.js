const {chromium}=require('playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:390,height:900},deviceScaleFactor:3});
await p.goto('file://'+process.cwd()+'/h1.html');await p.waitForTimeout(1500);const el=await p.$('#H');await el.screenshot({path:'h1.png'});await b.close();})();
