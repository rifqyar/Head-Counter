import assert from 'node:assert/strict';
import { parseScannerPayload, shouldAcceptDecode } from '../../resources/js/scannerPayload.js';

const origin = 'https://headcounter.test';
const token = 'A'.repeat(43);

assert.equal(parseScannerPayload(token, origin), token);
assert.equal(parseScannerPayload(`${origin}/scan/participant/${token}`, origin), token);
assert.equal(parseScannerPayload(`https://evil.test/scan/participant/${token}`, origin), null);
assert.equal(parseScannerPayload(`${origin}/unexpected/${token}`, origin), null);
assert.equal(parseScannerPayload('javascript:alert(1)', origin), null);
assert.equal(parseScannerPayload('<script>alert(1)</script>', origin), null);

const state = { pending: false, lastToken: null, lastAt: 0, windowMs: 2500 };
assert.equal(shouldAcceptDecode(state, token, 1000), true);
assert.equal(shouldAcceptDecode(state, token, 1100), false);
assert.equal(shouldAcceptDecode(state, `${token}B`, 1200), true);
state.pending = true;
assert.equal(shouldAcceptDecode(state, `${token}C`, 4000), false);

console.log('scanner payload tests passed');
