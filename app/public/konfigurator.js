function konfigurator() {
  const API = window.SKLO_API_BASE || '';
  const MEDIA_BASE =
    'https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/wp-content/uploads/2026/07';
  const TOTAL_STEPS = 6;

  return {
    krok: 1,
    totalSteps: TOTAL_STEPS,
    sending: false,
    loading: true,
    loadingProducts: false,
    error: '',
    done: '',
    searchQ: '',
    typy: [],
    produkty: [],
    productDetail: null,
    config: {
      typ_dveri: null,
      needs_advice: false,
      product_code: null,
      optionChoices: {},
      pouziti: null,
      rozmery: { sirka: [null, null, null], vyska: [null, null, null], hloubka: null },
      montaz: null,
      fotky: [],
      kontakt: { jmeno: '', telefon: '', email: '', mesto: '', poznamka: '', gdpr_souhlas: false },
    },
    prostor: {
      file: null,
      url: '',
      sampleId: null,
      x: 28,
      y: 18,
      w: 36,
      h: 58,
    },
    samples: [
      { id: 'interier-1', file: 'sklenene-dvere-v-interieru.webp', label: 'Interiér s výhledem' },
      { id: 'interier-2', file: 'sklenene-dvere-v-interieru-2.webp', label: 'Světlý byt' },
      { id: 'interier-3', file: 'moderni-interier-sklenene-dvere.webp', label: 'Moderní interiér' },
      { id: 'obyvak', file: 'sklenene-dvere-do-obyvaciho-pokoje-3.webp', label: 'Obývací pokoj' },
      { id: 'otevirani', file: 'zeny-za-sklenenymi-dvermi-v-interieru.webp', label: 'Otevřený průchod' },
      { id: 'drevo', file: 'interier-modernich-sklenenych-dveri.webp', label: 'Dřevěná podlaha' },
      { id: 'bydleni', file: 'moderni-bydleni-sklenene-dvere.webp', label: 'Moderní bydlení' },
      { id: 'posuvne', file: 'posuvne-sklenene-dvere-matne-sklo.webp', label: 'Posuvné matné' },
    ].map((s) => ({ ...s, url: `${MEDIA_BASE}/${s.file}` })),
    drag: null,
    options: {
      pouziti: [
        { v: 'byt_dum', l: 'Byt / dům' },
        { v: 'koupelna', l: 'Koupelna' },
        { v: 'kancelar', l: 'Kancelář / komerční' },
      ],
      montaz: [
        { v: 's_montazi', l: 'S montáží', d: 'Přijedeme a dveře osadíme' },
        { v: 'bez_montaze', l: 'Bez montáže', d: 'Montáž zvládneš sám' },
      ],
    },

    async init() {
      this.$watch(
        () => [...this.config.rozmery.sirka, ...this.config.rozmery.vyska],
        () => this.syncFrameAspect()
      );

      try {
        const res = await fetch(`${API}/api/katalog`);
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Katalog se nenačetl');
        this.typy = data.typy || [];
        if (!this.config.typ_dveri && this.typy[0]) {
          this.config.typ_dveri = this.typy.find((t) => t.slug !== 'nevim')?.slug || this.typy[0].slug;
        }
      } catch (err) {
        this.error = err.message || 'Katalog se nenačetl';
      } finally {
        this.loading = false;
      }
    },

    selectedTyp() {
      return this.typy.find((t) => t.slug === this.config.typ_dveri) || null;
    },
    selectedProduct() {
      if (this.productDetail?.code === this.config.product_code) return this.productDetail;
      return this.produkty.find((p) => p.code === this.config.product_code) || null;
    },

    formatPrice(n) {
      const v = Number(n);
      if (!Number.isFinite(v)) return '—';
      return `${Math.round(v).toLocaleString('cs-CZ')} Kč`;
    },

    optionsSelected() {
      const product = this.selectedProduct();
      if (!product?.options?.length) return [];
      const out = [];
      for (const group of product.options) {
        const choice = this.config.optionChoices[group.label];
        if (!choice) continue;
        const opt = (group.choices || []).find((c) => c.name === choice);
        out.push({
          label: group.label,
          choice,
          surcharge_czk: Number(opt?.surcharge_czk) || 0,
        });
      }
      return out;
    },

    priceTotal() {
      const product = this.selectedProduct();
      if (!product) return null;
      const base = Number(product.price) || 0;
      const surcharge = this.optionsSelected().reduce((s, o) => s + (o.surcharge_czk || 0), 0);
      return base + surcharge;
    },

    async selectTyp(slug) {
      this.config.typ_dveri = slug;
      this.config.needs_advice = slug === 'nevim';
      if (slug === 'nevim') {
        this.config.product_code = null;
        this.productDetail = null;
        this.produkty = [];
        this.config.optionChoices = {};
        return;
      }
      if (this.config.product_code) {
        const still = this.produkty.find((p) => p.code === this.config.product_code && p.typ === slug);
        if (!still) {
          this.config.product_code = null;
          this.productDetail = null;
          this.config.optionChoices = {};
        }
      }
      await this.loadProdukty();
    },

    async loadProdukty() {
      const typ = this.config.typ_dveri;
      if (!typ || typ === 'nevim') {
        this.produkty = [];
        return;
      }
      this.loadingProducts = true;
      this.error = '';
      try {
        const params = new URLSearchParams({ typ });
        if (this.searchQ.trim()) params.set('q', this.searchQ.trim());
        const res = await fetch(`${API}/api/produkty?${params}`);
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Produkty se nenačetly');
        this.produkty = data.items || [];
      } catch (err) {
        this.error = err.message || 'Produkty se nenačetly';
        this.produkty = [];
      } finally {
        this.loadingProducts = false;
      }
    },

    async selectProduct(code) {
      this.config.product_code = code;
      this.config.needs_advice = false;
      this.config.optionChoices = {};
      this.productDetail = null;
      try {
        const res = await fetch(`${API}/api/produkty/${encodeURIComponent(code)}`);
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Detail produktu se nenačetl');
        this.productDetail = data.item;
        for (const group of data.item.options || []) {
          if (!group.choices?.length) continue;
          const zero = group.choices.find((c) => !c.surcharge_czk) || group.choices[0];
          this.config.optionChoices[group.label] = zero.name;
        }
      } catch (err) {
        this.error = err.message || 'Detail produktu se nenačetl';
      }
    },

    skipProductAdvice() {
      this.config.needs_advice = true;
      this.config.product_code = null;
      this.productDetail = null;
      this.config.optionChoices = {};
    },

    isSliding() {
      return ['posuvne', 'do-pouzdra'].includes(this.config.typ_dveri);
    },
    doorClass() {
      return {
        sliding: this.isSliding(),
        pocket: this.config.typ_dveri === 'do-pouzdra',
        swing: this.config.typ_dveri === 'otocne' || this.config.typ_dveri === 'otevirane' || !this.config.typ_dveri,
      };
    },

    measureMm() {
      const s = this.config.rozmery.sirka.map(Number).filter((n) => Number.isFinite(n) && n >= 300);
      const v = this.config.rozmery.vyska.map(Number).filter((n) => Number.isFinite(n) && n >= 300);
      if (!s.length || !v.length) return null;
      return { w: Math.min(...s), h: Math.min(...v) };
    },

    openingStyle() {
      const m = this.measureMm();
      if (!m) return {};
      const ratio = Math.max(0.4, Math.min(0.9, m.w / m.h));
      return { '--door-ratio': ratio };
    },
    photoFrameStyle() {
      const f = this.prostor;
      return { left: `${f.x}%`, top: `${f.y}%`, width: `${f.w}%`, height: `${f.h}%` };
    },
    frameLabel() {
      const m = this.measureMm();
      if (!m) return 'Doplň rozměry — upraví se poměr otvoru';
      return `${m.w} × ${m.h} mm`;
    },
    previewTitle() {
      const p = this.selectedProduct();
      if (p) return p.name;
      return this.selectedTyp()?.nazev || 'Skleněné dveře';
    },
    previewSub() {
      const p = this.selectedProduct();
      if (p?.code) {
        const price = this.priceTotal();
        return `${p.code}${price != null ? ` · od ${this.formatPrice(price)}` : ''}`;
      }
      if (this.config.needs_advice) return 'Bez konkrétního modelu — doporučíme podle fotek';
      if (this.prostor.url) {
        return this.prostor.sampleId
          ? 'Ukázková místnost: posuň rámeček na otvor'
          : 'Fotka prostoru: posuň rámeček na otvor';
      }
      return 'Vyber typ a produkt z katalogu SklS';
    },

    syncFrameAspect() {
      const m = this.measureMm();
      if (!m || !this.prostor.url) return;
      const aspect = m.w / m.h;
      const f = this.prostor;
      const cx = f.x + f.w / 2;
      const cy = f.y + f.h / 2;
      let w = f.w;
      let h = w / aspect;
      if (h > 78) {
        h = 78;
        w = h * aspect;
      }
      if (w > 78) {
        w = 78;
        h = w / aspect;
      }
      if (w < 12) {
        w = 12;
        h = w / aspect;
      }
      f.w = w;
      f.h = h;
      f.x = Math.min(88, Math.max(2, cx - w / 2));
      f.y = Math.min(88, Math.max(2, cy - h / 2));
    },

    revokeProstorUrl() {
      const u = this.prostor.url;
      if (u && u.startsWith('blob:')) URL.revokeObjectURL(u);
    },
    applyProstorUrl(url, { sampleId = null, file = null } = {}) {
      this.revokeProstorUrl();
      this.prostor.file = file;
      this.prostor.url = url;
      this.prostor.sampleId = sampleId;
      this.prostor.x = 28;
      this.prostor.y = 16;
      this.prostor.w = 38;
      this.prostor.h = 60;
      this.syncFrameAspect();
    },
    loadSample(sample) {
      if (!sample?.url) return;
      this.applyProstorUrl(sample.url, { sampleId: sample.id });
    },
    onProstor(e) {
      const file = e.target.files?.[0];
      if (!file) return;
      this.applyProstorUrl(URL.createObjectURL(file), { file });
    },
    clearProstor() {
      this.revokeProstorUrl();
      this.prostor.file = null;
      this.prostor.url = '';
      this.prostor.sampleId = null;
    },

    onStagePointerDown(e) {
      if (!this.prostor.url) return;
      const handle = e.target?.dataset?.drag;
      if (!handle) return;
      const stage = e.currentTarget.getBoundingClientRect();
      this.drag = {
        mode: handle,
        startX: e.clientX,
        startY: e.clientY,
        orig: { ...this.prostor },
        stageW: stage.width,
        stageH: stage.height,
      };
      e.currentTarget.setPointerCapture?.(e.pointerId);
      e.preventDefault();
    },
    onStagePointerMove(e) {
      if (!this.drag) return;
      const d = this.drag;
      const dx = ((e.clientX - d.startX) / d.stageW) * 100;
      const dy = ((e.clientY - d.startY) / d.stageH) * 100;
      const o = d.orig;
      const m = this.measureMm();
      const aspect = m ? m.w / m.h : o.w / o.h;
      let { x, y, w, h } = o;
      if (d.mode === 'move') {
        x = o.x + dx;
        y = o.y + dy;
      } else {
        if (d.mode.includes('r')) w = o.w + dx;
        if (d.mode.includes('l')) {
          w = o.w - dx;
          x = o.x + dx;
        }
        if (d.mode.includes('b')) h = o.h + dy;
        if (d.mode.includes('t')) {
          h = o.h - dy;
          y = o.y + dy;
        }
        h = w / aspect;
        if (d.mode.includes('t')) y = o.y + o.h - h;
        if (d.mode.includes('l')) x = o.x + o.w - w;
      }
      w = Math.max(10, Math.min(85, w));
      h = w / aspect;
      h = Math.max(12, Math.min(85, h));
      w = h * aspect;
      x = Math.max(1, Math.min(99 - w, x));
      y = Math.max(1, Math.min(99 - h, y));
      this.prostor.x = x;
      this.prostor.y = y;
      this.prostor.w = w;
      this.prostor.h = h;
    },
    onStagePointerUp() {
      this.drag = null;
    },

    onFiles(e) {
      this.config.fotky = Array.from(e.target.files || []);
    },

    validate() {
      const c = this.config;
      const dimOk = (arr) =>
        Array.isArray(arr) && arr.length === 3 && arr.every((n) => Number(n) >= 300 && Number(n) <= 3500);

      if (this.krok === 1 && !c.typ_dveri) return 'Vyber typ dveří';
      if (this.krok === 2) {
        if (c.typ_dveri === 'nevim' || c.needs_advice) return '';
        if (!c.product_code) return 'Vyber produkt, nebo zvol „potřebuju poradit“';
      }
      if (this.krok === 3) {
        if (c.needs_advice || !c.product_code) return '';
        const product = this.selectedProduct();
        if (product?.options?.length) {
          for (const g of product.options) {
            if (g.choices?.length && !c.optionChoices[g.label]) {
              return `Vyber: ${g.label}`;
            }
          }
        }
      }
      if (this.krok === 4) {
        if (!dimOk(c.rozmery.sirka) || !dimOk(c.rozmery.vyska)) return 'Doplň šířku a výšku (300–3500 mm)';
        if (!(Number(c.rozmery.hloubka) >= 20 && Number(c.rozmery.hloubka) <= 800)) return 'Doplň hloubku stěny';
        if (c.fotky.length < 2) return 'Nahraj aspoň 2 fotky otvoru';
        if (c.fotky.length > 8) return 'Maximálně 8 fotek';
      }
      if (this.krok === 5) {
        if (!c.pouziti) return 'Vyber použití';
        if (!c.montaz) return 'Vyber montáž';
      }
      if (this.krok === 6) {
        if (!c.kontakt.jmeno || c.kontakt.jmeno.trim().length < 2) return 'Doplň jméno';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(c.kontakt.email || '')) return 'Neplatný e-mail';
        if (!c.kontakt.telefon || c.kontakt.telefon.replace(/\D/g, '').length < 9) return 'Neplatný telefon';
        if (!c.kontakt.mesto || c.kontakt.mesto.trim().length < 2) return 'Doplň město / PSČ';
        if (!c.kontakt.gdpr_souhlas) return 'Potřebujeme souhlas se zpracováním osobních údajů';
      }
      return '';
    },

    async next() {
      this.error = this.validate();
      if (this.error) return;

      if (this.krok === 1) {
        if (this.config.typ_dveri === 'nevim') {
          this.config.needs_advice = true;
          this.krok = 4;
          window.scrollTo({ top: 0, behavior: 'smooth' });
          return;
        }
        await this.loadProdukty();
      }

      if (this.krok === 2 && (this.config.needs_advice || !this.config.product_code)) {
        this.krok = 4;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      if (this.krok === 2 && this.config.product_code && !this.productDetail) {
        await this.selectProduct(this.config.product_code);
      }

      if (this.krok === 3 && (!this.selectedProduct()?.options?.length || this.config.needs_advice)) {
        this.krok = 4;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }

      this.krok = Math.min(TOTAL_STEPS, this.krok + 1);
      if (this.krok === 4) this.syncFrameAspect();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    back() {
      this.error = '';
      if (this.krok === 4 && (this.config.needs_advice || this.config.typ_dveri === 'nevim')) {
        this.krok = this.config.typ_dveri === 'nevim' ? 1 : 2;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }
      if (this.krok === 4 && this.config.product_code && !(this.selectedProduct()?.options?.length)) {
        this.krok = 2;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }
      this.krok = Math.max(1, this.krok - 1);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    async submit() {
      this.error = this.validate();
      if (this.error) return;
      this.sending = true;
      this.done = '';
      try {
        const c = this.config;
        const product = this.selectedProduct();
        const fd = new FormData();
        fd.append('typ_dveri', c.typ_dveri);
        fd.append('needs_advice', c.needs_advice || c.typ_dveri === 'nevim' ? '1' : '0');
        if (c.product_code) fd.append('product_code', c.product_code);
        if (product?.name) fd.append('product_name', product.name);
        fd.append('options_selected', JSON.stringify(this.optionsSelected()));
        const total = this.priceTotal();
        if (total != null) fd.append('price_total', String(total));
        fd.append('pouziti', c.pouziti);
        fd.append('sirka', JSON.stringify(c.rozmery.sirka));
        fd.append('vyska', JSON.stringify(c.rozmery.vyska));
        fd.append('hloubka', String(c.rozmery.hloubka));
        fd.append('montaz', c.montaz);
        fd.append('jmeno', c.kontakt.jmeno);
        fd.append('telefon', c.kontakt.telefon);
        fd.append('email', c.kontakt.email);
        fd.append('mesto', c.kontakt.mesto);
        fd.append('poznamka', c.kontakt.poznamka || '');
        fd.append('gdpr_souhlas', c.kontakt.gdpr_souhlas ? '1' : '0');
        if (this.prostor.file) fd.append('fotky', this.prostor.file, `prostor-${this.prostor.file.name}`);
        c.fotky.forEach((f) => fd.append('fotky', f));

        const res = await fetch(`${API}/api/konfigurator/odeslat`, { method: 'POST', body: fd });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Odeslání selhalo');
        const codeNote = data.product_code ? ` (${data.product_code})` : '';
        this.done = `Poptávka #${data.id}${codeNote} je odeslaná. Ozveme se s nabídkou.`;
      } catch (err) {
        this.error = err.message || 'Odeslání selhalo';
      } finally {
        this.sending = false;
      }
    },
  };
}
