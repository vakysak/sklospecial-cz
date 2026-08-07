(() => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!open));
      nav.classList.toggle('is-open', !open);
    });

    nav.querySelectorAll('a').forEach((a) => {
      a.addEventListener('click', () => {
        toggle.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
      });
    });
  }
})();

(() => {
  const lightbox = document.querySelector('[data-lightbox]');
  if (!lightbox) return;

  const img = lightbox.querySelector('[data-lightbox-img]');
  const btnClose = lightbox.querySelector('[data-lightbox-close]');
  const btnPrev = lightbox.querySelector('[data-lightbox-prev]');
  const btnNext = lightbox.querySelector('[data-lightbox-next]');
  let index = 0;
  let lastFocus = null;

  const visibleItems = () =>
    Array.from(document.querySelectorAll('[data-gallery] [data-lightbox-open]')).filter(
      (el) => !el.closest('[hidden]')
    );

  const show = (i) => {
    const items = visibleItems();
    if (!items.length) return;
    index = (i + items.length) % items.length;
    const el = items[index];
    img.src = el.getAttribute('data-src') || '';
    img.alt = el.getAttribute('data-alt') || '';
  };

  const open = (i) => {
    const items = visibleItems();
    if (!items.length) return;
    lastFocus = document.activeElement;
    show(i);
    lightbox.hidden = false;
    requestAnimationFrame(() => lightbox.classList.add('is-open'));
    document.documentElement.style.overflow = 'hidden';
    btnClose.focus();
  };

  const close = () => {
    lightbox.classList.remove('is-open');
    document.documentElement.style.overflow = '';
    const finish = () => {
      lightbox.hidden = true;
      img.removeAttribute('src');
      if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    };
    window.setTimeout(finish, 220);
  };

  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-lightbox-open]');
    if (!el || !el.closest('[data-gallery]')) return;
    if (el.closest('[hidden]')) return;
    const items = visibleItems();
    const i = items.indexOf(el);
    if (i >= 0) open(i);
  });

  btnClose.addEventListener('click', close);
  btnPrev.addEventListener('click', () => show(index - 1));
  btnNext.addEventListener('click', () => show(index + 1));

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) close();
  });

  document.addEventListener('keydown', (e) => {
    if (lightbox.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') show(index - 1);
    if (e.key === 'ArrowRight') show(index + 1);
  });
})();

(() => {
  document.querySelectorAll('[data-sklo-produkty]').forEach((root) => {
    const btn = root.querySelector('[data-sklo-produkty-more]');
    if (!btn) return;
    const hydrate = (el) => {
      el.querySelectorAll('img[data-src]').forEach((img) => {
        const src = img.getAttribute('data-src');
        if (!src) return;
        img.setAttribute('src', src);
        img.removeAttribute('data-src');
      });
    };
    btn.addEventListener('click', () => {
      root.querySelectorAll('.sklo-produkty__item.is-collapsed').forEach((el) => {
        if (el.classList.contains('is-filtered-out')) return;
        el.hidden = false;
        el.classList.remove('is-collapsed');
        hydrate(el);
      });
      btn.hidden = true;
    });
  });
})();

(() => {
  const page = document.querySelector('.sklo-katalog--filtered');
  if (!page) return;

  const chips = Array.from(page.querySelectorAll('[data-cat-nav] [data-cat-filter]'));
  const cards = Array.from(page.querySelectorAll('.sklo-cat-card[data-cat-filter]'));
  const gridRoot = page.querySelector('[data-sklo-produkty]');
  const items = gridRoot
    ? Array.from(gridRoot.querySelectorAll('.sklo-produkty__item[data-category]'))
    : [];
  const countEl = gridRoot ? gridRoot.querySelector('[data-sklo-produkty-count]') : null;
  const moreBtn = gridRoot ? gridRoot.querySelector('[data-sklo-produkty-more]') : null;
  const panels = Array.from(page.querySelectorAll('[data-cat-panel]'));
  const catSections = Array.from(page.querySelectorAll('[data-cat-section]'));
  const initial = gridRoot
    ? Number.parseInt(gridRoot.getAttribute('data-initial') || '24', 10) || 24
    : 12;

  let active = '';
  let expanded = moreBtn ? moreBtn.hidden : true;

  const setPressed = (nodes, value) => {
    nodes.forEach((el) => {
      const match = (el.getAttribute('data-cat-filter') || '') === value;
      el.classList.toggle('is-active', match);
      el.setAttribute('aria-pressed', match ? 'true' : 'false');
    });
  };

  const apply = (value, { scroll } = { scroll: false }) => {
    active = value;
    setPressed(chips, value);
    setPressed(cards, value);

    panels.forEach((panel) => {
      const show = value !== '' && panel.getAttribute('data-cat-panel') === value;
      panel.hidden = !show;
    });

    let visibleCount = 0;
    let shown = 0;
    items.forEach((el) => {
      const cat = el.getAttribute('data-category') || '';
      const match = value === '' || cat === value;
      el.classList.toggle('is-filtered-out', !match);
      if (!match) {
        el.hidden = true;
        return;
      }
      visibleCount += 1;
      const reveal = expanded || value !== '' || shown < initial;
      if (reveal) {
        el.hidden = false;
        el.classList.remove('is-collapsed');
        el.querySelectorAll('img[data-src]').forEach((img) => {
          const src = img.getAttribute('data-src');
          if (!src) return;
          img.setAttribute('src', src);
          img.removeAttribute('data-src');
        });
        shown += 1;
      } else {
        el.hidden = true;
        el.classList.add('is-collapsed');
      }
    });

    catSections.forEach((sec) => {
      const sid = sec.getAttribute('data-cat-section') || '';
      if (value !== '' && sid !== value) {
        sec.hidden = true;
        return;
      }
      const hasVisible = Array.from(
        sec.querySelectorAll('.sklo-produkty__item[data-category]')
      ).some(
        (el) =>
          !el.classList.contains('is-filtered-out') && !el.classList.contains('is-collapsed')
      );
      sec.hidden = !hasVisible;
    });

    if (countEl) countEl.textContent = String(visibleCount);

    if (moreBtn) {
      const remaining = items.filter(
        (el) =>
          !el.classList.contains('is-filtered-out') && el.classList.contains('is-collapsed')
      ).length;
      if (value !== '' || expanded || remaining === 0) {
        moreBtn.hidden = true;
      } else {
        moreBtn.hidden = false;
        moreBtn.textContent = `Zobrazit další (${remaining})`;
      }
    }

    if (scroll && gridRoot) {
      const top = gridRoot.getBoundingClientRect().top + window.scrollY - 96;
      window.scrollTo({ top, behavior: 'smooth' });
    }
  };

  const onFilterClick = (e) => {
    const btn = e.currentTarget;
    const value = btn.getAttribute('data-cat-filter') || '';
    // Toggle off when re-clicking the same category card
    const next = btn.classList.contains('sklo-cat-card') && value === active ? '' : value;
    apply(next, { scroll: true });
  };

  chips.forEach((el) => el.addEventListener('click', onFilterClick));
  cards.forEach((el) => el.addEventListener('click', onFilterClick));

  if (moreBtn) {
    moreBtn.addEventListener('click', () => {
      expanded = true;
      apply(active);
    });
  }

  // Honor hash deep-links (#diy etc.)
  const hash = (window.location.hash || '').replace(/^#/, '');
  if (hash && items.some((el) => el.getAttribute('data-category') === hash)) {
    apply(hash, { scroll: true });
  } else if (catSections.length) {
    apply('');
  }
})();

(() => {
  const root = document.querySelector('[data-sklo-pdetail]');
  if (!root) return;
  const mainImg = root.querySelector('[data-sklo-pdetail-main]');
  const mainBtn = root.querySelector('.sklo-pdetail__main[data-lightbox-open]');
  const thumbs = Array.from(root.querySelectorAll('[data-sklo-pdetail-thumb]'));
  if (mainImg && thumbs.length) {
    thumbs.forEach((thumb) => {
      thumb.addEventListener('click', (e) => {
        const src = thumb.getAttribute('data-src') || '';
        if (!src) return;
        e.preventDefault();
        e.stopPropagation();
        mainImg.src = src;
        if (mainBtn) mainBtn.setAttribute('data-src', src);
        thumbs.forEach((t) => t.classList.toggle('is-active', t === thumb));
      });
    });
  }

  const priceEl = root.querySelector('[data-sklo-orient-price]');
  const baseNode = root.querySelector('[data-sklo-base-price]');
  const selects = Array.from(root.querySelectorAll('[data-sklo-option-select]'));
  const orderBtn = root.querySelector('[data-sklo-order-selected]');
  const errEl = root.querySelector('[data-sklo-options-err]');
  const base =
    parseInt(
      (baseNode && baseNode.getAttribute('data-sklo-base-price')) ||
        root.getAttribute('data-base-price') ||
        '0',
      10
    ) || 0;

  const formatKc = (n) =>
    new Intl.NumberFormat('cs-CZ', { maximumFractionDigits: 0 }).format(n).replace(/\s/g, '\u00a0') +
    ' Kč';

  const currentTotal = () => {
    let sum = base;
    selects.forEach((sel) => {
      const opt = sel.options[sel.selectedIndex];
      const sur = parseInt((opt && opt.getAttribute('data-surcharge')) || '0', 10) || 0;
      sum += sur;
    });
    return sum;
  };

  const update = () => {
    if (priceEl) priceEl.textContent = formatKc(currentTotal());
    if (errEl) errEl.hidden = true;
    selects.forEach((sel) => sel.classList.remove('is-invalid'));
  };

  const selectionsValid = () => {
    let ok = true;
    selects.forEach((sel) => {
      if (!sel.value) {
        ok = false;
        sel.classList.add('is-invalid');
      }
    });
    return ok;
  };

  const buildOrderDraft = () => {
    const code = root.getAttribute('data-code') || '';
    const name = root.getAttribute('data-name') || '';
    const image = root.getAttribute('data-image') || '';
    const selections = [];
    let surcharges = 0;
    selects.forEach((sel) => {
      const label = sel.getAttribute('data-opt-label') || '';
      const key = sel.getAttribute('data-opt-key') || '';
      const opt = sel.options[sel.selectedIndex];
      const text = (opt && opt.value) || '';
      const sur = parseInt((opt && opt.getAttribute('data-surcharge')) || '0', 10) || 0;
      if (label && text) {
        const row = { label, value: text, surcharge: sur };
        if (key) row.key = key;
        selections.push(row);
        surcharges += sur;
      }
    });
    const unitTotal = currentTotal();
    return {
      v: 1,
      code,
      name,
      image,
      basePrice: base,
      surcharges,
      unitTotal,
      qty: 1,
      selections,
      createdAt: new Date().toISOString(),
    };
  };

  const saveAndGoPoptavka = () => {
    const draft = buildOrderDraft();
    try {
      sessionStorage.setItem('sklo_order_draft', JSON.stringify(draft));
    } catch (e) {
      /* ignore quota */
    }
    const poptavka =
      root.getAttribute('data-poptavka') ||
      new URL('/poptavka/', window.location.origin).href;
    const url = new URL(poptavka, window.location.origin);
    if (draft.code) url.searchParams.set('kod', draft.code);
    window.location.href = url.toString();
  };

  if (priceEl) {
    selects.forEach((sel) => sel.addEventListener('change', update));
    update();
  }

  if (orderBtn) {
    orderBtn.addEventListener('click', () => {
      if (selects.length && !selectionsValid()) {
        if (errEl) errEl.hidden = false;
        const firstBad = selects.find((s) => !s.value);
        if (firstBad) firstBad.focus();
        return;
      }
      saveAndGoPoptavka();
    });
  }
})();

(() => {
  const root = document.querySelector('[data-sklo-poptavka]');
  if (!root) return;

  const STORAGE_KEY = 'sklo_order_draft';
  const summaryEl = root.querySelector('[data-poptavka-summary]');
  const form = root.querySelector('[data-poptavka-form]');
  const orderJsonInput = root.querySelector('[data-poptavka-order-json]');
  const errEl = root.querySelector('[data-poptavka-err]');
  const okEl = root.querySelector('[data-poptavka-ok]');
  const submitBtn = root.querySelector('[data-poptavka-submit]');
  const emptyHint = root.querySelector('[data-poptavka-empty-hint]');
  const codeField = root.querySelector('[data-poptavka-code-field]');
  const codeInput = form && form.querySelector('input[name="kod_produktu"]');
  const steps = Array.from(root.querySelectorAll('.sklo-poptavka__steps li'));
  const restUrl = root.getAttribute('data-rest') || '';

  const formatKc = (n) =>
    new Intl.NumberFormat('cs-CZ', { maximumFractionDigits: 0 }).format(n).replace(/\s/g, '\u00a0') +
    ' Kč';

  const escapeHtml = (s) =>
    String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  const escapeAttr = (s) => escapeHtml(s).replace(/'/g, '&#39;');

  const loadDraft = () => {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      if (!data || typeof data !== 'object' || !data.code) return null;
      return data;
    } catch (e) {
      return null;
    }
  };

  let draft = loadDraft();
  const hasProduct = Boolean(draft && draft.code);

  if (hasProduct) {
    draft.qty = Math.max(1, parseInt(String(draft.qty || 1), 10) || 1);
    if (emptyHint) emptyHint.hidden = true;
    if (codeField) codeField.hidden = true;
  } else {
    draft = {
      v: 1,
      code: '',
      name: 'Obecná poptávka',
      image: '',
      basePrice: 0,
      surcharges: 0,
      unitTotal: 0,
      qty: 1,
      selections: [],
      general: true,
      createdAt: new Date().toISOString(),
    };
    if (emptyHint) emptyHint.hidden = false;
    if (codeField) codeField.hidden = false;
    const params = new URLSearchParams(window.location.search);
    const kod = (params.get('kod') || '').trim();
    if (kod && codeInput) codeInput.value = kod;
  }

  const setActiveStep = (n) => {
    steps.forEach((li, i) => {
      li.classList.toggle('is-active', i + 1 === n);
      li.classList.toggle('is-done', i + 1 < n);
    });
  };

  const syncOrderJson = () => {
    if (orderJsonInput) orderJsonInput.value = JSON.stringify(draft);
  };

  const renderGeneralSummary = () => {
    if (!summaryEl) return;
    summaryEl.innerHTML =
      '<div class="sklo-poptavka__card">' +
      '<h2>Obecná poptávka</h2>' +
      '<p class="sklo-poptavka__note">Bez konkrétního produktu z katalogu. Doplň detaily v poznámce — ozveme se s nabídkou.</p>' +
      '</div>';
    syncOrderJson();
  };

  const renderProductSummary = () => {
    if (!summaryEl || !draft) return;
    const unit = parseInt(String(draft.unitTotal || draft.basePrice || 0), 10) || 0;
    const total = unit * (parseInt(String(draft.qty || 1), 10) || 1);
    const sels = Array.isArray(draft.selections) ? draft.selections : [];
    const lines = sels
      .map((s) => {
        const sur =
          s.surcharge > 0
            ? ' <span class="sklo-poptavka__sur">(+ ' + formatKc(s.surcharge) + ')</span>'
            : '';
        return (
          '<li><span class="sklo-poptavka__sel-label">' +
          escapeHtml(s.label) +
          '</span> <span class="sklo-poptavka__sel-val">' +
          escapeHtml(s.value) +
          '</span>' +
          sur +
          '</li>'
        );
      })
      .join('');

    const img = draft.image
      ? '<img class="sklo-poptavka__thumb" src="' +
        escapeAttr(draft.image) +
        '" alt="" width="96" height="96" loading="lazy">'
      : '<div class="sklo-poptavka__thumb sklo-poptavka__thumb--empty" aria-hidden="true"></div>';

    summaryEl.innerHTML =
      '<div class="sklo-poptavka__card">' +
      '<h2>Shrnutí</h2>' +
      '<div class="sklo-poptavka__product">' +
      img +
      '<div class="sklo-poptavka__product-text">' +
      '<p class="sklo-poptavka__name">' +
      escapeHtml(draft.name || '') +
      '</p>' +
      '<p class="sklo-poptavka__code">' +
      escapeHtml(draft.code || '') +
      '</p>' +
      '</div></div>' +
      (lines
        ? '<ul class="sklo-poptavka__sels">' + lines + '</ul>'
        : '<p class="sklo-poptavka__no-opts">Bez variant</p>') +
      '<dl class="sklo-poptavka__prices">' +
      '<div><dt>Základ</dt><dd>' +
      formatKc(parseInt(String(draft.basePrice || 0), 10) || 0) +
      '</dd></div>' +
      (draft.surcharges > 0
        ? '<div><dt>Doplatky</dt><dd>+ ' + formatKc(draft.surcharges) + '</dd></div>'
        : '') +
      '<div><dt>Cena / ks</dt><dd>' +
      formatKc(unit) +
      '</dd></div>' +
      '<div class="sklo-poptavka__total"><dt>Orientační celkem</dt><dd data-poptavka-total>' +
      formatKc(total) +
      '</dd></div>' +
      '</dl>' +
      '<label class="sklo-field sklo-poptavka__qty">' +
      '<span class="sklo-field__label">Počet kusů</span>' +
      '<input class="sklo-field__input" type="number" min="1" max="99" value="' +
      String(draft.qty) +
      '" data-poptavka-qty>' +
      '</label>' +
      '<p class="sklo-poptavka__note">Finální nabídka podle rozměrů, dopravy a dostupnosti.</p>' +
      '</div>';

    const qtyInput = summaryEl.querySelector('[data-poptavka-qty]');
    if (qtyInput) {
      qtyInput.addEventListener('change', () => {
        let q = parseInt(qtyInput.value, 10) || 1;
        if (q < 1) q = 1;
        if (q > 99) q = 99;
        draft.qty = q;
        qtyInput.value = String(q);
        try {
          sessionStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
        } catch (e) {}
        const totalEl = summaryEl.querySelector('[data-poptavka-total]');
        if (totalEl) totalEl.textContent = formatKc(unit * q);
        syncOrderJson();
      });
    }
    syncOrderJson();
  };

  if (hasProduct) {
    renderProductSummary();
  } else {
    renderGeneralSummary();
  }
  setActiveStep(1);

  if (!form) return;

  const zarukaCb = form.querySelector('[data-poptavka-zaruka]');
  const zarukaAmountEl = form.querySelector('[data-poptavka-zaruka-amount]');
  const warrantyFromDraft = () => {
    const sels = Array.isArray(draft.selections) ? draft.selections : [];
    return sels.find((s) => s.key === 'prodlouzena_zaruka' || /prodloužená záruka/i.test(String(s.label || '')));
  };
  const warrantyPriceForBase = () => {
    const base = parseInt(String(draft.basePrice || 0), 10) || 0;
    return base > 0 ? Math.round(base * 0.1) : 0;
  };
  const syncZarukaUi = () => {
    const w = warrantyFromDraft();
    const price = (w && w.surcharge > 0) ? w.surcharge : warrantyPriceForBase();
    if (zarukaAmountEl) {
      zarukaAmountEl.textContent = price > 0 ? ' — ' + formatKc(price) : '';
    }
    if (zarukaCb && hasProduct && w) {
      zarukaCb.checked = String(w.value || '').toLowerCase() === 'ano';
    }
  };
  const applyZarukaToDraft = (on) => {
    if (!hasProduct || !draft) return;
    const price = warrantyPriceForBase();
    let sels = Array.isArray(draft.selections) ? draft.selections.slice() : [];
    sels = sels.filter((s) => s.key !== 'prodlouzena_zaruka' && !/prodloužená záruka/i.test(String(s.label || '')));
    if (on && price > 0) {
      sels.push({
        key: 'prodlouzena_zaruka',
        label: 'Prodloužená záruka (+1 rok)',
        value: 'Ano',
        surcharge: price,
      });
    } else if (!on) {
      sels.push({
        key: 'prodlouzena_zaruka',
        label: 'Prodloužená záruka (+1 rok)',
        value: 'Ne',
        surcharge: 0,
      });
    }
    draft.selections = sels;
    draft.surcharges = sels.reduce((acc, s) => acc + (parseInt(String(s.surcharge || 0), 10) || 0), 0);
    draft.unitTotal = (parseInt(String(draft.basePrice || 0), 10) || 0) + draft.surcharges;
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
    } catch (e) {}
    renderProductSummary();
    syncOrderJson();
  };
  syncZarukaUi();
  if (zarukaCb) {
    zarukaCb.addEventListener('change', () => {
      applyZarukaToDraft(!!zarukaCb.checked);
      syncZarukaUi();
    });
  }

  const sectionEls = Array.from(form.querySelectorAll('[data-poptavka-section]'));
  const bumpStepFromFocus = (target) => {
    const sec = target && target.closest ? target.closest('[data-poptavka-section]') : null;
    if (!sec) return;
    const n = parseInt(sec.getAttribute('data-poptavka-section') || '1', 10) || 1;
    setActiveStep(n);
  };
  form.addEventListener('focusin', (e) => bumpStepFromFocus(e.target));
  form.addEventListener(
    'scroll',
    () => {
      /* no-op; steps update on focus */
    },
    { passive: true }
  );

  if ('IntersectionObserver' in window && sectionEls.length) {
    const io = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((en) => en.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (!visible) return;
        const n = parseInt(visible.target.getAttribute('data-poptavka-section') || '1', 10) || 1;
        setActiveStep(n);
      },
      { root: null, threshold: [0.35, 0.55], rootMargin: '-15% 0px -40% 0px' }
    );
    sectionEls.forEach((el) => io.observe(el));
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (errEl) {
      errEl.hidden = true;
      errEl.textContent = '';
    }
    if (okEl) okEl.hidden = true;
    setActiveStep(4);

    const fd = new FormData(form);
    if (!hasProduct) {
      const kod = String(fd.get('kod_produktu') || '').trim();
      draft.code = kod;
      draft.name = kod ? 'Obecná poptávka (' + kod + ')' : 'Obecná poptávka';
    }
    syncOrderJson();

    const gdprEl = form.querySelector('[name="gdpr_souhlas"]');
    const payload = {
      jmeno: String(fd.get('jmeno') || '').trim(),
      email: String(fd.get('email') || '').trim(),
      telefon: String(fd.get('telefon') || '').trim(),
      adresa: String(fd.get('adresa') || '').trim(),
      doprava: String(fd.get('doprava') || 'ne'),
      montaz: String(fd.get('montaz') || 'ne'),
      zamereni: form.querySelector('[name="zamereni"]')
        ? !!form.querySelector('[name="zamereni"]').checked
        : false,
      prodlouzena_zaruka: form.querySelector('[name="prodlouzena_zaruka"]')
        ? !!form.querySelector('[name="prodlouzena_zaruka"]').checked
        : false,
      gdpr_souhlas: gdprEl ? !!gdprEl.checked : false,
      poznamka: String(fd.get('poznamka') || '').trim(),
      website: String(fd.get('website') || ''),
      order: draft,
    };

    const turnstileInput =
      form.querySelector('[name="cf-turnstile-response"]') ||
      form.querySelector('textarea[name="cf-turnstile-response"]') ||
      form.querySelector('input[name="cf-turnstile-response"]');
    if (turnstileInput && turnstileInput.value) {
      payload.cf_turnstile_response = String(turnstileInput.value);
    } else if (window.turnstile && form.querySelector('.cf-turnstile')) {
      try {
        const widget = form.querySelector('.cf-turnstile');
        const token = window.turnstile.getResponse(widget);
        if (token) payload.cf_turnstile_response = token;
      } catch (err) {}
    }

    if (payload.jmeno.length < 2 || !payload.email || payload.telefon.length < 5) {
      if (errEl) {
        errEl.textContent = 'Vyplň jméno, e-mail a telefon.';
        errEl.hidden = false;
      }
      setActiveStep(2);
      const firstBad = form.querySelector('[name="jmeno"], [name="email"], [name="telefon"]');
      if (firstBad) firstBad.focus();
      return;
    }

    if (!payload.gdpr_souhlas) {
      if (errEl) {
        errEl.textContent = 'Potřebujeme souhlas se zpracováním osobních údajů.';
        errEl.hidden = false;
      }
      setActiveStep(4);
      if (gdprEl) gdprEl.focus();
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Odesílám…';
    }

    try {
      const res = await fetch(restUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.success) {
        throw new Error((data && data.message) || 'Odeslání se nepovedlo.');
      }
      try {
        sessionStorage.removeItem(STORAGE_KEY);
      } catch (err) {}
      if (okEl) okEl.hidden = false;
      form.querySelectorAll('input, textarea, select, button').forEach((el) => {
        if (el !== submitBtn) el.disabled = true;
      });
      if (submitBtn) submitBtn.textContent = 'Odesláno';
    } catch (err) {
      const subject = encodeURIComponent(
        'Poptávka ' + (draft.code || 'obecná') + ' — ' + payload.jmeno
      );
      const body = encodeURIComponent(
        [
          'Jméno: ' + payload.jmeno,
          'E-mail: ' + payload.email,
          'Telefon: ' + payload.telefon,
          'Adresa: ' + payload.adresa,
          'Doprava: ' + payload.doprava,
          'Montáž: ' + payload.montaz,
          'Zaměření: ' + (payload.zamereni ? 'ano' : 'ne'),
          '',
          'Produkt: ' + (draft.name || '') + (draft.code ? ' (' + draft.code + ')' : ''),
          'Počet: ' + (draft.qty || 1),
          draft.unitTotal
            ? 'Orientační cena: ' + formatKc((draft.unitTotal || 0) * (draft.qty || 1))
            : '',
          'Volby:',
          ...((draft.selections || []).length
            ? (draft.selections || []).map((s) => '- ' + s.label + ': ' + s.value)
            : ['—']),
          '',
          'Poznámka: ' + payload.poznamka,
        ]
          .filter(Boolean)
          .join('\n')
      );
      if (errEl) {
        errEl.innerHTML =
          'Nepodařilo se odeslat přes server. <a href="mailto:info@sklospecial.eu?subject=' +
          subject +
          '&body=' +
          body +
          '">Otevři e-mailový klient</a> a pošli poptávku ručně.';
        errEl.hidden = false;
      }
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Odeslat poptávku';
      }
    }
  });
})();

(() => {
  const root = document.querySelector('[data-sklo-similar]');
  if (!root) return;
  const code = root.getAttribute('data-code') || '';
  const api = (root.getAttribute('data-api') || '').replace(/\/$/, '');
  const grid = root.querySelector('[data-sklo-similar-grid]');
  if (!code || !api || !grid) return;

  const formatKc = (n) => {
    const v = Number(n);
    if (!Number.isFinite(v) || v <= 0) return '';
    return 'od ' + Math.round(v).toLocaleString('cs-CZ') + ' Kč';
  };

  const esc = (s) =>
    String(s)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;');

  fetch(api + '/api/produkty/' + encodeURIComponent(code) + '/similar', {
    headers: { Accept: 'application/json' },
  })
    .then((r) => r.json())
    .then((data) => {
      const items = (data && data.success && Array.isArray(data.items) && data.items) || [];
      if (!items.length) return;
      grid.innerHTML = items
        .map((p) => {
          const href = p.url_path || ('?kod=' + encodeURIComponent(p.code || ''));
          const img = p.image
            ? '<span class="sklo-produkty__media"><img src="' +
              esc(p.image) +
              '" alt="' +
              esc(p.name || p.code || '') +
              '" loading="lazy" decoding="async" width="400" height="400"></span>'
            : '<div class="sklo-produkty__media sklo-produkty__media--empty" aria-hidden="true"></div>';
          const price = formatKc(p.price);
          return (
            '<article class="sklo-produkty__item">' +
            '<a class="sklo-produkty__link" href="' +
            esc(href) +
            '">' +
            img +
            '<div class="sklo-produkty__body">' +
            '<h3 class="sklo-produkty__name">' +
            esc(p.name || p.code || '') +
            '</h3>' +
            (p.code ? '<p class="sklo-produkty__code">' + esc(p.code) + '</p>' : '') +
            (price ? '<p class="sklo-produkty__price">' + esc(price) + '</p>' : '') +
            '<p class="sklo-produkty__more-link">Detail a doplatky</p>' +
            '</div></a></article>'
          );
        })
        .join('');
      root.hidden = false;
    })
    .catch(() => {
      /* silent — strip stays hidden */
    });
})();

(() => {
  const KEY = 'sklo_cookie_consent';
  const listeners = [];

  const read = () => {
    try {
      const raw = localStorage.getItem(KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      if (!data || typeof data !== 'object') return null;
      return data;
    } catch (e) {
      return null;
    }
  };

  const notify = (data) => {
    window.dispatchEvent(new CustomEvent('sklo_cookie_consent', { detail: data }));
    listeners.forEach((fn) => {
      try {
        fn(data);
      } catch (e) {}
    });
    if (typeof window.skloOnCookieConsent === 'function') {
      try {
        window.skloOnCookieConsent(data);
      } catch (e) {}
    }
  };

  const write = (choice) => {
    const data = {
      v: 1,
      choice: choice === 'all' ? 'all' : 'necessary',
      necessary: true,
      analytics: choice === 'all',
      ts: new Date().toISOString(),
    };
    try {
      localStorage.setItem(KEY, JSON.stringify(data));
    } catch (e) {}
    notify(data);
    return data;
  };

  window.skloGetCookieConsent = read;
  window.skloOnCookieConsentChange = (fn) => {
    if (typeof fn === 'function') listeners.push(fn);
  };

  const existing = read();
  if (existing) {
    notify(existing);
    return;
  }

  const cookiesUrl = new URL('/cookies/', window.location.origin).href;
  const bar = document.createElement('div');
  bar.className = 'sklo-cookie';
  bar.setAttribute('role', 'dialog');
  bar.setAttribute('aria-label', 'Souhlas s cookies');
  bar.innerHTML =
    '<div class="sklo-wrap sklo-cookie__inner">' +
    '<div class="sklo-cookie__text">' +
    '<p><strong>Cookies</strong> — nezbytné cookies používáme vždy. Analytické cookies zapneme jen po tvém souhlasu.</p>' +
    '<p class="sklo-cookie__more"><a href="' +
    cookiesUrl +
    '">Více o cookies</a></p>' +
    '</div>' +
    '<div class="sklo-cookie__actions">' +
    '<button type="button" class="sklo-btn sklo-btn--filled sklo-btn--sm" data-cookie-accept="all">Přijmout vše</button>' +
    '<button type="button" class="sklo-btn sklo-btn--ghost sklo-btn--sm" data-cookie-accept="necessary">Pouze nezbytné</button>' +
    '</div>' +
    '</div>';

  const dismiss = () => {
    bar.classList.remove('is-visible');
    window.setTimeout(() => bar.remove(), 220);
  };

  bar.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-cookie-accept]');
    if (!btn) return;
    write(btn.getAttribute('data-cookie-accept') || 'necessary');
    dismiss();
  });

  document.body.appendChild(bar);
  requestAnimationFrame(() => bar.classList.add('is-visible'));
})();

(() => {
  const track = document.querySelector('[data-reviews-track]');
  if (!track) return;
  const slides = Array.from(track.querySelectorAll('[data-reviews-slide]'));
  const dots = Array.from(document.querySelectorAll('[data-reviews-goto]'));
  if (slides.length < 2) return;

  let index = 0;
  let timer = null;

  const show = (i) => {
    index = (i + slides.length) % slides.length;
    slides.forEach((el, n) => {
      const on = n === index;
      el.classList.toggle('is-active', on);
      if (on) el.removeAttribute('hidden');
      else el.setAttribute('hidden', '');
    });
    dots.forEach((d, n) => d.classList.toggle('is-active', n === index));
  };

  const next = () => show(index + 1);
  const start = () => {
    stop();
    timer = window.setInterval(next, 6500);
  };
  const stop = () => {
    if (timer) window.clearInterval(timer);
    timer = null;
  };

  dots.forEach((d) => {
    d.addEventListener('click', () => {
      const i = Number(d.getAttribute('data-reviews-goto') || 0);
      show(i);
      start();
    });
  });

  track.addEventListener('mouseenter', stop);
  track.addEventListener('mouseleave', start);
  show(0);
  start();
})();

(() => {
  const list = document.querySelector('[data-recenze-list]');
  if (!list) return;
  const pageSize = Number(list.getAttribute('data-page-size') || 24);
  const items = Array.from(list.querySelectorAll('.sklo-recenze-item'));
  const chips = Array.from(document.querySelectorAll('[data-recenze-filters] [data-filter]'));
  const moreBtn = document.querySelector('[data-recenze-more]');
  const countEl = document.querySelector('[data-recenze-count]');
  let filter = 'all';
  let visible = pageSize;

  const matching = () =>
    items.filter((el) => filter === 'all' || el.getAttribute('data-category') === filter);

  const render = () => {
    const match = matching();
    items.forEach((el) => el.setAttribute('hidden', ''));
    match.slice(0, visible).forEach((el) => el.removeAttribute('hidden'));
    const shown = Math.min(visible, match.length);
    const remaining = match.length - shown;
    if (moreBtn) {
      moreBtn.hidden = remaining <= 0;
    }
    if (countEl) {
      if (match.length === 0) {
        countEl.hidden = false;
        countEl.textContent = 'Žádné recenze v této kategorii.';
      } else {
        countEl.hidden = false;
        countEl.textContent = `Zobrazeno ${shown} z ${match.length}`;
      }
    }
  };

  chips.forEach((chip) => {
    chip.addEventListener('click', () => {
      filter = chip.getAttribute('data-filter') || 'all';
      visible = pageSize;
      chips.forEach((c) => c.classList.toggle('is-active', c === chip));
      render();
    });
  });

  if (moreBtn) {
    moreBtn.addEventListener('click', () => {
      visible += pageSize;
      render();
    });
  }

  render();
})();
