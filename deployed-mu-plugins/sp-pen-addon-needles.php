<?php
/**
 * Bietet "Pen Nadeln 32G x 4mm" als Zubehoer-Zeile in der Buybox der drei
 * Peptrium-Pen-Produktseiten an - gleiches Muster wie "Bac Water" auf den
 * Peptid-Produktseiten (siehe dort: .rx-addons/.rx-addon-row, verarbeitet
 * von sp-product-addons.php ueber das Feld sp_addon_pennadeln).
 *
 * Die Pen-Produktseiten haben aktuell gar keinen #rx-addons-Block in ihrer
 * (fest in Elementor gebauten) Buybox - dieser wird hier komplett per JS
 * ergaenzt, inklusive der noetigen Interaktions-Logik (Toggle, Mengenwahl,
 * verstecktes Formularfeld), da das Original-Skript der jeweiligen Seite
 * diese Logik nur fuer bereits vorhandene Zubehoer-Zeilen mitbringt.
 *
 * Die Gesamtsumme (#rx-total/#rx-save) wird dabei nicht angetastet, um nicht
 * mit der Preis-/Mengenlogik der jeweiligen Seite zu kollidieren - stattdessen
 * wird sie bei jeder relevanten Aenderung (Zubehoer-Toggle, Mengenfeld) aus
 * Grundpreis x Menge + Zubehoer-Summe komplett neu berechnet und ueberschrieben.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    if (!function_exists('is_product') || !is_product()) {
        return;
    }
    if (!in_array((int) get_the_ID(), array(393, 395, 396), true)) {
        return;
    }
    ?>
    <style>
      /* Diese Klassen existieren sonst nur im <style>-Block der Produktseiten,
         die bereits eine eigene, nativ in Elementor gebaute Zubehoer-Zeile
         haben (z.B. Bac Water bei den Peptid-Seiten) - auf den Pen-Seiten
         fehlen sie komplett, da es dort nie eine gab. 1:1 von dort uebernommen,
         damit die hier per JS injizierte Zeile genauso aussieht.
      */
      .rx-addons{margin-bottom:16px}
      .rx-addon-row{display:flex;flex-direction:column;gap:8px;padding:10px 0;border-bottom:1px solid #F2F3F4}
      .rx-addon-row:last-child{border-bottom:none}
      .rx-addon-top{display:flex;align-items:center;gap:10px}
      .rx-addon-qty{display:none;align-items:center;gap:10px;margin-left:46px}
      .rx-addon-row.is-active .rx-addon-qty{display:flex}
      .rx-addon-qtybtn{width:26px;height:26px;border-radius:8px;border:1px solid #DCDEE0;background:#fff;color:#0D0F12;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;line-height:1;padding:0}
      .rx-addon-qtybtn:hover{border-color:#A8B0B9}
      .rx-addon-qtyval{font-size:13px;font-weight:700;color:#0D0F12;min-width:16px;text-align:center}
      .rx-addon-img{width:36px;height:36px;border-radius:10px!important;object-fit:cover;background:#F2F3F4;flex-shrink:0}
      .rx-addon-name{font-size:13.5px;font-weight:600;color:#0D0F12;flex:1;min-width:0}
      .rx-addon-sub{display:block;font-size:11.5px;font-weight:500;color:#5B6169;margin-top:1px}
      .rx-addon-btn{font-family:'Sora',sans-serif;font-size:12px;font-weight:600;border-radius:999px;padding:7px 12px;cursor:pointer;border:1px solid #DCDEE0;background:#fff;color:#4B5157;transition:border-color .15s,background .15s,color .15s;white-space:nowrap;flex-shrink:0}
      .rx-addon-btn.is-active{background:#0D0F12;color:#fff;border-color:#0D0F12}
      .rx-addon-btn:not(.is-active):hover{border-color:#A8B0B9}
      .rx-addon-price{opacity:.85}
      .rx-addon-btn-remove{display:none}
      .rx-addon-btn.is-active .rx-addon-btn-add{display:none}
      .rx-addon-btn.is-active .rx-addon-btn-remove{display:inline}
      .rx-tiers-head{font-size:13px;font-weight:700;color:#4B5157;text-transform:uppercase;letter-spacing:.03em;margin-bottom:10px}
      @media(max-width:480px){.rx-addon-name{font-size:12.5px}.rx-addon-btn{font-size:11px;padding:6px 9px}}
    </style>
    <script>
    (function () {
      var ADDON_FIELD = 'sp_addon_pennadeln';
      var ADDON_PRICE = 9.90;
      var ADDON_IMG = 'https://peptrium.com/wp-content/uploads/2026/10/pen-nadeln-32g-4mm-150x150.webp';

      function fmt(n) {
        return n.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
      }

      function parsePrice(text) {
        if (!text) return 0;
        var cleaned = text.replace(/[^0-9,.-]/g, '').replace(/\./g, '').replace(',', '.');
        return parseFloat(cleaned) || 0;
      }

      function buildAddonsBlock() {
        var wrap = document.createElement('div');
        wrap.className = 'rx-addons';
        wrap.id = 'rx-addons';
        wrap.innerHTML =
          '<div class="rx-tiers-head">Zubehör</div>' +
          '<div class="rx-addon-row" data-field="' + ADDON_FIELD + '" data-price="' + ADDON_PRICE + '">' +
            '<div class="rx-addon-top">' +
              '<img class="rx-addon-img" src="' + ADDON_IMG + '" alt="">' +
              '<span class="rx-addon-name">Pen Nadeln 32G x 4mm<span class="rx-addon-sub">15 Stück pro Packung</span></span>' +
              '<button type="button" class="rx-addon-btn rx-addon-btn--add" data-choice="1">' +
                '<span class="rx-addon-btn-add">Hinzufügen <span class="rx-addon-price">+' + fmt(ADDON_PRICE) + '</span></span>' +
                '<span class="rx-addon-btn-remove">Entfernen</span>' +
              '</button>' +
            '</div>' +
            '<div class="rx-addon-qty">' +
              '<button type="button" class="rx-addon-qtybtn" data-step="-1" aria-label="weniger">−</button>' +
              '<span class="rx-addon-qtyval">1</span>' +
              '<button type="button" class="rx-addon-qtybtn" data-step="1" aria-label="mehr">+</button>' +
            '</div>' +
          '</div>';
        return wrap;
      }

      function init() {
        if (document.getElementById('rx-addons')) {
          return;
        }
        // Vor "rx-shiphint" einfuegen, nicht vor "rx-buysum" - so landet der
        // Zubehoer-Block an derselben Stelle wie bei den nativ in Elementor
        // gebauten Zubehoer-Zeilen (z.B. Bac Water auf den Peptid-Seiten:
        // Mengen-Tiles -> Zubehoer -> Versandhinweis -> Abo-Umschalter ->
        // Gesamtsumme), statt mit dem Abo-Umschalter (der sich ebenfalls vor
        // ".rx-buysum" einhaengt) um dieselbe Position zu konkurrieren.
        var anchor = document.querySelector('.rx-shiphint') || document.querySelector('.rx-buysum') || document.querySelector('.rx-buycta');
        if (!anchor || !anchor.parentNode) {
          return;
        }

        var block = buildAddonsBlock();
        anchor.parentNode.insertBefore(block, anchor);

        var row = block.querySelector('.rx-addon-row');
        var addBtn = row.querySelector('.rx-addon-btn--add');
        var qtyValEl = row.querySelector('.rx-addon-qtyval');
        var qty = 0;

        function ensureHiddenInput() {
          var form = document.querySelector('form.cart');
          if (!form) return null;
          var inp = form.querySelector('input[name="' + ADDON_FIELD + '"]');
          if (!inp) {
            inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = ADDON_FIELD;
            form.appendChild(inp);
          }
          return inp;
        }

        function recomputeTotal() {
          var totalEl = document.getElementById('rx-total');
          var qtyField = document.getElementById('rx-qtyfield');
          var priceEl = document.getElementById('rx-price');
          if (!totalEl || !qtyField || !priceEl) return;
          var mainQty = parseInt(qtyField.value, 10) || 1;
          var unitPrice = parsePrice(priceEl.textContent);
          var addonTotal = qty > 0 ? ADDON_PRICE * qty : 0;
          totalEl.textContent = fmt(unitPrice * mainQty + addonTotal);
        }

        function render() {
          var active = qty > 0;
          row.classList.toggle('is-active', active);
          addBtn.classList.toggle('is-active', active);
          if (qtyValEl) qtyValEl.textContent = qty || 1;
          var inp = ensureHiddenInput();
          if (inp) inp.value = String(qty);
          recomputeTotal();
        }

        addBtn.addEventListener('click', function () {
          qty = addBtn.classList.contains('is-active') ? 0 : 1;
          render();
        });
        row.querySelectorAll('.rx-addon-qtybtn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var step = parseInt(btn.getAttribute('data-step'), 10);
            qty = Math.max(0, Math.min(10, qty + step));
            render();
          });
        });

        // Haupt-Mengenfeld dieser Seite aendert sich ueber ihr eigenes Skript -
        // bei jeder Aenderung (Klick auf +/- dort) unsere Summe neu berechnen.
        document.querySelectorAll('.rx-qtybtn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            setTimeout(recomputeTotal, 0);
          });
        });

        ensureHiddenInput();
        recomputeTotal();
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
      // Manche Produktseiten verschieben das eigentliche form.cart erst per
      // JS (siehe sp-product-crosssell.php) - etwas spaeter nochmal pruefen.
      setTimeout(init, 400);
      setTimeout(init, 1000);
    })();
    </script>
    <?php
}, 22);
