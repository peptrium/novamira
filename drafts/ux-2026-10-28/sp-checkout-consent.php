<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_init', function () {
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return;
    }

    woocommerce_register_additional_checkout_field(array(
        'id'            => 'sp/terms-consent',
        'label'         => 'Ich bin mindestens 18 Jahre alt und mit den AGB und Widerrufsbestimmungen einverstanden.',
        'location'      => 'order',
        'type'          => 'checkbox',
        'required'      => true,
        'error_message' => 'Bitte bestätige, dass du mindestens 18 bist und den AGB zustimmst.',
    ));

    // (2026-10-28) 18+ und AGB in EINEM Haekchen (vorher zwei; je ~7 Abbrueche/14 Tage, weil eines vergessen wurde).
    // Alte Bestellungen behalten ihr Feld sp/age-consent in den Metadaten.
});

// Pure CSS tweaks (no DOM mutation, so no risk to React's reconciliation):
// hide the "Zusätzliche Informationen zur Bestellung" step heading, add breathing room
// between the two consent checkboxes, and hide WooCommerce's native terms/privacy notice.
add_action('wp_head', function () {
    if (!function_exists('is_checkout') || !is_checkout()) {
        return;
    }
    ?>
    <style>
    .wp-block-woocommerce-checkout-additional-information-block .wc-block-components-checkout-step__heading-container {
        display: none;
    }
    .wp-block-woocommerce-checkout-additional-information-block .wc-block-components-checkbox {
        margin-bottom: 18px;
    }
    .wp-block-woocommerce-checkout-additional-information-block .wc-block-components-checkbox:last-child {
        margin-bottom: 0;
    }
    .wp-block-woocommerce-checkout-terms-block {
        display: none;
    }
    </style>
    <?php
}, 20);

// The block checkout is a React app and renders the checkbox label above as plain text, so
// "AGB" and "Widerrufsbestimmungen" aren't clickable. Mutating that text node directly (an
// earlier version of this file did that) breaks React's reconciliation on the next re-render
// (e.g. typing a shipping address), which silently stops the whole checkout form from
// updating. This version never touches the React-owned DOM at all: it draws real,
// independent <a> elements on document.body and keeps them pixel-aligned over those
// phrases, purely by reading positions (getClientRects), never writing into the checkout
// tree. Each phrase can wrap onto more than one line on mobile, so it gets one overlay per
// line rect rather than assuming a single rect.
add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout()) {
        return;
    }
    ?>
    <script>
    (function () {
      var TARGET_SENTENCE = 'Ich bin mindestens 18 Jahre alt und mit den AGB und Widerrufsbestimmungen einverstanden.';
      var WORDS = [
        { text: 'AGB', href: '/agb/' },
        { text: 'Widerrufsbestimmungen', href: '/rueckgabe-widerruf/' }
      ];
      var pools = WORDS.map(function () { return []; });

      function findSourceLabel() {
        var labels = document.querySelectorAll('label');
        for (var i = 0; i < labels.length; i++) {
          if (labels[i].textContent.indexOf(TARGET_SENTENCE) !== -1) {
            return labels[i];
          }
        }
        return null;
      }

      function findTextNode(root, needle) {
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        var node;
        while ((node = walker.nextNode())) {
          if (node.textContent.indexOf(needle) !== -1) {
            return node;
          }
        }
        return null;
      }

      function makeOverlay(w, cs) {
        var a = document.createElement('a');
        a.href = w.href;
        a.target = '_blank';
        a.rel = 'noopener';
        a.style.position = 'absolute';
        a.style.display = 'inline-block';
        a.style.overflow = 'hidden';
        a.style.fontFamily = cs.fontFamily;
        a.style.fontSize = cs.fontSize;
        a.style.fontWeight = '700';
        a.style.lineHeight = cs.lineHeight;
        a.style.color = '#0D0F12';
        a.style.textDecoration = 'underline';
        a.style.background = '#fff';
        a.style.zIndex = '99999';
        a.style.pointerEvents = 'auto';
        a.style.transition = 'none';
        document.body.appendChild(a);
        return a;
      }

      function styleAsHotspot(a) {
        // Used when a phrase wraps across more than one line: we can't safely split
        // the text between rects, so instead of covering the original text with a
        // (possibly blank) styled copy, just make this rect transparently clickable
        // and leave the real, already-visible text underneath untouched.
        a.textContent = '';
        a.style.background = 'transparent';
        a.style.color = 'transparent';
      }

      function positionOverlays() {
        var label = findSourceLabel();
        if (!label) {
          pools.forEach(function (pool) { pool.forEach(function (el) { el.style.display = 'none'; }); });
          return;
        }
        var cs = window.getComputedStyle(label);
        WORDS.forEach(function (w, i) {
          var pool = pools[i];
          var textNode = findTextNode(label, w.text);
          var rects = [];
          if (textNode) {
            var idx = textNode.textContent.indexOf(w.text);
            var range = document.createRange();
            range.setStart(textNode, idx);
            range.setEnd(textNode, idx + w.text.length);
            rects = Array.prototype.slice.call(range.getClientRects());
          }
          rects.forEach(function (rect, j) {
            if (!pool[j]) { pool[j] = makeOverlay(w, cs); }
            var el = pool[j];
            if (rects.length === 1) {
              el.textContent = w.text;
              el.style.background = '#fff';
              el.style.color = '#0D0F12';
            } else {
              styleAsHotspot(el);
            }
            el.style.display = 'inline-block';
            el.style.left = (rect.left + window.scrollX) + 'px';
            el.style.top = (rect.top + window.scrollY) + 'px';
            el.style.width = rect.width + 'px';
            el.style.height = rect.height + 'px';
          });
          for (var k = rects.length; k < pool.length; k++) {
            pool[k].style.display = 'none';
          }
        });
      }

      // Real click handling does NOT depend on the overlay elements at all: it re-measures
      // the phrase's rects fresh, at the exact moment of the click, from the live (still
      // unmutated) label DOM, using the same viewport coordinate space as the click event.
      // Capture phase so we run before the browser's native label-click-toggles-checkbox
      // behaviour, and can cancel it when the click was really on a linked phrase.
      document.addEventListener('click', function (e) {
        var label = e.target.closest ? e.target.closest('label') : null;
        if (!label || label.textContent.indexOf(TARGET_SENTENCE) === -1) {
          return;
        }
        for (var i = 0; i < WORDS.length; i++) {
          var w = WORDS[i];
          var textNode = findTextNode(label, w.text);
          if (!textNode) { continue; }
          var idx = textNode.textContent.indexOf(w.text);
          var range = document.createRange();
          range.setStart(textNode, idx);
          range.setEnd(textNode, idx + w.text.length);
          var rects = range.getClientRects();
          for (var j = 0; j < rects.length; j++) {
            var r = rects[j];
            if (e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom) {
              e.preventDefault();
              e.stopPropagation();
              window.open(w.href, '_blank', 'noopener');
              return;
            }
          }
        }
      }, true);

      function hideOverlaysNow() {
        pools.forEach(function (pool) {
          pool.forEach(function (el) { el.style.opacity = '0'; });
        });
      }

      // Switching payment method (Krypto <-> Vorkasse) mounts/unmounts the extra
      // description text under the selected option, which shifts everything below it
      // (incl. this checkbox) up or down. Repositioning every animation frame still made
      // the overlay visibly trail behind that shift for a moment before snapping into
      // place. Instead: the instant a payment method radio changes, hide the overlays
      // outright (no visible copy to trail behind anything), then reveal them again,
      // already correctly positioned, once the layout has settled.
      var revealTimer = null;
      document.addEventListener('change', function (e) {
        var t = e.target;
        if (t && t.classList && t.classList.contains('wc-block-components-radio-control__input')) {
          hideOverlaysNow();
          clearTimeout(revealTimer);
          revealTimer = setTimeout(function () {
            positionOverlays();
            pools.forEach(function (pool) {
              pool.forEach(function (el) { el.style.opacity = '1'; });
            });
          }, 300);
        }
      }, true);

      // Visual tracking (the bold/underlined overlay copy) still needs to follow the real
      // text on every ordinary reflow (window resize, scroll) - a requestAnimationFrame
      // loop tracks that continuously.
      function trackLoop() {
        positionOverlays();
        requestAnimationFrame(trackLoop);
      }
      trackLoop();
      window.addEventListener('resize', positionOverlays);
      window.addEventListener('scroll', positionOverlays, true);
    })();
    </script>
    <?php
}, 20);