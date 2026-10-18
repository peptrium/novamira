const { chromium } = require('playwright');
(async()=>{
  const b=await chromium.launch(); const p=await b.newPage({viewport:{width:1080,height:1920}});
  await p.goto('file://'+process.cwd()+'/overlay.html'); await p.waitForTimeout(500);
  const fps=30, dur=+process.argv[2]||24;
  for(let f=0;f<fps*dur;f++){ await p.evaluate(t=>render(t), f/fps); await p.screenshot({path:`fr/${String(f).padStart(4,'0')}.png`, omitBackground:true}); }
  await b.close();
})();
