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
    btn.addEventListener('click', () => {
      root.querySelectorAll('.sklo-produkty__item.is-collapsed').forEach((el) => {
        if (el.classList.contains('is-filtered-out')) return;
        el.hidden = false;
        el.classList.remove('is-collapsed');
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
  const initial = gridRoot
    ? Number.parseInt(gridRoot.getAttribute('data-initial') || '12', 10) || 12
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
        shown += 1;
      } else {
        el.hidden = true;
        el.classList.add('is-collapsed');
      }
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
    });
  }

  // Honor hash deep-links (#diy etc.)
  const hash = (window.location.hash || '').replace(/^#/, '');
  if (hash && items.some((el) => el.getAttribute('data-category') === hash)) {
    apply(hash, { scroll: true });
  }
})();
