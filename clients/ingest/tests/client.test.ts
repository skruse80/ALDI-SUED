import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import { IngestClient } from '../src/client.ts';
import {
  IngestConflictError,
  IngestRateLimitError,
  IngestUnauthorizedError,
  IngestValidationError,
} from '../src/errors.ts';
import type { PriceUpdateRequest } from '../src/types.ts';

const request: PriceUpdateRequest = {
  sku: 'MILCH-001',
  store_code: 'K-101',
  amount_cents: 129,
  trace_id: '550e8400-e29b-41d4-a716-446655440000',
};

function jsonResponse(status: number, body: unknown, headers: HeadersInit = {}): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'Content-Type': 'application/json', ...headers },
  });
}

function clientWith(handler: typeof fetch): IngestClient {
  return new IngestClient({
    baseUrl: 'http://localhost:8000/',
    token: 'local-ingest-token',
    fetch: handler,
  });
}

describe('IngestClient', () => {
  it('posts the price update and maps an accepted response', async () => {
    const handler: typeof fetch = async (input, init) => {
      assert.equal(String(input), 'http://localhost:8000/api/price-updates');
      assert.equal(init?.method, 'POST');
      assert.equal(new Headers(init?.headers).get('Authorization'), 'Bearer local-ingest-token');
      assert.deepEqual(JSON.parse(String(init?.body)), request);

      return jsonResponse(202, { message: 'accepted', trace_id: request.trace_id });
    };

    const result = await clientWith(handler).submitPriceUpdate(request);

    assert.deepEqual(result, { outcome: 'accepted', traceId: request.trace_id });
  });

  it('assigns a trace id when the caller omits one', async () => {
    const handler: typeof fetch = async (_input, init) => {
      const body = JSON.parse(String(init?.body)) as PriceUpdateRequest;
      assert.match(body.trace_id ?? '', /^[0-9a-f-]{36}$/i);

      return jsonResponse(202, { message: 'duplicate', trace_id: body.trace_id });
    };

    const result = await clientWith(handler).submitPriceUpdate({
      sku: 'MILCH-001',
      store_code: 'K-101',
      amount_cents: 129,
    });

    assert.equal(result.outcome, 'duplicate');
    assert.match(result.traceId, /^[0-9a-f-]{36}$/i);
  });

  it('throws a conflict error for a reused trace id', async () => {
    const handler: typeof fetch = async () =>
      jsonResponse(409, {
        message: 'trace_id already used with a different payload',
        trace_id: request.trace_id,
      });

    await assert.rejects(
      () => clientWith(handler).submitPriceUpdate(request),
      (error: unknown) => {
        assert.ok(error instanceof IngestConflictError);
        assert.equal(error.status, 409);
        assert.equal(error.traceId, request.trace_id);
        return true;
      },
    );
  });

  it('throws a validation error for unknown skus', async () => {
    const handler: typeof fetch = async () =>
      jsonResponse(422, {
        message: 'The sku field is invalid.',
        errors: { sku: ['The selected sku is invalid.'] },
      });

    await assert.rejects(
      () => clientWith(handler).submitPriceUpdate(request),
      (error: unknown) => {
        assert.ok(error instanceof IngestValidationError);
        assert.deepEqual(error.errors.sku, ['The selected sku is invalid.']);
        return true;
      },
    );
  });

  it('throws unauthorized and rate-limit errors', async () => {
    await assert.rejects(
      () => clientWith(async () => jsonResponse(401, { message: 'Invalid ingest token.' })).submitPriceUpdate(request),
      (error: unknown) => error instanceof IngestUnauthorizedError && error.status === 401,
    );

    await assert.rejects(
      () =>
        clientWith(async () => jsonResponse(429, { message: 'Too Many Attempts.' }, { 'Retry-After': '30' }))
          .submitPriceUpdate(request),
      (error: unknown) => error instanceof IngestRateLimitError && error.retryAfterSeconds === 30,
    );
  });
});
