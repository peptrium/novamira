<?php
/**
 * Plugin Name: SP Gratisversand-Hinweis
 * Description: Kasse: schmale Gratisversand-Zeile oben, passende Vorschläge (auch "Noch 1× <Produkt im Warenkorb>") in der Bestellübersicht, dort außerdem Menge ändern / Artikel entfernen (2026-10-09).
 *
 * Hintergrund (Analyse-Tab, 90 Tage): 7 Bestellungen lagen knapp (bis 30 €)
 * unter der 100-€-Grenze, nur 4 knapp darüber.
 *
 * Aufbau (v2, nach Feedback): oben nur eine schmale Zeile (lenkt nicht vom
 * Formular ab), die Vorschlaege sitzen in der Bestellübersicht direkt unter den
 * Artikeln - auf dem Handy steht die direkt vor "Zahlungspflichtig bestellen".
 * Vorschlaege = Zubehoer UND "Noch 1×" fuer Produkte, die schon im Warenkorb
 * liegen; zuerst das Guenstigste, das die Luecke allein schliesst (sonst
 * reichen Bac Water + Spritzen z. B. bei 1× Retatrutide nicht). Jeder Artikel
 * der Bestellübersicht bekommt −/+ und "Entfernen" (Geschenke nicht).
 *
 * Rechnet wie WooCommerce selbst (free_shipping, ignore_discounts = no):
 * Artikel-Zwischensumme inkl. Steuer minus Gutscheinrabatt, ohne
 * Guthaben-Aufladung (sp-abo-wallet-ux.php nimmt sie ebenfalls heraus).
 * Ob Gratisversand tatsaechlich greift, entscheidet die Versandrate im
 * Warenkorb (free_shipping vorhanden) - der Betrag ist nur die Anzeige.
 * Nur fuer Lieferland Deutschland (bzw. noch kein Land gewaehlt), weil nur die
 * Zone Deutschland Gratisversand hat. Liest die Mindestgrenze aus der
 * Versandzone, damit eine spaetere Aenderung in WooCommerce automatisch passt.
 *
 * Achtung: Das Inline-Skript darf das Wort "c-l-a-r-i-t-y" NICHT enthalten -
 * Real Cookie Banner blockiert sonst das ganze Skript bis zur Zustimmung.
 */
if (!defined('ABSPATH')) {
    exit;
}

/** Mindestbetrag der Gratisversand-Methode in der Zone Deutschland (0 = keine). */
function sp_fsh_threshold() {
    foreach (WC_Shipping_Zones::get_zones() as $zone) {
        $codes = array_map(function ($l) {
            return $l->code;
        }, $zone['zone_locations']);
        if (!in_array('DE', $codes, true)) {
            continue;
        }
        foreach ($zone['shipping_methods'] as $m) {
            if ($m->id === 'free_shipping' && $m->enabled === 'yes' && in_array($m->get_option('requires'), array('min_amount', 'either'), true)) {
                return (float) $m->get_option('min_amount');
            }
        }
    }
    return 0.0;
}

define('SP_FSH_ACCESSORY_CAT', 20);
define('SP_FSH_PEN_IDS', '393,395,396');
define('SP_FSH_SKIP_IDS', '729,745,817'); // Aufladung, reine Geschenk-Produkte

/**
 * Vorschlags-Katalog: alle kaufbaren Produkte mit Varianten/Preis, Kategorie-Infos,
 * Kombinationen (gepflegt in sp_crosssell_map() + echte Kaufdaten) und Mengenrabatt.
 * 12 h zwischengespeichert; die Kaufdaten aendern sich nur langsam.
 */
function sp_fsh_catalog() {
    $cached = get_transient('sp_fsh_catalog_v3');
    if (is_array($cached)) {
        return $cached;
    }
    $skip = array_map('intval', explode(',', SP_FSH_SKIP_IDS));
    $pens = array_map('intval', explode(',', SP_FSH_PEN_IDS));
    $products = array();
    $var2parent = array();
    foreach (wc_get_products(array('status' => 'publish', 'limit' => -1)) as $p) {
        $pid = $p->get_id();
        if (in_array($pid, $skip, true) || !$p->is_purchasable() || !$p->is_in_stock()) {
            continue;
        }
        // Vorbestell-Produkte nicht vorschlagen (Kunde muesste warten).
        if (function_exists('sp_preorder_product_ids') && in_array($pid, array_map('intval', sp_preorder_product_ids()), true)) {
            continue;
        }
        $vars = array();
        if ($p->is_type('variable')) {
            foreach ($p->get_children() as $vid) {
                $v = wc_get_product($vid);
                if (!$v || !$v->is_purchasable() || !$v->is_in_stock()) {
                    continue;
                }
                $vars[] = array('id' => $vid, 'price' => (float) wc_get_price_to_display($v), 'label' => implode(' ', array_filter(array_values($v->get_variation_attributes()))));
                $var2parent[$vid] = $pid;
            }
            if (!$vars) {
                continue;
            }
        } else {
            $vars[] = array('id' => $pid, 'price' => (float) wc_get_price_to_display($p), 'label' => '');
        }
        $img = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail');
        $products[$pid] = array(
            'name' => html_entity_decode($p->get_name(), ENT_QUOTES, 'UTF-8'),
            'img' => $img ?: '',
            'vars' => $vars,
            'acc' => has_term(SP_FSH_ACCESSORY_CAT, 'product_cat', $pid),
            'pen' => in_array($pid, $pens, true),
        );
    }
    // Kombinationen: gepflegte Paare (Gewicht 3, Rueckrichtung 2) + gemeinsam gekauft (je Bestellung +1).
    $comp = array();
    $add = function ($a, $b, $w) use (&$comp, $products) {
        if ($a === $b || !isset($products[$a], $products[$b]) || $products[$a]['acc'] || $products[$b]['acc']) {
            return;
        }
        $comp[$a][$b] = ($comp[$a][$b] ?? 0) + $w;
    };
    if (function_exists('sp_crosssell_map')) {
        foreach (sp_crosssell_map() as $a => $b) {
            $add((int) $a, (int) $b, 3);
            $add((int) $b, (int) $a, 2);
        }
    }
    foreach (wc_get_orders(array('limit' => 300, 'status' => array('processing', 'completed'), 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects')) as $o) {
        $ids = array();
        foreach ($o->get_items() as $item) {
            if (!$item->get_meta('Geschenk') && (float) $item->get_total() > 0) {
                $ids[(int) $item->get_product_id()] = true;
            }
        }
        $ids = array_keys($ids);
        foreach ($ids as $a) {
            foreach ($ids as $b) {
                $add($a, $b, 1);
            }
        }
    }
    // Reduzierte Produkte/Varianten bekommen keinen Mengenrabatt (sp_quantity_discount_is_on_sale()).
    $on_sale = array();
    if (function_exists('sp_quantity_discount_is_on_sale') && function_exists('sp_quantity_discount_product_ids')) {
        foreach (sp_quantity_discount_product_ids() as $qid) {
            $qp = wc_get_product($qid);
            if (!$qp) {
                continue;
            }
            foreach ($qp->is_type('variable') ? $qp->get_children() : array($qid) as $vid) {
                $vp = wc_get_product($vid);
                if ($vp && sp_quantity_discount_is_on_sale($vp)) {
                    $on_sale[] = (int) $vid;
                }
            }
        }
    }
    $qty = array(
        'ids' => function_exists('sp_quantity_discount_product_ids') ? array_map('intval', sp_quantity_discount_product_ids()) : array(),
        'tiers' => function_exists('sp_quantity_discount_tiers') ? sp_quantity_discount_tiers() : array(),
        'sale' => $on_sale,
    );
    $data = array('products' => $products, 'var2parent' => $var2parent, 'comp' => $comp, 'qty' => $qty);
    set_transient('sp_fsh_catalog_v3', $data, 12 * HOUR_IN_SECONDS);
    return $data;
}
add_action('woocommerce_update_product', function () {
    delete_transient('sp_fsh_catalog_v3');
});

/* Messung: Vorschlaege gesehen / "+" getippt -> wp_sp_stats (type fshview / fshclick, campaign "add|more:<Preis>"). */
function sp_fsh_ajax_event() {
    if (!function_exists('sp_an_should_track') || !function_exists('sp_an_insert') || !sp_an_should_track()) {
        wp_die('', '', array('response' => 204));
    }
    $click = isset($_POST['t']) && $_POST['t'] === 'click';
    $kind = isset($_POST['k']) && $_POST['k'] === 'more' ? 'more' : 'add';
    sp_an_insert(array(
        'type' => $click ? 'fshclick' : 'fshview',
        'product_id' => $click ? absint($_POST['pid'] ?? 0) : 0,
        'campaign' => $click ? $kind . ':' . number_format(round((float) ($_POST['p'] ?? 0), 2), 2, '.', '') : '',
    ));
    wp_die('', '', array('response' => 204));
}
add_action('wp_ajax_sp_fsh_ev', 'sp_fsh_ajax_event');
add_action('wp_ajax_nopriv_sp_fsh_ev', 'sp_fsh_ajax_event');

add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }
    $min = sp_fsh_threshold();
    $cat = sp_fsh_catalog();
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 729;
    ?>
    <style>
      /* Handy: die Kasse hatte 3 verschachtelte Seitenraender (Astra-Container 16 px +
         entry-content 16 px + Checkout-Block 16 px - 20 px) = 28 px je Seite, die Spalte
         war nur 294 px breit. Jetzt nur noch der Container-Rand (16 px) -> 358 px. */
      @media (max-width:781px){
        body.woocommerce-checkout .entry-content{padding-left:0 !important;padding-right:0 !important}
        body.woocommerce-checkout .wp-block-woocommerce-checkout.wc-block-checkout{margin-left:0 !important;margin-right:0 !important;padding-left:0 !important;padding-right:0 !important}
      }
      #sp-fsh-top{background:#F4F5F6;border:1px solid #E2E4E8;border-radius:12px;padding:10px 14px;margin:0 0 18px;font-family:Sora,sans-serif;color:#0D0F12}
      #sp-fsh-top .t{font-size:13px;line-height:1.4;margin:0 0 7px;display:flex;justify-content:space-between;gap:10px;align-items:baseline}
      #sp-fsh-top .t a{font-size:12px;color:#0D0F12;font-weight:600;white-space:nowrap;text-decoration:underline}
      #sp-fsh-top.ok{background:#ECF8F1;border-color:#B9E4CC}
      #sp-fsh-top.ok .t{margin:0;color:#0B6B3A}
      .sp-fsh-bar{height:6px;border-radius:99px;background:#DCDEE2;overflow:hidden}
      .sp-fsh-bar span{display:block;height:100%;border-radius:99px;background:#0D0F12;transition:width .4s ease}
      /* Als eigene Zeile in der Bestellübersicht (übernimmt Rahmen/Abstände von .wc-block-components-totals-wrapper) */
      .sp-fsh-box{font-family:Sora,sans-serif;color:#0D0F12;scroll-margin-top:90px;padding:16px 16px 6px !important}
      .sp-fsh-box .h{font-size:14px;line-height:1.4;margin:0 0 4px}
      .sp-fsh-box .it{display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid #EEF0F2}
      .sp-fsh-box .h+.it{border-top:0}
      .sp-fsh-box .it img{width:44px;height:44px;object-fit:cover;border-radius:9px;flex:none;background:#F2F3F4}
      .sp-fsh-box .it .n{flex:1;min-width:0;font-size:13px;font-weight:600;line-height:1.35}
      .sp-fsh-box .it .n small{display:block;font-weight:500;color:#5B6169;font-size:12px;margin-top:2px}
      .sp-fsh-box .it .n small b{color:#0B6B3A;font-weight:700}
      .sp-oi-ctl button{border:0;border-radius:9px;background:#0D0F12;color:#fff;font:600 12px Sora,sans-serif;padding:8px 10px;cursor:pointer;white-space:nowrap;flex:none}
      .sp-fsh-box button[disabled],.sp-oi-ctl button[disabled]{opacity:.5;cursor:default}
      .sp-oi-ctl{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px;font-family:Sora,sans-serif;max-width:100%;min-width:0}
      /* Theme-Button-Stile (Astra/Elementor) gezielt ueberschreiben */
      .sp-oi-ctl button.sp-q{all:unset;box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:1px solid #DCDEE2 !important;border-radius:8px;background:#fff !important;color:#0D0F12 !important;font:600 16px/1 Sora,sans-serif;cursor:pointer}
      .sp-oi-ctl button.sp-q[disabled]{opacity:.35;cursor:default}
      .sp-oi-ctl .q{min-width:18px;text-align:center;font-size:13px;font-weight:700}
      .sp-oi-ctl button.rm{all:unset;cursor:pointer;margin-left:8px;color:#8A9099 !important;background:none !important;font:500 12px Sora,sans-serif;text-decoration:underline}
    </style>
    <script>
    (function(){
      var MIN=<?php echo wp_json_encode($min); ?>,TOPUP=<?php echo (int) $topup; ?>,CAT=<?php echo wp_json_encode($cat); ?>,busy=false;
      var P=CAT.products||{},V2P=CAT.var2parent||{},COMP=CAT.comp||{},QTY=CAT.qty||{ids:[],tiers:[]};
      function sel(){return window.wp&&wp.data&&wp.data.select&&wp.data.select('wc/store/cart');}
      function act(){return wp.data.dispatch('wc/store/cart');}
      var dec=document.createElement('textarea');function txt(h){dec.innerHTML=h||'';return dec.value;}
      function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');}
      function money(v,t){
        var d=t&&t.currency_minor_unit!=null?t.currency_minor_unit:2,s=(Math.round(v*100)/100).toFixed(d).split('.');
        var ts=t&&t.currency_thousand_separator!=null?t.currency_thousand_separator:'.',ds=t&&t.currency_decimal_separator?t.currency_decimal_separator:',';
        s[0]=s[0].replace(/\B(?=(\d{3})+(?!\d))/g,ts);
        return (t&&t.currency_prefix||'')+s.join(ds)+(t&&t.currency_suffix!=null?t.currency_suffix:' €');
      }
      function isAbo(i){return (i.item_data||[]).some(function(d){return /abo|liefer|intervall/i.test((d.name||d.key||'')+' '+(d.value||''));});}
      function state(){
        var st=sel();if(!st||!st.getCartData)return null;
        var c=st.getCartData();if(!c||!c.items)return null;
        var t=c.totals||{},u=Math.pow(10,t.currency_minor_unit||2);
        var basis=(+t.total_items+(+t.total_items_tax||0)-(+t.total_discount||0)-(+t.total_discount_tax||0))/u,goods=0,ids={};
        c.items.forEach(function(i){
          ids[i.id]=1;
          var line=((+i.totals.line_subtotal||0)+(+i.totals.line_subtotal_tax||0))/u;
          if(i.id===TOPUP){basis-=line;}else{goods+=line;}
        });
        var free=false;(c.shippingRates||[]).forEach(function(p){(p.shipping_rates||[]).forEach(function(r){if(r.method_id==='free_shipping')free=true;});});
        return {c:c,u:u,basis:Math.max(0,basis),goods:goods,ids:ids,free:free,country:(c.shippingAddress&&c.shippingAddress.country)||'',totals:t};
      }
      function parentOf(id){return V2P[id]?+V2P[id]:+id;}
      /* Zeilenpreis mit Mengenrabatt (wie sp-quantity-discount.php: nur die Stueck im groessten erreichten Paket rabattiert) */
      function lineTotal(pid,unit,q,itemId){
        if(QTY.ids.indexOf(pid)===-1||(QTY.sale||[]).indexOf(itemId)>-1)return unit*q;
        for(var k=0;k<QTY.tiers.length;k++){var t=QTY.tiers[k];if(q>=t.size){return Math.round(unit*(1-t.percent/100)*100)/100*t.size+unit*(q-t.size);}}
        return unit*q;
      }
      function tierAt(pid,q,itemId){if(QTY.ids.indexOf(pid)===-1||(QTY.sale||[]).indexOf(itemId)>-1)return null;for(var k=0;k<QTY.tiers.length;k++){if(QTY.tiers[k].size===q)return QTY.tiers[k];}return null;}
      function pickVar(prod,rest){var vs=prod.vars.slice().sort(function(a,b){return a.price-b.price;});for(var k=0;k<vs.length;k++){if(vs[k].price>=rest)return vs[k];}return vs[0];}
      function suggestions(s,rest){
        var inCart={},cands={},vial=false,pen=false,names={};
        s.c.items.forEach(function(i){var pp=parentOf(i.id);inCart[pp]=1;names[pp]=txt(i.name);if(P[pp]&&!P[pp].acc){if(P[pp].pen)pen=true;else vial=true;}});
        function put(c){if(c.price>rest+150)return;var k=c.kind+':'+(c.key||c.id);if(!cands[k]||cands[k].rel<c.rel)cands[k]=c;}
        /* 1) Noch 1x vom selben Produkt - mit echtem Mehrpreis inkl. Mengenrabatt */
        s.c.items.forEach(function(i){
          var onSale=(QTY.sale||[]).indexOf(i.id)>-1,unit=((onSale?+i.prices.price:+i.prices.regular_price)||+i.prices.price||0)/s.u,lim=i.quantity_limits||{},pp=parentOf(i.id);
          if((+i.prices.price||0)<=0||i.id===TOPUP||i.sold_individually||lim.editable===false||i.quantity+1>(lim.maximum||9999)||isAbo(i))return;
          var now=((+i.totals.line_subtotal||0)+(+i.totals.line_subtotal_tax||0))/s.u,extra=Math.max(0,lineTotal(pp,unit,i.quantity+1,i.id)-now),t=tierAt(pp,i.quantity+1,i.id);
          /* Rabatt-Hinweis nur, wenn das zusaetzliche Stueck wirklich guenstiger ist als sein normaler Preis
             (sp-quantity-discount.php rechnet vom regulaeren Preis - bei Angebotspreisen kann das teurer werden) */
          if(t&&extra>=(+i.prices.price||0)/s.u-0.005)t=null;
          var v=(i.variation||[]).map(function(x){return x.value;}).join(' ');
          put({kind:'more',pid:pp,key:i.key,qty:i.quantity,price:extra,name:'Noch 1× '+txt(i.name)+(v?' '+v:''),img:(i.images&&i.images[0]&&i.images[0].thumbnail)||'',why:t?('ab '+t.size+' Stück −'+t.percent+' %'):'',rel:t?42:15});
        });
        /* 2) Passende Ergaenzung (gepflegte Kombinationen + gemeinsam gekauft) */
        Object.keys(inCart).forEach(function(pp){
          var m=COMP[pp]||{};
          Object.keys(m).forEach(function(q){
            q=+q;if(inCart[q]||!P[q]||m[q]<2)return; /* Einzelfall (1x zusammen gekauft) ist kein Muster */
            var vr=pickVar(P[q],rest);
            put({kind:'add',pid:q,id:vr.id,price:vr.price,name:P[q].name+(vr.label?' '+vr.label:''),img:P[q].img,why:'Oft zusammen mit '+(names[pp]||'deiner Bestellung')+' gekauft',rel:30+Math.min(30,m[q]*3)});
          });
        });
        /* 3) Zubehoer, das zur Bestellung gehoert */
        var need=[];if(vial){need.push([74,'Zum Anmischen'],[80,'Passendes Zubehör']);}if(pen){need.push([908,'Passend zum Pen']);}
        need.forEach(function(n){if(inCart[n[0]]||!P[n[0]])return;var vr=P[n[0]].vars[0];put({kind:'add',pid:n[0],id:vr.id,price:vr.price,name:P[n[0]].name,img:P[n[0]].img,why:n[1],rel:n[0]===74?40:35}); /* Bac Water ist bei Flaeschchen fast immer dabei */});
        var list=Object.keys(cands).map(function(k){var c=cands[k];c.closes=c.price>=rest-0.005;c.score=c.closes?100+c.rel-(c.price-rest)/4:c.rel+10*c.price/rest;return c;});
        list.sort(function(a,b){return b.score-a.score;});
        var out=list.slice(0,1),pair=false;
        if(out.length&&out[0].closes){
          /* Erster Vorschlag reicht schon - als zweiten den inhaltlich passendsten statt einer zweiten teuren Alternative */
          var restList=list.slice(1).sort(function(a,b){return b.rel-a.rel||a.price-b.price;});
          if(restList.length)out.push(restList[0]);
        }else{out=list.slice(0,2);}
        if(out.length===2&&!out[0].closes&&!out[1].closes&&out[0].price+out[1].price>=rest)pair=true;
        if(!out.length||(!out[0].closes&&!pair)){
          /* Kein Einzelartikel reicht: zwei passende, die zusammen reichen? */
          var nc=list.filter(function(c){return !c.closes;});
          outer:for(var a=0;a<nc.length;a++){for(var b=a+1;b<nc.length;b++){if(nc[a].price+nc[b].price>=rest&&(nc[a].key||nc[a].id)!==(nc[b].key||nc[b].id)){out=[nc[a],nc[b]];pair=true;break outer;}}}
        }
        return {items:out,pair:pair};
      }
      var QS='style="all:unset;box-sizing:border-box;display:inline-flex !important;align-items:center;justify-content:center;width:28px !important;height:28px !important;min-height:0 !important;padding:0 !important;border:1px solid #DCDEE2 !important;border-radius:8px !important;background:#fff !important;color:#0D0F12 !important;font:600 16px/1 Sora,sans-serif !important;box-shadow:none !important;cursor:pointer"';
      var RS='style="all:unset;cursor:pointer;margin-left:8px !important;padding:0 !important;min-height:0 !important;color:#8A9099 !important;background:none !important;border:0 !important;box-shadow:none !important;font:500 12px Sora,sans-serif !important;text-decoration:underline !important;width:auto !important;height:auto !important;white-space:nowrap !important"';
      var PS='style="all:unset;box-sizing:border-box;flex:none;display:inline-flex !important;align-items:center;justify-content:center;width:36px !important;height:36px !important;min-height:0 !important;padding:0 !important;border:0 !important;border-radius:50% !important;background:#0D0F12 !important;color:#fff !important;font:500 22px/1 Sora,sans-serif !important;box-shadow:none !important;cursor:pointer"';
      function setHTML(el,html){if(el.getAttribute('data-k')!==html){el.setAttribute('data-k',html);el.innerHTML=html;}}
      function renderControls(s){
        document.querySelectorAll('.wc-block-components-order-summary').forEach(function(sum){
          sum.querySelectorAll('.wc-block-components-order-summary-item').forEach(function(el,idx){
            var i=s.c.items[idx],desc=el.querySelector('.wc-block-components-order-summary-item__description')||el;
            var nameEl=el.querySelector('.wc-block-components-product-name'),ctl=el.querySelector('.sp-oi-ctl');
            if(!i||!nameEl||txt(i.name).trim().slice(0,12)!==nameEl.textContent.trim().slice(0,12)||(+i.prices.price||0)<=0){if(ctl)ctl.remove();return;}
            var lim=i.quantity_limits||{},canQty=!i.sold_individually&&lim.editable!==false;
            var h=(canQty?'<button type="button" class="sp-q" '+QS+' data-act="dec" aria-label="Weniger"'+(i.quantity<=(lim.minimum||1)||busy?' disabled':'')+'>−</button><span class="q">'+i.quantity+'</span><button type="button" class="sp-q" '+QS+' data-act="inc" aria-label="Mehr"'+(i.quantity>=(lim.maximum||9999)||busy?' disabled':'')+'>+</button>':'')+'<button type="button" class="rm" '+RS+' data-act="rm"'+(busy?' disabled':'')+'>Entfernen</button>';
            if(!ctl){ctl=document.createElement('div');ctl.className='sp-oi-ctl';desc.appendChild(ctl);}
            ctl.setAttribute('data-key',i.key);ctl.setAttribute('data-qty',i.quantity);setHTML(ctl,h);
          });
        });
      }
      function render(){
        var s=state();if(!s)return;
        document.querySelectorAll('.sp-fsh-box').forEach(function(b){var p=b.previousElementSibling;if(!p||!p.classList.contains('wp-block-woocommerce-checkout-order-summary-cart-items-block')){b.remove();}});
        renderControls(s);
        var main=document.querySelector('.wc-block-checkout__main'),top=document.getElementById('sp-fsh-top');
        var show=MIN>0&&s.goods>0&&(!s.country||s.country==='DE');
        if(!show){if(top)top.remove();document.querySelectorAll('.sp-fsh-box').forEach(function(b){b.remove();});return;}
        var reached=s.free||s.basis>=MIN,rest=Math.max(0,MIN-s.basis),pct=Math.min(100,Math.round(s.basis/MIN*100));
        var sg=reached?{items:[],pair:false}:suggestions(s,rest),sug=sg.items;
        if(main){
          if(!top){top=document.createElement('div');top.id='sp-fsh-top';top.lang='de';}
          if(top.parentNode!==main){main.insertBefore(top,main.firstChild);}
          top.className=reached?'ok':'';
          setHTML(top,reached?'<p class="t"><span>✓ <b>Gratisversand</b> – deine Bestellung wird kostenlos versendet.</span></p>'
            :'<p class="t"><span>🚚 Noch <b>'+money(rest,s.totals)+'</b> bis zum <b>Gratisversand</b></span>'+(sug.length?'<a href="#" data-fsh-jump>Vorschläge ↓</a>':'')+'</p><div class="sp-fsh-bar"><span style="width:'+pct+'%"></span></div>');
        }
        var boxHTML='';
        if(!reached&&sug.length){
          boxHTML='<p class="h">🚚 Für <b>Gratisversand</b> fehlen noch <b>'+money(rest,s.totals)+'</b>'+(sg.pair?' – <b>mit beidem zusammen</b> geschafft:':'')+'</p>';
          sug.forEach(function(x){
            boxHTML+='<div class="it">'+(x.img?'<img src="'+esc(x.img)+'" alt="">':'')+'<div class="n">'+esc(x.name)+'<small>'+(x.why?esc(x.why)+' · ':'')+(x.kind==='more'?'+ ':'')+money(x.price,s.totals)+(x.closes?' · <b>✓ Gratisversand</b>':'')+'</small></div><button type="button" '+PS+' aria-label="Hinzufügen" data-pid="'+(x.pid||0)+'" data-price="'+x.price.toFixed(2)+'" '+(x.kind==='more'?'data-more="'+esc(x.key)+'" data-qty="'+x.qty+'"':'data-add="'+x.id+'"')+(busy?' disabled':'')+'>+</button></div>';
          });
        }
        var seen=[];
        document.querySelectorAll('.wc-block-components-order-summary').forEach(function(sum){
          var block=sum.closest('.wp-block-woocommerce-checkout-order-summary-block')||sum;
          if(seen.indexOf(block)>-1)return;seen.push(block);
          var anchor=block.querySelector('.wp-block-woocommerce-checkout-order-summary-cart-items-block');
          if(!anchor)return;
          var box=anchor.nextElementSibling&&anchor.nextElementSibling.classList.contains('sp-fsh-box')?anchor.nextElementSibling:null;
          if(!boxHTML){if(box)box.remove();return;}
          if(!box){box=document.createElement('div');box.className='sp-fsh-box wc-block-components-totals-wrapper';box.lang='de';}
          if(box.previousSibling!==anchor){anchor.parentNode.insertBefore(box,anchor.nextSibling);}
          setHTML(box,boxHTML);
          if(!viewed&&box.offsetParent!==null){viewed=true;ev('view');}
        });
      }
      /* Messung fuer den Analyse-Tab: Vorschlaege gesehen (1x pro Seitenaufruf) und "+" getippt */
      var AJ=<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,viewed=false;
      function ev(t,b){try{var f=new FormData();f.append('action','sp_fsh_ev');f.append('t',t);if(b){f.append('pid',b.getAttribute('data-pid')||0);f.append('k',b.hasAttribute('data-more')?'more':'add');f.append('p',b.getAttribute('data-price')||0);}
        if(!(navigator.sendBeacon&&navigator.sendBeacon(AJ,f))){fetch(AJ,{method:'POST',body:f,keepalive:true,credentials:'same-origin'});}}catch(x){}}
      function run(p){busy=true;render();Promise.resolve(p).catch(function(){location.reload();}).then(function(){busy=false;render();});}
      document.addEventListener('click',function(e){
        var j=e.target.closest&&e.target.closest('[data-fsh-jump]');
        if(j){e.preventDefault();var b=[].filter.call(document.querySelectorAll('.sp-fsh-box'),function(x){return x.offsetParent!==null;})[0];if(b)b.scrollIntoView({behavior:'smooth',block:'start'});return;}
        var b=e.target.closest&&e.target.closest('.sp-fsh-box button,.sp-oi-ctl button');if(!b||busy||b.disabled)return;
        e.preventDefault();var d=act();if(!d)return;
        if(b.hasAttribute('data-add')||b.hasAttribute('data-more')){ev('click',b);}
        if(b.hasAttribute('data-add')){run(d.addItemToCart(+b.getAttribute('data-add'),1));return;}
        if(b.hasAttribute('data-more')){run(d.changeCartItemQuantity(b.getAttribute('data-more'),+b.getAttribute('data-qty')+1));return;}
        var ctl=b.closest('.sp-oi-ctl'),key=ctl.getAttribute('data-key'),q=+ctl.getAttribute('data-qty'),a=b.getAttribute('data-act');
        if(a==='rm'){run(d.removeItemFromCart(key));}
        else if(a==='inc'){run(d.changeCartItemQuantity(key,q+1));}
        else if(a==='dec'&&q>1){run(d.changeCartItemQuantity(key,q-1));}
      });
      // Drosseln statt entprellen: der Warenkorb-Store meldet staendig Aenderungen, ein
      // Entprell-Timer wuerde dadurch nie ablaufen.
      var t=null;function soon(){if(t)return;t=setTimeout(function(){t=null;safe();},200);}
      function safe(){try{render();}catch(e){}}
      (function wait(n){
        if(window.wp&&wp.data&&wp.data.subscribe&&sel()){wp.data.subscribe(soon);new MutationObserver(soon).observe(document.body,{childList:true,subtree:true});safe();return;}
        if(n<60)setTimeout(function(){wait(n+1);},250);
      })(0);
    })();
    </script>
    <?php
}, 40);
