const {chromium}=require('playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:3});
await p.goto('file://'+process.cwd()+'/wd-cagri.html');await p.waitForTimeout(1200);const h=await p.evaluate(()=>document.querySelector('.m').offsetHeight);
await p.screenshot({path:'em-wd-cagri.png',clip:{x:0,y:0,width:390,height:h}});console.log(h);await b.close();})();
