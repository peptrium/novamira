const {chromium}=require('playwright');(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
const p=await b.newPage({viewport:{width:390,height:844},deviceScaleFactor:2});
for(const n of ['a','b','c']){await p.goto('file://'+process.cwd()+'/'+n+'.html');await p.waitForTimeout(1500);await p.screenshot({path:n+'.png'});}await b.close();})();
