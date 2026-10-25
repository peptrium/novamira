<?php
/**
 * Plugin Name: SP Vorbestellung beendet
 * Description: (2026-10-25) Produkte, die wieder regulär lieferbar sind (sp_preorder_ended_ids(), z. B. BPC-157),
 *   haben in ihrem Elementor-Template noch die Vorbestell-Kaufbox (alter Preis durchgestrichen, −15 %-Chip,
 *   "Vorbestellung · Versand sobald verfügbar", Hinweisbox, Mengenstufen ohne Rabatt, unitPrice = Vorbestellpreis).
 *   Wird beim Ausgeben auf die normale Kaufbox umgeschrieben (wie Mots-C). Nichts Gespeichertes wird geändert.
 */
if (!defined('ABSPATH')) exit;

add_action('template_redirect', function () {
    if (!function_exists('sp_preorder_ended_ids') || !is_product()) return;
    $pid = (int) get_queried_object_id();
    if (!in_array($pid, sp_preorder_ended_ids(), true) || in_array($pid, sp_preorder_product_ids(), true)) return;
    $p = wc_get_product($pid);
    if (!$p || !$p->is_in_stock()) return;
    // Werte VOR ob_start berechnen (im Callback keine WC-Funktionen, die puffern könnten)
    $price = (float) ($p->is_type('variable') ? $p->get_variation_price('min') : $p->get_price());
    $fmt = number_format($price, 2, ',', '.') . '&nbsp;&euro;';
    $qty = function_exists('sp_quantity_discount_product_ids') && in_array($pid, sp_quantity_discount_product_ids(), true)
        && !(function_exists('sp_quantity_discount_is_on_sale') && sp_quantity_discount_is_on_sale($p));
    $tiers = $qty ? [1 => [0, 'Standard'], 3 => [10, '&minus;10%'], 5 => [15, '&minus;15%'], 10 => [20, '&minus;20%']] : [];
    ob_start(function ($html) use ($price, $fmt, $tiers) {
        if (strpos($html, 'id="rx-buybox"') === false) return $html;
        $html = preg_replace('~<span class="rx-price-old">[^<]*</span>\s*~', '', $html, 1);
        $html = preg_replace('~(<span class="rx-price" id="rx-price">)[^<]*(</span>)\s*<span class="rx-discount-chip">[^<]*</span>~',
            '${1}' . $fmt . '${2} <span class="rx-price-note">zzgl. Versand</span>', $html, 1);
        $html = preg_replace('~(<b id="rx-total">)[^<]*(</b>)~', '${1}' . $fmt . '${2}', $html, 1);
        $html = preg_replace('~<div class="rx-stock rx-stock--preorder">.*?</div>~s',
            '<div class="rx-stock"><span class="rx-stock-dot"></span> Auf Lager &middot; sofort versandfertig</div>', $html, 1);
        $html = preg_replace('~<div class="rx-preorder-note">.*?</span>\s*</div>~s', '', $html, 1);
        $html = preg_replace('~var unitPrice = [0-9.]+;~', 'var unitPrice = ' . $price . ';', $html, 1);
        if ($tiers) {
            $html = preg_replace_callback('~<button type="button" class="rx-tier( is-active)?" data-q="(\d+)" data-disc="\d+">\s*(?!<span class="rx-tier__disc")~',
                function ($m) use ($tiers) {
                    $q = (int) $m[2];
                    if (!isset($tiers[$q])) return $m[0];
                    return '<button type="button" class="rx-tier' . $m[1] . '" data-q="' . $q . '" data-disc="' . $tiers[$q][0] . '"> <span class="rx-tier__disc">' . $tiers[$q][1] . '</span> ';
                }, $html);
        }
        return $html;
    });
}, 8); // nach sp-halloween (7): dieser Puffer liegt innen, Halloween schreibt danach seine Stufen darüber
