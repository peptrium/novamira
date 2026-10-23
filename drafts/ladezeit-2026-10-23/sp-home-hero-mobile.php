<?php
/**
 * Ersetzt Hero-Banner + Statistik-Leiste auf der Startseite NUR in der
 * mobilen Ansicht (<=767px) durch eine neue Version mit eigenem Produktfoto
 * und einer schwebenden "Glas"-Statistikkarte, die den Uebergang zum
 * darunterliegenden (weissen) Seiteninhalt abfedert.
 *
 * Auf Desktop bleiben die bestehenden Elementor-HTML-Widgets (heroHtml1,
 * statsHtml1 in Post 211) unveraendert sichtbar, bis dafuer ein eigenes
 * Desktop-Bild vorliegt.
 *
 * Wie bei der Abo-Promo-Sektion: die Originale sind Elementor-HTML-Widgets,
 * daher keine Aenderung an den gespeicherten Elementor-Daten, sondern von
 * aussen per CSS ausgeblendet (nur mobil) und per JS durch die neue Sektion
 * ersetzt.
 */
if (!defined('ABSPATH')) {
    exit;
}

/* Ladezeit/CLS (2026-10-22): Hero serverseitig ausliefern statt erst per JS (~4 s auf dem Handy)
 * einzufuegen – vorher stand kurz die Transparenz-Sektion oben und sprang dann nach unten
 * (CLS ~1,8). Das JS unten bleibt als Fallback (es greift nur, wenn .sp-hero2 fehlt). */
function sp_hero2_img() { return 'https://peptrium.com/wp-content/uploads/2026/10/home-hero-mobile-vials-v2.webp'; }
function sp_hero2_html() {
    $img = sp_hero2_img();
    return '<div class="sp-hero2" data-ssr="1"><div class="sp-hero2-img-wrap"><picture><source media="(min-width:768px)" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="><img src="' . esc_url($img) . '" alt="Peptrium Fläschchen und Pen" fetchpriority="high" decoding="async" width="390" height="600"></picture><div class="sp-hero2-text"><h1 class="sp-hero2-title">Der neue Standard<br>für Peptidforschung</h1><p class="sp-hero2-sub">Reinheit. Qualität. Wirkung.</p></div><div class="sp-hero2-cta-wrap"><a href="/alle-produkte/" class="sp-hero2-cta">Jetzt Peptide bestellen<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a></div></div><div class="sp-hero2-stats"><div class="sp-hero2-stat"><span class="sp-hero2-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></span><span class="sp-hero2-stat-value">2500+</span><span class="sp-hero2-stat-label">Bestellungen</span></div><div class="sp-hero2-divider"></div><div class="sp-hero2-stat"><span class="sp-hero2-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l7 4v6c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-4z"></path><path d="M9 12l2 2 4-4"></path></svg></span><span class="sp-hero2-stat-value">99,4%</span><span class="sp-hero2-stat-label">Reinheitsgrad</span></div><div class="sp-hero2-divider"></div><div class="sp-hero2-stat"><span class="sp-hero2-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.63 22 9.24 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.24 8.91 8.63 12 2"></polygon></svg></span><span class="sp-hero2-stat-value">4,8</span><span class="sp-hero2-stat-label">Bewertung</span></div></div><div class="sp-hero2-spacer"></div></div>';
}
add_action('wp_head', function () {
    if (!is_front_page()) return;
    echo '<link rel="preload" as="image" href="' . esc_url(sp_hero2_img()) . '" media="(max-width:767px)" fetchpriority="high">' . "\n";
    ?>
    <style>
      @media(max-width:767px){
        #sp-hero-banner, #sp-stats-section{display:none!important}
      }
      @media(min-width:768px){
        .sp-hero2{display:none!important}
      }
      .sp-hero2{position:relative;width:100vw;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw;font-family:'Sora',sans-serif}
      .sp-hero2-img-wrap{position:relative;height:600px;overflow:hidden}
      .sp-hero2-img-wrap img{position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;object-position:center top;display:block}
      .sp-hero2-text{position:absolute;top:123px;left:24px;right:24px}
      .sp-hero2-title{margin:0 0 12px;font-size:29px;font-weight:700;line-height:1.2;letter-spacing:-0.01em;background:linear-gradient(135deg,#C7CCD1 0%,#FFFFFF 30%,#F2F3F4 50%,#FFFFFF 70%,#C7CCD1 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
      .sp-hero2-sub{margin:0;font-size:14.5px;line-height:1.5;color:rgba(255,255,255,.75)}
      .sp-hero2-cta-wrap{position:absolute;left:0;right:0;bottom:85px;display:flex;justify-content:center;z-index:1}
      .sp-hero2-cta, .sp-hero2-cta:hover, .sp-hero2-cta:focus{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:999px;background:linear-gradient(120deg,#F2F3F4 0%,#A8B0B9 100%);color:#0D0F12;font-size:14px;font-weight:600;text-decoration:none!important;position:relative;overflow:hidden}
      .sp-hero2-cta::after{content:"";position:absolute;top:0;left:-75%;width:50%;height:100%;background:linear-gradient(115deg,transparent 30%,rgba(13,15,18,.15) 50%,transparent 70%);transform:skewX(-20deg);animation:sp-hero2-shine 3.2s ease-in-out infinite;pointer-events:none}
      @keyframes sp-hero2-shine{0%,55%{left:-75%}100%{left:150%}}
      .sp-hero2-stats{position:relative;margin:-50px 20px 0;z-index:2;background:rgba(13,15,18,.55);-webkit-backdrop-filter:blur(18px);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,.14);border-radius:18px;box-shadow:0 20px 50px rgba(0,0,0,.35);padding:20px 10px;display:flex;align-items:center;justify-content:space-around}
      .sp-hero2-stat{display:flex;flex-direction:column;align-items:center;text-align:center}
      .sp-hero2-stat-icon{color:#C7CCD1;margin-bottom:6px;display:flex}
      .sp-hero2-stat-value{font-size:18px;font-weight:700;letter-spacing:-0.02em;background:linear-gradient(135deg,#C7CCD1 0%,#FFFFFF 30%,#F2F3F4 50%,#FFFFFF 70%,#C7CCD1 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
      .sp-hero2-stat-label{margin-top:5px;font-size:9px;color:rgba(255,255,255,.55);font-weight:600;text-transform:uppercase;letter-spacing:.05em}
      .sp-hero2-divider{width:1px;height:30px;background:linear-gradient(to bottom,rgba(199,204,209,0) 0%,rgba(199,204,209,.35) 50%,rgba(199,204,209,0) 100%)}
      .sp-hero2-spacer{height:28px}
      @media(prefers-reduced-motion:reduce){.sp-hero2-cta::after{display:none}}
    </style>
    <?php
}, 999); // nach Astra/Elementor, sonst überschreibt deren h1-Farbe den Chrom-Verlauf
add_action('template_redirect', function () {
    if (!is_front_page() || is_admin() || wp_doing_ajax() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || isset($_GET['elementor-preview'])) return;
    $GLOBALS['sp_hero2_html'] = sp_hero2_html();
    ob_start(function ($h) {
        $p = strpos($h, '<div id="sp-hero-banner">');
        if ($p === false || strpos($h, 'data-ssr="1"') !== false) return $h;
        return substr($h, 0, $p) . $GLOBALS['sp_hero2_html'] . substr($h, $p);
    });
}, 8);

add_action('wp_footer', function () {
    if (!is_front_page()) {
        return;
    }
    $img = 'https://peptrium.com/wp-content/uploads/2026/10/home-hero-mobile-vials-v2.webp';
    ?>
    <script>
    (function(){
      if (window.matchMedia('(min-width:768px)').matches) return;
      var IMG = <?php echo wp_json_encode($img); ?>;
      function buildHtml(){
        return '' +
        '<div class="sp-hero2">' +
          '<div class="sp-hero2-img-wrap">' +
            '<img src="' + IMG + '" alt="Peptrium Fläschchen und Pen">' +
            '<div class="sp-hero2-text">' +
              '<h1 class="sp-hero2-title">Der neue Standard<br>für Peptidforschung</h1>' +
              '<p class="sp-hero2-sub">Reinheit. Qualität. Wirkung.</p>' +
            '</div>' +
            '<div class="sp-hero2-cta-wrap">' +
              '<a href="/alle-produkte/" class="sp-hero2-cta">Jetzt Peptide bestellen' +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' +
              '</a>' +
            '</div>' +
          '</div>' +
          '<div class="sp-hero2-stats">' +
            '<div class="sp-hero2-stat"><span class="sp-hero2-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></span><span class="sp-hero2-stat-value">2500+</span><span class="sp-hero2-stat-label">Bestellungen</span></div>' +
            '<div class="sp-hero2-divider"></div>' +
            '<div class="sp-hero2-stat"><span class="sp-hero2-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l7 4v6c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-4z"></path><path d="M9 12l2 2 4-4"></path></svg></span><span class="sp-hero2-stat-value">99,4%</span><span class="sp-hero2-stat-label">Reinheitsgrad</span></div>' +
            '<div class="sp-hero2-divider"></div>' +
            '<div class="sp-hero2-stat"><span class="sp-hero2-stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.63 22 9.24 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.24 8.91 8.63 12 2"></polygon></svg></span><span class="sp-hero2-stat-value">4,8</span><span class="sp-hero2-stat-label">Bewertung</span></div>' +
          '</div>' +
          '<div class="sp-hero2-spacer"></div>' +
        '</div>';
      }
      function adjustTextOffset(){
        var heroEl = document.querySelector('.sp-hero2');
        var textEl = heroEl ? heroEl.querySelector('.sp-hero2-text') : null;
        if (!heroEl || !textEl) return;
        var heroTop = heroEl.getBoundingClientRect().top + window.scrollY;
        var chromeBottom = 0;
        ['sp-top-banner', 'sp-header-bar'].forEach(function(id){
          var el = document.getElementById(id);
          if (!el) return;
          var bottom = el.getBoundingClientRect().bottom + window.scrollY;
          if (bottom > chromeBottom) chromeBottom = bottom;
        });
        var clearance = Math.max(36, (chromeBottom - heroTop) + 16);
        textEl.style.top = clearance + 'px';
      }

      function tick(){
        var anchor = document.getElementById('sp-hero-banner');
        if (!anchor || document.querySelector('.sp-hero2')) return;
        anchor.insertAdjacentHTML('beforebegin', buildHtml());
        adjustTextOffset();
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tick);
      } else {
        tick();
      }
      setTimeout(function(){ tick(); adjustTextOffset(); }, 400);
    })();
    </script>
    <?php
}, 14);
