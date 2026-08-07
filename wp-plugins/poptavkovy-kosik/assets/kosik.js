/**
 * Poptávkový košík — frontend (vanilla JS, localStorage).
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'poptavka_kosik';
  var cfg = typeof poptavkaKosik !== 'undefined' ? poptavkaKosik : {};

  /* ---------- Storage ---------- */

  function nacistKosik() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return [];
      var data = JSON.parse(raw);
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function ulozitKosik(items) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    aktualizovatBadge();
    syncButtons();
  }

  /* ---------- Cart ops ---------- */

  function pridatDoKosiku(item) {
    var kosik = nacistKosik();
    var idx = kosik.findIndex(function (x) { return x.id === item.id; });
    if (idx >= 0) {
      kosik[idx].mnozstvi = (parseInt(kosik[idx].mnozstvi, 10) || 1) + 1;
    } else {
      kosik.push({
        id: String(item.id),
        nazev: item.nazev || '',
        kategorie: item.kategorie || '',
        cena_od: item.cena_od || item.cena || '',
        fotka: item.fotka || '',
        mnozstvi: 1,
        poznamka: ''
      });
    }
    ulozitKosik(kosik);
    zobrazitToast('✓ Přidáno do poptávky');
    renderPanel();
  }

  function odebratZKosiku(id) {
    var kosik = nacistKosik().filter(function (x) { return x.id !== id; });
    ulozitKosik(kosik);
    renderPanel();
  }

  function zmenitMnozstvi(id, delta) {
    var kosik = nacistKosik();
    var item = kosik.find(function (x) { return x.id === id; });
    if (!item) return;
    var next = (parseInt(item.mnozstvi, 10) || 1) + delta;
    if (next < 1) {
      odebratZKosiku(id);
      return;
    }
    item.mnozstvi = next;
    ulozitKosik(kosik);
    renderPanel();
  }

  function setPoznamka(id, text) {
    var kosik = nacistKosik();
    var item = kosik.find(function (x) { return x.id === id; });
    if (!item) return;
    item.poznamka = text;
    ulozitKosik(kosik);
  }

  /* ---------- UI helpers ---------- */

  function aktualizovatBadge() {
    var count = nacistKosik().reduce(function (sum, x) {
      return sum + (parseInt(x.mnozstvi, 10) || 1);
    }, 0);
    document.querySelectorAll('.poptavka-badge').forEach(function (badge) {
      if (count > 0) {
        badge.hidden = false;
        badge.textContent = String(count);
      } else {
        badge.hidden = true;
        badge.textContent = '0';
      }
    });
    var countEl = document.getElementById('poptavka-count');
    if (countEl) {
      countEl.textContent = count === 1 ? '1 položka' : count + ' položek';
    }
    var showForm = document.getElementById('poptavka-show-form');
    if (showForm) {
      showForm.disabled = count === 0;
    }
  }

  function zobrazitToast(text) {
    var existing = document.querySelector('.poptavka-toast');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.className = 'poptavka-toast';
    toast.textContent = text;
    document.body.appendChild(toast);
    requestAnimationFrame(function () {
      toast.classList.add('poptavka-toast--show');
    });
    setTimeout(function () {
      toast.classList.remove('poptavka-toast--show');
      setTimeout(function () { toast.remove(); }, 300);
    }, 2000);
  }

  function otevritPanel() {
    var overlay = document.getElementById('poptavka-overlay');
    var panel = document.getElementById('poptavka-panel');
    if (!overlay || !panel) return;
    overlay.hidden = false;
    panel.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(function () {
      overlay.classList.add('poptavka-overlay--open');
      panel.classList.add('poptavka-panel--open');
    });
    document.body.classList.add('poptavka-no-scroll');
    renderPanel();
  }

  function zavritPanel() {
    var overlay = document.getElementById('poptavka-overlay');
    var panel = document.getElementById('poptavka-panel');
    if (!overlay || !panel) return;
    overlay.classList.remove('poptavka-overlay--open');
    panel.classList.remove('poptavka-panel--open');
    panel.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('poptavka-no-scroll');
    setTimeout(function () {
      overlay.hidden = true;
    }, 300);
  }

  function escapeHtml(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderPanel() {
    var wrap = document.getElementById('poptavka-items');
    var empty = document.getElementById('poptavka-empty');
    var form = document.getElementById('poptavka-form');
    var footer = document.getElementById('poptavka-footer');
    if (!wrap) return;

    var kosik = nacistKosik();
    aktualizovatBadge();

    if (kosik.length === 0) {
      wrap.innerHTML = '';
      if (empty) empty.hidden = false;
      if (form) {
        form.hidden = true;
        form.reset();
      }
      if (footer) footer.hidden = false;
      return;
    }

    if (empty) empty.hidden = true;

    var grouped = {};
    kosik.forEach(function (item) {
      var kat = item.kategorie || 'Bez kategorie';
      if (!grouped[kat]) grouped[kat] = [];
      grouped[kat].push(item);
    });

    var html = '';
    Object.keys(grouped).forEach(function (kat) {
      html += '<div class="poptavka-group">';
      html += '<h3 class="poptavka-group__title">' + escapeHtml(kat) + '</h3>';
      grouped[kat].forEach(function (item) {
        html += '<div class="poptavka-item" data-id="' + escapeHtml(item.id) + '">';
        html += '<div class="poptavka-item__media">';
        if (item.fotka) {
          html += '<img src="' + escapeHtml(item.fotka) + '" alt="" loading="lazy">';
        } else {
          html += '<div class="poptavka-item__placeholder"></div>';
        }
        html += '</div>';
        html += '<div class="poptavka-item__info">';
        html += '<div class="poptavka-item__name">' + escapeHtml(item.nazev) + '</div>';
        if (item.cena_od) {
          html += '<div class="poptavka-item__cena">' + escapeHtml(item.cena_od) + '</div>';
        }
        html += '<div class="poptavka-item__qty">';
        html += '<button type="button" class="poptavka-qty-btn" data-qty="-1" aria-label="Snížit">−</button>';
        html += '<span class="poptavka-qty-val">' + escapeHtml(item.mnozstvi) + '</span>';
        html += '<button type="button" class="poptavka-qty-btn" data-qty="1" aria-label="Zvýšit">+</button>';
        html += '</div>';
        html += '<label class="poptavka-item__note">';
        html += '<span>Poznámka</span>';
        html += '<input type="text" class="poptavka-note-input" value="' + escapeHtml(item.poznamka || '') + '" placeholder="Volitelná poznámka">';
        html += '</label>';
        html += '</div>';
        html += '<button type="button" class="poptavka-item__remove" aria-label="Odebrat" data-remove>&times;</button>';
        html += '</div>';
      });
      html += '</div>';
    });

    wrap.innerHTML = html;
  }

  function syncButtons() {
    var kosik = nacistKosik();
    var ids = {};
    kosik.forEach(function (x) { ids[x.id] = true; });

    document.querySelectorAll('.poptavka-btn').forEach(function (btn) {
      var id = btn.getAttribute('data-id');
      if (ids[id]) {
        btn.classList.add('poptavka-btn--in-cart');
        btn.textContent = '✓ V poptávce';
      } else {
        btn.classList.remove('poptavka-btn--in-cart');
        btn.textContent = 'Přidat do poptávky';
      }
    });
  }

  function odeslatPoptavku(formEl) {
    var kosik = nacistKosik();
    var errEl = document.getElementById('poptavka-form-error');
    var okEl = document.getElementById('poptavka-form-success');
    var submitBtn = document.getElementById('poptavka-submit-btn');

    if (errEl) { errEl.hidden = true; errEl.textContent = ''; }
    if (okEl) { okEl.hidden = true; okEl.textContent = ''; }

    if (!kosik.length) {
      if (errEl) {
        errEl.hidden = false;
        errEl.textContent = 'Poptávka je prázdná.';
      }
      return;
    }

    var fd = new FormData(formEl);
    var jmeno = (fd.get('jmeno') || '').toString().trim();
    var email = (fd.get('email') || '').toString().trim();
    var telefon = (fd.get('telefon') || '').toString().trim();
    var zprava = (fd.get('zprava') || '').toString().trim();

    if (!jmeno || !email) {
      if (errEl) {
        errEl.hidden = false;
        errEl.textContent = 'Vyplňte prosím jméno a e-mail.';
      }
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Odesílám…';
    }

    var body = new FormData();
    body.append('action', 'odeslat_poptavku');
    body.append('nonce', cfg.nonce || '');
    body.append('jmeno', jmeno);
    body.append('email', email);
    body.append('telefon', telefon);
    body.append('zprava', zprava);
    body.append('zdroj_url', window.location.href);
    body.append('polozky', JSON.stringify(kosik));

    fetch(cfg.ajax_url || '/wp-admin/admin-ajax.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: body
    })
      .then(function (res) { return res.json().then(function (json) { return { ok: res.ok, json: json }; }); })
      .then(function (result) {
        if (result.json && result.json.success) {
          ulozitKosik([]);
          renderPanel();
          if (formEl) formEl.reset();
          if (okEl) {
            okEl.hidden = false;
            okEl.textContent = (result.json.data && result.json.data.message) || 'Poptávka byla úspěšně odeslána.';
          }
          var form = document.getElementById('poptavka-form');
          if (form) form.hidden = false;
          zobrazitToast('✓ Poptávka odeslána');
        } else {
          var msg = (result.json && result.json.data && result.json.data.message)
            || 'Odeslání se nezdařilo. Zkuste to prosím znovu.';
          if (errEl) {
            errEl.hidden = false;
            errEl.textContent = msg;
          }
        }
      })
      .catch(function () {
        if (errEl) {
          errEl.hidden = false;
          errEl.textContent = 'Chyba připojení. Zkuste to prosím znovu.';
        }
      })
      .finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Odeslat poptávku';
        }
      });
  }

  /* ---------- Events ---------- */

  function onReady() {
    aktualizovatBadge();
    syncButtons();

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.poptavka-btn');
      if (btn) {
        e.preventDefault();
        if (btn.classList.contains('poptavka-btn--in-cart')) {
          otevritPanel();
          return;
        }
        pridatDoKosiku({
          id: btn.getAttribute('data-id'),
          nazev: btn.getAttribute('data-nazev'),
          kategorie: btn.getAttribute('data-kategorie'),
          cena_od: btn.getAttribute('data-cena'),
          fotka: btn.getAttribute('data-fotka')
        });
        return;
      }

      if (e.target.closest('.poptavka-ikona')) {
        e.preventDefault();
        otevritPanel();
        return;
      }

      if (e.target.closest('[data-poptavka-close]') || e.target.id === 'poptavka-overlay') {
        zavritPanel();
        return;
      }

      var qtyBtn = e.target.closest('.poptavka-qty-btn');
      if (qtyBtn) {
        var itemEl = qtyBtn.closest('.poptavka-item');
        if (!itemEl) return;
        zmenitMnozstvi(itemEl.getAttribute('data-id'), parseInt(qtyBtn.getAttribute('data-qty'), 10) || 0);
        return;
      }

      if (e.target.closest('[data-remove]')) {
        var remItem = e.target.closest('.poptavka-item');
        if (remItem) odebratZKosiku(remItem.getAttribute('data-id'));
        return;
      }

      if (e.target.id === 'poptavka-show-form' || e.target.closest('#poptavka-show-form')) {
        var form = document.getElementById('poptavka-form');
        var footer = document.getElementById('poptavka-footer');
        if (form) {
          form.hidden = false;
          form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        if (footer) footer.hidden = true;
        return;
      }
    });

    document.addEventListener('change', function (e) {
      if (e.target.classList.contains('poptavka-note-input')) {
        var noteItem = e.target.closest('.poptavka-item');
        if (noteItem) setPoznamka(noteItem.getAttribute('data-id'), e.target.value);
      }
    });

    document.addEventListener('input', function (e) {
      if (e.target.classList.contains('poptavka-note-input')) {
        var noteItem = e.target.closest('.poptavka-item');
        if (noteItem) setPoznamka(noteItem.getAttribute('data-id'), e.target.value);
      }
    });

    var form = document.getElementById('poptavka-form');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        odeslatPoptavku(form);
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') zavritPanel();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', onReady);
  } else {
    onReady();
  }

  // Public API for theme / other scripts
  window.PoptavkaKosik = {
    pridatDoKosiku: pridatDoKosiku,
    odebratZKosiku: odebratZKosiku,
    zmenitMnozstvi: zmenitMnozstvi,
    nacistKosik: nacistKosik,
    ulozitKosik: ulozitKosik,
    aktualizovatBadge: aktualizovatBadge,
    zobrazitToast: zobrazitToast,
    otevritPanel: otevritPanel,
    zavritPanel: zavritPanel,
    renderPanel: renderPanel,
    odeslatPoptavku: odeslatPoptavku
  };
})();
