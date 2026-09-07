import type { LaravelValidationErrors } from './types.ts';

export class IngestHttpError extends Error {
  readonly status: number;

  constructor(message: string, status: number) {
    super(message);
    this.name = new.target.name;
    this.status = status;
  }
}

export class IngestUnauthorizedError extends IngestHttpError {
  constructor(message = 'Invalid ingest token.') {
    super(message, 401);
  }
}

export class IngestValidationError extends IngestHttpError {
  readonly errors: LaravelValidationErrors;

  constructor(errors: LaravelValidationErrors, message = 'The given data was invalid.') {
    super(message, 422);
    this.errors = errors;
  }
}

export class IngestConflictError extends IngestHttpError {
  readonly traceId: string;

  constructor(traceId: string, message = 'trace_id already used with a different payload') {
    super(message, 409);
    this.traceId = traceId;
  }
}

export class IngestRateLimitError extends IngestHttpError {
  readonly retryAfterSeconds: number | undefined;

  constructor(retryAfterSeconds?: number, message = 'Too many ingest attempts.') {
    super(message, 429);
    this.retryAfterSeconds = retryAfterSeconds;
  }
}
