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
  const gallery = document.querySelector('[data-gallery]');
  const lightbox = document.querySelector('[data-lightbox]');
  if (!gallery || !lightbox) return;

  const items = Array.from(gallery.querySelectorAll('[data-lightbox-open]'));
  if (!items.length) return;

  const img = lightbox.querySelector('[data-lightbox-img]');
  const btnClose = lightbox.querySelector('[data-lightbox-close]');
  const btnPrev = lightbox.querySelector('[data-lightbox-prev]');
  const btnNext = lightbox.querySelector('[data-lightbox-next]');
  let index = 0;
  let lastFocus = null;

  const show = (i) => {
    index = (i + items.length) % items.length;
    const el = items[index];
    img.src = el.getAttribute('data-src') || '';
    img.alt = el.getAttribute('data-alt') || '';
  };

  const open = (i) => {
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

  items.forEach((el, i) => {
    el.addEventListener('click', () => open(i));
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
