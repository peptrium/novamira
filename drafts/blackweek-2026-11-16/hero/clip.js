const {chromium}=require('playwright');
const M={'2-pen':'.stg','3-fav':'.nb','4-teaser':'.tl','5-insider':'.h','6-bf':'.sub','7-so':'.h','8-last':'.row'};
(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:3});
for(const [k,sel] of Object.entries(M)){await p.goto('file://'+process.cwd()+'/m2-'+k+'.html');await p.waitForTimeout(1500);
 const r=await p.evaluate(s=>{const m=document.querySelector('.m').getBoundingClientRect();const e=document.querySelector('.m '+s).getBoundingClientRect();return {y:m.top+scrollY,h:e.bottom-m.top+(s==='.stg'?-4:(s==='.nb'||s==='.row'?6:18))};},sel);
 await p.screenshot({path:'em-'+k+'.png',fullPage:true,clip:{x:0,y:r.y,width:390,height:r.h}});console.log(k,Math.round(r.h));}
await b.close();})();
