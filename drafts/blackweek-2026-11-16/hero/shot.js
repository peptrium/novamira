const {chromium}=require('playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:3});
for(const f of process.argv.slice(2)){await p.goto('file://'+process.cwd()+'/'+f+'.html');await p.waitForTimeout(1500);await p.screenshot({path:f+'.png'});}
await b.close();})();
