# Go-live: fotografie katalogu

**Stav položky 8 (technická část):** primární produktové a category/hub obrázky běží z naší API. **Licence stále vyžaduje potvrzení partnerství** s Quba Glass.

## Hosting

- API static: `https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/SklS-XXXX.jpg`
- Lokálně: `app/public/katalog-img/` (~1010 cropů)
- Primary `image` v `produkty.json` a hub thumbs → self-hosted
- Homepage type-grid: Sprchy SklS-0608, Zábradlí SklS-0734, Stříšky SklS-0832, Balkony SklS-0645

## Co zůstává

- Gallery `images[]` v `produkty.json` může obsahovat `qubaglass.pl` URL (plné galerie bez 1:1 cropů) — UI používá primární self-hosted image
- `source_url` na qubaglass.pl je interní metadata, ne public UI

## Licence (manuální)

Technické hostování je na naší API. Licence / oprávnění k užití fotek **stále potřebuje potvrzení partnerství** s Quba Glass před ostrým marketingem.
