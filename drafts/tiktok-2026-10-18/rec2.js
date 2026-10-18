const { chromium } = require('playwright'); const fs=require('fs');
(async()=>{
  const [,,spec,dir,dur]=process.argv; const d=JSON.parse(fs.readFileSync(spec,'utf8'));
  const b=await chromium.launch(); const p=await b.newPage({viewport:{width:1080,height:1920}});
  await p.goto('file://'+__dirname+'/engine.html'); await p.evaluate(x=>setData(x),d); await p.waitForTimeout(400);
  const n=Math.ceil(30*(+dur));
  for(let f=0;f<n;f++){ await p.evaluate(t=>render(t), f/30); await p.screenshot({path:`${dir}/${String(f).padStart(5,'0')}.jpg`,type:'jpeg',quality:92}); }
  await b.close();
})();
