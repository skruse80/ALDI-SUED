import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import { parseRetryAfter, parseValidationErrors } from '../src/parse.ts';

describe('parseRetryAfter', () => {
  it('reads a second count', () => {
    assert.equal(parseRetryAfter('12'), 12);
  });

  it('ignores empty and non-numeric values', () => {
    assert.equal(parseRetryAfter(null), undefined);
    assert.equal(parseRetryAfter(''), undefined);
    assert.equal(parseRetryAfter('Fri, 01 Jan 2030 00:00:00 GMT'), undefined);
  });
});

describe('parseValidationErrors', () => {
  it('keeps laravel field errors', () => {
    assert.deepEqual(
      parseValidationErrors({
        message: 'The sku field is required.',
        errors: { sku: ['The sku field is required.'] },
      }),
      { sku: ['The sku field is required.'] },
    );
  });

  it('returns an empty object for unexpected payloads', () => {
    assert.deepEqual(parseValidationErrors(null), {});
    assert.deepEqual(parseValidationErrors({ errors: { sku: [1] } }), {});
  });
});
