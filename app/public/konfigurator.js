function konfigurator() {
  const API = window.SKLO_API_BASE || '';
  const MEDIA_BASE =
    'https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/wp-content/uploads/2026/07';

  return {
    krok: 1,
    sending: false,
    loading: true,
    error: '',
    done: '',
    katalog: { typy: [], vzory: [], kovani: [] },
    config: {
      typ_dveri: null,
      pouziti: null,
      rozmery: { sirka: [null, null, null], vyska: [null, null, null], hloubka: null },
      typ_skla: null,
      kovani: null,
      montaz: null,
      fotky: [],
      kontakt: { jmeno: '', telefon: '', email: '', mesto: '', poznamka: '' },
    },
    prostor: {
      file: null,
      url: '',
      sampleId: null,
      // frame in % of preview-stage
      x: 28,
      y: 18,
      w: 36,
      h: 58,
    },
    /** Ukázkové interiéry z galerie Realizace (WP media). */
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
        () => [
          ...this.config.rozmery.sirka,
          ...this.config.rozmery.vyska,
        ],
        () => this.syncFrameAspect()
      );

      try {
        const res = await fetch(`${API}/api/katalog`);
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Katalog se nenačetl');
        this.katalog = {
          typy: data.typy || [],
          vzory: data.vzory || [],
          kovani: data.kovani || [],
        };
        if (!this.config.typ_dveri && this.katalog.typy[0]) this.config.typ_dveri = this.katalog.typy[0].slug;
        if (!this.config.typ_skla && this.katalog.vzory[0]) this.config.typ_skla = this.katalog.vzory[0].slug;
        if (!this.config.kovani && this.katalog.kovani[0]) this.config.kovani = this.katalog.kovani[0].slug;
      } catch (err) {
        this.error = err.message || 'Katalog se nenačetl';
      } finally {
        this.loading = false;
      }
    },

    selectedVzor() {
      return this.katalog.vzory.find((v) => v.slug === this.config.typ_skla) || null;
    },
    selectedKovani() {
      return this.katalog.kovani.find((k) => k.slug === this.config.kovani) || null;
    },
    selectedTyp() {
      return this.katalog.typy.find((t) => t.slug === this.config.typ_dveri) || null;
    },

    measureMm() {
      const s = this.config.rozmery.sirka.map(Number).filter((n) => Number.isFinite(n) && n >= 300);
      const v = this.config.rozmery.vyska.map(Number).filter((n) => Number.isFinite(n) && n >= 300);
      if (!s.length || !v.length) return null;
      return { w: Math.min(...s), h: Math.min(...v) };
    },

    isSliding() {
      return ['posuvne_stena', 'posuvne_pouzdro'].includes(this.config.typ_dveri);
    },
    doorClass() {
      return {
        sliding: this.isSliding(),
        pocket: this.config.typ_dveri === 'posuvne_pouzdro',
        double: this.config.typ_dveri === 'dvoukridle',
        swing: this.config.typ_dveri === 'otocne' || !this.config.typ_dveri,
      };
    },
    glassClass() {
      return this.selectedVzor()?.css_class || 'p-cire';
    },
    handleStyle() {
      return { background: this.selectedKovani()?.color_hex || '#1a1a1a' };
    },
    openingStyle() {
      const m = this.measureMm();
      if (!m) return {};
      const ratio = Math.max(0.4, Math.min(0.9, m.w / m.h));
      return { '--door-ratio': ratio };
    },
    photoFrameStyle() {
      const f = this.prostor;
      return {
        left: `${f.x}%`,
        top: `${f.y}%`,
        width: `${f.w}%`,
        height: `${f.h}%`,
      };
    },
    frameLabel() {
      const m = this.measureMm();
      if (!m) return 'Doplň rozměry — upraví se poměr otvoru';
      return `${m.w} × ${m.h} mm`;
    },
    previewTitle() {
      const typ = this.selectedTyp()?.nazev || 'Skleněné dveře';
      const vzor = this.selectedVzor()?.nazev;
      return [typ, vzor].filter(Boolean).join(' · ');
    },
    previewSub() {
      if (this.prostor.url) {
        return this.prostor.sampleId
          ? 'Ukázková místnost: posuň rámeček na otvor, dveře se skládají dovnitř'
          : 'Fotka prostoru: posuň rámeček na otvor, dveře se skládají dovnitř';
      }
      const k = this.selectedKovani()?.nazev;
      return k ? `Lišta / kování: ${k}` : 'Zkus ukázkovou fotku, nebo vlož vlastní';
    },

    /** Udrž poměr stran rámečku = zaměřená šířka/výška (nejmenší hodnoty). */
    syncFrameAspect() {
      const m = this.measureMm();
      if (!m || !this.prostor.url) return;
      const aspect = m.w / m.h; // width/height
      const f = this.prostor;
      // keep center, adjust height from width
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
        // corner resize — keep aspect from measurements
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
        // enforce aspect from width
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
      const dimOk = (arr) => Array.isArray(arr) && arr.length === 3 && arr.every((n) => Number(n) >= 300 && Number(n) <= 3500);
      if (this.krok === 1 && !c.typ_dveri) return 'Vyber typ dveří';
      if (this.krok === 2 && !c.pouziti) return 'Vyber použití';
      if (this.krok === 3) {
        if (!dimOk(c.rozmery.sirka) || !dimOk(c.rozmery.vyska)) return 'Doplň šířku a výšku (300–3500 mm)';
        if (!(Number(c.rozmery.hloubka) >= 20 && Number(c.rozmery.hloubka) <= 800)) return 'Doplň hloubku stěny';
      }
      if (this.krok === 4 && !c.typ_skla) return 'Vyber vzor skla';
      if (this.krok === 5 && !c.kovani) return 'Vyber lištu / kování';
      if (this.krok === 6 && !c.montaz) return 'Vyber montáž';
      if (this.krok === 7) {
        if (c.fotky.length < 2) return 'Nahraj aspoň 2 fotky';
        if (c.fotky.length > 8) return 'Maximálně 8 fotek';
        if (!c.kontakt.jmeno || c.kontakt.jmeno.trim().length < 2) return 'Doplň jméno';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(c.kontakt.email || '')) return 'Neplatný e-mail';
        if (!c.kontakt.telefon || c.kontakt.telefon.replace(/\D/g, '').length < 9) return 'Neplatný telefon';
        if (!c.kontakt.mesto || c.kontakt.mesto.trim().length < 2) return 'Doplň město / PSČ';
      }
      return '';
    },
    next() {
      this.error = this.validate();
      if (this.error) return;
      this.krok = Math.min(7, this.krok + 1);
      if (this.krok === 4 || this.krok === 3) this.syncFrameAspect();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    back() {
      this.error = '';
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
        const fd = new FormData();
        fd.append('typ_dveri', c.typ_dveri);
        fd.append('pouziti', c.pouziti);
        fd.append('sirka', JSON.stringify(c.rozmery.sirka));
        fd.append('vyska', JSON.stringify(c.rozmery.vyska));
        fd.append('hloubka', String(c.rozmery.hloubka));
        fd.append('typ_skla', c.typ_skla);
        fd.append('kovani', c.kovani);
        fd.append('montaz', c.montaz);
        fd.append('jmeno', c.kontakt.jmeno);
        fd.append('telefon', c.kontakt.telefon);
        fd.append('email', c.kontakt.email);
        fd.append('mesto', c.kontakt.mesto);
        fd.append('poznamka', c.kontakt.poznamka || '');
        if (this.prostor.file) fd.append('fotky', this.prostor.file, `prostor-${this.prostor.file.name}`);
        c.fotky.forEach((f) => fd.append('fotky', f));

        const res = await fetch(`${API}/api/konfigurator/odeslat`, { method: 'POST', body: fd });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Odeslání selhalo');
        this.done = `Poptávka #${data.id} je odeslaná. Ozveme se s nabídkou.`;
      } catch (err) {
        this.error = err.message || 'Odeslání selhalo';
      } finally {
        this.sending = false;
      }
    },
  };
}
