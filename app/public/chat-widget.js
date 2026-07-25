(() => {
  const API = window.SKLO_API_BASE || '';
  const CONFIG_URL = window.SKLO_CONFIGURATOR_URL || `${API}/public/konfigurator.html`;

  const css = `
  .sklo-chat-btn{position:fixed;right:1rem;bottom:1rem;z-index:9999;border:0;border-radius:999px;background:#0f4c5c;color:#fff;padding:.9rem 1.1rem;font:600 14px/1 system-ui,sans-serif;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.18)}
  .sklo-chat-panel{position:fixed;right:1rem;bottom:4.5rem;width:min(350px,calc(100vw - 2rem));height:500px;max-height:calc(100vh - 6rem);background:#fffcf7;border:1px solid #d8d2c8;z-index:9999;display:flex;flex-direction:column;box-shadow:0 16px 40px rgba(0,0,0,.2)}
  .sklo-chat-panel header{padding:.75rem 1rem;border-bottom:1px solid #d8d2c8;display:flex;justify-content:space-between;align-items:center;gap:.5rem}
  .sklo-chat-panel header strong{font:600 14px system-ui}
  .sklo-chat-panel header button{border:0;background:transparent;cursor:pointer;font-size:12px;color:#0f4c5c}
  .sklo-chat-msgs{flex:1;overflow:auto;padding:1rem;display:flex;flex-direction:column;gap:.6rem;background:linear-gradient(#f7f4ee,#fffcf7)}
  .sklo-chat-msg{max-width:90%;padding:.65rem .75rem;font:14px/1.4 system-ui;border:1px solid #d8d2c8;background:#fff}
  .sklo-chat-msg.user{align-self:flex-end;background:#0f4c5c;color:#fff;border-color:#0f4c5c}
  .sklo-chat-msg.bot{align-self:flex-start}
  .sklo-chat-form{display:flex;gap:.4rem;padding:.75rem;border-top:1px solid #d8d2c8}
  .sklo-chat-form input{flex:1;border:1px solid #d8d2c8;padding:.6rem;font:14px system-ui}
  .sklo-chat-form button{border:0;background:#0f4c5c;color:#fff;padding:.6rem .8rem;cursor:pointer;font:600 13px system-ui}
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
  panel.hidden = true;
  panel.innerHTML = `
    <header>
      <strong>Sklo asistent</strong>
      <span>
        <button type="button" data-act="config">Konfigurátor</button>
        <button type="button" data-act="reset">Reset</button>
        <button type="button" data-act="close">Zavřít</button>
      </span>
    </header>
    <div class="sklo-chat-msgs" data-msgs></div>
    <form class="sklo-chat-form">
      <input name="q" placeholder="Napiš otázku…" autocomplete="off" />
      <button type="submit">Odeslat</button>
    </form>
  `;

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

  btn.addEventListener('click', () => {
    state.open = !state.open;
    panel.hidden = !state.open;
  });

  panel.addEventListener('click', async (e) => {
    const act = e.target.getAttribute('data-act');
    if (act === 'close') {
      state.open = false;
      panel.hidden = true;
    }
    if (act === 'config') window.open(CONFIG_URL, '_blank');
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
          ? 'Chat ještě nemá API klíč. Mezitím můžeš jít do konfigurátoru a poslat rozměry s fotkami.'
          : `Nepodařilo se odpovědět: ${err.message}`,
      });
    } finally {
      state.loading = false;
      render();
    }
  });

  document.body.appendChild(btn);
  document.body.appendChild(panel);
  render();
})();
