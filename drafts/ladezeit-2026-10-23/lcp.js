const {chromium}=require('playwright');
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
for(const url of ['https://peptrium.com/','https://peptrium.com/produkt/retatrutide/','https://peptrium.com/alle-produkte/']){
const ctx=await b.newContext({viewport:{width:390,height:844},isMobile:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1'});
const p=await ctx.newPage();const c=await ctx.newCDPSession(p);await c.send('Emulation.setCPUThrottlingRate',{rate:4});
await c.send('Network.emulateNetworkConditions',{offline:false,latency:150,downloadThroughput:1.6*1024*1024/8,uploadThroughput:750*1024/8});
await p.addInitScript(()=>{window.__lcp=0;window.__cls=0;new PerformanceObserver(l=>{for(const e of l.getEntries())window.__lcp=e.startTime}).observe({type:'largest-contentful-paint',buffered:true});new PerformanceObserver(l=>{for(const e of l.getEntries())if(!e.hadRecentInput)window.__cls+=e.value}).observe({type:'layout-shift',buffered:true});});
const t0=Date.now();await p.goto(url,{waitUntil:'load',timeout:120000});const tl=Date.now()-t0;await p.waitForTimeout(3000);
const r=await p.evaluate(()=>{const n=performance.getEntriesByType('navigation')[0];const res=performance.getEntriesByType('resource');let kb=0;res.forEach(x=>kb+=x.transferSize||0);return {ttfb:Math.round(n.responseStart),fcp:Math.round((performance.getEntriesByName('first-contentful-paint')[0]||{}).startTime||0),lcp:Math.round(window.__lcp),cls:+window.__cls.toFixed(3),req:res.length,kb:Math.round(kb/1024),js:res.filter(x=>x.initiatorType==='script').length}});
console.log(url,'load',tl,'ms',JSON.stringify(r));await ctx.close();}
await b.close();})();
