'use strict';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_RE = /^(\+420|\+421|00420|00421)?[\s\-]?[1-9][0-9]{2}[\s\-]?[0-9]{3}[\s\-]?[0-9]{3}$/;

function isEmail(value) {
  return typeof value === 'string' && EMAIL_RE.test(value.trim());
}

function isPhoneCzSk(value) {
  if (typeof value !== 'string') return false;
  const compact = value.replace(/[\s\-()]/g, '');
  return PHONE_RE.test(value) || /^(\+420|\+421)?[1-9][0-9]{8}$/.test(compact);
}

function isDimMm(value) {
  const n = Number(value);
  return Number.isFinite(n) && n >= 300 && n <= 3500;
}

module.exports = {
  isEmail,
  isPhoneCzSk,
  isDimMm,
};
