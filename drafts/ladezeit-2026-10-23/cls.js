const {chromium}=require('playwright');
(async()=>{const u=new URL(process.env.https_proxy);const b=await chromium.launch({executablePath:'/opt/pw-browsers/chromium-1194/chrome-linux/chrome',proxy:{server:u.protocol+'//'+u.host,username:decodeURIComponent(u.username),password:decodeURIComponent(u.password)}});
for(const url of process.argv.slice(2)){
const ctx=await b.newContext({viewport:{width:390,height:844},isMobile:true,userAgent:'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1'});
const p=await ctx.newPage();const c=await ctx.newCDPSession(p);await c.send('Emulation.setCPUThrottlingRate',{rate:4});
await p.addInitScript(()=>{window.__s=[];new PerformanceObserver(l=>{for(const e of l.getEntries())if(!e.hadRecentInput)window.__s.push({v:+e.value.toFixed(3),t:Math.round(e.startTime),src:(e.sources||[]).map(s=>{const n=s.node;if(!n)return '?';return (n.id?'#'+n.id:'')+(n.className&&typeof n.className=='string'?'.'+n.className.split(' ').slice(0,2).join('.'):'')+'<'+(n.nodeName)+'> '+JSON.stringify(s.previousRect.y)+'→'+JSON.stringify(s.currentRect.y)})})}).observe({type:'layout-shift',buffered:true});});
await p.goto(url,{waitUntil:'load',timeout:120000});await p.waitForTimeout(4000);
const s=await p.evaluate(()=>window.__s);s.sort((a,b)=>b.v-a.v);console.log(url,'total',s.reduce((a,x)=>a+x.v,0).toFixed(3));s.slice(0,8).forEach(x=>console.log(' ',x.v,'@',x.t,'ms',x.src.join(' | ').slice(0,300)));await ctx.close();}
await b.close();})();
