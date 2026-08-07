<?php
/**
 * Email handler for Poptávkový košík.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Poptavka_Kosik_Email_Handler {

    /**
     * Send admin notification + customer confirmation.
     *
     * @param array $data Inquiry payload.
     * @return true|WP_Error
     */
    public function odeslat(array $data) {
        $admin_to = get_option('poptavka_kosik_email', 'info@sklospecial.eu');
        if (!is_email($admin_to)) {
            return new WP_Error('invalid_recipient', 'Neplatný e-mail příjemce v nastavení pluginu.');
        }

        $datum = wp_date('j. n. Y H:i');
        $predmet_tpl = get_option('poptavka_kosik_predmet', 'Nová poptávka z webu — {datum}');
        $predmet = str_replace('{datum}', $datum, $predmet_tpl);

        $admin_body = $this->build_admin_body($data, $datum);
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $data['jmeno'] . ' <' . $data['email'] . '>',
        );

        $admin_sent = wp_mail($admin_to, $predmet, $admin_body, $headers);
        if (!$admin_sent) {
            return new WP_Error('mail_fail', 'Nepodařilo se odeslat e-mail. Zkuste to prosím později.');
        }

        $potvrzeni = get_option('poptavka_kosik_potvrzeni', '');
        if ($potvrzeni !== '' && is_email($data['email'])) {
            $cust_headers = array('Content-Type: text/plain; charset=UTF-8');
            $cust_subject = 'Potvrzení poptávky — Sklospecial';
            wp_mail($data['email'], $cust_subject, $potvrzeni, $cust_headers);
        }

        return true;
    }

    /**
     * Build structured plain-text admin email.
     *
     * @param array  $data  Inquiry data.
     * @param string $datum Formatted datetime.
     * @return string
     */
    private function build_admin_body(array $data, $datum) {
        $lines = array();
        $lines[] = 'Nová poptávka z webu';
        $lines[] = 'Datum: ' . $datum;
        $lines[] = '';
        $lines[] = '=== KONTAKT ===';
        $lines[] = 'Jméno: ' . $data['jmeno'];
        $lines[] = 'E-mail: ' . $data['email'];
        $lines[] = 'Telefon: ' . ($data['telefon'] !== '' ? $data['telefon'] : '—');
        $lines[] = '';

        if (!empty($data['zprava'])) {
            $lines[] = '=== ZPRÁVA ===';
            $lines[] = $data['zprava'];
            $lines[] = '';
        }

        $grouped = array();
        foreach ($data['polozky'] as $item) {
            $kat = $item['kategorie'] !== '' ? $item['kategorie'] : 'Bez kategorie';
            if (!isset($grouped[$kat])) {
                $grouped[$kat] = array();
            }
            $grouped[$kat][] = $item;
        }

        $lines[] = '=== POLOŽKY POPTÁVKY ===';
        foreach ($grouped as $kategorie => $items) {
            $lines[] = '';
            $lines[] = '--- ' . $kategorie . ' ---';
            foreach ($items as $item) {
                $lines[] = '• ' . $item['nazev'];
                $lines[] = '  Množství: ' . $item['mnozstvi'];
                if ($item['cena_od'] !== '') {
                    $lines[] = '  Cena od: ' . $item['cena_od'];
                }
                if ($item['poznamka'] !== '') {
                    $lines[] = '  Poznámka: ' . $item['poznamka'];
                }
                if ($item['id'] !== '') {
                    $lines[] = '  ID: ' . $item['id'];
                }
            }
        }

        $lines[] = '';
        $lines[] = '=== ZDROJ ===';
        $lines[] = $data['zdroj_url'] !== '' ? $data['zdroj_url'] : '—';

        return implode("\n", $lines);
    }
}
