function konfigurator() {
  const API = window.SKLO_API_BASE || '';
  return {
    krok: 1,
    sending: false,
    error: '',
    done: '',
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
      typ: [
        { v: 'otocne', l: 'Otočné', d: 'Klasické otevírání do místnosti' },
        { v: 'posuvne_stena', l: 'Posuvné na stěnu', d: 'Křídlo pojede podél stěny' },
        { v: 'posuvne_pouzdro', l: 'Posuvné do pouzdra', d: 'Dveře zmizí ve stěně' },
        { v: 'dvoukridle', l: 'Dvoukřídlé', d: 'Širší otvor, dvě křídla' },
      ],
      pouziti: [
        { v: 'byt_dum', l: 'Byt / dům', d: 'Interiér bydlení' },
        { v: 'koupelna', l: 'Koupelna', d: 'Vlhký provoz, vhodné sklo' },
        { v: 'kancelar', l: 'Kancelář / komerční', d: 'Kancelář, studio, provozovna' },
      ],
      sklo: [
        { v: 'cire', l: 'Čiré', d: 'Maximum světla, otevřený prostor' },
        { v: 'matne', l: 'Matné', d: 'Soukromí bez tmy' },
        { v: 'dekor', l: 'Dekorativní', d: 'Vzor nebo struktura skla' },
        { v: 'nevim', l: 'Nevím, poraďte mi', d: 'Doporučíme podle místa' },
      ],
      kovani: [
        { v: 'cerne', l: 'Černé matné', d: 'Výrazný kontrast' },
        { v: 'nerez', l: 'Nerez', d: 'Nadčasový kovový vzhled' },
        { v: 'zlate', l: 'Zlaté', d: 'Teplý detail' },
        { v: 'nevim', l: 'Nevím, poraďte mi', d: 'Ladíme ke sklu a interiéru' },
      ],
      montaz: [
        { v: 's_montazi', l: 'S montáží', d: 'Přijedeme a dveře osadíme' },
        { v: 'bez_montaze', l: 'Bez montáže', d: 'Montáž zvládneš sám' },
      ],
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
      if (this.krok === 4 && !c.typ_skla) return 'Vyber typ skla';
      if (this.krok === 5 && !c.kovani) return 'Vyber kování';
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
