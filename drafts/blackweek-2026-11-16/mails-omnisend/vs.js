const {chromium}=require('playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2});
await p.goto('file://'+process.cwd()+'/vorschau-start.html');await p.waitForTimeout(800);await p.screenshot({path:'vorschau-start.png',fullPage:true});await b.close();})();
