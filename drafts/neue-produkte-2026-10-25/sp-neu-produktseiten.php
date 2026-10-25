<?php
/**
 * Plugin Name: SP Neue Produktseiten
 * Description: (2026-10-25) Produktseiten für Cagrilintid (1051), Melanotan II (1053) und GLOW (1055) – per WooCommerce-Hooks
 *   gebaut wie sp-pen-nadeln-page.php (keine Elementor-Vorlage). Inhalte im selben HTML wie die anderen Peptid-Seiten
 *   (Hero, Kaufbox #rx-buybox, Tabs, Kundenstimmen, FAQ, Zusammen gekauft, Hinweis); das echte WooCommerce-Formular wird per
 *   JS in die Kaufbox umgehängt. Das Redesign (sp-product-preview, pp.js) erkennt die Hook-Seite automatisch (.pp-hook).
 *   Quelle: drafts/neue-produkte-2026-10-25/ (texts.py → gen_tpl.py → gen_hook.py).
 */
if (!defined('ABSPATH')) exit;

function sp_nps_pages() {
    static $p = null;
    if ($p !== null) return $p;
    $p = [];
    $p[1051] = [
        'img' => "https://peptrium.com/wp-content/uploads/2026/10/cagrilintid-peptrium.png",
        'alt' => "Cagrilintid \u2013 Peptrium",
        'hero' => <<<'SPHTML'

<style>
#motsc-kundenstimmen{scroll-margin-top:100px}
.sp-reta-rating{display:flex;align-items:center;gap:8px;margin-bottom:10px;text-decoration:none;cursor:pointer;width:fit-content}
.sp-rate-stars{position:relative;display:inline-block;line-height:1;font-size:16px;letter-spacing:2px}
.sp-rate-stars-bg{color:#DCDEE0}
.sp-rate-stars-fg{position:absolute;top:0;left:0;overflow:hidden;white-space:nowrap;color:#f5a623}
.sp-reta-rating-num{font-size:14px;font-weight:700;color:#0D0F12}
.sp-reta-rating-count{font-size:13px;color:#4B5157;text-decoration:underline;text-decoration-color:#A8B0B9;text-underline-offset:2px}
.sp-reta-rating:hover .sp-reta-rating-count{color:#0D0F12}
.sp-reta-hero{font-family:'Sora',sans-serif}
.sp-reta-title{font-size:28px;font-weight:700;color:#0D0F12;margin:0 0 6px;line-height:1.2}
.sp-reta-subtitle-line{font-size:14px;font-weight:600;color:#4B5157;margin:0 0 18px}
.sp-reta-checks{display:flex;flex-direction:column;gap:10px;margin:0 0 22px}
.sp-reta-check-item{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#0D0F12}
.sp-reta-check-item svg{width:18px;height:18px;color:#16a34a;flex-shrink:0}
#sp-reta-buybox-el{border:1px solid #DCDEE0;border-radius:16px;padding:22px 24px;margin-bottom:20px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
#sp-reta-buybox-el .woocommerce{margin:0}
#sp-reta-buybox-el .price{font-family:'Sora',sans-serif;font-size:30px;font-weight:700;color:#0D0F12;display:block;margin-bottom:16px}
#sp-reta-buybox-el .price ins{text-decoration:none;color:#0D0F12}
#sp-reta-buybox-el form.cart{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
#sp-reta-buybox-el .quantity{margin:0}
#sp-reta-buybox-el .quantity .qty{width:64px;padding:12px 10px;border:1px solid #DCDEE0;border-radius:10px;font-size:15px;text-align:center}
#sp-reta-buybox-el .single_add_to_cart_button,
#sp-reta-buybox-el button[type="submit"]{border:none!important;padding:14px 28px!important;border-radius:10px!important;font-weight:700!important;font-size:15px!important;cursor:pointer;position:relative;overflow:hidden;transition:transform .2s}
#sp-reta-buybox-el .single_add_to_cart_button:hover{transform:translateY(-2px)}
@keyframes sp-reta-shine{0%,55%{left:-75%}100%{left:150%}}
.sp-reta-qualitybox{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:16px;padding:20px 22px;margin-bottom:16px}
.sp-reta-qualitybox h4{margin:0 0 4px;font-size:16px;font-weight:700;color:#0D0F12}
.sp-reta-qualitybox p{margin:0 0 10px;font-size:13.5px;color:#4B5157}
.sp-reta-qualitybox .sp-reta-check{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #DCDEE0;border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;color:#0D0F12}
.sp-reta-dhl{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(180deg,#0D0F12 0%,#1C1F23 100%);color:#fff;font-size:13.5px;font-weight:600;padding:10px 18px;border-radius:12px;margin-bottom:18px}
.sp-reta-dhl .sp-dhl-shine{background-image:linear-gradient(100deg,#FFFFFF 0%,#C7CCD1 35%,#FFFFFF 50%,#C7CCD1 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;font-weight:700;animation:sp-tb-shine2 3.2s linear infinite}
@keyframes sp-tb-shine2{0%{background-position:200% center}100%{background-position:0% center}}
.sp-reta-disclaimer{font-size:12.5px;color:#4B5157;line-height:1.5;max-width:520px}
@media(max-width:782px){.sp-reta-buybox .price{font-size:26px}}
</style>

<div class="sp-reta-hero">
  <a href="#motsc-kundenstimmen" class="sp-reta-rating">
    <span class="sp-rate-stars"><span class="sp-rate-stars-bg">&#9733;&#9733;&#9733;&#9733;&#9733;</span><span class="sp-rate-stars-fg" style="width:96%">&#9733;&#9733;&#9733;&#9733;&#9733;</span></span>
    <span class="sp-reta-rating-num">4,8</span>
    <span class="sp-reta-rating-count">(27 Bewertungen)</span>
  </a>
  <h1 class="sp-reta-title">Cagrilintid</h1>
  <p class="sp-reta-subtitle-line">Forschungspeptid &ndash; Nicht f&uuml;r den menschlichen Verzehr</p>
  <div class="sp-reta-checks">
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>99 % Reinheit (HPLC)</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>LC-MS Identit&auml;tsverifizierung</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>Chargenspezifisches Analysezertifikat (COA)</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>Diskrete Verpackung &amp; schneller Versand</span></div>
  </div>
</div>

SPHTML,
        'bb' => <<<'SPHTML'
<style>
.rx-buybox{border:1px solid #DCDEE0;border-radius:16px;padding:22px 24px;margin-bottom:14px;font-family:'Sora',sans-serif;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.rx-price-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px}
.rx-price-block{display:flex;align-items:baseline;gap:10px}
.rx-price{font-size:30px;font-weight:700;color:#0D0F12}
.rx-price-note{font-size:13px;color:#4B5157}
.rx-stock{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#16a34a}
.rx-stock-dot{width:9px;height:9px;border-radius:50%;background:#2bdc6b;display:inline-block;animation:rx-blink 1.4s ease-in-out infinite}
@keyframes rx-blink{
  0%,100%{opacity:1;box-shadow:0 0 6px 2px rgba(43,220,107,.9),0 0 16px 6px rgba(43,220,107,.55)}
  50%{opacity:.55;box-shadow:0 0 3px 1px rgba(43,220,107,.5),0 0 6px 2px rgba(43,220,107,.22)}
}
@keyframes rx-amt-shine{0%{background-position:200% center}100%{background-position:0% center}}
.rx-amt-shine{background-image:linear-gradient(100deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;font-weight:700;animation:rx-amt-shine 2.6s linear infinite;white-space:nowrap}
.rx-shipbadges{margin-bottom:16px}
.rx-shipbadges .sp-reta-dhl{padding:4px 10px 4px 4px;font-size:10.5px;font-weight:600;gap:7px;margin-bottom:0;border-radius:7px;background:#F2F3F4!important;color:#0D0F12!important;border:1px solid #DCDEE0;position:relative;overflow:hidden;box-shadow:none}
.rx-shipbadges .sp-dhl-shine{background:none!important;-webkit-text-fill-color:#0D0F12!important;color:#0D0F12!important;animation:none!important;font-weight:700!important}
.rx-shipbadges .sp-dhl-iconwrap{display:inline-flex;align-items:center;justify-content:center;width:19px;height:19px;background:#FFCC00;border-radius:5px;flex-shrink:0}
.rx-shipbadges .sp-reta-dhl svg{width:11px;height:11px}
.rx-shipbadges .sp-reta-dhl svg{width:15px;height:15px}
.rx-tiers-head{font-size:13px;font-weight:700;color:#363C42;text-transform:uppercase;letter-spacing:.03em;margin-bottom:10px}
.rx-tiers{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:18px}
.rx-tiers.rx-tiers--single{grid-template-columns:repeat(4,1fr);margin-bottom:18px}
.rx-tier-wrap{position:relative}
.rx-tier{position:relative;overflow:hidden;border:1.5px solid #DCDEE0;border-radius:12px;background:#fff;padding:14px 6px 10px;display:flex;flex-direction:column;align-items:center;cursor:pointer;transition:border-color .15s,background .15s;width:100%;box-sizing:border-box}
.rx-tier:hover{border-color:#0D0F12}
.rx-tier.is-active{border-color:#0D0F12;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%)}
.rx-tier.is-active .rx-tier__q{color:#fff}
.rx-tier.is-active .rx-tier__unit{color:#C7CCD1}
.rx-tier.is-active .rx-tier__disc{color:#2f7d4f}

.rx-tier--static{cursor:default}
.rx-tier__flag{position:absolute;top:-11px;left:50%;transform:translateX(-50%);z-index:2;overflow:hidden;background:linear-gradient(135deg,#F2F3F4 0%,#A8B0B9 100%);font-size:10px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap;box-shadow:0 2px 8px rgba(13,15,18,.35)}
.rx-tier__flag-text{background-image:linear-gradient(100deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;font-weight:700;animation:rx-amt-shine 2.6s linear infinite}
.rx-tier__flag::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(13,15,18,.15) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-reta-shine 3.2s ease-in-out infinite;pointer-events:none}
.rx-tier__disc{font-size:10.5px;font-weight:700;color:#2f7d4f;margin-bottom:2px}
.rx-tier__q{font-size:18px;font-weight:700;color:#0D0F12}
.rx-tier__unit{font-size:11px;color:#4B5157;font-weight:600}
.rx-buysum{display:flex;flex-direction:column;gap:8px;width:100%;box-sizing:border-box;background:linear-gradient(135deg,#2E3237 0%,#454B52 100%);border-radius:12px;padding:12px 16px;margin-bottom:8px}
.rx-buysum-top{display:flex;align-items:center;justify-content:space-between;gap:16px}
.rx-buysum__total{display:flex;align-items:baseline;gap:8px;text-align:left}
.rx-buysum__label{font-size:11.5px;color:rgba(255,255,255,.55);font-weight:600;text-transform:uppercase;letter-spacing:.02em}
.rx-buysum__total b{font-size:26px;color:#FFFFFF;white-space:nowrap;font-weight:700;line-height:1.1}
.rx-sv{background:#2f7d4f;color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;white-space:nowrap;width:fit-content}
.rx-shiphint{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:#4B5157;flex-wrap:wrap;margin-bottom:12px}
.rx-shiphint.is-done{color:#2f7d4f}
.rx-shiphint-ic{width:17px;height:17px;flex-shrink:0}
.rx-buycta{display:flex;gap:10px;align-items:stretch;margin-bottom:16px}
.rx-qtybox{display:inline-flex;align-items:stretch;border:1px solid rgba(255,255,255,.18);border-radius:10px;overflow:hidden;box-sizing:border-box;flex-shrink:0}
.rx-qtybox button{-webkit-appearance:none;appearance:none;box-sizing:border-box;width:32px;height:36px;border:none;border-radius:0;background:#FFFFFF;color:#0D0F12;font-size:15px;line-height:1;cursor:pointer;padding:0}
.rx-qtybox button:first-child{border-right:1px solid rgba(255,255,255,.18)}
.rx-qtybox button:last-child{border-left:1px solid rgba(255,255,255,.18)}
.rx-qtybox input{-webkit-appearance:none;appearance:none;box-sizing:border-box;width:36px;height:36px;border:none;border-radius:0;text-align:center;font-size:13.5px;font-weight:700;color:#0D0F12;background:#fff;padding:0}
.rx-qtybox input[type=number]::-webkit-inner-spin-button,
.rx-qtybox input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
.rx-qtybox input[type=number]{-moz-appearance:textfield}
.rx-cartslot #sp-reta-buybox-el{display:block;width:100%;height:100%;border:none!important;padding:0!important;margin:0!important;box-shadow:none!important;border-radius:0!important;background:transparent!important}
.rx-pay-in{border-top:1px solid #DCDEE0;padding-top:16px;margin-top:14px}
.rx-pay-in__k{display:block;font-size:11.5px;color:#4B5157;font-weight:600;margin-bottom:8px}
.rx-pay-in__row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.rx-paychip{display:inline-flex;align-items:center;gap:6px;background:#F2F3F4;border:1px solid #DCDEE0;border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700;color:#4B5157}.rx-paychip svg{width:14px;height:14px;flex-shrink:0}.rx-paychip--vorkasse svg{color:#2E7DD1}.rx-paychip--krypto svg{color:#F7931A}
.rx-trustbadges{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:14px}
.rx-trustbadge{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;background:#F9FAFA;border:1px solid #DCDEE0;border-radius:8px;padding:7px 4px;text-align:center}
.rx-trustbadge svg{width:15px;height:15px;color:#0D0F12;flex-shrink:0}.rx-trustbadge:nth-child(1) svg{color:#2E7DD1}.rx-trustbadge:nth-child(2) svg{color:#1F7A4D}.rx-trustbadge:nth-child(3) svg{color:#7C3AED}.rx-trustbadge:nth-child(4) svg{color:#8B5E3C}
.rx-trustbadge span{font-size:8px;font-weight:700;letter-spacing:.01em;color:#4B5157;line-height:1.15}
.rx-disclaimer{font-size:12px;color:#4B5157;line-height:1.5;margin:0}.rx-social-proof{display:flex;align-items:center;gap:10px;margin:14px 0 0;padding-top:14px;border-top:1px solid #DCDEE0;}.rx-sp-avatars{display:flex;flex-shrink:0;}.rx-sp-avatar{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 0 0 1px #DCDEE0;margin-left:-8px;box-sizing:border-box;overflow:hidden;}.rx-sp-avatar:first-child{margin-left:0;}.rx-sp-avatar img{width:100%;height:100%;object-fit:cover;display:block;}.rx-sp-text{font-size:12.5px;font-weight:700;color:#0D0F12;}
#sp-reta-buybox-el .price{display:none!important}
#sp-reta-buybox-el .quantity{display:none!important;position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;margin:0!important;padding:0!important}
#sp-reta-buybox-el .added_to_cart,
#sp-reta-buybox-el .wc-forward,
#sp-reta-buybox-el a.added_to_cart{display:none!important}
#sp-reta-buybox-el form.cart{display:block!important;height:100%}
#sp-reta-buybox-el .single_add_to_cart_button{width:100%;height:46px;justify-content:center;display:flex!important;align-items:center;gap:8px;margin:0!important;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%)!important;color:#fff!important;white-space:nowrap;font-size:15.5px;font-weight:600;box-sizing:border-box}
#sp-reta-buybox-el .single_add_to_cart_button::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(255,255,255,.45) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-reta-shine 3.2s ease-in-out infinite;pointer-events:none}
.rx-cartslot{flex:1;min-width:0}
.rx-addons{margin-bottom:16px}
.rx-addon-row{display:flex;flex-direction:column;gap:8px;padding:10px 0;border-bottom:1px solid #F2F3F4}
.rx-addon-row:last-child{border-bottom:none}
.rx-addon-top{display:flex;align-items:center;gap:10px}
.rx-addon-img{width:36px;height:36px;border-radius:10px!important;object-fit:cover;background:#F2F3F4;flex-shrink:0}
.rx-addon-name{font-size:13.5px;font-weight:600;color:#0D0F12;flex:1;min-width:0}
.rx-addon-btn{font-family:'Sora',sans-serif;font-size:12px;font-weight:600;border-radius:999px;padding:7px 12px;cursor:pointer;border:1px solid #DCDEE0;background:#fff;color:#4B5157;transition:border-color .15s,background .15s,color .15s;white-space:nowrap;flex-shrink:0}
.rx-addon-btn.is-active{background:#0D0F12;color:#fff;border-color:#0D0F12}
.rx-addon-btn:not(.is-active):hover{border-color:#A8B0B9}
.rx-addon-price{opacity:.85}
.rx-addon-btn-remove{display:none}
.rx-addon-btn.is-active .rx-addon-btn-add{display:none}
.rx-addon-btn.is-active .rx-addon-btn-remove{display:inline}
.rx-addon-qty{display:none;align-items:center;gap:10px;margin-left:46px}
.rx-addon-row.is-active .rx-addon-qty{display:flex}
.rx-addon-qtybtn{width:26px;height:26px;border-radius:8px;border:1px solid #DCDEE0;background:#fff;color:#0D0F12;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;line-height:1;padding:0}
.rx-addon-qtybtn:hover{border-color:#A8B0B9}
.rx-addon-qtyval{font-size:13px;font-weight:700;color:#0D0F12;min-width:16px;text-align:center}
@media(max-width:480px){.rx-tier__q{font-size:15px}.rx-price{font-size:26px}.rx-buysum__total{font-size:13px}.rx-buysum__total b{font-size:16px}.rx-addon-name{font-size:12.5px}.rx-addon-btn{font-size:11px;padding:6px 9px}}
</style>
<div class="rx-buybox" id="rx-buybox">
  <div class="rx-price-row">
    <div class="rx-price-block">
      <span class="rx-price" id="rx-price">104,90&nbsp;&euro;</span>
      <span class="rx-price-note">zzgl. Versand</span>
    </div>
    <div class="rx-stock"><span class="rx-stock-dot"></span> Auf Lager &middot; sofort versandfertig</div>
  </div>

  <div class="rx-shipbadges">
    <div class="sp-reta-dhl">
      <span class="sp-dhl-iconwrap"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D40511" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="15" height="11"/><path d="M16 10h3l3 3v4h-6z"/><circle cx="6" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/></svg></span>
      <span>DHL PREMIUM &middot; <span class="sp-dhl-shine">2 Tage Lieferung</span></span>
    </div>
  </div>

  <div class="rx-tiers-head">Volumen</div>
  <div class="rx-tiers rx-tiers--single">
    <div class="rx-tier is-active rx-tier--static">
      <span class="rx-tier__q">10</span><span class="rx-tier__unit">mg</span>
    </div>
  </div>

  <div class="rx-tiers-head">Menge &amp; Vorteil w&auml;hlen</div>
  <div class="rx-tiers" id="rx-tiers">
    <button type="button" class="rx-tier is-active" data-q="1" data-disc="0">
      <span class="rx-tier__disc">Standard</span>
      <span class="rx-tier__q">1</span><span class="rx-tier__unit">Vial</span>
    </button>
    <div class="rx-tier-wrap">
      <span class="rx-tier__flag"><span class="rx-tier__flag-text">Beliebt</span></span>
      <button type="button" class="rx-tier" data-q="3" data-disc="10">
        <span class="rx-tier__disc">&minus;10%</span>
        <span class="rx-tier__q">3</span><span class="rx-tier__unit">Vials</span>
      </button>
    </div>
    <button type="button" class="rx-tier" data-q="5" data-disc="15">
      <span class="rx-tier__disc">&minus;15%</span>
      <span class="rx-tier__q">5</span><span class="rx-tier__unit">Vials</span>
    </button>
    <button type="button" class="rx-tier" data-q="10" data-disc="20">
      <span class="rx-tier__disc">&minus;20%</span>
      <span class="rx-tier__q">10</span><span class="rx-tier__unit">Vials</span>
    </button>
  </div>

  <div class="rx-addons" id="rx-addons">
    <div class="rx-tiers-head">Zubeh&ouml;r</div>
    <div class="rx-addon-row" data-field="sp_addon_bacwater" data-price="9.90">
      <div class="rx-addon-top">
        <img class="rx-addon-img" src="https://peptrium.com/wp-content/uploads/2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-150x150.png" alt="">
        <span class="rx-addon-name">Bac Water 3&nbsp;ml</span>
        <button type="button" class="rx-addon-btn rx-addon-btn--add" data-choice="1"><span class="rx-addon-btn-add">Hinzuf&uuml;gen <span class="rx-addon-price">+9,90&nbsp;&euro;</span></span><span class="rx-addon-btn-remove">Entfernen</span></button>
      </div>
      <div class="rx-addon-qty">
        <button type="button" class="rx-addon-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <span class="rx-addon-qtyval">1</span>
        <button type="button" class="rx-addon-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
    <div class="rx-addon-row" data-field="sp_addon_insulin" data-price="9.90">
      <div class="rx-addon-top">
        <img class="rx-addon-img" src="https://peptrium.com/wp-content/uploads/2026/08/insulinspritze-10er-pack-wide-150x150.png" alt="">
        <span class="rx-addon-name">Insulinspritze 10er Pack</span>
        <button type="button" class="rx-addon-btn rx-addon-btn--add" data-choice="1"><span class="rx-addon-btn-add">Hinzuf&uuml;gen <span class="rx-addon-price">+9,90&nbsp;&euro;</span></span><span class="rx-addon-btn-remove">Entfernen</span></button>
      </div>
      <div class="rx-addon-qty">
        <button type="button" class="rx-addon-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <span class="rx-addon-qtyval">1</span>
        <button type="button" class="rx-addon-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
  </div>

  <div class="rx-shiphint" id="rx-shiphint"></div>

  <div class="rx-buysum">
    <div class="rx-buysum-top">
      <div class="rx-buysum__total"><span class="rx-buysum__label">Gesamt</span><b id="rx-total">104,90&nbsp;&euro;</b></div>
      <div class="rx-qtybox">
        <button type="button" class="rx-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <input type="number" id="rx-qtyfield" min="1" step="1" value="1" readonly>
        <button type="button" class="rx-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
    <span class="rx-sv" id="rx-save" style="display:none">Du sparst 10,47&nbsp;&euro;</span>
  </div>

  <div class="rx-buycta">
    <div class="rx-cartslot" id="rx-cartrow"></div>
  </div>

  <div class="rx-social-proof" id="rx-social-proof">
    <div class="rx-sp-avatars">
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-4.jpg" alt="" loading="lazy"></span>
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-5.jpg" alt="" loading="lazy"></span>
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-3.jpg" alt="" loading="lazy"></span>
    </div>
    <span class="rx-sp-text">&Uuml;ber 2.500+ zufriedene Kunden</span>
  </div>

  <div class="rx-pay-in">
    <div class="rx-trustbadges">
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v7.31"/><path d="M14 9.3V1.99"/><path d="M8.5 2h7"/><path d="M14 9.3a6.5 6.5 0 1 1-4 0"/><path d="M5.58 16.5h12.85"/></svg><span>HPLC &ge;99%</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg><span>COA</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18h8"/><path d="M3 22h18"/><path d="M14 22a7 7 0 1 0 0-14h-1"/><path d="M9 14h2"/><path d="M9 12a4 4 0 0 0-4 4v2"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg><span>LC-MS</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73Z"/><path d="M3.29 7 12 12l8.71-5"/><path d="M12 22V12"/></svg><span>Diskret</span></div>
    </div>
    <span class="rx-pay-in__k">Sichere Bezahlung</span>
    <div class="rx-pay-in__row">
      <span class="rx-paychip rx-paychip--vorkasse"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>Vorkasse</span>
      <span class="rx-paychip rx-paychip--krypto"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.638 14.904c-1.602 6.43-8.113 10.34-14.542 8.736C2.67 22.05-1.244 15.525.362 9.105 1.962 2.67 8.475-1.243 14.9.358c6.43 1.605 10.342 8.115 8.738 14.548v-.002zm-6.35-4.613c.24-1.59-.974-2.45-2.64-3.03l.54-2.153-1.315-.33-.525 2.107c-.345-.087-.705-.167-1.064-.25l.526-2.127-1.32-.33-.54 2.165c-.285-.065-.565-.13-.84-.2l-1.815-.45-.35 1.407s.975.225.955.236c.535.136.63.486.615.766l-.615 2.46c.035.01.08.024.13.048l-.13-.033-.865 3.46c-.065.164-.23.41-.606.316.015.02-.96-.24-.96-.24l-.655 1.51 1.71.426.93.242-.54 2.19 1.32.327.54-2.17c.36.1.705.19 1.05.273l-.51 2.154 1.32.33.545-2.19c2.24.427 3.93.257 4.64-1.774.57-1.63-.03-2.57-1.21-3.184.86-.198 1.51-.76 1.68-1.93h.01zm-3.01 4.22c-.404 1.64-3.157.75-4.05.53l.72-2.9c.896.23 3.757.67 3.33 2.37zm.41-4.24c-.37 1.49-2.662.735-3.405.55l.654-2.64c.744.18 3.137.53 2.75 2.084v.006z"/></svg>Krypto</span>
    </div>
    <p class="rx-disclaimer">Nicht f&uuml;r den menschlichen oder veterin&auml;rmedizinischen Gebrauch. Nur f&uuml;r Laborforschungszwecke.</p>
  </div>
</div>

<script>
(function(){
  var unitPrice = 104.90;
  var freeShipThreshold = 150;
  var truckIcon = '<svg class="rx-shiphint-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="15" height="11"/><path d="M16 10h3l3 3v4h-6z"/><circle cx="6" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/></svg>';

  function fmt(n){
    return n.toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' €';
  }

  function hideRealQty(){
    var el = document.querySelector('#sp-reta-buybox-el .quantity');
    if (el) {
      el.setAttribute('style', 'display:none!important;position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;margin:0!important;padding:0!important;');
    }
    var links = document.querySelectorAll('#sp-reta-buybox-el .added_to_cart, #sp-reta-buybox-el .wc-forward');
    links.forEach(function(l){ l.style.display = 'none'; });
  }

  function init(){
    var cartRow = document.getElementById('rx-cartrow');
    var realBox = document.getElementById('sp-reta-buybox-el');
    if (cartRow && realBox) { cartRow.appendChild(realBox); }
    hideRealQty();

    function ensureAddonInputs(){
      var form = document.querySelector('#sp-reta-buybox-el form.cart');
      if (!form) return null;
      document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
        var field = row.getAttribute('data-field');
        if (!form.querySelector('input[name="' + field + '"]')) {
          var inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = field;
          inp.value = '0';
          form.appendChild(inp);
        }
      });
      return form;
    }

    document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
      var field = row.getAttribute('data-field');
      var addBtn = row.querySelector('.rx-addon-btn--add');
      var qtyValEl = row.querySelector('.rx-addon-qtyval');
      var qty = 0;
      function render(){
        var active = qty > 0;
        row.classList.toggle('is-active', active);
        addBtn.classList.toggle('is-active', active);
        if (qtyValEl) qtyValEl.textContent = qty || 1;
        var form = ensureAddonInputs();
        if (form) {
          var inp = form.querySelector('input[name="' + field + '"]');
          if (inp) inp.value = String(qty);
        }
        if (qtyField) { update(parseInt(qtyField.value, 10)); }
      }
      addBtn.addEventListener('click', function(){ qty = addBtn.classList.contains('is-active') ? 0 : 1; render(); });
      row.querySelectorAll('.rx-addon-qtybtn').forEach(function(btn){
        btn.addEventListener('click', function(){
          var step = parseInt(btn.getAttribute('data-step'), 10);
          qty = Math.max(0, Math.min(10, qty + step));
          render();
        });
      });
      render();
    });
    ensureAddonInputs();

    if (window.jQuery) {
      jQuery(document.body).on('added_to_cart updated_wc_div wc_fragments_refreshed wc_fragments_loaded', function(){
        setTimeout(hideRealQty, 0);
      });
    }
    var rxObserver = new MutationObserver(function(){ hideRealQty(); });
    rxObserver.observe(document.body, {childList:true, subtree:true});

    var qtyField = document.getElementById('rx-qtyfield');
    var totalEl = document.getElementById('rx-total');
    var saveEl = document.getElementById('rx-save');
    var priceEl = document.getElementById('rx-price');
    var shiphintEl = document.getElementById('rx-shiphint');
    var tiers = document.querySelectorAll('#rx-tiers .rx-tier');
    if (!qtyField) return;
    priceEl.textContent = fmt(unitPrice);

    function findRealQtyInput(){
      return document.querySelector('#sp-reta-buybox-el .quantity input.qty');
    }

    function round2(n){
      return Math.round(n * 100) / 100;
    }

    // Bundle-pack model, matching the cart: only the units matching the largest
    // reached bundle size (1/3/5/10) get that bundle's discount, any extra units
    // above it stay full price. Mirrors sp-quantity-discount.php exactly.
    function bundleFor(qty){
      var best = null;
      tiers.forEach(function(btn){
        var q = parseInt(btn.getAttribute('data-q'),10);
        var d = parseInt(btn.getAttribute('data-disc'),10);
        if (qty >= q && (!best || q > best.q)) {
          best = { q: q, disc: d };
        }
      });
      return best;
    }

    function addonsTotal(){
      var sum = 0;
      document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
        var qtyValEl = row.querySelector('.rx-addon-qtyval');
        var addBtn = row.querySelector('.rx-addon-btn--add');
        var qty = (addBtn && addBtn.classList.contains('is-active') && qtyValEl) ? (parseInt(qtyValEl.textContent, 10) || 0) : 0;
        if (qty > 0) {
          sum += (parseFloat(row.getAttribute('data-price')) || 0) * qty;
        }
      });
      return sum;
    }

    function update(qty){
      qty = Math.max(1, qty);
      qtyField.value = qty;
      var bundle = bundleFor(qty);
      var fullTotal = unitPrice * qty;
      var productTotal = fullTotal;
      if (bundle && bundle.disc > 0) {
        var discountedUnit = round2(unitPrice * (1 - bundle.disc/100));
        var remainder = qty - bundle.q;
        productTotal = round2(discountedUnit * bundle.q + unitPrice * remainder);
      }
      var total = productTotal + addonsTotal();
      totalEl.textContent = fmt(total);
      if (bundle && bundle.disc > 0) {
        saveEl.style.display = '';
        saveEl.textContent = 'Du sparst ' + fmt(fullTotal - productTotal);
      } else {
        saveEl.style.display = 'none';
      }

      var realQtyInput = findRealQtyInput();
      if (realQtyInput) {
        realQtyInput.value = qty;
        realQtyInput.dispatchEvent(new Event('change', {bubbles:true}));
      }

      var remaining = freeShipThreshold - total;
      if (remaining > 0) {
        shiphintEl.innerHTML = truckIcon + '<span>Noch <span class="rx-amt-shine">' + fmt(remaining) + '</span> bis Gratisversand</span>';
        shiphintEl.classList.remove('is-done');
      } else {
        shiphintEl.innerHTML = truckIcon + '<span>Gratisversand</span>';
        shiphintEl.classList.add('is-done');
      }

      tiers.forEach(function(btn){
        btn.classList.toggle('is-active', !!bundle && parseInt(btn.getAttribute('data-q'),10) === bundle.q);
      });
    }

    tiers.forEach(function(btn){
      btn.addEventListener('click', function(){ update(parseInt(btn.getAttribute('data-q'),10)); });
    });

    document.querySelectorAll('.rx-qtybtn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var step = parseInt(btn.getAttribute('data-step'),10);
        update(parseInt(qtyField.value,10) + step);
      });
    });

    update(1);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>

SPHTML,
        'qual' => <<<'SPHTML'

<div class="sp-reta-qualitybox">
  <h4>Unabh&auml;ngig laborgepr&uuml;ft</h4>
    <span class="sp-reta-check">&#10003; Reinheit (HPLC)</span>
</div>

SPHTML,
        'tabs' => <<<'SPHTML'

<style>
.rx-tabsec{max-width:760px;margin:0 auto}
.rx-tabnav{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.rx-tabbtn{border:1px solid #DCDEE0;background:#F2F3F4;color:#4B5157;border-radius:999px;padding:10px 22px;font-family:'Sora',sans-serif;font-weight:700;font-size:14px;cursor:pointer;transition:all .2s;flex:1;min-width:140px;text-align:center}
.rx-tabbtn:hover{border-color:#A8B0B9}
.rx-tabbtn.is-active{background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#fff;border-color:#0D0F12;box-shadow:0 4px 14px rgba(13,15,18,.25)}
.rx-tabcard{border:1px solid #DCDEE0;border-radius:16px;background:#fff;padding:28px 24px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.rx-tabpanel{display:none}
.rx-tabpanel.is-active{display:block}
.rx-tabpanel .sp-reta-desc,.rx-tabpanel .sp-reta-specs{max-width:none;margin:0}
.rx-results{font-family:'Sora',sans-serif}
.rx-results-top{display:flex;align-items:center;gap:28px;flex-wrap:wrap;margin-bottom:22px;padding-bottom:22px;border-bottom:1px solid #DCDEE0}
.rx-results-purity{font-size:44px;font-weight:700;letter-spacing:-0.02em;background:linear-gradient(135deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;animation:rx-res-shine 2.6s linear infinite;white-space:nowrap}
@keyframes rx-res-shine{0%{background-position:200% center}100%{background-position:0% center}}
.rx-results-purity-label{font-size:13.5px;color:#4B5157;font-weight:600;margin-top:2px}
.rx-results-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px}
.rx-results-tile{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:12px;padding:14px 16px}
.rx-results-tile-k{font-size:11.5px;color:#8a9099;font-weight:700;text-transform:uppercase;letter-spacing:.03em;margin-bottom:4px}
.rx-results-tile-v{font-size:14.5px;color:#0D0F12;font-weight:700;display:flex;align-items:center;gap:6px}
.rx-results-check{color:#2f7d4f}
.rx-coa-box{border:1px dashed #DCDEE0;border-radius:12px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;background:#F2F3F4}
.rx-coa-info{font-size:14px;color:#4B5157;font-weight:600}
.rx-coa-info b{display:block;color:#0D0F12;font-size:15px;margin-bottom:2px}
.rx-coa-btn{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #DCDEE0;color:#8a9099;font-family:'Sora',sans-serif;font-weight:700;font-size:13.5px;padding:10px 18px;border-radius:10px;cursor:not-allowed;opacity:.7;white-space:nowrap}
@media(max-width:600px){.rx-results-grid{grid-template-columns:1fr}}
</style>

<div class="rx-tabsec">
  <div class="rx-tabnav" role="tablist">
    <button type="button" class="rx-tabbtn is-active" data-tab="produkt">Produkt</button>
    <button type="button" class="rx-tabbtn" data-tab="lagerung">Lagerung</button>
    <button type="button" class="rx-tabbtn" data-tab="results">Results</button>
    <button type="button" class="rx-tabbtn" data-tab="tools">Tools &amp; Rechner</button>
  </div>

  <div class="rx-tabcard">
    <div class="rx-tabpanel is-active" data-tab="produkt">

<style>
.sp-reta-desc{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif;color:#0D0F12}
.sp-reta-desc h4{font-size:20px;font-weight:700;margin:34px 0 12px;color:#0D0F12}
.sp-reta-desc h4:first-child{margin-top:0}
.sp-reta-desc h3{font-size:24px;font-weight:700;margin:40px 0 14px;color:#0D0F12}
.sp-reta-desc p{font-size:15.5px;line-height:1.7;color:#4B5157;margin:0 0 14px}
.sp-reta-desc strong{color:#0D0F12}
.sp-reta-desc a{color:#0D0F12;text-decoration:underline;text-decoration-color:#A8B0B9;text-underline-offset:2px}
.sp-reta-desc .sp-warn{background:#F2F3F4;border-left:3px solid #A8B0B9;padding:12px 16px;border-radius:8px;font-size:14px;color:#4B5157;margin:18px 0}
.sp-reta-iconrow{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:32px}
.sp-reta-iconrow div{background:#F2F3F4;border-radius:12px;padding:16px 14px;text-align:center;font-size:13.5px;font-weight:600;color:#0D0F12}
@media(max-width:782px){.sp-reta-iconrow{grid-template-columns:repeat(2,1fr)}}
</style>
<div class="sp-reta-desc">
<h3>Wirkung &amp; Forschung</h3>
<p>Cagrilintid ist ein synthetisches, lang wirksames Analogon des Peptidhormons <strong>Amylin</strong>. Eine Fettsäure-Seitenkette (Lipidierung) lässt es an Albumin binden, wodurch es in Studien eine deutlich verlängerte Halbwertszeit zeigt. In der Forschung wird Cagrilintid als Agonist an Amylin- und Calcitonin-Rezeptoren untersucht – häufig im Vergleich oder in Kombination mit GLP-1-Rezeptoragonisten. Damit ist es ein zentrales Analyseobjekt in der Forschung zu Sättigungssignalen, Energiehaushalt und Stoffwechselregulation.</p>
<div class="sp-warn">⚠️ <strong>Nur zu Forschungszwecken.</strong> Nicht für den menschlichen Gebrauch.</div>
</div>



<style>
.sp-reta-specs{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-reta-specs h3{font-size:22px;font-weight:700;color:#0D0F12;margin:0 0 18px;text-align:center}
.sp-reta-specs table{width:100%;table-layout:fixed;border-collapse:collapse;background:#fff;border:1px solid #DCDEE0;border-radius:14px;overflow:hidden}
.sp-reta-specs td{padding:14px 20px;font-size:14.5px;border-bottom:1px solid #DCDEE0;word-break:break-word;overflow-wrap:anywhere}
.sp-reta-specs tr:last-child td{border-bottom:none}
.sp-reta-specs td:first-child{color:#4B5157;font-weight:600;width:45%}
.sp-reta-specs td:last-child{color:#0D0F12;font-weight:600}
</style>
<div class="sp-reta-specs">
<h3>Technische Spezifikationen</h3>
<table>
<tr><td>Produktname</td><td>Cagrilintid</td></tr>
<tr><td>Menge</td><td>10 mg pro Vial</td></tr>
<tr><td>Form</td><td>Lyophilisiertes Pulver</td></tr>
<tr><td>Lagerung</td><td>2–8 °C, langfristig −20 °C</td></tr>
<tr><td>Molekulare Struktur</td><td>Lipidiertes Amylin-Analogon</td></tr>
<tr><td>Analyse</td><td>HPLC / LC-MS</td></tr>
</table>
</div>
    </div>

        <div class="rx-tabpanel" data-tab="lagerung">
<style>
.sp-lager{font-family:'Sora',sans-serif}
.sp-lager-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:26px}
.sp-lager-card{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:14px;padding:20px 22px}
.sp-lager-card h4{margin:0 0 12px;font-size:16px;font-weight:700;color:#0D0F12}
.sp-lager-stat{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #DCDEE0;font-size:13.5px}
.sp-lager-stat:last-of-type{border-bottom:none}
.sp-lager-stat-k{color:#4B5157;font-weight:600}
.sp-lager-stat-v{color:#0D0F12;font-weight:700}
.sp-lager-tips{list-style:none;margin:12px 0 0;padding:0;display:flex;flex-direction:column;gap:8px}
.sp-lager-tips li{display:flex;align-items:flex-start;gap:8px;font-size:13.5px;color:#4B5157;line-height:1.5}
.sp-lager-tips li svg{width:15px;height:15px;color:#2f7d4f;flex-shrink:0;margin-top:2px}
.sp-lager-general-title{font-size:16px;font-weight:700;color:#0D0F12;margin:0 0 14px}
.sp-lager-tipgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.sp-lager-tipcard{background:#fff;border:1px solid #DCDEE0;border-radius:12px;padding:16px;text-align:center}
.sp-lager-tipicon{font-size:22px;margin-bottom:8px}
.sp-lager-tipcard p{margin:0;font-size:12.5px;line-height:1.5;color:#4B5157}
.sp-lager-tipcard strong{display:block;color:#0D0F12;font-size:13px;margin-bottom:4px}
@media(max-width:600px){.sp-lager-row{grid-template-columns:1fr}.sp-lager-tipgrid{grid-template-columns:1fr}}
</style>
<div class="sp-lager">
  <div class="sp-lager-row">
    <div class="sp-lager-card">
      <h4>Als Pulver</h4>
      <div class="sp-lager-stat"><span class="sp-lager-stat-k">Temperatur</span><span class="sp-lager-stat-v">2&ndash;8&nbsp;&deg;C</span></div>
      <ul class="sp-lager-tips">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Immer gek&uuml;hlt lagern</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Empfindliches Peptid</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Lichtgesch&uuml;tzt aufbewahren</li>
      </ul>
    </div>
    <div class="sp-lager-card">
      <h4>Nach Rekonstitution</h4>
      <div class="sp-lager-stat"><span class="sp-lager-stat-k">Temperatur</span><span class="sp-lager-stat-v">2&ndash;8&nbsp;&deg;C (K&uuml;hlschrank)</span></div>
      <ul class="sp-lager-tips">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Kurze Haltbarkeit nach Aufl&ouml;sen</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Kleine Mengen anmischen</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Sofort k&uuml;hlen</li>
      </ul>
    </div>
  </div>
  <div class="sp-lager-general-title">Allgemeine Lagerungstipps</div>
  <div class="sp-lager-tipgrid">
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#128167;</div><strong>BAC-Wasser verwenden</strong><p>Nutze nur steriles BAC-Wasser (Benzylalkohol + Kochsalz) zur Rekonstitution, nicht gew&ouml;hnliches Wasser.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#129532;</div><strong>Septum desinfizieren</strong><p>Wische das Septum vor jeder Entnahme mit einem Alkoholtupfer ab, um Kontamination zu vermeiden.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#10052;&#65039;</div><strong>K&uuml;hlkette nicht unterbrechen</strong><p>Halte die empfohlene Temperatur konsistent &mdash; Temperaturausrei&szlig;er reduzieren die Haltbarkeit drastisch.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#9728;&#65039;</div><strong>Vor Sonnenlicht sch&uuml;tzen</strong><p>Lagere Peptide in dunklen Beh&auml;ltern oder undurchsichtigen Fl&auml;schchen, um Lichtabbau zu verhindern.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#10060;</div><strong>Nicht einfrieren nach Rekonstitution</strong><p>Wiederholtes Gefrieren und Auftauen zerst&ouml;rt die Peptidstruktur &mdash; nur bei Pulver geeignet.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#128197;</div><strong>Verfallsdatum notieren</strong><p>Schreibe das Rekonstitutionsdatum auf jede Flasche &mdash; dies ist entscheidend f&uuml;r die Patientenverteidigung.</p></div>
  </div>
</div>
    </div>

        <div class="rx-tabpanel" data-tab="results">
      <div class="rx-results">
        <div class="rx-results-top">
          <div>
            <div class="rx-results-purity">&ge;99%</div>
            <div class="rx-results-purity-label">HPLC-Reinheit</div>
          </div>
        </div>
        <div class="rx-results-grid">
          <div class="rx-results-tile">
            <div class="rx-results-tile-k">Analyse</div>
            <div class="rx-results-tile-v">HPLC / LC-MS</div>
          </div>
          <div class="rx-results-tile">
            <div class="rx-results-tile-k">Status</div>
            <div class="rx-results-tile-v"><span class="rx-results-check">&#10003;</span> Bestanden</div>
          </div>
        </div>
        <div class="rx-coa-box">
          <div class="rx-coa-info"><b>Analysezertifikat (COA)</b>Chargenspezifisches Pr&uuml;fprotokoll als PDF</div>
          <span class="rx-coa-btn" aria-disabled="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>
            Demn&auml;chst verf&uuml;gbar
          </span>
        </div>
      </div>
    </div>

    <div class="rx-tabpanel" data-tab="tools">
<style>
.sp-tools-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1200px;margin:0 auto}
@media(max-width:1024px){.sp-tools-grid{grid-template-columns:repeat(2,1fr);gap:20px;max-width:700px}}
@media(max-width:580px){.sp-tools-grid{grid-template-columns:1fr;gap:20px;max-width:400px}}
.sp-tool-card,.sp-tool-card:link,.sp-tool-card:visited,.sp-tool-card *{text-decoration:none!important}
.sp-tool-card{background:linear-gradient(160deg,#0D0F12 0%,#2A2E33 100%);border:1px solid rgba(255,255,255,.06);border-radius:16px;padding:36px 28px 32px;text-align:left;transition:all .35s ease;position:relative;overflow:hidden;display:block;color:inherit}
.sp-tool-card:hover{border-color:rgba(199,204,209,.4);transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.35)}
.sp-tool-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#C7CCD1,transparent);opacity:0;transition:opacity .35s}
.sp-tool-card:hover::before{opacity:1}
.sp-tool-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;font-size:24px;background:rgba(168,176,185,.14);color:#C7CCD1}
.sp-tool-card h3{color:#fff!important;font-size:20px;font-weight:700;margin:0 0 10px}
.sp-tool-card p{color:rgba(255,255,255,.55)!important;font-size:14.5px;line-height:1.6;margin:0 0 22px;min-height:66px}
.sp-tool-cta{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(120deg,#FFFFFF 0%,#DCDEE0 100%);color:#0D0F12!important;font-size:14px;font-weight:600;padding:10px 18px;border-radius:10px;transition:all .25s;position:relative;overflow:hidden;animation:sp-tool-cta-glow 2.2s ease-in-out infinite;}
@keyframes sp-tool-cta-glow{0%,100%{box-shadow:0 0 0 rgba(242,243,244,0);}50%{box-shadow:0 0 18px rgba(242,243,244,.6);}}
@media(prefers-reduced-motion:reduce){.sp-tool-cta{animation:none;}}
.sp-tool-cta::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(13,15,18,.18) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-btn-shine 3.2s ease-in-out infinite;pointer-events:none}
@keyframes sp-btn-shine{0%,55%{left:-75%}100%{left:150%}}
.sp-tool-cta:hover{gap:12px}
.sp-tool-cta svg{transition:transform .25s}
.sp-tool-cta:hover svg{transform:translateX(3px)}
</style>
<div class="sp-tools-grid">
    <a href="/peptid-rechner/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="14" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="16" y2="18"/></svg>
      </div>
      <h3>Peptid-Rechner</h3>
      <p>Berechne die exakte Rekonstitution und Dosierung deiner Peptide – schnell, einfach und präzise.</p>
      <span class="sp-tool-cta">Jetzt berechnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
    <a href="/lagerungs-guide/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </div>
      <h3>Lagerungs-Guide</h3>
      <p>Erfahre wie du Peptide optimal lagerst – Temperatur, Haltbarkeit und Rekonstitutions-Tipps.</p>
      <span class="sp-tool-cta">Guide öffnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
  
    <a href="/peptid-lexikon/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/></svg>
      </div>
      <h3>Peptid-Lexikon</h3>
      <p>Steckbriefe und Forschungsgebiete vieler Peptide im Lexikon nachlesen.</p>
      <span class="sp-tool-cta">Lexikon &ouml;ffnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
  </div>

    </div>
  </div>
</div>

<script>
(function(){
  var nav = document.currentScript.previousElementSibling;
  if (!nav || !nav.classList.contains('rx-tabsec')) {
    nav = document.querySelector('.rx-tabsec');
  }
  if (!nav) return;
  var btns = nav.querySelectorAll('.rx-tabbtn');
  var panels = nav.querySelectorAll('.rx-tabpanel');
  btns.forEach(function(btn){
    btn.addEventListener('click', function(){
      var tab = btn.getAttribute('data-tab');
      btns.forEach(function(b){ b.classList.toggle('is-active', b === btn); });
      panels.forEach(function(p){ p.classList.toggle('is-active', p.getAttribute('data-tab') === tab); });
    });
  });
})();
</script>

SPHTML,
        'rev' => <<<'SPHTML'
<div class="rv-wrap" id="rv-wrap">
  <style>
    #rv-wrap {
      --rv-accent: #C7CCD1;
      --rv-star: #f5a623;
      --rv-bg: #000000;
      --rv-bg-soft: rgba(255,255,255,.10);
      --rv-border: rgba(255,255,255,.10);
      --rv-text: #FFFFFF;
      --rv-text-soft: rgba(255,255,255,.55);
      --rv-radius: 16px;
      --rv-speed: 65s;

      max-width: 100vw;
      width: 100vw;
      margin-left: calc(-50vw + 50%);
      margin-right: calc(-50vw + 50%);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      color: var(--rv-text);
      overflow: hidden;
      box-sizing: border-box;
    }
    #rv-wrap *, #rv-wrap *::before, #rv-wrap *::after { box-sizing: border-box; }

    #rv-wrap .rv-mask {
      position: relative;
      overflow: hidden;
    }

    #rv-wrap .rv-track {
      display: flex;
      gap: 20px;
      width: max-content;
      animation: rv-scroll var(--rv-speed) linear infinite;
    }
    #rv-wrap .rv-mask:hover .rv-track {
      animation-play-state: paused;
    }

    @keyframes rv-scroll {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }

    #rv-wrap .rv-card {
      flex: 0 0 300px;
      background: var(--rv-bg);
      border: 1px solid var(--rv-border);
      border-radius: var(--rv-radius);
      padding: 22px 22px 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    #rv-wrap .rv-stars {
      display: flex;
      gap: 3px;
    }
    #rv-wrap .rv-stars svg {
      width: 15px;
      height: 15px;
      fill: var(--rv-star);
    }

    #rv-wrap .rv-text {
      font-size: 0.9rem;
      line-height: 1.55;
      color: var(--rv-text);
      margin: 0;
      flex-grow: 1;
    }

    #rv-wrap .rv-author {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 4px;
    }
    #rv-wrap .rv-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--rv-bg-soft);
      color: var(--rv-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.8rem;
      flex-shrink: 0;
    }
    #rv-wrap .rv-name {
      font-size: 0.85rem;
      font-weight: 700;
      margin: 0;
    }
    #rv-wrap .rv-meta {
      font-size: 0.75rem;
      color: var(--rv-text-soft);
      margin: 0;
    }

    @media (prefers-reduced-motion: reduce) {
      #rv-wrap .rv-track { animation: none; }
    }
  </style>

  <div class="rv-mask">
    <div class="rv-track" id="rv-track"></div>
  </div>
</div>

<script>
(function () {
  var reviews = [
    { name: "Fabian K.", initials: "FK", meta: "Verifizierter Kauf", stars: 5, text: "Schon meine dritte Bestellung und wieder alles top gerne wieder" },
    { name: "Jasmin T.", initials: "JT", meta: "Verifizierter Kauf", stars: 5, text: "Wirklich zufrieden mit der Qualität und dem ganzen Ablauf. Werde definitiv wieder bestellen." },
    { name: "Robert N.", initials: "RN", meta: "Verifizierter Kauf", stars: 4, text: "Alles gut gelaufen, bin zufrieden." },
    { name: "Vanessa B.", initials: "VB", meta: "Verifizierter Kauf", stars: 5, text: "Ich bin jetzt schon bei der dritten Bestellung und bin nach wie vor begeistert von der Qualität, den Zertifikaten und dem schnellen Versand. Absolute Kaufempfehlung, komme auf jeden Fall wieder." },
    { name: "Sebastian R.", initials: "SR", meta: "Verifizierter Kauf", stars: 5, text: "top service komme wieder" },
    { name: "Katharina D.", initials: "KD", meta: "Verifizierter Kauf", stars: 5, text: "Hat alles gepasst, bin sehr zufrieden und werde wieder bestellen." },
    { name: "Marco P.", initials: "MP", meta: "Verifizierter Kauf", stars: 4, text: "Gute Erfahrung gemacht auch wenn der Versand einen Tag länger gedauert hat als angegeben aber inhaltlich alles top" },
    { name: "Nadine S.", initials: "NS", meta: "Verifizierter Kauf", stars: 5, text: "Bestelle hier jetzt regelmäßig weil einfach alles passt Qualität Verpackung und der Support" },
    { name: "Tobias W.", initials: "TW", meta: "Verifizierter Kauf", stars: 5, text: "Sehr zufrieden. Zweite Bestellung war genauso gut wie die erste." },
    { name: "Michaela H.", initials: "MH", meta: "Verifizierter Kauf", stars: 5, text: "Rundum zufrieden, komme auf jeden Fall wieder" }
  ];

  function starSvg() {
    return '<svg viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.4 6 .8-4.3 4.1 1 5.9L10 14.9l-5.3 2.8 1-5.9L1.4 7.7l6-.8L10 1.5z"/></svg>';
  }

  function cardHtml(r) {
    var stars = '';
    for (var i = 0; i < 5; i++) {
      stars += i < r.stars ? starSvg() : '<span style="opacity:.25">' + starSvg() + '</span>';
    }
    return '' +
      '<div class="rv-card">' +
        '<div class="rv-stars">' + stars + '</div>' +
        '<p class="rv-text">' + r.text + '</p>' +
        '<div class="rv-author">' +
          '<div class="rv-avatar">' + r.initials + '</div>' +
          '<div>' +
            '<p class="rv-name">' + r.name + '</p>' +
            '<p class="rv-meta">' + r.meta + '</p>' +
          '</div>' +
        '</div>' +
      '</div>';
  }

  var track = document.getElementById('rv-track');
  var html = reviews.map(cardHtml).join('');
  track.innerHTML = html + html;
})();
</script>
SPHTML,
        'lex' => <<<'SPHTML'
<style>
.sp-sci-section{max-width:900px;margin:32px auto 0;font-family:'Sora',sans-serif;}
.sp-sci-section h3{font-size:24px;font-weight:700;color:#0D0F12;margin:0 0 20px;text-align:center}
.sp-sci-card{background:linear-gradient(137deg,#0D0F12 0%,#1C1F23 100%);border-radius:20px;padding:32px 30px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;position:relative;overflow:hidden;}
.sp-sci-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#C7CCD1,transparent);}
.sp-sci-badge{display:inline-block;background:rgba(255,255,255,.1);color:#C7CCD1;font-size:11.5px;font-weight:700;letter-spacing:2px;padding:5px 14px;border-radius:20px;margin-bottom:12px;text-transform:uppercase;}
.sp-sci-title{font-size:20px;font-weight:700;color:#fff;margin:0 0 8px;}
.sp-sci-desc{font-size:14.5px;color:rgba(255,255,255,.65);line-height:1.6;margin:0;max-width:480px;}
.sp-sci-cta{flex-shrink:0;display:inline-flex;align-items:center;gap:8px;background:#fff;color:#0D0F12;font-weight:700;font-size:14.5px;padding:13px 24px;border-radius:10px;text-decoration:none;white-space:nowrap;}
@media(max-width:600px){.sp-sci-card{padding:26px 22px;}}
</style>
<div class="sp-sci-section">
  <div class="sp-sci-card">
    <div>
      <span class="sp-sci-badge">Ratgeber</span>
      <h4 class="sp-sci-title">Stoffwechsel-Peptide im Vergleich</h4>
      <p class="sp-sci-desc">Retatrutide, Tirzepatide, Semaglutid und Amylin-Analoga – Unterschiede und Forschungsstand im Überblick.</p>
    </div>
    <a href="/ratgeber/retatrutide-tirzepatide-semaglutid/" class="sp-sci-cta">Zum Ratgeber <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
  </div>
</div>
SPHTML,
        'faq' => <<<'SPHTML'

<style>
.sp-reta-faq{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-reta-faq h3{font-size:24px;font-weight:700;color:#0D0F12;margin:0 0 20px;text-align:center}
.sp-faq-item{border:1px solid #DCDEE0;border-radius:12px;margin-bottom:12px;padding:16px 20px;background:#F2F3F4}
.sp-faq-item summary{cursor:pointer;list-style:none;font-weight:700;font-size:15.5px;color:#0D0F12;display:flex;justify-content:space-between;align-items:center}
.sp-faq-item summary::-webkit-details-marker{display:none}
.sp-faq-arrow{font-size:11px;color:#A8B0B9;transition:transform .25s}
.sp-faq-item[open] .sp-faq-arrow{transform:rotate(180deg)}
.sp-faq-item p{margin:12px 0 0;font-size:14.5px;line-height:1.65;color:#4B5157}
</style>
<div class="sp-reta-faq">
<h3>Häufig gestellte Fragen zu Cagrilintid</h3>

  <details class="sp-faq-item">
    <summary>Was ist Cagrilintid?<span class="sp-faq-arrow">▼</span></summary>
    <p>Cagrilintid ist ein synthetisches Analogon des Peptidhormons Amylin. Durch eine Lipidierung bindet es an Albumin und ist dadurch deutlich länger stabil als natürliches Amylin. Es wird ausschließlich im Laborkontext untersucht.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Worin unterscheidet sich Cagrilintid von GLP-1-Analoga?<span class="sp-faq-arrow">▼</span></summary>
    <p>GLP-1-Analoga wirken am GLP-1-Rezeptor, Cagrilintid dagegen an Amylin- und Calcitonin-Rezeptoren. Weil es einen anderen Signalweg anspricht, wird es in Studien häufig mit GLP-1-Rezeptoragonisten verglichen oder kombiniert betrachtet.</p>
  </details>
  <details class="sp-faq-item">
    <summary>In welchen Forschungsbereichen wird Cagrilintid untersucht?<span class="sp-faq-arrow">▼</span></summary>
    <p>Im Mittelpunkt stehen Studien zu Sättigungssignalen, Energiehaushalt, Magenentleerung und Stoffwechselregulation – meist in präklinischen Modellen und klinischen Studien zu Amylin-Analoga.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Wie wird Cagrilintid geliefert?<span class="sp-faq-arrow">▼</span></summary>
    <p>Als lyophilisiertes (gefriergetrocknetes) Pulver im Vial. Für die Arbeit im Labor wird es mit bakteriostatischem Wasser rekonstituiert – die Konzentration berechnest du mit unserem Peptid-Rechner.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Wie wird Cagrilintid gelagert?<span class="sp-faq-arrow">▼</span></summary>
    <p>Lyophilisiert kühl bei 2–8 °C und lichtgeschützt, für längere Zeit bei −20 °C. Nach dem Rekonstituieren im Kühlschrank aufbewahren und nicht wieder einfrieren.</p>
  </details>
</div>
SPHTML,
        'rel' => <<<'SPHTML'

<style>
.sp-rel-section{max-width:900px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-rel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
.sp-rel-title{font-size:22px;font-weight:700;color:#0D0F12;margin:0}
.sp-rel-more{display:inline-flex;align-items:center;gap:6px;background:#F2F3F4;border:1px solid #DCDEE0;color:#0D0F12;font-family:'Sora',sans-serif;font-weight:700;font-size:13px;padding:8px 14px;border-radius:999px;text-decoration:none!important;white-space:nowrap;transition:all .2s;flex-shrink:0}
.sp-rel-more:hover{background:#0D0F12;color:#fff;border-color:#0D0F12}
.sp-rel-more svg{width:14px;height:14px;transition:transform .2s}
.sp-rel-more:hover svg{transform:translateX(2px)}
.sp-rel-dots{display:flex;justify-content:center;gap:6px;margin-top:14px}
.sp-rel-dot{width:6px;height:6px;border-radius:50%;background:#DCDEE0;transition:all .25s}
.sp-rel-dot.is-active{background:#0D0F12;width:18px;border-radius:4px}
.sp-rel-scroll{display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;padding-bottom:6px;scrollbar-width:thin}
.sp-rel-scroll::-webkit-scrollbar{height:6px}
.sp-rel-scroll::-webkit-scrollbar-thumb{background:#DCDEE0;border-radius:10px}
.sp-rel-card{flex:0 0 auto;width:calc((100% - 28px) / 3);min-width:150px;scroll-snap-align:start;background:#fff;border:1px solid #DCDEE0;border-radius:14px;overflow:hidden;text-decoration:none!important;display:block;color:inherit;transition:all .25s}
.sp-rel-card:hover{border-color:#A8B0B9;transform:translateY(-3px);box-shadow:0 8px 20px rgba(13,15,18,.08)}
.sp-rel-card-img{width:100%;aspect-ratio:1/1;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden}
.sp-rel-card-img img{width:72%;height:72%;object-fit:contain}
.sp-rel-card-body{padding:10px 12px 14px;background:#F2F3F4}
.sp-rel-card h3{font-size:13px;font-weight:700;color:#0D0F12;margin:0 0 4px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sp-rel-card-price{font-size:13.5px;font-weight:700;color:#0D0F12}
@media(max-width:640px){.sp-rel-card{width:calc((100% - 14px) / 2)}}
</style>
<div class="sp-rel-section">
  <div class="sp-rel-head">
    <div class="sp-rel-title">Wird h&auml;ufig zusammen gekauft</div>
    <a href="https://peptrium.com/alle-produkte/" class="sp-rel-more">Mehr <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
  </div>
  <div class="sp-rel-scroll">
    <a href="https://peptrium.com/produkt/retatrutide/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png" alt="Retatrutide" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>Retatrutide</h3>
        <div class="sp-rel-card-price">ab 74,90&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/mots-c/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/7255FFFB-6823-45F9-AC96-88637F1411CB-300x300.png" alt="Mots-C" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>Mots-C</h3>
        <div class="sp-rel-card-price">34,90&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/ghk-cu/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png" alt="GHK-Cu" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>GHK-Cu</h3>
        <div class="sp-rel-card-price">ab 39,90&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/bac-water/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png" alt="Bac Water" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>Bac Water</h3>
        <div class="sp-rel-card-price">9,90&nbsp;&euro;</div>
      </div>
    </a>
  </div>
  <div class="sp-rel-dots" id="sp-rel-dots">
    <span class="sp-rel-dot is-active"></span>
    <span class="sp-rel-dot"></span>
    <span class="sp-rel-dot"></span>
    <span class="sp-rel-dot"></span>
  </div>
</div>
<script>
(function(){
  var scrollEl = document.querySelector('.sp-rel-scroll');
  var dots = document.querySelectorAll('.sp-rel-dot');
  if (!scrollEl || !dots.length) return;
  function update(){
    var maxScroll = scrollEl.scrollWidth - scrollEl.clientWidth;
    var progress = maxScroll > 0 ? scrollEl.scrollLeft / maxScroll : 0;
    var idx = Math.round(progress * (dots.length - 1));
    dots.forEach(function(d,i){ d.classList.toggle('is-active', i === idx); });
  }
  scrollEl.addEventListener('scroll', update, {passive:true});
  update();
})();
</script>

SPHTML,
    ];
    $p[1053] = [
        'img' => "https://peptrium.com/wp-content/uploads/2026/10/melanotan-2-peptrium.png",
        'alt' => "Melanotan II (MT-2) \u2013 Peptrium",
        'hero' => <<<'SPHTML'

<style>
#motsc-kundenstimmen{scroll-margin-top:100px}
.sp-reta-rating{display:flex;align-items:center;gap:8px;margin-bottom:10px;text-decoration:none;cursor:pointer;width:fit-content}
.sp-rate-stars{position:relative;display:inline-block;line-height:1;font-size:16px;letter-spacing:2px}
.sp-rate-stars-bg{color:#DCDEE0}
.sp-rate-stars-fg{position:absolute;top:0;left:0;overflow:hidden;white-space:nowrap;color:#f5a623}
.sp-reta-rating-num{font-size:14px;font-weight:700;color:#0D0F12}
.sp-reta-rating-count{font-size:13px;color:#4B5157;text-decoration:underline;text-decoration-color:#A8B0B9;text-underline-offset:2px}
.sp-reta-rating:hover .sp-reta-rating-count{color:#0D0F12}
.sp-reta-hero{font-family:'Sora',sans-serif}
.sp-reta-title{font-size:28px;font-weight:700;color:#0D0F12;margin:0 0 6px;line-height:1.2}
.sp-reta-subtitle-line{font-size:14px;font-weight:600;color:#4B5157;margin:0 0 18px}
.sp-reta-checks{display:flex;flex-direction:column;gap:10px;margin:0 0 22px}
.sp-reta-check-item{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#0D0F12}
.sp-reta-check-item svg{width:18px;height:18px;color:#16a34a;flex-shrink:0}
#sp-reta-buybox-el{border:1px solid #DCDEE0;border-radius:16px;padding:22px 24px;margin-bottom:20px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
#sp-reta-buybox-el .woocommerce{margin:0}
#sp-reta-buybox-el .price{font-family:'Sora',sans-serif;font-size:30px;font-weight:700;color:#0D0F12;display:block;margin-bottom:16px}
#sp-reta-buybox-el .price ins{text-decoration:none;color:#0D0F12}
#sp-reta-buybox-el form.cart{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
#sp-reta-buybox-el .quantity{margin:0}
#sp-reta-buybox-el .quantity .qty{width:64px;padding:12px 10px;border:1px solid #DCDEE0;border-radius:10px;font-size:15px;text-align:center}
#sp-reta-buybox-el .single_add_to_cart_button,
#sp-reta-buybox-el button[type="submit"]{border:none!important;padding:14px 28px!important;border-radius:10px!important;font-weight:700!important;font-size:15px!important;cursor:pointer;position:relative;overflow:hidden;transition:transform .2s}
#sp-reta-buybox-el .single_add_to_cart_button:hover{transform:translateY(-2px)}
@keyframes sp-reta-shine{0%,55%{left:-75%}100%{left:150%}}
.sp-reta-qualitybox{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:16px;padding:20px 22px;margin-bottom:16px}
.sp-reta-qualitybox h4{margin:0 0 4px;font-size:16px;font-weight:700;color:#0D0F12}
.sp-reta-qualitybox p{margin:0 0 10px;font-size:13.5px;color:#4B5157}
.sp-reta-qualitybox .sp-reta-check{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #DCDEE0;border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;color:#0D0F12}
.sp-reta-dhl{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(180deg,#0D0F12 0%,#1C1F23 100%);color:#fff;font-size:13.5px;font-weight:600;padding:10px 18px;border-radius:12px;margin-bottom:18px}
.sp-reta-dhl .sp-dhl-shine{background-image:linear-gradient(100deg,#FFFFFF 0%,#C7CCD1 35%,#FFFFFF 50%,#C7CCD1 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;font-weight:700;animation:sp-tb-shine2 3.2s linear infinite}
@keyframes sp-tb-shine2{0%{background-position:200% center}100%{background-position:0% center}}
.sp-reta-disclaimer{font-size:12.5px;color:#4B5157;line-height:1.5;max-width:520px}
@media(max-width:782px){.sp-reta-buybox .price{font-size:26px}}
</style>

<div class="sp-reta-hero">
  <a href="#motsc-kundenstimmen" class="sp-reta-rating">
    <span class="sp-rate-stars"><span class="sp-rate-stars-bg">&#9733;&#9733;&#9733;&#9733;&#9733;</span><span class="sp-rate-stars-fg" style="width:94%">&#9733;&#9733;&#9733;&#9733;&#9733;</span></span>
    <span class="sp-reta-rating-num">4,7</span>
    <span class="sp-reta-rating-count">(34 Bewertungen)</span>
  </a>
  <h1 class="sp-reta-title">Melanotan II (MT-2)</h1>
  <p class="sp-reta-subtitle-line">Forschungspeptid &ndash; Nicht f&uuml;r den menschlichen Verzehr</p>
  <div class="sp-reta-checks">
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>99 % Reinheit (HPLC)</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>LC-MS Identit&auml;tsverifizierung</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>Chargenspezifisches Analysezertifikat (COA)</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>Diskrete Verpackung &amp; schneller Versand</span></div>
  </div>
</div>

SPHTML,
        'bb' => <<<'SPHTML'
<style>
.rx-buybox{border:1px solid #DCDEE0;border-radius:16px;padding:22px 24px;margin-bottom:14px;font-family:'Sora',sans-serif;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.rx-price-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px}
.rx-price-block{display:flex;align-items:baseline;gap:10px}
.rx-price{font-size:30px;font-weight:700;color:#0D0F12}
.rx-price-note{font-size:13px;color:#4B5157}
.rx-stock{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#16a34a}
.rx-stock-dot{width:9px;height:9px;border-radius:50%;background:#2bdc6b;display:inline-block;animation:rx-blink 1.4s ease-in-out infinite}
@keyframes rx-blink{
  0%,100%{opacity:1;box-shadow:0 0 6px 2px rgba(43,220,107,.9),0 0 16px 6px rgba(43,220,107,.55)}
  50%{opacity:.55;box-shadow:0 0 3px 1px rgba(43,220,107,.5),0 0 6px 2px rgba(43,220,107,.22)}
}
@keyframes rx-amt-shine{0%{background-position:200% center}100%{background-position:0% center}}
.rx-amt-shine{background-image:linear-gradient(100deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;font-weight:700;animation:rx-amt-shine 2.6s linear infinite;white-space:nowrap}
.rx-shipbadges{margin-bottom:16px}
.rx-shipbadges .sp-reta-dhl{padding:4px 10px 4px 4px;font-size:10.5px;font-weight:600;gap:7px;margin-bottom:0;border-radius:7px;background:#F2F3F4!important;color:#0D0F12!important;border:1px solid #DCDEE0;position:relative;overflow:hidden;box-shadow:none}
.rx-shipbadges .sp-dhl-shine{background:none!important;-webkit-text-fill-color:#0D0F12!important;color:#0D0F12!important;animation:none!important;font-weight:700!important}
.rx-shipbadges .sp-dhl-iconwrap{display:inline-flex;align-items:center;justify-content:center;width:19px;height:19px;background:#FFCC00;border-radius:5px;flex-shrink:0}
.rx-shipbadges .sp-reta-dhl svg{width:11px;height:11px}
.rx-shipbadges .sp-reta-dhl svg{width:15px;height:15px}
.rx-tiers-head{font-size:13px;font-weight:700;color:#363C42;text-transform:uppercase;letter-spacing:.03em;margin-bottom:10px}
.rx-tiers{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:18px}
.rx-tiers.rx-tiers--single{grid-template-columns:repeat(4,1fr);margin-bottom:18px}
.rx-tier-wrap{position:relative}
.rx-tier{position:relative;overflow:hidden;border:1.5px solid #DCDEE0;border-radius:12px;background:#fff;padding:14px 6px 10px;display:flex;flex-direction:column;align-items:center;cursor:pointer;transition:border-color .15s,background .15s;width:100%;box-sizing:border-box}
.rx-tier:hover{border-color:#0D0F12}
.rx-tier.is-active{border-color:#0D0F12;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%)}
.rx-tier.is-active .rx-tier__q{color:#fff}
.rx-tier.is-active .rx-tier__unit{color:#C7CCD1}
.rx-tier.is-active .rx-tier__disc{color:#2f7d4f}

.rx-tier--static{cursor:default}
.rx-tier__flag{position:absolute;top:-11px;left:50%;transform:translateX(-50%);z-index:2;overflow:hidden;background:linear-gradient(135deg,#F2F3F4 0%,#A8B0B9 100%);font-size:10px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap;box-shadow:0 2px 8px rgba(13,15,18,.35)}
.rx-tier__flag-text{background-image:linear-gradient(100deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;font-weight:700;animation:rx-amt-shine 2.6s linear infinite}
.rx-tier__flag::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(13,15,18,.15) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-reta-shine 3.2s ease-in-out infinite;pointer-events:none}
.rx-tier__disc{font-size:10.5px;font-weight:700;color:#2f7d4f;margin-bottom:2px}
.rx-tier__q{font-size:18px;font-weight:700;color:#0D0F12}
.rx-tier__unit{font-size:11px;color:#4B5157;font-weight:600}
.rx-buysum{display:flex;flex-direction:column;gap:8px;width:100%;box-sizing:border-box;background:linear-gradient(135deg,#2E3237 0%,#454B52 100%);border-radius:12px;padding:12px 16px;margin-bottom:8px}
.rx-buysum-top{display:flex;align-items:center;justify-content:space-between;gap:16px}
.rx-buysum__total{display:flex;align-items:baseline;gap:8px;text-align:left}
.rx-buysum__label{font-size:11.5px;color:rgba(255,255,255,.55);font-weight:600;text-transform:uppercase;letter-spacing:.02em}
.rx-buysum__total b{font-size:26px;color:#FFFFFF;white-space:nowrap;font-weight:700;line-height:1.1}
.rx-sv{background:#2f7d4f;color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;white-space:nowrap;width:fit-content}
.rx-shiphint{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:#4B5157;flex-wrap:wrap;margin-bottom:12px}
.rx-shiphint.is-done{color:#2f7d4f}
.rx-shiphint-ic{width:17px;height:17px;flex-shrink:0}
.rx-buycta{display:flex;gap:10px;align-items:stretch;margin-bottom:16px}
.rx-qtybox{display:inline-flex;align-items:stretch;border:1px solid rgba(255,255,255,.18);border-radius:10px;overflow:hidden;box-sizing:border-box;flex-shrink:0}
.rx-qtybox button{-webkit-appearance:none;appearance:none;box-sizing:border-box;width:32px;height:36px;border:none;border-radius:0;background:#FFFFFF;color:#0D0F12;font-size:15px;line-height:1;cursor:pointer;padding:0}
.rx-qtybox button:first-child{border-right:1px solid rgba(255,255,255,.18)}
.rx-qtybox button:last-child{border-left:1px solid rgba(255,255,255,.18)}
.rx-qtybox input{-webkit-appearance:none;appearance:none;box-sizing:border-box;width:36px;height:36px;border:none;border-radius:0;text-align:center;font-size:13.5px;font-weight:700;color:#0D0F12;background:#fff;padding:0}
.rx-qtybox input[type=number]::-webkit-inner-spin-button,
.rx-qtybox input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
.rx-qtybox input[type=number]{-moz-appearance:textfield}
.rx-cartslot #sp-reta-buybox-el{display:block;width:100%;height:100%;border:none!important;padding:0!important;margin:0!important;box-shadow:none!important;border-radius:0!important;background:transparent!important}
.rx-pay-in{border-top:1px solid #DCDEE0;padding-top:16px;margin-top:14px}
.rx-pay-in__k{display:block;font-size:11.5px;color:#4B5157;font-weight:600;margin-bottom:8px}
.rx-pay-in__row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.rx-paychip{display:inline-flex;align-items:center;gap:6px;background:#F2F3F4;border:1px solid #DCDEE0;border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700;color:#4B5157}.rx-paychip svg{width:14px;height:14px;flex-shrink:0}.rx-paychip--vorkasse svg{color:#2E7DD1}.rx-paychip--krypto svg{color:#F7931A}
.rx-trustbadges{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:14px}
.rx-trustbadge{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;background:#F9FAFA;border:1px solid #DCDEE0;border-radius:8px;padding:7px 4px;text-align:center}
.rx-trustbadge svg{width:15px;height:15px;color:#0D0F12;flex-shrink:0}.rx-trustbadge:nth-child(1) svg{color:#2E7DD1}.rx-trustbadge:nth-child(2) svg{color:#1F7A4D}.rx-trustbadge:nth-child(3) svg{color:#7C3AED}.rx-trustbadge:nth-child(4) svg{color:#8B5E3C}
.rx-trustbadge span{font-size:8px;font-weight:700;letter-spacing:.01em;color:#4B5157;line-height:1.15}
.rx-disclaimer{font-size:12px;color:#4B5157;line-height:1.5;margin:0}.rx-social-proof{display:flex;align-items:center;gap:10px;margin:14px 0 0;padding-top:14px;border-top:1px solid #DCDEE0;}.rx-sp-avatars{display:flex;flex-shrink:0;}.rx-sp-avatar{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 0 0 1px #DCDEE0;margin-left:-8px;box-sizing:border-box;overflow:hidden;}.rx-sp-avatar:first-child{margin-left:0;}.rx-sp-avatar img{width:100%;height:100%;object-fit:cover;display:block;}.rx-sp-text{font-size:12.5px;font-weight:700;color:#0D0F12;}
#sp-reta-buybox-el .price{display:none!important}
#sp-reta-buybox-el .quantity{display:none!important;position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;margin:0!important;padding:0!important}
#sp-reta-buybox-el .added_to_cart,
#sp-reta-buybox-el .wc-forward,
#sp-reta-buybox-el a.added_to_cart{display:none!important}
#sp-reta-buybox-el form.cart{display:block!important;height:100%}
#sp-reta-buybox-el .single_add_to_cart_button{width:100%;height:46px;justify-content:center;display:flex!important;align-items:center;gap:8px;margin:0!important;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%)!important;color:#fff!important;white-space:nowrap;font-size:15.5px;font-weight:600;box-sizing:border-box}
#sp-reta-buybox-el .single_add_to_cart_button::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(255,255,255,.45) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-reta-shine 3.2s ease-in-out infinite;pointer-events:none}
.rx-cartslot{flex:1;min-width:0}
.rx-addons{margin-bottom:16px}
.rx-addon-row{display:flex;flex-direction:column;gap:8px;padding:10px 0;border-bottom:1px solid #F2F3F4}
.rx-addon-row:last-child{border-bottom:none}
.rx-addon-top{display:flex;align-items:center;gap:10px}
.rx-addon-img{width:36px;height:36px;border-radius:10px!important;object-fit:cover;background:#F2F3F4;flex-shrink:0}
.rx-addon-name{font-size:13.5px;font-weight:600;color:#0D0F12;flex:1;min-width:0}
.rx-addon-btn{font-family:'Sora',sans-serif;font-size:12px;font-weight:600;border-radius:999px;padding:7px 12px;cursor:pointer;border:1px solid #DCDEE0;background:#fff;color:#4B5157;transition:border-color .15s,background .15s,color .15s;white-space:nowrap;flex-shrink:0}
.rx-addon-btn.is-active{background:#0D0F12;color:#fff;border-color:#0D0F12}
.rx-addon-btn:not(.is-active):hover{border-color:#A8B0B9}
.rx-addon-price{opacity:.85}
.rx-addon-btn-remove{display:none}
.rx-addon-btn.is-active .rx-addon-btn-add{display:none}
.rx-addon-btn.is-active .rx-addon-btn-remove{display:inline}
.rx-addon-qty{display:none;align-items:center;gap:10px;margin-left:46px}
.rx-addon-row.is-active .rx-addon-qty{display:flex}
.rx-addon-qtybtn{width:26px;height:26px;border-radius:8px;border:1px solid #DCDEE0;background:#fff;color:#0D0F12;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;line-height:1;padding:0}
.rx-addon-qtybtn:hover{border-color:#A8B0B9}
.rx-addon-qtyval{font-size:13px;font-weight:700;color:#0D0F12;min-width:16px;text-align:center}
@media(max-width:480px){.rx-tier__q{font-size:15px}.rx-price{font-size:26px}.rx-buysum__total{font-size:13px}.rx-buysum__total b{font-size:16px}.rx-addon-name{font-size:12.5px}.rx-addon-btn{font-size:11px;padding:6px 9px}}
</style>
<div class="rx-buybox" id="rx-buybox">
  <div class="rx-price-row">
    <div class="rx-price-block">
      <span class="rx-price" id="rx-price">29,90&nbsp;&euro;</span>
      <span class="rx-price-note">zzgl. Versand</span>
    </div>
    <div class="rx-stock"><span class="rx-stock-dot"></span> Auf Lager &middot; sofort versandfertig</div>
  </div>

  <div class="rx-shipbadges">
    <div class="sp-reta-dhl">
      <span class="sp-dhl-iconwrap"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D40511" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="15" height="11"/><path d="M16 10h3l3 3v4h-6z"/><circle cx="6" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/></svg></span>
      <span>DHL PREMIUM &middot; <span class="sp-dhl-shine">2 Tage Lieferung</span></span>
    </div>
  </div>

  <div class="rx-tiers-head">Volumen</div>
  <div class="rx-tiers rx-tiers--single">
    <div class="rx-tier is-active rx-tier--static">
      <span class="rx-tier__q">10</span><span class="rx-tier__unit">mg</span>
    </div>
  </div>

  <div class="rx-tiers-head">Menge &amp; Vorteil w&auml;hlen</div>
  <div class="rx-tiers" id="rx-tiers">
    <button type="button" class="rx-tier is-active" data-q="1" data-disc="0">
      <span class="rx-tier__disc">Standard</span>
      <span class="rx-tier__q">1</span><span class="rx-tier__unit">Vial</span>
    </button>
    <div class="rx-tier-wrap">
      <span class="rx-tier__flag"><span class="rx-tier__flag-text">Beliebt</span></span>
      <button type="button" class="rx-tier" data-q="3" data-disc="10">
        <span class="rx-tier__disc">&minus;10%</span>
        <span class="rx-tier__q">3</span><span class="rx-tier__unit">Vials</span>
      </button>
    </div>
    <button type="button" class="rx-tier" data-q="5" data-disc="15">
      <span class="rx-tier__disc">&minus;15%</span>
      <span class="rx-tier__q">5</span><span class="rx-tier__unit">Vials</span>
    </button>
    <button type="button" class="rx-tier" data-q="10" data-disc="20">
      <span class="rx-tier__disc">&minus;20%</span>
      <span class="rx-tier__q">10</span><span class="rx-tier__unit">Vials</span>
    </button>
  </div>

  <div class="rx-addons" id="rx-addons">
    <div class="rx-tiers-head">Zubeh&ouml;r</div>
    <div class="rx-addon-row" data-field="sp_addon_bacwater" data-price="9.90">
      <div class="rx-addon-top">
        <img class="rx-addon-img" src="https://peptrium.com/wp-content/uploads/2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-150x150.png" alt="">
        <span class="rx-addon-name">Bac Water 3&nbsp;ml</span>
        <button type="button" class="rx-addon-btn rx-addon-btn--add" data-choice="1"><span class="rx-addon-btn-add">Hinzuf&uuml;gen <span class="rx-addon-price">+9,90&nbsp;&euro;</span></span><span class="rx-addon-btn-remove">Entfernen</span></button>
      </div>
      <div class="rx-addon-qty">
        <button type="button" class="rx-addon-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <span class="rx-addon-qtyval">1</span>
        <button type="button" class="rx-addon-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
    <div class="rx-addon-row" data-field="sp_addon_insulin" data-price="9.90">
      <div class="rx-addon-top">
        <img class="rx-addon-img" src="https://peptrium.com/wp-content/uploads/2026/08/insulinspritze-10er-pack-wide-150x150.png" alt="">
        <span class="rx-addon-name">Insulinspritze 10er Pack</span>
        <button type="button" class="rx-addon-btn rx-addon-btn--add" data-choice="1"><span class="rx-addon-btn-add">Hinzuf&uuml;gen <span class="rx-addon-price">+9,90&nbsp;&euro;</span></span><span class="rx-addon-btn-remove">Entfernen</span></button>
      </div>
      <div class="rx-addon-qty">
        <button type="button" class="rx-addon-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <span class="rx-addon-qtyval">1</span>
        <button type="button" class="rx-addon-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
  </div>

  <div class="rx-shiphint" id="rx-shiphint"></div>

  <div class="rx-buysum">
    <div class="rx-buysum-top">
      <div class="rx-buysum__total"><span class="rx-buysum__label">Gesamt</span><b id="rx-total">29,90&nbsp;&euro;</b></div>
      <div class="rx-qtybox">
        <button type="button" class="rx-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <input type="number" id="rx-qtyfield" min="1" step="1" value="1" readonly>
        <button type="button" class="rx-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
    <span class="rx-sv" id="rx-save" style="display:none">Du sparst 10,47&nbsp;&euro;</span>
  </div>

  <div class="rx-buycta">
    <div class="rx-cartslot" id="rx-cartrow"></div>
  </div>

  <div class="rx-social-proof" id="rx-social-proof">
    <div class="rx-sp-avatars">
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-4.jpg" alt="" loading="lazy"></span>
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-5.jpg" alt="" loading="lazy"></span>
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-3.jpg" alt="" loading="lazy"></span>
    </div>
    <span class="rx-sp-text">&Uuml;ber 2.500+ zufriedene Kunden</span>
  </div>

  <div class="rx-pay-in">
    <div class="rx-trustbadges">
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v7.31"/><path d="M14 9.3V1.99"/><path d="M8.5 2h7"/><path d="M14 9.3a6.5 6.5 0 1 1-4 0"/><path d="M5.58 16.5h12.85"/></svg><span>HPLC &ge;99%</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg><span>COA</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18h8"/><path d="M3 22h18"/><path d="M14 22a7 7 0 1 0 0-14h-1"/><path d="M9 14h2"/><path d="M9 12a4 4 0 0 0-4 4v2"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg><span>LC-MS</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73Z"/><path d="M3.29 7 12 12l8.71-5"/><path d="M12 22V12"/></svg><span>Diskret</span></div>
    </div>
    <span class="rx-pay-in__k">Sichere Bezahlung</span>
    <div class="rx-pay-in__row">
      <span class="rx-paychip rx-paychip--vorkasse"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>Vorkasse</span>
      <span class="rx-paychip rx-paychip--krypto"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.638 14.904c-1.602 6.43-8.113 10.34-14.542 8.736C2.67 22.05-1.244 15.525.362 9.105 1.962 2.67 8.475-1.243 14.9.358c6.43 1.605 10.342 8.115 8.738 14.548v-.002zm-6.35-4.613c.24-1.59-.974-2.45-2.64-3.03l.54-2.153-1.315-.33-.525 2.107c-.345-.087-.705-.167-1.064-.25l.526-2.127-1.32-.33-.54 2.165c-.285-.065-.565-.13-.84-.2l-1.815-.45-.35 1.407s.975.225.955.236c.535.136.63.486.615.766l-.615 2.46c.035.01.08.024.13.048l-.13-.033-.865 3.46c-.065.164-.23.41-.606.316.015.02-.96-.24-.96-.24l-.655 1.51 1.71.426.93.242-.54 2.19 1.32.327.54-2.17c.36.1.705.19 1.05.273l-.51 2.154 1.32.33.545-2.19c2.24.427 3.93.257 4.64-1.774.57-1.63-.03-2.57-1.21-3.184.86-.198 1.51-.76 1.68-1.93h.01zm-3.01 4.22c-.404 1.64-3.157.75-4.05.53l.72-2.9c.896.23 3.757.67 3.33 2.37zm.41-4.24c-.37 1.49-2.662.735-3.405.55l.654-2.64c.744.18 3.137.53 2.75 2.084v.006z"/></svg>Krypto</span>
    </div>
    <p class="rx-disclaimer">Nicht f&uuml;r den menschlichen oder veterin&auml;rmedizinischen Gebrauch. Nur f&uuml;r Laborforschungszwecke.</p>
  </div>
</div>

<script>
(function(){
  var unitPrice = 29.90;
  var freeShipThreshold = 150;
  var truckIcon = '<svg class="rx-shiphint-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="15" height="11"/><path d="M16 10h3l3 3v4h-6z"/><circle cx="6" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/></svg>';

  function fmt(n){
    return n.toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' €';
  }

  function hideRealQty(){
    var el = document.querySelector('#sp-reta-buybox-el .quantity');
    if (el) {
      el.setAttribute('style', 'display:none!important;position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;margin:0!important;padding:0!important;');
    }
    var links = document.querySelectorAll('#sp-reta-buybox-el .added_to_cart, #sp-reta-buybox-el .wc-forward');
    links.forEach(function(l){ l.style.display = 'none'; });
  }

  function init(){
    var cartRow = document.getElementById('rx-cartrow');
    var realBox = document.getElementById('sp-reta-buybox-el');
    if (cartRow && realBox) { cartRow.appendChild(realBox); }
    hideRealQty();

    function ensureAddonInputs(){
      var form = document.querySelector('#sp-reta-buybox-el form.cart');
      if (!form) return null;
      document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
        var field = row.getAttribute('data-field');
        if (!form.querySelector('input[name="' + field + '"]')) {
          var inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = field;
          inp.value = '0';
          form.appendChild(inp);
        }
      });
      return form;
    }

    document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
      var field = row.getAttribute('data-field');
      var addBtn = row.querySelector('.rx-addon-btn--add');
      var qtyValEl = row.querySelector('.rx-addon-qtyval');
      var qty = 0;
      function render(){
        var active = qty > 0;
        row.classList.toggle('is-active', active);
        addBtn.classList.toggle('is-active', active);
        if (qtyValEl) qtyValEl.textContent = qty || 1;
        var form = ensureAddonInputs();
        if (form) {
          var inp = form.querySelector('input[name="' + field + '"]');
          if (inp) inp.value = String(qty);
        }
        if (qtyField) { update(parseInt(qtyField.value, 10)); }
      }
      addBtn.addEventListener('click', function(){ qty = addBtn.classList.contains('is-active') ? 0 : 1; render(); });
      row.querySelectorAll('.rx-addon-qtybtn').forEach(function(btn){
        btn.addEventListener('click', function(){
          var step = parseInt(btn.getAttribute('data-step'), 10);
          qty = Math.max(0, Math.min(10, qty + step));
          render();
        });
      });
      render();
    });
    ensureAddonInputs();

    if (window.jQuery) {
      jQuery(document.body).on('added_to_cart updated_wc_div wc_fragments_refreshed wc_fragments_loaded', function(){
        setTimeout(hideRealQty, 0);
      });
    }
    var rxObserver = new MutationObserver(function(){ hideRealQty(); });
    rxObserver.observe(document.body, {childList:true, subtree:true});

    var qtyField = document.getElementById('rx-qtyfield');
    var totalEl = document.getElementById('rx-total');
    var saveEl = document.getElementById('rx-save');
    var priceEl = document.getElementById('rx-price');
    var shiphintEl = document.getElementById('rx-shiphint');
    var tiers = document.querySelectorAll('#rx-tiers .rx-tier');
    if (!qtyField) return;
    priceEl.textContent = fmt(unitPrice);

    function findRealQtyInput(){
      return document.querySelector('#sp-reta-buybox-el .quantity input.qty');
    }

    function round2(n){
      return Math.round(n * 100) / 100;
    }

    // Bundle-pack model, matching the cart: only the units matching the largest
    // reached bundle size (1/3/5/10) get that bundle's discount, any extra units
    // above it stay full price. Mirrors sp-quantity-discount.php exactly.
    function bundleFor(qty){
      var best = null;
      tiers.forEach(function(btn){
        var q = parseInt(btn.getAttribute('data-q'),10);
        var d = parseInt(btn.getAttribute('data-disc'),10);
        if (qty >= q && (!best || q > best.q)) {
          best = { q: q, disc: d };
        }
      });
      return best;
    }

    function addonsTotal(){
      var sum = 0;
      document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
        var qtyValEl = row.querySelector('.rx-addon-qtyval');
        var addBtn = row.querySelector('.rx-addon-btn--add');
        var qty = (addBtn && addBtn.classList.contains('is-active') && qtyValEl) ? (parseInt(qtyValEl.textContent, 10) || 0) : 0;
        if (qty > 0) {
          sum += (parseFloat(row.getAttribute('data-price')) || 0) * qty;
        }
      });
      return sum;
    }

    function update(qty){
      qty = Math.max(1, qty);
      qtyField.value = qty;
      var bundle = bundleFor(qty);
      var fullTotal = unitPrice * qty;
      var productTotal = fullTotal;
      if (bundle && bundle.disc > 0) {
        var discountedUnit = round2(unitPrice * (1 - bundle.disc/100));
        var remainder = qty - bundle.q;
        productTotal = round2(discountedUnit * bundle.q + unitPrice * remainder);
      }
      var total = productTotal + addonsTotal();
      totalEl.textContent = fmt(total);
      if (bundle && bundle.disc > 0) {
        saveEl.style.display = '';
        saveEl.textContent = 'Du sparst ' + fmt(fullTotal - productTotal);
      } else {
        saveEl.style.display = 'none';
      }

      var realQtyInput = findRealQtyInput();
      if (realQtyInput) {
        realQtyInput.value = qty;
        realQtyInput.dispatchEvent(new Event('change', {bubbles:true}));
      }

      var remaining = freeShipThreshold - total;
      if (remaining > 0) {
        shiphintEl.innerHTML = truckIcon + '<span>Noch <span class="rx-amt-shine">' + fmt(remaining) + '</span> bis Gratisversand</span>';
        shiphintEl.classList.remove('is-done');
      } else {
        shiphintEl.innerHTML = truckIcon + '<span>Gratisversand</span>';
        shiphintEl.classList.add('is-done');
      }

      tiers.forEach(function(btn){
        btn.classList.toggle('is-active', !!bundle && parseInt(btn.getAttribute('data-q'),10) === bundle.q);
      });
    }

    tiers.forEach(function(btn){
      btn.addEventListener('click', function(){ update(parseInt(btn.getAttribute('data-q'),10)); });
    });

    document.querySelectorAll('.rx-qtybtn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var step = parseInt(btn.getAttribute('data-step'),10);
        update(parseInt(qtyField.value,10) + step);
      });
    });

    update(1);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>

SPHTML,
        'qual' => <<<'SPHTML'

<div class="sp-reta-qualitybox">
  <h4>Unabh&auml;ngig laborgepr&uuml;ft</h4>
    <span class="sp-reta-check">&#10003; Reinheit (HPLC)</span>
</div>

SPHTML,
        'tabs' => <<<'SPHTML'

<style>
.rx-tabsec{max-width:760px;margin:0 auto}
.rx-tabnav{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.rx-tabbtn{border:1px solid #DCDEE0;background:#F2F3F4;color:#4B5157;border-radius:999px;padding:10px 22px;font-family:'Sora',sans-serif;font-weight:700;font-size:14px;cursor:pointer;transition:all .2s;flex:1;min-width:140px;text-align:center}
.rx-tabbtn:hover{border-color:#A8B0B9}
.rx-tabbtn.is-active{background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#fff;border-color:#0D0F12;box-shadow:0 4px 14px rgba(13,15,18,.25)}
.rx-tabcard{border:1px solid #DCDEE0;border-radius:16px;background:#fff;padding:28px 24px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.rx-tabpanel{display:none}
.rx-tabpanel.is-active{display:block}
.rx-tabpanel .sp-reta-desc,.rx-tabpanel .sp-reta-specs{max-width:none;margin:0}
.rx-results{font-family:'Sora',sans-serif}
.rx-results-top{display:flex;align-items:center;gap:28px;flex-wrap:wrap;margin-bottom:22px;padding-bottom:22px;border-bottom:1px solid #DCDEE0}
.rx-results-purity{font-size:44px;font-weight:700;letter-spacing:-0.02em;background:linear-gradient(135deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;animation:rx-res-shine 2.6s linear infinite;white-space:nowrap}
@keyframes rx-res-shine{0%{background-position:200% center}100%{background-position:0% center}}
.rx-results-purity-label{font-size:13.5px;color:#4B5157;font-weight:600;margin-top:2px}
.rx-results-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px}
.rx-results-tile{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:12px;padding:14px 16px}
.rx-results-tile-k{font-size:11.5px;color:#8a9099;font-weight:700;text-transform:uppercase;letter-spacing:.03em;margin-bottom:4px}
.rx-results-tile-v{font-size:14.5px;color:#0D0F12;font-weight:700;display:flex;align-items:center;gap:6px}
.rx-results-check{color:#2f7d4f}
.rx-coa-box{border:1px dashed #DCDEE0;border-radius:12px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;background:#F2F3F4}
.rx-coa-info{font-size:14px;color:#4B5157;font-weight:600}
.rx-coa-info b{display:block;color:#0D0F12;font-size:15px;margin-bottom:2px}
.rx-coa-btn{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #DCDEE0;color:#8a9099;font-family:'Sora',sans-serif;font-weight:700;font-size:13.5px;padding:10px 18px;border-radius:10px;cursor:not-allowed;opacity:.7;white-space:nowrap}
@media(max-width:600px){.rx-results-grid{grid-template-columns:1fr}}
</style>

<div class="rx-tabsec">
  <div class="rx-tabnav" role="tablist">
    <button type="button" class="rx-tabbtn is-active" data-tab="produkt">Produkt</button>
    <button type="button" class="rx-tabbtn" data-tab="lagerung">Lagerung</button>
    <button type="button" class="rx-tabbtn" data-tab="results">Results</button>
    <button type="button" class="rx-tabbtn" data-tab="tools">Tools &amp; Rechner</button>
  </div>

  <div class="rx-tabcard">
    <div class="rx-tabpanel is-active" data-tab="produkt">

<style>
.sp-reta-desc{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif;color:#0D0F12}
.sp-reta-desc h4{font-size:20px;font-weight:700;margin:34px 0 12px;color:#0D0F12}
.sp-reta-desc h4:first-child{margin-top:0}
.sp-reta-desc h3{font-size:24px;font-weight:700;margin:40px 0 14px;color:#0D0F12}
.sp-reta-desc p{font-size:15.5px;line-height:1.7;color:#4B5157;margin:0 0 14px}
.sp-reta-desc strong{color:#0D0F12}
.sp-reta-desc a{color:#0D0F12;text-decoration:underline;text-decoration-color:#A8B0B9;text-underline-offset:2px}
.sp-reta-desc .sp-warn{background:#F2F3F4;border-left:3px solid #A8B0B9;padding:12px 16px;border-radius:8px;font-size:14px;color:#4B5157;margin:18px 0}
.sp-reta-iconrow{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:32px}
.sp-reta-iconrow div{background:#F2F3F4;border-radius:12px;padding:16px 14px;text-align:center;font-size:13.5px;font-weight:600;color:#0D0F12}
@media(max-width:782px){.sp-reta-iconrow{grid-template-columns:repeat(2,1fr)}}
</style>
<div class="sp-reta-desc">
<h3>Wirkung &amp; Forschung</h3>
<p>Melanotan II (MT-2) ist ein synthetisches, zyklisches Analogon des <strong>α-Melanozyten-stimulierenden Hormons (α-MSH)</strong>. Es wurde in den 1980er-Jahren an der University of Arizona entwickelt und wird in der Forschung als nicht-selektiver Agonist an den Melanocortin-Rezeptoren MC1R, MC3R, MC4R und MC5R untersucht. Im Mittelpunkt stehen Studien zur Melanogenese, zur Pigmentierung der Haut und zu Melanocortin-Signalwegen. Melanotan II ist nicht als Arzneimittel zugelassen.</p>
<div class="sp-warn">⚠️ <strong>Nur zu Forschungszwecken.</strong> Nicht für den menschlichen Gebrauch.</div>
</div>



<style>
.sp-reta-specs{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-reta-specs h3{font-size:22px;font-weight:700;color:#0D0F12;margin:0 0 18px;text-align:center}
.sp-reta-specs table{width:100%;table-layout:fixed;border-collapse:collapse;background:#fff;border:1px solid #DCDEE0;border-radius:14px;overflow:hidden}
.sp-reta-specs td{padding:14px 20px;font-size:14.5px;border-bottom:1px solid #DCDEE0;word-break:break-word;overflow-wrap:anywhere}
.sp-reta-specs tr:last-child td{border-bottom:none}
.sp-reta-specs td:first-child{color:#4B5157;font-weight:600;width:45%}
.sp-reta-specs td:last-child{color:#0D0F12;font-weight:600}
</style>
<div class="sp-reta-specs">
<h3>Technische Spezifikationen</h3>
<table>
<tr><td>Produktname</td><td>Melanotan II (MT-2)</td></tr>
<tr><td>Menge</td><td>10 mg pro Vial</td></tr>
<tr><td>Form</td><td>Lyophilisiertes Pulver</td></tr>
<tr><td>Lagerung</td><td>2–8 °C, langfristig −20 °C</td></tr>
<tr><td>Molekulare Struktur</td><td>Zyklisches Heptapeptid (Ac-Nle-c[Asp-His-D-Phe-Arg-Trp-Lys]-NH₂)</td></tr>
<tr><td>Summenformel</td><td>C₅₀H₆₉N₁₅O₉ · 1024,2 g/mol</td></tr>
<tr><td>Analyse</td><td>HPLC / LC-MS</td></tr>
</table>
</div>
    </div>

        <div class="rx-tabpanel" data-tab="lagerung">
<style>
.sp-lager{font-family:'Sora',sans-serif}
.sp-lager-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:26px}
.sp-lager-card{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:14px;padding:20px 22px}
.sp-lager-card h4{margin:0 0 12px;font-size:16px;font-weight:700;color:#0D0F12}
.sp-lager-stat{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #DCDEE0;font-size:13.5px}
.sp-lager-stat:last-of-type{border-bottom:none}
.sp-lager-stat-k{color:#4B5157;font-weight:600}
.sp-lager-stat-v{color:#0D0F12;font-weight:700}
.sp-lager-tips{list-style:none;margin:12px 0 0;padding:0;display:flex;flex-direction:column;gap:8px}
.sp-lager-tips li{display:flex;align-items:flex-start;gap:8px;font-size:13.5px;color:#4B5157;line-height:1.5}
.sp-lager-tips li svg{width:15px;height:15px;color:#2f7d4f;flex-shrink:0;margin-top:2px}
.sp-lager-general-title{font-size:16px;font-weight:700;color:#0D0F12;margin:0 0 14px}
.sp-lager-tipgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.sp-lager-tipcard{background:#fff;border:1px solid #DCDEE0;border-radius:12px;padding:16px;text-align:center}
.sp-lager-tipicon{font-size:22px;margin-bottom:8px}
.sp-lager-tipcard p{margin:0;font-size:12.5px;line-height:1.5;color:#4B5157}
.sp-lager-tipcard strong{display:block;color:#0D0F12;font-size:13px;margin-bottom:4px}
@media(max-width:600px){.sp-lager-row{grid-template-columns:1fr}.sp-lager-tipgrid{grid-template-columns:1fr}}
</style>
<div class="sp-lager">
  <div class="sp-lager-row">
    <div class="sp-lager-card">
      <h4>Als Pulver</h4>
      <div class="sp-lager-stat"><span class="sp-lager-stat-k">Temperatur</span><span class="sp-lager-stat-v">2&ndash;8&nbsp;&deg;C</span></div>
      <ul class="sp-lager-tips">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Immer gek&uuml;hlt lagern</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Empfindliches Peptid</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Lichtgesch&uuml;tzt aufbewahren</li>
      </ul>
    </div>
    <div class="sp-lager-card">
      <h4>Nach Rekonstitution</h4>
      <div class="sp-lager-stat"><span class="sp-lager-stat-k">Temperatur</span><span class="sp-lager-stat-v">2&ndash;8&nbsp;&deg;C (K&uuml;hlschrank)</span></div>
      <ul class="sp-lager-tips">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Kurze Haltbarkeit nach Aufl&ouml;sen</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Kleine Mengen anmischen</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Sofort k&uuml;hlen</li>
      </ul>
    </div>
  </div>
  <div class="sp-lager-general-title">Allgemeine Lagerungstipps</div>
  <div class="sp-lager-tipgrid">
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#128167;</div><strong>BAC-Wasser verwenden</strong><p>Nutze nur steriles BAC-Wasser (Benzylalkohol + Kochsalz) zur Rekonstitution, nicht gew&ouml;hnliches Wasser.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#129532;</div><strong>Septum desinfizieren</strong><p>Wische das Septum vor jeder Entnahme mit einem Alkoholtupfer ab, um Kontamination zu vermeiden.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#10052;&#65039;</div><strong>K&uuml;hlkette nicht unterbrechen</strong><p>Halte die empfohlene Temperatur konsistent &mdash; Temperaturausrei&szlig;er reduzieren die Haltbarkeit drastisch.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#9728;&#65039;</div><strong>Vor Sonnenlicht sch&uuml;tzen</strong><p>Lagere Peptide in dunklen Beh&auml;ltern oder undurchsichtigen Fl&auml;schchen, um Lichtabbau zu verhindern.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#10060;</div><strong>Nicht einfrieren nach Rekonstitution</strong><p>Wiederholtes Gefrieren und Auftauen zerst&ouml;rt die Peptidstruktur &mdash; nur bei Pulver geeignet.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#128197;</div><strong>Verfallsdatum notieren</strong><p>Schreibe das Rekonstitutionsdatum auf jede Flasche &mdash; dies ist entscheidend f&uuml;r die Patientenverteidigung.</p></div>
  </div>
</div>
    </div>

        <div class="rx-tabpanel" data-tab="results">
      <div class="rx-results">
        <div class="rx-results-top">
          <div>
            <div class="rx-results-purity">&ge;99%</div>
            <div class="rx-results-purity-label">HPLC-Reinheit</div>
          </div>
        </div>
        <div class="rx-results-grid">
          <div class="rx-results-tile">
            <div class="rx-results-tile-k">Analyse</div>
            <div class="rx-results-tile-v">HPLC / LC-MS</div>
          </div>
          <div class="rx-results-tile">
            <div class="rx-results-tile-k">Status</div>
            <div class="rx-results-tile-v"><span class="rx-results-check">&#10003;</span> Bestanden</div>
          </div>
        </div>
        <div class="rx-coa-box">
          <div class="rx-coa-info"><b>Analysezertifikat (COA)</b>Chargenspezifisches Pr&uuml;fprotokoll als PDF</div>
          <span class="rx-coa-btn" aria-disabled="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>
            Demn&auml;chst verf&uuml;gbar
          </span>
        </div>
      </div>
    </div>

    <div class="rx-tabpanel" data-tab="tools">
<style>
.sp-tools-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1200px;margin:0 auto}
@media(max-width:1024px){.sp-tools-grid{grid-template-columns:repeat(2,1fr);gap:20px;max-width:700px}}
@media(max-width:580px){.sp-tools-grid{grid-template-columns:1fr;gap:20px;max-width:400px}}
.sp-tool-card,.sp-tool-card:link,.sp-tool-card:visited,.sp-tool-card *{text-decoration:none!important}
.sp-tool-card{background:linear-gradient(160deg,#0D0F12 0%,#2A2E33 100%);border:1px solid rgba(255,255,255,.06);border-radius:16px;padding:36px 28px 32px;text-align:left;transition:all .35s ease;position:relative;overflow:hidden;display:block;color:inherit}
.sp-tool-card:hover{border-color:rgba(199,204,209,.4);transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.35)}
.sp-tool-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#C7CCD1,transparent);opacity:0;transition:opacity .35s}
.sp-tool-card:hover::before{opacity:1}
.sp-tool-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;font-size:24px;background:rgba(168,176,185,.14);color:#C7CCD1}
.sp-tool-card h3{color:#fff!important;font-size:20px;font-weight:700;margin:0 0 10px}
.sp-tool-card p{color:rgba(255,255,255,.55)!important;font-size:14.5px;line-height:1.6;margin:0 0 22px;min-height:66px}
.sp-tool-cta{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(120deg,#FFFFFF 0%,#DCDEE0 100%);color:#0D0F12!important;font-size:14px;font-weight:600;padding:10px 18px;border-radius:10px;transition:all .25s;position:relative;overflow:hidden;animation:sp-tool-cta-glow 2.2s ease-in-out infinite;}
@keyframes sp-tool-cta-glow{0%,100%{box-shadow:0 0 0 rgba(242,243,244,0);}50%{box-shadow:0 0 18px rgba(242,243,244,.6);}}
@media(prefers-reduced-motion:reduce){.sp-tool-cta{animation:none;}}
.sp-tool-cta::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(13,15,18,.18) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-btn-shine 3.2s ease-in-out infinite;pointer-events:none}
@keyframes sp-btn-shine{0%,55%{left:-75%}100%{left:150%}}
.sp-tool-cta:hover{gap:12px}
.sp-tool-cta svg{transition:transform .25s}
.sp-tool-cta:hover svg{transform:translateX(3px)}
</style>
<div class="sp-tools-grid">
    <a href="/peptid-rechner/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="14" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="16" y2="18"/></svg>
      </div>
      <h3>Peptid-Rechner</h3>
      <p>Berechne die exakte Rekonstitution und Dosierung deiner Peptide – schnell, einfach und präzise.</p>
      <span class="sp-tool-cta">Jetzt berechnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
    <a href="/lagerungs-guide/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </div>
      <h3>Lagerungs-Guide</h3>
      <p>Erfahre wie du Peptide optimal lagerst – Temperatur, Haltbarkeit und Rekonstitutions-Tipps.</p>
      <span class="sp-tool-cta">Guide öffnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
  
    <a href="/peptid-lexikon/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/></svg>
      </div>
      <h3>Peptid-Lexikon</h3>
      <p>Steckbriefe und Forschungsgebiete vieler Peptide im Lexikon nachlesen.</p>
      <span class="sp-tool-cta">Lexikon &ouml;ffnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
  </div>

    </div>
  </div>
</div>

<script>
(function(){
  var nav = document.currentScript.previousElementSibling;
  if (!nav || !nav.classList.contains('rx-tabsec')) {
    nav = document.querySelector('.rx-tabsec');
  }
  if (!nav) return;
  var btns = nav.querySelectorAll('.rx-tabbtn');
  var panels = nav.querySelectorAll('.rx-tabpanel');
  btns.forEach(function(btn){
    btn.addEventListener('click', function(){
      var tab = btn.getAttribute('data-tab');
      btns.forEach(function(b){ b.classList.toggle('is-active', b === btn); });
      panels.forEach(function(p){ p.classList.toggle('is-active', p.getAttribute('data-tab') === tab); });
    });
  });
})();
</script>

SPHTML,
        'rev' => <<<'SPHTML'
<div class="rv-wrap" id="rv-wrap">
  <style>
    #rv-wrap {
      --rv-accent: #C7CCD1;
      --rv-star: #f5a623;
      --rv-bg: #000000;
      --rv-bg-soft: rgba(255,255,255,.10);
      --rv-border: rgba(255,255,255,.10);
      --rv-text: #FFFFFF;
      --rv-text-soft: rgba(255,255,255,.55);
      --rv-radius: 16px;
      --rv-speed: 65s;

      max-width: 100vw;
      width: 100vw;
      margin-left: calc(-50vw + 50%);
      margin-right: calc(-50vw + 50%);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      color: var(--rv-text);
      overflow: hidden;
      box-sizing: border-box;
    }
    #rv-wrap *, #rv-wrap *::before, #rv-wrap *::after { box-sizing: border-box; }

    #rv-wrap .rv-mask {
      position: relative;
      overflow: hidden;
    }

    #rv-wrap .rv-track {
      display: flex;
      gap: 20px;
      width: max-content;
      animation: rv-scroll var(--rv-speed) linear infinite;
    }
    #rv-wrap .rv-mask:hover .rv-track {
      animation-play-state: paused;
    }

    @keyframes rv-scroll {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }

    #rv-wrap .rv-card {
      flex: 0 0 300px;
      background: var(--rv-bg);
      border: 1px solid var(--rv-border);
      border-radius: var(--rv-radius);
      padding: 22px 22px 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    #rv-wrap .rv-stars {
      display: flex;
      gap: 3px;
    }
    #rv-wrap .rv-stars svg {
      width: 15px;
      height: 15px;
      fill: var(--rv-star);
    }

    #rv-wrap .rv-text {
      font-size: 0.9rem;
      line-height: 1.55;
      color: var(--rv-text);
      margin: 0;
      flex-grow: 1;
    }

    #rv-wrap .rv-author {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 4px;
    }
    #rv-wrap .rv-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--rv-bg-soft);
      color: var(--rv-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.8rem;
      flex-shrink: 0;
    }
    #rv-wrap .rv-name {
      font-size: 0.85rem;
      font-weight: 700;
      margin: 0;
    }
    #rv-wrap .rv-meta {
      font-size: 0.75rem;
      color: var(--rv-text-soft);
      margin: 0;
    }

    @media (prefers-reduced-motion: reduce) {
      #rv-wrap .rv-track { animation: none; }
    }
  </style>

  <div class="rv-mask">
    <div class="rv-track" id="rv-track"></div>
  </div>
</div>

<script>
(function () {
  var reviews = [
    { name: "Fabian K.", initials: "FK", meta: "Verifizierter Kauf", stars: 5, text: "Schon meine dritte Bestellung und wieder alles top gerne wieder" },
    { name: "Jasmin T.", initials: "JT", meta: "Verifizierter Kauf", stars: 5, text: "Wirklich zufrieden mit der Qualität und dem ganzen Ablauf. Werde definitiv wieder bestellen." },
    { name: "Robert N.", initials: "RN", meta: "Verifizierter Kauf", stars: 4, text: "Alles gut gelaufen, bin zufrieden." },
    { name: "Vanessa B.", initials: "VB", meta: "Verifizierter Kauf", stars: 5, text: "Ich bin jetzt schon bei der dritten Bestellung und bin nach wie vor begeistert von der Qualität, den Zertifikaten und dem schnellen Versand. Absolute Kaufempfehlung, komme auf jeden Fall wieder." },
    { name: "Sebastian R.", initials: "SR", meta: "Verifizierter Kauf", stars: 5, text: "top service komme wieder" },
    { name: "Katharina D.", initials: "KD", meta: "Verifizierter Kauf", stars: 5, text: "Hat alles gepasst, bin sehr zufrieden und werde wieder bestellen." },
    { name: "Marco P.", initials: "MP", meta: "Verifizierter Kauf", stars: 4, text: "Gute Erfahrung gemacht auch wenn der Versand einen Tag länger gedauert hat als angegeben aber inhaltlich alles top" },
    { name: "Nadine S.", initials: "NS", meta: "Verifizierter Kauf", stars: 5, text: "Bestelle hier jetzt regelmäßig weil einfach alles passt Qualität Verpackung und der Support" },
    { name: "Tobias W.", initials: "TW", meta: "Verifizierter Kauf", stars: 5, text: "Sehr zufrieden. Zweite Bestellung war genauso gut wie die erste." },
    { name: "Michaela H.", initials: "MH", meta: "Verifizierter Kauf", stars: 5, text: "Rundum zufrieden, komme auf jeden Fall wieder" }
  ];

  function starSvg() {
    return '<svg viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.4 6 .8-4.3 4.1 1 5.9L10 14.9l-5.3 2.8 1-5.9L1.4 7.7l6-.8L10 1.5z"/></svg>';
  }

  function cardHtml(r) {
    var stars = '';
    for (var i = 0; i < 5; i++) {
      stars += i < r.stars ? starSvg() : '<span style="opacity:.25">' + starSvg() + '</span>';
    }
    return '' +
      '<div class="rv-card">' +
        '<div class="rv-stars">' + stars + '</div>' +
        '<p class="rv-text">' + r.text + '</p>' +
        '<div class="rv-author">' +
          '<div class="rv-avatar">' + r.initials + '</div>' +
          '<div>' +
            '<p class="rv-name">' + r.name + '</p>' +
            '<p class="rv-meta">' + r.meta + '</p>' +
          '</div>' +
        '</div>' +
      '</div>';
  }

  var track = document.getElementById('rv-track');
  var html = reviews.map(cardHtml).join('');
  track.innerHTML = html + html;
})();
</script>
SPHTML,
        'lex' => <<<'SPHTML'
<style>
.sp-sci-section{max-width:900px;margin:32px auto 0;font-family:'Sora',sans-serif;}
.sp-sci-section h3{font-size:24px;font-weight:700;color:#0D0F12;margin:0 0 20px;text-align:center}
.sp-sci-card{background:linear-gradient(137deg,#0D0F12 0%,#1C1F23 100%);border-radius:20px;padding:32px 30px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;position:relative;overflow:hidden;}
.sp-sci-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#C7CCD1,transparent);}
.sp-sci-badge{display:inline-block;background:rgba(255,255,255,.1);color:#C7CCD1;font-size:11.5px;font-weight:700;letter-spacing:2px;padding:5px 14px;border-radius:20px;margin-bottom:12px;text-transform:uppercase;}
.sp-sci-title{font-size:20px;font-weight:700;color:#fff;margin:0 0 8px;}
.sp-sci-desc{font-size:14.5px;color:rgba(255,255,255,.65);line-height:1.6;margin:0;max-width:480px;}
.sp-sci-cta{flex-shrink:0;display:inline-flex;align-items:center;gap:8px;background:#fff;color:#0D0F12;font-weight:700;font-size:14.5px;padding:13px 24px;border-radius:10px;text-decoration:none;white-space:nowrap;}
@media(max-width:600px){.sp-sci-card{padding:26px 22px;}}
</style>
<div class="sp-sci-section">
  <div class="sp-sci-card">
    <div>
      <span class="sp-sci-badge">Ratgeber</span>
      <h4 class="sp-sci-title">Peptide richtig rekonstituieren</h4>
      <p class="sp-sci-desc">Schritt für Schritt: Bac Water, Konzentration berechnen und Lagerung nach dem Anmischen.</p>
    </div>
    <a href="/ratgeber/peptide-rekonstituieren/" class="sp-sci-cta">Zum Ratgeber <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
  </div>
</div>
SPHTML,
        'faq' => <<<'SPHTML'

<style>
.sp-reta-faq{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-reta-faq h3{font-size:24px;font-weight:700;color:#0D0F12;margin:0 0 20px;text-align:center}
.sp-faq-item{border:1px solid #DCDEE0;border-radius:12px;margin-bottom:12px;padding:16px 20px;background:#F2F3F4}
.sp-faq-item summary{cursor:pointer;list-style:none;font-weight:700;font-size:15.5px;color:#0D0F12;display:flex;justify-content:space-between;align-items:center}
.sp-faq-item summary::-webkit-details-marker{display:none}
.sp-faq-arrow{font-size:11px;color:#A8B0B9;transition:transform .25s}
.sp-faq-item[open] .sp-faq-arrow{transform:rotate(180deg)}
.sp-faq-item p{margin:12px 0 0;font-size:14.5px;line-height:1.65;color:#4B5157}
</style>
<div class="sp-reta-faq">
<h3>Häufig gestellte Fragen zu MT-2</h3>

  <details class="sp-faq-item">
    <summary>Was ist Melanotan II?<span class="sp-faq-arrow">▼</span></summary>
    <p>Melanotan II ist ein synthetisches, zyklisches Analogon des Hormons α-MSH. Es wurde als Forschungssubstanz entwickelt, ist nicht als Arzneimittel zugelassen und wird ausschließlich im Laborkontext untersucht.</p>
  </details>
  <details class="sp-faq-item">
    <summary>An welchen Rezeptoren wird MT-2 untersucht?<span class="sp-faq-arrow">▼</span></summary>
    <p>MT-2 gilt als nicht-selektiver Agonist an den Melanocortin-Rezeptoren MC1R, MC3R, MC4R und MC5R. Gerade diese breite Rezeptoraffinität macht es für die Grundlagenforschung zu Melanocortin-Signalwegen interessant.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Worin unterscheidet sich MT-2 von Melanotan I?<span class="sp-faq-arrow">▼</span></summary>
    <p>Melanotan I (Afamelanotid) ist ein lineares Peptid mit höherer Selektivität für MC1R. Melanotan II ist kürzer, zyklisch aufgebaut und spricht mehrere Melanocortin-Rezeptoren an.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Wie wird MT-2 geliefert?<span class="sp-faq-arrow">▼</span></summary>
    <p>Als lyophilisiertes (gefriergetrocknetes) Pulver im Vial (10 mg). Für die Arbeit im Labor wird es mit bakteriostatischem Wasser rekonstituiert – die Konzentration berechnest du mit unserem Peptid-Rechner.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Wie wird MT-2 gelagert?<span class="sp-faq-arrow">▼</span></summary>
    <p>Lyophilisiert kühl bei 2–8 °C und lichtgeschützt, für längere Zeit bei −20 °C. Nach dem Rekonstituieren im Kühlschrank aufbewahren und nicht wieder einfrieren.</p>
  </details>
</div>
SPHTML,
        'rel' => <<<'SPHTML'

<style>
.sp-rel-section{max-width:900px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-rel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
.sp-rel-title{font-size:22px;font-weight:700;color:#0D0F12;margin:0}
.sp-rel-more{display:inline-flex;align-items:center;gap:6px;background:#F2F3F4;border:1px solid #DCDEE0;color:#0D0F12;font-family:'Sora',sans-serif;font-weight:700;font-size:13px;padding:8px 14px;border-radius:999px;text-decoration:none!important;white-space:nowrap;transition:all .2s;flex-shrink:0}
.sp-rel-more:hover{background:#0D0F12;color:#fff;border-color:#0D0F12}
.sp-rel-more svg{width:14px;height:14px;transition:transform .2s}
.sp-rel-more:hover svg{transform:translateX(2px)}
.sp-rel-dots{display:flex;justify-content:center;gap:6px;margin-top:14px}
.sp-rel-dot{width:6px;height:6px;border-radius:50%;background:#DCDEE0;transition:all .25s}
.sp-rel-dot.is-active{background:#0D0F12;width:18px;border-radius:4px}
.sp-rel-scroll{display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;padding-bottom:6px;scrollbar-width:thin}
.sp-rel-scroll::-webkit-scrollbar{height:6px}
.sp-rel-scroll::-webkit-scrollbar-thumb{background:#DCDEE0;border-radius:10px}
.sp-rel-card{flex:0 0 auto;width:calc((100% - 28px) / 3);min-width:150px;scroll-snap-align:start;background:#fff;border:1px solid #DCDEE0;border-radius:14px;overflow:hidden;text-decoration:none!important;display:block;color:inherit;transition:all .25s}
.sp-rel-card:hover{border-color:#A8B0B9;transform:translateY(-3px);box-shadow:0 8px 20px rgba(13,15,18,.08)}
.sp-rel-card-img{width:100%;aspect-ratio:1/1;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden}
.sp-rel-card-img img{width:72%;height:72%;object-fit:contain}
.sp-rel-card-body{padding:10px 12px 14px;background:#F2F3F4}
.sp-rel-card h3{font-size:13px;font-weight:700;color:#0D0F12;margin:0 0 4px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sp-rel-card-price{font-size:13.5px;font-weight:700;color:#0D0F12}
@media(max-width:640px){.sp-rel-card{width:calc((100% - 14px) / 2)}}
</style>
<div class="sp-rel-section">
  <div class="sp-rel-head">
    <div class="sp-rel-title">Wird h&auml;ufig zusammen gekauft</div>
    <a href="https://peptrium.com/alle-produkte/" class="sp-rel-more">Mehr <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
  </div>
  <div class="sp-rel-scroll">
    <a href="https://peptrium.com/produkt/glow/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/10/glow-peptrium-300x300.png" alt="GLOW" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>GLOW</h3>
        <div class="sp-rel-card-price">139,90&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/ghk-cu/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png" alt="GHK-Cu" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>GHK-Cu</h3>
        <div class="sp-rel-card-price">ab 39,90&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/kpv/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/9A5E6104-889F-47CB-B191-D707F7FB77A2-300x300.png" alt="KPV" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>KPV</h3>
        <div class="sp-rel-card-price">29,67&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/bac-water/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png" alt="Bac Water" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>Bac Water</h3>
        <div class="sp-rel-card-price">9,90&nbsp;&euro;</div>
      </div>
    </a>
  </div>
  <div class="sp-rel-dots" id="sp-rel-dots">
    <span class="sp-rel-dot is-active"></span>
    <span class="sp-rel-dot"></span>
    <span class="sp-rel-dot"></span>
    <span class="sp-rel-dot"></span>
  </div>
</div>
<script>
(function(){
  var scrollEl = document.querySelector('.sp-rel-scroll');
  var dots = document.querySelectorAll('.sp-rel-dot');
  if (!scrollEl || !dots.length) return;
  function update(){
    var maxScroll = scrollEl.scrollWidth - scrollEl.clientWidth;
    var progress = maxScroll > 0 ? scrollEl.scrollLeft / maxScroll : 0;
    var idx = Math.round(progress * (dots.length - 1));
    dots.forEach(function(d,i){ d.classList.toggle('is-active', i === idx); });
  }
  scrollEl.addEventListener('scroll', update, {passive:true});
  update();
})();
</script>

SPHTML,
    ];
    $p[1055] = [
        'img' => "https://peptrium.com/wp-content/uploads/2026/10/glow-peptrium.png",
        'alt' => "GLOW \u2013 Peptrium",
        'hero' => <<<'SPHTML'

<style>
#motsc-kundenstimmen{scroll-margin-top:100px}
.sp-reta-rating{display:flex;align-items:center;gap:8px;margin-bottom:10px;text-decoration:none;cursor:pointer;width:fit-content}
.sp-rate-stars{position:relative;display:inline-block;line-height:1;font-size:16px;letter-spacing:2px}
.sp-rate-stars-bg{color:#DCDEE0}
.sp-rate-stars-fg{position:absolute;top:0;left:0;overflow:hidden;white-space:nowrap;color:#f5a623}
.sp-reta-rating-num{font-size:14px;font-weight:700;color:#0D0F12}
.sp-reta-rating-count{font-size:13px;color:#4B5157;text-decoration:underline;text-decoration-color:#A8B0B9;text-underline-offset:2px}
.sp-reta-rating:hover .sp-reta-rating-count{color:#0D0F12}
.sp-reta-hero{font-family:'Sora',sans-serif}
.sp-reta-title{font-size:28px;font-weight:700;color:#0D0F12;margin:0 0 6px;line-height:1.2}
.sp-reta-subtitle-line{font-size:14px;font-weight:600;color:#4B5157;margin:0 0 18px}
.sp-reta-checks{display:flex;flex-direction:column;gap:10px;margin:0 0 22px}
.sp-reta-check-item{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#0D0F12}
.sp-reta-check-item svg{width:18px;height:18px;color:#16a34a;flex-shrink:0}
#sp-reta-buybox-el{border:1px solid #DCDEE0;border-radius:16px;padding:22px 24px;margin-bottom:20px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
#sp-reta-buybox-el .woocommerce{margin:0}
#sp-reta-buybox-el .price{font-family:'Sora',sans-serif;font-size:30px;font-weight:700;color:#0D0F12;display:block;margin-bottom:16px}
#sp-reta-buybox-el .price ins{text-decoration:none;color:#0D0F12}
#sp-reta-buybox-el form.cart{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
#sp-reta-buybox-el .quantity{margin:0}
#sp-reta-buybox-el .quantity .qty{width:64px;padding:12px 10px;border:1px solid #DCDEE0;border-radius:10px;font-size:15px;text-align:center}
#sp-reta-buybox-el .single_add_to_cart_button,
#sp-reta-buybox-el button[type="submit"]{border:none!important;padding:14px 28px!important;border-radius:10px!important;font-weight:700!important;font-size:15px!important;cursor:pointer;position:relative;overflow:hidden;transition:transform .2s}
#sp-reta-buybox-el .single_add_to_cart_button:hover{transform:translateY(-2px)}
@keyframes sp-reta-shine{0%,55%{left:-75%}100%{left:150%}}
.sp-reta-qualitybox{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:16px;padding:20px 22px;margin-bottom:16px}
.sp-reta-qualitybox h4{margin:0 0 4px;font-size:16px;font-weight:700;color:#0D0F12}
.sp-reta-qualitybox p{margin:0 0 10px;font-size:13.5px;color:#4B5157}
.sp-reta-qualitybox .sp-reta-check{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #DCDEE0;border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;color:#0D0F12}
.sp-reta-dhl{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(180deg,#0D0F12 0%,#1C1F23 100%);color:#fff;font-size:13.5px;font-weight:600;padding:10px 18px;border-radius:12px;margin-bottom:18px}
.sp-reta-dhl .sp-dhl-shine{background-image:linear-gradient(100deg,#FFFFFF 0%,#C7CCD1 35%,#FFFFFF 50%,#C7CCD1 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;font-weight:700;animation:sp-tb-shine2 3.2s linear infinite}
@keyframes sp-tb-shine2{0%{background-position:200% center}100%{background-position:0% center}}
.sp-reta-disclaimer{font-size:12.5px;color:#4B5157;line-height:1.5;max-width:520px}
@media(max-width:782px){.sp-reta-buybox .price{font-size:26px}}
</style>

<div class="sp-reta-hero">
  <a href="#motsc-kundenstimmen" class="sp-reta-rating">
    <span class="sp-rate-stars"><span class="sp-rate-stars-bg">&#9733;&#9733;&#9733;&#9733;&#9733;</span><span class="sp-rate-stars-fg" style="width:98%">&#9733;&#9733;&#9733;&#9733;&#9733;</span></span>
    <span class="sp-reta-rating-num">4,9</span>
    <span class="sp-reta-rating-count">(21 Bewertungen)</span>
  </a>
  <h1 class="sp-reta-title">GLOW</h1>
  <p class="sp-reta-subtitle-line">Forschungspeptid &ndash; Nicht f&uuml;r den menschlichen Verzehr</p>
  <div class="sp-reta-checks">
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>99 % Reinheit (HPLC)</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>LC-MS Identit&auml;tsverifizierung</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>Chargenspezifisches Analysezertifikat (COA)</span></div>
    <div class="sp-reta-check-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><span>Diskrete Verpackung &amp; schneller Versand</span></div>
  </div>
</div>

SPHTML,
        'bb' => <<<'SPHTML'
<style>
.rx-buybox{border:1px solid #DCDEE0;border-radius:16px;padding:22px 24px;margin-bottom:14px;font-family:'Sora',sans-serif;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.rx-price-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px}
.rx-price-block{display:flex;align-items:baseline;gap:10px}
.rx-price{font-size:30px;font-weight:700;color:#0D0F12}
.rx-price-note{font-size:13px;color:#4B5157}
.rx-stock{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#16a34a}
.rx-stock-dot{width:9px;height:9px;border-radius:50%;background:#2bdc6b;display:inline-block;animation:rx-blink 1.4s ease-in-out infinite}
@keyframes rx-blink{
  0%,100%{opacity:1;box-shadow:0 0 6px 2px rgba(43,220,107,.9),0 0 16px 6px rgba(43,220,107,.55)}
  50%{opacity:.55;box-shadow:0 0 3px 1px rgba(43,220,107,.5),0 0 6px 2px rgba(43,220,107,.22)}
}
@keyframes rx-amt-shine{0%{background-position:200% center}100%{background-position:0% center}}
.rx-amt-shine{background-image:linear-gradient(100deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;font-weight:700;animation:rx-amt-shine 2.6s linear infinite;white-space:nowrap}
.rx-shipbadges{margin-bottom:16px}
.rx-shipbadges .sp-reta-dhl{padding:4px 10px 4px 4px;font-size:10.5px;font-weight:600;gap:7px;margin-bottom:0;border-radius:7px;background:#F2F3F4!important;color:#0D0F12!important;border:1px solid #DCDEE0;position:relative;overflow:hidden;box-shadow:none}
.rx-shipbadges .sp-dhl-shine{background:none!important;-webkit-text-fill-color:#0D0F12!important;color:#0D0F12!important;animation:none!important;font-weight:700!important}
.rx-shipbadges .sp-dhl-iconwrap{display:inline-flex;align-items:center;justify-content:center;width:19px;height:19px;background:#FFCC00;border-radius:5px;flex-shrink:0}
.rx-shipbadges .sp-reta-dhl svg{width:11px;height:11px}
.rx-shipbadges .sp-reta-dhl svg{width:15px;height:15px}
.rx-tiers-head{font-size:13px;font-weight:700;color:#363C42;text-transform:uppercase;letter-spacing:.03em;margin-bottom:10px}
.rx-tiers{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:18px}
.rx-tiers.rx-tiers--single{grid-template-columns:repeat(4,1fr);margin-bottom:18px}
.rx-tier-wrap{position:relative}
.rx-tier{position:relative;overflow:hidden;border:1.5px solid #DCDEE0;border-radius:12px;background:#fff;padding:14px 6px 10px;display:flex;flex-direction:column;align-items:center;cursor:pointer;transition:border-color .15s,background .15s;width:100%;box-sizing:border-box}
.rx-tier:hover{border-color:#0D0F12}
.rx-tier.is-active{border-color:#0D0F12;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%)}
.rx-tier.is-active .rx-tier__q{color:#fff}
.rx-tier.is-active .rx-tier__unit{color:#C7CCD1}
.rx-tier.is-active .rx-tier__disc{color:#2f7d4f}

.rx-tier--static{cursor:default}
.rx-tier__flag{position:absolute;top:-11px;left:50%;transform:translateX(-50%);z-index:2;overflow:hidden;background:linear-gradient(135deg,#F2F3F4 0%,#A8B0B9 100%);font-size:10px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap;box-shadow:0 2px 8px rgba(13,15,18,.35)}
.rx-tier__flag-text{background-image:linear-gradient(100deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;font-weight:700;animation:rx-amt-shine 2.6s linear infinite}
.rx-tier__flag::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(13,15,18,.15) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-reta-shine 3.2s ease-in-out infinite;pointer-events:none}
.rx-tier__disc{font-size:10.5px;font-weight:700;color:#2f7d4f;margin-bottom:2px}
.rx-tier__q{font-size:18px;font-weight:700;color:#0D0F12}
.rx-tier__unit{font-size:11px;color:#4B5157;font-weight:600}
.rx-buysum{display:flex;flex-direction:column;gap:8px;width:100%;box-sizing:border-box;background:linear-gradient(135deg,#2E3237 0%,#454B52 100%);border-radius:12px;padding:12px 16px;margin-bottom:8px}
.rx-buysum-top{display:flex;align-items:center;justify-content:space-between;gap:16px}
.rx-buysum__total{display:flex;align-items:baseline;gap:8px;text-align:left}
.rx-buysum__label{font-size:11.5px;color:rgba(255,255,255,.55);font-weight:600;text-transform:uppercase;letter-spacing:.02em}
.rx-buysum__total b{font-size:26px;color:#FFFFFF;white-space:nowrap;font-weight:700;line-height:1.1}
.rx-sv{background:#2f7d4f;color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;white-space:nowrap;width:fit-content}
.rx-shiphint{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:#4B5157;flex-wrap:wrap;margin-bottom:12px}
.rx-shiphint.is-done{color:#2f7d4f}
.rx-shiphint-ic{width:17px;height:17px;flex-shrink:0}
.rx-buycta{display:flex;gap:10px;align-items:stretch;margin-bottom:16px}
.rx-qtybox{display:inline-flex;align-items:stretch;border:1px solid rgba(255,255,255,.18);border-radius:10px;overflow:hidden;box-sizing:border-box;flex-shrink:0}
.rx-qtybox button{-webkit-appearance:none;appearance:none;box-sizing:border-box;width:32px;height:36px;border:none;border-radius:0;background:#FFFFFF;color:#0D0F12;font-size:15px;line-height:1;cursor:pointer;padding:0}
.rx-qtybox button:first-child{border-right:1px solid rgba(255,255,255,.18)}
.rx-qtybox button:last-child{border-left:1px solid rgba(255,255,255,.18)}
.rx-qtybox input{-webkit-appearance:none;appearance:none;box-sizing:border-box;width:36px;height:36px;border:none;border-radius:0;text-align:center;font-size:13.5px;font-weight:700;color:#0D0F12;background:#fff;padding:0}
.rx-qtybox input[type=number]::-webkit-inner-spin-button,
.rx-qtybox input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
.rx-qtybox input[type=number]{-moz-appearance:textfield}
.rx-cartslot #sp-reta-buybox-el{display:block;width:100%;height:100%;border:none!important;padding:0!important;margin:0!important;box-shadow:none!important;border-radius:0!important;background:transparent!important}
.rx-pay-in{border-top:1px solid #DCDEE0;padding-top:16px;margin-top:14px}
.rx-pay-in__k{display:block;font-size:11.5px;color:#4B5157;font-weight:600;margin-bottom:8px}
.rx-pay-in__row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.rx-paychip{display:inline-flex;align-items:center;gap:6px;background:#F2F3F4;border:1px solid #DCDEE0;border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700;color:#4B5157}.rx-paychip svg{width:14px;height:14px;flex-shrink:0}.rx-paychip--vorkasse svg{color:#2E7DD1}.rx-paychip--krypto svg{color:#F7931A}
.rx-trustbadges{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:14px}
.rx-trustbadge{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;background:#F9FAFA;border:1px solid #DCDEE0;border-radius:8px;padding:7px 4px;text-align:center}
.rx-trustbadge svg{width:15px;height:15px;color:#0D0F12;flex-shrink:0}.rx-trustbadge:nth-child(1) svg{color:#2E7DD1}.rx-trustbadge:nth-child(2) svg{color:#1F7A4D}.rx-trustbadge:nth-child(3) svg{color:#7C3AED}.rx-trustbadge:nth-child(4) svg{color:#8B5E3C}
.rx-trustbadge span{font-size:8px;font-weight:700;letter-spacing:.01em;color:#4B5157;line-height:1.15}
.rx-disclaimer{font-size:12px;color:#4B5157;line-height:1.5;margin:0}.rx-social-proof{display:flex;align-items:center;gap:10px;margin:14px 0 0;padding-top:14px;border-top:1px solid #DCDEE0;}.rx-sp-avatars{display:flex;flex-shrink:0;}.rx-sp-avatar{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 0 0 1px #DCDEE0;margin-left:-8px;box-sizing:border-box;overflow:hidden;}.rx-sp-avatar:first-child{margin-left:0;}.rx-sp-avatar img{width:100%;height:100%;object-fit:cover;display:block;}.rx-sp-text{font-size:12.5px;font-weight:700;color:#0D0F12;}
#sp-reta-buybox-el .price{display:none!important}
#sp-reta-buybox-el .quantity{display:none!important;position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;margin:0!important;padding:0!important}
#sp-reta-buybox-el .added_to_cart,
#sp-reta-buybox-el .wc-forward,
#sp-reta-buybox-el a.added_to_cart{display:none!important}
#sp-reta-buybox-el form.cart{display:block!important;height:100%}
#sp-reta-buybox-el .single_add_to_cart_button{width:100%;height:46px;justify-content:center;display:flex!important;align-items:center;gap:8px;margin:0!important;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%)!important;color:#fff!important;white-space:nowrap;font-size:15.5px;font-weight:600;box-sizing:border-box}
#sp-reta-buybox-el .single_add_to_cart_button::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(255,255,255,.45) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-reta-shine 3.2s ease-in-out infinite;pointer-events:none}
.rx-cartslot{flex:1;min-width:0}
.rx-addons{margin-bottom:16px}
.rx-addon-row{display:flex;flex-direction:column;gap:8px;padding:10px 0;border-bottom:1px solid #F2F3F4}
.rx-addon-row:last-child{border-bottom:none}
.rx-addon-top{display:flex;align-items:center;gap:10px}
.rx-addon-img{width:36px;height:36px;border-radius:10px!important;object-fit:cover;background:#F2F3F4;flex-shrink:0}
.rx-addon-name{font-size:13.5px;font-weight:600;color:#0D0F12;flex:1;min-width:0}
.rx-addon-btn{font-family:'Sora',sans-serif;font-size:12px;font-weight:600;border-radius:999px;padding:7px 12px;cursor:pointer;border:1px solid #DCDEE0;background:#fff;color:#4B5157;transition:border-color .15s,background .15s,color .15s;white-space:nowrap;flex-shrink:0}
.rx-addon-btn.is-active{background:#0D0F12;color:#fff;border-color:#0D0F12}
.rx-addon-btn:not(.is-active):hover{border-color:#A8B0B9}
.rx-addon-price{opacity:.85}
.rx-addon-btn-remove{display:none}
.rx-addon-btn.is-active .rx-addon-btn-add{display:none}
.rx-addon-btn.is-active .rx-addon-btn-remove{display:inline}
.rx-addon-qty{display:none;align-items:center;gap:10px;margin-left:46px}
.rx-addon-row.is-active .rx-addon-qty{display:flex}
.rx-addon-qtybtn{width:26px;height:26px;border-radius:8px;border:1px solid #DCDEE0;background:#fff;color:#0D0F12;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;line-height:1;padding:0}
.rx-addon-qtybtn:hover{border-color:#A8B0B9}
.rx-addon-qtyval{font-size:13px;font-weight:700;color:#0D0F12;min-width:16px;text-align:center}
@media(max-width:480px){.rx-tier__q{font-size:15px}.rx-price{font-size:26px}.rx-buysum__total{font-size:13px}.rx-buysum__total b{font-size:16px}.rx-addon-name{font-size:12.5px}.rx-addon-btn{font-size:11px;padding:6px 9px}}
</style>
<div class="rx-buybox" id="rx-buybox">
  <div class="rx-price-row">
    <div class="rx-price-block">
      <span class="rx-price" id="rx-price">139,90&nbsp;&euro;</span>
      <span class="rx-price-note">zzgl. Versand</span>
    </div>
    <div class="rx-stock"><span class="rx-stock-dot"></span> Auf Lager &middot; sofort versandfertig</div>
  </div>

  <div class="rx-shipbadges">
    <div class="sp-reta-dhl">
      <span class="sp-dhl-iconwrap"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D40511" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="15" height="11"/><path d="M16 10h3l3 3v4h-6z"/><circle cx="6" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/></svg></span>
      <span>DHL PREMIUM &middot; <span class="sp-dhl-shine">2 Tage Lieferung</span></span>
    </div>
  </div>

  <div class="rx-tiers-head">Volumen</div>
  <div class="rx-tiers rx-tiers--single">
    <div class="rx-tier is-active rx-tier--static">
      <span class="rx-tier__q">70</span><span class="rx-tier__unit">mg</span>
    </div>
  </div>

  <div class="rx-tiers-head">Menge &amp; Vorteil w&auml;hlen</div>
  <div class="rx-tiers" id="rx-tiers">
    <button type="button" class="rx-tier is-active" data-q="1" data-disc="0">
      <span class="rx-tier__disc">Standard</span>
      <span class="rx-tier__q">1</span><span class="rx-tier__unit">Vial</span>
    </button>
    <div class="rx-tier-wrap">
      <span class="rx-tier__flag"><span class="rx-tier__flag-text">Beliebt</span></span>
      <button type="button" class="rx-tier" data-q="3" data-disc="10">
        <span class="rx-tier__disc">&minus;10%</span>
        <span class="rx-tier__q">3</span><span class="rx-tier__unit">Vials</span>
      </button>
    </div>
    <button type="button" class="rx-tier" data-q="5" data-disc="15">
      <span class="rx-tier__disc">&minus;15%</span>
      <span class="rx-tier__q">5</span><span class="rx-tier__unit">Vials</span>
    </button>
    <button type="button" class="rx-tier" data-q="10" data-disc="20">
      <span class="rx-tier__disc">&minus;20%</span>
      <span class="rx-tier__q">10</span><span class="rx-tier__unit">Vials</span>
    </button>
  </div>

  <div class="rx-addons" id="rx-addons">
    <div class="rx-tiers-head">Zubeh&ouml;r</div>
    <div class="rx-addon-row" data-field="sp_addon_bacwater" data-price="9.90">
      <div class="rx-addon-top">
        <img class="rx-addon-img" src="https://peptrium.com/wp-content/uploads/2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-150x150.png" alt="">
        <span class="rx-addon-name">Bac Water 3&nbsp;ml</span>
        <button type="button" class="rx-addon-btn rx-addon-btn--add" data-choice="1"><span class="rx-addon-btn-add">Hinzuf&uuml;gen <span class="rx-addon-price">+9,90&nbsp;&euro;</span></span><span class="rx-addon-btn-remove">Entfernen</span></button>
      </div>
      <div class="rx-addon-qty">
        <button type="button" class="rx-addon-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <span class="rx-addon-qtyval">1</span>
        <button type="button" class="rx-addon-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
    <div class="rx-addon-row" data-field="sp_addon_insulin" data-price="9.90">
      <div class="rx-addon-top">
        <img class="rx-addon-img" src="https://peptrium.com/wp-content/uploads/2026/08/insulinspritze-10er-pack-wide-150x150.png" alt="">
        <span class="rx-addon-name">Insulinspritze 10er Pack</span>
        <button type="button" class="rx-addon-btn rx-addon-btn--add" data-choice="1"><span class="rx-addon-btn-add">Hinzuf&uuml;gen <span class="rx-addon-price">+9,90&nbsp;&euro;</span></span><span class="rx-addon-btn-remove">Entfernen</span></button>
      </div>
      <div class="rx-addon-qty">
        <button type="button" class="rx-addon-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <span class="rx-addon-qtyval">1</span>
        <button type="button" class="rx-addon-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
  </div>

  <div class="rx-shiphint" id="rx-shiphint"></div>

  <div class="rx-buysum">
    <div class="rx-buysum-top">
      <div class="rx-buysum__total"><span class="rx-buysum__label">Gesamt</span><b id="rx-total">139,90&nbsp;&euro;</b></div>
      <div class="rx-qtybox">
        <button type="button" class="rx-qtybtn" data-step="-1" aria-label="weniger">&minus;</button>
        <input type="number" id="rx-qtyfield" min="1" step="1" value="1" readonly>
        <button type="button" class="rx-qtybtn" data-step="1" aria-label="mehr">+</button>
      </div>
    </div>
    <span class="rx-sv" id="rx-save" style="display:none">Du sparst 10,47&nbsp;&euro;</span>
  </div>

  <div class="rx-buycta">
    <div class="rx-cartslot" id="rx-cartrow"></div>
  </div>

  <div class="rx-social-proof" id="rx-social-proof">
    <div class="rx-sp-avatars">
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-4.jpg" alt="" loading="lazy"></span>
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-5.jpg" alt="" loading="lazy"></span>
      <span class="rx-sp-avatar"><img src="https://peptrium.com/wp-content/uploads/2026/09/sp-kunde-avatar-3.jpg" alt="" loading="lazy"></span>
    </div>
    <span class="rx-sp-text">&Uuml;ber 2.500+ zufriedene Kunden</span>
  </div>

  <div class="rx-pay-in">
    <div class="rx-trustbadges">
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v7.31"/><path d="M14 9.3V1.99"/><path d="M8.5 2h7"/><path d="M14 9.3a6.5 6.5 0 1 1-4 0"/><path d="M5.58 16.5h12.85"/></svg><span>HPLC &ge;99%</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg><span>COA</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18h8"/><path d="M3 22h18"/><path d="M14 22a7 7 0 1 0 0-14h-1"/><path d="M9 14h2"/><path d="M9 12a4 4 0 0 0-4 4v2"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg><span>LC-MS</span></div>
      <div class="rx-trustbadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73Z"/><path d="M3.29 7 12 12l8.71-5"/><path d="M12 22V12"/></svg><span>Diskret</span></div>
    </div>
    <span class="rx-pay-in__k">Sichere Bezahlung</span>
    <div class="rx-pay-in__row">
      <span class="rx-paychip rx-paychip--vorkasse"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg>Vorkasse</span>
      <span class="rx-paychip rx-paychip--krypto"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.638 14.904c-1.602 6.43-8.113 10.34-14.542 8.736C2.67 22.05-1.244 15.525.362 9.105 1.962 2.67 8.475-1.243 14.9.358c6.43 1.605 10.342 8.115 8.738 14.548v-.002zm-6.35-4.613c.24-1.59-.974-2.45-2.64-3.03l.54-2.153-1.315-.33-.525 2.107c-.345-.087-.705-.167-1.064-.25l.526-2.127-1.32-.33-.54 2.165c-.285-.065-.565-.13-.84-.2l-1.815-.45-.35 1.407s.975.225.955.236c.535.136.63.486.615.766l-.615 2.46c.035.01.08.024.13.048l-.13-.033-.865 3.46c-.065.164-.23.41-.606.316.015.02-.96-.24-.96-.24l-.655 1.51 1.71.426.93.242-.54 2.19 1.32.327.54-2.17c.36.1.705.19 1.05.273l-.51 2.154 1.32.33.545-2.19c2.24.427 3.93.257 4.64-1.774.57-1.63-.03-2.57-1.21-3.184.86-.198 1.51-.76 1.68-1.93h.01zm-3.01 4.22c-.404 1.64-3.157.75-4.05.53l.72-2.9c.896.23 3.757.67 3.33 2.37zm.41-4.24c-.37 1.49-2.662.735-3.405.55l.654-2.64c.744.18 3.137.53 2.75 2.084v.006z"/></svg>Krypto</span>
    </div>
    <p class="rx-disclaimer">Nicht f&uuml;r den menschlichen oder veterin&auml;rmedizinischen Gebrauch. Nur f&uuml;r Laborforschungszwecke.</p>
  </div>
</div>

<script>
(function(){
  var unitPrice = 139.90;
  var freeShipThreshold = 150;
  var truckIcon = '<svg class="rx-shiphint-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="15" height="11"/><path d="M16 10h3l3 3v4h-6z"/><circle cx="6" cy="19" r="1.6"/><circle cx="18" cy="19" r="1.6"/></svg>';

  function fmt(n){
    return n.toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' €';
  }

  function hideRealQty(){
    var el = document.querySelector('#sp-reta-buybox-el .quantity');
    if (el) {
      el.setAttribute('style', 'display:none!important;position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;margin:0!important;padding:0!important;');
    }
    var links = document.querySelectorAll('#sp-reta-buybox-el .added_to_cart, #sp-reta-buybox-el .wc-forward');
    links.forEach(function(l){ l.style.display = 'none'; });
  }

  function init(){
    var cartRow = document.getElementById('rx-cartrow');
    var realBox = document.getElementById('sp-reta-buybox-el');
    if (cartRow && realBox) { cartRow.appendChild(realBox); }
    hideRealQty();

    function ensureAddonInputs(){
      var form = document.querySelector('#sp-reta-buybox-el form.cart');
      if (!form) return null;
      document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
        var field = row.getAttribute('data-field');
        if (!form.querySelector('input[name="' + field + '"]')) {
          var inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = field;
          inp.value = '0';
          form.appendChild(inp);
        }
      });
      return form;
    }

    document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
      var field = row.getAttribute('data-field');
      var addBtn = row.querySelector('.rx-addon-btn--add');
      var qtyValEl = row.querySelector('.rx-addon-qtyval');
      var qty = 0;
      function render(){
        var active = qty > 0;
        row.classList.toggle('is-active', active);
        addBtn.classList.toggle('is-active', active);
        if (qtyValEl) qtyValEl.textContent = qty || 1;
        var form = ensureAddonInputs();
        if (form) {
          var inp = form.querySelector('input[name="' + field + '"]');
          if (inp) inp.value = String(qty);
        }
        if (qtyField) { update(parseInt(qtyField.value, 10)); }
      }
      addBtn.addEventListener('click', function(){ qty = addBtn.classList.contains('is-active') ? 0 : 1; render(); });
      row.querySelectorAll('.rx-addon-qtybtn').forEach(function(btn){
        btn.addEventListener('click', function(){
          var step = parseInt(btn.getAttribute('data-step'), 10);
          qty = Math.max(0, Math.min(10, qty + step));
          render();
        });
      });
      render();
    });
    ensureAddonInputs();

    if (window.jQuery) {
      jQuery(document.body).on('added_to_cart updated_wc_div wc_fragments_refreshed wc_fragments_loaded', function(){
        setTimeout(hideRealQty, 0);
      });
    }
    var rxObserver = new MutationObserver(function(){ hideRealQty(); });
    rxObserver.observe(document.body, {childList:true, subtree:true});

    var qtyField = document.getElementById('rx-qtyfield');
    var totalEl = document.getElementById('rx-total');
    var saveEl = document.getElementById('rx-save');
    var priceEl = document.getElementById('rx-price');
    var shiphintEl = document.getElementById('rx-shiphint');
    var tiers = document.querySelectorAll('#rx-tiers .rx-tier');
    if (!qtyField) return;
    priceEl.textContent = fmt(unitPrice);

    function findRealQtyInput(){
      return document.querySelector('#sp-reta-buybox-el .quantity input.qty');
    }

    function round2(n){
      return Math.round(n * 100) / 100;
    }

    // Bundle-pack model, matching the cart: only the units matching the largest
    // reached bundle size (1/3/5/10) get that bundle's discount, any extra units
    // above it stay full price. Mirrors sp-quantity-discount.php exactly.
    function bundleFor(qty){
      var best = null;
      tiers.forEach(function(btn){
        var q = parseInt(btn.getAttribute('data-q'),10);
        var d = parseInt(btn.getAttribute('data-disc'),10);
        if (qty >= q && (!best || q > best.q)) {
          best = { q: q, disc: d };
        }
      });
      return best;
    }

    function addonsTotal(){
      var sum = 0;
      document.querySelectorAll('#rx-addons .rx-addon-row').forEach(function(row){
        var qtyValEl = row.querySelector('.rx-addon-qtyval');
        var addBtn = row.querySelector('.rx-addon-btn--add');
        var qty = (addBtn && addBtn.classList.contains('is-active') && qtyValEl) ? (parseInt(qtyValEl.textContent, 10) || 0) : 0;
        if (qty > 0) {
          sum += (parseFloat(row.getAttribute('data-price')) || 0) * qty;
        }
      });
      return sum;
    }

    function update(qty){
      qty = Math.max(1, qty);
      qtyField.value = qty;
      var bundle = bundleFor(qty);
      var fullTotal = unitPrice * qty;
      var productTotal = fullTotal;
      if (bundle && bundle.disc > 0) {
        var discountedUnit = round2(unitPrice * (1 - bundle.disc/100));
        var remainder = qty - bundle.q;
        productTotal = round2(discountedUnit * bundle.q + unitPrice * remainder);
      }
      var total = productTotal + addonsTotal();
      totalEl.textContent = fmt(total);
      if (bundle && bundle.disc > 0) {
        saveEl.style.display = '';
        saveEl.textContent = 'Du sparst ' + fmt(fullTotal - productTotal);
      } else {
        saveEl.style.display = 'none';
      }

      var realQtyInput = findRealQtyInput();
      if (realQtyInput) {
        realQtyInput.value = qty;
        realQtyInput.dispatchEvent(new Event('change', {bubbles:true}));
      }

      var remaining = freeShipThreshold - total;
      if (remaining > 0) {
        shiphintEl.innerHTML = truckIcon + '<span>Noch <span class="rx-amt-shine">' + fmt(remaining) + '</span> bis Gratisversand</span>';
        shiphintEl.classList.remove('is-done');
      } else {
        shiphintEl.innerHTML = truckIcon + '<span>Gratisversand</span>';
        shiphintEl.classList.add('is-done');
      }

      tiers.forEach(function(btn){
        btn.classList.toggle('is-active', !!bundle && parseInt(btn.getAttribute('data-q'),10) === bundle.q);
      });
    }

    tiers.forEach(function(btn){
      btn.addEventListener('click', function(){ update(parseInt(btn.getAttribute('data-q'),10)); });
    });

    document.querySelectorAll('.rx-qtybtn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var step = parseInt(btn.getAttribute('data-step'),10);
        update(parseInt(qtyField.value,10) + step);
      });
    });

    update(1);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>

SPHTML,
        'qual' => <<<'SPHTML'

<div class="sp-reta-qualitybox">
  <h4>Unabh&auml;ngig laborgepr&uuml;ft</h4>
    <span class="sp-reta-check">&#10003; Reinheit (HPLC)</span>
</div>

SPHTML,
        'tabs' => <<<'SPHTML'

<style>
.rx-tabsec{max-width:760px;margin:0 auto}
.rx-tabnav{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.rx-tabbtn{border:1px solid #DCDEE0;background:#F2F3F4;color:#4B5157;border-radius:999px;padding:10px 22px;font-family:'Sora',sans-serif;font-weight:700;font-size:14px;cursor:pointer;transition:all .2s;flex:1;min-width:140px;text-align:center}
.rx-tabbtn:hover{border-color:#A8B0B9}
.rx-tabbtn.is-active{background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#fff;border-color:#0D0F12;box-shadow:0 4px 14px rgba(13,15,18,.25)}
.rx-tabcard{border:1px solid #DCDEE0;border-radius:16px;background:#fff;padding:28px 24px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.rx-tabpanel{display:none}
.rx-tabpanel.is-active{display:block}
.rx-tabpanel .sp-reta-desc,.rx-tabpanel .sp-reta-specs{max-width:none;margin:0}
.rx-results{font-family:'Sora',sans-serif}
.rx-results-top{display:flex;align-items:center;gap:28px;flex-wrap:wrap;margin-bottom:22px;padding-bottom:22px;border-bottom:1px solid #DCDEE0}
.rx-results-purity{font-size:44px;font-weight:700;letter-spacing:-0.02em;background:linear-gradient(135deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;animation:rx-res-shine 2.6s linear infinite;white-space:nowrap}
@keyframes rx-res-shine{0%{background-position:200% center}100%{background-position:0% center}}
.rx-results-purity-label{font-size:13.5px;color:#4B5157;font-weight:600;margin-top:2px}
.rx-results-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px}
.rx-results-tile{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:12px;padding:14px 16px}
.rx-results-tile-k{font-size:11.5px;color:#8a9099;font-weight:700;text-transform:uppercase;letter-spacing:.03em;margin-bottom:4px}
.rx-results-tile-v{font-size:14.5px;color:#0D0F12;font-weight:700;display:flex;align-items:center;gap:6px}
.rx-results-check{color:#2f7d4f}
.rx-coa-box{border:1px dashed #DCDEE0;border-radius:12px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;background:#F2F3F4}
.rx-coa-info{font-size:14px;color:#4B5157;font-weight:600}
.rx-coa-info b{display:block;color:#0D0F12;font-size:15px;margin-bottom:2px}
.rx-coa-btn{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #DCDEE0;color:#8a9099;font-family:'Sora',sans-serif;font-weight:700;font-size:13.5px;padding:10px 18px;border-radius:10px;cursor:not-allowed;opacity:.7;white-space:nowrap}
@media(max-width:600px){.rx-results-grid{grid-template-columns:1fr}}
</style>

<div class="rx-tabsec">
  <div class="rx-tabnav" role="tablist">
    <button type="button" class="rx-tabbtn is-active" data-tab="produkt">Produkt</button>
    <button type="button" class="rx-tabbtn" data-tab="lagerung">Lagerung</button>
    <button type="button" class="rx-tabbtn" data-tab="results">Results</button>
    <button type="button" class="rx-tabbtn" data-tab="tools">Tools &amp; Rechner</button>
  </div>

  <div class="rx-tabcard">
    <div class="rx-tabpanel is-active" data-tab="produkt">

<style>
.sp-reta-desc{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif;color:#0D0F12}
.sp-reta-desc h4{font-size:20px;font-weight:700;margin:34px 0 12px;color:#0D0F12}
.sp-reta-desc h4:first-child{margin-top:0}
.sp-reta-desc h3{font-size:24px;font-weight:700;margin:40px 0 14px;color:#0D0F12}
.sp-reta-desc p{font-size:15.5px;line-height:1.7;color:#4B5157;margin:0 0 14px}
.sp-reta-desc strong{color:#0D0F12}
.sp-reta-desc a{color:#0D0F12;text-decoration:underline;text-decoration-color:#A8B0B9;text-underline-offset:2px}
.sp-reta-desc .sp-warn{background:#F2F3F4;border-left:3px solid #A8B0B9;padding:12px 16px;border-radius:8px;font-size:14px;color:#4B5157;margin:18px 0}
.sp-reta-iconrow{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:32px}
.sp-reta-iconrow div{background:#F2F3F4;border-radius:12px;padding:16px 14px;text-align:center;font-size:13.5px;font-weight:600;color:#0D0F12}
@media(max-width:782px){.sp-reta-iconrow{grid-template-columns:repeat(2,1fr)}}
</style>
<div class="sp-reta-desc">
<h3>Wirkung &amp; Forschung</h3>
<p>GLOW ist ein Peptid-Blend aus <strong>GHK-Cu (50 mg), BPC-157 (10 mg) und TB-500 (10 mg)</strong> in einem Vial. Die drei Peptide werden einzeln seit Jahren in der Forschung zu Geweberegeneration und Bindegewebe untersucht: GHK-Cu als Kupfer-Peptidkomplex in Haut- und Kollagenmodellen, BPC-157 und das Thymosin-β4-Fragment TB-500 in Regenerations- und Wundheilungsmodellen. Der Blend ermöglicht es, die Kombination in einem einzigen Ansatz zu untersuchen.</p>
<div class="sp-warn">⚠️ <strong>Nur zu Forschungszwecken.</strong> Nicht für den menschlichen Gebrauch.</div>
</div>



<style>
.sp-reta-specs{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-reta-specs h3{font-size:22px;font-weight:700;color:#0D0F12;margin:0 0 18px;text-align:center}
.sp-reta-specs table{width:100%;table-layout:fixed;border-collapse:collapse;background:#fff;border:1px solid #DCDEE0;border-radius:14px;overflow:hidden}
.sp-reta-specs td{padding:14px 20px;font-size:14.5px;border-bottom:1px solid #DCDEE0;word-break:break-word;overflow-wrap:anywhere}
.sp-reta-specs tr:last-child td{border-bottom:none}
.sp-reta-specs td:first-child{color:#4B5157;font-weight:600;width:45%}
.sp-reta-specs td:last-child{color:#0D0F12;font-weight:600}
</style>
<div class="sp-reta-specs">
<h3>Technische Spezifikationen</h3>
<table>
<tr><td>Produktname</td><td>GLOW (Peptid-Blend)</td></tr>
<tr><td>Zusammensetzung</td><td>GHK-Cu 50 mg · BPC-157 10 mg · TB-500 10 mg</td></tr>
<tr><td>Gesamtmenge</td><td>70 mg pro Vial</td></tr>
<tr><td>Form</td><td>Lyophilisiertes Pulver</td></tr>
<tr><td>Lagerung</td><td>2–8 °C, langfristig −20 °C</td></tr>
<tr><td>Analyse</td><td>HPLC / LC-MS</td></tr>
</table>
</div>
    </div>

        <div class="rx-tabpanel" data-tab="lagerung">
<style>
.sp-lager{font-family:'Sora',sans-serif}
.sp-lager-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:26px}
.sp-lager-card{background:#F2F3F4;border:1px solid #DCDEE0;border-radius:14px;padding:20px 22px}
.sp-lager-card h4{margin:0 0 12px;font-size:16px;font-weight:700;color:#0D0F12}
.sp-lager-stat{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #DCDEE0;font-size:13.5px}
.sp-lager-stat:last-of-type{border-bottom:none}
.sp-lager-stat-k{color:#4B5157;font-weight:600}
.sp-lager-stat-v{color:#0D0F12;font-weight:700}
.sp-lager-tips{list-style:none;margin:12px 0 0;padding:0;display:flex;flex-direction:column;gap:8px}
.sp-lager-tips li{display:flex;align-items:flex-start;gap:8px;font-size:13.5px;color:#4B5157;line-height:1.5}
.sp-lager-tips li svg{width:15px;height:15px;color:#2f7d4f;flex-shrink:0;margin-top:2px}
.sp-lager-general-title{font-size:16px;font-weight:700;color:#0D0F12;margin:0 0 14px}
.sp-lager-tipgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.sp-lager-tipcard{background:#fff;border:1px solid #DCDEE0;border-radius:12px;padding:16px;text-align:center}
.sp-lager-tipicon{font-size:22px;margin-bottom:8px}
.sp-lager-tipcard p{margin:0;font-size:12.5px;line-height:1.5;color:#4B5157}
.sp-lager-tipcard strong{display:block;color:#0D0F12;font-size:13px;margin-bottom:4px}
@media(max-width:600px){.sp-lager-row{grid-template-columns:1fr}.sp-lager-tipgrid{grid-template-columns:1fr}}
</style>
<div class="sp-lager">
  <div class="sp-lager-row">
    <div class="sp-lager-card">
      <h4>Als Pulver</h4>
      <div class="sp-lager-stat"><span class="sp-lager-stat-k">Temperatur</span><span class="sp-lager-stat-v">2&ndash;8&nbsp;&deg;C</span></div>
      <ul class="sp-lager-tips">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Immer gek&uuml;hlt lagern</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Empfindliches Peptid</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Lichtgesch&uuml;tzt aufbewahren</li>
      </ul>
    </div>
    <div class="sp-lager-card">
      <h4>Nach Rekonstitution</h4>
      <div class="sp-lager-stat"><span class="sp-lager-stat-k">Temperatur</span><span class="sp-lager-stat-v">2&ndash;8&nbsp;&deg;C (K&uuml;hlschrank)</span></div>
      <ul class="sp-lager-tips">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Kurze Haltbarkeit nach Aufl&ouml;sen</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Kleine Mengen anmischen</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Sofort k&uuml;hlen</li>
      </ul>
    </div>
  </div>
  <div class="sp-lager-general-title">Allgemeine Lagerungstipps</div>
  <div class="sp-lager-tipgrid">
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#128167;</div><strong>BAC-Wasser verwenden</strong><p>Nutze nur steriles BAC-Wasser (Benzylalkohol + Kochsalz) zur Rekonstitution, nicht gew&ouml;hnliches Wasser.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#129532;</div><strong>Septum desinfizieren</strong><p>Wische das Septum vor jeder Entnahme mit einem Alkoholtupfer ab, um Kontamination zu vermeiden.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#10052;&#65039;</div><strong>K&uuml;hlkette nicht unterbrechen</strong><p>Halte die empfohlene Temperatur konsistent &mdash; Temperaturausrei&szlig;er reduzieren die Haltbarkeit drastisch.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#9728;&#65039;</div><strong>Vor Sonnenlicht sch&uuml;tzen</strong><p>Lagere Peptide in dunklen Beh&auml;ltern oder undurchsichtigen Fl&auml;schchen, um Lichtabbau zu verhindern.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#10060;</div><strong>Nicht einfrieren nach Rekonstitution</strong><p>Wiederholtes Gefrieren und Auftauen zerst&ouml;rt die Peptidstruktur &mdash; nur bei Pulver geeignet.</p></div>
    <div class="sp-lager-tipcard"><div class="sp-lager-tipicon">&#128197;</div><strong>Verfallsdatum notieren</strong><p>Schreibe das Rekonstitutionsdatum auf jede Flasche &mdash; dies ist entscheidend f&uuml;r die Patientenverteidigung.</p></div>
  </div>
</div>
    </div>

        <div class="rx-tabpanel" data-tab="results">
      <div class="rx-results">
        <div class="rx-results-top">
          <div>
            <div class="rx-results-purity">&ge;99%</div>
            <div class="rx-results-purity-label">HPLC-Reinheit</div>
          </div>
        </div>
        <div class="rx-results-grid">
          <div class="rx-results-tile">
            <div class="rx-results-tile-k">Analyse</div>
            <div class="rx-results-tile-v">HPLC / LC-MS</div>
          </div>
          <div class="rx-results-tile">
            <div class="rx-results-tile-k">Status</div>
            <div class="rx-results-tile-v"><span class="rx-results-check">&#10003;</span> Bestanden</div>
          </div>
        </div>
        <div class="rx-coa-box">
          <div class="rx-coa-info"><b>Analysezertifikat (COA)</b>Chargenspezifisches Pr&uuml;fprotokoll als PDF</div>
          <span class="rx-coa-btn" aria-disabled="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>
            Demn&auml;chst verf&uuml;gbar
          </span>
        </div>
      </div>
    </div>

    <div class="rx-tabpanel" data-tab="tools">
<style>
.sp-tools-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1200px;margin:0 auto}
@media(max-width:1024px){.sp-tools-grid{grid-template-columns:repeat(2,1fr);gap:20px;max-width:700px}}
@media(max-width:580px){.sp-tools-grid{grid-template-columns:1fr;gap:20px;max-width:400px}}
.sp-tool-card,.sp-tool-card:link,.sp-tool-card:visited,.sp-tool-card *{text-decoration:none!important}
.sp-tool-card{background:linear-gradient(160deg,#0D0F12 0%,#2A2E33 100%);border:1px solid rgba(255,255,255,.06);border-radius:16px;padding:36px 28px 32px;text-align:left;transition:all .35s ease;position:relative;overflow:hidden;display:block;color:inherit}
.sp-tool-card:hover{border-color:rgba(199,204,209,.4);transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.35)}
.sp-tool-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#C7CCD1,transparent);opacity:0;transition:opacity .35s}
.sp-tool-card:hover::before{opacity:1}
.sp-tool-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;font-size:24px;background:rgba(168,176,185,.14);color:#C7CCD1}
.sp-tool-card h3{color:#fff!important;font-size:20px;font-weight:700;margin:0 0 10px}
.sp-tool-card p{color:rgba(255,255,255,.55)!important;font-size:14.5px;line-height:1.6;margin:0 0 22px;min-height:66px}
.sp-tool-cta{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(120deg,#FFFFFF 0%,#DCDEE0 100%);color:#0D0F12!important;font-size:14px;font-weight:600;padding:10px 18px;border-radius:10px;transition:all .25s;position:relative;overflow:hidden;animation:sp-tool-cta-glow 2.2s ease-in-out infinite;}
@keyframes sp-tool-cta-glow{0%,100%{box-shadow:0 0 0 rgba(242,243,244,0);}50%{box-shadow:0 0 18px rgba(242,243,244,.6);}}
@media(prefers-reduced-motion:reduce){.sp-tool-cta{animation:none;}}
.sp-tool-cta::after{content:'';position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(13,15,18,.18) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-btn-shine 3.2s ease-in-out infinite;pointer-events:none}
@keyframes sp-btn-shine{0%,55%{left:-75%}100%{left:150%}}
.sp-tool-cta:hover{gap:12px}
.sp-tool-cta svg{transition:transform .25s}
.sp-tool-cta:hover svg{transform:translateX(3px)}
</style>
<div class="sp-tools-grid">
    <a href="/peptid-rechner/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="14" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="16" y2="18"/></svg>
      </div>
      <h3>Peptid-Rechner</h3>
      <p>Berechne die exakte Rekonstitution und Dosierung deiner Peptide – schnell, einfach und präzise.</p>
      <span class="sp-tool-cta">Jetzt berechnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
    <a href="/lagerungs-guide/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </div>
      <h3>Lagerungs-Guide</h3>
      <p>Erfahre wie du Peptide optimal lagerst – Temperatur, Haltbarkeit und Rekonstitutions-Tipps.</p>
      <span class="sp-tool-cta">Guide öffnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
  
    <a href="/peptid-lexikon/" class="sp-tool-card">
      <div class="sp-tool-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/><line x1="9" y1="7" x2="15" y2="7"/><line x1="9" y1="11" x2="15" y2="11"/></svg>
      </div>
      <h3>Peptid-Lexikon</h3>
      <p>Steckbriefe und Forschungsgebiete vieler Peptide im Lexikon nachlesen.</p>
      <span class="sp-tool-cta">Lexikon &ouml;ffnen <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
  </div>

    </div>
  </div>
</div>

<script>
(function(){
  var nav = document.currentScript.previousElementSibling;
  if (!nav || !nav.classList.contains('rx-tabsec')) {
    nav = document.querySelector('.rx-tabsec');
  }
  if (!nav) return;
  var btns = nav.querySelectorAll('.rx-tabbtn');
  var panels = nav.querySelectorAll('.rx-tabpanel');
  btns.forEach(function(btn){
    btn.addEventListener('click', function(){
      var tab = btn.getAttribute('data-tab');
      btns.forEach(function(b){ b.classList.toggle('is-active', b === btn); });
      panels.forEach(function(p){ p.classList.toggle('is-active', p.getAttribute('data-tab') === tab); });
    });
  });
})();
</script>

SPHTML,
        'rev' => <<<'SPHTML'
<div class="rv-wrap" id="rv-wrap">
  <style>
    #rv-wrap {
      --rv-accent: #C7CCD1;
      --rv-star: #f5a623;
      --rv-bg: #000000;
      --rv-bg-soft: rgba(255,255,255,.10);
      --rv-border: rgba(255,255,255,.10);
      --rv-text: #FFFFFF;
      --rv-text-soft: rgba(255,255,255,.55);
      --rv-radius: 16px;
      --rv-speed: 65s;

      max-width: 100vw;
      width: 100vw;
      margin-left: calc(-50vw + 50%);
      margin-right: calc(-50vw + 50%);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      color: var(--rv-text);
      overflow: hidden;
      box-sizing: border-box;
    }
    #rv-wrap *, #rv-wrap *::before, #rv-wrap *::after { box-sizing: border-box; }

    #rv-wrap .rv-mask {
      position: relative;
      overflow: hidden;
    }

    #rv-wrap .rv-track {
      display: flex;
      gap: 20px;
      width: max-content;
      animation: rv-scroll var(--rv-speed) linear infinite;
    }
    #rv-wrap .rv-mask:hover .rv-track {
      animation-play-state: paused;
    }

    @keyframes rv-scroll {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }

    #rv-wrap .rv-card {
      flex: 0 0 300px;
      background: var(--rv-bg);
      border: 1px solid var(--rv-border);
      border-radius: var(--rv-radius);
      padding: 22px 22px 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    #rv-wrap .rv-stars {
      display: flex;
      gap: 3px;
    }
    #rv-wrap .rv-stars svg {
      width: 15px;
      height: 15px;
      fill: var(--rv-star);
    }

    #rv-wrap .rv-text {
      font-size: 0.9rem;
      line-height: 1.55;
      color: var(--rv-text);
      margin: 0;
      flex-grow: 1;
    }

    #rv-wrap .rv-author {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 4px;
    }
    #rv-wrap .rv-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--rv-bg-soft);
      color: var(--rv-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.8rem;
      flex-shrink: 0;
    }
    #rv-wrap .rv-name {
      font-size: 0.85rem;
      font-weight: 700;
      margin: 0;
    }
    #rv-wrap .rv-meta {
      font-size: 0.75rem;
      color: var(--rv-text-soft);
      margin: 0;
    }

    @media (prefers-reduced-motion: reduce) {
      #rv-wrap .rv-track { animation: none; }
    }
  </style>

  <div class="rv-mask">
    <div class="rv-track" id="rv-track"></div>
  </div>
</div>

<script>
(function () {
  var reviews = [
    { name: "Fabian K.", initials: "FK", meta: "Verifizierter Kauf", stars: 5, text: "Schon meine dritte Bestellung und wieder alles top gerne wieder" },
    { name: "Jasmin T.", initials: "JT", meta: "Verifizierter Kauf", stars: 5, text: "Wirklich zufrieden mit der Qualität und dem ganzen Ablauf. Werde definitiv wieder bestellen." },
    { name: "Robert N.", initials: "RN", meta: "Verifizierter Kauf", stars: 4, text: "Alles gut gelaufen, bin zufrieden." },
    { name: "Vanessa B.", initials: "VB", meta: "Verifizierter Kauf", stars: 5, text: "Ich bin jetzt schon bei der dritten Bestellung und bin nach wie vor begeistert von der Qualität, den Zertifikaten und dem schnellen Versand. Absolute Kaufempfehlung, komme auf jeden Fall wieder." },
    { name: "Sebastian R.", initials: "SR", meta: "Verifizierter Kauf", stars: 5, text: "top service komme wieder" },
    { name: "Katharina D.", initials: "KD", meta: "Verifizierter Kauf", stars: 5, text: "Hat alles gepasst, bin sehr zufrieden und werde wieder bestellen." },
    { name: "Marco P.", initials: "MP", meta: "Verifizierter Kauf", stars: 4, text: "Gute Erfahrung gemacht auch wenn der Versand einen Tag länger gedauert hat als angegeben aber inhaltlich alles top" },
    { name: "Nadine S.", initials: "NS", meta: "Verifizierter Kauf", stars: 5, text: "Bestelle hier jetzt regelmäßig weil einfach alles passt Qualität Verpackung und der Support" },
    { name: "Tobias W.", initials: "TW", meta: "Verifizierter Kauf", stars: 5, text: "Sehr zufrieden. Zweite Bestellung war genauso gut wie die erste." },
    { name: "Michaela H.", initials: "MH", meta: "Verifizierter Kauf", stars: 5, text: "Rundum zufrieden, komme auf jeden Fall wieder" }
  ];

  function starSvg() {
    return '<svg viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.4 6 .8-4.3 4.1 1 5.9L10 14.9l-5.3 2.8 1-5.9L1.4 7.7l6-.8L10 1.5z"/></svg>';
  }

  function cardHtml(r) {
    var stars = '';
    for (var i = 0; i < 5; i++) {
      stars += i < r.stars ? starSvg() : '<span style="opacity:.25">' + starSvg() + '</span>';
    }
    return '' +
      '<div class="rv-card">' +
        '<div class="rv-stars">' + stars + '</div>' +
        '<p class="rv-text">' + r.text + '</p>' +
        '<div class="rv-author">' +
          '<div class="rv-avatar">' + r.initials + '</div>' +
          '<div>' +
            '<p class="rv-name">' + r.name + '</p>' +
            '<p class="rv-meta">' + r.meta + '</p>' +
          '</div>' +
        '</div>' +
      '</div>';
  }

  var track = document.getElementById('rv-track');
  var html = reviews.map(cardHtml).join('');
  track.innerHTML = html + html;
})();
</script>
SPHTML,
        'lex' => <<<'SPHTML'
<style>
.sp-sci-section{max-width:900px;margin:32px auto 0;font-family:'Sora',sans-serif;}
.sp-sci-section h3{font-size:24px;font-weight:700;color:#0D0F12;margin:0 0 20px;text-align:center}
.sp-sci-card{background:linear-gradient(137deg,#0D0F12 0%,#1C1F23 100%);border-radius:20px;padding:32px 30px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;position:relative;overflow:hidden;}
.sp-sci-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,#C7CCD1,transparent);}
.sp-sci-badge{display:inline-block;background:rgba(255,255,255,.1);color:#C7CCD1;font-size:11.5px;font-weight:700;letter-spacing:2px;padding:5px 14px;border-radius:20px;margin-bottom:12px;text-transform:uppercase;}
.sp-sci-title{font-size:20px;font-weight:700;color:#fff;margin:0 0 8px;}
.sp-sci-desc{font-size:14.5px;color:rgba(255,255,255,.65);line-height:1.6;margin:0;max-width:480px;}
.sp-sci-cta{flex-shrink:0;display:inline-flex;align-items:center;gap:8px;background:#fff;color:#0D0F12;font-weight:700;font-size:14.5px;padding:13px 24px;border-radius:10px;text-decoration:none;white-space:nowrap;}
@media(max-width:600px){.sp-sci-card{padding:26px 22px;}}
</style>
<div class="sp-sci-section">
  <div class="sp-sci-card">
    <div>
      <span class="sp-sci-badge">Ratgeber</span>
      <h4 class="sp-sci-title">Peptide richtig rekonstituieren</h4>
      <p class="sp-sci-desc">Schritt für Schritt: Bac Water, Konzentration berechnen und Lagerung nach dem Anmischen.</p>
    </div>
    <a href="/ratgeber/peptide-rekonstituieren/" class="sp-sci-cta">Zum Ratgeber <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
  </div>
</div>
SPHTML,
        'faq' => <<<'SPHTML'

<style>
.sp-reta-faq{max-width:760px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-reta-faq h3{font-size:24px;font-weight:700;color:#0D0F12;margin:0 0 20px;text-align:center}
.sp-faq-item{border:1px solid #DCDEE0;border-radius:12px;margin-bottom:12px;padding:16px 20px;background:#F2F3F4}
.sp-faq-item summary{cursor:pointer;list-style:none;font-weight:700;font-size:15.5px;color:#0D0F12;display:flex;justify-content:space-between;align-items:center}
.sp-faq-item summary::-webkit-details-marker{display:none}
.sp-faq-arrow{font-size:11px;color:#A8B0B9;transition:transform .25s}
.sp-faq-item[open] .sp-faq-arrow{transform:rotate(180deg)}
.sp-faq-item p{margin:12px 0 0;font-size:14.5px;line-height:1.65;color:#4B5157}
</style>
<div class="sp-reta-faq">
<h3>Häufig gestellte Fragen zu GLOW</h3>

  <details class="sp-faq-item">
    <summary>Was ist GLOW?<span class="sp-faq-arrow">▼</span></summary>
    <p>GLOW ist ein Peptid-Blend aus GHK-Cu (50 mg), BPC-157 (10 mg) und TB-500 (10 mg) – drei Forschungspeptide in einem Vial, insgesamt 70 mg.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Warum ein Blend statt einzelner Peptide?<span class="sp-faq-arrow">▼</span></summary>
    <p>Alle drei Peptide werden in der Regenerations- und Bindegewebsforschung untersucht. Der Blend erlaubt es, die Kombination in einem einzigen Ansatz zu betrachten – ohne drei Vials einzeln anzumischen.</p>
  </details>
  <details class="sp-faq-item">
    <summary>In welchen Forschungsbereichen wird GLOW untersucht?<span class="sp-faq-arrow">▼</span></summary>
    <p>Im Fokus stehen Modelle zu Geweberegeneration, Kollagenbildung, Haut- und Wundheilungsprozessen sowie zum Zusammenspiel der drei Peptide.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Wie wird GLOW geliefert?<span class="sp-faq-arrow">▼</span></summary>
    <p>Als lyophilisiertes (gefriergetrocknetes) Pulver im Vial. Für die Arbeit im Labor wird es mit bakteriostatischem Wasser rekonstituiert – die Konzentration jedes Bestandteils berechnest du mit unserem Peptid-Rechner.</p>
  </details>
  <details class="sp-faq-item">
    <summary>Wie wird GLOW gelagert?<span class="sp-faq-arrow">▼</span></summary>
    <p>Lyophilisiert kühl bei 2–8 °C und lichtgeschützt, für längere Zeit bei −20 °C. Nach dem Rekonstituieren im Kühlschrank aufbewahren und nicht wieder einfrieren.</p>
  </details>
</div>
SPHTML,
        'rel' => <<<'SPHTML'

<style>
.sp-rel-section{max-width:900px;margin:0 auto;font-family:'Sora',sans-serif}
.sp-rel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
.sp-rel-title{font-size:22px;font-weight:700;color:#0D0F12;margin:0}
.sp-rel-more{display:inline-flex;align-items:center;gap:6px;background:#F2F3F4;border:1px solid #DCDEE0;color:#0D0F12;font-family:'Sora',sans-serif;font-weight:700;font-size:13px;padding:8px 14px;border-radius:999px;text-decoration:none!important;white-space:nowrap;transition:all .2s;flex-shrink:0}
.sp-rel-more:hover{background:#0D0F12;color:#fff;border-color:#0D0F12}
.sp-rel-more svg{width:14px;height:14px;transition:transform .2s}
.sp-rel-more:hover svg{transform:translateX(2px)}
.sp-rel-dots{display:flex;justify-content:center;gap:6px;margin-top:14px}
.sp-rel-dot{width:6px;height:6px;border-radius:50%;background:#DCDEE0;transition:all .25s}
.sp-rel-dot.is-active{background:#0D0F12;width:18px;border-radius:4px}
.sp-rel-scroll{display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;padding-bottom:6px;scrollbar-width:thin}
.sp-rel-scroll::-webkit-scrollbar{height:6px}
.sp-rel-scroll::-webkit-scrollbar-thumb{background:#DCDEE0;border-radius:10px}
.sp-rel-card{flex:0 0 auto;width:calc((100% - 28px) / 3);min-width:150px;scroll-snap-align:start;background:#fff;border:1px solid #DCDEE0;border-radius:14px;overflow:hidden;text-decoration:none!important;display:block;color:inherit;transition:all .25s}
.sp-rel-card:hover{border-color:#A8B0B9;transform:translateY(-3px);box-shadow:0 8px 20px rgba(13,15,18,.08)}
.sp-rel-card-img{width:100%;aspect-ratio:1/1;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden}
.sp-rel-card-img img{width:72%;height:72%;object-fit:contain}
.sp-rel-card-body{padding:10px 12px 14px;background:#F2F3F4}
.sp-rel-card h3{font-size:13px;font-weight:700;color:#0D0F12;margin:0 0 4px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sp-rel-card-price{font-size:13.5px;font-weight:700;color:#0D0F12}
@media(max-width:640px){.sp-rel-card{width:calc((100% - 14px) / 2)}}
</style>
<div class="sp-rel-section">
  <div class="sp-rel-head">
    <div class="sp-rel-title">Wird h&auml;ufig zusammen gekauft</div>
    <a href="https://peptrium.com/alle-produkte/" class="sp-rel-more">Mehr <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
  </div>
  <div class="sp-rel-scroll">
    <a href="https://peptrium.com/produkt/ghk-cu/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png" alt="GHK-Cu" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>GHK-Cu</h3>
        <div class="sp-rel-card-price">ab 39,90&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/bpc-157/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/104D478F-BD97-4CA2-A036-29E66BDE6ECF-300x300.png" alt="BPC-157" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>BPC-157</h3>
        <div class="sp-rel-card-price">39,90&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/tb-500/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/13C97F4D-C559-40FD-B414-14B8D73FCB56-300x300.png" alt="TB-500" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>TB-500</h3>
        <div class="sp-rel-card-price">ab 38,17&nbsp;&euro;</div>
      </div>
    </a>
    <a href="https://peptrium.com/produkt/bac-water/" class="sp-rel-card">
      <div class="sp-rel-card-img"><img src="https://peptrium.com/wp-content/uploads/2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png" alt="Bac Water" loading="lazy"></div>
      <div class="sp-rel-card-body">
        <h3>Bac Water</h3>
        <div class="sp-rel-card-price">9,90&nbsp;&euro;</div>
      </div>
    </a>
  </div>
  <div class="sp-rel-dots" id="sp-rel-dots">
    <span class="sp-rel-dot is-active"></span>
    <span class="sp-rel-dot"></span>
    <span class="sp-rel-dot"></span>
    <span class="sp-rel-dot"></span>
  </div>
</div>
<script>
(function(){
  var scrollEl = document.querySelector('.sp-rel-scroll');
  var dots = document.querySelectorAll('.sp-rel-dot');
  if (!scrollEl || !dots.length) return;
  function update(){
    var maxScroll = scrollEl.scrollWidth - scrollEl.clientWidth;
    var progress = maxScroll > 0 ? scrollEl.scrollLeft / maxScroll : 0;
    var idx = Math.round(progress * (dots.length - 1));
    dots.forEach(function(d,i){ d.classList.toggle('is-active', i === idx); });
  }
  scrollEl.addEventListener('scroll', update, {passive:true});
  update();
})();
</script>

SPHTML,
    ];
    return $p;
}

add_action('wp', function () {
    if (!function_exists('is_product') || !is_product()) return;
    $id = (int) get_the_ID();
    if (!isset(sp_nps_pages()[$id])) return;
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5); // H1 kommt aus dem Hero
    add_action('woocommerce_before_single_product_summary', 'sp_nps_render_main', 1);
    add_action('woocommerce_after_single_product_summary', 'sp_nps_render_rest', 1);
});

function sp_nps_render_main() {
    $id = (int) get_the_ID();
    $d = sp_nps_pages()[$id];
    ?>
    <style>
      body.postid-<?php echo $id; ?> .woocommerce-product-gallery{display:none!important}
      body.postid-<?php echo $id; ?> .summary.entry-summary{display:none!important}
      .sp-pen-wrap{max-width:1200px;margin:0 auto;background:#fff;display:flex;flex-direction:row;flex-wrap:wrap;justify-content:space-between;gap:32px 48px;padding:56px 20px 40px;box-sizing:border-box;font-family:'Sora',sans-serif}
      .sp-pen-col-img{width:40%;min-width:280px;box-sizing:border-box}
      .sp-pen-col-img img{width:100%;height:auto;display:block;border-radius:16px}
      .sp-pen-col-text{width:54%;min-width:280px;box-sizing:border-box}
      @media(max-width:900px){.sp-pen-col-img,.sp-pen-col-text{width:100%}}
    </style>
    <div class="sp-pen-wrap">
      <div class="sp-pen-col-img">
        <img fetchpriority="high" src="<?php echo esc_url($d['img']); ?>" alt="<?php echo esc_attr($d['alt']); ?>" width="1086" height="1448" loading="eager">
      </div>
      <div class="sp-pen-col-text">
<?php echo $d['hero'], $d['bb'], $d['qual'], $d['tabs']; ?>
      </div>
    </div>
    <script>
    (function(){
      function moveCartForm(){
        var cartRow = document.getElementById('rx-cartrow');
        var realForm = document.querySelector('.summary.entry-summary form.cart');
        if (cartRow && realForm && !document.getElementById('sp-reta-buybox-el')) {
          var wrap = document.createElement('div');
          wrap.id = 'sp-reta-buybox-el';
          realForm.parentNode.insertBefore(wrap, realForm);
          wrap.appendChild(realForm);
          cartRow.appendChild(wrap);
        }
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', moveCartForm); else moveCartForm();
    })();
    </script>
    <?php
}

function sp_nps_render_rest() {
    $d = sp_nps_pages()[(int) get_the_ID()];
    ?>
    <div style="text-align:center;padding:48px 20px;max-width:900px;margin:0 auto;box-sizing:border-box" id="motsc-kundenstimmen">
      <div class="elementor-heading-title elementor-size-default" style="font-family:'Sora',sans-serif;font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:1.5px;color:#8a9099;margin-bottom:8px">KUNDENSTIMMEN</div>
      <h2 class="elementor-heading-title elementor-size-default" style="font-family:'Sora',sans-serif;font-size:34px;font-weight:600;color:#0D0F12;margin:0 0 28px">Was unsere Kunden sagen</h2>
<?php echo $d['rev']; ?>
    </div>
    <div style="max-width:900px;margin:0 auto;padding:0 20px 48px;box-sizing:border-box">
<?php echo $d['lex']; ?>
    </div>
    <div style="max-width:760px;margin:0 auto;padding:0 20px 48px;box-sizing:border-box">
<?php echo $d['faq']; ?>
    </div>
    <div style="max-width:900px;margin:0 auto;padding:0 20px 48px;box-sizing:border-box">
<?php echo $d['rel']; ?>
    </div>
    <div style="max-width:1200px;margin:0 auto 48px;padding:0 20px;box-sizing:border-box">
      <div style="background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);border-radius:20px;padding:40px 32px;text-align:center;font-family:'Sora',sans-serif">
        <h2 style="color:#fff;font-weight:600;font-size:30px;margin:0 0 16px">Wichtiger Hinweis</h2>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:13px;align-items:center;font-size:15px;color:#fff">
          <li>&#9679;&nbsp; Nicht f&uuml;r die Anwendung am Menschen oder Tier bestimmt</li>
          <li>&#9679;&nbsp; F&uuml;r Laborforschungszwecke</li>
          <li>&#9679;&nbsp; Nicht f&uuml;r diagnostische oder therapeutische Zwecke</li>
        </ul>
      </div>
    </div>
    <?php
}
