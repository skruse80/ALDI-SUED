#!/usr/bin/env node

import { IngestClient } from './client.ts';
import { IngestHttpError } from './errors.ts';

function arg(name: string): string | undefined {
  const index = process.argv.indexOf(name);

  if (index === -1) {
    return undefined;
  }

  return process.argv[index + 1];
}

const required = (name: string, value: string | undefined, fallback?: string): string => {
  const resolved = value ?? fallback;

  if (resolved === undefined || resolved === '') {
    throw new Error(`Missing ${name}`);
  }

  return resolved;
};

try {
  const amount = Number.parseInt(required('--amount-cents', arg('--amount-cents')), 10);

  if (!Number.isInteger(amount) || amount < 0) {
    throw new Error('--amount-cents must be a non-negative integer');
  }

  const result = await new IngestClient({
    baseUrl: required('--base-url', arg('--base-url'), process.env.PRICE_BROKER_URL ?? 'http://localhost:8000'),
    token: required('--token', arg('--token'), process.env.INGEST_API_TOKEN),
  }).submitPriceUpdate({
    sku: required('--sku', arg('--sku')),
    store_code: required('--store-code', arg('--store-code')),
    amount_cents: amount,
    currency: arg('--currency'),
    trace_id: arg('--trace-id'),
  });

  process.stdout.write(`${result.outcome} ${result.traceId}\n`);
} catch (error) {
  const message = error instanceof IngestHttpError
    ? `${error.status} ${error.message}`
    : error instanceof Error
      ? error.message
      : 'Unknown ingest error';

  process.stderr.write(`${message}\n`);
  process.exitCode = 1;
}
