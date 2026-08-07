<?php
/**
 * Plugin Name: Poptávkový košík
 * Description: Lehký poptávkový košík bez WooCommerce — localStorage + AJAX e-mail.
 * Version: 1.0.0
 * Author: Sklospecial
 * Text Domain: poptavkovy-kosik
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('POPTAVKA_KOSIK_VERSION', '1.0.0');
define('POPTAVKA_KOSIK_PATH', plugin_dir_path(__FILE__));
define('POPTAVKA_KOSIK_URL', plugin_dir_url(__FILE__));

require_once POPTAVKA_KOSIK_PATH . 'includes/class-email-handler.php';

/**
 * Activation: default options.
 */
function poptavka_kosik_activate() {
    $defaults = array(
        'email' => 'info@sklospecial.eu',
        'predmet' => 'Nová poptávka z webu — {datum}',
        'potvrzeni' => "Dobrý den,\n\nděkujeme za Vaši poptávku. Brzy se Vám ozveme.\n\nS pozdravem,\nSklospecial",
    );

    if (get_option('poptavka_kosik_email') === false) {
        add_option('poptavka_kosik_email', $defaults['email']);
    }
    if (get_option('poptavka_kosik_predmet') === false) {
        add_option('poptavka_kosik_predmet', $defaults['predmet']);
    }
    if (get_option('poptavka_kosik_potvrzeni') === false) {
        add_option('poptavka_kosik_potvrzeni', $defaults['potvrzeni']);
    }
}
register_activation_hook(__FILE__, 'poptavka_kosik_activate');

/**
 * Enqueue frontend assets on all public pages.
 */
function poptavka_kosik_enqueue() {
    if (is_admin()) {
        return;
    }

    wp_enqueue_style(
        'poptavka-kosik',
        POPTAVKA_KOSIK_URL . 'assets/kosik.css',
        array(),
        POPTAVKA_KOSIK_VERSION
    );

    wp_enqueue_script(
        'poptavka-kosik',
        POPTAVKA_KOSIK_URL . 'assets/kosik.js',
        array(),
        POPTAVKA_KOSIK_VERSION,
        true
    );

    wp_localize_script(
        'poptavka-kosik',
        'poptavkaKosik',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('poptavka_kosik_nonce'),
            'email' => get_option('poptavka_kosik_email', 'info@sklospecial.eu'),
        )
    );
}
add_action('wp_enqueue_scripts', 'poptavka_kosik_enqueue');

/**
 * AJAX: send inquiry (logged-in + guests).
 */
function poptavka_kosik_ajax_odeslat() {
    check_ajax_referer('poptavka_kosik_nonce', 'nonce');

    $jmeno = isset($_POST['jmeno']) ? sanitize_text_field(wp_unslash($_POST['jmeno'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $telefon = isset($_POST['telefon']) ? sanitize_text_field(wp_unslash($_POST['telefon'])) : '';
    $zprava = isset($_POST['zprava']) ? sanitize_textarea_field(wp_unslash($_POST['zprava'])) : '';
    $zdroj_url = isset($_POST['zdroj_url']) ? esc_url_raw(wp_unslash($_POST['zdroj_url'])) : '';

    $polozky_raw = isset($_POST['polozky']) ? wp_unslash($_POST['polozky']) : '';
    if (is_string($polozky_raw)) {
        $polozky = json_decode($polozky_raw, true);
    } else {
        $polozky = $polozky_raw;
    }

    if ($jmeno === '' || $email === '' || !is_email($email)) {
        wp_send_json_error(array('message' => 'Vyplňte prosím jméno a platný e-mail.'), 400);
    }

    if (!is_array($polozky) || count($polozky) === 0) {
        wp_send_json_error(array('message' => 'Poptávka je prázdná.'), 400);
    }

    $clean_items = array();
    foreach ($polozky as $item) {
        if (!is_array($item)) {
            continue;
        }
        $clean_items[] = array(
            'id' => isset($item['id']) ? sanitize_text_field($item['id']) : '',
            'nazev' => isset($item['nazev']) ? sanitize_text_field($item['nazev']) : '',
            'kategorie' => isset($item['kategorie']) ? sanitize_text_field($item['kategorie']) : '',
            'cena_od' => isset($item['cena_od']) ? sanitize_text_field($item['cena_od']) : '',
            'fotka' => isset($item['fotka']) ? esc_url_raw($item['fotka']) : '',
            'mnozstvi' => isset($item['mnozstvi']) ? max(1, absint($item['mnozstvi'])) : 1,
            'poznamka' => isset($item['poznamka']) ? sanitize_textarea_field($item['poznamka']) : '',
        );
    }

    if (count($clean_items) === 0) {
        wp_send_json_error(array('message' => 'Poptávka je prázdná.'), 400);
    }

    $handler = new Poptavka_Kosik_Email_Handler();
    $result = $handler->odeslat(array(
        'jmeno' => $jmeno,
        'email' => $email,
        'telefon' => $telefon,
        'zprava' => $zprava,
        'zdroj_url' => $zdroj_url,
        'polozky' => $clean_items,
    ));

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()), 500);
    }

    wp_send_json_success(array('message' => 'Poptávka byla úspěšně odeslána.'));
}
add_action('wp_ajax_odeslat_poptavku', 'poptavka_kosik_ajax_odeslat');
add_action('wp_ajax_nopriv_odeslat_poptavku', 'poptavka_kosik_ajax_odeslat');

/**
 * Shortcode: [poptavka_tlacitko id="" nazev="" kategorie="" cena="" fotka=""]
 */
function poptavka_kosik_shortcode_tlacitko($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '',
            'nazev' => '',
            'kategorie' => '',
            'cena' => '',
            'fotka' => '',
        ),
        $atts,
        'poptavka_tlacitko'
    );

    if ($atts['id'] === '' || $atts['nazev'] === '') {
        return '';
    }

    return sprintf(
        '<button type="button" class="poptavka-btn" data-id="%s" data-nazev="%s" data-kategorie="%s" data-cena="%s" data-fotka="%s">Přidat do poptávky</button>',
        esc_attr($atts['id']),
        esc_attr($atts['nazev']),
        esc_attr($atts['kategorie']),
        esc_attr($atts['cena']),
        esc_attr($atts['fotka'])
    );
}
add_shortcode('poptavka_tlacitko', 'poptavka_kosik_shortcode_tlacitko');

/**
 * Shortcode: [poptavka_ikona] — cart icon + badge.
 */
function poptavka_kosik_shortcode_ikona() {
    return '<button type="button" class="poptavka-ikona" aria-label="Otevřít poptávku" title="Vaše poptávka">'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . '<path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>'
        . '<path d="M6 6L5 3H2"/></svg>'
        . '<span class="poptavka-badge" hidden>0</span>'
        . '</button>';
}
add_shortcode('poptavka_ikona', 'poptavka_kosik_shortcode_ikona');

/**
 * Print panel template once in footer.
 */
function poptavka_kosik_footer_panel() {
    static $printed = false;
    if ($printed || is_admin()) {
        return;
    }
    $printed = true;
    include POPTAVKA_KOSIK_PATH . 'templates/panel.php';
}
add_action('wp_footer', 'poptavka_kosik_footer_panel', 50);

/**
 * Settings page under Nastavení → Poptávkový košík.
 */
function poptavka_kosik_settings_menu() {
    add_options_page(
        'Poptávkový košík',
        'Poptávkový košík',
        'manage_options',
        'poptavkovy-kosik',
        'poptavka_kosik_settings_page'
    );
}
add_action('admin_menu', 'poptavka_kosik_settings_menu');

function poptavka_kosik_register_settings() {
    register_setting('poptavka_kosik_settings', 'poptavka_kosik_email', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default' => 'info@sklospecial.eu',
    ));
    register_setting('poptavka_kosik_settings', 'poptavka_kosik_predmet', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Nová poptávka z webu — {datum}',
    ));
    register_setting('poptavka_kosik_settings', 'poptavka_kosik_potvrzeni', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '',
    ));
}
add_action('admin_init', 'poptavka_kosik_register_settings');

function poptavka_kosik_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Poptávkový košík</h1>
        <form method="post" action="options.php">
            <?php settings_fields('poptavka_kosik_settings'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="poptavka_kosik_email">E-mail příjemce</label></th>
                    <td>
                        <input type="email" class="regular-text" id="poptavka_kosik_email" name="poptavka_kosik_email"
                               value="<?php echo esc_attr(get_option('poptavka_kosik_email', 'info@sklospecial.eu')); ?>" required>
                        <p class="description">Kam chodí admin notifikace (výchozí: info@sklospecial.eu).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="poptavka_kosik_predmet">Předmět e-mailu</label></th>
                    <td>
                        <input type="text" class="regular-text" id="poptavka_kosik_predmet" name="poptavka_kosik_predmet"
                               value="<?php echo esc_attr(get_option('poptavka_kosik_predmet', 'Nová poptávka z webu — {datum}')); ?>">
                        <p class="description">Použijte <code>{datum}</code> pro aktuální datum.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="poptavka_kosik_potvrzeni">Potvrzení zákazníkovi</label></th>
                    <td>
                        <textarea class="large-text" rows="8" id="poptavka_kosik_potvrzeni" name="poptavka_kosik_potvrzeni"><?php
                            echo esc_textarea(get_option('poptavka_kosik_potvrzeni', ''));
                        ?></textarea>
                        <p class="description">Text potvrzovacího e-mailu zákazníkovi.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <hr>
        <h2>Shortcody</h2>
        <p><code>[poptavka_tlacitko id="produkt-1" nazev="Název" kategorie="Kategorie" cena="1 000 Kč" fotka="https://…"]</code></p>
        <p><code>[poptavka_ikona]</code> — ikona košíku s odznakem (panel se načte automaticky ve footeru).</p>
    </div>
    <?php
}
