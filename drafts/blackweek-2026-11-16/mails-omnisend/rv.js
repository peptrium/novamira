const {chromium}=require('playwright');const fs=require('fs');
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
for(const [w,tag] of [[390,'m'],[640,'d']]){const p=await b.newPage({viewport:{width:w,height:900},deviceScaleFactor:1});
for(const k of ['start','pen','favoriten','teaser','insider','blackfriday','sonntag','letzte']){const body=fs.readFileSync(k+'.body','utf8').replace(/\[\[unsubscribe_link\]\]/g,'#');
 await p.setContent('<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width"><body style="margin:0;background:#E5E5E5">'+body+'</body>',{waitUntil:'load',timeout:60000});await p.waitForTimeout(1500);
 const info=await p.evaluate(()=>({w:document.documentElement.scrollWidth,imgs:[...document.images].map(i=>i.naturalWidth?'ok':'FEHLT '+i.src)}));
 console.log(tag,k,JSON.stringify(info));await p.screenshot({path:`r-${tag}-${k}.png`,fullPage:true});}
await p.close();}await b.close();})();
