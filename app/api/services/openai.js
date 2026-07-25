'use strict';

const OpenAI = require('openai');

const SYSTEM_PROMPT = `Jsi asistent firmy sklospecial.cz specializovaný na skleněné dveře.
Pomáháš zákazníkům vybrat správný typ skleněných dveří, poradit s zaměřením
a provést je procesem poptávky.

Odpovídáš pouze k tématu skleněných dveří, zaměření, výběru skla,
kování a procesu objednávky.

Pokud se zákazník ptá na něco mimo tento rozsah, zdvořile ho přesměruješ
na kontakt nebo formulář.

Vždy mluv česky. Tykej zákazníkovi.
Buď konkrétní, stručný a praktický.
Nepoužívej marketingové fráze.

Pokud zákazník neví, jaký typ dveří chce, zeptej se:
- Kde budou dveře? (byt, koupelna, kancelář)
- Kolik je místa kolem otvoru?
- Preferuješ otočné nebo posuvné?

Pokud zákazník chce podat poptávku, řekni mu, co potřebuje:
- šířka a výška otvoru (změřit na 3 místech)
- fotka celého otvoru
- fotka detailu stěny a podlahy
- informace o typu otevírání
- lokalita

Na konci konverzace vždy nabídni sestavení dveří (/sklenene-dvere/#sestavit) nebo kontakt.`;

const sessions = new Map();
const MAX_MESSAGES = Number(process.env.CHAT_MAX_MESSAGES || 20);

function getClient() {
  const key = process.env.OPENAI_API_KEY;
  if (!key) {
    const err = new Error('OPENAI_API_KEY není nastavený');
    err.status = 503;
    throw err;
  }
  return new OpenAI({ apiKey: key });
}

function getHistory(sessionId) {
  if (!sessions.has(sessionId)) {
    sessions.set(sessionId, []);
  }
  return sessions.get(sessionId);
}

function resetSession(sessionId) {
  sessions.delete(sessionId);
}

/**
 * Non-streaming reply for reliability; UI can still feel snappy.
 */
async function chat(userMessages, sessionId) {
  const client = getClient();
  const history = getHistory(sessionId);

  const incoming = (userMessages || [])
    .filter((m) => m && (m.role === 'user' || m.role === 'assistant') && m.content)
    .map((m) => ({ role: m.role, content: String(m.content).slice(0, 4000) }));

  const last = incoming[incoming.length - 1];
  if (!last || last.role !== 'user') {
    const err = new Error('Poslední zpráva musí být od uživatele');
    err.status = 400;
    throw err;
  }

  history.push(last);
  while (history.length > MAX_MESSAGES) history.shift();

  const model = process.env.OPENAI_MODEL || 'gpt-4o';
  const messages = [{ role: 'system', content: SYSTEM_PROMPT }, ...history];

  let replyText = '';
  try {
    // Prefer Responses API when available
    if (typeof client.responses?.create === 'function') {
      const response = await client.responses.create({
        model,
        input: messages.map((m) => ({
          role: m.role,
          content: m.content,
        })),
      });
      replyText = response.output_text || '';
    } else {
      throw new Error('no responses');
    }
  } catch {
    const completion = await client.chat.completions.create({
      model,
      messages,
      temperature: 0.4,
    });
    replyText = completion.choices?.[0]?.message?.content || '';
  }

  if (!replyText) replyText = 'Teď se mi nepodařilo odpovědět. Zkus to znovu, nebo si sestav dveře a pošli rozměry.';

  history.push({ role: 'assistant', content: replyText });
  return { reply: replyText, session_id: sessionId, message_count: history.length };
}

module.exports = { chat, resetSession, SYSTEM_PROMPT };
