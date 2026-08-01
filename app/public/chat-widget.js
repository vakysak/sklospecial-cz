/* sklo chat-widget v1.8.4 */
(() => {
  const API = window.SKLO_API_BASE || '';
  const CONFIG_URL = window.SKLO_CONFIGURATOR_URL || `${API}/public/konfigurator.html`;
  const WA_URL =
    'https://wa.me/420736134604?text=' +
    encodeURIComponent('Dobrý den, mám dotaz ke skleněným dveřím…');
  const MAX_PHOTOS = 3;
  const MAX_PHOTO_EDGE = 1280;
  const JPEG_QUALITY = 0.72;

  const css = `
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@500;600&display=swap');
  .sklo-chat-btn{position:fixed;right:1.1rem;bottom:1.1rem;z-index:9999;border:0;border-radius:999px;background:#1a5c6b;color:#fff;padding:.7rem 1.15rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.12rem;font:600 13px/1.15 Outfit,sans-serif;cursor:pointer;box-shadow:0 14px 34px rgba(26,92,107,.28);transition:transform .15s ease;text-align:center}
  .sklo-chat-btn:hover{transform:translateY(-1px)}
  .sklo-chat-btn__line{display:block}
  .sklo-chat-btn__line--wa{font-weight:500;font-size:11px;line-height:1.1;opacity:.88;letter-spacing:.02em}
  .sklo-chat-choice{position:fixed;right:1.1rem;bottom:5.4rem;width:min(280px,calc(100vw - 2rem));z-index:9999;display:none;flex-direction:column;gap:.45rem;padding:.7rem;border-radius:16px;background:#152028;border:1px solid rgba(255,255,255,.08);box-shadow:0 18px 40px rgba(10,20,28,.35);font-family:Outfit,sans-serif}
  .sklo-chat-choice.is-open{display:flex}
  .sklo-chat-choice__label{margin:0 .15rem .15rem;font:500 11px/1.3 Outfit,sans-serif;letter-spacing:.04em;text-transform:uppercase;color:rgba(255,255,255,.55)}
  .sklo-chat-choice__opt{border:0;border-radius:12px;padding:.75rem .9rem;text-align:left;cursor:pointer;font:600 14px/1.25 Outfit,sans-serif;background:rgba(255,255,255,.06);color:#f2f6f8;transition:background .15s ease}
  .sklo-chat-choice__opt:hover{background:rgba(255,255,255,.11)}
  .sklo-chat-choice__opt--wa{background:rgba(37,211,102,.14);color:#b8f5cf}
  .sklo-chat-choice__opt--wa:hover{background:rgba(37,211,102,.22)}
  .sklo-chat-choice__hint{margin:0 .15rem;font:500 12px/1.35 Outfit,sans-serif;color:rgba(255,255,255,.45)}
  .sklo-chat-panel{position:fixed;right:1.1rem;bottom:5.4rem;width:min(360px,calc(100vw - 2rem));height:min(560px,calc(100vh - 6rem));background:rgba(255,255,255,.92);backdrop-filter:blur(14px);border:1px solid rgba(18,24,28,.1);border-radius:18px;z-index:9999;display:none;flex-direction:column;box-shadow:0 22px 50px rgba(18,40,55,.16);overflow:hidden;font-family:Outfit,sans-serif}
  .sklo-chat-panel.is-open{display:flex}
  .sklo-chat-panel header{padding:.85rem 1rem;border-bottom:1px solid rgba(18,24,28,.08);display:flex;justify-content:space-between;align-items:center;gap:.5rem;background:linear-gradient(180deg,#f7fbfc,#fff)}
  .sklo-chat-panel header strong{font:600 14px Outfit,sans-serif;color:#12181c}
  .sklo-chat-panel header button{border:0;background:transparent;cursor:pointer;font:500 12px Outfit,sans-serif;color:#1a5c6b;padding:.2rem .35rem}
  .sklo-chat-panel header button[data-act="close"]{color:#5b6a75;font-weight:600}
  .sklo-chat-msgs{flex:1;overflow:auto;padding:1rem;display:flex;flex-direction:column;gap:.55rem;background:linear-gradient(180deg,#f3f7f9,#fbfcfd)}
  .sklo-chat-msg{max-width:92%;padding:.7rem .8rem;font:14px/1.45 Outfit,sans-serif;border-radius:14px;border:1px solid rgba(18,24,28,.08);background:#fff;color:#12181c}
  .sklo-chat-msg.user{align-self:flex-end;background:#1a5c6b;color:#fff;border-color:#1a5c6b}
  .sklo-chat-msg.bot{align-self:flex-start}
  .sklo-chat-msg a{color:#1a5c6b;font-weight:600;text-decoration:underline;text-underline-offset:2px}
  .sklo-chat-msg.user a{color:#e8f4f7}
  .sklo-chat-thumbs{display:flex;flex-wrap:wrap;gap:.35rem;margin-top:.45rem}
  .sklo-chat-thumbs img{width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid rgba(18,24,28,.12)}
  .sklo-chat-msg.user .sklo-chat-thumbs img{border-color:rgba(255,255,255,.35)}
  .sklo-chat-cards{display:flex;flex-direction:column;gap:.4rem;margin-top:.55rem}
  .sklo-chat-card{display:flex;gap:.55rem;align-items:center;padding:.45rem;border-radius:12px;border:1px solid rgba(18,24,28,.1);background:#f7fbfc;text-decoration:none;color:#12181c;transition:border-color .15s ease,background .15s ease}
  .sklo-chat-card:hover{border-color:#1a5c6b;background:#eef6f8}
  .sklo-chat-card img{width:52px;height:52px;object-fit:cover;border-radius:8px;flex-shrink:0;background:#dde5e9}
  .sklo-chat-card__body{min-width:0;flex:1}
  .sklo-chat-card__name{font:600 12.5px/1.3 Outfit,sans-serif;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .sklo-chat-card__meta{font:500 11px/1.35 Outfit,sans-serif;color:#5b6a75;margin-top:.15rem}
  .sklo-chat-cta{display:block;width:100%;margin-top:.55rem;border:0;border-radius:12px;padding:.7rem .9rem;cursor:pointer;font:600 13px/1.25 Outfit,sans-serif;background:#1a5c6b;color:#fff;text-align:center}
  .sklo-chat-cta:hover{background:#154a57}
  .sklo-chat-cta--ghost{background:#eef6f8;color:#1a5c6b;border:1px solid rgba(26,92,107,.28)}
  .sklo-chat-cta--ghost:hover{background:#e0eef2}
  .sklo-chat-photo-bar{display:flex;align-items:center;gap:.45rem;padding:.45rem .8rem 0;background:#fff;border-top:1px solid rgba(18,24,28,.06);flex-wrap:wrap}
  .sklo-chat-photo-btn{border:1px solid rgba(26,92,107,.28);background:#eef6f8;color:#1a5c6b;border-radius:999px;padding:.45rem .75rem;cursor:pointer;font:600 12px Outfit,sans-serif}
  .sklo-chat-photo-btn:hover{background:#e0eef2}
  .sklo-chat-photo-hint{font:500 11px/1.3 Outfit,sans-serif;color:#5b6a75}
  .sklo-chat-pending{display:flex;gap:.3rem;flex-wrap:wrap;width:100%;padding:.35rem 0 0}
  .sklo-chat-pending__item{position:relative;width:48px;height:48px}
  .sklo-chat-pending__item img{width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid rgba(18,24,28,.12)}
  .sklo-chat-pending__rm{position:absolute;top:-6px;right:-6px;width:18px;height:18px;border:0;border-radius:999px;background:#12181c;color:#fff;font:600 11px/18px Outfit,sans-serif;cursor:pointer;padding:0}
  .sklo-chat-form{display:flex;gap:.45rem;padding:.8rem;border-top:1px solid rgba(18,24,28,.08);background:#fff}
  .sklo-chat-form input[type="text"]{flex:1;border:1px solid rgba(18,24,28,.12);border-radius:999px;padding:.7rem .9rem;font:14px Outfit,sans-serif;outline:none}
  .sklo-chat-form input[type="text"]:focus{border-color:#1a5c6b;box-shadow:0 0 0 3px rgba(26,92,107,.12)}
  .sklo-chat-form button[type="submit"]{border:0;background:#1a5c6b;color:#fff;border-radius:999px;padding:.7rem 1rem;cursor:pointer;font:600 13px Outfit,sans-serif}
  `;

  const style = document.createElement('style');
  style.textContent = css;
  document.head.appendChild(style);

  const state = {
    open: false,
    choiceOpen: false,
    loading: false,
    sessionId: crypto.randomUUID(),
    pendingImages: [],
    lastPhotoImages: [],
    messages: [
      {
        role: 'assistant',
        content:
          'Ahoj, jsem Sklo asistent. Pomůžu s výběrem skleněných dveří, zaměřením nebo poptávkou. Můžeš i poslat fotku otvoru nebo dveří — najdu podobné v katalogu.',
        products: [],
        poptavka_draft: null,
        poptavka_url: null,
        images: [],
        offer_similar: false,
      },
    ],
  };

  const btn = document.createElement('button');
  btn.className = 'sklo-chat-btn';
  btn.type = 'button';
  btn.innerHTML =
    '<span class="sklo-chat-btn__line">Sklo asistent</span>' +
    '<span class="sklo-chat-btn__line sklo-chat-btn__line--wa">WhatsApp</span>';
  btn.setAttribute('aria-label', 'Sklo asistent, WhatsApp');
  btn.setAttribute('aria-expanded', 'false');
  btn.setAttribute('aria-haspopup', 'dialog');

  const choice = document.createElement('div');
  choice.className = 'sklo-chat-choice';
  choice.id = 'sklo-chat-choice';
  choice.setAttribute('aria-hidden', 'true');
  choice.innerHTML = `
    <p class="sklo-chat-choice__label">Jak se spojit</p>
    <button type="button" class="sklo-chat-choice__opt" data-act="open-chat">Napsat do chatu</button>
    <button type="button" class="sklo-chat-choice__opt sklo-chat-choice__opt--wa" data-act="whatsapp">WhatsApp</button>
    <p class="sklo-chat-choice__hint">Chat na webu, nebo rovnou WhatsApp.</p>
  `;

  const panel = document.createElement('div');
  panel.className = 'sklo-chat-panel';
  panel.id = 'sklo-chat-panel';
  panel.setAttribute('aria-hidden', 'true');
  panel.innerHTML = `
    <header>
      <strong>Sklo asistent</strong>
      <span>
        <button type="button" data-act="config">Sestavit dveře</button>
        <button type="button" data-act="reset">Reset</button>
        <button type="button" data-act="close" aria-label="Zavřít chat">Zavřít</button>
      </span>
    </header>
    <div class="sklo-chat-msgs" data-msgs></div>
    <div class="sklo-chat-photo-bar">
      <button type="button" class="sklo-chat-photo-btn" data-act="photo">Poslat fotku otvoru</button>
      <span class="sklo-chat-photo-hint" data-photo-hint>až 3 fotky · typ dveří / podobné</span>
      <div class="sklo-chat-pending" data-pending hidden></div>
      <input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-file />
    </div>
    <form class="sklo-chat-form">
      <input type="text" name="q" placeholder="Napiš otázku…" autocomplete="off" />
      <button type="submit">Odeslat</button>
    </form>
  `;

  const fileInput = panel.querySelector('[data-file]');
  const pendingBox = panel.querySelector('[data-pending]');
  const photoHint = panel.querySelector('[data-photo-hint]');

  function detectPageContext() {
    const params = new URLSearchParams(location.search);
    let kod = params.get('kod') || '';
    if (!kod) {
      const el = document.querySelector('[data-sklo-kod], [data-product-code], [data-kod]');
      if (el) {
        kod =
          el.getAttribute('data-sklo-kod') ||
          el.getAttribute('data-product-code') ||
          el.getAttribute('data-kod') ||
          '';
      }
    }
    if (!kod) {
      const m = location.pathname.match(/SklS-\d+/i);
      if (m) kod = m[0];
    }
    return { kod: kod || undefined, path: location.pathname || '/' };
  }

  function formatPrice(n) {
    const v = Number(n);
    if (!Number.isFinite(v)) return '';
    return `${Math.round(v).toLocaleString('cs-CZ')} Kč`;
  }

  function linkify(escaped) {
    return escaped.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (_, text, url) => {
      const href = String(url).replaceAll('&amp;', '&').replace(/"/g, '');
      if (!/^(https?:\/\/|\/)/i.test(href)) return text;
      return `<a href="${href}" target="_blank" rel="noopener noreferrer">${text}</a>`;
    });
  }

  function productCardsHtml(products) {
    if (!Array.isArray(products) || !products.length) return '';
    const cards = products
      .slice(0, 5)
      .map((p) => {
        const href = escapeAttr(p.url_path || '#');
        const name = escapeHtml(p.name || p.code || '');
        const code = escapeHtml(p.code || '');
        const price = formatPrice(p.price);
        const img = p.image
          ? `<img src="${escapeAttr(p.image)}" alt="" loading="lazy" width="52" height="52" />`
          : '<img alt="" width="52" height="52" />';
        return `<a class="sklo-chat-card" href="${href}" target="_blank" rel="noopener noreferrer">${img}<span class="sklo-chat-card__body"><span class="sklo-chat-card__name">${name}</span><span class="sklo-chat-card__meta">${code}${price ? ' · ' + price : ''}</span></span></a>`;
      })
      .join('');
    return `<div class="sklo-chat-cards">${cards}</div>`;
  }

  function thumbsHtml(images) {
    if (!Array.isArray(images) || !images.length) return '';
    return `<div class="sklo-chat-thumbs">${images
      .map((src) => `<img src="${escapeAttr(src)}" alt="" />`)
      .join('')}</div>`;
  }

  function poptavkaCtaHtml(msg) {
    if (!msg.poptavka_draft && !msg.poptavka_url) return '';
    return '<button type="button" class="sklo-chat-cta" data-act="poptavka">Odeslat poptávku</button>';
  }

  function similarCtaHtml(msg) {
    if (msg.role !== 'assistant' || !msg.offer_similar) return '';
    return '<button type="button" class="sklo-chat-cta sklo-chat-cta--ghost" data-act="similar">Najít podobné v katalogu</button>';
  }

  function saveDraftAndGo(draft, url) {
    if (draft) {
      try {
        sessionStorage.setItem('sklo_order_draft', JSON.stringify(draft));
      } catch {
        // Navigation should still work when storage is unavailable.
      }
    }
    window.location.href = url || '/poptavka/';
  }

  function setChoiceOpen(open) {
    state.choiceOpen = open;
    choice.classList.toggle('is-open', open);
    choice.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) {
      state.open = false;
      panel.classList.remove('is-open');
      panel.setAttribute('aria-hidden', 'true');
    }
    btn.setAttribute('aria-expanded', open || state.open ? 'true' : 'false');
    btn.setAttribute('aria-controls', open ? 'sklo-chat-choice' : 'sklo-chat-panel');
  }

  function setOpen(open) {
    state.open = open;
    panel.classList.toggle('is-open', open);
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) {
      state.choiceOpen = false;
      choice.classList.remove('is-open');
      choice.setAttribute('aria-hidden', 'true');
    }
    btn.setAttribute('aria-expanded', open || state.choiceOpen ? 'true' : 'false');
    btn.setAttribute('aria-controls', 'sklo-chat-panel');
  }

  function renderPending() {
    if (!state.pendingImages.length) {
      pendingBox.hidden = true;
      pendingBox.innerHTML = '';
      photoHint.textContent = 'až 3 fotky · typ dveří / podobné';
      return;
    }
    pendingBox.hidden = false;
    photoHint.textContent = `${state.pendingImages.length}/${MAX_PHOTOS} fotek připraveno`;
    pendingBox.innerHTML = state.pendingImages
      .map(
        (src, i) =>
          `<span class="sklo-chat-pending__item"><img src="${escapeAttr(src)}" alt="" /><button type="button" class="sklo-chat-pending__rm" data-act="rm-photo" data-i="${i}" aria-label="Odebrat">×</button></span>`
      )
      .join('');
  }

  function render() {
    const box = panel.querySelector('[data-msgs]');
    box.innerHTML = state.messages
      .map((m) => {
        const body = linkify(escapeHtml(m.content || ''));
        const thumbs = thumbsHtml(m.images);
        const cards = m.role === 'assistant' ? productCardsHtml(m.products) : '';
        const cta = m.role === 'assistant' ? poptavkaCtaHtml(m) : '';
        const similar = m.role === 'assistant' ? similarCtaHtml(m) : '';
        return `<div class="sklo-chat-msg ${m.role === 'user' ? 'user' : 'bot'}">${body}${thumbs}${cards}${cta}${similar}</div>`;
      })
      .join('');
    box.scrollTop = box.scrollHeight;
    renderPending();
  }

  function escapeHtml(s) {
    return String(s).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
  }

  function escapeAttr(s) {
    return String(s)
      .replaceAll('&', '&amp;')
      .replaceAll('"', '&quot;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;');
  }

  function requestMessages() {
    const filtered = state.messages.filter((m) => m.role === 'user' || m.role === 'assistant');
    const lastIdx = filtered.length - 1;
    return filtered
      .map((m, idx) => {
        const isLastUser = idx === lastIdx && m.role === 'user';
        if (isLastUser && Array.isArray(m.images) && m.images.length) {
          return {
            role: 'user',
            content: [
              { type: 'text', text: m.content || 'Posílám fotku otvoru.' },
              ...m.images.map((url) => ({
                type: 'image_url',
                image_url: { url },
              })),
            ],
          };
        }
        let text = m.content || '';
        if (m.role === 'user' && Array.isArray(m.images) && m.images.length) {
          text = `${text}\n[přiloženo ${m.images.length} fotografie]`.trim();
        }
        return { role: m.role, content: text };
      })
      .filter((m) => {
        if (typeof m.content === 'string') return !!m.content;
        return Array.isArray(m.content) && m.content.length > 0;
      })
      .slice(-20);
  }

  function lastUserHadPhotos() {
    for (let i = state.messages.length - 1; i >= 0; i -= 1) {
      const m = state.messages[i];
      if (m.role === 'user') {
        return Array.isArray(m.images) && m.images.length > 0;
      }
    }
    return false;
  }

  function addReply(data) {
    if (!data || !data.success) throw new Error((data && data.error) || 'Chat selhal');
    state.messages.push({
      role: 'assistant',
      content: data.reply || '',
      products: Array.isArray(data.products) ? data.products : [],
      poptavka_draft: data.poptavka_draft || null,
      poptavka_url: data.poptavka_url || null,
      images: [],
      offer_similar: lastUserHadPhotos(),
    });
    render();
  }

  function resizeImageFile(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onerror = () => reject(new Error('Nepodařilo se načíst fotku'));
      reader.onload = () => {
        const img = new Image();
        img.onerror = () => reject(new Error('Neplatný obrázek'));
        img.onload = () => {
          let { width, height } = img;
          const max = MAX_PHOTO_EDGE;
          if (width > max || height > max) {
            const scale = Math.min(max / width, max / height);
            width = Math.round(width * scale);
            height = Math.round(height * scale);
          }
          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, width, height);
          resolve(canvas.toDataURL('image/jpeg', JPEG_QUALITY));
        };
        img.src = reader.result;
      };
      reader.readAsDataURL(file);
    });
  }

  async function addFiles(fileList) {
    const files = Array.from(fileList || []).filter((f) => f && f.type.startsWith('image/'));
    for (const file of files) {
      if (state.pendingImages.length >= MAX_PHOTOS) break;
      try {
        const dataUrl = await resizeImageFile(file);
        state.pendingImages.push(dataUrl);
      } catch {
        // skip broken file
      }
    }
    renderPending();
  }

  async function sendJsonFallback(payload) {
    const res = await fetch(`${API}/api/chat`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...payload, stream: false }),
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.error || 'Chat selhal');
    addReply(data);
  }

  async function sendStream(payload) {
    const res = await fetch(`${API}/api/chat`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'text/event-stream',
      },
      body: JSON.stringify({ ...payload, stream: true }),
    });
    if (!res.ok || !res.body) throw new Error('stream fail');

    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('text/event-stream')) {
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Chat selhal');
      addReply(data);
      return;
    }

    const botMsg = {
      role: 'assistant',
      content: '',
      products: [],
      poptavka_draft: null,
      poptavka_url: null,
      images: [],
      offer_similar: lastUserHadPhotos(),
    };
    state.messages.push(botMsg);
    render();

    try {
      const reader = res.body.getReader();
      const decoder = new TextDecoder();
      let buffer = '';

      const consumeEvent = (part) => {
        const line = part.split('\n').find((item) => item.startsWith('data: '));
        if (!line) return;
        let event;
        try {
          event = JSON.parse(line.slice(6));
        } catch {
          return;
        }
        if (event.type === 'token' && event.text) {
          botMsg.content += event.text;
          render();
        } else if (event.type === 'done') {
          botMsg.content = event.reply || botMsg.content;
          botMsg.products = Array.isArray(event.products) ? event.products : [];
          botMsg.poptavka_draft = event.poptavka_draft || null;
          botMsg.poptavka_url = event.poptavka_url || null;
          render();
        } else if (event.type === 'error') {
          throw new Error(event.error || 'Chat selhal');
        }
      };

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;
        buffer += decoder.decode(value, { stream: true }).replace(/\r\n/g, '\n');
        const parts = buffer.split('\n\n');
        buffer = parts.pop() || '';
        for (const part of parts) consumeEvent(part);
      }
      buffer += decoder.decode().replace(/\r\n/g, '\n');
      if (buffer.trim()) consumeEvent(buffer);
      if (!botMsg.content) throw new Error('Prázdná stream odpověď');
    } catch (err) {
      const index = state.messages.indexOf(botMsg);
      if (index !== -1) state.messages.splice(index, 1);
      render();
      throw err;
    }
  }

  async function sendUserTurn(text, images) {
    const contentText =
      text ||
      (images && images.length
        ? 'Posílám fotku otvoru — poradíš typ dveří a checklist zaměření?'
        : '');
    if (!contentText && !(images && images.length)) return;

    state.messages.push({
      role: 'user',
      content: contentText,
      images: images || [],
      products: [],
      poptavka_draft: null,
      poptavka_url: null,
      offer_similar: false,
    });
    if (images && images.length) {
      state.lastPhotoImages = images.slice();
    }
    state.pendingImages = [];
    render();
    state.loading = true;

    const payload = {
      session_id: state.sessionId,
      messages: requestMessages(),
      page_context: detectPageContext(),
    };

    try {
      try {
        await sendStream(payload);
      } catch {
        await sendJsonFallback(payload);
      }
    } catch (err) {
      state.messages.push({
        role: 'assistant',
        content:
          err.message.includes('OPENAI') || err.message.includes('nastavený')
            ? 'Chat ještě nemá API klíč. Mezitím si sestav dveře a pošli rozměry s fotkami.'
            : `Nepodařilo se odpovědět: ${err.message}`,
        products: [],
        poptavka_draft: null,
        poptavka_url: null,
        images: [],
        offer_similar: false,
      });
    } finally {
      state.loading = false;
      render();
    }
  }

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    if (state.open) {
      setOpen(false);
      return;
    }
    setChoiceOpen(!state.choiceOpen);
  });

  choice.addEventListener('click', (e) => {
    const actEl = e.target.closest('[data-act]');
    if (!actEl) return;
    e.preventDefault();
    e.stopPropagation();
    const act = actEl.getAttribute('data-act');
    if (act === 'open-chat') {
      setChoiceOpen(false);
      setOpen(true);
      return;
    }
    if (act === 'whatsapp') {
      setChoiceOpen(false);
      window.open(WA_URL, '_blank', 'noopener,noreferrer');
    }
  });

  panel.addEventListener('click', async (e) => {
    const actEl = e.target.closest('[data-act]');
    if (!actEl) return;
    e.preventDefault();
    e.stopPropagation();
    const act = actEl.getAttribute('data-act');
    if (act === 'close') {
      setOpen(false);
      return;
    }
    if (act === 'config') {
      window.open(CONFIG_URL, '_blank');
      return;
    }
    if (act === 'photo') {
      fileInput.click();
      return;
    }
    if (act === 'rm-photo') {
      const i = Number(actEl.getAttribute('data-i'));
      if (Number.isFinite(i)) {
        state.pendingImages.splice(i, 1);
        renderPending();
      }
      return;
    }
    if (act === 'poptavka') {
      const msg = [...state.messages]
        .reverse()
        .find((item) => item.poptavka_draft || item.poptavka_url);
      if (!msg) return;
      const draft = msg.poptavka_draft;
      const url =
        msg.poptavka_url ||
        (draft && draft.code
          ? `/poptavka/?kod=${encodeURIComponent(draft.code)}`
          : '/poptavka/');
      saveDraftAndGo(draft, url);
      return;
    }
    if (act === 'similar') {
      if (state.loading) return;
      const photos = state.lastPhotoImages.length
        ? state.lastPhotoImages.slice()
        : [];
      await sendUserTurn(
        'Najdi podobné dveře v katalogu podle té fotky.',
        photos
      );
      return;
    }
    if (act === 'reset') {
      await fetch(`${API}/api/chat`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ session_id: state.sessionId, reset: true }),
      });
      state.sessionId = crypto.randomUUID();
      state.pendingImages = [];
      state.lastPhotoImages = [];
      state.messages = [
        {
          role: 'assistant',
          content:
            'Konverzace je nová. Kde budou skleněné dveře a preferuješ otočné, nebo posuvné? Můžeš i poslat fotku otvoru nebo dveří.',
          products: [],
          poptavka_draft: null,
          poptavka_url: null,
          images: [],
          offer_similar: false,
        },
      ];
      render();
    }
  });

  fileInput.addEventListener('change', async () => {
    await addFiles(fileInput.files);
    fileInput.value = '';
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (state.open) setOpen(false);
      else if (state.choiceOpen) setChoiceOpen(false);
    }
  });

  document.addEventListener('click', (e) => {
    if (!state.choiceOpen) return;
    if (choice.contains(e.target) || btn.contains(e.target)) return;
    setChoiceOpen(false);
  });

  panel.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (state.loading) return;
    const input = panel.querySelector('input[name="q"]');
    const text = input.value.trim();
    const images = state.pendingImages.slice();
    if (!text && !images.length) return;
    input.value = '';
    await sendUserTurn(text, images);
  });

  document.body.appendChild(btn);
  document.body.appendChild(choice);
  document.body.appendChild(panel);
  setOpen(false);
  render();
})();
