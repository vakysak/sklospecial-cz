function konfigurator() {
  const API = window.SKLO_API_BASE || '';

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
      try {
        const res = await fetch(`${API}/api/katalog`);
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Katalog se nenačetl');
        this.katalog = {
          typy: data.typy || [],
          vzory: data.vzory || [],
          kovani: data.kovani || [],
        };
        // defaults for live preview
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
      const vzor = this.selectedVzor();
      return vzor?.css_class || 'p-cire';
    },
    frameStyle() {
      const k = this.selectedKovani();
      const color = k?.color_hex || '#1a1a1a';
      return { '--frame': color, borderColor: color, backgroundColor: color };
    },
    handleStyle() {
      const k = this.selectedKovani();
      return { background: k?.color_hex || '#1a1a1a' };
    },
    openingStyle() {
      const s = this.config.rozmery.sirka.filter((n) => Number(n) > 0);
      const v = this.config.rozmery.vyska.filter((n) => Number(n) > 0);
      if (!s.length || !v.length) return {};
      const w = Math.min(...s);
      const h = Math.min(...v);
      const ratio = Math.max(0.45, Math.min(0.85, w / h));
      return { '--door-ratio': ratio };
    },
    previewTitle() {
      const typ = this.selectedTyp()?.nazev || 'Skleněné dveře';
      const vzor = this.selectedVzor()?.nazev;
      return [typ, vzor].filter(Boolean).join(' · ');
    },
    previewSub() {
      const k = this.selectedKovani()?.nazev;
      return k ? `Lišta / kování: ${k}` : 'Vyber vzor a lištu — náhled se mění hned';
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

document.addEventListener('alpine:init', () => {
  // auto-init when Alpine mounts x-data
});
