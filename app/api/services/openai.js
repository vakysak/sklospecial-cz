'use strict';

const OpenAI = require('openai');
const {
  searchProducts,
  getProduct,
  getKonfiguratorLink,
  createPoptavka,
  recommendAddons,
  findSimilarProducts,
  normalizeTyp,
} = require('./catalog');
const { checkDimensions } = require('./rozmery');

// Phase C2 (AR / DoorVision) and C3 (WhatsApp Business AI) deferred — later.

const SYSTEM_PROMPT = `Jsi asistent firmy sklospecial.cz specializovaný na skleněné dveře.
Pomáháš zákazníkům vybrat správný typ skleněných dveří, poradit s zaměřením
a provést je procesem poptávky.

Máš k dispozici nástroje: search_products, get_product, find_similar_products,
get_konfigurator_link, check_dimensions, recommend_addons, create_poptavka a vytvor_poptavku.
Než vymyslíš konkrétní model, cenu nebo kód SklS, VŽDY nejdřív použij nástroje.
Nikdy nevymýšlej kódy SklS ani ceny — ber je jen z výsledků nástrojů.

Když zákazník pošle fotku otvoru / prostoru:
- Odhadni pravděpodobný typ dveří: posuvné po stěně, posuvné do pouzdra, kyvné (otočné), otevírané.
- Uveď nejistotu (co z fotky nevidíš — hloubka stěny, skryté pouzdro, nosnost…).
- Dej krátký checklist zaměření: 3 šířky, 3 výšky, vždy ber nejmenší, fotky detailů (podlaha, strop, bok stěny).
- Nenavrhuj konkrétní SklS jen z fotky bez search_products / get_product / find_similar_products, pokud to dává smysl.
- Po analýze nabídni, že můžeš najít podobné dveře v katalogu (find_similar_products s use_uploaded_image).

Když zákazník pošle fotku dveří (nejen holý otvor), nebo chce „něco podobného“ /
„najdi podobné“ / „podobné jako …“:
- Zavolej find_similar_products.
- U fotky: use_uploaded_image=true (atributy vytáhne vision uvnitř nástroje).
- U kódu SklS-XXXX: předej code a vyluč stejný produkt.
- Ukaž až 5 tipů s kódem, cenou a odkazem.

Když zákazník napíše / vloží rozměry (šířky a výšky):
- Nejdřív zavolej check_dimensions.
- Teprve potom doporuč poptávku / create_poptavka.
- Vždy připomeň, že se řídíme nejmenšími hodnotami.

Když doporučuješ produkt nebo zákazník řeší doplňky u SklS kódu:
- Zavolej recommend_addons.
- Navrhni 1–3 volitelné upgrady s + Kč (pokud existuje surcharge).
- Vždy nabídni i služby: doprava, montáž, zaměření (bez SKU).
- Když zákazník souhlasí s doplňky, při create_poptavka je vlož do addons_summary.

Když doporučuješ produkty:
- maximálně 3 tipy (u find_similar_products až 5)
- uveď kód (např. SklS-0002) a orientační cenu v Kč
- odkazuj relativními WP cestami ve formátu /sklenene-dvere/posuvne/?kod=SklS-0002
  (nebo odpovídající kategorii: otocne, otevirane, celosklenene, posuvne/do-pouzdra)

Když zákazník chce poptávku / objednávku / „chci tohle“ u konkrétního produktu:
- použij create_poptavka (nebo vytvor_poptavku) s kódem, jménem, cenou, shrnutím a případně addons_summary
- v odpovědi uveď, že může kliknout na tlačítko „Odeslat poptávku“ v chatu
- můžeš také vložit markdown odkaz na vrácené url

Konfigurátor: /public/konfigurator.html (nebo odkaz z get_konfigurator_link).
Návod na zaměření: /navod-na-zamereni/
Poptávka: /poptavka/ nebo create_poptavka / sestavení v konfigurátoru.
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

Na konci nabídni sestavení v konfigurátoru, create_poptavka, nebo kontakt / WhatsApp.`;

const POPTAVKA_TOOL_PARAMS = {
  type: 'object',
  properties: {
    kod: {
      type: 'string',
      description: 'Kód produktu SklS-XXXX (doporučeno)',
    },
    name: {
      type: 'string',
      description: 'Název produktu (pokud znáš z katalogu)',
    },
    price: {
      type: 'number',
      description: 'Orientační cena v Kč z katalogu',
    },
    selected_summary: {
      type: 'string',
      description:
        'Krátké textové shrnutí výběru (typ, sklo, kování, rozměry…) pro poptávku',
    },
    addons_summary: {
      type: 'string',
      description:
        'Text doporučených / odsouhlasených doplňků (madlo, samozavírač, doprava, montáž…)',
    },
  },
  additionalProperties: false,
};

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
      name: 'find_similar_products',
      description:
        'Najde až 5 podobných produktů SklS. Použij při fotce dveří, „něco podobného“, nebo referenčním kódu SklS. U fotky nastav use_uploaded_image=true (vision vytáhne typ/sklo/styl). U kódu předej code.',
      parameters: {
        type: 'object',
        properties: {
          code: {
            type: 'string',
            description: 'Referenční kód SklS-XXXX (podobné jako tento produkt)',
          },
          use_uploaded_image: {
            type: 'boolean',
            description:
              'true = použij poslední nahranou fotku ze session a vytáhni atributy přes vision',
          },
          typ: {
            type: 'string',
            description:
              'Volitelný override typu: posuvne, do-pouzdra, otocne, otevirane, celosklenene',
          },
          keywords: {
            type: 'string',
            description:
              'Volitelná klíčová slova (matné, loft, zrcadlo, černý systém…). Pokud chybí u fotky, doplní vision.',
          },
        },
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
  {
    type: 'function',
    function: {
      name: 'check_dimensions',
      description:
        'Zkontroluje 3 šířky a 3 výšky otvoru v mm. Spočítá minimum, varuje u nerovností a nereálných rozměrů. Volej PŘED create_poptavka, když zákazník uvede rozměry.',
      parameters: {
        type: 'object',
        properties: {
          widths: {
            type: 'array',
            items: { type: 'number' },
            description: '3 šířky v mm (vlevo, uprostřed, vpravo)',
            minItems: 3,
            maxItems: 3,
          },
          heights: {
            type: 'array',
            items: { type: 'number' },
            description: '3 výšky v mm (vlevo, uprostřed, vpravo)',
            minItems: 3,
            maxItems: 3,
          },
        },
        required: ['widths', 'heights'],
        additionalProperties: false,
      },
    },
  },
  {
    type: 'function',
    function: {
      name: 'recommend_addons',
      description:
        'Doporučí doplňky (madlo/úchyt, samozavírač, barva kování) + služby doprava/montáž/zaměření pro daný SklS kód. Volej před poptávkou u konkrétního produktu.',
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
      name: 'create_poptavka',
      description:
        'Sestaví předvyplněný odkaz na /poptavka/ a draft poptávky (kod, name, price, shrnutí, doplňky). Použij, když zákazník chce odeslat poptávku.',
      parameters: POPTAVKA_TOOL_PARAMS,
    },
  },
  {
    type: 'function',
    function: {
      name: 'vytvor_poptavku',
      description:
        'Alias create_poptavka — sestaví předvyplněný odkaz na /poptavka/ a draft poptávky.',
      parameters: POPTAVKA_TOOL_PARAMS,
    },
  },
];

const sessions = new Map();
const MAX_MESSAGES = Number(process.env.CHAT_MAX_MESSAGES || 20);
const MAX_TOOL_ROUNDS = 4;
const MAX_IMAGES = 3;
const MAX_IMAGE_CHARS = 900_000; // ~base64 budget per image data URL

function getClient() {
  const key = process.env.OPENAI_API_KEY;
  if (!key) {
    const err = new Error('OPENAI_API_KEY není nastavený');
    err.status = 503;
    throw err;
  }
  return new OpenAI({ apiKey: key });
}

function getSession(sessionId) {
  if (!sessions.has(sessionId)) {
    sessions.set(sessionId, { messages: [], lastImages: [] });
  }
  return sessions.get(sessionId);
}

function getHistory(sessionId) {
  return getSession(sessionId).messages;
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

function keywordsFromAttrs(attrs) {
  if (!attrs || typeof attrs !== 'object') return '';
  const parts = [];
  if (attrs.sklo) parts.push(String(attrs.sklo));
  if (attrs.barva_systemu) parts.push(String(attrs.barva_systemu));
  if (attrs.styl) parts.push(String(attrs.styl));
  if (attrs.keywords) parts.push(String(attrs.keywords));
  return parts.join(' ').trim();
}

/**
 * gpt-4o vision → door attributes for catalog keyword search.
 */
async function extractDoorAttributesFromImage(client, model, imageUrl) {
  const completion = await client.chat.completions.create({
    model,
    temperature: 0.2,
    response_format: { type: 'json_object' },
    messages: [
      {
        role: 'system',
        content:
          'Jsi klasifikátor skleněných dveří pro e-shop. Vrať jen JSON s poli: ' +
          'typ (posuvne|do-pouzdra|otocne|otevirane|celosklenene|unknown), ' +
          'sklo (matne|cire|zrcadlo|leptane|other), ' +
          'barva_systemu (cerna|bila|nerez|zlata|other), ' +
          'styl (loft|design|minimal|classic|other), ' +
          'keywords (česká klíčová slova oddělená mezerou pro katalogové hledání).',
      },
      {
        role: 'user',
        content: [
          {
            type: 'text',
            text: 'Popiš typ skleněných dveří na fotce pro vyhledání podobných v katalogu.',
          },
          {
            type: 'image_url',
            image_url: { url: imageUrl, detail: 'low' },
          },
        ],
      },
    ],
  });

  const raw = completion.choices?.[0]?.message?.content || '{}';
  try {
    return JSON.parse(raw);
  } catch {
    return { typ: 'unknown', keywords: '' };
  }
}

async function runFindSimilarProducts(args, ctx) {
  const code = args.code ? String(args.code).trim() : '';
  let typ = args.typ ? normalizeTyp(args.typ) : '';
  let keywords = args.keywords ? String(args.keywords).trim() : '';
  const useImage = Boolean(args.use_uploaded_image) || (!code && !keywords && !typ);
  let attributes = null;

  if (useImage) {
    const img = (ctx.lastImages && ctx.lastImages[0]) || null;
    if (img && ctx.client) {
      attributes = await extractDoorAttributesFromImage(ctx.client, ctx.model, img);
      const attrTyp = attributes.typ && attributes.typ !== 'unknown' ? attributes.typ : '';
      if (!typ && attrTyp) typ = normalizeTyp(attrTyp);
      if (!keywords) keywords = keywordsFromAttrs(attributes);
    }
  }

  const result = findSimilarProducts({
    code: code || undefined,
    typ: typ || undefined,
    keywords: keywords || undefined,
    exclude_code: code || undefined,
    limit: 5,
  });

  return {
    ...result,
    attributes: attributes || undefined,
    source: code ? 'code' : useImage ? 'image' : 'keywords',
  };
}

async function executeTool(name, args, collected, poptavkaState, ctx) {
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
    if (name === 'find_similar_products') {
      const result = await runFindSimilarProducts(args, ctx || {});
      for (const h of result.items || []) pushCollected(collected, h);
      return JSON.stringify(result);
    }
    if (name === 'get_konfigurator_link') {
      const url = getKonfiguratorLink({ typ: args.typ, kod: args.kod });
      return JSON.stringify({ ok: true, url });
    }
    if (name === 'check_dimensions') {
      const result = checkDimensions({
        widths: args.widths,
        heights: args.heights,
      });
      return JSON.stringify(result);
    }
    if (name === 'recommend_addons') {
      const result = recommendAddons(args.code);
      if (result.ok && result.code) {
        const item = getProduct(result.code);
        if (item) pushCollected(collected, item);
      }
      return JSON.stringify(result);
    }
    if (name === 'create_poptavka' || name === 'vytvor_poptavku') {
      const result = createPoptavka({
        kod: args.kod,
        name: args.name,
        price: args.price,
        selected_summary: args.selected_summary,
        addons_summary: args.addons_summary,
      });
      if (result.ok && poptavkaState) {
        poptavkaState.draft = result.poptavka_draft;
        poptavkaState.url = result.url;
      }
      if (result.poptavka_draft && result.poptavka_draft.code) {
        const item = getProduct(result.poptavka_draft.code);
        if (item) pushCollected(collected, item);
      }
      return JSON.stringify({
        ok: true,
        url: result.url,
        poptavka_url: result.poptavka_url,
        poptavka_draft: result.poptavka_draft,
      });
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

function sanitizeImageUrl(url) {
  if (!url || typeof url !== 'string') return null;
  const s = url.trim();
  if (!s.startsWith('data:image/')) return null;
  if (!/^data:image\/(jpeg|jpg|png|webp);base64,/i.test(s)) return null;
  if (s.length > MAX_IMAGE_CHARS) return null;
  return s;
}

/**
 * Normalize inbound message content for OpenAI (text or multimodal).
 * History stores a compact text+flag form; OpenAI gets full multimodal parts.
 */
function normalizeUserContent(content) {
  if (typeof content === 'string') {
    return { openai: content.slice(0, 4000), history: content.slice(0, 4000) };
  }
  if (Array.isArray(content)) {
    const textParts = [];
    const imageParts = [];
    for (const part of content) {
      if (!part || typeof part !== 'object') continue;
      if (part.type === 'text' && part.text) {
        textParts.push(String(part.text).slice(0, 4000));
      } else if (part.type === 'image_url') {
        const url = sanitizeImageUrl(part.image_url?.url || part.url);
        if (url && imageParts.length < MAX_IMAGES) {
          imageParts.push({
            type: 'image_url',
            image_url: { url, detail: 'low' },
          });
        }
      }
    }
    const text =
      textParts.join('\n').trim() ||
      (imageParts.length ? 'Posílám fotku otvoru — poradíš typ dveří a checklist zaměření?' : '');
    if (!imageParts.length) {
      return { openai: text.slice(0, 4000), history: text.slice(0, 4000) };
    }
    const openai = [{ type: 'text', text: text.slice(0, 4000) }, ...imageParts];
    const history = `${text.slice(0, 3500)}\n[přiloženo ${imageParts.length} fotografie]`;
    return { openai, history };
  }
  if (content && typeof content === 'object' && content.text) {
    return normalizeUserContent([
      { type: 'text', text: content.text },
      ...(Array.isArray(content.images)
        ? content.images.map((url) => ({
            type: 'image_url',
            image_url: { url },
          }))
        : []),
    ]);
  }
  return { openai: '', history: '' };
}

function prepareMessages(userMessages, sessionId, pageContext) {
  const session = getSession(sessionId);
  const history = session.messages;

  const incoming = (userMessages || []).filter(
    (m) => m && (m.role === 'user' || m.role === 'assistant') && m.content
  );

  const lastIncoming = incoming[incoming.length - 1];
  if (!lastIncoming || lastIncoming.role !== 'user') {
    const err = new Error('Poslední zpráva musí být od uživatele');
    err.status = 400;
    throw err;
  }

  // Session history stays text-only; last user turn may be multimodal for OpenAI.
  const { openai: lastOpenAi, history: lastHistory } = normalizeUserContent(lastIncoming.content);
  if (!lastOpenAi || (typeof lastOpenAi === 'string' && !lastOpenAi.trim())) {
    const err = new Error('Prázdná zpráva');
    err.status = 400;
    throw err;
  }

  // Keep last uploaded images for find_similar_products (vision path).
  if (Array.isArray(lastOpenAi)) {
    const imgs = lastOpenAi
      .filter((p) => p && p.type === 'image_url' && p.image_url?.url)
      .map((p) => p.image_url.url);
    if (imgs.length) session.lastImages = imgs;
  }

  history.push({ role: 'user', content: lastHistory });
  while (history.length > MAX_MESSAGES) history.shift();

  const messages = [{ role: 'system', content: SYSTEM_PROMPT }];

  if (pageContext && (pageContext.kod || pageContext.path)) {
    const bits = [];
    if (pageContext.path) bits.push(`cesta ${pageContext.path}`);
    if (pageContext.kod) bits.push(`produkt kod ${pageContext.kod}`);
    messages.push({
      role: 'system',
      content:
        `Zákazník je na stránce: ${bits.join(', ')}. ` +
        (pageContext.kod
          ? `Pokud je relevantní, použij get_product, recommend_addons a nabídni podobné přes find_similar_products s code=${pageContext.kod}. `
          : '') +
        'Pro poptávku použij create_poptavka.',
    });
  }

  const histWithoutLast = history.slice(0, -1);
  for (const m of histWithoutLast) {
    messages.push({ role: m.role, content: m.content });
  }
  messages.push({ role: 'user', content: lastOpenAi });

  return { history, messages, session };
}

function buildResult(history, sessionId, replyText, collected, poptavkaState) {
  history.push({ role: 'assistant', content: replyText });
  const out = {
    reply: replyText,
    session_id: sessionId,
    message_count: history.length,
    products: collected,
  };
  if (poptavkaState && poptavkaState.draft) {
    out.poptavka_draft = poptavkaState.draft;
    out.poptavka_url = poptavkaState.url;
  }
  return out;
}

/**
 * Run tool-calling rounds (non-streaming). Leaves `messages` ready for a final text reply.
 */
async function runToolRounds(client, model, messages, collected, poptavkaState, ctx) {
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
    if (!msg) return '';

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
        const result = await executeTool(name, args, collected, poptavkaState, ctx);
        messages.push({
          role: 'tool',
          tool_call_id: call.id,
          content: result,
        });
      }
      continue;
    }

    return msg.content || '';
  }
  return '';
}

/**
 * @param {Array<{role:string,content:string|object|array}>} userMessages
 * @param {string} sessionId
 * @param {{ kod?: string, path?: string }|null} pageContext
 */
async function chat(userMessages, sessionId, pageContext) {
  const client = getClient();
  const model = process.env.OPENAI_MODEL || 'gpt-4o';
  const { history, messages, session } = prepareMessages(userMessages, sessionId, pageContext);
  const collected = [];
  const poptavkaState = { draft: null, url: null };
  const ctx = {
    client,
    model,
    lastImages: session.lastImages || [],
  };

  let replyText = await runToolRounds(client, model, messages, collected, poptavkaState, ctx);

  if (!replyText) {
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

  return buildResult(history, sessionId, replyText, collected, poptavkaState);
}

/**
 * Stream final assistant tokens after tool rounds.
 * @param {(chunk: object) => void} emit — SSE payload objects
 */
async function chatStream(userMessages, sessionId, pageContext, emit) {
  const client = getClient();
  const model = process.env.OPENAI_MODEL || 'gpt-4o';
  const { history, messages, session } = prepareMessages(userMessages, sessionId, pageContext);
  const collected = [];
  const poptavkaState = { draft: null, url: null };
  const ctx = {
    client,
    model,
    lastImages: session.lastImages || [],
  };

  let replyText = await runToolRounds(client, model, messages, collected, poptavkaState, ctx);

  if (replyText) {
    emit({ type: 'token', text: replyText });
  } else {
    const stream = await client.chat.completions.create({
      model,
      messages,
      temperature: 0.4,
      stream: true,
    });

    let acc = '';
    for await (const part of stream) {
      const delta = part.choices?.[0]?.delta?.content || '';
      if (!delta) continue;
      acc += delta;
      emit({ type: 'token', text: delta });
    }
    replyText = acc;
  }

  if (!replyText) {
    replyText =
      'Teď se mi nepodařilo odpovědět. Zkus to znovu, nebo si sestav dveře a pošli rozměry.';
    emit({ type: 'token', text: replyText });
  }

  const result = buildResult(history, sessionId, replyText, collected, poptavkaState);
  emit({
    type: 'done',
    success: true,
    reply: result.reply,
    session_id: result.session_id,
    message_count: result.message_count,
    products: result.products,
    poptavka_draft: result.poptavka_draft || null,
    poptavka_url: result.poptavka_url || null,
  });
  return result;
}

module.exports = { chat, chatStream, resetSession, SYSTEM_PROMPT, TOOLS };
