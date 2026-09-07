import {
  IngestConflictError,
  IngestRateLimitError,
  IngestUnauthorizedError,
  IngestValidationError,
} from './errors.ts';
import { isRecord, parseRetryAfter, parseValidationErrors, readString } from './parse.ts';
import type { AcceptedIngest, IngestClientOptions, PriceUpdateRequest } from './types.ts';

export class IngestClient {
  readonly #baseUrl: string;
  readonly #token: string;
  readonly #fetch: typeof fetch;

  constructor(options: IngestClientOptions) {
    const baseUrl = options.baseUrl.trim().replace(/\/+$/, '');
    const token = options.token.trim();

    if (baseUrl === '') {
      throw new Error('Ingest client requires a base URL.');
    }

    if (token === '') {
      throw new Error('Ingest client requires a bearer token.');
    }

    this.#baseUrl = baseUrl;
    this.#token = token;
    this.#fetch = options.fetch ?? fetch;
  }

  async submitPriceUpdate(request: PriceUpdateRequest): Promise<AcceptedIngest> {
    const payload = {
      sku: request.sku,
      store_code: request.store_code,
      amount_cents: request.amount_cents,
      currency: request.currency,
      recorded_at: request.recorded_at,
      trace_id: request.trace_id ?? crypto.randomUUID(),
    };

    const response = await this.#fetch(`${this.#baseUrl}/api/price-updates`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${this.#token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const body: unknown = await response.json().catch(() => null);

    if (response.status === 202) {
      return this.#accepted(body, payload.trace_id);
    }

    if (response.status === 401) {
      throw new IngestUnauthorizedError(readString(isRecord(body) ? body.message : undefined));
    }

    if (response.status === 409) {
      const traceId = readString(isRecord(body) ? body.trace_id : undefined) ?? payload.trace_id ?? '';
      throw new IngestConflictError(traceId, readString(isRecord(body) ? body.message : undefined));
    }

    if (response.status === 422) {
      throw new IngestValidationError(
        parseValidationErrors(body),
        readString(isRecord(body) ? body.message : undefined),
      );
    }

    if (response.status === 429) {
      throw new IngestRateLimitError(
        parseRetryAfter(response.headers.get('Retry-After')),
        readString(isRecord(body) ? body.message : undefined),
      );
    }

    throw new Error(`Unexpected ingest response: HTTP ${response.status}`);
  }

  #accepted(body: unknown, fallbackTraceId: string | undefined): AcceptedIngest {
    if (!isRecord(body)) {
      throw new Error('Ingest response was not JSON.');
    }

    const message = readString(body.message);
    const traceId = readString(body.trace_id) ?? fallbackTraceId;

    if ((message !== 'accepted' && message !== 'duplicate') || traceId === undefined) {
      throw new Error('Ingest response missed message or trace_id.');
    }

    return { outcome: message, traceId };
  }
}
