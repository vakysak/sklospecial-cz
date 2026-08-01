'use strict';

/**
 * Shared dimension checks for chat tool + konfigurátor + POST /api/rozmery/check.
 * Units: mm.
 */

const SPREAD_WARN_MM = 10;
const SPREAD_HIGH_MM = 15;

const DOOR_W_SOFT_MIN = 500;
const DOOR_W_SOFT_MAX = 1500;
const DOOR_H_SOFT_MIN = 1800;
const DOOR_H_SOFT_MAX = 2500;

const HARD_MIN = 300;
const HARD_MAX = 3500;

function toNumList(raw) {
  if (raw == null) return [];
  if (Array.isArray(raw)) return raw.map((n) => Number(n));
  if (typeof raw === 'string') {
    try {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) return parsed.map((n) => Number(n));
    } catch {
      // fall through — comma-separated
    }
    return raw
      .split(/[,;\s]+/)
      .map((s) => s.trim())
      .filter(Boolean)
      .map((n) => Number(n));
  }
  return [Number(raw)];
}

/**
 * Accept widths[3] + heights[3], or pairs [{w,h},…], or flat {sirka,vyska}.
 */
function normalizeInput(body = {}) {
  let widths = toNumList(body.widths ?? body.sirka ?? body.w);
  let heights = toNumList(body.heights ?? body.vyska ?? body.h);

  if ((!widths.length || !heights.length) && Array.isArray(body.pairs)) {
    widths = body.pairs.map((p) => Number(p?.w ?? p?.width ?? p?.[0]));
    heights = body.pairs.map((p) => Number(p?.h ?? p?.height ?? p?.[1]));
  }

  return { widths, heights };
}

function spread(values) {
  const ok = values.filter((n) => Number.isFinite(n));
  if (ok.length < 2) return 0;
  return Math.max(...ok) - Math.min(...ok);
}

/**
 * @returns {{
 *   ok: boolean,
 *   valid: boolean,
 *   widths: number[],
 *   heights: number[],
 *   min_w: number|null,
 *   min_h: number|null,
 *   spread_w: number,
 *   spread_h: number,
 *   use_smallest: { w: number|null, h: number|null },
 *   errors: string[],
 *   warnings: string[],
 *   flags: object,
 *   summary_cs: string
 * }}
 */
function checkDimensions(input) {
  const { widths: rawW, heights: rawH } = normalizeInput(input);
  const errors = [];
  const warnings = [];
  const flags = {
    uneven_opening: false,
    uneven_opening_high: false,
    unrealistic_width: false,
    unrealistic_height: false,
  };

  if (rawW.length !== 3) {
    errors.push('Potřebuju 3 šířky (vlevo, uprostřed, vpravo) v mm.');
  }
  if (rawH.length !== 3) {
    errors.push('Potřebuju 3 výšky (vlevo, uprostřed, vpravo) v mm.');
  }

  const widths = rawW.slice(0, 3);
  const heights = rawH.slice(0, 3);

  for (let i = 0; i < widths.length; i += 1) {
    const n = widths[i];
    if (!Number.isFinite(n)) errors.push(`Šířka #${i + 1} není číslo.`);
    else if (n < HARD_MIN || n > HARD_MAX) {
      errors.push(`Šířka #${i + 1} (${n} mm) mimo rozsah ${HARD_MIN}–${HARD_MAX} mm.`);
    }
  }
  for (let i = 0; i < heights.length; i += 1) {
    const n = heights[i];
    if (!Number.isFinite(n)) errors.push(`Výška #${i + 1} není číslo.`);
    else if (n < HARD_MIN || n > HARD_MAX) {
      errors.push(`Výška #${i + 1} (${n} mm) mimo rozsah ${HARD_MIN}–${HARD_MAX} mm.`);
    }
  }

  const validNumsW = widths.filter((n) => Number.isFinite(n));
  const validNumsH = heights.filter((n) => Number.isFinite(n));
  const min_w = validNumsW.length ? Math.min(...validNumsW) : null;
  const min_h = validNumsH.length ? Math.min(...validNumsH) : null;
  const spread_w = spread(validNumsW);
  const spread_h = spread(validNumsH);

  if (spread_w >= SPREAD_HIGH_MM || spread_h >= SPREAD_HIGH_MM) {
    flags.uneven_opening = true;
    flags.uneven_opening_high = true;
    warnings.push(
      `Otvor je hodně nerovný (rozdíl šířek ${spread_w} mm, výšek ${spread_h} mm). Počítej s nejmenšími hodnotami a při zaměření to ověř.`
    );
  } else if (spread_w >= SPREAD_WARN_MM || spread_h >= SPREAD_WARN_MM) {
    flags.uneven_opening = true;
    warnings.push(
      `Otvor je mírně nerovný (rozdíl šířek ${spread_w} mm, výšek ${spread_h} mm). Ber nejmenší šířku a výšku.`
    );
  }

  if (min_w != null && (min_w < DOOR_W_SOFT_MIN || min_w > DOOR_W_SOFT_MAX)) {
    flags.unrealistic_width = true;
    warnings.push(
      `Šířka ${min_w} mm je mimo typický rozsah dveří (${DOOR_W_SOFT_MIN}–${DOOR_W_SOFT_MAX} mm) — ověř zaměření, nebo napiš, jestli jde o atyp.`
    );
  }
  if (min_h != null && (min_h < DOOR_H_SOFT_MIN || min_h > DOOR_H_SOFT_MAX)) {
    flags.unrealistic_height = true;
    warnings.push(
      `Výška ${min_h} mm je mimo typický rozsah dveří (${DOOR_H_SOFT_MIN}–${DOOR_H_SOFT_MAX} mm) — ověř zaměření, nebo napiš, jestli jde o atyp.`
    );
  }

  const valid = errors.length === 0 && min_w != null && min_h != null;

  const parts = [];
  if (valid) {
    parts.push(`Nejmenší rozměr: ${min_w} × ${min_h} mm (tím se řídíme).`);
    if (!warnings.length) {
      parts.push('Rozměry vypadají v pořádku — můžeš pokračovat k poptávce.');
    }
  } else {
    parts.push(errors[0] || 'Rozměry nejsou kompletní.');
  }
  if (warnings.length) parts.push(warnings.join(' '));

  return {
    ok: true,
    valid,
    widths,
    heights,
    min_w,
    min_h,
    spread_w,
    spread_h,
    use_smallest: { w: min_w, h: min_h },
    errors,
    warnings,
    flags,
    summary_cs: parts.join(' '),
  };
}

module.exports = {
  checkDimensions,
  normalizeInput,
  SPREAD_WARN_MM,
  SPREAD_HIGH_MM,
  DOOR_W_SOFT_MIN,
  DOOR_W_SOFT_MAX,
  DOOR_H_SOFT_MIN,
  DOOR_H_SOFT_MAX,
};
