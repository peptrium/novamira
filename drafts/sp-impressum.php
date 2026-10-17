<?php
/**
 * Plugin Name: SP Impressum
 * Description: Seite /impressum/ (normale WP-Seite, Inhalt aus sp_imp_html()) im Stil der Rechtsseiten,
 * Link "Impressum" in der Footer-Spalte "Rechtliches". 2026-10-17 (User: nur Firma + Adresse).
 */

if (!defined('ABSPATH')) exit;

function sp_imp_page_id() { return (int) get_option('sp_imp_page_id', 0); }

function sp_imp_html() {
    return '<div class="sp-imp">'
        . '<span class="sp-imp-eye">Rechtliches</span>'
        . '<h1>Impressum</h1>'
        . '<div class="sp-imp-card">'
        . '<h2>Anbieter</h2>'
        . '<p><strong>STM Prime Holdings LLC</strong><br>30 N Gould St, Ste N<br>Sheridan, WY 82801<br>USA</p>'
        . '<h2>Kontakt</h2>'
        . '<p>E-Mail: <a href="mailto:info@peptrium.com">info@peptrium.com</a><br>Telegram: <a href="https://t.me/peptrium" target="_blank" rel="noopener">@peptrium</a></p>'
        . '<h2>Verbraucherstreitbeilegung</h2>'
        . '<p>Wir sind nicht bereit und nicht verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>'
        . '</div>'
        . '<p class="sp-imp-note">Alle Produkte sind ausschließlich für Laborforschungszwecke bestimmt und nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</p>'
        . '</div>';
}

add_filter('the_content', function ($c) {
    if (!sp_imp_page_id() || !is_page(sp_imp_page_id()) || !in_the_loop()) return $c;
    return sp_imp_html();
}, 20);

add_filter('astra_the_title_enabled', function ($on) {
    return (sp_imp_page_id() && is_page(sp_imp_page_id())) ? false : $on;
});

add_action('wp_head', function () {
    if (!sp_imp_page_id() || !is_page(sp_imp_page_id())) return;
    echo '<style>
.sp-imp{max-width:760px;margin:3.5rem auto 4.5rem;padding:0 16px;color:#0D0F12;font-family:inherit}
.sp-imp-eye{display:inline-block;padding:.4rem 1rem;border-radius:999px;background:rgba(13,15,18,.08);font-size:.72rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;margin-bottom:1.2rem}
.sp-imp h1{font-size:clamp(2rem,5vw,2.8rem);font-weight:700;letter-spacing:-.02em;margin:0 0 1.6rem;color:#0D0F12}
.sp-imp-card{background:#fff;border:1px solid #E6E8EB;border-radius:20px;padding:26px 22px;box-shadow:0 6px 24px rgba(13,15,18,.05)}
.sp-imp-card h2{font-size:.78rem!important;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#6B7280;margin:22px 0 8px}
.sp-imp-card h2:first-child{margin-top:0}
.sp-imp-card p{font-size:1rem;line-height:1.7;margin:0;color:#0D0F12}
.sp-imp-card a{color:#0D0F12;text-decoration:underline;text-underline-offset:3px}
.sp-imp-note{font-size:.85rem;color:#6B7280;line-height:1.6;margin:18px 4px 0}
</style>';
});

/* Footer-Spalte "Rechtliches": Impressum nach Datenschutz einfügen */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || !sp_imp_page_id()) return;
    ob_start(function ($html) {
        if (strpos($html, 'href="/impressum/"') !== false) return $html;
        return preg_replace('~(<a href="/datenschutz/">Datenschutz</a>)~', '$1' . "\n  " . '<a href="/impressum/">Impressum</a>', $html, 1);
    });
}, 6);
