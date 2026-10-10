<?php
/**
 * Email Footer - Peptrium-Design (sp-email-design.php, 2026-10-10).
 * Schliesst den Rahmen aus email-header.php; dunkler Footer mit E-Mail- und
 * Telegram-Button (direkter Link t.me/peptrium), Links und Labor-Hinweis.
 */
defined('ABSPATH') || exit;

$btn = 'display:inline-block;white-space:nowrap;box-sizing:border-box;text-decoration:none;font-weight:700;font-size:14px;line-height:1.2;padding:12px 18px;border-radius:12px;';
?>
</td></tr>
<tr><td id="sp-em-foot" style="<?php echo esc_attr(SP_EM_GRAD); ?>padding:28px 22px 26px;text-align:center;font-family:<?php echo esc_attr(SP_EM_FONT); ?>;">
<p style="margin:0 0 6px;font-size:16px;font-weight:700;color:#FFFFFF;">Fragen? Wir sind für dich da.</p>
<p style="margin:0 0 18px;font-size:13px;line-height:1.6;color:#B9BEC5;">Antworte einfach auf diese E-Mail oder schreib uns direkt.</p>
<table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
<tr><td align="center" style="padding:0 0 10px;"><a href="mailto:info@peptrium.com" style="<?php echo esc_attr($btn); ?>background:#FFFFFF;color:#0D0F12;width:230px;text-align:center;">✉️&nbsp; info@peptrium.com</a></td></tr>
<tr><td align="center" style="padding:0 0 10px;"><a href="<?php echo esc_url(SP_EM_TELEGRAM); ?>" style="<?php echo esc_attr($btn); ?>background:#229ED9;color:#FFFFFF;width:230px;text-align:center;"><img src="<?php echo esc_url(content_url('/uploads/sp-email/telegram.png')); ?>" width="20" height="20" alt="" style="display:inline-block;width:20px;height:20px;border:0;vertical-align:-5px;margin:0;" />&nbsp;&nbsp;Telegram öffnen</a></td></tr>
</table>
<p style="margin:2px 0 18px;font-size:12px;color:#B9BEC5;">Telegram: <a href="<?php echo esc_url(SP_EM_TELEGRAM); ?>" style="color:#FFFFFF;font-weight:600;text-decoration:underline;">t.me/peptrium</a></p>
<table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0"><tr>
<td style="padding:0 9px;"><a href="<?php echo esc_url(home_url('/alle-produkte/')); ?>" style="font-size:12px;color:#C9CDD2;text-decoration:none;">Shop</a></td>
<td style="padding:0 9px;"><a href="<?php echo esc_url(home_url('/abo-modell/')); ?>" style="font-size:12px;color:#C9CDD2;text-decoration:none;">Abo-Modell</a></td>
<td style="padding:0 9px;"><a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" style="font-size:12px;color:#C9CDD2;text-decoration:none;">Mein Konto</a></td>
</tr></table>
<p style="margin:18px 0 0;font-size:11px;line-height:1.6;color:#80868E;">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
