const {chromium}=require('playwright');const fs=require('fs');
(async()=>{const b=await chromium.launch();
for(const f of fs.readdirSync(__dirname).filter(x=>/^\d-.*\.html$/.test(x))){const p=await b.newPage({viewport:{width:1080,height:1920}});await p.goto('file://'+__dirname+'/'+f,{waitUntil:'networkidle',timeout:60000});await p.waitForTimeout(1200);await p.screenshot({path:__dirname+'/'+f.replace('.html','.png')});await p.close();}
await b.close();})().catch(e=>{console.error(e.message);process.exit(1)});
