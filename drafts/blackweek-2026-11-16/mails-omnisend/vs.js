const {chromium}=require('playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:1});
for(const k of ['pen','favoriten','teaser','insider','blackfriday','sonntag','letzte']){await p.goto('file://'+process.cwd()+'/vorschau-'+k+'.html');await p.waitForTimeout(600);await p.screenshot({path:'v-'+k+'.png',fullPage:true});}
await b.close();})();
