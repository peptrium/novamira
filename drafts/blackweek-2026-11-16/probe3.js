const L=require('./lib.js');(async()=>{const {b,c}=await L.open();const p=await c.newPage();
await L.go(p,'https://peptrium.com/produkt/retatrutide/?bw=1');
const r=await p.evaluate(async()=>{const add=async(id,q)=>{const f=new FormData();f.append('product_id',id);f.append('quantity',q);await fetch('/?wc-ajax=add_to_cart',{method:'POST',body:f,credentials:'same-origin'});};await add(555,2);await add(1011,1);await add(74,1);return 1;});
await L.go(p,'https://peptrium.com/produkt/retatrutide/?bw=1');
await p.evaluate(()=>{const f=document.getElementById('sp-cart-fab');f&&f.click();});await p.waitForTimeout(3000);
console.log(await p.evaluate(()=>{const d=document.getElementById('sp-cart-drawer');return d?d.outerHTML.replace(/<svg[\s\S]*?<\/svg>/g,'').replace(/<style[\s\S]*?<\/style>/g,'').replace(/<script[\s\S]*?<\/script>/g,'').slice(0,9000):'none'}));
await p.screenshot({path:'dr0.png'});await b.close();})();
