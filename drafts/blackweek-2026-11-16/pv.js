const L=require('./lib.js');(async()=>{const {b,c}=await L.open(2);const p=await c.newPage();p.on('pageerror',e=>console.log('ERR',e.message));
await L.go(p,'https://peptrium.com/?sp_bw=vorschau&k=zc96rgpowe');await p.waitForTimeout(1500);console.log(p.url());await p.screenshot({path:'pv-home.png'});
await L.go(p,'https://peptrium.com/produkt/retatrutide/');let y=await p.evaluate(()=>document.getElementById('rx-buybox').getBoundingClientRect().top+scrollY-120);await p.evaluate(y=>scrollTo(0,y),y);await p.waitForTimeout(1500);await p.screenshot({path:'pv-prod.png'});
await p.evaluate(()=>document.querySelector('.single_add_to_cart_button').click());await p.waitForTimeout(5000);await p.screenshot({path:'pv-drawer.png'});
await L.go(p,'https://peptrium.com/kasse/');await p.waitForTimeout(3000);y=await p.evaluate(()=>{const x=document.querySelector('.wc-block-components-totals-footer-item');return x?x.getBoundingClientRect().top+scrollY-500:0});await p.evaluate(y=>scrollTo(0,y),y);await p.waitForTimeout(1500);await p.screenshot({path:'pv-kasse.png'});
await L.go(p,'https://peptrium.com/?sp_bw=aus');console.log(await p.evaluate(()=>!!document.getElementById('sp-bwv-js')));
await b.close();})();
