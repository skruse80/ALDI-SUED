export function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

export function readString(value: unknown): string | undefined {
  return typeof value === 'string' && value !== '' ? value : undefined;
}

export function parseRetryAfter(header: string | null): number | undefined {
  if (header === null || header === '') {
    return undefined;
  }

  const seconds = Number.parseInt(header, 10);

  return Number.isFinite(seconds) && seconds >= 0 ? seconds : undefined;
}

export function parseValidationErrors(payload: unknown): Record<string, string[]> {
  if (!isRecord(payload) || !isRecord(payload.errors)) {
    return {};
  }

  const errors: Record<string, string[]> = {};

  for (const [field, messages] of Object.entries(payload.errors)) {
    if (Array.isArray(messages) && messages.every((message) => typeof message === 'string')) {
      errors[field] = messages;
    }
  }

  return errors;
}
