const L=require('./lib.js');(async()=>{const {b,c}=await L.open();const p=await c.newPage();
await L.go(p,'https://peptrium.com/alle-produkte/?bw=1');
console.log(await p.evaluate(()=>{const x=document.querySelector('.sp-pc[data-id]');return x?x.outerHTML.slice(0,2500):'none'}));
await L.go(p,'https://peptrium.com/produkt/retatrutide/?bw=1');
console.log('\n=====\n'+await p.evaluate(()=>{const x=document.getElementById('rx-buybox');return x?x.outerHTML.replace(/<script[\s\S]*?<\/script>/g,'').replace(/<style[\s\S]*?<\/style>/g,'').slice(0,6000):'none'}));
await b.close();})();
