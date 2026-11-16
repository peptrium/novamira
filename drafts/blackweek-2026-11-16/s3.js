const L=require('./lib.js');const fs=require('fs');const bw=fs.readFileSync('bw.js','utf8'),bw3=fs.readFileSync('bw3.js','utf8');
(async()=>{const {b,c}=await L.open(2);const p=await c.newPage();p.on('pageerror',e=>console.log('ERR',e.message));
await L.go(p,'https://peptrium.com/produkt/retatrutide/?bw=1');
await p.evaluate(async()=>{const add=async(id,q)=>{const f=new FormData();f.append('product_id',id);f.append('quantity',q);await fetch('/?wc-ajax=add_to_cart',{method:'POST',body:f,credentials:'same-origin'});};await add(555,3);await add(1011,1);await add(74,1);});
await L.go(p,'https://peptrium.com/kasse/?bw=1');await p.waitForTimeout(3000);
await p.evaluate(()=>{window.BWP='bw';window.BWNOW='2026-11-17T09:12:33+01:00';});await p.addScriptTag({content:bw});await p.addScriptTag({content:bw3});await p.waitForTimeout(500);
const y=await p.evaluate(()=>{const x=document.querySelector('.wp-block-woocommerce-checkout-order-summary-cart-items-block');return x.getBoundingClientRect().top+scrollY-160});await p.evaluate(y=>scrollTo(0,y),y);await p.waitForTimeout(700);
await p.screenshot({path:'06-kasse.png'});
await b.close();})();
