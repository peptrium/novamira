<?php
/**
 * Site-wide fixed top shipping banner (above the sticky header), all pages.
 * Single-line infinite marquee - same technique as the homepage reviews
 * ticker (#rv-track: doubled content + translateX(-50%) loop) so the four
 * selling points scroll continuously instead of wrapping to a second line.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_top_banner_items() {
    ob_start();
    ?>
    <span class="sp-tb-item">
      <svg class="sp-tb-flag" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 3" width="18" height="11">
        <rect width="5" height="1" y="0" fill="#000000"/>
        <rect width="5" height="1" y="1" fill="#DD0000"/>
        <rect width="5" height="1" y="2" fill="#FFCC00"/>
      </svg>
      Nur 2 Tage Versand
    </span>
    <span class="sp-tb-sep">•</span>
    <span class="sp-tb-item">Kostenloser Versand ab 150€</span>
    <span class="sp-tb-sep">•</span>
    <span class="sp-tb-item">Pure Qualität</span>
    <span class="sp-tb-sep">•</span>
    <span class="sp-tb-item">Sichere Zahlung</span>
    <span class="sp-tb-sep">•</span>
    <?php
    return ob_get_clean();
}

add_action('wp_body_open', function () {
    $items = sp_top_banner_items();
    ?>
    <div id="sp-top-banner">
      <div class="sp-tb-mask">
        <div class="sp-tb-track">
          <?php echo $items; ?>
          <?php echo $items; ?>
        </div>
      </div>
    </div>
    <?php
}, 5);

add_action('wp_head', function () {
    ?>
    <style id="sp-top-banner-style">
      #sp-top-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100000;
        background: linear-gradient(180deg, #0D0F12 0%, #1C1F23 100%);
        color: #ffffff;
        font-family: 'Sora', sans-serif;
        font-size: 14px;
        line-height: 1.4;
        padding: 9px 0;
        overflow: hidden;
      }
      #sp-top-banner .sp-tb-mask {
        overflow: hidden;
        -webkit-mask-image: linear-gradient(to right, transparent 0, #000 32px, #000 calc(100% - 32px), transparent 100%);
        mask-image: linear-gradient(to right, transparent 0, #000 32px, #000 calc(100% - 32px), transparent 100%);
      }
      #sp-top-banner .sp-tb-track {
        display: flex;
        align-items: center;
        gap: 10px;
        width: max-content;
        animation: sp-tb-scroll 24s linear infinite;
      }
      @keyframes sp-tb-scroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
      }
      #sp-top-banner .sp-tb-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
        font-weight: 600;
      }
      #sp-top-banner .sp-tb-flag {
        border-radius: 2px;
        box-shadow: 0 0 1px rgba(0,0,0,0.3);
        flex-shrink: 0;
      }
      #sp-top-banner .sp-tb-sep {
        opacity: .4;
        flex-shrink: 0;
      }
      @media (prefers-reduced-motion: reduce) {
        #sp-top-banner .sp-tb-track {
          animation: none;
        }
      }
      body.admin-bar #sp-top-banner {
        top: 32px;
      }
      @media (max-width: 782px) {
        body.admin-bar #sp-top-banner {
          top: 46px;
        }
      }
      @media (max-width: 600px) {
        #sp-top-banner {
          font-size: 12.5px;
          padding: 7px 0;
        }
        #sp-top-banner .sp-tb-track {
          animation-duration: 18s;
        }
      }
      .elementor-location-header {
        position: fixed !important;
        left: 0;
        right: 0;
        z-index: 99999;
        width: 100%;
      }
      body.home .elementor-location-header {
        transition: transform .3s ease;
      }
      body.home {
        transition: padding-top .3s ease;
      }
    </style>
    <?php
}, 5);

add_action('wp_footer', function () {
    ?>
    <script>
    (function () {
      // Buffer so the header/logo never sits flush against the banner on desktop (avoids sub-pixel/font-timing overlap - larger because non-home pages have less internal header padding above the logo than the homepage, 11px vs 20px, so need more margin here to compensate). Desktop only: on mobile the menu bar is meant to sit flush against the banner with no gap, matching the site's existing 767px mobile breakpoint.
      function spGetHeaderSafetyGap() {
        return window.innerWidth <= 767 ? 0 : 20;
      }

      function spAdjustFixedLayout() {
        var banner = document.getElementById('sp-top-banner');
        var header = document.querySelector('.elementor-location-header');
        if (!banner || !header) {
          return;
        }
        var bannerHeight = banner.offsetHeight;
        header.style.top = (bannerHeight + spGetHeaderSafetyGap()) + 'px';
        window.__spBannerHeight = bannerHeight;
        window.__spHeaderHeight = header.offsetHeight;
        if (!window.__spHeaderHidden) {
          document.body.style.paddingTop = (bannerHeight + spGetHeaderSafetyGap() + header.offsetHeight) + 'px';
        }
      }

      // Keep the offset in sync with the banner/header's *actual* rendered
      // size at all times (font swaps, text reflow, resizes) instead of
      // racing a fixed set of load/resize events, which can leave a stale,
      // too-large gap if the size changes after the last event fired.
      var spBannerEl = document.getElementById('sp-top-banner');
      var spHeaderEl = document.querySelector('.elementor-location-header');
      if (window.ResizeObserver && spBannerEl && spHeaderEl) {
        var spLayoutObserver = new ResizeObserver(spAdjustFixedLayout);
        spLayoutObserver.observe(spBannerEl);
        spLayoutObserver.observe(spHeaderEl);
      } else {
        window.addEventListener('load', spAdjustFixedLayout);
        window.addEventListener('resize', spAdjustFixedLayout);
        document.addEventListener('DOMContentLoaded', spAdjustFixedLayout);
        if (document.fonts && document.fonts.ready) {
          document.fonts.ready.then(spAdjustFixedLayout);
        }
      }
      spAdjustFixedLayout();

      // Homepage only: hide the menu bar once the visitor scrolls down past
      // the top, show it again only once they're back at the very top -
      // no reveal on incidental upward scroll wobble.
      if (document.body.classList.contains('home')) {
        var header = document.querySelector('.elementor-location-header');
        if (header) {
          window.__spHeaderHidden = false;
          var ticking = false;
          function updateHeaderVisibility() {
            ticking = false;
            var y = window.scrollY || window.pageYOffset;
            var bannerH = window.__spBannerHeight || 0;
            var headerH = window.__spHeaderHeight || header.offsetHeight;

            if (y <= 10) {
              if (window.__spHeaderHidden) {
                window.__spHeaderHidden = false;
                header.style.transform = 'translateY(0)';
                document.body.style.paddingTop = (bannerH + spGetHeaderSafetyGap() + headerH) + 'px';
              }
            } else if (!window.__spHeaderHidden) {
              window.__spHeaderHidden = true;
              header.style.transform = 'translateY(-120%)';
              document.body.style.paddingTop = bannerH + 'px';
            }
          }
          window.addEventListener('scroll', function () {
            if (!ticking) {
              ticking = true;
              requestAnimationFrame(updateHeaderVisibility);
            }
          }, { passive: true });
        }
      }

    })();
    </script>
    <?php
}, 5);
