'use strict';

const OpenAI = require('openai');
const {
  searchProducts,
  getProduct,
  getKonfiguratorLink,
} = require('./catalog');

const SYSTEM_PROMPT = `Jsi asistent firmy sklospecial.cz specializovaný na skleněné dveře.
Pomáháš zákazníkům vybrat správný typ skleněných dveří, poradit s zaměřením
a provést je procesem poptávky.

Máš k dispozici nástroje search_products, get_product a get_konfigurator_link.
Než vymyslíš konkrétní model, cenu nebo kód SklS, VŽDY nejdřív použij nástroje.
Nikdy nevymýšlej kódy SklS ani ceny — ber je jen z výsledků nástrojů.

Když doporučuješ produkty:
- maximálně 3 tipy
- uveď kód (např. SklS-0002) a orientační cenu v Kč
- odkazuj relativními WP cestami ve formátu /sklenene-dvere/posuvne/?kod=SklS-0002
  (nebo odpovídající kategorii: otocne, otevirane, celosklenene, posuvne/do-pouzdra)

Konfigurátor: /public/konfigurator.html (nebo odkaz z get_konfigurator_link).
Návod na zaměření: /navod-na-zamereni/
Poptávka: /poptavka/ nebo sestavení v konfigurátoru.
WhatsApp: https://wa.me/420736134604

Odpovídáš pouze k tématu skleněných dveří, zaměření, výběru skla,
kování a procesu objednávky. Mimo rozsah zdvořile přesměruj na kontakt.

Vždy mluv česky. Tykej. Buď konkrétní, stručný a praktický.
Nepoužívej marketingové fráze (luxusní, prémiový, exkluzivní).

Pokud zákazník neví typ dveří, zeptej se:
- Kde budou dveře? (byt, koupelna, kancelář)
- Kolik je místa kolem otvoru?
- Preferuješ otočné/kyvné nebo posuvné?

Pro poptávku potřebuješ:
- šířka a výška otvoru (změřit na 3 místech)
- fotka celého otvoru
- fotka detailu stěny a podlahy
- typ otevírání
- lokalita

Na konci nabídni sestavení v konfigurátoru nebo kontakt / WhatsApp.`;

const TOOLS = [
  {
    type: 'function',
    function: {
      name: 'search_products',
      description:
        'Vyhledá produkty v katalogu SklS. Filtruj podle typu dveří, klíčových slov a max. ceny.',
      parameters: {
        type: 'object',
        properties: {
          typ: {
            type: 'string',
            description:
              'Typ: posuvne, do-pouzdra, otocne (kyvne), otevirane, celosklenene',
          },
          keywords: {
            type: 'string',
            description: 'Klíčová slova (matné, loft, zrcadlo, …), oddělená mezerou',
          },
          max_price: {
            type: 'number',
            description: 'Maximální orientační cena v Kč',
          },
        },
        additionalProperties: false,
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'get_product',
      description: 'Detail jednoho produktu podle kódu SklS (např. SklS-0002).',
      parameters: {
        type: 'object',
        properties: {
          code: { type: 'string', description: 'Kód produktu SklS-XXXX' },
        },
        required: ['code'],
        additionalProperties: false,
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'get_konfigurator_link',
      description: 'Vrátí odkaz do konfigurátoru s volitelným typem a kódem produktu.',
      parameters: {
        type: 'object',
        properties: {
          typ: { type: 'string' },
          kod: { type: 'string', description: 'Kód SklS' },
        },
        additionalProperties: false,
      },
    },
  },
];

const sessions = new Map();
const MAX_MESSAGES = Number(process.env.CHAT_MAX_MESSAGES || 20);
const MAX_TOOL_ROUNDS = 4;

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

function pushCollected(collected, item) {
  if (!item || !item.code) return;
  if (collected.some((p) => p.code === item.code)) return;
  if (collected.length >= 5) return;
  collected.push({
    code: item.code,
    name: item.name,
    price: item.price,
    url_path: item.url_path,
    image: item.image || '',
    typ: item.typ || undefined,
  });
}

function executeTool(name, args, collected) {
  try {
    if (name === 'search_products') {
      const hits = searchProducts({
        typ: args.typ,
        keywords: args.keywords,
        max_price: args.max_price,
        limit: 5,
      });
      for (const h of hits) pushCollected(collected, h);
      return JSON.stringify({ ok: true, items: hits });
    }
    if (name === 'get_product') {
      const item = getProduct(args.code);
      if (item) pushCollected(collected, item);
      return JSON.stringify({ ok: true, item });
    }
    if (name === 'get_konfigurator_link') {
      const url = getKonfiguratorLink({ typ: args.typ, kod: args.kod });
      return JSON.stringify({ ok: true, url });
    }
    return JSON.stringify({ ok: false, error: `Neznámý nástroj: ${name}` });
  } catch (err) {
    return JSON.stringify({ ok: false, error: err.message || String(err) });
  }
}

function parseArgs(raw) {
  if (!raw) return {};
  if (typeof raw === 'object') return raw;
  try {
    return JSON.parse(raw);
  } catch {
    return {};
  }
}

/**
 * @param {Array<{role:string,content:string}>} userMessages
 * @param {string} sessionId
 * @param {{ kod?: string, path?: string }|null} pageContext
 */
async function chat(userMessages, sessionId, pageContext) {
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
  const messages = [{ role: 'system', content: SYSTEM_PROMPT }];

  if (pageContext && (pageContext.kod || pageContext.path)) {
    const bits = [];
    if (pageContext.path) bits.push(`cesta ${pageContext.path}`);
    if (pageContext.kod) bits.push(`produkt kod ${pageContext.kod}`);
    messages.push({
      role: 'system',
      content: `Zákazník je na stránce: ${bits.join(', ')}. Pokud je relevantní, použij get_product pro tento kód.`,
    });
  }

  messages.push(...history);

  const collected = [];
  let replyText = '';

  for (let round = 0; round < MAX_TOOL_ROUNDS; round += 1) {
    const completion = await client.chat.completions.create({
      model,
      messages,
      tools: TOOLS,
      tool_choice: 'auto',
      temperature: 0.4,
    });

    const choice = completion.choices?.[0];
    const msg = choice?.message;
    if (!msg) break;

    const toolCalls = msg.tool_calls;
    if (toolCalls && toolCalls.length) {
      messages.push({
        role: 'assistant',
        content: msg.content || null,
        tool_calls: toolCalls,
      });

      for (const call of toolCalls) {
        const name = call.function?.name || '';
        const args = parseArgs(call.function?.arguments);
        const result = executeTool(name, args, collected);
        messages.push({
          role: 'tool',
          tool_call_id: call.id,
          content: result,
        });
      }
      continue;
    }

    replyText = msg.content || '';
    break;
  }

  if (!replyText) {
    // one more pass without forcing tools if loop exhausted mid-tools
    const completion = await client.chat.completions.create({
      model,
      messages,
      temperature: 0.4,
    });
    replyText = completion.choices?.[0]?.message?.content || '';
  }

  if (!replyText) {
    replyText =
      'Teď se mi nepodařilo odpovědět. Zkus to znovu, nebo si sestav dveře a pošli rozměry.';
  }

  history.push({ role: 'assistant', content: replyText });
  return {
    reply: replyText,
    session_id: sessionId,
    message_count: history.length,
    products: collected,
  };
}

module.exports = { chat, resetSession, SYSTEM_PROMPT, TOOLS };
