const {chromium}=require('playwright');
(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1080,height:1920}});
await p.goto('file://'+__dirname+'/sheet.html',{waitUntil:'networkidle',timeout:60000});await p.waitForTimeout(1500);
const h=await p.evaluate(()=>Math.ceil(document.documentElement.scrollHeight));
await p.pdf({path:__dirname+'/Peptrium-Partner-Info-Halloween.pdf',width:'1080px',height:(h+2)+'px',printBackground:true,pageRanges:'1',margin:{top:0,right:0,bottom:0,left:0}});
console.log('h',h);await b.close();})().catch(e=>{console.error(e.message);process.exit(1)});
