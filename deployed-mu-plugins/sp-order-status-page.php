<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public "Bestellstatus" guest lookup: shortcode [sp_order_status] rendering
 * a Bestellnummer + E-Mail form, verified against the real order (billing
 * email match, case-insensitive), reusing sp_tracking_render_progress()
 * from sp-order-tracking.php so the visual progress state is identical to
 * the one shown inside My Account order view.
 */

add_shortcode('sp_order_status', function () {
    $order = null;
    $error = null;
    $submitted_number = '';

    if (!empty($_POST['sp_status_submit'])) {
        if (!isset($_POST['sp_status_nonce']) || !wp_verify_nonce($_POST['sp_status_nonce'], 'sp_order_status_lookup')) {
            $error = 'Sitzung abgelaufen, bitte versuche es erneut.';
        } else {
            $submitted_number = sanitize_text_field(wp_unslash($_POST['sp_order_number'] ?? ''));
            $submitted_email = sanitize_email(wp_unslash($_POST['sp_order_email'] ?? ''));
            $order_id = (int) preg_replace('/[^0-9]/', '', $submitted_number);

            if ($order_id <= 0 || empty($submitted_email)) {
                $error = 'Bitte gib deine Bestellnummer und E-Mail-Adresse ein.';
            } else {
                // Kunden kennen nur die Bestellnummer aus der Mail (_sp_order_number, sp-order-numbering.php),
                // nicht die interne ID -> zuerst danach suchen, dann (alte Bestellungen ohne Nummer) die ID.
                $ids = wc_get_orders([
                    'limit' => 1,
                    'return' => 'ids',
                    'meta_key' => '_sp_order_number',
                    'meta_value' => (string) $order_id,
                    'status' => array_keys(wc_get_order_statuses()),
                ]);
                $candidate = $ids ? wc_get_order($ids[0]) : wc_get_order($order_id);
                if (
                    $candidate
                    && strcasecmp(trim($candidate->get_billing_email()), trim($submitted_email)) === 0
                ) {
                    $order = $candidate;
                } else {
                    $error = 'Wir konnten keine Bestellung mit diesen Angaben finden. Bitte überprüfe Bestellnummer und E-Mail-Adresse.';
                }
            }
        }
    }

    ob_start();
    ?>
    <div class="sp-status-hero">
      <div class="sp-status-eyebrow">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        Sendungsverfolgung
      </div>
      <h1>Bestellstatus</h1>
      <p>Gib deine Bestellnummer und E-Mail-Adresse ein, um den aktuellen Status deiner Bestellung zu sehen.</p>
    </div>
    <div class="sp-status-wrap">
      <?php if ($order): ?>
        <div class="sp-status-found">
          <div class="sp-status-found-head">
            <div>
              <p class="sp-status-found-k">Bestellung</p>
              <p class="sp-status-found-v">#<?php echo esc_html($order->get_order_number()); ?></p>
            </div>
            <div class="sp-status-found-meta">
              <p class="sp-status-found-k">Bestelldatum</p>
              <p class="sp-status-found-v-sm"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></p>
            </div>
          </div>
          <?php echo sp_tracking_render_progress($order); ?>
          <a class="sp-status-back" href="<?php echo esc_url(get_permalink()); ?>">&larr; Andere Bestellung ansehen</a>
        </div>
      <?php else: ?>
        <form class="sp-status-form" method="post">
          <?php wp_nonce_field('sp_order_status_lookup', 'sp_status_nonce'); ?>
          <?php if ($error): ?>
            <div class="sp-status-error"><?php echo esc_html($error); ?></div>
          <?php endif; ?>
          <div class="sp-status-field">
            <label for="sp_order_number">Bestellnummer</label>
            <input type="text" id="sp_order_number" name="sp_order_number" placeholder="z. B. 1234" value="<?php echo esc_attr($submitted_number); ?>" required />
          </div>
          <div class="sp-status-field">
            <label for="sp_order_email">E-Mail-Adresse</label>
            <input type="email" id="sp_order_email" name="sp_order_email" placeholder="deine@email.de" required />
          </div>
          <button type="submit" name="sp_status_submit" value="1" class="sp-status-btn">
            Bestellung verfolgen
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </button>
          <p class="sp-status-hint">Die Bestellnummer findest du in deiner Bestätigungs-E-Mail.</p>
        </form>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});

add_action('wp_head', function () {
    if (!is_page() || !has_shortcode(get_post()->post_content ?? '', 'sp_order_status')) {
        return;
    }
    ?>
    <style id="sp-status-style">
      body.page-id-567 .entry-header{display:none}

      .sp-status-hero{max-width:480px;margin:40px auto 28px;text-align:center;font-family:'Sora',sans-serif}
      .sp-status-eyebrow{display:inline-flex;align-items:center;gap:7px;background:#F2F3F4;color:#4B5157;padding:6px 13px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:16px}
      .sp-status-eyebrow svg{width:13px;height:13px;color:#0D0F12}
      .sp-status-hero h1{font-size:30px;font-weight:800;color:#0D0F12;margin:0 0 10px}
      .sp-status-hero p{font-size:14px;color:#4B5157;line-height:1.6;margin:0}

      .sp-status-wrap{font-family:'Sora',sans-serif;max-width:480px;margin:0 auto 60px}

      .sp-status-form{border:1px solid #DCDEE0;border-radius:16px;background:#F9FAFA;padding:28px 26px}
      .sp-status-field{margin-bottom:16px}
      .sp-status-field label{display:block;font-size:12.5px;font-weight:700;color:#0D0F12;margin-bottom:6px}
      .sp-status-field input{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid #DCDEE0;border-radius:9px;background:#FFFFFF;font-family:'Sora',sans-serif;font-size:14px;color:#0D0F12}
      .sp-status-field input:focus{outline:none;border-color:#A8B0B9}
      .sp-status-btn{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:7px;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;border:none;border-radius:9px;padding:13px 18px;font-family:'Sora',sans-serif;font-weight:700;font-size:14.5px;cursor:pointer;margin-top:4px}
      .sp-status-btn svg{width:15px;height:15px}
      .sp-status-hint{margin:14px 0 0;font-size:12px;color:#4B5157;text-align:center}
      .sp-status-error{background:#FFFFFF;border:1px solid #DCDEE0;border-left:3px solid #0D0F12;border-radius:8px;padding:12px 14px;font-size:13px;color:#0D0F12;margin-bottom:18px}

      .sp-status-found{border:1px solid #DCDEE0;border-radius:16px;background:#FFFFFF;padding:24px 26px}
      .sp-status-found-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid #DCDEE0}
      .sp-status-found-meta{text-align:right}
      .sp-status-found-k{margin:0 0 3px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#4B5157}
      .sp-status-found-v{margin:0;font-size:20px;font-weight:800;color:#0D0F12}
      .sp-status-found-v-sm{margin:0;font-size:13px;font-weight:700;color:#0D0F12}
      .sp-status-back{display:inline-block;margin-top:16px;font-size:13px;color:#4B5157;text-decoration:none;font-weight:700}
      .sp-status-back:hover{color:#0D0F12}
    </style>
    <?php
}, 25);
