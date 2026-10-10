<?php
/**
 * Plugin Name: SP Kasse-Vorschau
 * Description: Entwurf der neuen Kasse (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * Nur Optik: reduzierter Kopf (Suche/Menue/Konto weg, "Sichere Kasse"), Schritt-Anzeige
 * Warenkorb -> Kasse -> Fertig (ausserhalb des React-Formulars eingefuegt), nummerierte Abschnitte
 * per CSS-Zaehler, Vorname/Nachname und PLZ/Ort nebeneinander (PLZ vor Ort per CSS order),
 * Zahlungsarten als Karten, Vertrauenszeile unter dem Bestell-Button. Keine Aenderung an Feldern,
 * Validierung oder Bestelllogik. Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_cop_active() {
    return function_exists('sp_hpv_token_ok') && sp_hpv_token_ok() && function_exists('is_checkout') && is_checkout()
        && !is_wc_endpoint_url('order-received') && !is_wc_endpoint_url('order-pay');
}

add_action('wp_footer', function () {
    if (!sp_cop_active()) {
        return;
    }
    ?>
<style id="sp-cop-css">
/* Kopf: nur Logo + Sichere Kasse */
body.woocommerce-checkout #sp-header-bar .sp-search-wrap,body.woocommerce-checkout #sp-header-bar .sp-acct-wrap,body.woocommerce-checkout #sp-menu-btn{display:none!important}
body.woocommerce-checkout #sp-header-bar .sp-hb-right:before{content:'Sichere Kasse';display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 12px 0 32px;border-radius:999px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06) no-repeat 12px center/14px url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237DDBA0' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='4' y='11' width='16' height='10' rx='2'/%3E%3Cpath d='M8 11V7a4 4 0 018 0v4'/%3E%3C/svg%3E");color:#E6E9EC;font:700 12px/1 Sora,sans-serif;letter-spacing:.02em}
/* Schritt-Anzeige */
#sp-cop-steps{display:flex;align-items:center;justify-content:center;gap:0;margin:18px auto 6px;max-width:420px;padding:0 16px;font-family:Sora,sans-serif}
#sp-cop-steps .s{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:700;color:#A8B0B9;white-space:nowrap}
#sp-cop-steps .s i{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font:800 11px/1 Sora,sans-serif;font-style:normal;background:#EEF0F2;color:#80868E}
#sp-cop-steps .s.ok{color:#2E9B57}#sp-cop-steps .s.ok i{background:#2E9B57;color:#fff}
#sp-cop-steps .s.on{color:#0D0F12}#sp-cop-steps .s.on i{background:#0D0F12;color:#fff;box-shadow:0 0 0 4px rgba(13,15,18,.1)}
#sp-cop-steps .ln{flex:1 1 auto;height:2px;min-width:18px;max-width:56px;margin:0 8px;background:#E3E6E9;border-radius:2px}
#sp-cop-steps .ln.ok{background:#2E9B57}
#sp-cop-steps a{text-decoration:none!important;color:inherit}
/* Abschnitte nummeriert */
.wc-block-checkout__form{counter-reset:spstep}
.wc-block-checkout__form .wc-block-components-checkout-step__title{display:flex!important;align-items:center;gap:10px;font-size:17px!important;font-weight:800!important;letter-spacing:-.01em}
.wc-block-checkout__form .wc-block-components-checkout-step__title:before{counter-increment:spstep;content:counter(spstep);flex:0 0 26px;width:26px;height:26px;border-radius:50%;background:#0D0F12;color:#fff;font:800 12px/26px Sora,sans-serif;text-align:center}
/* Felder nebeneinander (auch auf dem Handy) */
.wc-block-components-address-form{display:flex!important;flex-wrap:wrap!important;justify-content:space-between!important;column-gap:0!important}
.wc-block-components-address-form>*{order:0}
.wc-block-components-address-form .wc-block-components-address-form__first_name,.wc-block-components-address-form .wc-block-components-address-form__last_name{flex:0 0 calc(50% - 5px)!important;width:calc(50% - 5px)!important;box-sizing:border-box}
.wc-block-components-address-form .wc-block-components-address-form__postcode{order:1!important;flex:0 0 calc(38% - 5px)!important;width:calc(38% - 5px)!important;box-sizing:border-box}
.wc-block-components-address-form .wc-block-components-address-form__city{order:1!important;flex:0 0 calc(62% - 5px)!important;width:calc(62% - 5px)!important;box-sizing:border-box}
.wc-block-components-address-form .wc-block-components-address-form__state,.wc-block-components-address-form .wc-block-components-address-form__phone{order:2!important;flex:0 0 100%!important;width:100%!important}
.wc-block-components-address-form>*:not([class*="__first_name"]):not([class*="__last_name"]):not([class*="__postcode"]):not([class*="__city"]){flex-basis:100%}
.wc-block-components-address-form .wc-block-components-address-form__address_2-toggle{order:0}
/* Bundesland ist bei Deutschland optional und wird nicht gebraucht */
.wc-block-components-address-form:has(select[id$="-country"] option[value="DE"]:checked) .wc-block-components-address-form__state{display:none!important}
/* Zahlungsarten als Karten */
#payment-method .wc-block-components-radio-control-accordion-option{border:1.5px solid #E3E6E9!important;border-radius:14px!important;margin:0 0 10px!important;box-shadow:none!important;overflow:hidden;background:#fff}
#payment-method .wc-block-components-radio-control-accordion-option--checked-option-highlighted{border-color:#0D0F12!important;box-shadow:0 6px 16px rgba(13,15,18,.08)!important}
#payment-method .wc-block-components-radio-control__option{padding-top:14px!important;padding-bottom:14px!important;border:none!important;box-shadow:none!important;background:transparent!important;margin:0!important;border-radius:0!important}
#payment-method .wc-block-components-radio-control__label{font-weight:700!important;font-size:14.5px!important}
#payment-method .wc-block-components-radio-control-accordion-content{font-size:13px;line-height:1.55;color:#4B5157;padding-top:0!important}
#payment-method .wc-block-components-radio-control:after,#payment-method .wc-block-components-radio-control-accordion-option:after{display:none!important}
/* Bestell-Button + Vertrauenszeile */
.wc-block-checkout__actions_row{display:flex!important;flex-direction:column!important;align-items:stretch!important}
.wc-block-components-checkout-place-order-button{min-height:56px!important;border-radius:14px!important;font-size:16px!important;font-weight:800!important;box-shadow:0 8px 20px rgba(13,15,18,.2)!important}
/* Vertrauens-Leiste unter dem Bestell-Button (eigenes Element nach dem Checkout-Block) */
#sp-cop-trust{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:14px 0 8px;padding:14px 8px;border-radius:16px;background:#F6F7F8;border:1px solid #EEF0F2;font-family:Sora,sans-serif}
#sp-cop-trust div{display:flex;flex-direction:column;align-items:center;text-align:center;gap:3px;min-width:0}
#sp-cop-trust i{width:34px;height:34px;border-radius:50%;background:#fff;border:1px solid #E3E6E9;display:flex;align-items:center;justify-content:center;margin-bottom:4px;color:#0D0F12}
#sp-cop-trust i svg{width:16px;height:16px}
#sp-cop-trust b{font-size:11.5px;font-weight:800;color:#0D0F12;line-height:1.25}
#sp-cop-trust span{font-size:10.5px;color:#80868E;line-height:1.3}
/* Abstand Button -> Vertrauens-Leiste */
.wp-block-woocommerce-checkout,.wc-block-components-sidebar-layout,.wc-block-checkout__main,.wc-block-checkout__form,.wc-block-checkout__actions{margin-bottom:0!important;padding-bottom:0!important}
/* Anmelden-Link als kleine Pille */
#contact-fields .wc-block-checkout__login-prompt,#contact-fields .wc-block-components-checkout-step__heading-content a{font-size:12px!important;font-weight:700;text-decoration:none!important;border:1px solid #DCDEE0;border-radius:999px;padding:5px 11px;color:#0D0F12!important;white-space:nowrap}
#contact-fields .wc-block-components-checkout-step__heading{align-items:center!important}
/* "Kontaktinformationen" -> "Kontakt" (sonst stoesst der Titel an "Anmelden") */
#contact-fields .wc-block-components-checkout-step__title{font-size:0!important;gap:0!important}
#contact-fields .wc-block-components-checkout-step__title:after{content:'Kontakt';font-size:17px;font-weight:800;margin-left:10px}
</style>
<script id="sp-cop-js">
(function(){
 var co=document.querySelector('.wp-block-woocommerce-checkout');if(!co||document.getElementById('sp-cop-steps'))return;
 var st=document.createElement('nav');st.id='sp-cop-steps';st.setAttribute('aria-label','Bestellschritte');
 st.innerHTML='<a class="s ok" href="<?php echo esc_url(wc_get_cart_url()); ?>" onclick="var b=document.getElementById(\'sp-cart-fab\');if(b){b.click();return false;}"><i>✓</i>Warenkorb</a><span class="ln ok"></span><span class="s on"><i>2</i>Kasse</span><span class="ln"></span><span class="s"><i>3</i>Fertig</span>';
 co.parentNode.insertBefore(st,co);
 var tr=document.createElement('div');tr.id='sp-cop-trust';
 var ic={lock:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>',box:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>',truck:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4h14v12H1zM15 9h4l3 3v4h-7"/><circle cx="5.5" cy="18.5" r="2"/><circle cx="18.5" cy="18.5" r="2"/></svg>'};
 tr.innerHTML='<div><i>'+ic.lock+'</i><b>Sicher bezahlen</b><span>SSL-verschlüsselt</span></div><div><i>'+ic.box+'</i><b>Diskret</b><span>Neutral verpackt</span></div><div><i>'+ic.truck+'</i><b>In 2 Werktagen</b><span>Versand mit DHL</span></div>';
 function place(){if(!tr.isConnected||tr.previousElementSibling!==co){co.parentNode.insertBefore(tr,co.nextSibling);}}
 place();new MutationObserver(place).observe(co.parentNode,{childList:true});
})();
</script>
    <?php
}, 100);
