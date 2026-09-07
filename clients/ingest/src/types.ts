export type PriceUpdateRequest = {
  sku: string;
  store_code: string;
  amount_cents: number;
  currency?: string;
  recorded_at?: string;
  trace_id?: string;
};

export type AcceptedIngest = {
  outcome: 'accepted' | 'duplicate';
  traceId: string;
};

export type IngestClientOptions = {
  baseUrl: string;
  token: string;
  fetch?: typeof fetch;
};

export type LaravelValidationErrors = Record<string, string[]>;
