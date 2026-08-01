'use strict';

const fs = require('fs/promises');
const path = require('path');
const nodemailer = require('nodemailer');

let transporter;

function getTransporter() {
  if (transporter) return transporter;

  const host = process.env.SMTP_HOST;
  if (!host) {
    throw Object.assign(new Error('Chybí SMTP_HOST'), { status: 500 });
  }

  transporter = nodemailer.createTransport({
    host,
    port: Number(process.env.SMTP_PORT || 587),
    secure: String(process.env.SMTP_PORT) === '465',
    auth: process.env.SMTP_USER
      ? {
          user: process.env.SMTP_USER,
          pass: process.env.SMTP_PASS,
        }
      : undefined,
  });

  return transporter;
}

async function loadTemplate(name, vars) {
  const file = path.join(__dirname, '../templates', name);
  let html = await fs.readFile(file, 'utf8');
  for (const [key, value] of Object.entries(vars)) {
    html = html.replaceAll(`{{${key}}}`, value == null ? '' : String(value));
  }
  return html;
}

function leadVars(lead) {
  const options = Array.isArray(lead.options_selected)
    ? lead.options_selected
    : (() => {
        try {
          return JSON.parse(lead.options_selected || '[]');
        } catch {
          return [];
        }
      })();
  const optionsHtml = options.length
    ? options
        .map(
          (o) =>
            `<li>${o.label}: ${o.choice}${
              o.surcharge_czk ? ` (+${o.surcharge_czk}&nbsp;Kč)` : ''
            }</li>`
        )
        .join('')
    : '<li>—</li>';

  return {
    id: lead.id,
    jmeno: lead.jmeno,
    telefon: lead.telefon,
    email: lead.email,
    mesto: lead.mesto,
    typ_dveri: lead.typ_dveri,
    pouziti: lead.pouziti,
    sirka_min: lead.sirka_min,
    sirka_max: lead.sirka_max,
    vyska_min: lead.vyska_min,
    vyska_max: lead.vyska_max,
    hloubka: lead.hloubka,
    typ_skla: lead.typ_skla,
    kovani: lead.kovani,
    montaz: lead.montaz,
    product_code: lead.product_code || '—',
    product_name: lead.product_name || '—',
    price_total: lead.price_total != null ? `${lead.price_total} Kč` : '—',
    options_html: optionsHtml,
    poznamka: lead.poznamka || '—',
    fotky_count: Array.isArray(lead.fotky) ? lead.fotky.length : 0,
    mail_from: process.env.MAIL_FROM || 'info@sklospecial.cz',
  };
}

async function sendLeadToFirm(lead, attachments = []) {
  const html = await loadTemplate('email-firma.html', leadVars(lead));
  await getTransporter().sendMail({
    from: process.env.MAIL_FROM || 'info@sklospecial.cz',
    to: process.env.MAIL_TO || process.env.MAIL_FROM,
    subject: `Poptávka #${lead.id} — ${lead.jmeno} (${lead.mesto})`,
    html,
    attachments,
  });
}

async function sendConfirmationToClient(lead) {
  const html = await loadTemplate('email-klient.html', leadVars(lead));
  await getTransporter().sendMail({
    from: process.env.MAIL_FROM || 'info@sklospecial.cz',
    to: lead.email,
    subject: `Potvrzení poptávky #${lead.id} — sklospecial.cz`,
    html,
  });
}

module.exports = {
  sendLeadToFirm,
  sendConfirmationToClient,
};
