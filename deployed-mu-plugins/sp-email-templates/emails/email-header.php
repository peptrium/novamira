<?php
/**
 * Email Header - Peptrium-Design (sp-email-design.php, 2026-10-10).
 *
 * Dunkler Kopfbereich mit Chrom-Logo, Schriftzug PEPTRIUM, optionaler
 * Bestellnummer (eyebrow), Ueberschrift, Untertitel und Fortschritt.
 * Die <h1> muss genau "<h1>" . esc_html($email_heading) . "</h1>" bleiben:
 * sp_abo_send_branded_email() ersetzt sie per str_replace (ABO-Schriftzug).
 *
 * Eigene IDs (#sp-em-hero/#sp-em-body) statt der WooCommerce-IDs, damit die
 * generischen WC-Regeln (#body_content table td {padding...}) nicht greifen.
 *
 * @see sp-order-emails.php (woocommerce_locate_template)
 */
if (!defined('ABSPATH')) {
    exit;
}

$store_name = $store_name ?? get_bloginfo('name', 'display');
$email = $email ?? null;
$hero = function_exists('sp_em_hero') ? sp_em_hero() : null;
if ($hero === null && function_exists('sp_em_default_hero')) {
    $hero = sp_em_default_hero($email);
}
$hero = is_array($hero) ? $hero : [];
$logo = get_option('woocommerce_email_header_image');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta name="color-scheme" content="light">
<title><?php echo esc_html($store_name); ?></title>
</head>
<body style="margin:0;padding:0;background:#E9EBED;" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<?php if (!empty($hero['preheader'])) : ?>
<div style="display:none;max-height:0;overflow:hidden;opacity:0;"><?php echo esc_html($hero['preheader']); ?></div>
<?php endif; ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#E9EBED;">
<tr><td align="center" style="padding:24px 10px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#FFFFFF;border-radius:20px;overflow:hidden;font-family:<?php echo esc_attr(SP_EM_FONT); ?>;color:#0D0F12;">
<tr><td id="sp-em-hero" align="center" style="<?php echo esc_attr(SP_EM_GRAD); ?>padding:28px 22px 30px;text-align:center;">
<?php if ($logo) : ?>
<a href="<?php echo esc_url(home_url('/')); ?>" style="text-decoration:none;display:inline-block;"><img src="<?php echo esc_url($logo); ?>" width="104" alt="<?php echo esc_attr($store_name); ?>" style="display:block;border:0;width:104px;height:auto;margin:0 auto;" /></a>
<?php endif; ?>
<div style="font-size:19px;font-weight:800;letter-spacing:.32em;color:#E6E8EB;margin:10px 0 0;padding-left:.32em;">PEPTRIUM</div>
<?php if (!empty($hero['eyebrow'])) : ?>
<div style="font-size:11px;font-weight:700;letter-spacing:.14em;color:<?php echo (($hero['accent'] ?? '') === 'abo') ? '#FF8A5C' : '#B9BEC5'; ?>;text-transform:uppercase;margin-top:20px;"><?php echo esc_html($hero['eyebrow']); ?></div>
<?php else : ?>
<div style="height:12px;line-height:12px;font-size:0;">&nbsp;</div>
<?php endif; ?>
<h1><?php echo esc_html($email_heading); ?></h1>
<?php if (!empty($hero['sub'])) : ?>
<p style="margin:0 auto;max-width:460px;font-size:15px;line-height:1.55;color:#C9CDD2;"><?php echo wp_kses_post($hero['sub']); ?></p>
<?php endif; ?>
<?php if (isset($hero['step']) && $hero['step'] !== null && function_exists('sp_em_tracker')) : ?>
<?php echo sp_em_tracker((int) $hero['step']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php endif; ?>
</td></tr>
<tr><td id="sp-em-body" style="padding:26px 22px 6px;font-family:<?php echo esc_attr(SP_EM_FONT); ?>;font-size:15px;line-height:1.6;color:#0D0F12;text-align:left;">
