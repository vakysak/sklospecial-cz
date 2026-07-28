(() => {
  const API = window.SKLO_API_BASE || '';
  const CONFIG_URL = window.SKLO_CONFIGURATOR_URL || `${API}/public/konfigurator.html`;

  const css = `
  @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@500;600&display=swap');
  .sklo-chat-btn{position:fixed;right:1.1rem;bottom:1.1rem;z-index:9999;border:0;border-radius:999px;background:#1a5c6b;color:#fff;padding:.95rem 1.2rem;font:600 14px/1 Outfit,sans-serif;cursor:pointer;box-shadow:0 14px 34px rgba(26,92,107,.28);transition:transform .15s ease}
  .sklo-chat-btn:hover{transform:translateY(-1px)}
  .sklo-chat-panel{position:fixed;right:1.1rem;bottom:4.7rem;width:min(360px,calc(100vw - 2rem));height:min(520px,calc(100vh - 6rem));background:rgba(255,255,255,.92);backdrop-filter:blur(14px);border:1px solid rgba(18,24,28,.1);border-radius:18px;z-index:9999;display:none;flex-direction:column;box-shadow:0 22px 50px rgba(18,40,55,.16);overflow:hidden;font-family:Outfit,sans-serif}
  .sklo-chat-panel.is-open{display:flex}
  .sklo-chat-panel header{padding:.85rem 1rem;border-bottom:1px solid rgba(18,24,28,.08);display:flex;justify-content:space-between;align-items:center;gap:.5rem;background:linear-gradient(180deg,#f7fbfc,#fff)}
  .sklo-chat-panel header strong{font:600 14px Outfit,sans-serif;color:#12181c}
  .sklo-chat-panel header button{border:0;background:transparent;cursor:pointer;font:500 12px Outfit,sans-serif;color:#1a5c6b;padding:.2rem .35rem}
  .sklo-chat-panel header button[data-act="close"]{color:#5b6a75;font-weight:600}
  .sklo-chat-msgs{flex:1;overflow:auto;padding:1rem;display:flex;flex-direction:column;gap:.55rem;background:linear-gradient(180deg,#f3f7f9,#fbfcfd)}
  .sklo-chat-msg{max-width:88%;padding:.7rem .8rem;font:14px/1.45 Outfit,sans-serif;border-radius:14px;border:1px solid rgba(18,24,28,.08);background:#fff;color:#12181c}
  .sklo-chat-msg.user{align-self:flex-end;background:#1a5c6b;color:#fff;border-color:#1a5c6b}
  .sklo-chat-msg.bot{align-self:flex-start}
  .sklo-chat-form{display:flex;gap:.45rem;padding:.8rem;border-top:1px solid rgba(18,24,28,.08);background:#fff}
  .sklo-chat-form input{flex:1;border:1px solid rgba(18,24,28,.12);border-radius:999px;padding:.7rem .9rem;font:14px Outfit,sans-serif;outline:none}
  .sklo-chat-form input:focus{border-color:#1a5c6b;box-shadow:0 0 0 3px rgba(26,92,107,.12)}
  .sklo-chat-form button{border:0;background:#1a5c6b;color:#fff;border-radius:999px;padding:.7rem 1rem;cursor:pointer;font:600 13px Outfit,sans-serif}
  `;

  const style = document.createElement('style');
  style.textContent = css;
  document.head.appendChild(style);

  const state = {
    open: false,
    loading: false,
    sessionId: crypto.randomUUID(),
    messages: [
      { role: 'assistant', content: 'Ahoj, jsem Sklo asistent. Pomůžu s výběrem skleněných dveří, zaměřením nebo poptávkou.' },
    ],
  };

  const btn = document.createElement('button');
  btn.className = 'sklo-chat-btn';
  btn.type = 'button';
  btn.textContent = 'Sklo asistent';

  const panel = document.createElement('div');
  panel.className = 'sklo-chat-panel';
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
    <form class="sklo-chat-form">
      <input name="q" placeholder="Napiš otázku…" autocomplete="off" />
      <button type="submit">Odeslat</button>
    </form>
  `;

  function setOpen(open) {
    state.open = open;
    panel.classList.toggle('is-open', open);
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function render() {
    const box = panel.querySelector('[data-msgs]');
    box.innerHTML = state.messages
      .map((m) => `<div class="sklo-chat-msg ${m.role === 'user' ? 'user' : 'bot'}">${escapeHtml(m.content)}</div>`)
      .join('');
    box.scrollTop = box.scrollHeight;
  }

  function escapeHtml(s) {
    return String(s)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;');
  }

  btn.setAttribute('aria-expanded', 'false');
  btn.setAttribute('aria-controls', 'sklo-chat-panel');
  panel.id = 'sklo-chat-panel';

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    setOpen(!state.open);
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
    if (act === 'reset') {
      await fetch(`${API}/api/chat`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ session_id: state.sessionId, reset: true }),
      });
      state.sessionId = crypto.randomUUID();
      state.messages = [
        { role: 'assistant', content: 'Konverzace je nová. Kde budou skleněné dveře a preferuješ otočné, nebo posuvné?' },
      ];
      render();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && state.open) setOpen(false);
  });

  panel.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (state.loading) return;
    const input = panel.querySelector('input[name="q"]');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    state.messages.push({ role: 'user', content: text });
    render();
    state.loading = true;
    try {
      const res = await fetch(`${API}/api/chat`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          session_id: state.sessionId,
          messages: state.messages.filter((m) => m.role === 'user' || m.role === 'assistant').slice(-20),
        }),
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.error || 'Chat selhal');
      state.messages.push({ role: 'assistant', content: data.reply });
    } catch (err) {
      state.messages.push({
        role: 'assistant',
        content: err.message.includes('OPENAI') || err.message.includes('nastavený')
          ? 'Chat ještě nemá API klíč. Mezitím si sestav dveře a pošli rozměry s fotkami.'
          : `Nepodařilo se odpovědět: ${err.message}`,
      });
    } finally {
      state.loading = false;
      render();
    }
  });

  document.body.appendChild(btn);
  document.body.appendChild(panel);
  setOpen(false);
  render();
})();
